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
            'pending'     => Order::whereDate('created_at', today())->where('status', 'pending')->count(),
            'preparing'   => Order::whereDate('created_at', today())->where('status', 'preparing')->count(),
            'ready'       => Order::whereDate('created_at', today())->where('status', 'ready')->count(),
            'total_today' => Order::whereDate('created_at', today())->count(),
        ];

        $chartData = Order::whereDate('created_at', today())
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hours = [];
        $counts = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[]  = sprintf('%02d:00', $i);
            $counts[] = $chartData->firstWhere('hour', $i)->count ?? 0;
        }

        $recentOrders = Order::with('table')
            ->where('user_id', current_user_id())
            ->where('status', '!=', 'paid')
            ->whereDate('created_at', today())
            ->latest()
            ->take(5)
            ->get();

        return view('waiter.dashboard', compact('stats', 'hours', 'counts', 'recentOrders'));
    }
}
