<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::where('tenant_id', session('tenant_id'))->get();
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:employees,email,NULL,id,tenant_id,' . session('tenant_id'),
            'password' => 'required|min:6',
            'role' => 'required|in:waiter,chef,cashier',
        ]);

        Employee::create([
            'tenant_id' => session('tenant_id'),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        return redirect('/admin/employees')->with('success', 'Employee added');
    }

    public function edit($id)
    {
        $employee = Employee::where('tenant_id', session('tenant_id'))->findOrFail($id);
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::where('tenant_id', session('tenant_id'))->findOrFail($id);
        
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:employees,email,' . $id . ',id,tenant_id,' . session('tenant_id'),
            'role' => 'required|in:waiter,chef,cashier',
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
        Employee::where('tenant_id', session('tenant_id'))->findOrFail($id)->delete();
        return redirect('/admin/employees')->with('success', 'Employee deleted');
    }
}
