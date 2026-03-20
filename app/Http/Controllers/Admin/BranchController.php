<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;

class BranchController extends BaseAdminController
{
    public function index()
    {
        $branches = Branch::where('tenant_id', $this->tenantId())
            ->withCount(['employees', 'tables', 'orders'])
            ->get();
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone'   => 'nullable|string|max:20',
        ]);

        Branch::create([
            'tenant_id' => $this->tenantId(),
            'name'      => $request->name,
            'address'   => $request->address,
            'phone'     => $request->phone,
            'is_active' => true,
        ]);

        return redirect()->route('admin.branches.index')->with('success', 'Branch created.');
    }

    public function show($id)
    {
        $branch    = Branch::where('tenant_id', $this->tenantId())->findOrFail($id);
        $employees = Employee::withoutGlobalScope('tenant')->where('branch_id', $id)->get();
        $stats = [
            'employees' => $employees->count(),
            'tables'    => \App\Models\RestaurantTable::withoutGlobalScopes()->where('branch_id', $id)->count(),
            'orders'    => \App\Models\Order::withoutGlobalScopes()->where('branch_id', $id)->count(),
            'revenue'   => \App\Models\Order::withoutGlobalScopes()->where('branch_id', $id)->where('status', 'paid')->sum('total_amount'),
        ];
        return view('admin.branches.show', compact('branch', 'employees', 'stats'));
    }

    public function edit($id)
    {
        $branch = Branch::where('tenant_id', $this->tenantId())->findOrFail($id);
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::where('tenant_id', $this->tenantId())->findOrFail($id);
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone'   => 'nullable|string|max:20',
        ]);
        $branch->update($request->only('name', 'address', 'phone', 'is_active'));
        return redirect()->route('admin.branches.index')->with('success', 'Branch updated.');
    }

    public function destroy($id)
    {
        $branch = Branch::where('tenant_id', $this->tenantId())->findOrFail($id);
        $branch->delete();
        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted.');
    }
}
