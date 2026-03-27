<?php

namespace App\Http\Controllers\Manager;

use App\Models\{RestaurantTable, MenuItem, Order, Employee, CashHandover};

class DashboardController extends BaseManagerController
{
    public function index()
    {
        $branchId = $this->branchId();

        $tableQuery = RestaurantTable::query();
        $this->scopeBranch($tableQuery);

        $orderQuery = Order::query()->where('tenant_id', $this->tenantId());
        $this->scopeBranch($orderQuery);

        $stats = [
            'tables'               => (clone $tableQuery)->count(),
            'tables_occupied'      => (clone $tableQuery)->where('is_occupied', true)->count(),
            'menu_items'           => MenuItem::where('tenant_id', $this->tenantId())->count(),
            'orders_today'         => (clone $orderQuery)->whereDate('created_at', today())->count(),
            'pending_orders'       => (clone $orderQuery)->whereIn('status', ['pending', 'preparing'])->whereDate('created_at', today())->count(),
            'revenue_today'        => (clone $orderQuery)->where('status', 'paid')->whereDate('created_at', today())->sum('total_amount'),
            'revenue_this_month'   => (clone $orderQuery)->where('status', 'paid')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount'),
            'employees'            => Employee::where('branch_id', $branchId)->where('tenant_id', $this->tenantId())->count(),
        ];

        $recentOrders    = (clone $orderQuery)->with(['table', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])->latest()->take(10)->get();
        $recentTables    = (clone $tableQuery)->with(['category', 'orders' => fn($q) => $q->whereIn('status', ['pending','preparing','ready','served'])->latest()->limit(1)])->get();
        $recentMenuItems = MenuItem::with('category')->where('tenant_id', $this->tenantId())->latest()->take(5)->get();
        $recentEmployees = Employee::where('branch_id', $branchId)->where('tenant_id', $this->tenantId())->latest()->take(5)->get();
        $pendingOrders   = $stats['pending_orders'];

        return view('manager.dashboard', compact('stats', 'recentOrders', 'recentTables', 'recentMenuItems', 'recentEmployees', 'pendingOrders'));
    }
}
