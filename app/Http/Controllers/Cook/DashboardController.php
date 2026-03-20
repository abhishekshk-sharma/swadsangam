<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function branchId(): ?int
    {
        return auth()->guard('employee')->user()->branch_id ?? null;
    }

    private function waiterManagerIds(): array
    {
        $branchId = $this->branchId();
        return \App\Models\Employee::whereIn('role', ['waiter', 'manager', 'cashier'])
            ->where(fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereNull('branch_id'))
            ->pluck('id')->all();
    }

    public function index()
    {
        $ids = $this->waiterManagerIds();

        $stats = [
            'active'      => Order::whereDate('created_at', today())->whereIn('status', ['pending', 'preparing'])->whereIn('user_id', $ids)->count(),
            'ready'       => Order::whereDate('created_at', today())->where('status', 'ready')->whereIn('user_id', $ids)->count(),
            'total_today' => Order::whereDate('created_at', today())->whereIn('user_id', $ids)->count(),
        ];

        $chartData = Order::whereDate('created_at', today())
            ->whereIn('user_id', $ids)
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
            ->whereIn('status', ['pending', 'preparing'])
            ->whereIn('user_id', $ids)
            ->whereDate('created_at', today())
            ->latest()
            ->take(5)
            ->get();

        return view('cook.dashboard', compact('stats', 'hours', 'counts', 'recentOrders'));
    }
}
