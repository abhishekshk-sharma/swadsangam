<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends BaseAdminController
{
    public function index()
    {
        $employees = Employee::get();
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $tenantId = $this->tenantId();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'unique:employees,email,NULL,id,tenant_id,' . $tenantId],
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:waiter,chef,cashier',
        ]);

        Employee::create([
            'tenant_id' => $tenantId,
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => true,
        ]);

        return redirect('/admin/employees')->with('success', 'Employee added');
    }

    public function edit($id)
    {
        $employee = $this->findForTenant(Employee::class, $id);
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = $this->tenantId();
        $employee = $this->findForTenant(Employee::class, $id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'unique:employees,email,' . $id . ',id,tenant_id,' . $tenantId],
            'role'     => 'required|in:waiter,chef,cashier',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'role', 'is_active']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);
        return redirect('/admin/employees')->with('success', 'Employee updated');
    }

    public function destroy($id)
    {
        $this->findForTenant(Employee::class, $id)->delete();
        return redirect('/admin/employees')->with('success', 'Employee deleted');
    }
}
