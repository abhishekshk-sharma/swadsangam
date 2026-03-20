@extends('layouts.manager')
@section('title', 'Create Menu Item')
@section('content')

<div class="content-card" style="max-width:900px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-plus-circle"></i> Create Menu Item</div>
        <a href="{{ route('manager.menu.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-error"><ul class="mb-0" style="padding-left:20px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('manager.menu.store') }}" method="POST">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Menu Category *</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <select name="menu_category_id" id="menu_category_id" class="form-select" required style="flex:1;">
                            <option value="">-- Select Category --</option>
                            @foreach($menuCategories as $category)
                                <option value="{{ $category->id }}" {{ old('menu_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="document.getElementById('quickMenuCatModal').style.display='flex'" class="btn btn-secondary btn-sm" style="white-space:nowrap;"><i class="fas fa-plus"></i> New</button>
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Item Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Price *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="col-12">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', '1') ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--blue-600);">
                        <strong>Show on menu</strong>
                    </label>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-1"></i> Create</button>
                <a href="{{ route('manager.menu.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

{{-- Quick Create Menu Category Modal --}}
<div id="quickMenuCatModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;padding:24px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <h3 style="font-size:1rem;font-weight:600;margin-bottom:16px;"><i class="fas fa-tags me-2" style="color:var(--blue-500);"></i>New Menu Category</h3>
        <div class="form-group">
            <label class="form-label">Name *</label>
            <input type="text" id="qmcName" class="form-control" placeholder="e.g., Starters, Desserts">
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <input type="text" id="qmcDesc" class="form-control" placeholder="Optional">
        </div>
        <div id="qmcError" style="color:var(--error);font-size:13px;margin-bottom:8px;display:none;"></div>
        <div style="display:flex;gap:8px;margin-top:16px;">
            <button type="button" onclick="quickCreateMenuCat()" class="btn btn-primary" style="flex:1;"><i class="fas fa-plus me-1"></i>Create & Select</button>
            <button type="button" onclick="document.getElementById('quickMenuCatModal').style.display='none'" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function quickCreateMenuCat() {
    const name = document.getElementById('qmcName').value.trim();
    const desc = document.getElementById('qmcDesc').value.trim();
    const err  = document.getElementById('qmcError');
    if (!name) { err.textContent = 'Name is required.'; err.style.display = 'block'; return; }
    err.style.display = 'none';

    fetch('{{ route('manager.menu-categories.quickCreate') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ name, description: desc })
    })
    .then(r => r.json())
    .then(data => {
        const sel = document.getElementById('menu_category_id');
        sel.appendChild(new Option(data.name, data.id, true, true));
        sel.value = data.id;
        document.getElementById('quickMenuCatModal').style.display = 'none';
        document.getElementById('qmcName').value = '';
        document.getElementById('qmcDesc').value = '';
    })
    .catch(() => { err.textContent = 'Failed to create category.'; err.style.display = 'block'; });
}
</script>
@endpush
@endsection
