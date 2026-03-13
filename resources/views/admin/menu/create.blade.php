@extends('layouts.admin')

@section('title', 'Create Menu Item')

@section('content')
<div class="content-card" style="max-width: 900px;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="card-title mb-1">
                    <i class="fas fa-plus-circle" style="color: #ff9900;"></i> Create New Menu Item
                </h2>
                <p class="text-muted mb-0" style="font-size: 13px;">Add a new item to your restaurant menu</p>
            </div>
            <a href="{{ route('admin.menu.index') }}" class="btn-secondary" style="text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Menu
            </a>
        </div>
    </div>
    
    <div class="card-body">
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

        <form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data" id="menuForm">
            @csrf
            
            <!-- Basic Information Section -->
            <div class="mb-4">
                <h5 style="color: #232f3e; font-weight: 600; font-size: 15px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">
                    <i class="fas fa-info-circle" style="color: #ff9900;"></i> Basic Information
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">
                            <i class="fas fa-utensils"></i> Item Name *
                        </label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name') }}" 
                               placeholder="e.g., Margherita Pizza, Caesar Salad" 
                               required>
                        <small class="text-muted">Enter the name as it will appear on the menu</small>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-dollar-sign"></i> Price *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: #f7f8f9; border-color: #d5d9d9;">₹</span>
                            <input type="number" step="0.01" name="price" class="form-control" 
                                   value="{{ old('price') }}" 
                                   placeholder="0.00" 
                                   required>
                        </div>
                        <small class="text-muted">Enter price in rupees</small>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <label class="form-label">
                            <i class="fas fa-align-left"></i> Description
                        </label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Describe the dish, ingredients, or special features...">{{ old('description') }}</textarea>
                        <small class="text-muted">Optional: Add details about the dish to help customers decide</small>
                    </div>
                </div>
            </div>
            
            <!-- Categorization Section -->
            <div class="mb-4">
                <h5 style="color: #232f3e; font-weight: 600; font-size: 15px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">
                    <i class="fas fa-tags" style="color: #ff9900;"></i> Categorization
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-list"></i> Category *
                        </label>
                        <input type="text" name="category" class="form-control" 
                               value="{{ old('category') }}" 
                               placeholder="e.g., Main Course, Appetizer, Dessert" 
                               list="categoryList"
                               required>
                        <datalist id="categoryList">
                            <option value="Appetizer">
                            <option value="Main Course">
                            <option value="Dessert">
                            <option value="Beverage">
                            <option value="Starter">
                            <option value="Salad">
                        </datalist>
                        <small class="text-muted">Type or select from suggestions</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-folder"></i> Menu Category (Optional)
                        </label>
                        <select name="menu_category_id" class="form-select">
                            <option value="">-- No Category --</option>
                            @foreach($menuCategories as $category)
                                <option value="{{ $category->id }}" {{ old('menu_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Link to a predefined category</small>
                    </div>
                </div>
            </div>
            
            <!-- Image Upload Section -->
            <div class="mb-4">
                <h5 style="color: #232f3e; font-weight: 600; font-size: 15px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">
                    <i class="fas fa-image" style="color: #ff9900;"></i> Item Image
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-upload"></i> Upload Image
                        </label>
                        <input type="file" name="image" class="form-control" 
                               accept="image/jpeg,image/png,image/jpg,image/gif" 
                               id="imageInput"
                               onchange="previewImage(event)">
                        <small class="text-muted">Supported: JPG, PNG, GIF (Max: 5MB)</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Preview</label>
                        <div id="imagePreview" style="border: 2px dashed #d5d9d9; border-radius: 8px; padding: 20px; text-align: center; background: #fafafa; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                            <div style="color: #999;">
                                <i class="fas fa-image" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                <span style="font-size: 13px;">No image selected</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Availability Section -->
            <div class="mb-4">
                <h5 style="color: #232f3e; font-weight: 600; font-size: 15px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;">
                    <i class="fas fa-toggle-on" style="color: #ff9900;"></i> Availability
                </h5>
                
                <div class="form-check" style="padding-left: 0;">
                    <div style="background: #f7f8f9; border: 1px solid #d5d9d9; border-radius: 6px; padding: 16px;">
                        <label class="form-check-label d-flex align-items-center" style="cursor: pointer;">
                            <input type="checkbox" name="is_available" value="1" 
                                   {{ old('is_available', '1') ? 'checked' : '' }} 
                                   class="form-check-input" 
                                   style="width: 20px; height: 20px; margin-right: 12px; cursor: pointer;">
                            <div>
                                <strong style="color: #232f3e; font-size: 14px;">Item is Available</strong>
                                <p class="mb-0 text-muted" style="font-size: 12px; margin-top: 4px;">
                                    Uncheck this if the item is temporarily out of stock
                                </p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex gap-2 pt-3" style="border-top: 2px solid #f0f0f0;">
                <button type="submit" class="btn-primary" style="padding: 12px 32px;">
                    <i class="fas fa-check-circle"></i> Create Menu Item
                </button>
                <a href="{{ route('admin.menu.index') }}" class="btn-secondary" style="padding: 12px 32px; text-decoration: none;">
                    <i class="fas fa-times-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(event) {
    const preview = document.getElementById('imagePreview');
    const file = event.target.files[0];
    
    if (file) {
        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size should not exceed 5MB');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" 
                     style="max-width: 100%; max-height: 200px; border-radius: 6px; object-fit: cover;" 
                     alt="Preview">
            `;
        }
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = `
            <div style="color: #999;">
                <i class="fas fa-image" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                <span style="font-size: 13px;">No image selected</span>
            </div>
        `;
    }
}

// Form validation
document.getElementById('menuForm').addEventListener('submit', function(e) {
    const name = document.querySelector('input[name="name"]').value.trim();
    const price = document.querySelector('input[name="price"]').value;
    const category = document.querySelector('input[name="category"]').value.trim();
    
    if (!name || !price || !category) {
        e.preventDefault();
        alert('Please fill in all required fields (Name, Price, and Category)');
        return false;
    }
    
    if (parseFloat(price) <= 0) {
        e.preventDefault();
        alert('Price must be greater than 0');
        return false;
    }
});
</script>
@endsection
