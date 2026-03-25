@extends('layouts.superadmin')
@section('title', 'Edit Branch')
@section('header', 'Edit Branch')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="/superadmin/branches/{{ $branch->id }}" method="POST">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Tenant</label>
                <input type="text" value="{{ $branch->tenant->name ?? '-' }}"
                    class="w-full px-4 py-2 border rounded bg-gray-50 text-gray-500" disabled>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Branch Name *</label>
                <input type="text" name="name" value="{{ old('name', $branch->name) }}"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Address</label>
                <input type="text" name="address" value="{{ old('address', $branch->address) }}"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600">
                    <span class="text-gray-700 text-sm font-medium">Active</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update Branch</button>
                <a href="/superadmin/branches" class="px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
