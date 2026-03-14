<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::with('menuCategory');

        if ($request->menu_category_id) {
            $query->where('menu_category_id', $request->menu_category_id);
        }

        if ($request->status === 'available') {
            $query->where('is_available', true);
        } elseif ($request->status === 'unavailable') {
            $query->where('is_available', false);
        }

        $menuItems      = $query->get();
        $menuCategories = MenuCategory::get();
        return view('admin.menu.index', compact('menuItems', 'menuCategories'));
    }

    public function create()
    {
        $menuCategories = MenuCategory::get();
        return view('admin.menu.create', compact('menuCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required',
            'price'            => 'required|numeric',
            'category'         => 'required',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'menu_category_id' => 'nullable|exists:menu_categories,id',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/menu'), $imageName);
            $data['image'] = 'uploads/menu/' . $imageName;
        }

        MenuItem::create($data);
        return redirect()->route('admin.menu.index')->with('success', 'Menu item created successfully');
    }

    public function edit($id)
    {
        $menuItem       = MenuItem::findOrFail($id);
        $menuCategories = MenuCategory::get();
        return view('admin.menu.edit', compact('menuItem', 'menuCategories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'             => 'required',
            'price'            => 'required|numeric',
            'category'         => 'required',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'menu_category_id' => 'nullable|exists:menu_categories,id',
        ]);

        $menuItem = MenuItem::findOrFail($id);
        $data     = $request->except(['image', '_token', '_method']);
        $data['is_available'] = $request->has('is_available') ? 1 : 0;

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            if ($menuItem->image && file_exists(public_path($menuItem->image))) {
                unlink(public_path($menuItem->image));
            }

            $imageName     = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/menu'), $imageName);
            $data['image'] = 'uploads/menu/' . $imageName;
        }

        $menuItem->update($data);
        return redirect()->route('admin.menu.index')->with('success', 'Menu item updated successfully');
    }

    public function destroy($id)
    {
        MenuItem::findOrFail($id)->delete();
        return redirect()->route('admin.menu.index')->with('success', 'Menu item deleted successfully');
    }
}
