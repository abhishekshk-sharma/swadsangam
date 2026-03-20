@extends('layouts.manager')
@section('title', 'Cash Handovers')
@section('content')

<div class="content-card mb-4">
    <div class="card-header"><div class="card-title"><i class="fas fa-filter"></i> Filter Handovers</div></div>
    <div class="card-body">
        <form method="GET" action="{{ route('manager.handover.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Filter Type</label>
                    <select name="filter_type" class="form-select" id="filterType">
                        <option value="">All</option>
                        <option value="today"  {{ request('filter_type') === 'today'  ? 'selected' : '' }}>Today</option>
                        <option value="month"  {{ request('filter_type') === 'month'  ? 'selected' : '' }}>By Month</option>
                        <option value="custom" {{ request('filter_type') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3" id="monthField" style="display:none;">
                    <label class="form-label">Month</label>
                    <input type="month" name="month" class="form-control" value="{{ request('month') }}">
                </div>
                <div class="col-md-2" id="dateFromField" style="display:none;">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2" id="dateToField" style="display:none;">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <a href="{{ route('manager.handover.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>#</th><th>Cashier</th><th>Date</th><th>Submitted</th><th>System Cash</th><th>Difference</th><th>Status</th><th>Approved By</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($handovers as $h)
                @php
                    $key        = $h->cashier_id . '_' . $h->handover_date->toDateString();
                    $systemCash = $systemTotals[$key]->total ?? 0;
                    $diff       = $h->total_cash - $systemCash;
                @endphp
                <tr>
                    <td><strong>#{{ $h->id }}</strong></td>
                    <td>{{ $h->cashier?->name ?? '—' }}</td>
                    <td>{{ $h->handover_date->format('d M Y') }}</td>
                    <td><strong>₹{{ number_format($h->total_cash, 2) }}</strong></td>
                    <td>₹{{ number_format($systemCash, 2) }}</td>
                    <td>
                        @if($diff == 0) <span class="badge badge-success">✔ Exact</span>
                        @elseif($diff > 0) <span class="badge badge-info">▲ Excess ₹{{ number_format($diff, 2) }}</span>
                        @else <span class="badge badge-error">▼ Short ₹{{ number_format(abs($diff), 2) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($h->status === 'approved') <span class="badge badge-success">Approved</span>
                        @else <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td style="font-size:13px;color:#666;">
                        @if($h->approved_by) {{ $h->approvedBy?->name }}<br>{{ $h->approved_at?->format('d M, h:i A') }}
                        @else —
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary btn-sm btn-view"
                                data-h="{!! htmlspecialchars(json_encode($h->only(['id','denom_1','denom_2','denom_5','denom_10','denom_20','denom_50','denom_100','denom_200','denom_500','total_cash','notes'])), ENT_QUOTES) !!}"
                                data-sys="{{ $systemCash }}" data-diff="{{ $diff }}">🔍 View</button>
                            @if($h->status === 'pending')
                                <a href="{{ route('manager.handover.edit', $h) }}" class="btn btn-sm btn-secondary">✏️ Edit</a>
                                <button type="button" class="btn btn-sm btn-approve"
                                    style="background:#059669;color:#fff;border:none;border-radius:4px;padding:4px 10px;cursor:pointer;"
                                    data-id="{{ $h->id }}" data-cashier="{{ $h->cashier?->name }}"
                                    data-total="{{ number_format($h->total_cash, 2) }}"
                                    data-sys="{{ $systemCash }}" data-diff="{{ $diff }}">✅ Approve</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No handover reports found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem;">{{ $handovers->links() }}</div>
</div>

<!-- Modals (same logic as admin, adapted for manager routes) -->
<style>
.ho-overlay { position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99998;display:flex;align-items:center;justify-content:center; }
.ho-modal { background:#fff;border-radius:10px;box-shadow:0 8px 40px rgba(0,0,0,.22);z-index:99999;width:100%;max-height:90vh;overflow-y:auto;display:flex;flex-direction:column; }
.ho-modal-sm { max-width:480px; } .ho-modal-lg { max-width:640px; }
.ho-modal-header { display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0;font-size:16px;font-weight:700;flex-shrink:0; }
.ho-modal-header.success { background:#059669;color:#fff;border-radius:10px 10px 0 0; }
.ho-modal-body { padding:20px;flex:1;overflow-y:auto; } .ho-modal-footer { padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:8px;flex-shrink:0; }
.ho-btn { padding:8px 18px;border-radius:7px;font-size:13.5px;font-weight:600;cursor:pointer;border:none; }
.ho-btn-secondary { background:#6c757d;color:#fff; } .ho-btn-success { background:#059669;color:#fff; }
.ho-close { background:none;border:none;cursor:pointer;font-size:20px;line-height:1;color:inherit;opacity:.8;padding:0 4px; }
.ho-alert { padding:10px 14px;border-radius:7px;font-size:13.5px;font-weight:600;text-align:center; }
.ho-alert-success { background:#d1fae5;color:#065f46; } .ho-alert-danger { background:#fee2e2;color:#991b1b; }
.ho-alert-primary { background:#dbeafe;color:#1e40af; } .ho-alert-warning { background:#fef3c7;color:#92400e;font-weight:400; }
</style>

<script>
(function() {
    var DENOMS = [1,2,5,10,20,50,100,200,500];
    function fmt(n) { return parseFloat(n).toLocaleString('en-IN', {minimumFractionDigits:2}); }

    function openOverlay(html, size) {
        var overlay = document.createElement('div');
        overlay.className = 'ho-overlay';
        overlay.innerHTML = '<div class="ho-modal ' + (size === 'lg' ? 'ho-modal-lg' : 'ho-modal-sm') + '">' + html + '</div>';
        document.body.appendChild(overlay);
        overlay.querySelector('.ho-close').addEventListener('click', function() { document.body.removeChild(overlay); });
        overlay.addEventListener('click', function(e) { if (e.target === overlay) document.body.removeChild(overlay); });
        return overlay;
    }

    var filterType = document.getElementById('filterType');
    function toggleFilterFields() {
        var v = filterType.value;
        document.getElementById('monthField').style.display    = v === 'month'  ? '' : 'none';
        document.getElementById('dateFromField').style.display = v === 'custom' ? '' : 'none';
        document.getElementById('dateToField').style.display   = v === 'custom' ? '' : 'none';
    }
    filterType.addEventListener('change', toggleFilterFields);
    toggleFilterFields();

    document.querySelectorAll('.btn-view').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var h = JSON.parse(this.dataset.h), sys = parseFloat(this.dataset.sys), diff = parseFloat(this.dataset.diff);
            var rows = '', total = 0;
            DENOMS.forEach(function(d) {
                var qty = parseInt(h['denom_' + d]) || 0, sub = qty * d; total += sub;
                rows += '<tr style="' + (qty === 0 ? 'color:#94a3b8' : '') + '"><td style="padding:6px 10px;">&#8377;' + d + '</td><td style="padding:6px 10px;text-align:center;">' + qty + '</td><td style="padding:6px 10px;text-align:right;"><strong>&#8377;' + sub.toLocaleString('en-IN') + '</strong></td></tr>';
            });
            var diffAbs = Math.abs(diff), diffBadge, diffCls;
            if (diff === 0) { diffBadge = '&#10004; Exact Match'; diffCls = 'ho-alert-success'; }
            else if (diff > 0) { diffBadge = '&#9650; Excess &#8377;' + fmt(diffAbs); diffCls = 'ho-alert-primary'; }
            else { diffBadge = '&#9660; Short &#8377;' + fmt(diffAbs); diffCls = 'ho-alert-danger'; }
            var body = '<table style="width:100%;border-collapse:collapse;font-size:13.5px;"><thead><tr style="background:#f8fafc;"><th style="padding:8px 10px;border-bottom:2px solid #e2e8f0;">Denomination</th><th style="padding:8px 10px;border-bottom:2px solid #e2e8f0;text-align:center;">Count</th><th style="padding:8px 10px;border-bottom:2px solid #e2e8f0;text-align:right;">Subtotal</th></tr></thead><tbody>' + rows + '</tbody><tfoot><tr style="background:#1e293b;color:#fff;"><td colspan="2" style="padding:8px 10px;"><strong>Total</strong></td><td style="padding:8px 10px;text-align:right;"><strong>&#8377;' + fmt(total) + '</strong></td></tr></tfoot></table><div style="margin-top:16px;"><div class="ho-alert ' + diffCls + '">' + diffBadge + '</div></div>' + (h.notes ? '<p style="margin-top:12px;color:#64748b;font-size:13px;"><em>&#128221; ' + h.notes + '</em></p>' : '');
            openOverlay('<div class="ho-modal-header"><span>&#128269; Handover Detail</span><button class="ho-close">&times;</button></div><div class="ho-modal-body">' + body + '</div>', 'lg');
        });
    });

    document.querySelectorAll('.btn-approve').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id, cashier = this.dataset.cashier, total = this.dataset.total, sys = parseFloat(this.dataset.sys), diff = parseFloat(this.dataset.diff);
            var diffAbs = Math.abs(diff).toLocaleString('en-IN', {minimumFractionDigits:2}), diffAlert;
            if (diff === 0) diffAlert = '<div class="ho-alert ho-alert-success" style="margin-bottom:12px;">&#10004; Exact match.</div>';
            else if (diff > 0) diffAlert = '<div class="ho-alert ho-alert-primary" style="margin-bottom:12px;">&#9650; Excess &#8377;' + diffAbs + '</div>';
            else diffAlert = '<div class="ho-alert ho-alert-danger" style="margin-bottom:12px;">&#9660; Short &#8377;' + diffAbs + '</div>';
            var overlay = openOverlay(
                '<div class="ho-modal-header success"><span>&#9989; Approve Handover</span><button class="ho-close">&times;</button></div>'
              + '<div class="ho-modal-body"><p style="margin-bottom:12px;">Approving for <strong>' + cashier + '</strong>.</p>'
              + '<div style="display:flex;justify-content:space-between;margin-bottom:6px;"><span style="color:#64748b;">Submitted</span><strong style="color:#059669;">&#8377;' + total + '</strong></div>'
              + '<div style="display:flex;justify-content:space-between;margin-bottom:14px;"><span style="color:#64748b;">System Cash</span><strong>&#8377;' + fmt(sys) + '</strong></div>'
              + diffAlert
              + '<div class="ho-alert ho-alert-warning" style="margin-bottom:14px;">&#9888;&#65039; Irreversible. Enter your password to confirm.</div>'
              + '<form id="approveForm" method="POST" action="/manager/handover/' + id + '/approve">'
              + '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
              + '<label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Manager Password</label>'
              + '<input type="password" name="password" class="form-control" placeholder="Enter your password" required>'
              + '</form></div>'
              + '<div class="ho-modal-footer"><button class="ho-btn ho-btn-secondary ho-close">Cancel</button><button class="ho-btn ho-btn-success" id="confirmBtn">Confirm</button></div>',
                'sm'
            );
            overlay.querySelector('#confirmBtn').addEventListener('click', function() { overlay.querySelector('#approveForm').submit(); });
        });
    });
}());
</script>
@endsection
