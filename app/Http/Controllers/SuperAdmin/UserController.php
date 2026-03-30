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
        // Only show Restaurant Admins — Super Admins have their own profile page
        $users = Admin::withoutGlobalScope('tenant')->with('tenant')->latest()->get()->map(function ($user) {
            $user->role = 'admin';
            return $user;
        });

        return view('superadmin.users.index', compact('users'));
    }

    public function create()
    {
        $tenants = Tenant::where('status', 'active')->get();
        return view('superadmin.users.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        // Build rules based on role so everything is validated in one call
        $tenantId = $request->input('tenant_id');

        $emailUnique = $request->role === 'super_admin'
            ? 'unique:super_admins,email'
            : 'unique:admins,email';

        $phoneUnique = $request->role === 'super_admin'
            ? 'unique:super_admins,phone'
            : 'unique:admins,phone';

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', $emailUnique],
            'phone'     => ['required', 'string', 'max:20', $phoneUnique],
            'password'  => 'required|min:6',
            'role'      => 'required|in:super_admin,admin',
            'tenant_id' => 'required_if:role,admin|nullable|exists:tenants,id',
        ]);

        if ($request->role === 'super_admin') {
            SuperAdmin::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'password'  => Hash::make($request->password),
                'is_active' => true,
            ]);
            $message = 'Super Admin created successfully';
        } else {
            Admin::withoutGlobalScope('tenant')->create([
                'tenant_id' => $request->tenant_id,
                'name'      => $request->name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'password'  => Hash::make($request->password),
                'is_active' => true,
            ]);
            $message = 'Restaurant Admin created successfully';
        }

        return redirect('/superadmin/users')->with('success', $message);
    }

    public function edit($id)
    {
        $user = Admin::withoutGlobalScope('tenant')->find($id);
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
        // Determine existing user and their role — role is NOT changeable
        $user = Admin::withoutGlobalScope('tenant')->find($id);
        $isAdmin = (bool) $user;
        if (!$user) {
            $user = SuperAdmin::find($id);
        }
        if (!$user) {
            return redirect('/superadmin/users')->with('error', 'User not found');
        }

        $tenantId = $request->input('tenant_id');

        $emailUnique = $isAdmin
            ? 'unique:admins,email,' . $id . ',id,tenant_id,' . $tenantId
            : 'unique:super_admins,email,' . $id;

        $phoneUnique = $isAdmin
            ? 'unique:admins,phone,' . $id
            : 'unique:super_admins,phone,' . $id;

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', $emailUnique],
            'phone'     => ['required', 'string', 'max:20', $phoneUnique],
            'password'  => 'nullable|min:6',
            'tenant_id' => $isAdmin ? 'required|exists:tenants,id' : 'nullable',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'is_active']);

        if ($isAdmin) {
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
        $deleted = Admin::withoutGlobalScope('tenant')->where('id', $id)->delete();

        if (!$deleted) {
            $deleted = SuperAdmin::where('id', $id)->delete();
        }

        return $deleted
            ? redirect('/superadmin/users')->with('success', 'User deleted successfully')
            : redirect('/superadmin/users')->with('error', 'User not found');
    }
}
