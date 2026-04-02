<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends BaseAdminController
{
    public function index()
    {
        $branches       = \App\Models\Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();
        $selectedBranch = request('branch_id');

        $query = MenuCategory::withCount(['menuItems' => function ($q) use ($selectedBranch) {
            if ($selectedBranch) {
                $q->where(fn($q2) => $q2->whereNull('branch_id')->orWhere('branch_id', $selectedBranch));
            }
        }]);

        if ($selectedBranch) {
            $query->where(fn($q) => $q->whereNull('branch_id')->orWhere('branch_id', $selectedBranch));
        }

        $categories = $query->get();

        return view('admin.menu-categories.index', compact('categories', 'branches', 'selectedBranch'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);

        MenuCategory::create([
            'tenant_id'   => $this->tenantId(),
            'branch_id'   => $request->branch_id ?: null,
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.menu-categories.index', $request->branch_id ? ['branch_id' => $request->branch_id] : [])
            ->with('success', 'Category created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        $category = $this->findForTenant(MenuCategory::class, $id);
        abort_if(is_null($category->tenant_id), 403, 'Cannot edit global categories');
        $category->update($request->only('name', 'description'));
        return redirect()->route('admin.menu-categories.index')->with('success', 'Category updated successfully');
    }

    public function reorder(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        foreach ($request->ids as $order => $id) {
            MenuCategory::where('id', $id)->where('tenant_id', $this->tenantId())->update(['sort_order' => $order + 1]);
        }
        return response()->json(['success' => true]);
    }

    public function quickCreate(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category = MenuCategory::create([
            'tenant_id'   => $this->tenantId(),
            'name'        => $request->name,
            'description' => $request->description,
        ]);
        return response()->json(['id' => $category->id, 'name' => $category->name]);
    }

    public function destroy($id)
    {
        $category = $this->findForTenant(MenuCategory::class, $id);
        abort_if(is_null($category->tenant_id), 403, 'Cannot delete global categories');
        $category->delete();
        return redirect()->route('admin.menu-categories.index')->with('success', 'Category deleted successfully');
    }
}
