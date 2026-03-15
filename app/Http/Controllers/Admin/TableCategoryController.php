<?php

namespace App\Http\Controllers\Admin;

use App\Models\TableCategory;
use Illuminate\Http\Request;

class TableCategoryController extends BaseAdminController
{
    public function index()
    {
        $categories = TableCategory::withCount('tables')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);

        TableCategory::create([
            'tenant_id'   => $this->tenantId(),
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        $category = $this->findForTenant(TableCategory::class, $id);
        abort_if(is_null($category->tenant_id), 403, 'Cannot edit global categories');
        $category->update($request->only('name', 'description'));
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully');
    }

    public function quickCreate(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category = TableCategory::create([
            'tenant_id'   => $this->tenantId(),
            'name'        => $request->name,
            'description' => $request->description,
        ]);
        return response()->json(['id' => $category->id, 'name' => $category->name]);
    }

    public function destroy($id)
    {
        $category = $this->findForTenant(TableCategory::class, $id);
        abort_if(is_null($category->tenant_id), 403, 'Cannot delete global categories');
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully');
    }
}
