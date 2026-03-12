<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Models\{Order, RestaurantTable, MenuItem};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'tables_available' => RestaurantTable::where('is_occupied', false)->count(),
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'revenue_today' => Order::whereDate('created_at', today())->sum('total_amount'),
            'my_orders_today' => Order::where('user_id', auth()->id())->whereDate('created_at', today())->count(),
        ];

        // Last 7 days chart data
        $chartData = Order::where('tenant_id', session('tenant_id'))
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $recentOrders = Order::with('table')
            ->where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();

        return view('waiter.dashboard', compact('stats', 'chartData', 'recentOrders'));
    }
}
