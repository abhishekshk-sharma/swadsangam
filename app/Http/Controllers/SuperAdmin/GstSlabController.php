<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{GstSlab, Tenant, Branch};
use Illuminate\Http\Request;

class GstSlabController extends Controller
{
    public function index(Request $request)
    {
        $slabs      = GstSlab::latest()->get();
        $tenants    = Tenant::with('gstSlab')->orderBy('name')->get();

        $selectedTenant = $request->filled('tenant_id') ? (int) $request->tenant_id : null;
        $selectedBranch = $request->filled('branch_id') ? (int) $request->branch_id : null;

        // Branches for selected tenant (for dropdown)
        $branches = $selectedTenant
            ? Branch::where('tenant_id', $selectedTenant)->orderBy('name')->get()
            : collect();

        // Branch GST details to display
        $branchDetails = collect();
        if ($selectedTenant) {
            $query = Branch::with('gstSlab')
                ->where('tenant_id', $selectedTenant);
            if ($selectedBranch) {
                $query->where('id', $selectedBranch);
            }
            $branchDetails = $query->orderBy('name')->get();
        }

        return view('superadmin.gst-slabs.index', compact(
            'slabs', 'tenants', 'branches', 'branchDetails',
            'selectedTenant', 'selectedBranch'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'cgst_rate'  => 'required|numeric|min:0|max:50',
            'sgst_rate'  => 'required|numeric|min:0|max:50',
            'is_active'  => 'boolean',
        ]);
        $data['total_rate'] = $data['cgst_rate'] + $data['sgst_rate'];
        $data['is_active']  = $request->boolean('is_active', true);
        GstSlab::create($data);
        return back()->with('success', 'GST slab created.');
    }

    public function update(Request $request, GstSlab $gstSlab)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'cgst_rate' => 'required|numeric|min:0|max:50',
            'sgst_rate' => 'required|numeric|min:0|max:50',
            'is_active' => 'boolean',
        ]);
        $data['total_rate'] = $data['cgst_rate'] + $data['sgst_rate'];
        $data['is_active']  = $request->boolean('is_active', true);
        $gstSlab->update($data);
        return back()->with('success', 'GST slab updated.');
    }

    public function destroy(GstSlab $gstSlab)
    {
        $gstSlab->delete();
        return back()->with('success', 'GST slab deleted.');
    }

    public function assignTenant(Request $request, Tenant $tenant)
    {
        $request->validate([
            'gst_slab_id' => 'nullable|exists:gst_slabs,id',
            'gst_mode'    => 'nullable|in:included,excluded',
        ]);
        $tenant->update([
            'gst_slab_id' => $request->gst_slab_id ?: null,
            'gst_mode'    => $request->gst_slab_id ? $request->gst_mode : null,
        ]);
        return back()->with('success', 'GST settings updated for ' . $tenant->name . '.');
    }

    public function assignBranch(Request $request, Branch $branch)
    {
        $request->validate([
            'gst_slab_id' => 'nullable|exists:gst_slabs,id',
            'gst_mode'    => 'nullable|in:included,excluded',
            'gst_number'  => 'nullable|string|max:20',
        ]);
        $branch->update([
            'gst_slab_id' => $request->gst_slab_id ?: null,
            'gst_mode'    => $request->gst_slab_id ? $request->gst_mode : null,
            'gst_number'  => $request->gst_slab_id ? strtoupper($request->gst_number) : null,
        ]);
        return back()->with('success', 'GST updated for branch ' . $branch->name . '.');
    }
}
