@extends('admin.layouts.app')
@section('title', 'MyBill Dashboard')
@section('bc')
<a href="{{ route('admin.mybill.dashboard') }}">MyBill</a> / Dashboard
@endsection
@section('content')

{{-- Subnav (Premium Glass Style) --}}
<div class="glass-nav">
    <div class="gn-inner">
        <a href="{{ route('admin.mybill.dashboard') }}" class="gn-link active"><i class="bi bi-grid-fill"></i> Overview</a>
        <a href="{{ route('admin.mybill.loans') }}" class="gn-link"><i class="bi bi-list-ul"></i> All Loans</a>
        <a href="{{ route('admin.mybill.limits') }}" class="gn-link"><i class="bi bi-sliders"></i> Credit Limits</a>
    </div>
</div>

{{-- Top Stats Cards --}}
<div class="kpi-grid-3">
    <div class="kpi-card-simple" style="border-left: 4px solid #4f46e5">
        <div class="ks-label">Total Disbursed</div>
        <div class="ks-value">M{{ number_format($stats['total_bill_value'], 2) }}</div>
        <div class="ks-sub">{{ number_format($stats['total_loans']) }} total purchases</div>
    </div>
    
    <div class="kpi-card-simple" style="border-left: 4px solid #f59e0b">
        <div class="ks-label">Outstanding Balance</div>
        <div class="ks-value" style="color:#92400e">M{{ number_format($stats['total_outstanding'], 2) }}</div>
        <div class="ks-sub">Across {{ number_format($stats['active_loans']) }} active loans</div>
    </div>

    <div class="kpi-card-simple" style="border-left: 4px solid #10b981">
        <div class="ks-label">Total Fee Revenue</div>
        <div class="ks-value" style="color:#065f46">M{{ number_format($stats['total_revenue'], 2) }}</div>
        <div class="ks-sub">From {{ number_format($stats['settled_loans']) }} settlements</div>
    </div>
</div>

@if(session('success'))
    <div class="p-alert-success mb-4"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<div style="display:grid; grid-template-columns: 1.8fr 1fr; gap:24px; margin-bottom:24px">
    
    {{-- Category Breakdown --}}
    <div class="premium-card">
        <div class="pc-header">
            <h3 class="pc-title">Purchases by Category</h3>
        </div>
        <div class="pc-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px">
                @php
                    $cats = [
                        ['electricity', 'Electricity', 'lightning-charge-fill', '#f59e0b'],
                        ['airtime', 'Airtime', 'phone-fill', '#06b6d4'],
                        ['insurance', 'Insurance', 'shield-fill', '#8b5cf6'],
                        ['ticket', 'Tickets', 'ticket-perforated-fill', '#ec4899']
                    ];
                    $totalLoans = max(1, $stats['total_loans']);
                @endphp
                
                @foreach($cats as [$key, $label, $icon, $color])
                @php $count = $stats['by_category'][$key] ?? 0; $pct = round(($count / $totalLoans) * 100); @endphp
                <div class="category-stat-box">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px">
                        <div class="cat-icon-sm" style="background:{{ $color }}15; color:{{ $color }}"><i class="bi bi-{{ $icon }}"></i></div>
                        <div style="font-weight:700; font-size:14px; color:#1e293b">{{ $label }}</div>
                        <div style="margin-left:auto; font-weight:900; font-size:18px; color:#0f172a">{{ number_format($count) }}</div>
                    </div>
                    <div class="prog-bg"><div class="prog-bar" style="background:{{ $color }}; width:{{ $pct }}%"></div></div>
                    <div style="text-align:right; font-size:11px; color:#94a3b8; font-weight:700; margin-top:6px">{{ $pct }}% of volume</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Global Settings --}}
    <div class="premium-card">
        <div class="pc-header">
            <h3 class="pc-title"><i class="bi bi-gear-wide-connected"></i> Global Settings</h3>
        </div>
        <div class="pc-body">
            <form method="POST" action="{{ route('admin.mybill.settings.update') }}">
                @csrf
                <div class="settings-box">
                    <label class="s-label">Default User Credit Limit (M)</label>
                    <div style="display:flex; gap:10px; margin-top:8px">
                        <input type="number" name="default_limit" value="{{ (int) \App\Models\SystemSetting::get('mybill_default_limit', 1000) }}" class="s-input" required>
                        <button type="submit" class="s-btn">Update</button>
                    </div>
                    <p class="s-hint">Auto-assigned to all new users on first access.</p>
                </div>
            </form>

            <div style="margin-top:24px; padding-top:24px; border-top:1px solid #f1f5f9">
                <h4 style="font-size:13px; font-weight:800; color:#1e293b; margin-bottom:16px">Tier Utilization</h4>
                @php $t30 = $stats['by_tier']['30'] ?? 0; $t40 = $stats['by_tier']['40'] ?? 0; $tTot = max(1, $t30 + $t40); @endphp
                <div style="margin-bottom:18px">
                    <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px">
                        <span style="font-weight:700; color:#64748b">Standard (30%)</span>
                        <span style="font-weight:900; color:#0f172a">{{ number_format($t30) }}</span>
                    </div>
                    <div class="prog-bg"><div class="prog-bar" style="background:#4f46e5; width:{{ ($t30/$tTot)*100 }}%"></div></div>
                </div>
                <div>
                    <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px">
                        <span style="font-weight:700; color:#64748b">No-Upfront (40%)</span>
                        <span style="font-weight:900; color:#0f172a">{{ number_format($t40) }}</span>
                    </div>
                    <div class="prog-bg"><div class="prog-bar" style="background:#94a3b8; width:{{ ($t40/$tTot)*100 }}%"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Transactions Table --}}
