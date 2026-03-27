<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\GstSlab;
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
        $gstSlabs = GstSlab::where('is_active', true)->get();
        return view('admin.branches.create', compact('gstSlabs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:20',
            'upi_id'      => 'nullable|string|max:100',
            'gst_slab_id' => 'nullable|exists:gst_slabs,id',
            'gst_mode'    => 'nullable|in:included,excluded',
            'gst_number'  => 'nullable|string|max:20|unique:branches,gst_number,' . null . ',id,tenant_id,' . $this->tenantId(),
        ]);

        Branch::create([
            'tenant_id'   => $this->tenantId(),
            'name'        => $request->name,
            'address'     => $request->address,
            'phone'       => $request->phone,
            'upi_id'      => $request->upi_id,
            'gst_slab_id' => $request->gst_slab_id ?: null,
            'gst_mode'    => $request->gst_slab_id ? $request->gst_mode : null,
            'gst_number'  => $request->gst_slab_id ? strtoupper($request->gst_number) : null,
            'is_active'   => true,
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
        $branch   = Branch::where('tenant_id', $this->tenantId())->findOrFail($id);
        $gstSlabs = GstSlab::where('is_active', true)->get();
        return view('admin.branches.edit', compact('branch', 'gstSlabs'));
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::where('tenant_id', $this->tenantId())->findOrFail($id);
        $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:20',
            'upi_id'      => 'nullable|string|max:100',
            'gst_slab_id' => 'nullable|exists:gst_slabs,id',
            'gst_mode'    => 'nullable|in:included,excluded',
            'gst_number'  => 'nullable|string|max:20|unique:branches,gst_number,' . $branch->id . ',id,tenant_id,' . $this->tenantId(),
        ]);
        $branch->update([
            'name'        => $request->name,
            'address'     => $request->address,
            'phone'       => $request->phone,
            'upi_id'      => $request->upi_id,
            'is_active'   => $request->is_active,
            'gst_slab_id' => $request->gst_slab_id ?: null,
            'gst_mode'    => $request->gst_slab_id ? $request->gst_mode : null,
            'gst_number'  => $request->gst_slab_id ? strtoupper($request->gst_number) : null,
        ]);
        return redirect()->route('admin.branches.index')->with('success', 'Branch updated.');
    }

    public function destroy($id)
    {
        $branch = Branch::where('tenant_id', $this->tenantId())->findOrFail($id);
        $branch->delete();
        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted.');
    }
}
