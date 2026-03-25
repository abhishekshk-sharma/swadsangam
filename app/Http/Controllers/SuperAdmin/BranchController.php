<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Branch, Tenant};
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $tenants  = Tenant::orderBy('name')->get();
        $query    = Branch::with('tenant')->withCount(['employees', 'tables', 'orders']);

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $branches        = $query->orderBy('tenant_id')->orderBy('name')->get();
        $selectedTenant  = $request->tenant_id;

        return view('superadmin.branches.index', compact('branches', 'tenants', 'selectedTenant'));
    }

    public function create()
    {
        $tenants = Tenant::where('status', 'active')->orderBy('name')->get();
        return view('superadmin.branches.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name'      => 'required|string|max:255',
            'address'   => 'nullable|string|max:500',
            'phone'     => 'nullable|string|max:20',
        ]);

        Branch::create([
            'tenant_id' => $request->tenant_id,
            'name'      => $request->name,
            'address'   => $request->address,
            'phone'     => $request->phone,
            'is_active' => true,
        ]);

        return redirect('/superadmin/branches')->with('success', 'Branch created.');
    }

    public function edit($id)
    {
        $branch  = Branch::findOrFail($id);
        $tenants = Tenant::where('status', 'active')->orderBy('name')->get();
        return view('superadmin.branches.edit', compact('branch', 'tenants'));
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone'   => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);
        $branch->update($request->only('name', 'address', 'phone', 'is_active'));
        return redirect('/superadmin/branches')->with('success', 'Branch updated.');
    }

    public function destroy($id)
    {
        Branch::findOrFail($id)->delete();
        return redirect('/superadmin/branches')->with('success', 'Branch deleted.');
    }
}
