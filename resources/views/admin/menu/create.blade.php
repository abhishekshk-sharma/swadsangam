@extends('layouts.admin')

@section('title', 'Create Menu Item')

@section('content')
<div class="max-w-2xl bg-white p-8 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Create Menu Item</h1>
    
    <form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-gray-700 mb-2">Name</label>
                <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
            </div>
            
            <div>
                <label class="block text-gray-700 mb-2">Category</label>
                <input type="text" name="category" class="w-full border rounded px-3 py-2" required>
            </div>
            
            <div>
                <label class="block text-gray-700 mb-2">Price</label>
                <input type="number" step="0.01" name="price" class="w-full border rounded px-3 py-2" required>
            </div>
            
            <div class="col-span-2">
                <label class="block text-gray-700 mb-2">Description</label>
                <textarea name="description" class="w-full border rounded px-3 py-2" rows="3"></textarea>
            </div>
            
            <div class="col-span-2">
                <label class="block text-gray-700 mb-2">Image</label>
                <input type="file" name="image" class="w-full border rounded px-3 py-2" accept="image/*">
            </div>
            
            <div class="col-span-2">
                <label class="block text-gray-700 mb-2">Menu Category (Optional)</label>
                <select name="menu_category_id" class="w-full border rounded px-3 py-2">
                    <option value="">No Category</option>
                    @foreach($menuCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="is_available" value="1" checked class="mr-2">
                    <span class="text-gray-700">Available</span>
                </label>
            </div>
        </div>
        
        <div class="flex space-x-2 mt-6">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create</button>
            <a href="{{ route('admin.menu.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</a>
        </div>
    </form>
</div>
@endsection
