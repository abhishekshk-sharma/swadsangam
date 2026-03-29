@extends('layouts.admin')

@section('title', 'Order Assignment Logs')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:#232f3e;"><i class="fas fa-exchange-alt me-2"></i>Order Assignment Logs</h1>
        <p style="font-size:13px;color:#6b7280;">Track all order handoffs between waiters</p>
    </div>
    <form method="GET" action="{{ route('admin.assignment-logs.index') }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
        @if(isset($branches) && $branches->count() > 0)
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;">Branch</label>
            <select name="branch_id" style="padding:7px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;color:#374151;min-width:160px;">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div style="display:flex;flex-direction:column;gap:4px;">
            <label style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;">Date</label>
            <input type="date" name="date" value="{{ request('date') }}"
                   style="padding:7px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;">
        </div>
        <div style="display:flex;gap:8px;align-items:flex-end;">
            <button type="submit" style="background:#2563eb;color:#fff;border:none;padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;"><i class="fas fa-filter" style="margin-right:4px;"></i>Filter</button>
            @if(request('date') || request('branch_id'))
                <a href="{{ route('admin.assignment-logs.index') }}" style="padding:8px 14px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">Clear</a>
            @endif
        </div>
    </form>
</div>

<div class="content-card">
    <div class="table-scroll-wrap">
        @if($logs->count())
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb;">
                    <th style="padding:12px 16px;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;text-align:left;">Time</th>
                    <th style="padding:12px 16px;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;text-align:left;">Order</th>
                    <th style="padding:12px 16px;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;text-align:left;">Table</th>
                    <th style="padding:12px 16px;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;text-align:left;">Branch</th>
                    <th style="padding:12px 16px;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;text-align:left;">From</th>
                    <th style="padding:12px 16px;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;text-align:left;">To</th>
                    <th style="padding:12px 16px;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;text-align:left;">Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:12px 16px;font-size:13px;color:#6b7280;white-space:nowrap;">
                        {{ $log->created_at->format('d M, h:i A') }}
                    </td>
                    <td style="padding:12px 16px;">
                        <span style="font-weight:700;font-size:14px;">#{{ $log->order_id }}</span>
                    </td>
                    <td style="padding:12px 16px;font-size:13px;">
                        @if($log->order?->is_parcel)
                            <span style="background:#ea580c;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;">📦 Parcel</span>
                        @else
                            <span style="background:#1e3a5f;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;">
                                T{{ $log->order?->table?->table_number ?? '?' }}
                            </span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;font-size:13px;color:#6b7280;">
                        {{ $log->order?->branch?->name ?? '—' }}
                    </td>
                    <td style="padding:12px 16px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:30px;height:30px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#dc2626;">
                                {{ strtoupper(substr($log->fromUser?->name ?? '?', 0, 1)) }}
                            </div>
                            <span style="font-size:13px;font-weight:600;">{{ $log->fromUser?->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td style="padding:12px 16px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:30px;height:30px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#16a34a;">
                                {{ strtoupper(substr($log->toUser?->name ?? '?', 0, 1)) }}
                            </div>
                            <span style="font-size:13px;font-weight:600;">{{ $log->toUser?->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td style="padding:12px 16px;font-size:13px;color:#6b7280;font-style:italic;">
                        {{ $log->note ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding:16px;">
            {{ $logs->links() }}
        </div>
        @else
        <div style="text-align:center;padding:60px 20px;color:#9ca3af;">
            <i class="fas fa-exchange-alt" style="font-size:48px;color:#e5e7eb;display:block;margin-bottom:16px;"></i>
            <p style="font-size:15px;">No assignment logs {{ request('date') ? 'for this date' : 'yet' }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
