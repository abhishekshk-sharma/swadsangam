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
    .btn-delete:hover {
        background: #b02a0f;
    }
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
                <form action="{{ route('admin.tables.destroy', $table->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete" onclick="return confirm('Are you sure you want to delete this table?')">
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
