@extends('layouts.superadmin')
@section('title', 'Edit Staff')
@section('header', 'Edit Staff')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="/superadmin/staff/{{ $employee->id }}" method="POST">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Tenant *</label>
                    <select name="tenant_id" id="tenant_id" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                        <option value="">Select Tenant</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ old('tenant_id', $employee->tenant_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    @error('tenant_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Branch</label>
                    <select name="branch_id" id="branch_id" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Select Branch</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id', $employee->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $employee->name) }}"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email', $employee->email) }}"
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">New Password <span class="text-gray-400 font-normal">(leave blank to keep)</span></label>
                    <input type="password" name="password"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Role *</label>
                    <select name="role" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                        @foreach(['manager','waiter','chef','cashier'] as $r)
                            <option value="{{ $r }}" {{ old('role', $employee->role) === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600">
                    <span class="text-gray-700 text-sm font-medium">Active</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update Staff</button>
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
