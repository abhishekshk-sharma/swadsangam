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
            'pending' => Order::whereDate('created_at', today())->where('status', 'pending')->count(),
            'preparing' => Order::whereDate('created_at', today())->where('status', 'preparing')->count(),
            'ready' => Order::whereDate('created_at', today())->where('status', 'ready')->count(),
            'total_today' => Order::whereDate('created_at', today())->count(),
        ];

        // Last 7 days data
        $chartData = Order::where('created_at', '>=', now()->subDays(6))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
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
            ->whereIn('status', ['pending', 'processing'])
            ->whereDate('created_at', today())
            ->latest()
            ->take(5)
            ->get();

        return view('cook.dashboard', compact('stats', 'dates', 'counts', 'recentOrders'));
    }
}
