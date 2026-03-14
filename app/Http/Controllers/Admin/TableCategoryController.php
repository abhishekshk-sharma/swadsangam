<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableCategory;
use Illuminate\Http\Request;

class TableCategoryController extends Controller
{
    public function index()
    {
        // Get global categories (tenant_id = null) and tenant-specific categories
        $categories = TableCategory::accessibleByTenant()
            ->withCount('tables')
            ->get();
        
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
        
        // Only allow deletion of tenant-specific categories
        if ($category->tenant_id !== session('tenant_id')) {
            return redirect()->route('admin.categories.index')->with('error', 'You can only delete your own categories');
        }
        
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully');
    }
}
