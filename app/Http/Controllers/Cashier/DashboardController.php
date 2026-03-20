<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $branchId = auth()->guard('employee')->user()->branch_id ?? null;
        $branch   = fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereNull('branch_id');

        $stats = [
            'pending_payment' => Order::where('status', 'served')->whereDate('created_at', today())->where($branch)->count(),
            'today_orders'    => Order::whereDate('created_at', today())->where('status', 'paid')->where($branch)->count(),
            'pending_parcels' => Order::where('is_parcel', true)->whereNotIn('status', ['paid', 'cancelled'])->whereDate('created_at', today())->where($branch)->count(),
            'paid_today'      => Order::whereDate('created_at', today())->where('status', 'paid')->where($branch)->count(),
        ];

        $chartData = Order::whereDate('created_at', today())
            ->where('status', 'paid')
            ->where($branch)
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
            ->where($branch)
            ->latest()
            ->take(5)
            ->get();

        return view('cashier.dashboard', compact('stats', 'hours', 'counts', 'recentPayments'));
    }
}
