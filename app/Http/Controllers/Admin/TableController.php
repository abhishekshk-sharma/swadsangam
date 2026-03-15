<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Models\TableCategory;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends BaseAdminController
{
    public function index(Request $request)
    {
        $query = RestaurantTable::with('category');

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status === 'available') {
            $query->where('is_occupied', false);
        } elseif ($request->status === 'occupied') {
            $query->where('is_occupied', true);
        }

        $tables = $query->get();
        $categories = TableCategory::get();
        return view('admin.tables.index', compact('tables', 'categories'));
    }

    public function create()
    {
        $categories = TableCategory::get();
        return view('admin.tables.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'table_number' => 'required|unique:restaurant_tables',
            'capacity' => 'required|integer|min:1',
            'category_id' => 'nullable|exists:table_categories,id'
        ]);
        
        $qrCode = uniqid('table_');
        RestaurantTable::create([
            'tenant_id'    => $this->tenantId(),
            'table_number' => $request->table_number,
            'capacity'     => $request->capacity,
            'qr_code'      => $qrCode,
            'category_id'  => $request->category_id,
        ]);

        return redirect()->route('admin.tables.index')->with('success', 'Table created successfully');
    }

    public function edit($id)
    {
        $table = $this->findForTenant(RestaurantTable::class, $id);
        $categories = TableCategory::get();
        return view('admin.tables.edit', compact('table', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $table = $this->findForTenant(RestaurantTable::class, $id);
        $request->validate([
            'table_number' => 'required|unique:restaurant_tables,table_number,' . $id,
            'capacity'     => 'required|integer|min:1',
            'category_id'  => 'nullable|exists:table_categories,id',
        ]);
        $table->update($request->only('table_number', 'capacity', 'category_id'));
        return redirect()->route('admin.tables.index')->with('success', 'Table updated successfully');
    }

    public function show($id)
    {
        $table = $this->findForTenant(RestaurantTable::class, $id);
        $qrCodeImage = QrCode::size(300)->generate(url('/table/' . $table->qr_code));
        return view('admin.tables.show', compact('table', 'qrCodeImage'));
    }

    public function destroy($id)
    {
        $this->findForTenant(RestaurantTable::class, $id)->delete();
        return redirect()->route('admin.tables.index')->with('success', 'Table deleted successfully');
    }
}
