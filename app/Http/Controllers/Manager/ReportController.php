<?php

namespace App\Http\Controllers\Manager;

use App\Models\Order;
use App\Exports\OrdersExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends BaseManagerController
{
    public function index(Request $request)
    {
        $query = Order::where('tenant_id', $this->tenantId())
            ->with(['table', 'branch.gstSlab', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'user']);

        $this->scopeBranch($query);
        $this->applyDateFilter($query, $request);

        if ($request->filled('payment_mode') && $request->payment_mode !== 'all') {
            $query->where('payment_mode', $request->payment_mode);
        }

        $orders      = $query->orderBy('created_at', 'desc')->get();
        $paidOrders  = $orders->where('status', 'paid');
        $totalOrders = $orders->count();
        $totalRevenue = $paidOrders->sum(fn($o) => $o->grand_total ?? $o->total_amount);

        // Payment totals always from full paid set (no mode filter) so cards show complete breakdown
        $allPaidQuery = Order::where('tenant_id', $this->tenantId())->where('status', 'paid');
        $this->scopeBranch($allPaidQuery);
        $this->applyDateFilter($allPaidQuery, $request);
        $allPaid = $allPaidQuery->get();

        $paymentTotals = [
            'cash' => $allPaid->where('payment_mode', 'cash')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
            'upi'  => $allPaid->where('payment_mode', 'upi')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
            'card' => $allPaid->where('payment_mode', 'card')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
        ];

        $gstStats = $this->computeGstStats($paidOrders);

        return view('manager.reports.index', compact('orders', 'totalRevenue', 'totalOrders', 'gstStats', 'paymentTotals'));
    }

    public function deleteOrders(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:day,month,year,custom',
            'confirm'     => 'required|in:yes',
        ]);

        $query = Order::where('tenant_id', $this->tenantId())
            ->whereIn('status', ['paid', 'cancelled']);

        $this->scopeBranch($query);

        if ($request->filter_type === 'day' && $request->filled('day')) {
            $query->whereDate('created_at', $request->day);
        } elseif ($request->filter_type === 'month' && $request->filled('month')) {
            $query->whereYear('created_at', substr($request->month, 0, 4))
                  ->whereMonth('created_at', substr($request->month, 5, 2));
        } elseif ($request->filter_type === 'year' && $request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        } elseif ($request->filter_type === 'custom' && $request->filled('date_from') && $request->filled('date_to')) {
            $query->whereDate('created_at', '>=', $request->date_from)
                  ->whereDate('created_at', '<=', $request->date_to);
        }

        $count = $query->count();
        $query->delete();

        return back()->with('delete_success', $count . ' order(s) deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = Order::where('tenant_id', $this->tenantId())
            ->with(['table', 'branch.gstSlab', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'user']);

        $this->scopeBranch($query);
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

        $gstStats = $this->computeGstStats($paid);

        $filename = 'orders';
        if ($request->filter_type === 'month' && $request->month)           $filename .= '_' . $request->month;
        elseif ($request->filter_type === 'year' && $request->year)         $filename .= '_' . $request->year;
        elseif ($request->filter_type === 'custom')                         $filename .= '_' . $request->date_from . '_to_' . $request->date_to;
        if ($request->filled('payment_mode') && $request->payment_mode !== 'all') $filename .= '_' . $request->payment_mode;
        $filename .= '.xlsx';

        return Excel::download(new OrdersExport($orders, $gstStats, $paymentTotals), $filename);
    }

    private function applyDateFilter($query, Request $request): void
    {
        if ($request->filter_type === 'month' && $request->month) {
            $query->whereYear('created_at', substr($request->month, 0, 4))
                  ->whereMonth('created_at', substr($request->month, 5, 2));
        } elseif ($request->filter_type === 'year' && $request->year) {
            $query->whereYear('created_at', $request->year);
        } elseif ($request->filter_type === 'custom' && $request->date_from && $request->date_to) {
            $query->whereDate('created_at', '>=', $request->date_from)
                  ->whereDate('created_at', '<=', $request->date_to);
        }
    }

    private function computeGstStats($paidOrders): array
    {
        $branchId = $this->branchId();
        $branch   = $branchId ? \App\Models\Branch::with('gstSlab')->find($branchId) : null;
        $slab     = $branch?->gstSlab;
        $mode     = $branch?->gst_mode;
        if (!$slab || !$mode) return ['enabled' => false];

        $cgst = $sgst = 0;
        foreach ($paidOrders as $o) {
            $base = (float) $o->total_amount;
            if ($mode === 'excluded') {
                $cgst += round($base * $slab->cgst_rate / 100, 2);
                $sgst += round($base * $slab->sgst_rate / 100, 2);
            } else {
                $totalPct = $slab->cgst_rate + $slab->sgst_rate;
                $baseEx   = round($base * 100 / (100 + $totalPct), 2);
                $cgst    += round($baseEx * $slab->cgst_rate / 100, 2);
                $sgst    += round($baseEx * $slab->sgst_rate / 100, 2);
            }
        }
        return ['enabled' => true, 'cgst' => $cgst, 'sgst' => $sgst, 'total' => $cgst + $sgst, 'mode' => $mode];
    }
}
