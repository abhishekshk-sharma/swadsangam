<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Order, Tenant, Branch};
use App\Exports\OrdersExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tenants  = Tenant::orderBy('name')->get();
        $branches = collect();

        $query = Order::withoutGlobalScopes()
            ->with(['table', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'user']);

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
            $branches = Branch::where('tenant_id', $request->tenant_id)->where('is_active', true)->get();
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

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

        $stats = [
            'orders_today'       => Order::withoutGlobalScopes()->whereDate('created_at', today())->count(),
            'revenue_today'      => Order::withoutGlobalScopes()->where('status', 'paid')->whereDate('created_at', today())->sum('total_amount'),
            'revenue_this_month' => Order::withoutGlobalScopes()->where('status', 'paid')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount'),
        ];

        return view('superadmin.reports.index', compact('orders', 'totalRevenue', 'totalOrders', 'tenants', 'branches', 'stats'));
    }

    public function export(Request $request)
    {
        $query = Order::withoutGlobalScopes()
            ->with(['table', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'user']);

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

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

        $orders = $query->orderBy('created_at', 'desc')->get();
        return Excel::download(new OrdersExport($orders), $filename);
    }
}
