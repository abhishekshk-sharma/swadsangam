@extends('layouts.admin')
@section('title', 'Menu Categories')
@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <h1 class="section-title"><i class="fas fa-tags me-2"></i>Menu Categories</h1>
    <a href="{{ route('admin.menu.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Menu
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-4"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
@endif

{{-- Branch filter --}}
@if(isset($branches) && $branches->count() > 0)
<div class="content-card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" action="{{ route('admin.menu-categories.index') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <label style="font-size:13px;font-weight:600;color:var(--gray-600);white-space:nowrap;"><i class="fas fa-store me-1"></i>Branch:</label>
            <select name="branch_id" onchange="this.form.submit()" style="padding:7px 12px;border:1px solid var(--gray-300);border-radius:8px;font-size:13px;background:#fff;min-width:180px;">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
            @if($selectedBranch)
                <a href="{{ route('admin.menu-categories.index') }}" style="font-size:12px;color:var(--gray-500);text-decoration:none;"><i class="fas fa-times me-1"></i>Clear</a>
            @endif
        </form>
    </div>
</div>
@endif

{{-- Add Form --}}
<div class="content-card mb-4">
    <div class="card-header" style="background:linear-gradient(135deg,#1d4ed8,#1e40af);">
        <h2 class="card-title mb-0" style="color:#fff;font-size:15px;"><i class="fas fa-plus-circle me-2"></i>Add New Category</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.menu-categories.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Category Name <span style="color:var(--error)">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g., Pizza, Beverages, Starters" required>
                    @error('name')<div style="color:var(--error);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">Description <span style="color:var(--gray-400);font-weight:400;">(optional)</span></label>
                    <input type="text" name="description" class="form-control" placeholder="Brief description">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn-primary w-100"><i class="fas fa-plus me-1"></i>Add</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Categories Table --}}
