@extends('layouts.admin')

@section('title', 'Menu Categories')

@section('content')
<div class="content-card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center" style="width: 100%">
            <h1 class="card-title mb-0"><i class="fas fa-tags me-2"></i>Menu Categories</h1>
            <a href="{{ route('admin.menu.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Menu
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        @if(isset($branches) && $branches->count() > 0)
        <form method="GET" action="{{ route('admin.menu-categories.index') }}" style="margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <label style="font-size:13px;font-weight:600;color:var(--gray-600);white-space:nowrap;"><i class="fas fa-store me-1"></i>Branch:</label>
                <select name="branch_id" onchange="this.form.submit()" style="padding:7px 12px;border:1px solid var(--gray-300);border-radius:8px;font-size:13px;font-weight:500;color:var(--gray-700);background:var(--white);min-width:180px;cursor:pointer;">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @if($selectedBranch)
                    <a href="{{ route('admin.menu-categories.index') }}" style="font-size:12px;color:var(--gray-500);text-decoration:none;"><i class="fas fa-times me-1"></i>Clear</a>
                @endif
            </div>
        </form>
        @endif

        <div class="content-card mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);">
                <h2 class="card-title mb-0" style="color: white; font-size: 16px;">
                    <i class="fas fa-plus-circle me-2"></i>Add New Category
                </h2>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.menu-categories.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Pizza, Gujarati Thali, Beverages" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" name="description" class="form-control" placeholder="Brief description">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn-primary w-100">
                                <i class="fas fa-plus me-2"></i>Add
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>All Categories
                </h2>
                <button onclick="saveOrder()" id="saveOrderBtn" style="display:none;background:#16a34a;color:#fff;border:none;padding:7px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                    <i class="fas fa-save me-1"></i>Save Order
                </button>
            </div>
            <div class="card-body p-0" style="overflow-x:auto;">
                <table class="table" style="min-width:600px;">
                    <thead>
                        <tr style="background:#eff6ff;">
                            <th style="width:80px;color:#1d4ed8;">Order</th>
                            <th style="min-width:140px;color:#1d4ed8;">Name</th>
                            <th style="min-width:180px;color:#1d4ed8;">Description</th>
                            <th style="min-width:100px;color:#1d4ed8;">Type</th>
                            <th style="min-width:110px;color:#1d4ed8;">Items Count</th>
                            <th style="text-align:center;min-width:160px;color:#1d4ed8;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sortableBody">
                        @forelse($categories as $category)
                        <tr data-id="{{ $category->id }}">
                            <td>
                                <div style="display:flex;gap:4px;align-items:center;">
                                    <span style="font-size:12px;color:#9ca3af;font-weight:600;min-width:24px;">{{ $loop->iteration }}</span>
                                    <div style="display:flex;flex-direction:column;gap:2px;">
                                        <button type="button" onclick="moveRow(this,-1)" style="background:#f3f4f6;border:1px solid #d1d5db;border-radius:4px;padding:1px 6px;cursor:pointer;font-size:11px;line-height:1.4;">▲</button>
                                        <button type="button" onclick="moveRow(this,1)" style="background:#f3f4f6;border:1px solid #d1d5db;border-radius:4px;padding:1px 6px;cursor:pointer;font-size:11px;line-height:1.4;">▼</button>
                                    </div>
                                </div>
                            </td>
                                <div style="font-weight: 600; color: #232f3e;">
                                    <i class="fas fa-utensils me-2" style="color: #1d4ed8;"></i>{{ $category->name }}
                                </div>
                            </td>
                            <td style="color: #666;">
                                {{ $category->description ?? '-' }}
                            </td>
                            <td>
                                @if($category->tenant_id)
                                    <span class="badge-custom" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                        <i class="fas fa-user me-1"></i>Custom
                                    </span>
                                @else
                                    <span class="badge-custom" style="background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db;">
                                        <i class="fas fa-globe me-1"></i>Global
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-custom" style="background: #dbeafe; color: #1d4ed8;">
                                    {{ $category->menu_items_count }} {{ Str::plural('item', $category->menu_items_count) }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if($category->tenant_id)
                                    <button onclick="toggleEdit({{ $category->id }})" class="btn-secondary" style="padding: 6px 12px; font-size: 13px;">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                    <form action="{{ route('admin.menu-categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Delete this category?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" style="padding: 6px 12px; font-size: 13px;">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                @else
                                    <span style="color: #9ca3af; font-size: 13px; font-style: italic;">
                                        <i class="fas fa-lock me-1"></i>Protected
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @if($category->tenant_id)
                        <tr id="editRow{{ $category->id }}" style="display:none; background:#eff6ff;">
                            <td colspan="6" style="padding: 12px 16px;">
                                <form action="{{ route('admin.menu-categories.update', $category->id) }}" method="POST" class="d-flex gap-2 align-items-end flex-wrap">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="form-label mb-1" style="font-size:12px;">Name</label>
                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $category->name }}" required style="min-width:180px;">
                                    </div>
                                    <div>
                                        <label class="form-label mb-1" style="font-size:12px;">Description</label>
                                        <input type="text" name="description" class="form-control form-control-sm" value="{{ $category->description }}" style="min-width:220px;">
                                    </div>
                                    <button type="submit" class="btn-primary" style="padding:6px 16px; font-size:13px;"><i class="fas fa-save me-1"></i>Save</button>
                                    <button type="button" onclick="toggleEdit({{ $category->id }})" class="btn-secondary" style="padding:6px 14px; font-size:13px;">Cancel</button>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px; color: #9ca3af;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                                <div style="font-size: 16px; font-weight: 600;">No categories yet</div>
                                <div style="font-size: 14px; margin-top: 8px;">Create your first category using the form above</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
function toggleEdit(id) {
    const row = document.getElementById('editRow' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
function moveRow(btn, dir) {
    const row  = btn.closest('tr');
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
    fetch('{{ route('admin.menu-categories.reorder') }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({ids})
    }).then(r => r.json()).then(res => {
        if (res.success) {
            document.getElementById('saveOrderBtn').style.display = 'none';
            const btn = document.getElementById('saveOrderBtn');
            btn.textContent = '✓ Saved!';
            setTimeout(() => { btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Order'; btn.style.display='none'; }, 1500);
        }
    });
}
</script>
