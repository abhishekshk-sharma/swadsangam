<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Exports\OrdersExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : null;
        $branches = \App\Models\Branch::where('tenant_id', $tenantId)->where('is_active', true)->with('gstSlab')->get();
        $selectedBranch = $request->branch_id;

        $query = Order::where('tenant_id', $tenantId)
            ->with(['table', 'branch.gstSlab', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'user']);

        $this->applyBranchFilter($query, $request);
        $this->applyDateFilter($query, $request);

        if ($request->filled('payment_mode') && $request->payment_mode !== 'all') {
            $query->where('payment_mode', $request->payment_mode);
        }

        $orders      = $query->orderBy('created_at', 'desc')->get();
        $paidOrders  = $orders->where('status', 'paid');
        $totalOrders = $orders->count();
        $totalRevenue = $paidOrders->sum(fn($o) => $o->grand_total ?? $o->total_amount);

        // Payment totals always from full paid set (no mode filter) so cards show complete breakdown
        $allPaidQuery = Order::where('tenant_id', $tenantId)->where('status', 'paid');
        $this->applyBranchFilter($allPaidQuery, $request);
        $this->applyDateFilter($allPaidQuery, $request);
        $allPaid = $allPaidQuery->get();

        $paymentTotals = [
            'cash' => $allPaid->where('payment_mode', 'cash')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
            'upi'  => $allPaid->where('payment_mode', 'upi')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
            'card' => $allPaid->where('payment_mode', 'card')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
        ];

        $gstStats = $this->computeGstStats($paidOrders, $branches, $selectedBranch);

        $stats = [
            'orders_today'       => Order::where('tenant_id', $tenantId)->whereDate('created_at', today())->count(),
            'revenue_today'      => Order::where('tenant_id', $tenantId)->where('status', 'paid')->whereDate('created_at', today())->sum('total_amount'),
            'revenue_this_month' => Order::where('tenant_id', $tenantId)->where('status', 'paid')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount'),
        ];

        return view('admin.reports.index', compact('orders', 'totalRevenue', 'totalOrders', 'branches', 'selectedBranch', 'stats', 'gstStats', 'paymentTotals'));
    }

    private function computeGstStats($paidOrders, $branches, $selectedBranchId): array
    {
        $totalCgst = 0; $totalSgst = 0; $enabled = false;
        foreach ($paidOrders as $order) {
            $branch = $branches->firstWhere('id', $order->branch_id);
            $slab   = $branch?->gstSlab;
            $mode   = $branch?->gst_mode;
            if (!$slab || !$mode) continue;
            $enabled = true;
            $base    = (float) $order->total_amount;
            $cgstPct = (float) $slab->cgst_rate;
            $sgstPct = (float) $slab->sgst_rate;
            if ($mode === 'excluded') {
                $totalCgst += round($base * $cgstPct / 100, 2);
                $totalSgst += round($base * $sgstPct / 100, 2);
            } else {
                $totalPct   = $cgstPct + $sgstPct;
                $baseEx     = round($base * 100 / (100 + $totalPct), 2);
                $totalCgst += round($baseEx * $cgstPct / 100, 2);
                $totalSgst += round($baseEx * $sgstPct / 100, 2);
            }
        }
        return ['enabled' => $enabled, 'cgst' => $totalCgst, 'sgst' => $totalSgst, 'total' => $totalCgst + $totalSgst];
    }

    public function export(Request $request)
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : null;
        $branches = \App\Models\Branch::where('tenant_id', $tenantId)->with('gstSlab')->get();

        $query = Order::where('tenant_id', $tenantId)
            ->with(['table', 'branch.gstSlab', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'user']);

        $this->applyBranchFilter($query, $request);
        $this->applyDateFilter($query, $request);

        if ($request->filled('payment_mode') && $request->payment_mode !== 'all') {
            $query->where('payment_mode', $request->payment_mode);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();
        $paid   = $orders->where('status', 'paid');

        $paymentTotals = [
            'cash' => $paid->where('payment_mode', 'cash')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
            'upi'  => $paid->where('payment_mode', 'upi')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
            'card' => $paid->where('payment_mode', 'card')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
        ];

        $gstStats = $this->computeGstStats($paid, $branches, $request->branch_id);

        $filename = 'orders';
        if ($request->filter_type === 'month' && $request->filled('month'))                                    $filename .= '_' . $request->month;
        elseif ($request->filter_type === 'year' && $request->filled('year'))                                  $filename .= '_' . $request->year;
        elseif ($request->filter_type === 'custom' && $request->filled('date_from'))                           $filename .= '_' . $request->date_from . '_to_' . $request->date_to;
        if ($request->filled('payment_mode') && $request->payment_mode !== 'all')                              $filename .= '_' . $request->payment_mode;
        $filename .= '.xlsx';

        return Excel::download(new OrdersExport($orders, $gstStats, $paymentTotals), $filename);
    }

    private function applyBranchFilter($query, Request $request): void
    {
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        } elseif (app()->bound('current_branch_id')) {
            $query->where('branch_id', app('current_branch_id'));
        }
    }

    private function applyDateFilter($query, Request $request): void
    {
        if ($request->filter_type === 'month' && $request->filled('month')) {
            $query->whereYear('created_at', substr($request->month, 0, 4))
                  ->whereMonth('created_at', substr($request->month, 5, 2));
        } elseif ($request->filter_type === 'year' && $request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        } elseif ($request->filter_type === 'custom' && $request->filled('date_from') && $request->filled('date_to')) {
            $query->whereDate('created_at', '>=', $request->date_from)
                  ->whereDate('created_at', '<=', $request->date_to);
        }
    }
}
