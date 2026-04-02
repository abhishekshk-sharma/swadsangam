@extends('layouts.admin')
@section('title', 'Bills')
@section('content')
@php
    $activeMode = $activeMode ?? 'all';
    $filterType = $filterType ?? '';
    $filterLabel = match($filterType) {
        'day'    => 'Day: ' . (request('day') ? \Carbon\Carbon::parse(request('day'))->format('d M Y') : ''),
        'month'  => 'Month: ' . (request('month') ? \Carbon\Carbon::parse(request('month').'-01')->format('F Y') : ''),
        'year'   => 'Year: ' . request('year'),
        'custom' => request('date_from') . ' to ' . request('date_to'),
        default  => 'All Time'
    };
@endphp

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 class="section-title"><i class="fas fa-file-invoice me-2"></i>Bills</h1>
        <p style="font-size:13px;color:var(--gray-500);">Manage paid bills — deleted bills are hidden from customer view but kept for reports.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
@endif

{{-- Filter Card --}}
<div class="content-card mb-4">
    <div class="card-header">
        <h5 style="margin:0;font-weight:600;color:#232f3e;"><i class="fas fa-filter me-2"></i>Filter Bills</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.bills.index') }}" id="billFilterForm">
            <div style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;">

                {{-- Branch --}}
                @if($branches->count() > 0)
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label class="form-label"><i class="fas fa-store me-1"></i>Branch</label>
                    <select name="branch_id" class="form-select" style="min-width:160px;">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Filter by dropdown --}}
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label class="form-label">Filter by</label>
                    <select id="billFilterType" name="filter_type" onchange="onBillFilterChange(this.value)" class="form-select" style="min-width:150px;">
                        <option value="">— All Time —</option>
                        <option value="day"    {{ $filterType==='day'    ? 'selected' : '' }}>Specific Day</option>
                        <option value="month"  {{ $filterType==='month'  ? 'selected' : '' }}>Month</option>
                        <option value="year"   {{ $filterType==='year'   ? 'selected' : '' }}>Year</option>
                        <option value="custom" {{ $filterType==='custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                {{-- Day picker --}}
                <div id="bfpanel-day" style="display:{{ $filterType==='day' ? 'flex' : 'none' }};flex-direction:column;gap:6px;">
                    <label class="form-label">Select Day</label>
                    <input type="date" name="day" class="form-control" value="{{ request('day', now()->format('Y-m-d')) }}">
                </div>

                {{-- Month picker --}}
                <div id="bfpanel-month" style="display:{{ $filterType==='month' ? 'flex' : 'none' }};flex-direction:column;gap:6px;">
                    <label class="form-label">Select Month</label>
                    <input type="month" name="month" class="form-control" value="{{ request('month', now()->format('Y-m')) }}">
                </div>

                {{-- Year dropdown --}}
                <div id="bfpanel-year" style="display:{{ $filterType==='year' ? 'flex' : 'none' }};flex-direction:column;gap:6px;">
                    <label class="form-label">Select Year</label>
                    <select name="year" class="form-select" style="min-width:120px;">
                        @for($y = now()->year; $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Custom range --}}
                <div id="bfpanel-custom-from" style="display:{{ $filterType==='custom' ? 'flex' : 'none' }};flex-direction:column;gap:6px;">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div id="bfpanel-custom-to" style="display:{{ $filterType==='custom' ? 'flex' : 'none' }};flex-direction:column;gap:6px;">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div style="display:flex;gap:8px;align-items:flex-end;">
                    <button type="submit" class="btn-primary"><i class="fas fa-search me-1"></i>Apply</button>
                    <a href="{{ route('admin.bills.index') }}" class="btn-secondary">Clear</a>
                </div>
            </div>

            {{-- Payment Mode --}}
            <div style="margin-top:16px;padding-top:14px;border-top:1px solid #e3e6e8;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span style="font-size:12px;font-weight:600;color:#374151;">Payment Mode:</span>
                @foreach(['all'=>'All','cash'=>'💵 Cash','upi'=>'📱 UPI','card'=>'💳 Card'] as $mv=>$ml)
                <button type="button" onclick="setBillMode('{{ $mv }}')" id="bmode-{{ $mv }}"
                    style="padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:2px solid {{ $activeMode===$mv ? '#2563eb' : '#d1d5db' }};background:{{ $activeMode===$mv ? '#eff6ff' : '#fff' }};color:{{ $activeMode===$mv ? '#2563eb' : '#6b7280' }};transition:all 0.15s;">
                    {{ $ml }}
                </button>
                @endforeach
                <input type="hidden" name="payment_mode" id="billPayMode" value="{{ $activeMode }}">
            </div>
        </form>
    </div>
</div>

{{-- Stat Cards --}}
<div style="display:grid;grid-template-columns:repeat(2,1fr) repeat(3,1fr);gap:16px;margin-bottom:20px;">
    <div class="stat-card-report" style="border-left:4px solid #4facfe;">
        <h5>Total Bills</h5>
        <h2>{{ $totalBills }}</h2>
        <div style="font-size:12px;color:#6b7280;margin-top:4px;">{{ $filterLabel }}</div>
    </div>
    <div class="stat-card-report" style="border-left:4px solid #43e97b;">
        <h5>Total Revenue</h5>
        <h2>₹{{ number_format($totalAmount, 2) }}</h2>
    </div>
    <div class="stat-card-report" style="border-left:4px solid #16a34a;">
        <div style="display:flex;align-items:center;gap:6px;justify-content:center;margin-bottom:8px;"><span style="font-size:16px;">💵</span><h5 style="margin:0;">Cash</h5></div>
        <h2 style="color:#15803d;">₹{{ number_format($paymentTotals['cash'], 2) }}</h2>
    </div>
    <div class="stat-card-report" style="border-left:4px solid #7c3aed;">
        <div style="display:flex;align-items:center;gap:6px;justify-content:center;margin-bottom:8px;"><span style="font-size:16px;">📱</span><h5 style="margin:0;">UPI</h5></div>
        <h2 style="color:#6d28d9;">₹{{ number_format($paymentTotals['upi'], 2) }}</h2>
    </div>
    <div class="stat-card-report" style="border-left:4px solid #0284c7;">
        <div style="display:flex;align-items:center;gap:6px;justify-content:center;margin-bottom:8px;"><span style="font-size:16px;">💳</span><h5 style="margin:0;">Card</h5></div>
        <h2 style="color:#0369a1;">₹{{ number_format($paymentTotals['card'], 2) }}</h2>
    </div>
</div>

{{-- Chart --}}
@if($chartData->count() > 0)
<div class="content-card mb-4">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h5 style="margin:0;font-weight:600;"><i class="fas fa-chart-bar me-2" style="color:#3b82f6;"></i>Bills & Revenue Chart</h5>
        <div style="display:flex;gap:8px;">
            <button onclick="switchChart('bar')" id="chartBtnBar" style="padding:5px 14px;border-radius:6px;border:1px solid #3b82f6;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:600;cursor:pointer;">Bar</button>
            <button onclick="switchChart('line')" id="chartBtnLine" style="padding:5px 14px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#6b7280;font-size:12px;font-weight:600;cursor:pointer;">Line</button>
        </div>
    </div>
    <div class="card-body">
        <canvas id="billsChart" style="max-height:280px;"></canvas>
    </div>
</div>
@endif

{{-- Bulk Delete --}}
<div class="content-card mb-4" style="border-left:4px solid #dc2626;">
    <div class="card-header"><h5 style="margin:0;font-weight:600;color:#dc2626;"><i class="fas fa-trash-alt me-2"></i>Bulk Delete / Restore Bills</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.bills.bulkHide') }}" onsubmit="return confirm('Are you sure? This will delete/restore all bills for the selected period.')">
            @csrf @method('PATCH')
            @if($selectedBranch)<input type="hidden" name="branch_id" value="{{ $selectedBranch }}">@endif
            <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label class="form-label">Period</label>
                    <select name="filter_type" id="bulkFilterType" onchange="toggleBulkPanels(this.value)" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;min-width:160px;">
                        <option value="">Select...</option>
                        <option value="day">Specific Day</option>
                        <option value="month">Month</option>
                        <option value="year">Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div id="bulk-day" style="display:none;flex-direction:column;gap:6px;"><label class="form-label">Day</label><input type="date" name="day" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;"></div>
                <div id="bulk-month" style="display:none;flex-direction:column;gap:6px;"><label class="form-label">Month</label><input type="month" name="month" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;"></div>
                <div id="bulk-year" style="display:none;flex-direction:column;gap:6px;"><label class="form-label">Year</label><select name="year" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;">@for($y=now()->year;$y>=2020;$y--)<option value="{{ $y }}">{{ $y }}</option>@endfor</select></div>
                <div id="bulk-from" style="display:none;flex-direction:column;gap:6px;"><label class="form-label">From</label><input type="date" name="date_from" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;"></div>
                <div id="bulk-to" style="display:none;flex-direction:column;gap:6px;"><label class="form-label">To</label><input type="date" name="date_to" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;"></div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" name="action" value="hide" style="padding:9px 18px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;"><i class="fas fa-trash-alt me-1"></i>Delete All</button>
                    <button type="submit" name="action" value="restore" style="padding:9px 18px;background:#16a34a;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;"><i class="fas fa-undo me-1"></i>Restore All</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Bills Table --}}
