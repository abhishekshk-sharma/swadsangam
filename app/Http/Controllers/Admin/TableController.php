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

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->status === 'available') {
            $query->where('is_occupied', false);
        } elseif ($request->status === 'occupied') {
            $query->where('is_occupied', true);
        }

        $tables = $query->get()->sort(function($a, $b) {
            preg_match('/^(\D*)(\d*)(.*)$/', $a->table_number, $am);
            preg_match('/^(\D*)(\d*)(.*)$/', $b->table_number, $bm);
            $prefixCmp = strcmp($am[1], $bm[1]);
            if ($prefixCmp !== 0) return $prefixCmp;
            $numA = (int)($am[2] ?? 0);
            $numB = (int)($bm[2] ?? 0);
            if ($numA !== $numB) return $numA - $numB;
            return strcmp($am[3] ?? '', $bm[3] ?? '');
        })->values();
        $categories     = TableCategory::get();
        $branches       = \App\Models\Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();
        $selectedBranch = $request->branch_id;
        return view('admin.tables.index', compact('tables', 'categories', 'branches', 'selectedBranch'));
    }

    public function create(Request $request)
    {
        $categories = TableCategory::get();
        $branches   = \App\Models\Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();
        $branchId   = $request->branch_id ?? session('admin_branch_id');
        return view('admin.tables.create', compact('categories', 'branches', 'branchId'));
    }

    public function store(Request $request)
    {
        $branchId = $request->branch_id ?: null;
        if ($branchId) session(['admin_branch_id' => $branchId]);

        $request->validate([
            'branch_id'    => 'nullable|exists:branches,id',
            'capacity'     => 'required|integer|min:1',
            'category_id'  => 'nullable|exists:table_categories,id',
            'count'        => 'nullable|integer|min:1|max:100',
            'table_number' => $request->filled('count') ? 'nullable' : [
                'required',
                \Illuminate\Validation\Rule::unique('restaurant_tables')
                    ->where('tenant_id', $this->tenantId()),
            ],
        ]);

        if ($request->filled('count') && $request->filled('category_id')) {
            $category = TableCategory::find($request->category_id);
            $prefix   = strtoupper(substr($category->name, 0, 1));

            $existing = RestaurantTable::where('tenant_id', $this->tenantId())
                ->where('table_number', 'LIKE', $prefix . '%')
                ->pluck('table_number')
                ->map(fn($n) => (int) substr($n, strlen($prefix)))
                ->filter(fn($n) => $n > 0)
                ->toArray();

            $created = 0; $counter = 1;
            while ($created < $request->count) {
                while (in_array($counter, $existing)) { $counter++; }
                RestaurantTable::create([
                    'tenant_id'    => $this->tenantId(),
                    'branch_id'    => $branchId,
                    'table_number' => $prefix . $counter,
                    'capacity'     => $request->capacity,
                    'qr_code'      => uniqid('table_'),
                    'category_id'  => $request->category_id,
                ]);
                $existing[] = $counter++; $created++;
            }
            return redirect()->route('admin.tables.index', $branchId ? ['branch_id' => $branchId] : [])
                ->with('success', $request->count . ' tables created successfully');
        }

        RestaurantTable::create([
            'tenant_id'    => $this->tenantId(),
            'branch_id'    => $branchId,
            'table_number' => $request->table_number,
            'capacity'     => $request->capacity,
            'qr_code'      => uniqid('table_'),
            'category_id'  => $request->category_id,
        ]);

        return redirect()->route('admin.tables.index', $branchId ? ['branch_id' => $branchId] : [])
            ->with('success', 'Table created successfully');
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
            'table_number' => [
                'required',
                \Illuminate\Validation\Rule::unique('restaurant_tables')
                    ->where('tenant_id', $this->tenantId())
                    ->ignore($id),
            ],
            'capacity'    => 'required|integer|min:1',
            'category_id' => 'nullable|exists:table_categories,id',
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
