<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{RestaurantTable, MenuItem, Order, Employee, CashHandover};

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'tables'        => RestaurantTable::count(),
            'menu_items'    => MenuItem::count(),
            'orders_today'  => Order::whereDate('created_at', today())->count(),
            'revenue_today'      => Order::where('status', 'paid')->whereDate('created_at', today())->sum('total_amount'),
            'total_revenue'      => Order::where('status', 'paid')->sum('total_amount'),
            'employees'          => Employee::count(),
            'pending_handovers'  => CashHandover::where('status', 'pending')->count(),
        ];

        $recentOrders    = Order::with(['table', 'orderItems.menuItem'])->latest()->take(5)->get();
        $recentTables    = RestaurantTable::with('category')->latest()->take(5)->get();
        $recentMenuItems = MenuItem::with('category')->latest()->take(5)->get();
        $recentEmployees = Employee::latest()->take(5)->get();
        $pendingOrders   = Order::whereIn('status', ['pending', 'preparing'])->count();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentTables', 'recentMenuItems', 'recentEmployees', 'pendingOrders'));
    }
}
