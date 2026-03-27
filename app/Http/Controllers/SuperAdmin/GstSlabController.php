<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\GstSlab;
use App\Models\Tenant;
use Illuminate\Http\Request;

class GstSlabController extends Controller
{
    public function index()
    {
        $slabs   = GstSlab::latest()->get();
        $tenants = Tenant::with('gstSlab')->get();
        return view('superadmin.gst-slabs.index', compact('slabs', 'tenants'));
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
}
