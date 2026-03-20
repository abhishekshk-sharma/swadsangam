<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends BaseAdminController
{
    public function index()
    {
        $categories     = MenuCategory::withCount('menuItems')->get();
        $branches       = \App\Models\Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();
        $selectedBranch = request('branch_id');
        if ($selectedBranch) {
            $categories = MenuCategory::withCount('menuItems')
                ->where(fn($q) => $q->whereNull('branch_id')->orWhere('branch_id', $selectedBranch))
                ->get();
        }
        return view('admin.menu-categories.index', compact('categories', 'branches', 'selectedBranch'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);

        MenuCategory::create([
            'tenant_id'   => $this->tenantId(),
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.menu-categories.index')->with('success', 'Category created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        $category = $this->findForTenant(MenuCategory::class, $id);
        abort_if(is_null($category->tenant_id), 403, 'Cannot edit global categories');
        $category->update($request->only('name', 'description'));
        return redirect()->route('admin.menu-categories.index')->with('success', 'Category updated successfully');
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
