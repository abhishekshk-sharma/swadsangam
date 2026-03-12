@extends('layouts.admin')

@section('title', 'Menu Categories')

@section('content')
<div class="max-w-4xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Menu Categories</h1>
        <a href="{{ route('admin.menu.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to Menu</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h2 class="text-lg font-semibold mb-4">Add New Category</h2>
        <form action="{{ route('admin.menu-categories.store') }}" method="POST" class="flex gap-4">
            @csrf
            <input type="text" name="name" placeholder="Category Name (e.g., Pizza, Gujarati Thali)" class="flex-1 border rounded px-3 py-2" required>
            <input type="text" name="description" placeholder="Description (optional)" class="flex-1 border rounded px-3 py-2">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">Add</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categories as $category)
                <tr>
                    <td class="px-6 py-4">{{ $category->name }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $category->description ?? '-' }}</td>
                    <td class="px-6 py-4">{{ $category->menu_items_count }}</td>
                    <td class="px-6 py-4">
                        <form action="{{ route('admin.menu-categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No categories yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
