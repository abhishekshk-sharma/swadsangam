@extends('layouts.superadmin')

@section('title', 'Super Admin Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-xs uppercase tracking-wide">Total Tenants</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['tenants'] }}</h3>
            <p class="text-green-600 text-xs mt-1">{{ $stats['active_tenants'] }} active</p>
        </div>
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <i class="fas fa-building text-blue-600 text-xl"></i>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-xs uppercase tracking-wide">Branches</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_branches'] }}</h3>
            <p class="text-gray-400 text-xs mt-1">across all tenants</p>
        </div>
        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
            <i class="fas fa-store text-orange-600 text-xl"></i>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-xs uppercase tracking-wide">Admins</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_admins'] }}</h3>
            <p class="text-gray-400 text-xs mt-1">{{ $stats['super_admins'] }} super admins</p>
        </div>
        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
            <i class="fas fa-user-shield text-purple-600 text-xl"></i>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-xs uppercase tracking-wide">Staff</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_employees'] }}</h3>
            <p class="text-gray-400 text-xs mt-1">all roles</p>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <i class="fas fa-users text-green-600 text-xl"></i>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between">
        <div>
            <p class="text-gray-500 text-xs uppercase tracking-wide">Total Orders</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_orders'] }}</h3>
            <p class="text-gray-400 text-xs mt-1">platform-wide</p>
        </div>
        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
            <i class="fas fa-receipt text-yellow-600 text-xl"></i>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between col-span-1 md:col-span-3">
        <div>
            <p class="text-gray-500 text-xs uppercase tracking-wide">Total Revenue (Paid)</p>
            <h3 class="text-3xl font-bold text-green-600 mt-1">₹{{ number_format($stats['total_revenue'], 2) }}</h3>
            <p class="text-gray-400 text-xs mt-1">all tenants combined</p>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <i class="fas fa-rupee-sign text-green-600 text-xl"></i>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="text-lg font-semibold">Recent Tenants</h3>
        <a href="/superadmin/tenants" class="text-blue-600 text-sm hover:underline">View all →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Slug</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Orders</th>
                    <th class="px-6 py-3">Created</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTenants as $tenant)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium">{{ $tenant->name }}</td>
                    <td class="px-6 py-3 font-mono text-sm text-gray-500">{{ $tenant->slug }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 text-xs rounded {{ $tenant->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-3">{{ $tenant->orders_count }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $tenant->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-3">
                        <a href="/superadmin/tenants/{{ $tenant->id }}/edit" class="text-blue-600 hover:underline text-sm">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">No tenants yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
