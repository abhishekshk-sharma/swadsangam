@extends('layouts.admin')

@section('title', 'Edit Menu Item')

@section('content')
<div class="content-card" style="max-width: 900px;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="card-title mb-1">
                    <i class="fas fa-edit" style="color: #ff9900;"></i> Edit Menu Item
                </h2>
                <p class="text-muted mb-0" style="font-size: 13px;">Update menu item details</p>
            </div>
            <a href="{{ route('admin.menu.index') }}" class="btn-secondary" style="text-decoration: none;">
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
        
        <form action="{{ route('admin.menu.update.post', $menuItem->id) }}" method="POST" enctype="multipart/form-data" id="menuForm">
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
                               value="{{ old('name', $menuItem->name) }}" 
                               placeholder="e.g., Margherita Pizza, Caesar Salad" 
                               required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-dollar-sign"></i> Price *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: #f7f8f9; border-color: #d5d9d9;">₹</span>
                            <input type="number" step="0.01" name="price" class="form-control" 
                                   value="{{ old('price', $menuItem->price) }}" 
                                   placeholder="0.00" 
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <label class="form-label">
                            <i class="fas fa-align-left"></i> Description
                        </label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Describe the dish, ingredients, or special features...">{{ old('description', $menuItem->description) }}</textarea>
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
                               value="{{ old('category', $menuItem->category) }}" 
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
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-folder"></i> Menu Category (Optional)
                        </label>
                        <select name="menu_category_id" class="form-select">
                            <option value="">-- No Category --</option>
                            @foreach($menuCategories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ old('menu_category_id', $menuItem->menu_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
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
                            <i class="fas fa-upload"></i> Upload New Image (Optional)
                        </label>
                        <input type="file" name="image" class="form-control" 
                               accept="image/jpeg,image/png,image/jpg,image/gif" 
                               id="imageInput"
                               onchange="previewImage(event)">
                        <small class="text-muted">Leave empty to keep current image</small>
                        <div id="fileInfo" style="margin-top: 8px; padding: 8px 12px; border-radius: 4px; font-size: 13px; display: none;"></div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Current / Preview</label>
                        <div id="imagePreview" style="border: 2px dashed #d5d9d9; border-radius: 8px; padding: 20px; text-align: center; background: #fafafa; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                            @if($menuItem->image)
                                <img src="{{ asset($menuItem->image) }}" 
                                     style="max-width: 100%; max-height: 200px; border-radius: 6px; object-fit: cover;" 
                                     alt="Current image"
                                     id="currentImage">
                            @else
                                <div style="color: #999;">
                                    <i class="fas fa-image" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                    <span style="font-size: 13px;">No image uploaded</span>
                                </div>
                            @endif
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
                                   {{ old('is_available', $menuItem->is_available) ? 'checked' : '' }} 
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
function previewImage(event) {
    const preview = document.getElementById('imagePreview');
    const fileInfo = document.getElementById('fileInfo');
    const file = event.target.files[0];
    
    if (file) {
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        
        if (file.size > 5 * 1024 * 1024) {
            fileInfo.style.display = 'block';
            fileInfo.style.background = '#f8d7da';
            fileInfo.style.color = '#842029';
            fileInfo.style.border = '1px solid #f5c2c7';
            fileInfo.innerHTML = `<i class="fas fa-exclamation-triangle"></i> File too large: ${sizeMB} MB (Max: 5MB)`;
            event.target.value = '';
            return;
        }
        
        fileInfo.style.display = 'block';
        fileInfo.style.background = '#d1e7dd';
        fileInfo.style.color = '#0f5132';
        fileInfo.style.border = '1px solid #badbcc';
        fileInfo.innerHTML = `<i class="fas fa-check-circle"></i> Selected: ${file.name} (${sizeMB} MB)`;
        
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
        fileInfo.style.display = 'none';
        @if($menuItem->image)
            preview.innerHTML = `
                <img src="{{ asset($menuItem->image) }}" 
                     style="max-width: 100%; max-height: 200px; border-radius: 6px; object-fit: cover;" 
                     alt="Current image">
            `;
        @else
            preview.innerHTML = `
                <div style="color: #999;">
                    <i class="fas fa-image" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                    <span style="font-size: 13px;">No image uploaded</span>
                </div>
            `;
        @endif
    }
}

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
