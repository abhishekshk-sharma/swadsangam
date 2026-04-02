<?php

namespace App\Http\Controllers\Manager;

use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class MenuController extends BaseManagerController
{
    public function index(Request $request)
    {
        $branchId = $this->branchId();

        // Bypass global branch scope — apply our own strict filter
        $query = MenuItem::withoutGlobalScope('branch')->with('menuCategory');

        // Always scope to manager's branch (strict — no null-branch bleed)
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('menu_category_id')) {
            $query->where('menu_category_id', $request->menu_category_id);
        }

        if ($request->status === 'available') {
            $query->where('is_available', true);
        } elseif ($request->status === 'unavailable') {
            $query->where('is_available', false);
        }

        $menuItems = $query->get();

        // Categories scoped to manager's branch + global
        $menuCategories = MenuCategory::withoutGlobalScope('branch')
            ->where(fn($q) => $branchId
                ? $q->where('branch_id', $branchId)->orWhereNull('branch_id')
                : $q
            )->get();

        return view('manager.menu.index', compact('menuItems', 'menuCategories'));
    }

    public function create()
    {
        $menuCategories = MenuCategory::get();
        return view('manager.menu.create', compact('menuCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'price'            => 'required|numeric|min:0.01',
            'menu_category_id' => 'required|exists:menu_categories,id',
        ]);

        $cat = MenuCategory::findOrFail($request->menu_category_id);
        abort_if($cat->tenant_id !== null && $cat->tenant_id !== $this->tenantId(), 403);

        MenuItem::create([
            'tenant_id'        => $this->tenantId(),
            'name'             => $request->name,
            'price'            => $request->price,
            'description'      => $request->description,
            'menu_category_id' => $request->menu_category_id,
            'is_available'     => $request->has('is_available') ? 1 : 0,
        ]);

        return redirect()->route('manager.menu.index')->with('success', 'Menu item created successfully');
    }

    public function edit($id)
    {
        $menuItem       = $this->findForTenant(MenuItem::class, $id);
        $menuCategories = MenuCategory::get();
        return view('manager.menu.edit', compact('menuItem', 'menuCategories'));
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

        return redirect()->route('manager.menu.index')->with('success', 'Menu item updated successfully');
    }

    public function destroy($id)
    {
        $this->findForTenant(MenuItem::class, $id)->delete();
        return redirect()->route('manager.menu.index')->with('success', 'Menu item deleted successfully');
    }
}
