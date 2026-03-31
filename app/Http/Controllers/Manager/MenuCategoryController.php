<?php

namespace App\Http\Controllers\Manager;

use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends BaseManagerController
{
    public function index()
    {
        $categories = MenuCategory::withCount('menuItems')
            ->where(function ($q) {
                $q->whereNull('branch_id')->orWhere('branch_id', $this->branchId());
            })->get();
        return view('manager.menu-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        MenuCategory::create([
            'tenant_id'   => $this->tenantId(),
            'branch_id'   => $this->branchId(),
            'name'        => $request->name,
            'description' => $request->description,
        ]);
        return redirect()->route('manager.menu-categories.index')->with('success', 'Category created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        $category = MenuCategory::where('id', $id)->where('tenant_id', $this->tenantId())->firstOrFail();
        abort_if($category->branch_id !== $this->branchId(), 403, 'Cannot edit categories created by admin.');
        $category->update($request->only('name', 'description'));
        return redirect()->route('manager.menu-categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        $category = MenuCategory::where('id', $id)->where('tenant_id', $this->tenantId())->firstOrFail();
        abort_if($category->branch_id !== $this->branchId(), 403, 'Cannot delete categories created by admin.');
        $category->delete();
        return redirect()->route('manager.menu-categories.index')->with('success', 'Category deleted successfully');
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
            'branch_id'   => $this->branchId(),
            'name'        => $request->name,
            'description' => $request->description,
        ]);
        return response()->json(['id' => $category->id, 'name' => $category->name]);
    }
}
