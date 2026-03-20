<?php

namespace App\Http\Controllers\Manager;

use App\Models\TableCategory;
use Illuminate\Http\Request;

class TableCategoryController extends BaseManagerController
{
    public function index()
    {
        $categories = TableCategory::withCount('tables')
            ->where(function ($q) {
                $q->whereNull('branch_id')->orWhere('branch_id', $this->branchId());
            })->get();
        return view('manager.table-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        TableCategory::create([
            'tenant_id'   => $this->tenantId(),
            'branch_id'   => $this->branchId(),
            'name'        => $request->name,
            'description' => $request->description,
        ]);
        return redirect()->route('manager.table-categories.index')->with('success', 'Category created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        $category = TableCategory::where('id', $id)->where('tenant_id', $this->tenantId())->firstOrFail();
        abort_if($category->branch_id !== $this->branchId(), 403, 'Cannot edit categories created by admin.');
        $category->update($request->only('name', 'description'));
        return redirect()->route('manager.table-categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        $category = TableCategory::where('id', $id)->where('tenant_id', $this->tenantId())->firstOrFail();
        abort_if($category->branch_id !== $this->branchId(), 403, 'Cannot delete categories created by admin.');
        $category->delete();
        return redirect()->route('manager.table-categories.index')->with('success', 'Category deleted successfully');
    }

    public function quickCreate(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category = TableCategory::create([
            'tenant_id'   => $this->tenantId(),
            'branch_id'   => $this->branchId(),
            'name'        => $request->name,
            'description' => $request->description,
        ]);
        return response()->json(['id' => $category->id, 'name' => $category->name]);
    }
}
