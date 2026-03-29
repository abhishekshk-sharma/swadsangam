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
            ->with('gstSlab')
            ->get();
        $gstSlabs = GstSlab::where('is_active', true)->get();
        return view('admin.branches.index', compact('branches', 'gstSlabs'));
    }

    public function bulkGst(Request $request)
    {
        $request->validate([
            'branch_ids'  => 'required|array|min:1',
            'branch_ids.*'=> 'exists:branches,id',
            'action'      => 'required|in:apply,remove',
            'gst_slab_id' => 'required_if:action,apply|nullable|exists:gst_slabs,id',
            'gst_mode'    => 'required_if:action,apply|nullable|in:included,excluded',
            'gst_number'  => 'nullable|string|max:20',
        ]);

        $branches = Branch::where('tenant_id', $this->tenantId())
            ->whereIn('id', $request->branch_ids)
            ->get();

        if ($request->action === 'apply') {
            foreach ($branches as $branch) {
                $branch->update([
                    'gst_slab_id' => $request->gst_slab_id,
                    'gst_mode'    => $request->gst_mode,
                    'gst_number'  => $request->gst_number ? strtoupper($request->gst_number) : $branch->gst_number,
                ]);
            }
            $msg = 'GST applied to ' . $branches->count() . ' branch(es).';
        } else {
            foreach ($branches as $branch) {
                $branch->update(['gst_slab_id' => null, 'gst_mode' => null, 'gst_number' => null]);
            }
            $msg = 'GST removed from ' . $branches->count() . ' branch(es).';
        }

        return redirect()->route('admin.branches.index')->with('success', $msg);
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
            'gst_number'  => 'nullable|string|max:20',
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
            'gst_number'  => 'nullable|string|max:20',
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
