<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admin can manage categories');
        }
        
        $categories = MenuCategory::withCount('menuItems')->get();
        return view('admin.menu-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admin can manage categories');
        }
        
        $request->validate([
            'name' => 'required|unique:menu_categories',
            'description' => 'nullable'
        ]);

        MenuCategory::create($request->all());
        return redirect()->route('admin.menu-categories.index')->with('success', 'Category created');
    }

    public function destroy($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only super admin can manage categories');
        }
        
        MenuCategory::findOrFail($id)->delete();
        return redirect()->route('admin.menu-categories.index')->with('success', 'Category deleted');
    }
}
