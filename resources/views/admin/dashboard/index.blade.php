@extends('admin.layouts.app')
@section('title','Dashboard')
@section('page-title','Overview')
@section('bc') Home @endsection
@section('content')
@php
use App\Models\{Loan, LoanApplication, LoanInstallment, Payment, User, LoanProduct};
$now = now();
$totalPortfolio = Loan::whereIn('status',['active','overdue'])->sum('outstanding_balance');
$par30amount    = Loan::whereIn('status',['active','overdue'])
    ->whereHas('installments', fn($q)=>$q->where('status','overdue')->whereDate('due_date','<=',$now->copy()->subDays(30)))
    ->sum('outstanding_balance');
$par30pct    = $totalPortfolio > 0 ? round($par30amount/$totalPortfolio*100,1) : 0;
$totalPrincipal = Loan::sum('principal_amount');
$defaultAmt  = Loan::whereIn('status',['defaulted','written_off'])->sum('outstanding_balance');
$defaultRate = $totalPrincipal > 0 ? round($defaultAmt/$totalPrincipal*100,1) : 0;
$monthExpected  = LoanInstallment::whereMonth('due_date',$now->month)->whereYear('due_date',$now->year)->sum('total_amount');
$monthCollected = Payment::whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->where('status','verified')->sum('amount');
$collectionPct  = $monthExpected > 0 ? round($monthCollected/$monthExpected*100,1) : 0;

$yearData = collect(range(1,12))->map(function($m) use ($now){
    $dis = Loan::whereMonth('disbursement_date',$m)->whereYear('disbursement_date',$now->year)->sum('principal_amount');
    $col = Payment::whereMonth('created_at',$m)->whereYear('created_at',$now->year)->where('status','verified')->sum('amount');
    $exp = LoanInstallment::whereMonth('due_date',$m)->whereYear('due_date',$now->year)->sum('total_amount');
    $arr = LoanInstallment::whereMonth('due_date',$m)->whereYear('due_date',$now->year)->where('status','overdue')->sum('outstanding_amount');
    $ini = Loan::whereMonth('disbursement_date',$m)->whereYear('disbursement_date',$now->year)->get()->sum(fn($l)=>round($l->principal_amount*0.40,2));
    $adm = Loan::whereMonth('disbursement_date',$m)->whereYear('disbursement_date',$now->year)->get()->sum(fn($l)=>50*$l->term_months);
    $int = $dis > 0 ? round($dis*0.15*6,2) : 0;
    $agr = Loan::whereMonth('disbursement_date',$m)->whereYear('disbursement_date',$now->year)->count();
    $cp  = $exp > 0 ? round($col/$exp*100,1) : 0;
    $turnover = $dis + $ini + $adm + $int;
    return ['disbursed'=>$dis,'collected'=>$col,'expected'=>$exp,'arrears'=>$arr,'initiation'=>$ini,'admin'=>$adm,'interest'=>$int,'agreements'=>$agr,'collPct'=>$cp,'turnover'=>$turnover];
});

$ytdTurnover = $yearData->sum('turnover');
$months12 = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$loanStatusBreakdown = [
    'Active' => Loan::where('status','active')->count(),
    'Overdue' => Loan::where('status','overdue')->count(),
    'Closed' => Loan::where('status','closed')->count(),
    'Written Off' => Loan::where('status','written_off')->count()
];
@endphp

{{-- Dashboard Header --}}
<div class="dash-hero">
    <div style="flex:1">
        <h1 style="font-size:28px; font-weight:900; color:#0f172a; margin:0; letter-spacing:-1px">Management Dashboard</h1>
        <div style="display:flex; align-items:center; gap:12px; margin-top:6px; color:#64748b; font-size:14px; font-weight:600">
            <span><i class="bi bi-calendar3"></i> {{ $now->format('l, d F Y') }}</span>
            <span style="color:#e2e8f0">|</span>
            <span style="color:#10b981"><i class="bi bi-circle-fill" style="font-size:8px"></i> System Online</span>
        </div>
    </div>
    <div style="display:flex; gap:12px">
        <a href="{{ route('admin.applications.create') }}" class="btn-premium">
            <i class="bi bi-plus-lg"></i> New Loan App
        </a>
    </div>
