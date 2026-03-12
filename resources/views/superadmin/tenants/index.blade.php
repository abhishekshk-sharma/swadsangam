<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin - Tenants</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-red-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">🔐 Super Admin Panel</h1>
            <a href="/superadmin/logout" class="bg-red-800 px-4 py-2 rounded hover:bg-red-900">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold">Manage Tenants</h2>
            <a href="/superadmin/tenants/create" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">+ Add Tenant</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stats</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($tenants as $tenant)
                    <tr>
                        <td class="px-6 py-4">{{ $tenant->id }}</td>
                        <td class="px-6 py-4 font-semibold">{{ $tenant->name }}</td>
                        <td class="px-6 py-4">
                            <code class="bg-gray-100 px-2 py-1 rounded">{{ $tenant->slug }}</code>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded {{ $tenant->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $tenant->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            📋 {{ $tenant->tables_count }} tables<br>
                            🍕 {{ $tenant->menu_items_count }} items<br>
                            📦 {{ $tenant->orders_count }} orders
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="/?tenant={{ $tenant->slug }}" target="_blank" class="text-blue-600 hover:text-blue-800">View</a>
                                <a href="/superadmin/tenants/{{ $tenant->id }}/edit" class="text-green-600 hover:text-green-800">Edit</a>
                                <form action="/superadmin/tenants/{{ $tenant->id }}" method="POST" class="inline" onsubmit="return confirm('Delete this tenant?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
