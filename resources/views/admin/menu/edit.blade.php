@extends('layouts.admin')

@section('title', 'Edit Menu Item')

@section('content')
<div class="content-card" style="max-width: 900px;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center" style="position:relative;width: 100%;">
            <div>
                <h2 class="card-title mb-1">
                    <i class="fas fa-edit" style="color: #ff9900;"></i> Edit Menu Item
                </h2>
                <p class="text-muted mb-0" style="font-size: 13px;">Update menu item details</p>
            </div>
            <a href="{{ route('admin.menu.index') }}" class="btn-secondary" style="text-decoration: none;" >
                <i class="fas fa-arrow-left"></i> Back to Menu
            </a>
        </div>
    </div>
    
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2" style="padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('admin.menu.update.post', $menuItem->id) }}" method="POST" id="menuForm">
            @csrf
            
            <!-- Categorization -->
            <div class="mb-4">
                <h5 style="color: #232f3e; font-weight: 600; font-size: 15px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">
                    <i class="fas fa-tags" style="color: #ff9900;"></i> Category *
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><i class="fas fa-folder"></i> Menu Category *</label>
                        <select name="menu_category_id" id="menu_category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            @foreach($menuCategories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('menu_category_id', $menuItem->menu_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                            <option value="__new__">➕ Add New Category...</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="mb-4">
                <h5 style="color: #232f3e; font-weight: 600; font-size: 15px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">
                    <i class="fas fa-info-circle" style="color: #ff9900;"></i> Item Details
                </h5>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label"><i class="fas fa-utensils"></i> Item Name *</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $menuItem->name) }}"
                               placeholder="e.g., Margherita Pizza, Caesar Salad" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-rupee-sign"></i> Price *</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: #f7f8f9; border-color: #d5d9d9;">₹</span>
                            <input type="number" step="0.01" name="price" class="form-control"
                                   value="{{ old('price', $menuItem->price) }}" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Describe the dish, ingredients, or special features...">{{ old('description', $menuItem->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Show -->
            <div class="mb-4">
                <h5 style="color:#232f3e;font-weight:600;font-size:15px;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #f0f0f0;">
                    <i class="fas fa-eye" style="color:#ff9900;"></i> Show
                </h5>
                <div style="background:#f7f8f9;border:1px solid #d5d9d9;border-radius:6px;padding:16px;">
                    <label class="form-check-label d-flex align-items-center" style="cursor:pointer;">
                        <input type="checkbox" name="is_available" value="1"
                               {{ old('is_available', $menuItem->is_available) ? 'checked' : '' }}
                               class="form-check-input"
                               style="width:20px;height:20px;margin-right:12px;cursor:pointer;">
                        <div>
                            <strong style="color:#232f3e;font-size:14px;">Show this item on menu</strong>
                            <p class="mb-0 text-muted" style="font-size:12px;margin-top:4px;">Uncheck to hide this item from the menu</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 pt-3" style="border-top: 2px solid #f0f0f0;">
                <button type="submit" class="btn-primary" style="padding: 12px 32px;">
                    <i class="fas fa-save"></i> Update Menu Item
                </button>
                <a href="{{ route('admin.menu.index') }}" class="btn-secondary" style="padding: 12px 32px; text-decoration: none;">
                    <i class="fas fa-times-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('menuForm').addEventListener('submit', function(e) {
    const name = document.querySelector('input[name="name"]').value.trim();
    const price = document.querySelector('input[name="price"]').value;
    const cat = document.querySelector('select[name="menu_category_id"]').value;
    if (!name || !price || !cat) {
        e.preventDefault();
        alert('Please fill in all required fields (Category, Name, and Price)');
        return false;
    }
    if (parseFloat(price) <= 0) {
        e.preventDefault();
        alert('Price must be greater than 0');
        return false;
    }
});

document.getElementById('menu_category_id').addEventListener('change', function() {
    if (this.value === '__new__') {
        this.value = '';
        openQuickCategoryModal('menu_category_id', '{{ route('admin.menu-categories.quickCreate') }}');
    }
});
</script>
@include('admin.partials.quick-category-modal')
@endsection
