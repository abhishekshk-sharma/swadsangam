@extends('layouts.superadmin')
@section('title', 'Staff')
@section('header', 'Manage Staff')

@section('content')
<div class="flex flex-wrap items-end gap-3 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 flex-1">
        <select name="tenant_id" class="px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Tenants</option>
            @foreach($tenants as $t)
                <option value="{{ $t->id }}" {{ $selectedTenant == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
        </select>
        <select name="role" class="px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Roles</option>
            @foreach(['manager','waiter','chef','cashier'] as $r)
                <option value="{{ $r }}" {{ $selectedRole === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ $search }}" placeholder="Name or email…"
            class="px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button class="px-4 py-2 bg-gray-100 border rounded text-sm hover:bg-gray-200">Filter</button>
        @if($selectedTenant || $selectedRole || $search)
            <a href="/superadmin/staff" class="px-4 py-2 bg-gray-100 border rounded text-sm hover:bg-gray-200">Clear</a>
        @endif
    </form>
    <a href="/superadmin/staff/create" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm whitespace-nowrap">
        <i class="fas fa-plus mr-2"></i>Add Staff
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="px-6 py-3 text-left">Name</th>
                <th class="px-6 py-3 text-left">Email</th>
                <th class="px-6 py-3 text-left">Phone</th>
                <th class="px-6 py-3 text-left">Role</th>
                <th class="px-6 py-3 text-left">Tenant</th>
                <th class="px-6 py-3 text-left">Branch</th>
                <th class="px-6 py-3 text-center">Status</th>
                <th class="px-6 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staff as $emp)
            <tr class="border-t hover:bg-gray-50">
                <td class="px-6 py-3 font-medium">{{ $emp->name }}</td>
                <td class="px-6 py-3 text-sm text-gray-600">{{ $emp->email }}</td>
                <td class="px-6 py-3 text-sm text-gray-500">{{ $emp->phone ?? '-' }}</td>
                <td class="px-6 py-3">
                    @php $roleColors = ['manager'=>'purple','waiter'=>'blue','chef'=>'orange','cashier'=>'green']; $c = $roleColors[$emp->role] ?? 'gray'; @endphp
                    <span class="px-2 py-1 text-xs rounded bg-{{ $c }}-100 text-{{ $c }}-800">{{ ucfirst($emp->role) }}</span>
                </td>
                <td class="px-6 py-3 text-sm text-gray-600">{{ $emp->tenant->name ?? '-' }}</td>
                <td class="px-6 py-3 text-sm text-gray-500">{{ $emp->branch->name ?? '-' }}</td>
                <td class="px-6 py-3 text-center">
                    <span class="px-2 py-1 text-xs rounded {{ $emp->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $emp->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-3 flex gap-3">
                    <a href="/superadmin/staff/{{ $emp->id }}/edit" class="text-blue-600 hover:underline text-sm">Edit</a>
                    <form action="/superadmin/staff/{{ $emp->id }}" method="POST" onsubmit="return confirm('Delete this staff?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline text-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-10 text-center text-gray-400">No staff found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($staff->hasPages())
    <div class="px-6 py-4 border-t">
        {{ $staff->links() }}
    </div>
    @endif
</div>
@endsection
