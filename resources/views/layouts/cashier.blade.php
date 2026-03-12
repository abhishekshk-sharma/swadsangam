<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cashier Panel')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/order-updates.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Top Bar -->
    <div class="bg-purple-600 text-white p-4 fixed top-0 left-0 right-0 z-10 shadow-lg">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="font-bold text-lg">💰 Swad Sangam</h1>
                <p class="text-xs opacity-90">Cashier Panel</p>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-purple-700 hover:bg-purple-800 px-3 py-1 rounded text-sm">
                    Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="pt-20 pb-20 px-4" style="min-height: 100vh;">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bottom Navigation -->
    <div class="bg-white border-t border-gray-200 fixed bottom-0 left-0 right-0 z-10 shadow-lg">
        <div class="grid grid-cols-4 gap-1">
            <a href="{{ route('cashier.dashboard') }}" class="flex flex-col items-center py-3 {{ request()->routeIs('cashier.dashboard') ? 'text-purple-600 bg-purple-50' : 'text-gray-600' }}">
                <span class="text-xl mb-1">◈</span>
                <span class="text-xs">Dashboard</span>
            </a>
            <a href="{{ route('cashier.payments.index') }}" class="flex flex-col items-center py-3 {{ request()->routeIs('cashier.payments.index') ? 'text-purple-600 bg-purple-50' : 'text-gray-600' }}">
                <span class="text-xl mb-1">₹</span>
                <span class="text-xs">Payments</span>
            </a>
            <a href="{{ route('cashier.payments.history') }}" class="flex flex-col items-center py-3 {{ request()->routeIs('cashier.payments.history') ? 'text-purple-600 bg-purple-50' : 'text-gray-600' }}">
                <span class="text-xl mb-1">✓</span>
                <span class="text-xs">History</span>
            </a>
            <a href="/profile" class="flex flex-col items-center py-3 {{ request()->is('profile*') ? 'text-purple-600 bg-purple-50' : 'text-gray-600' }}">
                <span class="text-xl mb-1">👤</span>
                <span class="text-xs">Profile</span>
            </a>
        </div>
    </div>
</body>
</html>
