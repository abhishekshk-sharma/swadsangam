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
            ->with(['table', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'user']);

        $this->scopeBranch($query);

        if ($request->filter_type === 'date' && $request->date) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->filter_type === 'month' && $request->month) {
            $query->whereYear('created_at', substr($request->month, 0, 4))
                  ->whereMonth('created_at', substr($request->month, 5, 2));
        } elseif ($request->filter_type === 'year' && $request->year) {
            $query->whereYear('created_at', $request->year);
        }

        $orders       = $query->orderBy('created_at', 'desc')->get();
        $totalRevenue = $orders->where('status', 'paid')->sum('total_amount');
        $totalOrders  = $orders->count();

        return view('manager.reports.index', compact('orders', 'totalRevenue', 'totalOrders'));
    }

    public function export(Request $request)
    {
        $query = Order::where('tenant_id', $this->tenantId())
            ->with(['table', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'user']);

        $this->scopeBranch($query);

        if ($request->filter_type === 'date' && $request->date) {
            $query->whereDate('created_at', $request->date);
            $filename = 'orders_' . $request->date . '.xlsx';
        } elseif ($request->filter_type === 'month' && $request->month) {
            $query->whereYear('created_at', substr($request->month, 0, 4))
                  ->whereMonth('created_at', substr($request->month, 5, 2));
            $filename = 'orders_' . $request->month . '.xlsx';
        } elseif ($request->filter_type === 'year' && $request->year) {
            $query->whereYear('created_at', $request->year);
            $filename = 'orders_' . $request->year . '.xlsx';
        } else {
            $filename = 'orders_all.xlsx';
        }

        return Excel::download(new OrdersExport($query->orderBy('created_at', 'desc')->get()), $filename);
    }
}
