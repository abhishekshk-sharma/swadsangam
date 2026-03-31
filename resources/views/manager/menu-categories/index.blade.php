@extends('layouts.manager')
@section('title', 'Menu Categories')
@section('content')
@php $authUser = auth()->guard('employee')->user(); @endphp

<div class="content-card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-tags"></i> Menu Categories</div>
        <a href="{{ route('manager.menu.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Menu</a>
    </div>
    <div class="card-body">

        {{-- Add Form --}}
        <div class="content-card mb-4">
            <div class="card-header" style="background:var(--blue-600);">
                <div class="card-title" style="color:white;"><i class="fas fa-plus-circle"></i> Add New Category</div>
            </div>
            <div class="card-body">
                <form action="{{ route('manager.menu-categories.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Pizza, Beverages, Starters" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" name="description" class="form-control" placeholder="Brief description">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i> Add</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- List --}}
        <div class="content-card">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
                <div class="card-title"><i class="fas fa-list"></i> All Categories</div>
                <button onclick="saveOrder()" id="saveOrderBtn" style="display:none;background:#16a34a;color:#fff;border:none;padding:7px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                    <i class="fas fa-save me-1"></i>Save Order
                </button>
            </div>
            <div class="card-body p-0" style="overflow-x:auto;">
                <table class="table" style="min-width:580px;">
                    <thead>
                        <tr>
                            <th style="width:80px;">Order</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Items</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sortableBody">
                        @forelse($categories as $cat)
                        <tr data-id="{{ $cat->id }}">
                            <td>
                                <div style="display:flex;gap:4px;align-items:center;">
                                    <span style="font-size:12px;color:#9ca3af;font-weight:600;min-width:24px;">{{ $loop->iteration }}</span>
                                    <div style="display:flex;flex-direction:column;gap:2px;">
                                        <button type="button" onclick="moveRow(this,-1)" style="background:#f3f4f6;border:1px solid #d1d5db;border-radius:4px;padding:1px 6px;cursor:pointer;font-size:11px;line-height:1.4;">▲</button>
                                        <button type="button" onclick="moveRow(this,1)" style="background:#f3f4f6;border:1px solid #d1d5db;border-radius:4px;padding:1px 6px;cursor:pointer;font-size:11px;line-height:1.4;">▼</button>
                                    </div>
                                </div>
                            </td>
                            <td><strong><i class="fas fa-utensils me-2" style="color:var(--blue-500);"></i>{{ $cat->name }}</strong></td>
                            <td style="color:var(--gray-500);">{{ $cat->description ?? '-' }}</td>
                            <td>
                                @if($cat->branch_id)
                                    <span class="badge badge-info">Branch</span>
                                @elseif($cat->tenant_id)
                                    <span class="badge badge-neutral">Custom</span>
                                @else
                                    <span class="badge badge-neutral">Global</span>
                                @endif
                            </td>
                            <td><span class="badge badge-info">{{ $cat->menu_items_count }}</span></td>
                            <td style="text-align:center;">
                                @if($cat->branch_id === $authUser->branch_id)
                                    <button onclick="toggleEdit({{ $cat->id }})" class="btn btn-secondary btn-sm"><i class="fas fa-edit me-1"></i>Edit</button>
                                    <form action="{{ route('manager.menu-categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Delete this category?')" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background:var(--error-light);color:var(--error);border:1px solid var(--error);"><i class="fas fa-trash me-1"></i>Delete</button>
                                    </form>
                                @else
                                    <span style="color:var(--gray-400);font-size:13px;font-style:italic;"><i class="fas fa-lock me-1"></i>Protected</span>
                                @endif
                            </td>
                        </tr>
                        @if($cat->branch_id === $authUser->branch_id)
                        <tr id="editRow{{ $cat->id }}" style="display:none;background:var(--blue-50);">
                            <td colspan="6" style="padding:12px 16px;">
                                <form action="{{ route('manager.menu-categories.update', $cat->id) }}" method="POST" class="d-flex gap-2 align-items-end flex-wrap">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="form-label mb-1" style="font-size:12px;">Name</label>
                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $cat->name }}" required style="min-width:180px;">
                                    </div>
                                    <div>
                                        <label class="form-label mb-1" style="font-size:12px;">Description</label>
                                        <input type="text" name="description" class="form-control form-control-sm" value="{{ $cat->description }}" style="min-width:220px;">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Save</button>
                                    <button type="button" onclick="toggleEdit({{ $cat->id }})" class="btn btn-secondary btn-sm">Cancel</button>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:48px;color:var(--gray-400);">
                                <i class="fas fa-inbox" style="font-size:3rem;display:block;margin-bottom:12px;opacity:.5;"></i>
                                No categories yet. Create one above.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function toggleEdit(id) {
    const row = document.getElementById('editRow' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
function moveRow(btn, dir) {
    const row   = btn.closest('tr[data-id]');
    const tbody = document.getElementById('sortableBody');
    const rows  = Array.from(tbody.querySelectorAll('tr[data-id]'));
    const idx   = rows.indexOf(row);
    const target = rows[idx + dir];
    if (!target) return;
    if (dir === -1) tbody.insertBefore(row, target);
    else tbody.insertBefore(target, row);
    updateNumbers();
    document.getElementById('saveOrderBtn').style.display = 'inline-block';
}
function updateNumbers() {
    document.querySelectorAll('#sortableBody tr[data-id]').forEach(function(row, i) {
        const num = row.querySelector('span');
        if (num) num.textContent = i + 1;
    });
}
function saveOrder() {
    const ids = Array.from(document.querySelectorAll('#sortableBody tr[data-id]')).map(r => r.dataset.id);
    const btn = document.getElementById('saveOrderBtn');
    btn.disabled = true;
    fetch('{{ route('manager.menu-categories.reorder') }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({ids})
    }).then(r => r.json()).then(res => {
        if (res.success) {
            btn.textContent = '✓ Saved!';
            setTimeout(() => { btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Order'; btn.style.display = 'none'; btn.disabled = false; }, 1500);
        }
    });
}
</script>
@endpush
@endsection
