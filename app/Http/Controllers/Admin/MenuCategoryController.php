<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index()
    {
        // Get global categories (tenant_id = null) and tenant-specific categories
        $categories = MenuCategory::accessibleByTenant()
            ->withCount('menuItems')
            ->get();
        
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
        
        // Only allow deletion of tenant-specific categories
        if ($category->tenant_id !== session('tenant_id')) {
            return redirect()->route('admin.menu-categories.index')->with('error', 'You can only delete your own categories');
        }
        
        $category->delete();
        return redirect()->route('admin.menu-categories.index')->with('success', 'Category deleted successfully');
    }
}
