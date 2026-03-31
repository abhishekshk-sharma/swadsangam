<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends BaseAdminController
{
    private function clean(mixed $val): string
    {
        $v = trim((string) $val);
        return ($v === '' || $v === 'null') ? '' : $v;
    }

    private function buildStaffQuery(Request $request): array
    {
        $search  = $this->clean($request->input('search'));
        $phone   = $this->clean($request->input('phone'));
        $status  = $this->clean($request->input('status'));
        $role    = $this->clean($request->input('role'));
        $perPage = in_array($request->input('per_page'), ['10','25','50','100']) ? (int)$request->input('per_page') : 10;

        $staff = Employee::with('branch')
            ->where('role', '!=', 'manager')
            ->when(app()->bound('current_branch_id'), fn($q) => $q->where('branch_id', app('current_branch_id')))
            ->when($search !== '', fn($q) => $q->where(fn($q2) =>
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
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
        return view('admin.staff.index', $this->buildStaffQuery($request));
    }

    public function ajaxIndex(Request $request)
    {
        $data = $this->buildStaffQuery($request);
        return response()->json([
            'tbody'      => view('admin.staff._tbody', $data)->render(),
            'pagination' => view('admin.staff._pagination', $data)->render(),
            'total'      => $data['staff']->total(),
        ]);
    }

    public function create()
    {
        $branches = Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();
        return view('admin.staff.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $tenantId = $this->tenantId();

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', 'unique:employees,email,NULL,id,tenant_id,' . $tenantId],
            'phone'     => ['required', 'string', 'max:20', 'unique:employees,phone'],
            'password'  => 'required|min:6|confirmed',
            'role'      => 'required|in:waiter,chef,cashier',
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
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.staff.index')->with('success', 'Staff added successfully.');
    }

    public function edit($id)
    {
        $employee = $this->findForTenant(Employee::class, $id);
        $branches = Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();
        return view('admin.staff.edit', compact('employee', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = $this->tenantId();
        $employee = $this->findForTenant(Employee::class, $id);

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', 'unique:employees,email,' . $id . ',id,tenant_id,' . $tenantId],
            'phone'     => ['required', 'string', 'max:20', 'unique:employees,phone,' . $id],
            'role'      => 'required|in:waiter,chef,cashier',
            'password'  => 'nullable|min:6|confirmed',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'role'      => $request->role,
            'branch_id' => $request->branch_id ?: null,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);
        return redirect()->route('admin.staff.index')->with('success', 'Staff updated successfully.');
    }

    public function destroy($id)
    {
        $this->findForTenant(Employee::class, $id)->delete();
        return redirect()->route('admin.staff.index')->with('success', 'Staff deleted.');
    }
}
