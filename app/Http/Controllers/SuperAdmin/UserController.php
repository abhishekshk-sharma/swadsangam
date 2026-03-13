<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{User, Tenant};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('tenant')
            ->whereIn('role', ['super_admin', 'admin'])
            ->latest()
            ->get();
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
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:super_admin,admin',
            'tenant_id' => 'required_if:role,admin|exists:tenants,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'tenant_id' => $request->role === 'admin' ? $request->tenant_id : null,
            'is_active' => true,
        ]);

        $message = $request->role === 'super_admin' ? 'Super Admin created' : 'Restaurant Admin created';
        return redirect('/superadmin/users')->with('success', $message);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $tenants = Tenant::where('status', 'active')->get();
        return view('superadmin.users.edit', compact('user', 'tenants'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:super_admin,admin',
            'tenant_id' => 'required_if:role,admin|exists:tenants,id',
        ]);

        $data = $request->only(['name', 'email', 'is_active', 'role']);
        $data['tenant_id'] = $request->role === 'admin' ? $request->tenant_id : null;
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect('/superadmin/users')->with('success', 'User updated');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect('/superadmin/users')->with('success', 'User deleted');
    }
}
