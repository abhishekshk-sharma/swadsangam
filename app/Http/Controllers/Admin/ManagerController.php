<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagerController extends BaseAdminController
{
    private function clean(mixed $val): string
    {
        $v = trim((string) $val);
        return ($v === '' || $v === 'null') ? '' : $v;
    }

    private function buildManagerQuery(Request $request): array
    {
        $search  = $this->clean($request->input('search'));
        $phone   = $this->clean($request->input('phone'));
        $status  = $this->clean($request->input('status'));
        $perPage = in_array($request->input('per_page'), ['10','25','50','100']) ? (int)$request->input('per_page') : 10;

        $managers = Employee::with('branch')
            ->where('role', 'manager')
            ->when($search !== '', fn($q) => $q->where(fn($q2) =>
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
            ))
            ->when($phone  !== '', fn($q) => $q->where('phone', 'like', "%{$phone}%"))
            ->when($status !== '', fn($q) => $q->where('is_active', (int) $status))
            ->orderBy('name')
            ->paginate($perPage);

        $assignedByBranch = Employee::where('role', '!=', 'manager')
            ->whereNotNull('branch_id')
            ->get(['id', 'name', 'role', 'branch_id'])
            ->groupBy('branch_id');

        return compact('managers', 'assignedByBranch', 'search', 'phone', 'status', 'perPage');
    }

    public function index(Request $request)
    {
        return view('admin.managers.index', $this->buildManagerQuery($request));
    }

    public function ajaxIndex(Request $request)
    {
        $data = $this->buildManagerQuery($request);
        return response()->json([
            'tbody' => view('admin.managers._tbody', $data)->render(),
            'pagination' => view('admin.managers._pagination', $data)->render(),
            'total' => $data['managers']->total(),
        ]);
    }

    public function create()
    {
        $branches = Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();
        return view('admin.managers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $tenantId = $this->tenantId();

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', 'unique:employees,email,NULL,id,tenant_id,' . $tenantId],
            'password'  => 'required|min:6|confirmed',
            'branch_id' => 'nullable|exists:branches,id',
            'phone'     => 'nullable|string|max:20',
        ]);

        Employee::create([
            'tenant_id' => $tenantId,
            'branch_id' => $request->branch_id ?: null,
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'role'      => 'manager',
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.managers.index')->with('success', 'Manager added successfully.');
    }

    public function edit($id)
    {
        $manager  = $this->findForTenant(Employee::class, $id);
        $branches = Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();

        $assignedEmployees   = collect();
        $unassignedEmployees = collect();

        if ($manager->branch_id) {
            $assignedEmployees   = Employee::where('role', '!=', 'manager')->where('branch_id', $manager->branch_id)->get();
            $unassignedEmployees = Employee::where('role', '!=', 'manager')->whereNull('branch_id')->get();
        }

        return view('admin.managers.edit', compact('manager', 'branches', 'assignedEmployees', 'unassignedEmployees'));
    }

    public function update(Request $request, $id)
    {
        $tenantId = $this->tenantId();
        $manager  = $this->findForTenant(Employee::class, $id);

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', 'unique:employees,email,' . $id . ',id,tenant_id,' . $tenantId],
            'password'  => 'nullable|min:6|confirmed',
            'branch_id' => 'nullable|exists:branches,id',
            'phone'     => 'nullable|string|max:20',
        ]);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'branch_id' => $request->branch_id ?: null,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $manager->update($data);

        // Sync assigned staff
        if ($manager->branch_id) {
            Employee::where('role', '!=', 'manager')->where('branch_id', $manager->branch_id)->update(['branch_id' => null]);
            $ids = array_filter((array) $request->input('assigned_ids', []));
            if (!empty($ids)) {
                Employee::whereIn('id', $ids)->update(['branch_id' => $manager->branch_id]);
            }
        }

        return redirect()->route('admin.managers.index')->with('success', 'Manager updated successfully.');
    }

    public function destroy($id)
    {
        $this->findForTenant(Employee::class, $id)->delete();
        return redirect()->route('admin.managers.index')->with('success', 'Manager deleted.');
    }
}
