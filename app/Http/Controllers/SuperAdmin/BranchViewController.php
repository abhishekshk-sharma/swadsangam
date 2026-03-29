<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Branch, Employee, Order, RestaurantTable, MenuItem};

class BranchViewController extends Controller
{
    public function show($id)
    {
        $branch = Branch::with(['tenant', 'gstSlab'])->findOrFail($id);

        $staff = Employee::withoutGlobalScopes()
            ->where('branch_id', $id)
            ->orderBy('role')
            ->get();

        $tables = RestaurantTable::withoutGlobalScopes()
            ->with('category')
            ->where('branch_id', $id)
            ->get()
            ->sort(function ($a, $b) {
                preg_match('/^(\D*)(\d*)(.*)$/', $a->table_number, $am);
                preg_match('/^(\D*)(\d*)(.*)$/', $b->table_number, $bm);
                $p = strcmp($am[1] ?? '', $bm[1] ?? '');
                if ($p !== 0) return $p;
                $na = (int)($am[2] ?? 0); $nb = (int)($bm[2] ?? 0);
                return $na !== $nb ? $na - $nb : strcmp($am[3] ?? '', $bm[3] ?? '');
            })->values();

        $menuItems = MenuItem::withoutGlobalScopes()
            ->with('category')
            ->where('branch_id', $id)
            ->get();

        $todayOrders = Order::withoutGlobalScopes()
            ->with(['table', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
            ->where('branch_id', $id)
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        $stats = [
            'total_staff'    => $staff->count(),
            'total_tables'   => $tables->count(),
            'occupied_tables'=> $tables->where('is_occupied', true)->count(),
            'total_menu'     => $menuItems->count(),
            'available_menu' => $menuItems->where('is_available', true)->count(),
            'orders_today'   => $todayOrders->count(),
            'revenue_today'  => $todayOrders->where('status', 'paid')->sum('total_amount'),
            'total_revenue'  => Order::withoutGlobalScopes()->where('branch_id', $id)->where('status', 'paid')->sum('total_amount'),
            'total_orders'   => Order::withoutGlobalScopes()->where('branch_id', $id)->count(),
        ];

        return view('superadmin.branches.show', compact('branch', 'staff', 'tables', 'menuItems', 'todayOrders', 'stats'));
    }
}
