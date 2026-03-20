@forelse($staff as $i => $emp)
<tr>
    <td style="color:var(--gray-400);font-size:0.8rem;">{{ $staff->firstItem() + $i }}</td>
    <td><strong>{{ $emp->name }}</strong></td>
    <td>{{ $emp->email }}</td>
    <td>{{ $emp->phone ?? '—' }}</td>
    <td>
        @php $rc = ['chef'=>'badge-info','waiter'=>'badge-neutral','cashier'=>'badge-success'][$emp->role] ?? 'badge-neutral'; @endphp
        <span class="badge {{ $rc }}">{{ ucfirst($emp->role) }}</span>
    </td>
    <td>
        @if($emp->branch)
            <span>{{ $emp->branch->name }}</span>
        @else
            <span class="text-muted">Unassigned</span>
        @endif
    </td>
    <td>
        <span class="badge {{ $emp->is_active ? 'badge-success' : 'badge-error' }}">
            {{ $emp->is_active ? 'Active' : 'Inactive' }}
        </span>
    </td>
    <td>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.staff.edit', $emp->id) }}" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
            <form action="{{ route('admin.staff.destroy', $emp->id) }}" method="POST" onsubmit="return confirm('Delete?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm" style="background:var(--error-light);border-color:var(--error);color:var(--error);"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="8"><div class="empty-state"><i class="fas fa-users"></i><p>No staff found</p></div></td></tr>
@endforelse
