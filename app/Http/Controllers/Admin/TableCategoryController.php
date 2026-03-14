<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableCategory;
use Illuminate\Http\Request;

class TableCategoryController extends Controller
{
    public function index()
    {
        $categories = TableCategory::withCount('tables')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        TableCategory::create([
            'tenant_id' => session('tenant_id'),
            'name' => $request->name,
            'description' => $request->description,
        ]);
        
        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully');
    }

    public function destroy($id)
    {
        $category = TableCategory::findOrFail($id);
        if (is_null($category->tenant_id)) {
            return redirect()->route('admin.categories.index')->with('error', 'Cannot delete global categories');
        }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully');
    }
}
