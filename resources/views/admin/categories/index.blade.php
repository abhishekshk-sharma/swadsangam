@extends('layouts.admin')

@section('title', 'Table Categories')

@section('content')
<div class="content-card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="card-title mb-0"><i class="fas fa-layer-group me-2"></i>Table Categories</h1>
            <a href="{{ route('admin.tables.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Tables
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="content-card mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #ff9900 0%, #ec8b00 100%);">
                <h2 class="card-title mb-0" style="color: white; font-size: 16px;">
                    <i class="fas fa-plus-circle me-2"></i>Add New Category
                </h2>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., AC, Non-AC, VIP" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" name="description" class="form-control" placeholder="Brief description">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn-primary w-100">
                                <i class="fas fa-plus me-2"></i>Add
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>All Categories
                </h2>
            </div>
            <div class="card-body p-0">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Tables Count</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #232f3e;">
                                    <i class="fas fa-tag me-2" style="color: #ff9900;"></i>{{ $category->name }}
                                </div>
                            </td>
                            <td style="color: #666;">
                                {{ $category->description ?? '-' }}
                            </td>
                            <td>
                                @if($category->tenant_id)
                                    <span class="badge-custom" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                        <i class="fas fa-user me-1"></i>Custom
                                    </span>
                                @else
                                    <span class="badge-custom" style="background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db;">
                                        <i class="fas fa-globe me-1"></i>Global
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-custom" style="background: #fff3e0; color: #e65100;">
                                    {{ $category->tables_count }} {{ Str::plural('table', $category->tables_count) }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if($category->tenant_id)
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" style="padding: 8px 16px;">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                @else
                                    <span style="color: #9ca3af; font-size: 13px; font-style: italic;">
                                        <i class="fas fa-lock me-1"></i>Protected
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 48px; color: #9ca3af;">
                                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                                <div style="font-size: 16px; font-weight: 600;">No categories yet</div>
                                <div style="font-size: 14px; margin-top: 8px;">Create your first category using the form above</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
