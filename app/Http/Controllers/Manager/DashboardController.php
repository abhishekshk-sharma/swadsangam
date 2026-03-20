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

        $orderQuery = Order::query();
        $this->scopeBranch($orderQuery);

        $stats = [
            'tables'        => (clone $tableQuery)->count(),
            'menu_items'    => MenuItem::count(),
            'orders_today'        => (clone $orderQuery)->whereDate('created_at', today())->count(),
            'revenue_today'        => (clone $orderQuery)->where('status', 'paid')->whereDate('created_at', today())->sum('total_amount'),
            'revenue_this_month'   => (clone $orderQuery)->where('status', 'paid')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount'),
            'employees'     => Employee::where('branch_id', $branchId)->count(),
        ];

        $recentOrders    = (clone $orderQuery)->with(['table', 'orderItems.menuItem'])->latest()->take(5)->get();
        $recentTables    = (clone $tableQuery)->with(['category', 'orders' => fn($q) => $q->whereIn('status', ['pending','preparing','served'])->latest()->limit(1)])->get();
        $recentMenuItems = MenuItem::with('category')->latest()->take(5)->get();
        $pendingOrders   = (clone $orderQuery)->whereIn('status', ['pending', 'preparing'])->count();

        return view('manager.dashboard', compact('stats', 'recentOrders', 'recentTables', 'recentMenuItems', 'pendingOrders'));
    }
}
