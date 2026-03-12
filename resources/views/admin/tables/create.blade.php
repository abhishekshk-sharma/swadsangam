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
                    <i class="fas fa-hashtag me-2"></i>Table Number / Name
                </label>
                <input type="text" name="table_number" class="form-control" 
                       placeholder="e.g., Table 1, VIP-A, etc." 
                       value="{{ old('table_number') }}" required>
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
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-tag me-2"></i>Category (Optional)
                </label>
                <select name="category_id" class="form-select">
                    <option value="">No Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
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
@endsection
