<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Models\{Order, RestaurantTable, MenuItem};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $branchId = auth()->guard('employee')->user()->branch_id ?? null;

        $stats = [
            'pending'     => Order::whereDate('created_at', today())->where('status', 'pending')->where(fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereNull('branch_id'))->count(),
            'preparing'   => Order::whereDate('created_at', today())->where('status', 'preparing')->where(fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereNull('branch_id'))->count(),
            'ready'       => Order::whereDate('created_at', today())->where('status', 'ready')->where(fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereNull('branch_id'))->count(),
            'total_today' => Order::whereDate('created_at', today())->where(fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereNull('branch_id'))->count(),
        ];

        $chartData = Order::whereDate('created_at', today())
            ->where(fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereNull('branch_id'))
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
            ->where(fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereNull('branch_id'))
            ->latest()
            ->take(5)
            ->get();

        return view('waiter.dashboard', compact('stats', 'hours', 'counts', 'recentOrders'));
    }
}
