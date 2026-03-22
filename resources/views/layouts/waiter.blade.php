<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Mobile Layout -->
    <div class="flex flex-col h-screen">
        <!-- Top Bar -->
        <div class="bg-indigo-900 text-white p-4 shadow-lg">
            @php $branchName = current_user()->branch_id ? \App\Models\Branch::find(current_user()->branch_id)?->name : null; @endphp
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-lg font-bold">{{ $tenant->name }}</h1>
                    <p class="text-xs text-indigo-300">{{ current_user()->name ?? 'User' }}
                        @if($branchName) &nbsp;·&nbsp; <span class="text-indigo-200">{{ $branchName }}</span>@endif
                    </p>
                </div>
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto pb-20">
            <div class="p-4">
                @yield('content')
            </div>
        </div>
        
        <!-- Bottom Navigation -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg">
            <div class="flex justify-around">
                <a href="/waiter/dashboard" class="flex-1 text-center py-3 {{ request()->is('waiter/dashboard') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600' }}">
                    <div class="text-2xl">■</div>
                    <div class="text-xs">Dashboard</div>
                </a>
                <a href="/waiter/orders/create" class="flex-1 text-center py-3 {{ request()->is('waiter/orders/create') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600' }}">
                    <div class="text-2xl">+</div>
                    <div class="text-xs">New Order</div>
                </a>
                <a href="/waiter/orders" class="flex-1 text-center py-3 {{ request()->is('waiter/orders') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600' }}">
                    <div class="text-2xl">▦</div>
                    <div class="text-xs">Orders</div>
                </a>
                <a href="/profile" class="flex-1 text-center py-3 {{ request()->is('profile*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600' }}">
                    <div class="text-2xl">👤</div>
                    <div class="text-xs">Profile</div>
                </a>
            </div>
        </div>
    </div>
<script>window.ORDER_POLL = { panel: 'waiter' };</script>
<script src="/js/order-poll.js"></script>
</body>
</html>
