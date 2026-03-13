@extends('layouts.superadmin')

@section('title', 'Create Tenant')
@section('header', 'Create New Tenant')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="/superadmin/tenants" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Restaurant Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" 
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Slug (URL identifier) *</label>
                <input type="text" name="slug" value="{{ old('slug') }}" 
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" 
                    placeholder="e.g., demo, restaurant1" required>
                <p class="text-gray-500 text-sm mt-1">Only letters, numbers, dashes and underscores</p>
                @error('slug')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Domain (optional)</label>
                <input type="text" name="domain" value="{{ old('domain') }}" 
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" 
                    placeholder="e.g., restaurant.com">
                @error('domain')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Create Tenant
                </button>
                <a href="/superadmin/tenants" class="px-6 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
