@extends('layouts.superadmin')

@section('title', 'Table Categories')
@section('header', 'Manage Table Categories')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">All Categories</h3>
            </div>
            <div class="p-6">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-600 text-sm">
                            <th class="pb-3">Name</th>
                            <th class="pb-3">Tables Count</th>
                            <th class="pb-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr class="border-t">
                            <td class="py-3">{{ $category->name }}</td>
                            <td class="py-3">{{ $category->tables_count }}</td>
                            <td class="py-3">
                                <form action="/superadmin/table-categories/{{ $category->id }}" method="POST" onsubmit="return confirm('Delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">No categories yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Add New Category</h3>
            <form action="/superadmin/table-categories" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Category Name</label>
                    <input type="text" name="name" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" 
                        placeholder="e.g., VIP, Regular" required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Add Category
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
