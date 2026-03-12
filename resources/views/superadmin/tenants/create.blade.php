<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Tenant</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-red-600 text-white p-4">
        <div class="container mx-auto">
            <h1 class="text-xl font-bold">🔐 Super Admin Panel</h1>
        </div>
    </nav>

    <div class="container mx-auto p-8">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-2xl font-bold mb-6">Create New Tenant</h2>
            
            <form action="/superadmin/tenants" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Restaurant Name</label>
                    <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Slug (URL identifier)</label>
                    <input type="text" name="slug" class="w-full border rounded px-3 py-2" placeholder="e.g., mcdonalds" required>
                    <p class="text-xs text-gray-500 mt-1">Will be used as: /?tenant=slug</p>
                    @error('slug')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Custom Domain (optional)</label>
                    <input type="text" name="domain" class="w-full border rounded px-3 py-2" placeholder="e.g., orders.mcdonalds.com">
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create</button>
                    <a href="/superadmin/tenants" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
