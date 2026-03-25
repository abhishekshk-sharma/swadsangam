@extends('layouts.superadmin')
@section('title', 'Branches')
@section('header', 'Manage Branches')

@section('content')
<div class="flex items-center justify-between mb-6">
    <form method="GET" class="flex gap-3">
        <select name="tenant_id" class="px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Tenants</option>
            @foreach($tenants as $t)
                <option value="{{ $t->id }}" {{ $selectedTenant == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
        </select>
        <button class="px-4 py-2 bg-gray-100 border rounded text-sm hover:bg-gray-200">Filter</button>
        @if($selectedTenant)
            <a href="/superadmin/branches" class="px-4 py-2 bg-gray-100 border rounded text-sm hover:bg-gray-200">Clear</a>
        @endif
    </form>
    <a href="/superadmin/branches/create" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
        <i class="fas fa-plus mr-2"></i>Add Branch
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="px-6 py-3 text-left">Branch</th>
                <th class="px-6 py-3 text-left">Tenant</th>
                <th class="px-6 py-3 text-left">Address</th>
                <th class="px-6 py-3 text-left">Phone</th>
                <th class="px-6 py-3 text-center">Staff</th>
                <th class="px-6 py-3 text-center">Tables</th>
                <th class="px-6 py-3 text-center">Orders</th>
                <th class="px-6 py-3 text-center">Status</th>
                <th class="px-6 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($branches as $branch)
            <tr class="border-t hover:bg-gray-50">
                <td class="px-6 py-4 font-medium">{{ $branch->name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ $branch->tenant->name ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $branch->address ?? '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $branch->phone ?? '-' }}</td>
                <td class="px-6 py-4 text-center">{{ $branch->employees_count }}</td>
                <td class="px-6 py-4 text-center">{{ $branch->tables_count }}</td>
                <td class="px-6 py-4 text-center">{{ $branch->orders_count }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="px-2 py-1 text-xs rounded {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 flex gap-3">
                    <a href="/superadmin/branches/{{ $branch->id }}/edit" class="text-blue-600 hover:underline text-sm">Edit</a>
                    <form action="/superadmin/branches/{{ $branch->id }}" method="POST" onsubmit="return confirm('Delete this branch?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline text-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-6 py-10 text-center text-gray-400">No branches found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
