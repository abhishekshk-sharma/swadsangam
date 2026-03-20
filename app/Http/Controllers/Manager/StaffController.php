<?php

namespace App\Http\Controllers\Manager;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends BaseManagerController
{
    private function clean(mixed $val): string
    {
        $v = trim((string) $val);
        return ($v === '' || $v === 'null') ? '' : $v;
    }

    private function buildQuery(Request $request): array
    {
        $search  = $this->clean($request->input('search'));
        $phone   = $this->clean($request->input('phone'));
        $status  = $this->clean($request->input('status'));
        $role    = $this->clean($request->input('role'));
        $perPage = in_array($request->input('per_page'), ['10','25','50','100']) ? (int)$request->input('per_page') : 10;

        $branchId = $this->branchId();

        $staff = Employee::with('branch')
            ->where('role', '!=', 'manager')
            ->where(fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereNull('branch_id'))
            ->when($search !== '', fn($q) => $q->where(fn($q2) =>
                $q2->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")
            ))
            ->when($phone  !== '', fn($q) => $q->where('phone', 'like', "%{$phone}%"))
            ->when($status !== '', fn($q) => $q->where('is_active', (int) $status))
            ->when($role   !== '', fn($q) => $q->where('role', $role))
            ->orderBy('role')->orderBy('name')
            ->paginate($perPage);

        return compact('staff', 'search', 'phone', 'status', 'role', 'perPage');
    }

    public function index(Request $request)
    {
        return view('manager.staff.index', $this->buildQuery($request));
    }

    public function create()
    {
        return view('manager.staff.create');
    }

    public function store(Request $request)
    {
        $tenantId = $this->tenantId();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'unique:employees,email,NULL,id,tenant_id,' . $tenantId],
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:waiter,chef,cashier',
            'phone'    => 'nullable|string|max:20',
        ]);

        Employee::create([
            'tenant_id' => $tenantId,
            'branch_id' => $this->branchId(),
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('manager.staff.index')->with('success', 'Staff added successfully.');
    }

    public function edit($id)
    {
        $employee = $this->findForTenant(Employee::class, $id);
        return view('manager.staff.edit', compact('employee'));
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
            'phone'    => 'nullable|string|max:20',
        ]);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'role'      => $request->role,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);
        return redirect()->route('manager.staff.index')->with('success', 'Staff updated successfully.');
    }

    public function destroy($id)
    {
        $employee = $this->findForTenant(Employee::class, $id);
        abort_if($employee->branch_id !== $this->branchId(), 403, 'Cannot delete staff outside your branch.');
        $employee->delete();
        return redirect()->route('manager.staff.index')->with('success', 'Staff deleted.');
    }
}