</div>

{{-- KPI SECTION --}}
<div class="kpi-grid">
    {{-- Total Portfolio --}}
    <div class="kpi-card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start">
            <div>
                <span class="kpi-label">Active Portfolio</span>
                <div class="kpi-value">M{{ number_format($totalPortfolio, 0) }}</div>
            </div>
            <div class="kpi-icon" style="background:rgba(79,70,229,0.1); color:#4f46e5"><i class="bi bi-safe2"></i></div>
        </div>
        <div class="kpi-footer">
            <span style="color:#10b981; font-weight:700">+12.5%</span> <span style="color:#94a3b8">vs last month</span>
        </div>
    </div>

    {{-- Month Expected --}}
    <div class="kpi-card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start">
            <div>
                <span class="kpi-label">Month Expected</span>
                <div class="kpi-value">M{{ number_format($monthExpected, 0) }}</div>
            </div>
            <div class="kpi-icon" style="background:rgba(6,182,212,0.1); color:#0891b2"><i class="bi bi-calendar-check"></i></div>
        </div>
        <div class="kpi-footer">
            <span style="color:#94a3b8">Target: M{{ number_format($monthExpected * 1.2, 0) }}</span>
        </div>
    </div>

    {{-- Month Collected --}}
    <div class="kpi-card highlight">
        <div style="display:flex; justify-content:space-between; align-items:flex-start">
            <div>
                <span class="kpi-label" style="color:rgba(255,255,255,0.7)">Month Collected</span>
                <div class="kpi-value" style="color:#fff">M{{ number_format($monthCollected, 0) }}</div>
            </div>
            <div class="kpi-icon" style="background:rgba(255,255,255,0.2); color:#fff"><i class="bi bi-cash-stack"></i></div>
        </div>
        <div class="kpi-footer" style="color:rgba(255,255,255,0.8)">
            <i class="bi bi-graph-up-arrow"></i> {{ $collectionPct }}% Collection Rate
        </div>
    </div>

    {{-- PAR 30 --}}
    <div class="kpi-card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start">
            <div>
                <span class="kpi-label">PAR 30+ Risk</span>
                <div class="kpi-value" style="color:{{ $par30pct > 5 ? '#ef4444' : '#1e293b' }}">{{ $par30pct }}%</div>
            </div>
            <div class="kpi-icon" style="background:rgba(239,68,68,0.1); color:#ef4444"><i class="bi bi-shield-exclamation"></i></div>
        </div>
        <div class="kpi-footer">
            <span style="color:{{ $par30pct > 5 ? '#ef4444' : '#10b981' }}; font-weight:700">{{ $par30pct > 5 ? 'Above' : 'Within' }} Tolerance</span>
        </div>
    </div>

    {{-- Turnovber --}}
    <div class="kpi-card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start">
            <div>
                <span class="kpi-label">YTD Turnover</span>
                <div class="kpi-value">M{{ number_format($ytdTurnover/1000, 1) }}k</div>
            </div>
            <div class="kpi-icon" style="background:rgba(139,92,246,0.1); color:#8b5cf6"><i class="bi bi-speedometer2"></i></div>
        </div>
        <div class="kpi-footer">
            <span style="color:#94a3b8">Total Revenue & Capital</span>
        </div>
    </div>
</div>

