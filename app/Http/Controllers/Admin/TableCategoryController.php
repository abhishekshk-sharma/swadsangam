<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableCategory;
use Illuminate\Http\Request;

class TableCategoryController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admin can manage categories');
        }
        
        $categories = TableCategory::withCount('tables')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admin can manage categories');
        }
        
        $request->validate([
            'name' => 'required|unique:table_categories',
            'description' => 'nullable'
        ]);

        TableCategory::create($request->all());
        return redirect()->route('admin.categories.index')->with('success', 'Category created');
    }

    public function destroy($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admin can manage categories');
        }
        
        TableCategory::findOrFail($id)->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted');
    }
}
