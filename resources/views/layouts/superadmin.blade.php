<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin Panel')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #1e40af;
            --secondary-blue: #3b82f6;
            --light-blue: #60a5fa;
            --dark-blue: #1e3a8a;
            --accent-blue: #2563eb;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-blue-900 to-blue-700 text-white">
            <div class="p-6">
                <h1 class="text-2xl font-bold">Super Admin</h1>
                <p class="text-blue-200 text-sm">Swad Sangam</p>
            </div>
            
            <nav class="mt-6">
                <a href="/superadmin/dashboard" class="flex items-center px-6 py-3 hover:bg-blue-800 {{ request()->is('superadmin/dashboard') ? 'bg-blue-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-chart-line w-5"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="/superadmin/tenants" class="flex items-center px-6 py-3 hover:bg-blue-800 {{ request()->is('superadmin/tenants*') ? 'bg-blue-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-building w-5"></i>
                    <span class="ml-3">Tenants</span>
                </a>
                <a href="/superadmin/users" class="flex items-center px-6 py-3 hover:bg-blue-800 {{ request()->is('superadmin/users*') ? 'bg-blue-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-user-shield w-5"></i>
                    <span class="ml-3">Admin Management</span>
                </a>
                <a href="/superadmin/table-categories" class="flex items-center px-6 py-3 hover:bg-blue-800 {{ request()->is('superadmin/table-categories*') ? 'bg-blue-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-layer-group w-5"></i>
                    <span class="ml-3">Table Categories</span>
                </a>
                <a href="/superadmin/menu-categories" class="flex items-center px-6 py-3 hover:bg-blue-800 {{ request()->is('superadmin/menu-categories*') ? 'bg-blue-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-utensils w-5"></i>
                    <span class="ml-3">Menu Categories</span>
                </a>
                <a href="/superadmin/branches" class="flex items-center px-6 py-3 hover:bg-blue-800 {{ request()->is('superadmin/branches*') ? 'bg-blue-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-store w-5"></i>
                    <span class="ml-3">Branches</span>
                </a>
                <a href="/superadmin/staff" class="flex items-center px-6 py-3 hover:bg-blue-800 {{ request()->is('superadmin/staff*') ? 'bg-blue-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-users w-5"></i>
                    <span class="ml-3">Staff</span>
                </a>
                <a href="/superadmin/reports" class="flex items-center px-6 py-3 hover:bg-blue-800 {{ request()->is('superadmin/reports*') ? 'bg-blue-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span class="ml-3">Reports</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">{{ Auth::guard('super_admin')->user()->name ?? 'Super Admin' }}</span>
                        <form action="{{ route('superadmin.logout') }}" method="POST">
                            @csrf
                            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
