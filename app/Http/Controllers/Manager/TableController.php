<?php

namespace App\Http\Controllers\Manager;

use App\Models\RestaurantTable;
use App\Models\TableCategory;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends BaseManagerController
{
    public function index(Request $request)
    {
        $query = RestaurantTable::with('category');
        $this->scopeBranch($query);

        if ($request->category_id) $query->where('category_id', $request->category_id);
        if ($request->status === 'available') $query->where('is_occupied', false);
        elseif ($request->status === 'occupied') $query->where('is_occupied', true);

        $tables     = $query->get();
        $categories = TableCategory::get();
        return view('manager.tables.index', compact('tables', 'categories'));
    }

    public function create()
    {
        $categories = TableCategory::get();
        return view('manager.tables.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'capacity'     => 'required|integer|min:1',
            'category_id'  => 'nullable|exists:table_categories,id',
            'count'        => 'nullable|integer|min:1|max:100',
            'table_number' => $request->filled('count') ? 'nullable' : [
                'required',
                \Illuminate\Validation\Rule::unique('restaurant_tables')
                    ->where('tenant_id', $this->tenantId())
                    ->where('branch_id', $this->branchId()),
            ],
        ]);

        if ($request->filled('count') && $request->filled('category_id')) {
            $category = TableCategory::find($request->category_id);
            $prefix   = strtoupper(substr($category->name, 0, 1));

            $existing = RestaurantTable::where('tenant_id', $this->tenantId())
                ->where('branch_id', $this->branchId())
                ->where('table_number', 'LIKE', $prefix . '%')
                ->pluck('table_number')
                ->map(fn($n) => (int) substr($n, strlen($prefix)))
                ->filter(fn($n) => $n > 0)
                ->toArray();

            $created = 0; $counter = 1;
            while ($created < $request->count) {
                while (in_array($counter, $existing)) $counter++;
                RestaurantTable::create([
                    'tenant_id'    => $this->tenantId(),
                    'branch_id'    => $this->branchId(),
                    'table_number' => $prefix . $counter,
                    'capacity'     => $request->capacity,
                    'qr_code'      => uniqid('table_'),
                    'category_id'  => $request->category_id,
                ]);
                $existing[] = $counter++;
                $created++;
            }

            return redirect()->route('manager.tables.index')->with('success', $request->count . ' tables created.');
        }

        RestaurantTable::create([
            'tenant_id'    => $this->tenantId(),
            'branch_id'    => $this->branchId(),
            'table_number' => $request->table_number,
            'capacity'     => $request->capacity,
            'qr_code'      => uniqid('table_'),
            'category_id'  => $request->category_id,
        ]);

        return redirect()->route('manager.tables.index')->with('success', 'Table created successfully');
    }

    public function show($id)
    {
        $table       = $this->findForTenant(RestaurantTable::class, $id);
        $qrCodeImage = QrCode::size(300)->generate(url('/table/' . $table->qr_code));
        return view('manager.tables.show', compact('table', 'qrCodeImage'));
    }

    public function edit($id)
    {
        $table      = $this->findForTenant(RestaurantTable::class, $id);
        $categories = TableCategory::get();
        return view('manager.tables.edit', compact('table', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $table = $this->findForTenant(RestaurantTable::class, $id);
        $request->validate([
            'table_number' => [
                'required',
                \Illuminate\Validation\Rule::unique('restaurant_tables')
                    ->where('tenant_id', $this->tenantId())
                    ->where('branch_id', $this->branchId())
                    ->ignore($id),
            ],
            'capacity'    => 'required|integer|min:1',
            'category_id' => 'nullable|exists:table_categories,id',
        ]);
        $table->update($request->only('table_number', 'capacity', 'category_id'));
        return redirect()->route('manager.tables.index')->with('success', 'Table updated successfully');
    }

    public function destroy($id)
    {
        $this->findForTenant(RestaurantTable::class, $id)->delete();
        return redirect()->route('manager.tables.index')->with('success', 'Table deleted successfully');
    }
}