<div class="content-card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h2 class="card-title mb-0"><i class="fas fa-list me-2"></i>All Categories <span style="font-size:13px;font-weight:400;color:var(--gray-500);">({{ $categories->count() }})</span></h2>
        <button onclick="saveOrder()" id="saveOrderBtn"
            style="display:none;background:#16a34a;color:#fff;border:none;padding:7px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
            <i class="fas fa-save me-1"></i>Save Order
        </button>
    </div>
    <div class="card-body p-0" style="overflow-x:auto;">
        <table class="table mb-0" style="min-width:620px;">
            <thead>
                <tr style="background:#eff6ff;">
                    <th style="width:90px;padding:12px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#1d4ed8;border-bottom:2px solid #dbeafe;">Order</th>
                    <th style="padding:12px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#1d4ed8;border-bottom:2px solid #dbeafe;">Name</th>
                    <th style="padding:12px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#1d4ed8;border-bottom:2px solid #dbeafe;">Description</th>
                    <th style="padding:12px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#1d4ed8;border-bottom:2px solid #dbeafe;">Type</th>
                    <th style="padding:12px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#1d4ed8;border-bottom:2px solid #dbeafe;">Items</th>
                    <th style="padding:12px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#1d4ed8;border-bottom:2px solid #dbeafe;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody id="sortableBody">
                @forelse($categories as $category)
                <tr data-id="{{ $category->id }}" style="border-bottom:1px solid #f3f4f6;transition:background .15s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    {{-- Order column --}}
                    <td style="padding:12px 16px;vertical-align:middle;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <select onchange="moveRowTo(this)" data-id="{{ $category->id }}"
                                style="padding:4px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;font-weight:600;color:#374151;background:#f9fafb;cursor:pointer;width:64px;">
                                @foreach($categories as $i => $cat)
                                    <option value="{{ $i + 1 }}" {{ $cat->id === $category->id ? 'selected' : '' }}>{{ $i + 1 }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    {{-- Name column --}}
                    <td style="padding:12px 16px;vertical-align:middle;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="width:32px;height:32px;background:#eff6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas fa-utensils" style="color:#1d4ed8;font-size:13px;"></i>
                            </span>
                            <span style="font-weight:600;font-size:14px;color:#111827;">{{ $category->name }}</span>
                        </div>
                    </td>
                    {{-- Description --}}
                    <td style="padding:12px 16px;vertical-align:middle;color:#6b7280;font-size:13px;">
                        {{ $category->description ?: '—' }}
                    </td>
                    {{-- Type --}}
                    <td style="padding:12px 16px;vertical-align:middle;">
                        @if($category->tenant_id)
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                                <i class="fas fa-user" style="font-size:10px;"></i>Custom
                            </span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#f3f4f6;color:#4b5563;border:1px solid #d1d5db;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                                <i class="fas fa-globe" style="font-size:10px;"></i>Global
                            </span>
                        @endif
                    </td>
                    {{-- Items count --}}
                    <td style="padding:12px 16px;vertical-align:middle;">
                        <span style="display:inline-flex;align-items:center;gap:4px;background:#dbeafe;color:#1d4ed8;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                            {{ $category->menu_items_count }} {{ Str::plural('item', $category->menu_items_count) }}
                        </span>
                    </td>
                    {{-- Actions --}}
                    <td style="padding:12px 16px;vertical-align:middle;text-align:center;">
                        @if($category->tenant_id)
                            <button onclick="toggleEdit({{ $category->id }})" class="btn-secondary" style="padding:5px 12px;font-size:12px;">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                            <form action="{{ route('admin.menu-categories.destroy', $category->id) }}" method="POST"
                                  onsubmit="return confirm('Delete \'{{ addslashes($category->name) }}\'?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger" style="padding:5px 12px;font-size:12px;">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </button>
                            </form>
                        @else
                            <span style="color:#9ca3af;font-size:12px;font-style:italic;"><i class="fas fa-lock me-1"></i>Protected</span>
                        @endif
                    </td>
                </tr>
                @if($category->tenant_id)
                <tr id="editRow{{ $category->id }}" style="display:none;background:#eff6ff;">
                    <td colspan="6" style="padding:14px 20px;">
                        <form action="{{ route('admin.menu-categories.update', $category->id) }}" method="POST"
                              style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                            @csrf @method('PUT')
                            <div>
                                <label class="form-label mb-1" style="font-size:12px;">Name</label>
                                <input type="text" name="name" class="form-control form-control-sm"
                                       value="{{ $category->name }}" required style="min-width:180px;">
                            </div>
                            <div>
                                <label class="form-label mb-1" style="font-size:12px;">Description</label>
                                <input type="text" name="description" class="form-control form-control-sm"
                                       value="{{ $category->description }}" style="min-width:220px;">
                            </div>
                            <button type="submit" class="btn-primary" style="padding:6px 16px;font-size:13px;">
                                <i class="fas fa-save me-1"></i>Save
                            </button>
                            <button type="button" onclick="toggleEdit({{ $category->id }})" class="btn-secondary" style="padding:6px 14px;font-size:13px;">
                                Cancel
                            </button>
                        </form>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:56px 20px;color:#9ca3af;">
                        <i class="fas fa-tags" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3;"></i>
                        <div style="font-size:15px;font-weight:600;margin-bottom:6px;">No categories yet</div>
                        <div style="font-size:13px;">Use the form above to create your first category.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleEdit(id) {
    const row = document.getElementById('editRow' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}

function moveRowTo(select) {
    const newPos = parseInt(select.value) - 1;
    const row = select.closest('tr[data-id]');
    const tbody = document.getElementById('sortableBody');
    const rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
    const currentPos = rows.indexOf(row);
    if (currentPos === newPos) return;

    // Move the data row (and its paired edit row if present)
    const editRow = row.nextElementSibling;
    const hasEditRow = editRow && editRow.id && editRow.id.startsWith('editRow');

    rows.splice(currentPos, 1);
    rows.splice(newPos, 0, row);

    // Re-insert all data rows (and their edit rows) in new order
    rows.forEach(r => {
        tbody.appendChild(r);
        const er = document.getElementById('editRow' + r.dataset.id);
        if (er) tbody.appendChild(er);
    });

    updateDropdowns();
    document.getElementById('saveOrderBtn').style.display = 'inline-block';
}

function updateDropdowns() {
    const rows = Array.from(document.querySelectorAll('#sortableBody tr[data-id]'));
    rows.forEach((row, idx) => {
        const select = row.querySelector('select[data-id]');
        if (select) select.value = idx + 1;
    });
}

function saveOrder() {
    const ids = Array.from(document.querySelectorAll('#sortableBody tr[data-id]')).map(r => r.dataset.id);
    const btn = document.getElementById('saveOrderBtn');
    btn.disabled = true;
    fetch('{{ route('admin.menu-categories.reorder') }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({ids})
    }).then(r => r.json()).then(res => {
        if (res.success) {
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Saved!';
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Order';
                btn.style.display = 'none';
                btn.disabled = false;
            }, 1500);
        }
    });
}
</script>
@endsection
