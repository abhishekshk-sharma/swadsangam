@extends('layouts.admin')

@section('title', 'Edit Menu Item')

@section('content')
<style>
    .edit-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .edit-card {
        background: #fff;
        padding: 32px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e3e6e8;
    }
    .edit-title {
        font-size: 24px;
        font-weight: 700;
        color: #232f3e;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .image-preview-container {
        margin-top: 16px;
        padding: 16px;
        background: #f9f9f9;
        border-radius: 8px;
        border: 2px dashed #d5d9d9;
    }
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .file-info {
        font-size: 13px;
        margin-top: 8px;
        padding: 8px 12px;
        border-radius: 4px;
    }
    .file-info.success {
        background: #d1e7dd;
        color: #0f5132;
    }
    .file-info.error {
        background: #f8d7da;
        color: #842029;
    }
    .checkbox-container {
        background: #f9f9f9;
        padding: 16px;
        border-radius: 8px;
        border: 1px solid #e3e6e8;
    }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        font-weight: 600;
        color: #232f3e;
    }
    .checkbox-label input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
</style>

<div class="edit-container">
    <div class="edit-card">
        <h1 class="edit-title">
            <i class="fas fa-edit" style="color: #ff9900;"></i>
            Edit Menu Item
        </h1>
        
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('admin.menu.update.post', $menuItem->id) }}" method="POST" enctype="multipart/form-data" id="menuForm">
            @csrf
            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label">
                        <i class="fas fa-utensils me-2"></i>Item Name
                    </label>
                    <input type="text" name="name" value="{{ old('name', $menuItem->name) }}" 
                           class="form-control" placeholder="e.g., Butter Chicken" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-list me-2"></i>Category (Old)
                    </label>
                    <input type="text" name="category" value="{{ old('category', $menuItem->category) }}" 
                           class="form-control" placeholder="e.g., Main Course" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-rupee-sign me-2"></i>Price
                    </label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $menuItem->price) }}" 
                           class="form-control" placeholder="0.00" required>
                </div>
                
                <div class="col-12">
                    <label class="form-label">
                        <i class="fas fa-align-left me-2"></i>Description (Optional)
                    </label>
                    <textarea name="description" class="form-control" rows="3" 
                              placeholder="Describe your dish...">{{ old('description', $menuItem->description) }}</textarea>
                </div>
                
                <div class="col-12">
                    <label class="form-label">
                        <i class="fas fa-image me-2"></i>Item Image (Max 5MB)
                    </label>
                    <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                    <div id="fileInfo" class="file-info" style="display: none;"></div>
                    
                    @if($menuItem->image)
                        <div class="image-preview-container">
                            <label class="form-label mb-2">Current Image:</label>
                            <div>
                                <img src="{{ asset($menuItem->image) }}" class="image-preview" alt="Current image">
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="col-12">
                    <label class="form-label">
                        <i class="fas fa-tags me-2"></i>Menu Category (Optional)
                    </label>
                    <select name="menu_category_id" class="form-select">
                        <option value="">No Category</option>
                        @foreach($menuCategories as $category)
                            <option value="{{ $category->id }}" 
                                {{ old('menu_category_id', $menuItem->menu_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-12">
                    <div class="checkbox-container">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_available" value="1" 
                                {{ old('is_available', $menuItem->is_available) ? 'checked' : '' }}>
                            <span>
                                <i class="fas fa-check-circle me-2" style="color: #067d62;"></i>
                                Item is Available for Orders
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary" style="flex: 1;">
                    <i class="fas fa-save me-2"></i>Update Menu Item
                </button>
                <a href="{{ route('admin.menu.index') }}" class="btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const info = document.getElementById('fileInfo');
    
    if (file) {
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        info.style.display = 'block';
        
        if (file.size > 5 * 1024 * 1024) {
            info.className = 'file-info error';
            info.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Selected: ${file.name} (${sizeMB} MB) - File exceeds 5MB limit!`;
        } else {
            info.className = 'file-info success';
            info.innerHTML = `<i class="fas fa-check-circle me-2"></i>Selected: ${file.name} (${sizeMB} MB)`;
        }
    } else {
        info.style.display = 'none';
    }
});
</script>
@endsection
