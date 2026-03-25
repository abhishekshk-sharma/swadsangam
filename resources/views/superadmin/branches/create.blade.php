@extends('layouts.superadmin')
@section('title', 'Add Branch')
@section('header', 'Add Branch')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="/superadmin/branches" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Tenant *</label>
                <select name="tenant_id" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                    <option value="">Select Tenant</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ old('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
                @error('tenant_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Branch Name *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Address</label>
                <input type="text" name="address" value="{{ old('address') }}"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Branch</button>
                <a href="/superadmin/branches" class="px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
