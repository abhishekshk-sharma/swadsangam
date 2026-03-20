@extends('layouts.manager')
@section('title', 'Tables')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 style="font-size:1.2rem;font-weight:600;">Branch Tables</h1>
    <a href="{{ route('manager.tables.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Add Table</a>
</div>

<div class="content-card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="{{ route('manager.tables.index', ['status' => request('status')]) }}"
                       class="btn btn-sm {{ !request('category_id') ? 'btn-primary' : 'btn-secondary' }}">All</a>
                    @foreach($categories as $category)
                        <a href="{{ route('manager.tables.index', ['category_id' => $category->id, 'status' => request('status')]) }}"
                           class="btn btn-sm {{ request('category_id') == $category->id ? 'btn-primary' : 'btn-secondary' }}">{{ $category->name }}</a>
                    @endforeach
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <div style="display:flex;gap:8px;">
                    <a href="{{ route('manager.tables.index', ['category_id' => request('category_id')]) }}"
                       class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-secondary' }}">All</a>
                    <a href="{{ route('manager.tables.index', ['category_id' => request('category_id'), 'status' => 'available']) }}"
                       class="btn btn-sm {{ request('status') === 'available' ? 'btn-primary' : 'btn-secondary' }}">Available</a>
                    <a href="{{ route('manager.tables.index', ['category_id' => request('category_id'), 'status' => 'occupied']) }}"
                       class="btn btn-sm {{ request('status') === 'occupied' ? 'btn-primary' : 'btn-secondary' }}">Occupied</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    @forelse($tables as $table)
    <div class="col-md-4">
        <div style="background:#fff;border-radius:8px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.08);border:1px solid #e3e6e8;transition:all .2s;">
            <div style="font-size:20px;font-weight:700;color:#232f3e;margin-bottom:8px;">Table {{ $table->table_number }}</div>
            @if($table->category)
                <span style="display:inline-block;padding:4px 12px;background:#e7f3ff;color:#0066c0;border-radius:4px;font-size:12px;font-weight:600;margin-bottom:12px;">{{ $table->category->name }}</span>
            @endif
            <div style="color:#666;font-size:14px;margin-bottom:8px;"><i class="fas fa-users me-2"></i>Capacity: <strong>{{ $table->capacity }} seats</strong></div>
            <div style="color:#666;font-size:14px;margin-bottom:16px;">
                Status: <span style="display:inline-block;padding:4px 12px;border-radius:4px;font-size:12px;font-weight:600;background:{{ $table->is_occupied ? '#f8d7da' : '#d1e7dd' }};color:{{ $table->is_occupied ? '#842029' : '#0f5132' }};">{{ $table->is_occupied ? 'Occupied' : 'Available' }}</span>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('manager.tables.show', $table->id) }}" style="flex:1;background:#067d62;color:#fff;padding:8px 16px;border-radius:4px;font-size:13px;font-weight:600;text-align:center;text-decoration:none;">
                    <i class="fas fa-qrcode me-1"></i> View QR
                </a>
                @if(!$table->is_occupied)
                <button onclick="openEdit({{ $table->id }}, '{{ $table->table_number }}', {{ $table->capacity }}, {{ $table->category_id ?? 'null' }})"
                        style="background:#fff;border:1px solid #d5d9d9;color:#232f3e;padding:8px 14px;border-radius:4px;font-size:13px;font-weight:600;cursor:pointer;">
                    <i class="fas fa-edit"></i>
                </button>
                @endif
                <form action="{{ route('manager.tables.destroy', $table->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:#d13212;color:#fff;padding:8px 14px;border-radius:4px;font-size:13px;border:none;cursor:pointer;" onclick="return confirm('Delete this table?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="empty-state"><i class="fas fa-table"></i><p>No tables found</p>
            <a href="{{ route('manager.tables.create') }}" class="btn btn-primary mt-3"><i class="fas fa-plus me-1"></i> Add First Table</a>
        </div>
    </div>
    @endforelse
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;padding:28px;width:100%;max-width:420px;box-shadow:0 8px 32px rgba(0,0,0,.18);">
        <div style="font-size:17px;font-weight:700;color:#232f3e;margin-bottom:20px;"><i class="fas fa-edit me-2" style="color:#3b82f6;"></i>Edit Table</div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Table Number</label>
                <input type="text" name="table_number" id="edit_table_number" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Capacity</label>
                <input type="number" name="capacity" id="edit_capacity" class="form-control" min="1" required>
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category_id" id="edit_category_id" class="form-select">
                    <option value="">-- No Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px;margin-top:1rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-save me-1"></i>Save</button>
                <button type="button" onclick="closeEdit()" class="btn btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, tableNumber, capacity, categoryId) {
    document.getElementById('editForm').action = '/manager/tables/' + id;
    document.getElementById('edit_table_number').value = tableNumber;
    document.getElementById('edit_capacity').value = capacity;
    document.getElementById('edit_category_id').value = categoryId ?? '';
    document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() { document.getElementById('editModal').style.display = 'none'; }
document.getElementById('editModal').addEventListener('click', function(e) { if (e.target === this) closeEdit(); });
</script>
@endsection
