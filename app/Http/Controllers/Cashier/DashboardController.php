<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'pending_payment' => Order::where('status', 'served')
            ->whereDate('created_at', today())->count(),
            'today_orders'    => Order::whereDate('created_at', today())->where('status', 'paid')->count(),
            'pending_parcels' => Order::where('is_parcel', true)->whereNotIn('status', ['paid', 'cancelled'])->whereDate('created_at', today())->count(),
            'paid_today'      => Order::whereDate('created_at', today())->where('status', 'paid')->count(),
        ];

        $chartData = Order::whereDate('created_at', today())
            ->where('status', 'paid')
            ->select(DB::raw('HOUR(paid_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hours  = [];
        $counts = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[]  = sprintf('%02d:00', $i);
            $counts[] = $chartData->firstWhere('hour', $i)->count ?? 0;
        }

        $recentPayments = Order::with('table')
            ->where('status', 'paid')
            ->whereDate('created_at', today())
            ->latest()
            ->take(5)
            ->get();

        return view('cashier.dashboard', compact('stats', 'hours', 'counts', 'recentPayments'));
    }
}
