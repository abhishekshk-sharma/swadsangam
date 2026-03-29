@extends('layouts.admin')
@section('title', 'Cash Handovers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="section-title"><i class="fas fa-cash-register me-2"></i>Cash Handover Reports</h1>
        <p style="font-size:13px;color:var(--gray-500);">{{ now()->format('l, F j, Y') }}</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
@endif

{{-- Filter Card --}}
<div class="content-card" style="margin-bottom:20px;">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;border-radius:12px 12px 0 0;">
        <strong style="font-size:14px;color:#374151;"><i class="fas fa-filter" style="margin-right:8px;color:#3b82f6;"></i>Filter Handovers</strong>
    </div>
    <div style="padding:20px;">
        <form method="GET" action="{{ route('admin.handover.index') }}" id="handoverFilterForm">

            {{-- Period tabs --}}
            <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
                @foreach(['today'=>'Today','month'=>'Monthly','custom'=>'Custom Range'] as $val=>$label)
                <button type="button" onclick="setHFilterType('{{ $val }}')" id="hftab-{{ $val }}"
                    style="padding:8px 20px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:2px solid {{ request('filter_type')===$val ? '#2563eb' : '#d1d5db' }};background:{{ request('filter_type')===$val ? '#eff6ff' : '#fff' }};color:{{ request('filter_type')===$val ? '#2563eb' : '#6b7280' }};transition:all 0.15s;">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            <input type="hidden" name="filter_type" id="hFilterType" value="{{ request('filter_type') }}">

            <div style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap;">
                @if(isset($branches) && $branches->count() > 0)
                <div style="display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label style="font-size:12px;font-weight:600;color:#374151;"><i class="fas fa-store" style="margin-right:4px;"></i>Branch</label>
                    <select name="branch_id" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;color:#374151;">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div id="hpanel-month" style="display:{{ request('filter_type')==='month' ? 'flex' : 'none' }};flex-direction:column;gap:6px;min-width:160px;">
                    <label style="font-size:12px;font-weight:600;color:#374151;">Month</label>
                    <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}"
                           style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;color:#374151;">
                </div>

                <div id="hpanel-custom-from" style="display:{{ request('filter_type')==='custom' ? 'flex' : 'none' }};flex-direction:column;gap:6px;min-width:140px;">
                    <label style="font-size:12px;font-weight:600;color:#374151;">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;color:#374151;">
                </div>

                <div id="hpanel-custom-to" style="display:{{ request('filter_type')==='custom' ? 'flex' : 'none' }};flex-direction:column;gap:6px;min-width:140px;">
                    <label style="font-size:12px;font-weight:600;color:#374151;">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;color:#374151;">
                </div>

                <div style="display:flex;gap:8px;align-items:center;">
                    <button type="submit" style="padding:9px 22px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Apply</button>
                    <a href="{{ route('admin.handover.index') }}" style="padding:9px 22px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="content-card">
    <div style="padding:16px 20px;border-bottom:1px solid var(--gray-200);display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:15px;font-weight:600;color:var(--gray-800);"><i class="fas fa-cash-register me-2" style="color:var(--blue-500);"></i>Handover Records</div>
        <form method="GET" action="{{ route('admin.handover.export') }}">
            <input type="hidden" name="filter_type" value="{{ request('filter_type') }}">
            <input type="hidden" name="month"       value="{{ request('month') }}">
            <input type="hidden" name="date_from"   value="{{ request('date_from') }}">
            <input type="hidden" name="date_to"     value="{{ request('date_to') }}">
            <input type="hidden" name="branch_id"   value="{{ request('branch_id') }}">
            <button type="submit" style="display:inline-flex;align-items:center;gap:6px;background:#059669;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                <i class="fas fa-download"></i> Export Excel
            </button>
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--gray-50);">
                    <th style="padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);border-bottom:1px solid var(--gray-200);text-align:left;white-space:nowrap;">#</th>
                    <th style="padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);border-bottom:1px solid var(--gray-200);text-align:left;white-space:nowrap;">Cashier</th>
                    <th style="padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);border-bottom:1px solid var(--gray-200);text-align:left;white-space:nowrap;">Branch</th>
                    <th style="padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);border-bottom:1px solid var(--gray-200);text-align:left;white-space:nowrap;">Date</th>
                    <th style="padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);border-bottom:1px solid var(--gray-200);text-align:left;white-space:nowrap;">Submitted</th>
                    <th style="padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);border-bottom:1px solid var(--gray-200);text-align:left;white-space:nowrap;">System Cash</th>
                    <th style="padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);border-bottom:1px solid var(--gray-200);text-align:left;white-space:nowrap;">Difference</th>
                    <th style="padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);border-bottom:1px solid var(--gray-200);text-align:left;white-space:nowrap;">Status</th>
                    <th style="padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);border-bottom:1px solid var(--gray-200);text-align:left;white-space:nowrap;">Approved By</th>
                    <th style="padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);border-bottom:1px solid var(--gray-200);text-align:left;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($handovers as $h)
                @php
                    $key        = $h->cashier_id . '_' . $h->handover_date->toDateString();
                    $systemCash = $systemTotals[$key]->total ?? 0;
                    $diff       = $h->total_cash - $systemCash;
                @endphp
                <tr style="border-bottom:1px solid var(--gray-100);" onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background=''">
                    <td style="padding:14px 16px;font-size:13px;color:var(--gray-800);font-weight:700;">#{{ $h->id }}</td>
                    <td style="padding:14px 16px;font-size:13px;color:var(--gray-700);">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:30px;height:30px;border-radius:50%;background:var(--blue-100);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--blue-600);flex-shrink:0;">
                                {{ strtoupper(substr($h->cashier?->name ?? '?', 0, 1)) }}
                            </div>
                            <span>{{ $h->cashier?->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td style="padding:14px 16px;font-size:13px;color:var(--gray-500);">
                        {{ $h->cashier?->branch?->name ?? '—' }}
                    </td>
                    <td style="padding:14px 16px;font-size:13px;color:var(--gray-600);white-space:nowrap;">{{ $h->handover_date->format('d M Y') }}</td>
                    <td style="padding:14px 16px;font-size:13px;font-weight:700;color:var(--gray-800);">₹{{ number_format($h->total_cash, 2) }}</td>
                    <td style="padding:14px 16px;font-size:13px;color:var(--gray-600);">₹{{ number_format($systemCash, 2) }}</td>
                    <td style="padding:14px 16px;">
                        @if($diff == 0)
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#15803d;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">✔ Exact</span>
                        @elseif($diff > 0)
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#dbeafe;color:#1d4ed8;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">▲ +₹{{ number_format($diff, 2) }}</span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">▼ -₹{{ number_format(abs($diff), 2) }}</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;">
                        @if($h->status === 'approved')
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#15803d;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;"><i class="fas fa-check-circle"></i> Approved</span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#fef9c3;color:#a16207;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;"><i class="fas fa-clock"></i> Pending</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;font-size:12px;color:var(--gray-500);">
                        @if($h->approved_by)
                            <div style="font-weight:600;color:var(--gray-700);">{{ $h->approvedBy?->name }}</div>
                            <div style="font-size:11px;margin-top:2px;">{{ $h->approved_at?->format('d M, h:i A') }}</div>
                        @else
                            <span style="color:var(--gray-400);">—</span>
                        @endif
                    </td>
                    <td style="padding:14px 16px;">
                        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                            <button type="button"
                                    class="btn-view-handover"
                                    data-h="{!! htmlspecialchars(json_encode($h->only(['id','denom_1','denom_2','denom_5','denom_10','denom_20','denom_50','denom_100','denom_200','denom_500','total_cash','notes'])), ENT_QUOTES) !!}"
                                    data-sys="{{ $systemCash }}"
                                    data-diff="{{ $diff }}"
                                    style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border:1px solid var(--gray-300);background:#fff;color:var(--gray-700);border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
                                <i class="fas fa-eye"></i> View
                            </button>
                            @if($h->status === 'pending')
                                <a href="{{ route('admin.handover.edit', $h) }}"
                                   style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border:1px solid var(--blue-300);background:var(--blue-50);color:var(--blue-700);border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <button type="button"
                                        class="btn-approve-handover"
                                        data-id="{{ $h->id }}"
                                        data-cashier="{{ $h->cashier?->name }}"
                                        data-total="{{ number_format($h->total_cash, 2) }}"
                                        data-sys="{{ $systemCash }}"
                                        data-diff="{{ $diff }}"
                                        style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border:none;background:#059669;color:#fff;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="padding:48px;text-align:center;">
                        <div style="font-size:40px;color:var(--gray-300);margin-bottom:12px;"><i class="fas fa-inbox"></i></div>
                        <div style="font-size:14px;color:var(--gray-500);">No handover reports found.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:16px 20px;border-top:1px solid var(--gray-200);">{{ $handovers->links() }}</div>
</div>
@endsection

@push('scripts')
<style>
.ho-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 99998;
    display: flex; align-items: center; justify-content: center;
}
.ho-modal {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.22);
    z-index: 99999;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    display: flex; flex-direction: column;
}
.ho-modal-sm { max-width: 480px; }
.ho-modal-lg { max-width: 640px; }
.ho-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 16px; font-weight: 700;
    flex-shrink: 0;
}
.ho-modal-header.success { background: #059669; color: #fff; border-radius: 10px 10px 0 0; }
.ho-modal-body  { padding: 20px; flex: 1; overflow-y: auto; }
.ho-modal-footer {
    padding: 12px 20px;
    border-top: 1px solid #e2e8f0;
    display: flex; justify-content: flex-end; gap: 8px;
    flex-shrink: 0;
}
.ho-btn {
    padding: 8px 18px; border-radius: 7px; font-size: 13.5px;
    font-weight: 600; cursor: pointer; border: none;
}
.ho-btn-secondary { background: #6c757d; color: #fff; }
.ho-btn-secondary:hover { background: #5c636a; }
.ho-btn-success  { background: #059669; color: #fff; }
.ho-btn-success:hover  { background: #047857; }
.ho-close {
    background: none; border: none; cursor: pointer;
    font-size: 20px; line-height: 1; color: inherit; opacity: 0.8;
    padding: 0 4px;
}
.ho-close:hover { opacity: 1; }
.ho-alert {
    padding: 10px 14px; border-radius: 7px;
    font-size: 13.5px; font-weight: 600; text-align: center;
}
.ho-alert-success { background: #d1fae5; color: #065f46; }
.ho-alert-danger  { background: #fee2e2; color: #991b1b; }
.ho-alert-primary { background: #dbeafe; color: #1e40af; }
.ho-alert-warning { background: #fef3c7; color: #92400e; font-weight: 400; }
</style>

<script>
(function () {
    var DENOMS = [1, 2, 5, 10, 20, 50, 100, 200, 500];

    function fmt(n) {
        return parseFloat(n).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    }

    function openOverlay(html, size) {
        var overlay = document.createElement('div');
        overlay.className = 'ho-overlay';
        overlay.innerHTML = '<div class="ho-modal ' + (size === 'lg' ? 'ho-modal-lg' : 'ho-modal-sm') + '">' + html + '</div>';
        document.body.appendChild(overlay);
        overlay.querySelector('.ho-close').addEventListener('click', function () {
            document.body.removeChild(overlay);
        });
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) document.body.removeChild(overlay);
        });
        return overlay;
    }

    // Filter pill-tab toggle
    window.setHFilterType = function(type) {
        document.getElementById('hFilterType').value = type;
        ['today','month','custom'].forEach(function(t) {
            var active = t === type;
            var btn = document.getElementById('hftab-' + t);
            if (btn) {
                btn.style.borderColor = active ? '#2563eb' : '#d1d5db';
                btn.style.background  = active ? '#eff6ff' : '#fff';
                btn.style.color       = active ? '#2563eb' : '#6b7280';
            }
        });
        document.getElementById('hpanel-month').style.display       = type === 'month'  ? 'flex' : 'none';
        document.getElementById('hpanel-custom-from').style.display  = type === 'custom' ? 'flex' : 'none';
        document.getElementById('hpanel-custom-to').style.display    = type === 'custom' ? 'flex' : 'none';
    };

    document.getElementById('handoverFilterForm').addEventListener('submit', function(e) {
        var type = document.getElementById('hFilterType').value;
        if (type === 'month'  && !document.querySelector('[name="month"]').value)     { e.preventDefault(); alert('Please select a month.'); return; }
        if (type === 'custom' && !document.querySelector('[name="date_from"]').value) { e.preventDefault(); alert('Please select a From date.'); return; }
        if (type === 'custom' && !document.querySelector('[name="date_to"]').value)   { e.preventDefault(); alert('Please select a To date.'); return; }
    });

    // View button
    document.querySelectorAll('.btn-view-handover').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var h          = JSON.parse(this.dataset.h);
            var systemCash = parseFloat(this.dataset.sys);
            var diff       = parseFloat(this.dataset.diff);
            var rows = '', total = 0;

            DENOMS.forEach(function (d) {
                var qty = parseInt(h['denom_' + d]) || 0;
                var sub = qty * d;
                total += sub;
                rows += '<tr style="' + (qty === 0 ? 'color:#94a3b8' : '') + '">'
                      + '<td style="padding:6px 10px;">&#8377;' + d + '</td>'
                      + '<td style="padding:6px 10px;text-align:center;">' + qty + '</td>'
                      + '<td style="padding:6px 10px;text-align:right;"><strong>&#8377;' + sub.toLocaleString('en-IN') + '</strong></td>'
                      + '</tr>';
            });

            var diffAbs = Math.abs(diff);
            var diffBadge, diffCls;
            if (diff === 0)    { diffBadge = '&#10004; Exact Match';                              diffCls = 'ho-alert-success'; }
            else if (diff > 0) { diffBadge = '&#9650; Excess &#8377;' + fmt(diffAbs);            diffCls = 'ho-alert-primary'; }
            else               { diffBadge = '&#9660; Short &#8377;'  + fmt(diffAbs);            diffCls = 'ho-alert-danger';  }

            var body =
                '<table style="width:100%;border-collapse:collapse;font-size:13.5px;">'
              + '<thead><tr style="background:#f8fafc;">'
              + '<th style="padding:8px 10px;border-bottom:2px solid #e2e8f0;text-align:left;">Denomination</th>'
              + '<th style="padding:8px 10px;border-bottom:2px solid #e2e8f0;text-align:center;">Count</th>'
              + '<th style="padding:8px 10px;border-bottom:2px solid #e2e8f0;text-align:right;">Subtotal</th>'
              + '</tr></thead>'
              + '<tbody>' + rows + '</tbody>'
              + '<tfoot><tr style="background:#1e293b;color:#fff;">'
              + '<td colspan="2" style="padding:8px 10px;"><strong>Total Submitted</strong></td>'
              + '<td style="padding:8px 10px;text-align:right;"><strong>&#8377;' + fmt(total) + '</strong></td>'
              + '</tr></tfoot>'
              + '</table>'
              + '<div style="margin-top:16px;">'
              + '<table style="width:100%;border-collapse:collapse;font-size:13.5px;margin-bottom:10px;">'
              + '<tr style="border-bottom:1px solid #e2e8f0;">'
              + '<td style="padding:8px 10px;color:#64748b;">Submitted by Cashier</td>'
              + '<td style="padding:8px 10px;text-align:right;font-weight:700;">&#8377;' + fmt(total) + '</td>'
              + '</tr>'
              + '<tr>'
              + '<td style="padding:8px 10px;color:#64748b;">System Cash Orders</td>'
              + '<td style="padding:8px 10px;text-align:right;font-weight:700;">&#8377;' + fmt(systemCash) + '</td>'
              + '</tr>'
              + '</table>'
              + '<div class="ho-alert ' + diffCls + '">' + diffBadge + '</div>'
              + '</div>'
              + (h.notes ? '<p style="margin-top:12px;color:#64748b;font-size:13px;"><em>&#128221; Note: ' + h.notes + '</em></p>' : '');

            openOverlay(
                '<div class="ho-modal-header">'
              +   '<span>&#128269; Handover Detail</span>'
              +   '<button class="ho-close">&times;</button>'
              + '</div>'
              + '<div class="ho-modal-body">' + body + '</div>',
                'lg'
            );
        });
    });

    // Approve button
    document.querySelectorAll('.btn-approve-handover').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id      = this.dataset.id;
            var cashier = this.dataset.cashier;
            var total   = this.dataset.total;
            var sys     = parseFloat(this.dataset.sys);
            var diff    = parseFloat(this.dataset.diff);
            var diffAbs = Math.abs(diff).toLocaleString('en-IN', { minimumFractionDigits: 2 });

            var diffAlert;
            if (diff === 0)
                diffAlert = '<div class="ho-alert ho-alert-success" style="margin-bottom:12px;">&#10004; Submitted amount matches system cash total exactly.</div>';
            else if (diff > 0)
                diffAlert = '<div class="ho-alert ho-alert-primary" style="margin-bottom:12px;">&#9650; Cashier submitted <strong>&#8377;' + diffAbs + ' excess</strong> over system total.</div>';
            else
                diffAlert = '<div class="ho-alert ho-alert-danger" style="margin-bottom:12px;">&#9660; Cashier is <strong>&#8377;' + diffAbs + ' short</strong> of system cash total.</div>';

            var overlay = openOverlay(
                '<div class="ho-modal-header success">'
              +   '<span>&#9989; Approve Handover</span>'
              +   '<button class="ho-close">&times;</button>'
              + '</div>'
              + '<div class="ho-modal-body">'
              +   '<p style="margin-bottom:12px;">Approving handover for <strong>' + cashier + '</strong>.</p>'
              +   '<div style="display:flex;justify-content:space-between;margin-bottom:6px;">'
              +     '<span style="color:#64748b;">Submitted Amount</span>'
              +     '<strong style="color:#059669;font-size:16px;">&#8377;' + total + '</strong>'
              +   '</div>'
              +   '<div style="display:flex;justify-content:space-between;margin-bottom:14px;">'
              +     '<span style="color:#64748b;">System Cash Total</span>'
              +     '<strong>&#8377;' + fmt(sys) + '</strong>'
              +   '</div>'
              +   diffAlert
              +   '<div class="ho-alert ho-alert-warning" style="margin-bottom:14px;">&#9888;&#65039; This action is irreversible. Enter your admin password to confirm.</div>'
              +   '<form id="approveFormInner" method="POST" action="/admin/handover/' + id + '/approve">'
              +     '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
              +     '<label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Admin Password</label>'
              +     '<input type="password" name="password" class="form-control" placeholder="Enter your password" required style="margin-bottom:0;">'
              +   '</form>'
              + '</div>'
              + '<div class="ho-modal-footer">'
              +   '<button class="ho-btn ho-btn-secondary ho-close">Cancel</button>'
              +   '<button class="ho-btn ho-btn-success" id="confirmApproveBtn">Confirm Approval</button>'
              + '</div>',
                'sm'
            );

            overlay.querySelector('#confirmApproveBtn').addEventListener('click', function () {
                overlay.querySelector('#approveFormInner').submit();
            });
        });
    });
}());
</script>
@endpush
