@forelse($managers as $i => $manager)
<tr>
    <td style="color:var(--gray-400);font-size:0.8rem;">{{ $managers->firstItem() + $i }}</td>
    <td><strong>{{ $manager->name }}</strong></td>
    <td>{{ $manager->email }}</td>
    <td>{{ $manager->phone ?? '—' }}</td>
    <td>{{ $manager->branch->name ?? '—' }}</td>
    <td>
        @php $assigned = $manager->branch_id ? ($assignedByBranch[$manager->branch_id] ?? collect()) : collect(); @endphp
        @if($assigned->count())
            @foreach($assigned as $emp)
                <span class="badge badge-info" style="margin:1px;">{{ $emp->name }} <span style="font-size:0.65rem;">({{ ucfirst($emp->role) }})</span></span>
            @endforeach
        @else
            <span class="text-muted" style="font-size:0.8rem;">None</span>
        @endif
    </td>
    <td>
        <span class="badge {{ $manager->is_active ? 'badge-success' : 'badge-error' }}">
            {{ $manager->is_active ? 'Active' : 'Inactive' }}
        </span>
    </td>
    <td>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.managers.edit', $manager->id) }}" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
            <form action="{{ route('admin.managers.destroy', $manager->id) }}" method="POST" onsubmit="return confirm('Delete this manager?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm" style="background:var(--error-light);border-color:var(--error);color:var(--error);"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="8"><div class="empty-state"><i class="fas fa-user-tie"></i><p>No managers found</p></div></td></tr>
@endforelse
