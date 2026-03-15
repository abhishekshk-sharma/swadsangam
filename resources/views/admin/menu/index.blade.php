@extends('layouts.admin')

@section('title', 'Menu Items')

@section('content')
<style>
    .menu-item-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e3e6e8;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        gap: 12px;
    }
    .menu-item-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .menu-name {
        font-size: 15px;
        font-weight: 700;
        color: #232f3e;
    }
    .menu-category {
        display: inline-block;
        padding: 2px 8px;
        background: #e7f3ff;
        color: #0066c0;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 4px;
    }
    .menu-price {
        font-size: 16px;
        font-weight: 700;
        color: #067d62;
        white-space: nowrap;
    }
    .menu-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .btn-edit {
        background: #fff;
        border: 1px solid #d5d9d9;
        color: #232f3e;
        padding: 6px 14px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-edit:hover { background: #f7f8f9; border-color: #ff9900; color: #ff9900; }
    .btn-delete-menu {
        background: #fff;
        border: 1px solid #d5d9d9;
        color: #d13212;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-delete-menu:hover { background: #d13212; border-color: #d13212; color: #fff; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="section-title">Menu Items</h1>
    <div class="d-flex gap-2">
        @if(auth()->check() && auth()->user()->isSuperAdmin())
        <a href="{{ route('admin.menu-categories.index') }}" class="btn-secondary">
            <i class="fas fa-tags me-1"></i> Manage Categories
        </a>
        @endif
        <a href="{{ route('admin.menu.create') }}" class="btn-primary">
            <i class="fas fa-plus me-1"></i> Add Item
        </a>
    </div>
</div>

<div class="content-card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label"><i class="fas fa-filter me-2"></i>Filter by Category</label>
                <select class="form-select" onchange="filterMenu(this.value, '{{ request('status') }}')">
                    <option value="" {{ !request('menu_category_id') ? 'selected' : '' }}>All Categories</option>
                    @foreach($menuCategories as $category)
                        <option value="{{ $category->id }}" {{ request('menu_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label"><i class="fas fa-check-circle me-2"></i>Filter by Availability</label>
                <select class="form-select" onchange="filterMenu('{{ request('menu_category_id') }}', this.value)">
                    <option value="" {{ !request('status') ? 'selected' : '' }}>All Items</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                </select>
            </div>
            <div class="col-md-4">
                <button onclick="clearFilters()" class="btn-secondary w-100">
                    <i class="fas fa-redo me-2"></i>Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function filterMenu(categoryId, status) {
    let url = '{{ route('admin.menu.index') }}';
    let params = [];
    
    if (categoryId) params.push('menu_category_id=' + categoryId);
    if (status) params.push('status=' + status);
    
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    window.location.href = url;
}

function clearFilters() {
    window.location.href = '{{ route('admin.menu.index') }}';
}
</script>

<div class="d-flex flex-column gap-2">
    @forelse($menuItems as $item)
    <div class="menu-item-card">
        <div style="flex:1; min-width:0;">
            <div class="menu-name">{{ $item->name }}</div>
            <div>
                @if($item->menuCategory)
                    <span class="menu-category"><i class="fas fa-tag me-1"></i>{{ $item->menuCategory->name }}</span>
                @endif
                <span class="badge-custom {{ $item->is_available ? 'badge-completed' : 'badge-pending' }} ms-1">
                    {{ $item->is_available ? 'Available' : 'Unavailable' }}
                </span>
            </div>
        </div>
        <div class="menu-price">₹{{ number_format($item->price, 2) }}</div>
        <div class="menu-actions">
            <a href="{{ route('admin.menu.edit', $item->id) }}" class="btn-edit">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <form action="{{ route('admin.menu.destroy', $item->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete-menu" onclick="return confirm('Delete this item?')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <i class="fas fa-utensils"></i>
        <p>No menu items found</p>
        <a href="{{ route('admin.menu.create') }}" class="btn-primary mt-3">
            <i class="fas fa-plus me-1"></i> Add Your First Menu Item
        </a>
    </div>
    @endforelse
</div>
@endsection
