@extends('layouts.manager')
@section('title', 'Menu Items')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 style="font-size:1.2rem;font-weight:600;">Menu Items</h1>
    <a href="{{ route('manager.menu.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Add Item
    </a>
</div>

<div class="content-card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Category</label>
                <select class="form-select" onchange="filterMenu(this.value, '{{ request('status') }}')">
                    <option value="" {{ !request('menu_category_id') ? 'selected' : '' }}>All Categories</option>
                    @foreach($menuCategories as $category)
                        <option value="{{ $category->id }}" {{ request('menu_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Availability</label>
                <select class="form-select" onchange="filterMenu('{{ request('menu_category_id') }}', this.value)">
                    <option value="" {{ !request('status') ? 'selected' : '' }}>All Items</option>
                    <option value="available"   {{ request('status') === 'available'   ? 'selected' : '' }}>Available</option>
                    <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                </select>
            </div>
            <div class="col-md-4">
                <button onclick="window.location='{{ route('manager.menu.index') }}'" class="btn btn-secondary w-100">
                    <i class="fas fa-redo me-2"></i>Clear
                </button>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-column gap-2">
    @forelse($menuItems as $item)
    <div style="background:#fff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);border:1px solid #e3e6e8;display:flex;align-items:center;justify-content:space-between;padding:14px 16px;gap:12px;">
        <div style="flex:1;min-width:0;">
            <div style="font-size:15px;font-weight:700;color:#232f3e;">{{ $item->name }}</div>
            <div>
                @if($item->menuCategory)
                    <span style="display:inline-block;padding:2px 8px;background:#e7f3ff;color:#0066c0;border-radius:4px;font-size:11px;font-weight:600;text-transform:uppercase;margin-top:4px;">{{ $item->menuCategory->name }}</span>
                @endif
                <span class="badge {{ $item->is_available ? 'badge-success' : 'badge-error' }} ms-1">{{ $item->is_available ? 'Available' : 'Unavailable' }}</span>
            </div>
        </div>
        <div style="font-size:16px;font-weight:700;color:#067d62;white-space:nowrap;">₹{{ number_format($item->price, 2) }}</div>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="{{ route('manager.menu.edit', $item->id) }}" class="btn btn-secondary btn-sm"><i class="fas fa-edit me-1"></i> Edit</a>
            <form action="{{ route('manager.menu.destroy', $item->id) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm" style="background:#d13212;color:#fff;border:none;" onclick="return confirm('Delete this item?')"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <i class="fas fa-utensils"></i>
        <p>No menu items found</p>
        <a href="{{ route('manager.menu.create') }}" class="btn btn-primary mt-3"><i class="fas fa-plus me-1"></i> Add First Item</a>
    </div>
    @endforelse
</div>

<script>
function filterMenu(cat, status) {
    const params = new URLSearchParams(window.location.search);
    if (cat)    params.set('menu_category_id', cat);    else params.delete('menu_category_id');
    if (status) params.set('status', status);           else params.delete('status');
    window.location.href = '{{ route('manager.menu.index') }}?' + params.toString();
}
</script>
@endsection
