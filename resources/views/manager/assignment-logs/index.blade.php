@extends('layouts.manager')
@section('title', 'Assignment Logs')
@section('content')

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;">
            <i class="fas fa-exchange-alt" style="margin-right:8px;color:#3b82f6;"></i>Order Assignment Logs
        </h1>
        <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">Track all order handoffs between waiters</p>
    </div>

    {{-- Date filter --}}
    <form method="GET" action="{{ route('manager.assignment-logs.index') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <input type="date" name="date" value="{{ request('date') }}"
               style="border:1px solid #d1d5db;border-radius:8px;padding:8px 12px;font-size:13px;color:#374151;background:#fff;">
        <button type="submit"
                style="background:#2563eb;color:#fff;border:none;padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
            <i class="fas fa-filter" style="margin-right:4px;"></i>Filter
        </button>
        @if(request('date'))
            <a href="{{ route('manager.assignment-logs.index') }}"
               style="font-size:13px;color:#6b7280;text-decoration:none;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;background:#fff;">
                Clear
            </a>
        @endif
    </form>
</div>

<div class="content-card">
    @if($logs->count())
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    @php $th = 'padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:2px solid #e5e7eb;text-align:left;white-space:nowrap;'; @endphp
                    <th style="{{ $th }}">Time</th>
                    <th style="{{ $th }}">Order</th>
                    <th style="{{ $th }}">Table</th>
                    <th style="{{ $th }}">From</th>
                    <th style="{{ $th }}"><i class="fas fa-arrow-right" style="margin-right:4px;color:#9ca3af;"></i>To</th>
                    <th style="{{ $th }}">Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                @php $td = 'padding:13px 16px;border-bottom:1px solid #f3f4f6;'; @endphp
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}font-size:13px;color:#6b7280;white-space:nowrap;">
                        <div>{{ $log->created_at->format('d M Y') }}</div>
                        <div style="font-size:12px;margin-top:2px;">{{ $log->created_at->format('h:i A') }}</div>
                    </td>
                    <td style="{{ $td }}">
                        <span style="font-weight:700;font-size:14px;color:#111827;">#{{ $log->order_id }}</span>
                    </td>
                    <td style="{{ $td }}">
                        @if($log->order?->is_parcel)
                            <span style="background:#ea580c;color:#fff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:6px;">📦 Parcel</span>
                        @else
                            <span style="background:#1e3a5f;color:#fff;font-size:11px;font-weight:700;padding:3px 8px;border-radius:6px;">
                                T{{ $log->order?->table?->table_number ?? '?' }}
                            </span>
                        @endif
                    </td>
                    <td style="{{ $td }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:30px;height:30px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#dc2626;flex-shrink:0;">
                                {{ strtoupper(substr($log->fromUser?->name ?? '?', 0, 1)) }}
                            </div>
                            <span style="font-size:13px;font-weight:600;color:#374151;">{{ $log->fromUser?->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td style="{{ $td }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:30px;height:30px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#16a34a;flex-shrink:0;">
                                {{ strtoupper(substr($log->toUser?->name ?? '?', 0, 1)) }}
                            </div>
                            <span style="font-size:13px;font-weight:600;color:#374151;">{{ $log->toUser?->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td style="{{ $td }}font-size:13px;color:#6b7280;font-style:italic;">
                        {{ $log->note ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;">
        {{ $logs->links() }}
    </div>
    @else
    <div style="text-align:center;padding:60px 20px;">
        <div style="font-size:48px;color:#e5e7eb;margin-bottom:16px;"><i class="fas fa-exchange-alt"></i></div>
        <p style="font-size:15px;color:#9ca3af;">No assignment logs {{ request('date') ? 'for this date' : 'yet' }}</p>
    </div>
    @endif
</div>

@endsection
