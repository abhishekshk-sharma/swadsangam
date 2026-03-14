<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Tenant, SuperAdmin, Admin};

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'total_admins' => Admin::count(),
            'super_admins' => SuperAdmin::count(),
        ];
        
        $recentTenants = Tenant::latest()->take(5)->get();
        
        return view('superadmin.dashboard', compact('stats', 'recentTenants'));
    }
}
