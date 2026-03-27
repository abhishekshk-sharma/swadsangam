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
        $query = Order::where('tenant_id', $tenantId)
            ->with(['table', 'branch.gstSlab', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'user']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        } elseif (app()->bound('current_branch_id')) {
            $query->where('branch_id', app('current_branch_id'));
        }

        if ($request->filter_type === 'month' && $request->filled('month')) {
            $query->whereYear('created_at', substr($request->month, 0, 4))
                  ->whereMonth('created_at', substr($request->month, 5, 2));
        } elseif ($request->filter_type === 'year' && $request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        } elseif ($request->filter_type === 'custom' && $request->filled('date_from') && $request->filled('date_to')) {
            $query->whereDate('created_at', '>=', $request->date_from)
                  ->whereDate('created_at', '<=', $request->date_to);
        }

        $orders       = $query->orderBy('created_at', 'desc')->get();
        $paidOrders   = $orders->where('status', 'paid');
        $totalRevenue = $paidOrders->sum('total_amount');
        $totalOrders  = $orders->count();
        $branches     = \App\Models\Branch::where('tenant_id', $tenantId)->where('is_active', true)->with('gstSlab')->get();
        $selectedBranch = $request->branch_id;

        // GST stats for paid orders
        $gstStats = $this->computeGstStats($paidOrders, $branches, $request->branch_id);

        $stats = [
            'orders_today'       => Order::where('tenant_id', $tenantId)->whereDate('created_at', today())->count(),
            'revenue_today'      => Order::where('tenant_id', $tenantId)->where('status', 'paid')->whereDate('created_at', today())->sum('total_amount'),
            'revenue_this_month' => Order::where('tenant_id', $tenantId)->where('status', 'paid')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount'),
        ];

        return view('admin.reports.index', compact('orders', 'totalRevenue', 'totalOrders', 'branches', 'selectedBranch', 'stats', 'gstStats'));
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
        $query = Order::where('tenant_id', $tenantId)
            ->with(['table', 'branch.gstSlab', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'user']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        } elseif (app()->bound('current_branch_id')) {
            $query->where('branch_id', app('current_branch_id'));
        }

        if ($request->filter_type === 'month' && $request->filled('month')) {
            $query->whereYear('created_at', substr($request->month, 0, 4))
                  ->whereMonth('created_at', substr($request->month, 5, 2));
            $filename = 'orders_' . $request->month . '.xlsx';
        } elseif ($request->filter_type === 'year' && $request->filled('year')) {
            $query->whereYear('created_at', $request->year);
            $filename = 'orders_' . $request->year . '.xlsx';
        } elseif ($request->filter_type === 'custom' && $request->filled('date_from') && $request->filled('date_to')) {
            $query->whereDate('created_at', '>=', $request->date_from)
                  ->whereDate('created_at', '<=', $request->date_to);
            $filename = 'orders_' . $request->date_from . '_to_' . $request->date_to . '.xlsx';
        } else {
            $filename = 'orders_all.xlsx';
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $branches   = \App\Models\Branch::where('tenant_id', $tenantId)->with('gstSlab')->get();
        $gstStats   = $this->computeGstStats($orders->where('status', 'paid'), $branches, $request->branch_id);

        return Excel::download(new OrdersExport($orders, $gstStats), $filename);
    }
}
