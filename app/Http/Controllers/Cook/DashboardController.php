<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active' => Order::whereDate('created_at', today())->whereIn('status', ['pending', 'preparing'])->count(),
            'ready' => Order::whereDate('created_at', today())->where('status', 'ready')->count(),
            'total_today' => Order::whereDate('created_at', today())->count(),
        ];

        // Today's hourly data
        $chartData = Order::whereDate('created_at', today())
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hours = [];
        $counts = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[] = sprintf('%02d:00', $i);
            $counts[] = $chartData->firstWhere('hour', $i)->count ?? 0;
        }

        $recentOrders = Order::with('table')
            ->whereIn('status', ['pending', 'preparing'])
            ->whereDate('created_at', today())
            ->latest()
            ->take(5)
            ->get();

        return view('cook.dashboard', compact('stats', 'hours', 'counts', 'recentOrders'));
    }
}
