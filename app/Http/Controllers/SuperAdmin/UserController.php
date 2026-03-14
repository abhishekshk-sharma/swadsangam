<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{SuperAdmin, Admin, Tenant};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Get both super admins and admins
        $superAdmins = SuperAdmin::latest()->get()->map(function($user) {
            $user->role = 'super_admin';
            $user->tenant = null;
            return $user;
        });
        
        $admins = Admin::with('tenant')->latest()->get()->map(function($user) {
            $user->role = 'admin';
            return $user;
        });
        
        $users = $superAdmins->concat($admins);
        
        return view('superadmin.users.index', compact('users'));
    }

    public function create()
    {
        $tenants = Tenant::where('status', 'active')->get();
        return view('superadmin.users.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'role' => 'required|in:super_admin,admin',
            'tenant_id' => 'required_if:role,admin|exists:tenants,id',
        ]);

        if ($request->role === 'super_admin') {
            // Check unique email in super_admins table
            $request->validate([
                'email' => 'unique:super_admins,email',
            ]);
            
            SuperAdmin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);
            
            $message = 'Super Admin created successfully';
        } else {
            // Check unique email in admins table for this tenant
            $request->validate([
                'email' => 'unique:admins,email,NULL,id,tenant_id,' . $request->tenant_id,
            ]);
            
            Admin::create([
                'tenant_id' => $request->tenant_id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);
            
            $message = 'Restaurant Admin created successfully';
        }

        return redirect('/superadmin/users')->with('success', $message);
    }

    public function edit($id)
    {
        // Try to find in both tables
        $user = Admin::find($id);
        if ($user) {
            $user->role = 'admin';
        } else {
            $user = SuperAdmin::findOrFail($id);
            $user->role = 'super_admin';
        }
        
        $tenants = Tenant::where('status', 'active')->get();
        return view('superadmin.users.edit', compact('user', 'tenants'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required|in:super_admin,admin',
            'tenant_id' => 'required_if:role,admin|exists:tenants,id',
        ]);

        // Find user in appropriate table
        $user = Admin::find($id);
        $isSuperAdmin = false;
        
        if (!$user) {
            $user = SuperAdmin::find($id);
            $isSuperAdmin = true;
        }
        
        if (!$user) {
            return redirect('/superadmin/users')->with('error', 'User not found');
        }

        // Validate email uniqueness
        if ($request->role === 'super_admin') {
            $request->validate([
                'email' => 'unique:super_admins,email,' . $id,
            ]);
        } else {
            $request->validate([
                'email' => 'unique:admins,email,' . $id . ',id,tenant_id,' . $request->tenant_id,
            ]);
        }

        $data = $request->only(['name', 'email', 'is_active']);
        
        if ($request->role === 'admin') {
            $data['tenant_id'] = $request->tenant_id;
        }
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect('/superadmin/users')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        // Try to delete from both tables
        $deleted = Admin::where('id', $id)->delete();
        
        if (!$deleted) {
            $deleted = SuperAdmin::where('id', $id)->delete();
        }
        
        if ($deleted) {
            return redirect('/superadmin/users')->with('success', 'User deleted successfully');
        }
        
        return redirect('/superadmin/users')->with('error', 'User not found');
    }
}
