@extends('layouts.superadmin')
@section('title', 'Add Staff')
@section('header', 'Add Staff')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="/superadmin/staff" method="POST">
            @csrf

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Tenant *</label>
                    <select name="tenant_id" id="tenant_id" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                        <option value="">Select Tenant</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ old('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    @error('tenant_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Branch</label>
                    <select name="branch_id" id="branch_id" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Select Branch</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Password *</label>
                    <input type="password" name="password"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Role *</label>
                    <select name="role" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                        <option value="">Select Role</option>
                        @foreach(['manager','waiter','chef','cashier'] as $r)
                            <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Staff</button>
                <a href="/superadmin/staff" class="px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('tenant_id').addEventListener('change', function () {
    const tenantId = this.value;
    const branchSelect = document.getElementById('branch_id');
    branchSelect.innerHTML = '<option value="">Select Branch</option>';
    if (!tenantId) return;
    fetch('/superadmin/staff/branches/' + tenantId)
        .then(r => r.json())
        .then(data => {
            data.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = b.name;
                branchSelect.appendChild(opt);
            });
        });
});
</script>
@endsection
