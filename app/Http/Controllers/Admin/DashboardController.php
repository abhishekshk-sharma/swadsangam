<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{RestaurantTable, MenuItem, Order, Employee};

class DashboardController extends Controller
{
    public function index()
    {
        $recentOrders    = Order::with(['table', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])->latest()->take(5)->get();
        $recentTables    = RestaurantTable::with(['category', 'orders' => fn($q) => $q->whereIn('status', ['pending','preparing','served'])->latest()->limit(1)])->get();
        $recentMenuItems = MenuItem::with('category')->latest()->take(5)->get();
        $recentEmployees = Employee::latest()->take(5)->get();

        return view('admin.dashboard', compact('recentOrders', 'recentTables', 'recentMenuItems', 'recentEmployees'));
    }
}
