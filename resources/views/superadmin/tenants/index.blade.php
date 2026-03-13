@extends('layouts.superadmin')

@section('title', 'Tenants')
@section('header', 'Manage Tenants')

@section('content')
<div class="mb-6">
    <a href="/superadmin/tenants/create" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i>Add Tenant
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr class="text-left text-gray-600 text-sm">
                <th class="px-6 py-3">Name</th>
                <th class="px-6 py-3">Slug</th>
                <th class="px-6 py-3">Domain</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Tables</th>
                <th class="px-6 py-3">Menu Items</th>
                <th class="px-6 py-3">Orders</th>
                <th class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenants as $tenant)
            <tr class="border-t hover:bg-gray-50">
                <td class="px-6 py-4">{{ $tenant->name }}</td>
                <td class="px-6 py-4 font-mono text-sm">{{ $tenant->slug }}</td>
                <td class="px-6 py-4">{{ $tenant->domain ?? '-' }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded {{ $tenant->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($tenant->status) }}
                    </span>
                </td>
                <td class="px-6 py-4">{{ $tenant->tables_count }}</td>
                <td class="px-6 py-4">{{ $tenant->menu_items_count }}</td>
                <td class="px-6 py-4">{{ $tenant->orders_count }}</td>
                <td class="px-6 py-4">
                    <a href="/superadmin/tenants/{{ $tenant->id }}/edit" class="text-blue-600 hover:underline mr-3">Edit</a>
                    <form action="/superadmin/tenants/{{ $tenant->id }}" method="POST" class="inline" onsubmit="return confirm('Delete this tenant?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">No tenants found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