{{-- MAIN CHARTS ROW --}}
<div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px; margin-bottom:24px">
    {{-- Lending Performance --}}
    <div class="premium-card">
        <div class="pc-header">
            <div>
                <h3 class="pc-title">Monthly Lending Performance</h3>
                <p class="pc-subtitle">Comparison between Disbursements and Collections</p>
            </div>
            <div class="pc-actions">
                <button class="btn-tab active">Amounts</button>
                <button class="btn-tab">Volume</button>
            </div>
        </div>
        <div class="pc-body">
            <canvas id="cLendingPerformance" height="320"></canvas>
        </div>
    </div>

    {{-- Status Breakdown --}}
    <div class="premium-card">
        <div class="pc-header">
            <h3 class="pc-title">Portfolio Status</h3>
        </div>
        <div class="pc-body" style="padding-top:20px">
            <div style="position:relative; height:200px">
                <canvas id="cPortfolioDoughnut"></canvas>
            </div>
            <div class="status-legend">
                @foreach([['Active','#10b981',$loanStatusBreakdown['Active']],['Overdue','#ef4444',$loanStatusBreakdown['Overdue']],['Closed','#64748b',$loanStatusBreakdown['Closed']],['Other','#374151',$loanStatusBreakdown['Written Off']]] as [$lbl,$c,$v])
                <div class="sl-item">
                    <span class="sl-dot" style="background:{{ $c }}"></span>
                    <span class="sl-label">{{ $lbl }}</span>
                    <span class="sl-value">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- BOTTOM SECTION: Recent Activity --}}
<div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:24px">
    {{-- Recent Applications --}}
    <div class="premium-card">
        <div class="pc-header">
            <h3 class="pc-title">Incoming Applications</h3>
            <a href="{{ route('admin.applications.index') }}" class="pc-link">Review All <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="table-wrap">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentApplications as $app)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px">
                                <div class="avatar-sm">{{ strtoupper(substr($app->applicant_name,0,1)) }}</div>
                                <div>
                                    <div style="font-weight:700; color:#1e293b">{{ $app->applicant_name }}</div>
                                    <div style="font-size:11px; color:#94a3b8">{{ $app->application_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:13px; color:#64748b">{{ $app->loanProduct?->name ?? '—' }}</td>
                        <td style="font-weight:800; color:#0f172a">M{{ number_format($app->requested_amount,0) }}</td>
                        <td><span class="p-badge b{{ $app->status_badge }}">{{ ucfirst($app->status) }}</span></td>
                        <td><a href="{{ route('admin.applications.show',$app) }}" class="btn-icon"><i class="bi bi-chevron-right"></i></a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Overdue Radar --}}
    <div class="premium-card">
        <div class="pc-header">
            <h3 class="pc-title" style="color:#ef4444"><i class="bi bi-lightning-fill"></i> Arrears Watchlist</h3>
            <span class="pc-tag-red">{{ count($overdueLoans) }} AT RISK</span>
        </div>
        <div class="pc-body" style="padding:0">
            @forelse($overdueLoans->take(5) as $loan)
            <a href="{{ route('admin.loans.show',$loan) }}" class="overdue-item">
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:center; color:#ef4444">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div style="flex:1">
                    <div style="font-weight:700; font-size:14px; color:#1e293b">{{ $loan->user->name ?? '—' }}</div>
                    <div style="font-size:11px; color:#94a3b8">{{ $loan->days_overdue }} days overdue</div>
                </div>
                <div style="text-align:right">
                    <div style="font-weight:900; color:#ef4444; font-size:15px">M{{ number_format($loan->outstanding_balance,0) }}</div>
                    <div style="font-size:10px; color:#94a3b8; font-weight:700">#{{ $loan->loan_number }}</div>
                </div>
            </a>
            @empty
            <div style="padding:60px; text-align:center; color:#94a3b8">
                <i class="bi bi-check2-circle" style="font-size:48px; color:#10b981; opacity:0.3"></i>
                <p style="margin-top:12px; font-weight:600">No critical overdue items</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
/* Dashboard Layout */
.dash-hero { display:flex; align-items:center; justify-content:space-between; margin-bottom:32px; padding:0 4px; }
.kpi-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:20px; margin-bottom:32px; }

/* KPI Cards */
.kpi-card { 
    background:#fff; border-radius:20px; padding:24px; 
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 10px 15px -3px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.03); transition: all 0.3s;
}
.kpi-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05); }
.kpi-card.highlight { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border:none; }

