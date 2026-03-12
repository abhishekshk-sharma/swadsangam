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

        // Last 7 days revenue
        $chartData = Order::where('created_at', '>=', now()->subDays(6))
            ->where('status', 'closed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = [];
        $revenues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('M d');
            $revenues[] = $chartData->firstWhere('date', $date)->revenue ?? 0;
        }

        $recentPayments = Order::with('table')
            ->where('status', 'closed')
            ->whereDate('created_at', today())
            ->latest()
            ->take(5)
            ->get();

        return view('cashier.dashboard', compact('stats', 'dates', 'revenues', 'recentPayments'));
    }
}
