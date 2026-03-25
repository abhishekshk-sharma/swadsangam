<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Tenant, Branch};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    public function login()
    {
        return view('superadmin.auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('super_admin')->attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => true])) {
            $request->session()->regenerate();
            return redirect('/superadmin/dashboard');
        }
        
        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('super_admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/superadmin/login');
    }

    public function index()
    {
        $tenants = Tenant::withCount([
            'tables' => fn($q) => $q->withoutGlobalScope('tenant'),
            'menuItems' => fn($q) => $q->withoutGlobalScope('tenant'),
            'orders' => fn($q) => $q->withoutGlobalScope('tenant')
        ])->get();
        
        return view('superadmin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('superadmin.tenants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:tenants|alpha_dash',
        ]);

        $tenant = Tenant::create([
            'name'   => $request->name,
            'slug'   => $request->slug,
            'domain' => $request->domain,
            'status' => 'active',
        ]);

        Branch::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Main',
            'is_active' => true,
        ]);

        return redirect('/superadmin/tenants')->with('success', 'Tenant created with default Main branch');
    }

    public function edit($id)
    {
        $tenant = Tenant::findOrFail($id);
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        
        $request->validate([
            'name' => 'required',
            'slug' => 'required|alpha_dash|unique:tenants,slug,' . $id,
            'status' => 'required|in:active,suspended',
        ]);

        $tenant->update($request->only(['name', 'slug', 'domain', 'status']));

        return redirect('/superadmin/tenants')->with('success', 'Tenant updated');
    }

    public function destroy($id)
    {
        Tenant::findOrFail($id)->delete();
        return redirect('/superadmin/tenants')->with('success', 'Tenant deleted');
    }
}