.kpi-label { font-size:12px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; display:block; }
.kpi-value { font-size:32px; font-weight:900; color:#0f172a; letter-spacing:-1.5px; }
.kpi-icon { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px; }
.kpi-footer { margin-top:16px; font-size:12px; font-weight:600; display:flex; gap:8px; align-items:center; }

/* Premium Cards */
.premium-card { background:#fff; border-radius:24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.03); overflow:hidden; }
.pc-header { padding:24px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; }
.pc-title { font-size:18px; font-weight:900; color:#0f172a; margin:0; letter-spacing:-0.5px; }
.pc-subtitle { font-size:13px; color:#64748b; margin:4px 0 0; font-weight:500; }
.pc-body { padding:24px; }
.pc-link { font-size:13px; font-weight:800; color:#4f46e5; text-decoration:none; display:flex; align-items:center; gap:6px; }

/* Buttons */
.btn-premium { 
    background:#4f46e5; color:#fff; padding:12px 24px; border-radius:14px; font-weight:800; 
    font-size:14px; text-decoration:none; display:flex; align-items:center; gap:8px;
    box-shadow: 0 10px 20px -5px rgba(79,70,229,0.4); transition: all 0.3s;
}
.btn-premium:hover { background:#4338ca; transform:translateY(-2px); box-shadow: 0 15px 30px -5px rgba(79,70,229,0.5); }
.btn-tab { background:#f8fafc; border:1px solid #e2e8f0; padding:8px 16px; border-radius:8px; font-size:12px; font-weight:800; color:#64748b; cursor:pointer; }
.btn-tab.active { background:#0f172a; color:#fff; border-color:#0f172a; }

/* Status Legend */
.status-legend { margin-top:24px; display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.sl-item { display:flex; align-items:center; gap:8px; }
.sl-dot { width:10px; height:10px; border-radius:50%; }
.sl-label { font-size:13px; font-weight:700; color:#64748b; }
.sl-value { font-weight:900; color:#0f172a; margin-left:auto; }

/* Tables */
.premium-table { width:100%; border-collapse:collapse; }
.premium-table th { padding:16px 24px; text-align:left; font-size:11px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:1px; background:#fbfcfe; }
.premium-table td { padding:16px 24px; border-bottom:1px solid #f1f5f9; }
.avatar-sm { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg, #4f46e5, #8b5cf6); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; }

/* Status Badges */
.p-badge { padding:6px 12px; border-radius:8px; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:0.5px; }
.bsubmitted, .bunder_review { background:rgba(245,158,11,0.1); color:#f59e0b; }
.bapproved, .bactive { background:rgba(16,185,129,0.1); color:#10b981; }

/* Overdue Items */
.overdue-item { 
    display:flex; align-items:center; gap:16px; padding:18px 24px; 
    border-bottom:1px solid #f1f5f9; text-decoration:none; transition: all 0.2s;
}
.overdue-item:hover { background:#fff5f5; }
.pc-tag-red { background:rgba(239,68,68,0.1); color:#ef4444; font-size:11px; font-weight:900; padding:4px 10px; border-radius:6px; }

.btn-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; border:1px solid #e2e8f0; color:#94a3b8; text-decoration:none; }
.btn-icon:hover { color:#0f172a; border-color:#0f172a; }
</style>

@push('scripts')
<script>
(function(){
    const months = @json($months12);
    const yd = @json($yearData->values());
    const lsb = @json($loanStatusBreakdown);
    
    // Performance Chart
    new Chart(document.getElementById('cLendingPerformance'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Disbursed',
                    data: yd.map(d => d.disbursed),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 4,
                    pointRadius: 0,
                    pointHoverRadius: 6
                },
                {
                    label: 'Collected',
                    data: yd.map(d => d.collected),
                    borderColor: '#10b981',
                    borderWidth: 3,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4,
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
            scales: {
                y: { grid: { color: '#f1f5f9', drawBorder: false }, ticks: { font: { size: 11, weight: '600' }, color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' }, color: '#94a3b8' } }
            }
        }
    });

    // Portfolio Doughnut
    new Chart(document.getElementById('cPortfolioDoughnut'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(lsb),
            datasets: [{
                data: Object.values(lsb),
                backgroundColor: ['#10b981', '#ef4444', '#64748b', '#374151'],
                borderWidth: 4,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { legend: { display: false } }
        }
    });
})();
</script>
@endpush
@endsection
