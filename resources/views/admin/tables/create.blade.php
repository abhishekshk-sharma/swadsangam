@extends('layouts.admin')

@section('title', 'Create Table')

@section('content')
<style>
    .form-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .form-card {
        background: #fff;
        padding: 32px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e3e6e8;
    }
    .form-title {
        font-size: 24px;
        font-weight: 700;
        color: #232f3e;
        margin-bottom: 24px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 32px;
    }
    .error-message {
        color: #d13212;
        font-size: 13px;
        margin-top: 6px;
    }
</style>

<div class="form-container">
    <div class="form-card">
        <h1 class="form-title">
            <i class="fas fa-plus-circle me-2" style="color: #ff9900;"></i>Create New Table
        </h1>
        
        <form action="{{ route('admin.tables.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-tag me-2"></i>Category (Optional)
                </label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">No Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" data-prefix="{{ strtoupper(substr($category->name, 0, 1)) }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                    <option value="__new__">➕ Add New Category...</option>
                </select>
            </div>

            {{-- Bulk creation row (shown when category selected) --}}
            <div class="form-group" id="bulk-row" style="display:none;">
                <label class="form-label">
                    <i class="fas fa-layer-group me-2"></i>Number of Tables to Create
                </label>
                <input type="number" name="count" id="count" min="1" max="100" class="form-control" placeholder="e.g. 5">
                <div id="bulk-preview" style="margin-top:8px; font-size:13px; color:#067d62;"></div>
            </div>

            <div class="form-group" id="manual-name-row">
                <label class="form-label">
                    <i class="fas fa-hashtag me-2"></i>Table Number / Name
                </label>
                <input type="text" name="table_number" id="table_number" class="form-control"
                       placeholder="e.g., Table 1, VIP-A, etc."
                       value="{{ old('table_number') }}">
                @error('table_number')
                    <p class="error-message"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-users me-2"></i>Capacity (seats)
                </label>
                <input type="number" name="capacity" min="1" value="{{ old('capacity', 4) }}"
                       class="form-control" placeholder="Number of seats" required>
                @error('capacity')
                    <p class="error-message"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</p>
                @enderror
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary" style="flex: 1;">
                    <i class="fas fa-check me-2"></i>Create Table
                </button>
                <a href="{{ route('admin.tables.index') }}" class="btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@include('admin.partials.quick-category-modal')

<script>
const categorySelect = document.getElementById('category_id');
const bulkRow        = document.getElementById('bulk-row');
const manualRow      = document.getElementById('manual-name-row');
const countInput     = document.getElementById('count');
const preview        = document.getElementById('bulk-preview');
const tableNumInput  = document.getElementById('table_number');

function updateBulkUI() {
    const opt = categorySelect.options[categorySelect.selectedIndex];
    const hasCat = categorySelect.value && categorySelect.value !== '__new__';
    bulkRow.style.display  = hasCat ? '' : 'none';
    if (!hasCat) {
        countInput.value = '';
        preview.textContent = '';
        manualRow.style.display = '';
        tableNumInput.required = true;
    }
    updatePreview();
}

function updatePreview() {
    const opt = categorySelect.options[categorySelect.selectedIndex];
    const prefix = opt ? (opt.dataset.prefix || '') : '';
    const n = parseInt(countInput.value);
    if (prefix && n > 0) {
        manualRow.style.display = 'none';
        tableNumInput.required = false;
        const labels = Array.from({length: Math.min(n, 5)}, (_, i) => prefix + (i + 1));
        preview.innerHTML = '<i class="fas fa-eye me-1"></i>Will create: <strong>' +
            labels.join(', ') + (n > 5 ? ', ...' : '') + '</strong> (exact numbers may shift to avoid duplicates)';
    } else if (categorySelect.value && categorySelect.value !== '__new__') {
        manualRow.style.display = '';
        tableNumInput.required = true;
        preview.textContent = '';
    }
}

categorySelect.addEventListener('change', function() {
    if (this.value === '__new__') {
        this.value = '';
        openQuickCategoryModal('category_id', '{{ route('admin.categories.quickCreate') }}');
        return;
    }
    updateBulkUI();
});
countInput.addEventListener('input', updatePreview);
</script>
@endsection
