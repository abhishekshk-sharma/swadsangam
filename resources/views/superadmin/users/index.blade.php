@extends('layouts.superadmin')

@section('title', 'Super Admins')
@section('header', 'Manage Super Admins')

@section('content')
<div class="mb-6">
    <a href="/superadmin/users/create" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i>Add Super Admin
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($users as $user)
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-800 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="ml-4">
                <h3 class="font-semibold text-lg">{{ $user->name }}</h3>
                <p class="text-gray-600 text-sm">{{ $user->email }}</p>
            </div>
        </div>
        
        <div class="mb-4">
            <span class="px-3 py-1 text-xs rounded {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $user->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <div class="flex space-x-2">
            <a href="/superadmin/users/{{ $user->id }}/edit" class="flex-1 px-4 py-2 bg-blue-600 text-white text-center rounded hover:bg-blue-700 text-sm">
                Edit
            </a>
            <form action="/superadmin/users/{{ $user->id }}" method="POST" class="flex-1" onsubmit="return confirm('Delete this user?')">
                @csrf
                @method('DELETE')
                <button class="w-full px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                    Delete
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-12 text-gray-500">
        No super admins found
    </div>
    @endforelse
</div>
@endsection
