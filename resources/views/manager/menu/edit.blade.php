@extends('layouts.manager')
@section('title', 'Edit Menu Item')
@section('content')

<div class="content-card" style="max-width:900px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-edit"></i> Edit Menu Item</div>
        <a href="{{ route('manager.menu.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-error"><ul class="mb-0" style="padding-left:20px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('manager.menu.update', $menuItem->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Menu Category *</label>
                    <select name="menu_category_id" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        @foreach($menuCategories as $category)
                            <option value="{{ $category->id }}" {{ old('menu_category_id', $menuItem->menu_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Item Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $menuItem->name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Price *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $menuItem->price) }}" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $menuItem->description) }}</textarea>
                </div>
                <div class="col-12">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', $menuItem->is_available) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--blue-600);">
                        <strong>Show on menu</strong>
                    </label>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
                <a href="{{ route('manager.menu.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
