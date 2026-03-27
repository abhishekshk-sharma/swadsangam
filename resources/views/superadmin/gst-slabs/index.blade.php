@extends('layouts.superadmin')
@section('title', 'GST Slabs')
@section('header', 'GST Slabs')

@section('content')
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
@endif

{{-- ── GST Slabs Table ── --}}
<div class="bg-white rounded-lg shadow mb-8">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="text-lg font-semibold">GST Slabs</h3>
        <button onclick="document.getElementById('createSlabForm').classList.toggle('hidden')"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-semibold">
            + Add Slab
        </button>
    </div>

    {{-- Create form --}}
    <div id="createSlabForm" class="hidden px-6 py-4 bg-gray-50 border-b">
        <form method="POST" action="{{ route('superadmin.gst-slabs.store') }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Name</label>
                <input type="text" name="name" required placeholder="e.g. 5% GST"
                       class="border border-gray-300 rounded px-3 py-2 text-sm w-52">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">CGST %</label>
                <input type="number" name="cgst_rate" step="0.01" min="0" max="50" required placeholder="2.50"
                       class="border border-gray-300 rounded px-3 py-2 text-sm w-24">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">SGST %</label>
                <input type="number" name="sgst_rate" step="0.01" min="0" max="50" required placeholder="2.50"
                       class="border border-gray-300 rounded px-3 py-2 text-sm w-24">
            </div>
            <div class="flex items-center gap-2 pb-1">
                <input type="checkbox" name="is_active" value="1" checked id="createActive">
                <label for="createActive" class="text-sm text-gray-600">Active</label>
            </div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm font-semibold">Save</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">CGST</th>
                    <th class="px-6 py-3">SGST</th>
                    <th class="px-6 py-3">Total</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slabs as $slab)
                <tr class="border-t hover:bg-gray-50" id="slabRow{{ $slab->id }}">
                    <td class="px-6 py-3 font-medium" id="slabName{{ $slab->id }}">{{ $slab->name }}</td>
                    <td class="px-6 py-3">{{ $slab->cgst_rate }}%</td>
                    <td class="px-6 py-3">{{ $slab->sgst_rate }}%</td>
                    <td class="px-6 py-3 font-semibold">{{ $slab->total_rate }}%</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 text-xs rounded {{ $slab->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $slab->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-3 flex gap-2">
                        <button onclick="toggleEditSlab({{ $slab->id }})"
                                class="text-blue-600 hover:underline text-sm">Edit</button>
                        <form method="POST" action="{{ route('superadmin.gst-slabs.destroy', $slab) }}"
                              onsubmit="return confirm('Delete this slab?')" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline text-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                {{-- Inline edit row --}}
                <tr id="editRow{{ $slab->id }}" class="hidden bg-blue-50">
                    <td colspan="6" class="px-6 py-3">
                        <form method="POST" action="{{ route('superadmin.gst-slabs.update', $slab) }}"
                              class="flex flex-wrap gap-3 items-end">
                            @csrf @method('PUT')
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Name</label>
                                <input type="text" name="name" value="{{ $slab->name }}" required
                                       class="border border-gray-300 rounded px-3 py-2 text-sm w-52">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">CGST %</label>
                                <input type="number" name="cgst_rate" step="0.01" value="{{ $slab->cgst_rate }}" required
                                       class="border border-gray-300 rounded px-3 py-2 text-sm w-24">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">SGST %</label>
                                <input type="number" name="sgst_rate" step="0.01" value="{{ $slab->sgst_rate }}" required
                                       class="border border-gray-300 rounded px-3 py-2 text-sm w-24">
                            </div>
                            <div class="flex items-center gap-2 pb-1">
                                <input type="checkbox" name="is_active" value="1" {{ $slab->is_active ? 'checked' : '' }} id="editActive{{ $slab->id }}">
                                <label for="editActive{{ $slab->id }}" class="text-sm text-gray-600">Active</label>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-semibold">Update</button>
                            <button type="button" onclick="toggleEditSlab({{ $slab->id }})"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">Cancel</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No GST slabs yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Per-Tenant GST Assignment ── --}}
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold">Tenant GST Settings</h3>
        <p class="text-sm text-gray-500 mt-1">Assign a GST slab and mode to each tenant. Leave blank to disable GST for that tenant.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                    <th class="px-6 py-3">Tenant</th>
                    <th class="px-6 py-3">Current Slab</th>
                    <th class="px-6 py-3">Mode</th>
                    <th class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tenants as $tenant)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium">{{ $tenant->name }}</td>
                    <td class="px-6 py-3 text-sm text-gray-500">
                        {{ $tenant->gstSlab?->name ?? '— None —' }}
                    </td>
                    <td class="px-6 py-3 text-sm">
                        @if($tenant->gst_mode)
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $tenant->gst_mode === 'excluded' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($tenant->gst_mode) }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-3">
                        <form method="POST" action="{{ route('superadmin.gst-slabs.assign', $tenant) }}"
                              class="flex flex-wrap gap-2 items-center">
                            @csrf @method('PATCH')
                            <select name="gst_slab_id" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                <option value="">— No GST —</option>
                                @foreach($slabs->where('is_active', true) as $slab)
                                    <option value="{{ $slab->id }}" {{ $tenant->gst_slab_id == $slab->id ? 'selected' : '' }}>
                                        {{ $slab->name }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="gst_mode" class="border border-gray-300 rounded px-2 py-1 text-sm">
                                <option value="included" {{ $tenant->gst_mode === 'included' ? 'selected' : '' }}>Included (in price)</option>
                                <option value="excluded" {{ $tenant->gst_mode === 'excluded' ? 'selected' : '' }}>Excluded (add on top)</option>
                            </select>
                            <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-semibold">Save</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleEditSlab(id) {
    document.getElementById('editRow' + id).classList.toggle('hidden');
}
</script>
@endsection
