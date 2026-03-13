@extends('layouts.superadmin')

@section('title', 'Super Admin Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Tenants</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['tenants'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-building text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Active Tenants</p>
                <h3 class="text-3xl font-bold text-green-600 mt-2">{{ $stats['active_tenants'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Users</p>
                <h3 class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['total_users'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Super Admins</p>
                <h3 class="text-3xl font-bold text-indigo-600 mt-2">{{ $stats['super_admins'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-shield text-indigo-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold">Recent Tenants</h3>
    </div>
    <div class="p-6">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-600 text-sm">
                    <th class="pb-3">Name</th>
                    <th class="pb-3">Slug</th>
                    <th class="pb-3">Domain</th>
                    <th class="pb-3">Status</th>
                    <th class="pb-3">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTenants as $tenant)
                <tr class="border-t">
                    <td class="py-3">{{ $tenant->name }}</td>
                    <td class="py-3">{{ $tenant->slug }}</td>
                    <td class="py-3">{{ $tenant->domain ?? '-' }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 text-xs rounded {{ $tenant->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </td>
                    <td class="py-3 text-sm text-gray-600">{{ $tenant->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-4 text-center text-gray-500">No tenants yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
