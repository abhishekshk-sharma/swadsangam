<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Restaurant Management')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold">Restaurant Manager</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.tables.index') }}" class="text-gray-700 hover:text-gray-900">Tables</a>
                    <a href="{{ route('admin.menu.index') }}" class="text-gray-700 hover:text-gray-900">Menu</a>
                    <a href="{{ route('admin.cook.index') }}" class="text-gray-700 hover:text-gray-900">Cook Panel</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
