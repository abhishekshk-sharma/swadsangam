<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends BaseAdminController
{
    public function index(Request $request)
    {
        $tab     = $request->input('tab', 'managers');
        $perPage = in_array($request->input('per_page'), [10,25,50,100]) ? (int)$request->input('per_page') : 10;
        $search  = trim($request->input('search', ''));
        $status  = $request->input('status', '');
        $phone   = trim($request->input('phone', ''));

        // --- Managers tab ---
        $mQuery = Employee::with('branch')->where('role', 'manager');
        if ($search) $mQuery->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        });
        if ($phone)  $mQuery->where('phone', 'like', "%{$phone}%");
        if ($status !== '') $mQuery->where('is_active', (int)$status);
        $managers = $mQuery->paginate($perPage, ['*'], 'mpage')->withQueryString();

        // --- Staff tab ---
        $eQuery = Employee::with('branch')->where('role', '!=', 'manager');
        if ($search) $eQuery->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('role', 'like', "%{$search}%");
        });
        if ($phone)  $eQuery->where('phone', 'like', "%{$phone}%");
        if ($status !== '') $eQuery->where('is_active', (int)$status);
        $employees = $eQuery->paginate($perPage, ['*'], 'epage')->withQueryString();

        // --- Admins tab ---
        $aQuery = Admin::query();
        if ($search) $aQuery->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        });
        if ($phone)  $aQuery->where('phone', 'like', "%{$phone}%");
        if ($status !== '') $aQuery->where('is_active', (int)$status);
        $admins = $aQuery->paginate($perPage, ['*'], 'apage')->withQueryString();

        // All managers (unpaginated) needed for assign column + staff lookup
        $allManagers = Employee::with('branch')->where('role', 'manager')->get();

        // Assigned staff grouped by branch_id for the managers table
        $assignedByBranch = Employee::where('role', '!=', 'manager')
            ->whereNotNull('branch_id')
            ->get()
            ->groupBy('branch_id');

        return view('admin.employees.index', compact('managers','employees','admins','allManagers','assignedByBranch','tab','perPage','search','status','phone'));
    }

    public function create()
    {
        $branches = Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();
        return view('admin.employees.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $tenantId = $this->tenantId();

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', 'unique:employees,email,NULL,id,tenant_id,' . $tenantId],
            'password'  => 'required|min:6|confirmed',
            'role'      => 'required|in:waiter,chef,cashier,manager',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        Employee::create([
            'tenant_id' => $tenantId,
            'branch_id' => $request->branch_id ?: null,
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => true,
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee added');
    }

    public function edit($id)
    {
        $employee = $this->findForTenant(Employee::class, $id);
        $branches = Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();

        $assignedEmployees   = collect();
        $unassignedEmployees = collect();

        if ($employee->role === 'manager' && $employee->branch_id) {
            $assignedEmployees   = Employee::where('role', '!=', 'manager')
                                           ->where('branch_id', $employee->branch_id)
                                           ->get();
            $unassignedEmployees = Employee::where('role', '!=', 'manager')
                                           ->whereNull('branch_id')
                                           ->get();
        }

        return view('admin.employees.edit', compact('employee', 'branches', 'assignedEmployees', 'unassignedEmployees'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = $this->tenantId();
        $employee = $this->findForTenant(Employee::class, $id);

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', 'unique:employees,email,' . $id . ',id,tenant_id,' . $tenantId],
            'role'      => 'required|in:waiter,chef,cashier,manager',
            'password'  => 'nullable|min:6|confirmed',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'role', 'is_active']);
        $data['branch_id'] = $request->branch_id ?: null;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        // Handle assign panel (only for managers with a branch)
        if ($employee->role === 'manager' && $employee->branch_id) {
            $assignedIds = $request->input('assigned_ids', []);

            // Unassign all currently assigned employees for this branch
            Employee::where('role', '!=', 'manager')
                    ->where('branch_id', $employee->branch_id)
                    ->update(['branch_id' => null]);

            // Re-assign selected ones
            if (!empty($assignedIds)) {
                Employee::whereIn('id', $assignedIds)
                        ->update(['branch_id' => $employee->branch_id]);
            }
        }

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated');
    }

    public function destroy($id)
    {
        $this->findForTenant(Employee::class, $id)->delete();
        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted');
    }

    // Assign an employee to a manager (sets manager's branch_id on the employee)
    public function assignEmployee(Request $request, $managerId)
    {
        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $manager  = $this->findForTenant(Employee::class, $managerId);
        $employee = $this->findForTenant(Employee::class, $request->employee_id);

        $employee->update(['branch_id' => $manager->branch_id]);

        return back()->with('success', "{$employee->name} assigned to {$manager->name}");
    }

    // Unassign an employee from their manager (clears branch_id)
    public function unassignEmployee($employeeId)
    {
        $employee = $this->findForTenant(Employee::class, $employeeId);
        $employee->update(['branch_id' => null]);

        return back()->with('success', "{$employee->name} unassigned");
    }
}
