<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\GstSlab;
use Illuminate\Http\Request;

class BranchSettingsController extends Controller
{
    private function branch(): Branch
    {
        $branchId = auth()->guard('employee')->user()->branch_id;
        abort_if(!$branchId, 403, 'No branch assigned.');
        return Branch::findOrFail($branchId);
    }

    public function edit()
    {
        $branch   = $this->branch();
        $gstSlabs = GstSlab::where('is_active', true)->get();
        return view('manager.branch.settings', compact('branch', 'gstSlabs'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'upi_id'      => 'nullable|string|max:100',
            'gst_slab_id' => 'nullable|exists:gst_slabs,id',
            'gst_mode'    => 'nullable|in:included,excluded',
        ]);

        $this->branch()->update([
            'upi_id'      => $request->upi_id,
            'gst_slab_id' => $request->gst_slab_id ?: null,
            'gst_mode'    => $request->gst_slab_id ? $request->gst_mode : null,
        ]);

        return back()->with('success', 'Branch settings updated.');
    }
}
