<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-2xl">
            <h1 class="text-3xl font-bold text-center mb-6">🔐 Super Admin</h1>
            
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ $errors->first() }}
                </div>
            @endif
            
            <form action="/superadmin/login" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" class="w-full border rounded px-3 py-2" required autofocus>
                </div>
                
                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    Login
                </button>
            </form>
            
            <p class="text-xs text-gray-500 text-center mt-4">Default: superadmin123</p>
        </div>
    </div>
</body>
</html>
