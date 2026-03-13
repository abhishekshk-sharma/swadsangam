<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index()
    {
        $categories = MenuCategory::withoutGlobalScope('tenant')
            ->withCount(['menuItems' => function($query) {
                $query->withoutGlobalScope('tenant');
            }])
            ->latest()
            ->get();
        return view('superadmin.menu-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        MenuCategory::create(['name' => $request->name]);
        return back()->with('success', 'Category created');
    }

    public function destroy($id)
    {
        MenuCategory::findOrFail($id)->delete();
        return back()->with('success', 'Category deleted');
    }
}
