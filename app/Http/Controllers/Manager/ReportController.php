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

        if ($request->filter_type === 'month' && $request->month) {
            $query->whereYear('created_at', substr($request->month, 0, 4))
                  ->whereMonth('created_at', substr($request->month, 5, 2));
        } elseif ($request->filter_type === 'year' && $request->year) {
            $query->whereYear('created_at', $request->year);
        } elseif ($request->filter_type === 'custom' && $request->date_from && $request->date_to) {
            $query->whereDate('created_at', '>=', $request->date_from)
                  ->whereDate('created_at', '<=', $request->date_to);
        }

        $orders       = $query->orderBy('created_at', 'desc')->get();
        $totalRevenue = $orders->where('status', 'paid')->sum('total_amount');
        $totalOrders  = $orders->count();

        // GST stats
        $branchId = $this->branchId();
        $branch   = $branchId ? \App\Models\Branch::with('gstSlab')->find($branchId) : null;
        $slab     = $branch?->gstSlab;
        $mode     = $branch?->gst_mode;
        $gstStats = ['enabled' => false];
        if ($slab && $mode) {
            $paidOrders = $orders->where('status', 'paid');
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
            $gstStats = ['enabled' => true, 'cgst' => $cgst, 'sgst' => $sgst, 'total' => $cgst + $sgst, 'mode' => $mode];
        }

        return view('manager.reports.index', compact('orders', 'totalRevenue', 'totalOrders', 'gstStats'));
    }

    public function export(Request $request)
    {
        $query = Order::where('tenant_id', $this->tenantId())
            ->with(['table', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'user']);

        $this->scopeBranch($query);

        if ($request->filter_type === 'month' && $request->month) {
            $query->whereYear('created_at', substr($request->month, 0, 4))
                  ->whereMonth('created_at', substr($request->month, 5, 2));
            $filename = 'orders_' . $request->month . '.xlsx';
        } elseif ($request->filter_type === 'year' && $request->year) {
            $query->whereYear('created_at', $request->year);
            $filename = 'orders_' . $request->year . '.xlsx';
        } elseif ($request->filter_type === 'custom' && $request->date_from && $request->date_to) {
            $query->whereDate('created_at', '>=', $request->date_from)
                  ->whereDate('created_at', '<=', $request->date_to);
            $filename = 'orders_' . $request->date_from . '_to_' . $request->date_to . '.xlsx';
        } else {
            $filename = 'orders_all.xlsx';
        }

        return Excel::download(new OrdersExport($query->orderBy('created_at', 'desc')->get()), $filename);
    }
}