<div class="content-card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h5 style="margin:0;font-weight:600;"><i class="fas fa-list me-2" style="color:#3b82f6;"></i>Bills List
            <span style="font-size:12px;font-weight:400;color:#6b7280;">— {{ $filterLabel }}</span>
            @if($hiddenCount > 0)<span style="background:#fee2e2;color:#dc2626;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;margin-left:8px;">{{ $hiddenCount }} deleted</span>@endif
        </h5>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    @php $th = 'padding:10px 14px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;white-space:nowrap;'; @endphp
                    <th style="{{ $th }}">Bill #</th>
                    <th style="{{ $th }}">Table</th>
                    <th style="{{ $th }}">Amount</th>
                    <th style="{{ $th }}">Payment</th>
                    <th style="{{ $th }}">Paid At</th>
                    <th style="{{ $th }}">Status</th>
                    <th style="{{ $th }};text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                <tr style="border-bottom:1px solid #f3f4f6;{{ $bill->bill_hidden ? 'opacity:.55;' : '' }}" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="padding:12px 14px;font-weight:700;color:#111827;">#{{ $bill->id }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:#374151;">
                        {{ $bill->is_parcel ? '📦 Parcel' : 'T'.($bill->table?->table_number ?? '?') }}
                    </td>
                    <td style="padding:12px 14px;font-weight:700;color:#15803d;">₹{{ number_format($bill->grand_total ?? $bill->total_amount, 2) }}</td>
                    <td style="padding:12px 14px;">
                        @php $pm = $bill->payment_mode; @endphp
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $pm==='cash'?'#dcfce7':($pm==='upi'?'#ede9fe':'#dbeafe') }};color:{{ $pm==='cash'?'#15803d':($pm==='upi'?'#6d28d9':'#1d4ed8') }};">
                            {{ $pm==='cash'?'💵':($pm==='upi'?'📱':'💳') }} {{ ucfirst($pm ?? '—') }}
                        </span>
                    </td>
                    <td style="padding:12px 14px;font-size:13px;color:#6b7280;white-space:nowrap;">{{ $bill->paid_at?->format('d M Y, h:i A') ?? '—' }}</td>
                    <td style="padding:12px 14px;">
                        @if($bill->bill_hidden)
                            <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:#fee2e2;color:#dc2626;"><i class="fas fa-trash-alt me-1"></i>Deleted</span>
                        @else
                            <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:#dcfce7;color:#15803d;"><i class="fas fa-check me-1"></i>Active</span>
                        @endif
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <div style="display:flex;gap:6px;justify-content:center;align-items:center;">
                            <a href="{{ URL::signedRoute('bill.show', ['orderId' => $bill->id]) }}" target="_blank"
                               style="padding:5px 10px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <form action="{{ route('admin.bills.toggleHidden', $bill->id) }}" method="POST" style="margin:0;">
                                @csrf @method('PATCH')
                                <button type="submit" style="padding:5px 12px;background:{{ $bill->bill_hidden ? '#16a34a' : '#dc2626' }};color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
                                    <i class="fas fa-{{ $bill->bill_hidden ? 'undo' : 'trash-alt' }} me-1"></i>
                                    {{ $bill->bill_hidden ? 'Restore' : 'Delete' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:48px;text-align:center;color:#9ca3af;">
                        <i class="fas fa-file-invoice" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3;"></i>
                        <div style="font-size:15px;font-weight:600;">No bills found</div>
                        <div style="font-size:13px;margin-top:4px;">Apply a filter above to view bills.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function onBillFilterChange(type) {
    ['day','month','year','custom-from','custom-to'].forEach(function(p) {
        var el = document.getElementById('bfpanel-'+p);
        if (el) el.style.display = 'none';
    });
    if (type === 'day')    document.getElementById('bfpanel-day').style.display = 'flex';
    if (type === 'month')  document.getElementById('bfpanel-month').style.display = 'flex';
    if (type === 'year')   document.getElementById('bfpanel-year').style.display = 'flex';
    if (type === 'custom') {
        document.getElementById('bfpanel-custom-from').style.display = 'flex';
        document.getElementById('bfpanel-custom-to').style.display = 'flex';
    }
}
function setBillMode(mode) {
    document.getElementById('billPayMode').value = mode;
    ['all','cash','upi','card'].forEach(function(m) {
        var btn = document.getElementById('bmode-'+m);
        if (!btn) return;
        var active = m === mode;
        btn.style.borderColor = active ? '#2563eb' : '#d1d5db';
        btn.style.background  = active ? '#eff6ff' : '#fff';
        btn.style.color       = active ? '#2563eb' : '#6b7280';
    });
    document.getElementById('billFilterForm').submit();
}
function toggleBulkPanels(type) {
    ['day','month','year','from','to'].forEach(function(p){ var el=document.getElementById('bulk-'+p); if(el) el.style.display='none'; });
    if (type==='day')    document.getElementById('bulk-day').style.display='flex';
    if (type==='month')  document.getElementById('bulk-month').style.display='flex';
    if (type==='year')   document.getElementById('bulk-year').style.display='flex';
    if (type==='custom') { document.getElementById('bulk-from').style.display='flex'; document.getElementById('bulk-to').style.display='flex'; }
}

@if($chartData->count() > 0)
var chartLabels = @json($chartData->pluck('date'));
var chartCounts = @json($chartData->pluck('count'));
var chartRevenue = @json($chartData->pluck('revenue')->map(fn($v) => round($v, 2)));
var currentChartType = 'bar';
var billChart;

function buildChart(type) {
    if (billChart) billChart.destroy();
    billChart = new Chart(document.getElementById('billsChart'), {
        type: type,
        data: {
            labels: chartLabels,
            datasets: [
                { label: 'Bills Count', data: chartCounts, backgroundColor: 'rgba(59,130,246,0.7)', borderColor: '#2563eb', borderWidth: 2, yAxisID: 'y', tension: 0.4 },
                { label: 'Revenue (₹)', data: chartRevenue, backgroundColor: 'rgba(22,163,74,0.15)', borderColor: '#16a34a', borderWidth: 2, yAxisID: 'y1', tension: 0.4, fill: type==='line' }
            ]
        },
        options: {
            responsive: true, interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top' } },
            scales: {
                y:  { type: 'linear', position: 'left',  title: { display: true, text: 'Bills' } },
                y1: { type: 'linear', position: 'right', title: { display: true, text: 'Revenue (₹)' }, grid: { drawOnChartArea: false } }
            }
        }
    });
}
function switchChart(type) {
    currentChartType = type;
    ['bar','line'].forEach(function(t) {
        var btn = document.getElementById('chartBtn'+t.charAt(0).toUpperCase()+t.slice(1));
        btn.style.background = t===type ? '#eff6ff' : '#fff';
        btn.style.borderColor = t===type ? '#3b82f6' : '#d1d5db';
        btn.style.color = t===type ? '#1d4ed8' : '#6b7280';
    });
    buildChart(type);
}
document.addEventListener('DOMContentLoaded', function() { buildChart('bar'); });
@endif
</script>
@endsection
