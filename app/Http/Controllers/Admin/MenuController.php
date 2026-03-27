<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuController extends BaseAdminController
{
    public function index(Request $request)
    {
        $query = MenuItem::with('menuCategory');

        if ($request->filled('branch_id')) {
            $catIds = MenuCategory::where(fn($q) =>
                $q->where('branch_id', $request->branch_id)->orWhereNull('branch_id')
            )->pluck('id');
            $query->whereIn('menu_category_id', $catIds);
        }
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
        $branches       = \App\Models\Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();
        $selectedBranch = $request->branch_id;
        return view('admin.menu.index', compact('menuItems', 'menuCategories', 'branches', 'selectedBranch'));
    }

    public function create(Request $request)
    {
        $menuCategories = MenuCategory::get();
        $branches       = \App\Models\Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();
        $branchId       = $request->branch_id ?? session('admin_branch_id');
        return view('admin.menu.create', compact('menuCategories', 'branches', 'branchId'));
    }

    public function store(Request $request)
    {
        $branchId = $request->branch_id ?: null;
        if ($branchId) session(['admin_branch_id' => $branchId]);

        $request->validate([
            'name'             => 'required|string|max:255',
            'price'            => 'required|numeric|min:0.01',
            'menu_category_id' => 'required|exists:menu_categories,id',
            'branch_id'        => 'nullable|exists:branches,id',
        ]);

        $cat = MenuCategory::findOrFail($request->menu_category_id);
        abort_if($cat->tenant_id !== null && $cat->tenant_id !== $this->tenantId(), 403);

        MenuItem::create([
            'tenant_id'        => $this->tenantId(),
            'branch_id'        => $branchId,
            'name'             => $request->name,
            'price'            => $request->price,
            'description'      => $request->description,
            'menu_category_id' => $request->menu_category_id,
            'is_available'     => $request->has('is_available') ? 1 : 0,
        ]);

        return redirect()->route('admin.menu.index', $branchId ? ['branch_id' => $branchId] : [])
            ->with('success', 'Menu item created successfully');
    }

    public function edit($id)
    {
        $menuItem       = $this->findForTenant(MenuItem::class, $id);
        $menuCategories = MenuCategory::get();
        return view('admin.menu.edit', compact('menuItem', 'menuCategories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'price'            => 'required|numeric|min:0.01',
            'menu_category_id' => 'required|exists:menu_categories,id',
        ]);

        $menuItem = $this->findForTenant(MenuItem::class, $id);

        $cat = MenuCategory::findOrFail($request->menu_category_id);
        abort_if($cat->tenant_id !== null && $cat->tenant_id !== $this->tenantId(), 403);

        $menuItem->update([
            'name'             => $request->name,
            'price'            => $request->price,
            'description'      => $request->description,
            'menu_category_id' => $request->menu_category_id,
            'is_available'     => $request->has('is_available') ? 1 : 0,
        ]);

        return redirect()->route('admin.menu.index')->with('success', 'Menu item updated successfully');
    }

    public function destroy($id)
    {
        $this->findForTenant(MenuItem::class, $id)->delete();
        return redirect()->route('admin.menu.index')->with('success', 'Menu item deleted successfully');
    }
}
