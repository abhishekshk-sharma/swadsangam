@extends('layouts.admin')

@section('title', 'Tables')

@section('content')
<style>
    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .filter-tab {
        padding: 8px 20px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid #d5d9d9;
        background: #fff;
        color: #232f3e;
    }
    .filter-tab:hover {
        border-color: #ff9900;
        color: #ff9900;
    }
    .filter-tab.active {
        background: #ff9900;
        border-color: #ff9900;
        color: #fff;
    }
    .table-card {
        background: #fff;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e3e6e8;
        transition: all 0.2s ease;
    }
    .table-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    .table-name {
        font-size: 20px;
        font-weight: 700;
        color: #232f3e;
        margin-bottom: 8px;
    }
    .table-category {
        display: inline-block;
        padding: 4px 12px;
        background: #e7f3ff;
        color: #0066c0;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
    }
    .table-info {
        color: #666;
        font-size: 14px;
        margin-bottom: 8px;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-available {
        background: #d1e7dd;
        color: #0f5132;
    }
    .status-occupied {
        background: #f8d7da;
        color: #842029;
    }
    .action-buttons {
        display: flex;
        gap: 8px;
        margin-top: 16px;
    }
    .btn-view {
        flex: 1;
        background: #067d62;
        color: #fff;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-view:hover {
        background: #055a47;
    }
    .btn-delete {
        background: #d13212;
        color: #fff;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-edit-table {
        background: #fff;
        border: 1px solid #d5d9d9;
        color: #232f3e;
        padding: 8px 14px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-edit-table:hover { border-color: #ff9900; color: #ff9900; }
    .modal-overlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: #fff;
        border-radius: 10px;
        padding: 28px;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    }
    .modal-title { font-size: 17px; font-weight: 700; color: #232f3e; margin-bottom: 20px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="section-title">Restaurant Tables</h1>
    <div class="d-flex gap-2">
        @if(auth()->check() && auth()->user()->isSuperAdmin())
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary">
            <i class="fas fa-layer-group me-1"></i> Manage Categories
        </a>
        @endif
        <a href="{{ route('admin.tables.create') }}" class="btn-primary">
            <i class="fas fa-plus me-1"></i> Add Table
        </a>
    </div>
</div>

<div class="content-card mb-4">
    <div class="card-body">
        @if($branches->count() > 0)
        <form method="GET" action="{{ route('admin.tables.index') }}" style="margin-bottom:16px;">
            <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <label style="font-size:13px;font-weight:600;color:var(--gray-600);white-space:nowrap;"><i class="fas fa-store me-1"></i>Branch:</label>
                <select name="branch_id" onchange="this.form.submit()" style="padding:7px 12px;border:1px solid var(--gray-300);border-radius:8px;font-size:13px;font-weight:500;color:var(--gray-700);background:var(--white);min-width:180px;cursor:pointer;">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @if($selectedBranch)
                    <a href="{{ route('admin.tables.index', array_filter(['category_id' => request('category_id'), 'status' => request('status')])) }}" style="font-size:12px;color:var(--gray-500);text-decoration:none;"><i class="fas fa-times me-1"></i>Clear</a>
                @endif
            </div>
        </form>
        @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Filter by Category</label>
                <div class="filter-tabs">
                    <a href="{{ route('admin.tables.index', ['status' => request('status')]) }}" 
                       class="filter-tab {{ !request('category_id') ? 'active' : '' }}">
                        <i class="fas fa-th me-1"></i> All Categories
                    </a>
                    @foreach($categories as $category)
                        <a href="{{ route('admin.tables.index', ['category_id' => $category->id, 'status' => request('status')]) }}" 
                           class="filter-tab {{ request('category_id') == $category->id ? 'active' : '' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Filter by Status</label>
                <div class="filter-tabs">
                    <a href="{{ route('admin.tables.index', ['category_id' => request('category_id')]) }}" 
                       class="filter-tab {{ !request('status') ? 'active' : '' }}">
                        <i class="fas fa-list me-1"></i> All Status
                    </a>
                    <a href="{{ route('admin.tables.index', ['category_id' => request('category_id'), 'status' => 'available']) }}" 
                       class="filter-tab {{ request('status') === 'available' ? 'active' : '' }}">
                        <i class="fas fa-check-circle me-1"></i> Available
                    </a>
                    <a href="{{ route('admin.tables.index', ['category_id' => request('category_id'), 'status' => 'occupied']) }}" 
                       class="filter-tab {{ request('status') === 'occupied' ? 'active' : '' }}">
                        <i class="fas fa-times-circle me-1"></i> Occupied
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    @forelse($tables as $table)
    <div class="col-md-4">
        <div class="table-card">
            <div class="table-name">Table {{ $table->table_number }}</div>
            @if($table->category)
                <span class="table-category">
                    <i class="fas fa-tag me-1"></i>{{ $table->category->name }}
                </span>
            @endif
            <div class="table-info">
                <i class="fas fa-users me-2"></i>Capacity: <strong>{{ $table->capacity }} seats</strong>
            </div>
            <div class="table-info">
                <i class="fas fa-info-circle me-2"></i>Status: 
                <span class="status-badge {{ $table->is_occupied ? 'status-occupied' : 'status-available' }}">
                    {{ $table->is_occupied ? 'Occupied' : 'Available' }}
                </span>
            </div>
            <div class="action-buttons">
                <a href="{{ route('admin.tables.show', $table->id) }}" class="btn-view">
                    <i class="fas fa-qrcode me-1"></i> View QR
                </a>
                @if(!$table->is_occupied)
                <button onclick="openEdit({{ $table->id }}, '{{ $table->table_number }}', {{ $table->capacity }}, {{ $table->category_id ?? 'null' }})" class="btn-edit-table">
                    <i class="fas fa-edit"></i>
                </button>
                @endif
                <form action="{{ route('admin.tables.destroy', $table->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete" onclick="return confirm('Delete this table?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="empty-state">
            <i class="fas fa-table"></i>
            <p>No tables found</p>
            <a href="{{ route('admin.tables.create') }}" class="btn-primary mt-3">
                <i class="fas fa-plus me-1"></i> Add Your First Table
            </a>
        </div>
    </div>
    @endforelse
</div>
@endsection

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-edit me-2" style="color:#ff9900;"></i>Edit Table</div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Table Number</label>
                <input type="text" name="table_number" id="edit_table_number" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Capacity</label>
                <input type="number" name="capacity" id="edit_capacity" class="form-control" min="1" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Category</label>
                <select name="category_id" id="edit_category_id" class="form-select">
                    <option value="">-- No Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                    <option value="__new__">➕ Add New Category...</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn-primary" style="flex:1;"><i class="fas fa-save me-1"></i>Save</button>
                <button type="button" onclick="closeEdit()" class="btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, tableNumber, capacity, categoryId) {
    document.getElementById('editForm').action = '/admin/tables/' + id;
    document.getElementById('edit_table_number').value = tableNumber;
    document.getElementById('edit_capacity').value = capacity;
    const sel = document.getElementById('edit_category_id');
    sel.value = categoryId ?? '';
    document.getElementById('editModal').classList.add('open');
}
function closeEdit() {
    document.getElementById('editModal').classList.remove('open');
}
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});
document.getElementById('edit_category_id').addEventListener('change', function() {
    if (this.value === '__new__') {
        this.value = '';
        openQuickCategoryModal('edit_category_id', '{{ route('admin.categories.quickCreate') }}');
    }
});
</script>

@include('admin.partials.quick-category-modal')
