<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Tenant, SuperAdmin, Admin, Employee, Branch, Order};

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'tenants'        => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'total_admins'   => Admin::withoutGlobalScope('tenant')->count(),
            'super_admins'   => SuperAdmin::count(),
            'total_branches' => Branch::count(),
            'total_employees'=> Employee::withoutGlobalScope('tenant')->count(),
            'total_orders'   => Order::withoutGlobalScopes()->count(),
            'total_revenue'  => Order::withoutGlobalScopes()->where('status', 'paid')->sum('total_amount'),
        ];

        $recentTenants = Tenant::withCount([
            'orders' => fn($q) => $q->withoutGlobalScope('tenant'),
        ])->latest()->take(5)->get();

        return view('superadmin.dashboard', compact('stats', 'recentTenants'));
    }
}
