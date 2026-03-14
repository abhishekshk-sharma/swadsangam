<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index()
    {
        $categories = MenuCategory::withCount('menuItems')->get();
        return view('admin.menu-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        MenuCategory::create([
            'tenant_id' => session('tenant_id'),
            'name' => $request->name,
            'description' => $request->description,
        ]);
        
        return redirect()->route('admin.menu-categories.index')->with('success', 'Category created successfully');
    }

    public function destroy($id)
    {
        $category = MenuCategory::findOrFail($id);
        if (is_null($category->tenant_id)) {
            return redirect()->route('admin.menu-categories.index')->with('error', 'Cannot delete global categories');
        }
        $category->delete();
        return redirect()->route('admin.menu-categories.index')->with('success', 'Category deleted successfully');
    }
}
