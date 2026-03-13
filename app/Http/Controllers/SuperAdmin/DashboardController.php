<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TableCategory;
use App\Models\MenuCategory;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'total_users' => User::count(),
            'super_admins' => User::where('role', 'super_admin')->count(),
        ];
        
        $recentTenants = Tenant::latest()->take(5)->get();
        
        return view('superadmin.dashboard', compact('stats', 'recentTenants'));
    }
}
