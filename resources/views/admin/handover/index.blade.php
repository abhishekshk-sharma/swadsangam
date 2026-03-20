@extends('layouts.admin')
@section('title', 'Cash Handovers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">💵 Cash Handover Reports</h2>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Filter Card --}}
<div class="content-card mb-4">
    <div class="p-3 border-bottom bg-light" style="border-radius:8px 8px 0 0;">
        <strong><i class="fas fa-filter me-2"></i>Filter Handovers</strong>
    </div>
    <div class="p-3">
        <form method="GET" action="{{ route('admin.handover.index') }}">
            <div class="row g-3 align-items-end">
                @if(isset($branches) && $branches->count() > 0)
                <div class="col-md-3">
                    <label class="form-label fw-semibold small"><i class="fas fa-store me-1"></i>Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Filter Type</label>
                    <select name="filter_type" class="form-select" id="filterType">
                        <option value="">All</option>
                        <option value="today"  {{ request('filter_type') === 'today'  ? 'selected' : '' }}>Today</option>
                        <option value="month"  {{ request('filter_type') === 'month'  ? 'selected' : '' }}>By Month</option>
                        <option value="custom" {{ request('filter_type') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3" id="monthField" style="display:none;">
                    <label class="form-label fw-semibold small">Month</label>
                    <input type="month" name="month" class="form-control" value="{{ request('month') }}">
                </div>
                <div class="col-md-2" id="dateFromField" style="display:none;">
                    <label class="form-label fw-semibold small">From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2" id="dateToField" style="display:none;">
                    <label class="form-label fw-semibold small">To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <a href="{{ route('admin.handover.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="content-card">
    <div class="p-3 border-bottom d-flex justify-content-end">
        <form method="GET" action="{{ route('admin.handover.export') }}">
            <input type="hidden" name="filter_type" value="{{ request('filter_type') }}">
            <input type="hidden" name="month"       value="{{ request('month') }}">
            <input type="hidden" name="date_from"   value="{{ request('date_from') }}">
            <input type="hidden" name="date_to"     value="{{ request('date_to') }}">
            <input type="hidden" name="branch_id"   value="{{ request('branch_id') }}">
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-download me-1"></i> Export Excel
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Cashier</th><th>Date</th><th>Submitted</th>
                    <th>System Cash</th><th>Difference</th><th>Status</th>
                    <th>Approved By</th><th>Actions</th>
                </tr>
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
                        @if($diff == 0)
                            <span class="badge bg-success">✔ Exact</span>
                        @elseif($diff > 0)
                            <span class="badge bg-primary">▲ Excess ₹{{ number_format($diff, 2) }}</span>
                        @else
                            <span class="badge bg-danger">▼ Short ₹{{ number_format(abs($diff), 2) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($h->status === 'approved')
                            <span class="badge bg-success">Approved</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        @if($h->approved_by)
                            {{ $h->approvedBy?->name }}<br>{{ $h->approved_at?->format('d M, h:i A') }}
                        @else —
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary btn-view-handover"
                                    data-h="{!! htmlspecialchars(json_encode($h->only(['id','denom_1','denom_2','denom_5','denom_10','denom_20','denom_50','denom_100','denom_200','denom_500','total_cash','notes'])), ENT_QUOTES) !!}"
                                    data-sys="{{ $systemCash }}"
                                    data-diff="{{ $diff }}">
                                🔍 View
                            </button>
                            @if($h->status === 'pending')
                                <a href="{{ route('admin.handover.edit', $h) }}" class="btn btn-sm btn-outline-primary">✏️ Edit</a>
                                <button type="button"
                                        class="btn btn-sm btn-success btn-approve-handover"
                                        data-id="{{ $h->id }}"
                                        data-cashier="{{ $h->cashier?->name }}"
                                        data-total="{{ number_format($h->total_cash, 2) }}"
                                        data-sys="{{ $systemCash }}"
                                        data-diff="{{ $diff }}">
                                    ✅ Approve
                                </button>
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
    <div class="p-3">{{ $handovers->links() }}</div>
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

    // Filter UI toggle
    var filterType = document.getElementById('filterType');
    function toggleFilterFields() {
        var v = filterType.value;
        document.getElementById('monthField').style.display    = v === 'month'  ? '' : 'none';
        document.getElementById('dateFromField').style.display = v === 'custom' ? '' : 'none';
        document.getElementById('dateToField').style.display   = v === 'custom' ? '' : 'none';
    }
    filterType.addEventListener('change', toggleFilterFields);
    toggleFilterFields();

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
