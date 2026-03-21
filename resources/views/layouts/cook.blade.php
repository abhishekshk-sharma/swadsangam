<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/pusher.min.js"></script>
    <script src="/js/echo.iife.js"></script>
    <script src="/js/chef-notifications.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Mobile Layout -->
    <div class="flex flex-col h-screen">
        <!-- Top Bar -->
        <div class="bg-blue-900 text-white p-4 shadow-lg">
            @php $branchName = current_user()->branch_id ? \App\Models\Branch::find(current_user()->branch_id)?->name : null; @endphp
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-lg font-bold">{{ $tenant->name }}</h1>
                    <p class="text-xs text-blue-300">{{ current_user()->name ?? 'User' }} - Chef
                        @if($branchName) &nbsp;·&nbsp; <span class="text-blue-200">{{ $branchName }}</span>@endif
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
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
        
        <!-- Bottom Navigation -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg">
            <div class="flex justify-around">
                <a href="{{ route('cook.dashboard') }}" class="flex-1 text-center py-3 {{ request()->routeIs('cook.dashboard') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }}">
                    <div class="text-2xl">■</div>
                    <div class="text-xs">Dashboard</div>
                </a>
                <a href="{{ route('cook.orders.pending') }}" class="flex-1 text-center py-3 {{ request()->routeIs('cook.orders.pending') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }}">
                    <div class="text-2xl">⏱</div>
                    <div class="text-xs">Orders</div>
                </a>
                <a href="{{ route('cook.orders.completed') }}" class="flex-1 text-center py-3 {{ request()->routeIs('cook.orders.completed') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }}">
                    <div class="text-2xl">✓</div>
                    <div class="text-xs">Ready</div>
                </a>
                <a href="/profile" class="flex-1 text-center py-3 {{ request()->is('profile*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }}">
                    <div class="text-2xl">👤</div>
                    <div class="text-xs">Profile</div>
                </a>
            </div>
        </div>
    </div>
<script>
window.ORDER_WS = {
    panel:       'cook',
    tenantId:    {{ $tenant->id ?? 0 }},
    reverbKey:   '{{ config('broadcasting.connections.reverb.key') }}',
    reverbHost:  '{{ env('REVERB_HOST', 'localhost') }}',
    reverbPort:  {{ env('REVERB_PORT', 8080) }},
    reverbScheme:'{{ env('REVERB_SCHEME', 'http') }}',
};
</script>
<script src="/js/order-ws.js"></script>
</body>
</html>
