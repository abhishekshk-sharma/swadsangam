<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function login()
    {
        return view('superadmin.login');
    }

    public function authenticate(Request $request)
    {
        // Simple password: "superadmin123" (change this!)
        if ($request->password === 'superadmin123') {
            session(['super_admin_authenticated' => true]);
            return redirect('/superadmin/tenants');
        }
        
        return back()->withErrors(['password' => 'Invalid password']);
    }

    public function logout()
    {
        session()->forget('super_admin_authenticated');
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

        Tenant::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'domain' => $request->domain,
            'status' => 'active',
        ]);

        return redirect('/superadmin/tenants')->with('success', 'Tenant created');
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
