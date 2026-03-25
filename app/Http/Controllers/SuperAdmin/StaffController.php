<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Employee, Tenant, Branch};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $tenants = Tenant::orderBy('name')->get();
        $query   = Employee::withoutGlobalScope('tenant')->with(['tenant', 'branch']);

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }

        $staff          = $query->orderBy('tenant_id')->orderBy('role')->orderBy('name')->paginate(25)->withQueryString();
        $selectedTenant = $request->tenant_id;
        $selectedRole   = $request->role;
        $search         = $request->search;

        return view('superadmin.staff.index', compact('staff', 'tenants', 'selectedTenant', 'selectedRole', 'search'));
    }

    public function create()
    {
        $tenants  = Tenant::where('status', 'active')->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('superadmin.staff.create', compact('tenants', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'branch_id' => 'nullable|exists:branches,id',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:employees,email',
            'password'  => 'required|min:6',
            'role'      => 'required|in:manager,waiter,chef,cashier',
            'phone'     => 'nullable|string|max:20',
        ]);

        Employee::withoutGlobalScope('tenant')->create([
            'tenant_id' => $request->tenant_id,
            'branch_id' => $request->branch_id ?: null,
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => true,
        ]);

        return redirect('/superadmin/staff')->with('success', 'Staff created.');
    }

    public function edit($id)
    {
        $employee = Employee::withoutGlobalScope('tenant')->findOrFail($id);
        $tenants  = Tenant::where('status', 'active')->orderBy('name')->get();
        $branches = Branch::where('tenant_id', $employee->tenant_id)->where('is_active', true)->get();
        return view('superadmin.staff.edit', compact('employee', 'tenants', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::withoutGlobalScope('tenant')->findOrFail($id);

        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'branch_id' => 'nullable|exists:branches,id',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:employees,email,' . $id,
            'role'      => 'required|in:manager,waiter,chef,cashier',
            'password'  => 'nullable|min:6',
            'phone'     => 'nullable|string|max:20',
        ]);

        $data = $request->only('tenant_id', 'branch_id', 'name', 'email', 'phone', 'role', 'is_active');
        $data['branch_id'] = $request->branch_id ?: null;
        $data['is_active'] = $request->boolean('is_active');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);
        return redirect('/superadmin/staff')->with('success', 'Staff updated.');
    }

    public function destroy($id)
    {
        Employee::withoutGlobalScope('tenant')->findOrFail($id)->delete();
        return redirect('/superadmin/staff')->with('success', 'Staff deleted.');
    }

    public function branchesByTenant($tenantId)
    {
        $branches = Branch::where('tenant_id', $tenantId)->where('is_active', true)->get(['id', 'name']);
        return response()->json($branches);
    }
}
