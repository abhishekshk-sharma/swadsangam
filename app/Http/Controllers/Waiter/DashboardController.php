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
            'pending' => Order::whereDate('created_at', today())->where('status', 'pending')->count(),
            'preparing' => Order::whereDate('created_at', today())->where('status', 'preparing')->count(),
            'ready' => Order::whereDate('created_at', today())->where('status', 'ready')->count(),
            'total_today' => Order::whereDate('created_at', today())->count(),
        ];

        // Last 7 days chart data
        $chartData = Order::where('tenant_id', session('tenant_id'))
            ->where('created_at', '>=', now()->subDays(6))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = [];
        $counts = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('M d');
            $counts[] = $chartData->firstWhere('date', $date)->count ?? 0;
        }

        $recentOrders = Order::with('table')
            ->where('user_id', auth()->id())
            ->where('status', '!=', 'paid')
            ->whereDate('created_at', today())
            ->latest()
            ->take(5)
            ->get();

        return view('waiter.dashboard', compact('stats', 'dates', 'counts', 'recentOrders'));
    }
}
