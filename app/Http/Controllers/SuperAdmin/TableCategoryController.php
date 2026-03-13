<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TableCategory;
use Illuminate\Http\Request;

class TableCategoryController extends Controller
{
    public function index()
    {
        $categories = TableCategory::withoutGlobalScope('tenant')
            ->withCount(['tables' => function($query) {
                $query->withoutGlobalScope('tenant');
            }])
            ->latest()
            ->get();
        return view('superadmin.table-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        TableCategory::create(['name' => $request->name]);
        return back()->with('success', 'Category created');
    }

    public function destroy($id)
    {
        TableCategory::findOrFail($id)->delete();
        return back()->with('success', 'Category deleted');
    }
}
