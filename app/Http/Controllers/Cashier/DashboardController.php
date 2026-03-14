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
            'pending_payment' => Order::where('status', '!=', 'closed')->count(),
            'today_revenue' => Order::whereDate('created_at', today())->where('status', 'closed')->sum('total_amount'),
            'today_orders' => Order::whereDate('created_at', today())->where('status', 'closed')->count(),
            'cash_collected' => Order::whereDate('created_at', today())->where('status', 'closed')->where('payment_mode', 'cash')->sum('total_amount'),
        ];

        // Today's hourly revenue
        $chartData = Order::whereDate('created_at', today())
            ->where('status', 'closed')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hours = [];
        $revenues = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[] = sprintf('%02d:00', $i);
            $revenues[] = $chartData->firstWhere('hour', $i)->revenue ?? 0;
        }

        $recentPayments = Order::with('table')
            ->where('status', 'closed')
            ->whereDate('created_at', today())
            ->latest()
            ->take(5)
            ->get();

        return view('cashier.dashboard', compact('stats', 'hours', 'revenues', 'recentPayments'));
    }
}