<div class="premium-card">
    <div class="pc-header">
        <h3 class="pc-title">Recent MyBill Activity</h3>
        <a href="{{ route('admin.mybill.loans') }}" class="btn-tab">View History</a>
    </div>
    <div class="table-wrap">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Loan Ref</th>
                    <th>Borrower</th>
                    <th>Category</th>
                    <th>Value</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Processed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent as $loan)
                <tr>
                    <td style="font-family:monospace; font-weight:700; color:#4f46e5">#{{ $loan->loan_number }}</td>
                    <td>
                        <div style="font-weight:700; color:#1e293b; font-size:14px">{{ $loan->user->name ?? '—' }}</div>
                        <div style="font-size:11px; color:#94a3b8">{{ $loan->user->phone ?? '—' }}</div>
                    </td>
                    <td><span class="c-tag">{{ ucfirst($loan->bill_category) }}</span></td>
                    <td style="font-weight:800; color:#0f172a">M{{ number_format($loan->bill_value, 2) }}</td>
                    <td style="font-size:12px; font-weight:700; color:#64748b">{{ $loan->tier }}% Fee</td>
                    <td><span class="p-badge b{{ $loan->status === 'settled' ? 'approved' : ($loan->status === 'active' ? 'submitted' : 'rejected') }}">{{ ucfirst($loan->status) }}</span></td>
                    <td style="font-size:12px; color:#94a3b8">{{ $loan->created_at->format('d M, H:i') }}</td>
                    <td><a href="{{ route('admin.mybill.loans.show', $loan) }}" class="btn-icon"><i class="bi bi-eye-fill"></i></a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
/* Modern Slate Styles */
.glass-nav { background:#fff; border-radius:16px; margin-bottom:24px; box-shadow:0 4px 15px rgba(0,0,0,0.02); border:1px solid #f1f5f9; padding:6px; }
.gn-inner { display:flex; gap:4px; }
.gn-link { padding:10px 20px; border-radius:12px; text-decoration:none; font-size:14px; font-weight:700; color:#64748b; transition:all 0.2s; }
.gn-link:hover { background:#f8fafc; color:#0f172a; }
.gn-link.active { background:#4f46e5; color:#fff; }

.kpi-grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:32px; }
.kpi-card-simple { background:#fff; border-radius:20px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.02); border:1px solid #f1f5f9; }
.ks-label { font-size:12px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
.ks-value { font-size:28px; font-weight:900; color:#0f172a; letter-spacing:-1px; }
.ks-sub { font-size:12px; color:#64748b; font-weight:600; margin-top:4px; }

.premium-card { background:#fff; border-radius:24px; border:1px solid #f1f5f9; box-shadow:0 10px 40px rgba(0,0,0,0.03); overflow:hidden; }
.pc-header { padding:20px 24px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; }
.pc-title { font-size:16px; font-weight:900; color:#1e293b; margin:0; }
.pc-body { padding:24px; }

.category-stat-box { background:#fbfcfe; border:1px solid #f1f5f9; border-radius:16px; padding:20px; }
.cat-icon-sm { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; }
.prog-bg { background:#f1f5f9; height:6px; border-radius:3px; overflow:hidden; }
.prog-bar { height:100%; border-radius:3px; }

.settings-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:20px; }
.s-label { font-size:11px; font-weight:900; color:#64748b; text-transform:uppercase; letter-spacing:1px; }
.s-input { background:#fff; border:1px solid #d1d5db; border-radius:10px; padding:10px 16px; font-weight:700; flex:1; outline:none; transition:all 0.2s; }
.s-input:focus { border-color:#4f46e5; box-shadow:0 0 0 4px rgba(79,70,229,0.1); }
.s-btn { background:#0f172a; color:#fff; border:none; padding:10px 20px; border-radius:10px; font-weight:800; font-size:13px; cursor:pointer; }
.s-hint { font-size:11px; color:#94a3b8; margin-top:10px; font-weight:600; }

.premium-table { width:100%; border-collapse:collapse; }
.premium-table th { background:#fbfcfe; padding:16px 24px; text-align:left; font-size:11px; font-weight:800; color:#94a3b8; text-transform:uppercase; border-bottom:1px solid #f1f5f9; }
.premium-table td { padding:18px 24px; border-bottom:1px solid #f8fafc; }

.c-tag { background:#f1f5f9; color:#475569; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:800; text-transform:uppercase; }
.p-badge { padding:6px 12px; border-radius:8px; font-size:10px; font-weight:900; text-transform:uppercase; }
.bsubmitted { background:rgba(245,158,11,0.1); color:#f59e0b; }
.bapproved { background:rgba(22,163,74,0.1); color:#10b981; }
.brejected { background:rgba(239,68,68,0.1); color:#ef4444; }

.btn-icon { width:32px; height:32px; border-radius:8px; border:1px solid #e2e8f0; display:flex; align-items:center; justify-content:center; color:#94a3b8; text-decoration:none; }
.btn-icon:hover { color:#0f172a; border-color:#0f172a; background:#f8fafc; }
.p-alert-success { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; border-radius:12px; padding:14px 18px; font-weight:700; font-size:14px; }
.btn-tab { font-size:12px; font-weight:800; color:#4f46e5; text-decoration:none; border:1px solid #4f46e5; padding:6px 14px; border-radius:8px; }
</style>

@endsection
