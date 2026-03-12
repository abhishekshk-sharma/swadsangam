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
        
        $menuItems = $query->get();
        $menuCategories = MenuCategory::all();
        return view('admin.menu.index', compact('menuItems', 'menuCategories'));
    }

    public function create()
    {
        $menuCategories = MenuCategory::all();
        return view('admin.menu.create', compact('menuCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category' => 'required',
            'image' => 'nullable|image|max:5120',
            'menu_category_id' => 'nullable|exists:menu_categories,id'
        ]);

        $data = $request->except('image');
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/menu'), $imageName);
            $data['image'] = 'uploads/menu/' . $imageName;
        }

        MenuItem::create($data);
        return redirect()->route('admin.menu.index')->with('success', 'Menu item created successfully');
    }

    public function edit($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        $menuCategories = MenuCategory::all();
        return view('admin.menu.edit', compact('menuItem', 'menuCategories'));
    }

    public function update(Request $request, $id)
    {
        // Comprehensive debugging
        \Log::info('=== UPDATE REQUEST START ===');
        \Log::info('Request Method: ' . $request->method());
        \Log::info('Content Type: ' . $request->header('Content-Type'));
        \Log::info('Has File: ' . ($request->hasFile('image') ? 'YES' : 'NO'));
        \Log::info('All Files: ', $request->allFiles());
        \Log::info('All Input: ', $request->except('_token'));
        
        // Validate with detailed error logging
        try {
            $validated = $request->validate([
                'name' => 'required',
                'price' => 'required|numeric',
                'category' => 'required',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'menu_category_id' => 'nullable|exists:menu_categories,id'
            ]);
            \Log::info('Validation passed');
        } catch (\Exception $e) {
            \Log::error('Validation failed: ' . $e->getMessage());
            return back()->withErrors(['image' => 'Validation failed: ' . $e->getMessage()])->withInput();
        }
        
        $menuItem = MenuItem::findOrFail($id);
        $data = $request->except(['image', '_token', '_method']);
        $data['is_available'] = $request->has('is_available') ? 1 : 0;
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            \Log::info('Image Details: ', [
                'name' => $image->getClientOriginalName(),
                'size' => $image->getSize(),
                'mime' => $image->getMimeType(),
                'error' => $image->getError(),
                'valid' => $image->isValid()
            ]);
            
            if (!$image->isValid()) {
                \Log::error('Image file is not valid!');
                return back()->withErrors(['image' => 'Uploaded file is not valid'])->withInput();
            }
            
            if ($menuItem->image && file_exists(public_path($menuItem->image))) {
                unlink(public_path($menuItem->image));
            }
            
            $imageName = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('uploads/menu');
            \Log::info('Moving to: ' . $destinationPath . '\\' . $imageName);
            
            try {
                $image->move($destinationPath, $imageName);
                $data['image'] = 'uploads/menu/' . $imageName;
                \Log::info('Image saved successfully: ' . $data['image']);
            } catch (\Exception $e) {
                \Log::error('Failed to move image: ' . $e->getMessage());
                return back()->withErrors(['image' => 'Failed to save image: ' . $e->getMessage()])->withInput();
            }
        } else {
            \Log::warning('NO IMAGE FILE IN REQUEST');
        }

        $menuItem->update($data);
        \Log::info('=== UPDATE REQUEST END ===');
        
        return redirect()->route('admin.menu.index')->with('success', 'Menu item updated successfully');
    }

    public function destroy($id)
    {
        MenuItem::findOrFail($id)->delete();
        return redirect()->route('admin.menu.index')->with('success', 'Menu item deleted successfully');
    }
}
