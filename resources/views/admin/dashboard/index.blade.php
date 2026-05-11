@extends('admin.layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Business Intelligence Overview')
@section('bc') Dashboard @endsection
@section('content')

    @php
        $cur  = $periodStats['current'];
        $prev = $periodStats['previous'];
        $chg  = $periodStats['changes'];
        // Quarter number helper
        $quarterNum = now()->quarter;
        $quarterNames = [1=>'1st Quarter',2=>'2nd Quarter',3=>'3rd Quarter',4=>'4th Quarter'];
    @endphp

    <div class="d-wrap">

        {{-- ══════════════ HEADER ══════════════ --}}
        <div class="d-header">
            <div>
                <h1 class="d-title">Welcome back, {{ auth('admin')->user()->name ?? 'Admin' }}</h1>
                <p class="d-sub">
                    {{ now()->format('l, d F Y') }}
                    &nbsp;·&nbsp;
                    @if($activePeriod === 'month')
                        Showing: <strong>{{ $periodLabel }}</strong> vs {{ $prevPeriodLabel }}
                    @elseif($activePeriod === 'quarter')
                        Showing: <strong>{{ $quarterNames[$quarterNum] }} — {{ $periodLabel }}</strong> vs {{ $prevPeriodLabel }}
                    @else
                        Showing: <strong>Year {{ $periodLabel }}</strong> (Jan–{{ now()->format('M') }}) vs Year {{ $prevPeriodLabel }}
                    @endif
                </p>
            </div>
            <div class="d-header-right">
                <form action="{{ route('admin.dashboard') }}" method="GET" class="d-period">
                    <span class="d-period-ico">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </span>
                    <select name="period" onchange="this.form.submit()" class="d-period-sel">
                        <option value="month" {{ $activePeriod == 'month' ? 'selected' : '' }}>This Month — {{ now()->format('F Y') }}</option>
                        <option value="quarter" {{ $activePeriod == 'quarter' ? 'selected' : '' }}>This Quarter — {{ $quarterNames[$quarterNum] }}</option>
                        <option value="year" {{ $activePeriod == 'year' ? 'selected' : '' }}>This Year — {{ now()->year }}</option>
                    </select>
                </form>
                <a href="{{ route('admin.applications.index') }}" class="d-btn-primary">+ New Application</a>
            </div>
        </div>

        {{-- ══════════════ KPI STRIP — Row 1: 4 cards ══════════════ --}}
        @php
            $vsLabel = $prevPeriodLabel;
        @endphp
        <div class="kpi-grid kpi-row-4">
            {{-- 1. Applications (period) --}}
            <a href="{{ route('admin.applications.index') }}" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Applications</span>
                    <span class="kpi-ico kpi-ico--blue">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                </div>
                <div class="kpi-val">{{ number_format($cur['applications']) }}</div>
                <div class="kpi-sub-val">All-time: {{ number_format($stats['apps_submitted']) }}</div>
                <div class="kpi-badge kpi-badge--{{ $chg['applications'] >= 0 ? 'up' : 'down' }}">
                    @if($chg['applications'] >= 0)<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>@else<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>@endif
                    {{ abs($chg['applications']) }}% vs {{ $vsLabel }}
                </div>
            </a>

            {{-- 2. Approved (period) --}}
            <a href="{{ route('admin.applications.index', ['status'=>'approved']) }}" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Approved</span>
                    <span class="kpi-ico kpi-ico--green">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="kpi-val">{{ number_format($cur['approved']) }}</div>
                <div class="kpi-sub-val">All-time: {{ number_format($stats['apps_approved']) }}</div>
                <div class="kpi-badge kpi-badge--{{ $chg['approved'] >= 0 ? 'up' : 'down' }}">
                    @if($chg['approved'] >= 0)<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>@else<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>@endif
                    {{ abs($chg['approved']) }}% vs {{ $vsLabel }}
                </div>
            </a>

            {{-- 3. Disbursed (period) --}}
            <a href="{{ route('admin.loans.index') }}" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Disbursed</span>
                    <span class="kpi-ico kpi-ico--indigo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </span>
                </div>
                <div class="kpi-val">M{{ number_format($cur['disbursed'], 2) }}</div>
                <div class="kpi-sub-val">MTD: M{{ number_format($stats['disbursed_month'], 2) }}</div>
                <div class="kpi-badge kpi-badge--{{ $chg['disbursed'] >= 0 ? 'up' : 'down' }}">
                    @if($chg['disbursed'] >= 0)<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>@else<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>@endif
                    {{ abs($chg['disbursed']) }}% vs {{ $vsLabel }}
                </div>
            </a>

            {{-- 4. Active Loans (global, not period) --}}
            <a href="{{ route('admin.loans.index', ['status'=>'active']) }}" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Active Loans</span>
                    <span class="kpi-ico kpi-ico--orange">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                </div>
                <div class="kpi-val">{{ number_format($stats['active_loans']) }}</div>
                <div class="kpi-sub-val">Overdue: {{ number_format($stats['overdue_loans']) }}</div>
                <div class="kpi-badge kpi-badge--up">Live portfolio</div>
            </a>
        </div>

        {{-- ══════════════ KPI STRIP — Row 2: 5 cards ══════════════ --}}
        <div class="kpi-grid kpi-row-5" style="margin-top:10px">
            {{-- 5. Pending Review — URGENT ACTION CARD --}}
            <a href="{{ route('admin.applications.index', ['status'=>'submitted']) }}" class="kpi-card kpi-card--urgent">
                <div class="kpi-top">
                    <span class="kpi-label">⚡ Pending Review</span>
                    <span class="kpi-ico kpi-ico--amber">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                </div>
                <div class="kpi-val" style="color:#d97706">{{ number_format($stats['apps_pending_review']) }}</div>
                <div class="kpi-sub-val">Need action now</div>
                <div class="kpi-badge" style="background:#fef3c7;color:#92400e">Action required</div>
            </a>

            {{-- 6. Declined (period) --}}
            <a href="{{ route('admin.applications.index', ['status'=>'declined']) }}" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Declined</span>
                    <span class="kpi-ico kpi-ico--red">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="kpi-val">{{ number_format($cur['declined']) }}</div>
                <div class="kpi-sub-val">All-time: {{ number_format($stats['apps_declined']) }}</div>
                <div class="kpi-badge kpi-badge--{{ $chg['declined'] <= 0 ? 'up' : 'down' }}">
                    @if($chg['declined'] <= 0)<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>@else<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>@endif
                    {{ abs($chg['declined']) }}% vs {{ $vsLabel }}
                </div>
            </a>

            {{-- 7. Collection Rate (MTD) --}}
            <a href="#" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Collection Rate</span>
                    <span class="kpi-ico kpi-ico--teal">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                </div>
                <div class="kpi-val">{{ $stats['collection_pct'] }}%</div>
                <div class="kpi-sub-val">M{{ number_format($stats['month_collected'],2) }} collected</div>
                <div class="kpi-badge kpi-badge--up">MTD</div>
            </a>

            {{-- 8. PAR 30 --}}
            <a href="#" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">PAR 30</span>
                    <span class="kpi-ico kpi-ico--rose">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                    </span>
                </div>
                <div class="kpi-val">{{ $stats['par30_pct'] }}%</div>
                <div class="kpi-sub-val">M{{ number_format($stats['par30_amount'],2) }} at risk</div>
                <div class="kpi-badge kpi-badge--{{ $stats['par30_pct'] <= 5 ? 'up' : 'down' }}">Portfolio risk</div>
            </a>

            {{-- 9. Revenue (period) --}}
            <a href="#" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Revenue</span>
                    <span class="kpi-ico kpi-ico--violet">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 6v1m0 4v1m-3-4h.01M17 16.01h.01"/></svg>
                    </span>
                </div>
                <div class="kpi-val">M{{ number_format($stats['total_revenue'], 2) }}</div>
                <div class="kpi-sub-val">All-time (est.)</div>
                <div class="kpi-badge kpi-badge--up">YTD total</div>
            </a>
        </div>

        {{-- ══════════════ MAIN BODY ══════════════ --}}
        <div class="body-grid">

            {{-- LEFT column --}}
            <div class="body-left">

                {{-- Applications Trend Chart --}}
                <div class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Applications Trend</div>
                            <div class="card-sub">Daily — {{ now()->format('F Y') }}</div>
                        </div>
                        <div class="chart-legend-row">
                            <span class="cl-item"><span class="cl-dot" style="background:#3b82f6"></span>Submitted</span>
                            <span class="cl-item"><span class="cl-dot" style="background:#10b981"></span>Approved</span>
                            <span class="cl-item"><span class="cl-dot cl-dash"
                                    style="background:#ef4444"></span>Declined</span>
                        </div>
                    </div>
                    <div style="position:relative;height:240px;padding:0 20px 16px">
                        <canvas id="chartTrend" role="img"
                            aria-label="Line chart showing daily application trends for submitted, approved, and declined"></canvas>
                    </div>
                </div>

                {{-- Two-col: Donut + Loan Book --}}
                <div class="two-col">
                    <div class="card">
                        <div class="card-head">
                            <div class="card-title">By Status</div>
                        </div>
                        <div class="donut-wrap">
                            <canvas id="chartStatus" role="img" aria-label="Donut chart of application statuses"></canvas>
                            <div class="donut-center">
                                <div class="dc-val">{{ number_format($stats['apps_submitted']) }}</div>
                                <div class="dc-lbl">Total</div>
                            </div>
                        </div>
                        <div class="legend-list">
                            <div class="ll-row"><span class="ll-dot"
                                    style="background:#3b82f6"></span><span>Submitted</span><strong>{{ number_format($stats['apps_submitted']) }}</strong>
                            </div>
                            <div class="ll-row"><span class="ll-dot"
                                    style="background:#10b981"></span><span>Approved</span><strong>{{ number_format($stats['apps_approved']) }}
                                    ({{ round($stats['apps_approved'] / $stats['apps_submitted'] * 100) }}%)</strong></div>
                            <div class="ll-row"><span class="ll-dot"
                                    style="background:#ef4444"></span><span>Declined</span><strong>{{ number_format($stats['apps_declined']) }}
                                    ({{ round($stats['apps_declined'] / $stats['apps_submitted'] * 100) }}%)</strong></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <div class="card-title">Loan Book</div>
                        </div>
                        <div class="lb-hero">
                            <div class="lb-lbl">Outstanding Portfolio</div>
                            <div class="lb-big">M{{ number_format($stats['total_portfolio'], 2) }}</div>
                            <div class="lb-trend">↑ 18.4% vs last month</div>
                        </div>
                        <div class="lb-pair">
                            <div class="lb-stat">
                                <div class="lbs-lbl">Avg Loan</div>
                            <div class="lbs-val">M{{ number_format($stats['avg_loan_size'], 2) }}</div>
                            </div>
                            <div class="lb-stat">
                                <div class="lbs-lbl">Avg Tenure</div>
                                <div class="lbs-val">3.2 mo</div>
                            </div>
                        </div>
                        <div class="seg-block">
                            <div class="seg-title">By Segment</div>
                            @php
                                $totalPortfolio = $stats['total_portfolio'] ?: 1;
                                $segs = collect($segmentBreakdown)->map(function($s) use ($totalPortfolio) {
                                    return [
                                        'label' => ucfirst(str_replace('_', ' ', $s->employer_type)),
                                        'pct' => round(($s->portfolio / $totalPortfolio) * 100, 1),
                                        'val' => $s->portfolio
                                    ];
                                })->sortByDesc('pct');
                            @endphp
                            @foreach($segs as $s)
                                <div class="seg-row">
                                    <div class="seg-label">{{ $s['label'] }}</div>
                                    <div class="seg-bar-wrap">
                                        <div class="seg-bar" style="width:{{ $s['pct'] }}%"></div>
                                    </div>
                                    <div class="seg-pct">{{ $s['pct'] }}%</div>
                                </div>
                            @endforeach
                            @if($segs->isEmpty())
                                <div class="text-muted small">No data available</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Recent Applications Table --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">Recent Applications</div>
                        <a href="{{ route('admin.applications.index') }}" class="card-link">View all →</a>
                    </div>
                    <div class="tbl-wrap">
                        <table class="dtbl">
                            <thead>
                                <tr>
                                    <th>App ID</th>
                                    <th>Applicant</th>
                                    <th>Segment</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentApplications->take(6) as $app)
                                    <tr>
                                        <td class="td-id">{{ $app->application_number }}</td>
                                        <td class="td-name">{{ $app->user->name ?? 'N/A' }}</td>
                                        <td><span class="seg-pill">Private</span></td>
                                        <td class="td-amount">M{{ number_format($app->requested_amount, 2) }}</td>
                                        <td><span class="status-pill sp-{{ $app->status }}">{{ ucfirst($app->status) }}</span>
                                        </td>
                                        <td class="td-date">{{ $app->created_at->format('d M Y') }}</td>
                                        <td><a href="{{ route('admin.applications.show', $app->id) }}" class="ico-btn"
                                                title="View">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ═══ PROFITABILITY — must be exact, not estimated ═══ --}}
                @php
                    $interestIncome = $stats['total_interest_revenue'];
                    $feeIncome = $stats['total_fee_revenue'];
                    $totalRevenue = $stats['total_revenue'];
                    $writtenOff = $stats['written_off_amount'];
                    $netProfit = $totalRevenue - $writtenOff;
                    $netMargin = $totalRevenue > 0 ? round($netProfit / $totalRevenue * 100, 1) : 0;
                @endphp
                <div class="two-col">
                    <div class="card">
                        <div class="card-head">
                            <div class="card-title">Profitability <span class="card-badge">YTD</span></div>
                            <a href="#" class="card-link">P&L →</a>
                        </div>
                        <div class="pl-grid">
                            <div class="pl-item">
                                <div class="pl-lbl">Interest Income</div>
                                <div class="pl-val">M{{ number_format($interestIncome, 2) }}</div>
                            </div>
                            <div class="pl-item">
                                <div class="pl-lbl">Fee Income</div>
                                <div class="pl-val">M{{ number_format($feeIncome, 2) }}</div>
                            </div>
                            <div class="pl-item pl-total">
                                <div class="pl-lbl">Total Revenue</div>
                                <div class="pl-val">M{{ number_format($totalRevenue, 2) }}</div>
                            </div>
                            <div class="pl-divider"></div>
                            <div class="pl-item">
                                <div class="pl-lbl">Write-Offs</div>
                                <div class="pl-val" style="color:#dc2626">M{{ number_format($writtenOff, 2) }}</div>
                            </div>
                            <div class="pl-item">
                                <div class="pl-lbl">Total Disbursed</div>
                                <div class="pl-val">M{{ number_format($stats['total_disbursed'], 2) }}</div>
                            </div>
                            <div class="pl-item pl-profit">
                                <div class="pl-lbl">Net Profit</div>
                                <div class="pl-val">M{{ number_format($netProfit, 2) }}</div>
                                <div class="pl-margin">{{ $netMargin }}% margin</div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <div class="card-title">Collections <span class="card-badge">MTD</span></div>
                            <a href="#" class="card-link">Report →</a>
                        </div>
                        <div class="col-trio">
                            <div class="col-stat">
                                <div class="cs-lbl">Total Due</div>
                                <div class="cs-val">M{{ number_format($stats['month_expected'], 2) }}</div>
                            </div>
                            <div class="col-stat">
                                <div class="cs-lbl">Collected</div>
                                <div class="cs-val cs-green">M{{ number_format($stats['month_collected'], 2) }}</div>
                            </div>
                            <div class="col-stat">
                                <div class="cs-lbl">Rate</div>
                                <div class="cs-val cs-green">{{ $stats['collection_pct'] }}%</div>
                            </div>
                        </div>
                        <div class="aging-block">
                            <div class="aging-head">
                                <span>Overdue Aging</span>
                                <span class="aging-total">M{{ number_format($stats['overdue_total'], 2) }}</span>
                            </div>
                            @php
                                $overdueTotal = $stats['overdue_total'] ?: 1;
                                $aging = [
                                    ['label' => '1–7 days', 'val' => $stats['overdue_1_7'], 'pct' => round($stats['overdue_1_7'] / $overdueTotal * 100)],
                                    ['label' => '8–30 days', 'val' => $stats['overdue_8_30'], 'pct' => round($stats['overdue_8_30'] / $overdueTotal * 100)],
                                    ['label' => '31–60 days', 'val' => $stats['overdue_31_60'], 'pct' => round($stats['overdue_31_60'] / $overdueTotal * 100)],
                                    ['label' => '60+ days', 'val' => $stats['overdue_60p'], 'pct' => round($stats['overdue_60p'] / $overdueTotal * 100)],
                                ];
                            @endphp
                            @foreach($aging as $a)
                                <div class="aging-row">
                                    <span class="aging-label">{{ $a['label'] }}</span>
                                    <div class="aging-bar-wrap">
                                        <div class="aging-bar" style="width:{{ $a['pct'] }}%"></div>
                                    </div>
                                    <span class="aging-val">M{{ number_format($a['val'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ═══ TOP REFERRERS — moved below profitability per Charles ═══ --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">Top Referrers</div>
                        <a href="#" class="card-link">All →</a>
                    </div>
                    <div class="tbl-wrap">
                        <table class="dtbl">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th style="text-align:center">Referrals</th>
                                    <th style="text-align:center">Qualified</th>
                                    <th style="text-align:right">Earned</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topReferrers as $r)
                                    <tr>
                                        <td class="td-name">{{ $r->name }}</td>
                                        <td style="text-align:center">{{ $r->total_referrals }}</td>
                                        <td style="text-align:center">{{ $r->qualified }}</td>
                                        <td style="text-align:right;font-weight:700">M{{ number_format($r->total_earned, 2) }}</td>
                                    </tr>
                                @endforeach
                                @if(empty($topReferrers))
                                    <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:20px">No referrals yet</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>{{-- /body-left --}}

            {{-- RIGHT column --}}
            <div class="body-right">

                {{-- Alerts --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">Alerts</div>
                        <a href="#" class="card-link">All →</a>
                    </div>
                    <div class="alerts-wrap">
                        <div class="alert-row alert-warn">
                            <span class="alert-ico">⚠</span>
                            <div>
                                <div class="alert-msg">PAR 30 for Private Sector rose to <strong>3.65%</strong></div>
                                <div class="alert-time">10 min ago</div>
                            </div>
                        </div>
                        <div class="alert-row alert-info">
                            <span class="alert-ico">ℹ</span>
                            <div>
                                <div class="alert-msg"><strong>12 loans</strong> due today · M76,450 total</div>
                                <div class="alert-time">20 min ago</div>
                            </div>
                        </div>
                        <div class="alert-row alert-ok">
                            <span class="alert-ico">✓</span>
                            <div>
                                <div class="alert-msg">Referral payouts of <strong>M5,600</strong> completed</div>
                                <div class="alert-time">1 hr ago</div>
                            </div>
                        </div>
                        <div class="alert-row alert-info">
                            <span class="alert-ico">↑</span>
                            <div>
                                <div class="alert-msg">Repeat borrowing rate hit <strong>41%</strong> this month</div>
                                <div class="alert-time">2 hr ago</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Segment Donut --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">By Segment</div>
                    </div>
                    <div class="donut-wrap">
                        <canvas id="chartSeg" role="img"
                            aria-label="Donut chart of applications by borrower segment"></canvas>
                    </div>
                    <div class="legend-list">
                        <div class="ll-row"><span class="ll-dot"
                                style="background:#3b82f6"></span><span>Government</span><strong>512 (41%)</strong></div>
                        <div class="ll-row"><span class="ll-dot" style="background:#10b981"></span><span>Private
                                Sector</span><strong>512 (41%)</strong></div>
                        <div class="ll-row"><span class="ll-dot"
                                style="background:#f59e0b"></span><span>Pensioners</span><strong>224 (18%)</strong></div>
                    </div>
                </div>

                {{-- PAR Trend --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">PAR Quality</div>
                        <div class="chart-legend-row">
                            <span class="cl-item"><span class="cl-dot" style="background:#10b981"></span>PAR 1</span>
                            <span class="cl-item"><span class="cl-dot" style="background:#f59e0b"></span>PAR 7</span>
                            <span class="cl-item"><span class="cl-dot" style="background:#ef4444"></span>PAR 30</span>
                        </div>
                    </div>
                    <div style="position:relative;height:160px;padding:0 16px 16px">
                        <canvas id="chartPAR" role="img"
                            aria-label="PAR trend lines for 1, 7 and 30 day delinquency"></canvas>
                    </div>
                </div>

                {{-- Referral Funnel --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">Referral Funnel</div>
                        <a href="#" class="card-link">Report →</a>
                    </div>
                    @php
                        $funnel = [
                            ['label' => 'Link Clicks', 'val' => 12842, 'pct' => null],
                            ['label' => 'Apps Started', 'val' => 3421, 'pct' => 26.6],
                            ['label' => 'Submitted', 'val' => $stats['apps_submitted'], 'pct' => 36.5],
                            ['label' => 'Disbursed', 'val' => $stats['apps_approved'], 'pct' => 67.5],
                            ['label' => '1st Payment', 'val' => 632, 'pct' => 75.1],
                        ];
                    @endphp
                    <div class="funnel-list">
                        @foreach($funnel as $i => $f)
                            <div class="fn-step">
                                <div class="fn-num">{{ $i + 1 }}</div>
                                <div class="fn-bar-col">
                                    <div class="fn-label">{{ $f['label'] }}</div>
                                    @if($f['pct'])
                                        <div class="fn-bar-wrap">
                                            <div class="fn-bar" style="width:{{ min(100, $f['pct'] * 1.5) }}%"></div>
                                        </div>
                                    @endif
                                </div>
                                <div class="fn-right">
                                    <div class="fn-val">{{ number_format($f['val']) }}</div>
                                    @if($f['pct'])
                                    <div class="fn-pct">{{ $f['pct'] }}%</div>@endif
                                </div>
                            </div>
                        @endforeach
                        <div class="fn-footer">Overall conversion: <strong>4.92%</strong></div>
                    </div>
                </div>

                {{-- Top Referrers moved to main body below Profitability --}}

                {{-- Payout Summary --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">Payout Summary</div>
                    </div>
                    <div class="payout-list">
                        <div class="po-row"><span>Total
                                Eligible</span><strong>M{{ number_format($stats['pending_payouts'] + $stats['total_paid_out'], 2) }}</strong>
                        </div>
                        <div class="po-row po-paid"><span>Paid
                                Out</span><strong>M{{ number_format($stats['total_paid_out'], 2) }}</strong></div>
                        <div class="po-row po-pending">
                            <span>Pending</span><strong>M{{ number_format($stats['pending_payouts'], 2) }}</strong>
                        </div>
                        <div class="po-row po-muted"><span>Disqualified</span><strong>M650</strong></div>
                    </div>
                    <a href="#" class="card-footer-link">Full payout report →</a>
                </div>

            </div>{{-- /body-right --}}
        </div>{{-- /body-grid --}}
    </div>{{-- /d-wrap --}}

    <style>
        /* ══ Reset & Base ══ */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        .d-wrap {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f0f2f5;
            padding: 24px;
            min-height: 100vh;
            color: #111827
        }

        a {
            text-decoration: none;
            color: inherit
        }

        /* ══ Header ══ */
        .d-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px
        }

        .d-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2
        }

        .d-sub {
            font-size: 13px;
            color: #6b7280;
            margin-top: 2px
        }

        .d-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap
        }

        .d-period {
            display: flex;
            align-items: center;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0 12px;
            height: 36px;
            gap: 8px
        }

        .d-period-ico {
            color: #9ca3af;
            display: flex;
            align-items: center
        }

        .d-period-sel {
            border: none;
            outline: none;
            font-size: 13px;
            font-weight: 600;
            background: transparent;
            color: #374151;
            cursor: pointer
        }

        .d-btn-primary {
            background: #3b82f6;
            color: #fff;
            border-radius: 8px;
            padding: 0 16px;
            height: 36px;
            display: flex;
            align-items: center;
            font-size: 13px;
            font-weight: 600;
            transition: background .15s
        }

        .d-btn-primary:hover {
            background: #2563eb
        }

        /* ══ KPI Grid ══ */
        .kpi-grid {
            display: grid;
            gap: 12px;
            margin-bottom: 4px
        }

        .kpi-row-4 {
            grid-template-columns: repeat(4, 1fr)
        }

        .kpi-row-5 {
            grid-template-columns: repeat(5, 1fr);
            margin-bottom: 20px
        }

        .kpi-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            transition: box-shadow .15s, transform .15s;
            cursor: pointer
        }

        .kpi-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
            transform: translateY(-2px)
        }

        .kpi-top {
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .kpi-label {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .4px
        }

        .kpi-ico {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0
        }

        .kpi-ico--blue {
            background: #eff6ff;
            color: #3b82f6
        }

        .kpi-ico--green {
            background: #f0fdf4;
            color: #16a34a
        }

        .kpi-ico--red {
            background: #fef2f2;
            color: #dc2626
        }

        .kpi-ico--indigo {
            background: #eef2ff;
            color: #4f46e5
        }

        .kpi-ico--orange {
            background: #fff7ed;
            color: #ea580c
        }

        .kpi-ico--teal {
            background: #f0fdfa;
            color: #0d9488
        }

        .kpi-ico--rose {
            background: #fff1f2;
            color: #e11d48
        }

        .kpi-ico--violet {
            background: #f5f3ff;
            color: #7c3aed
        }

        .kpi-ico--amber {
            background: #fffbeb;
            color: #d97706
        }

        .kpi-card--urgent {
            border: 1.5px solid #fbbf24;
            background: #fffbeb
        }

        .kpi-card--urgent:hover {
            box-shadow: 0 4px 16px rgba(251, 191, 36, .3);
            transform: translateY(-2px)
        }

        .kpi-sub-val {
            font-size: 11px;
            color: #9ca3af;
            font-weight: 500
        }

        .kpi-val {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            line-height: 1
        }

        .kpi-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 100px;
            width: fit-content
        }

        .kpi-badge--up {
            background: #f0fdf4;
            color: #15803d
        }

        .kpi-badge--down {
            background: #fef2f2;
            color: #b91c1c
        }

        /* ══ Body Grid ══ */
        .body-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 16px;
            align-items: start
        }

        .body-left {
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-width: 0
        }

        .body-right {
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-width: 0
        }

        /* ══ Cards ══ */
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden
        }

        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            flex-wrap: wrap;
            gap: 8px
        }

        .card-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827
        }

        .card-sub {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px
        }

        .card-badge {
            background: #f3f4f6;
            color: #6b7280;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 100px;
            margin-left: 6px
        }

        .card-link {
            font-size: 12px;
            font-weight: 700;
            color: #3b82f6
        }

        .card-footer-link {
            display: block;
            padding: 12px 20px;
            border-top: 1px solid #f3f4f6;
            font-size: 12px;
            font-weight: 700;
            color: #3b82f6
        }

        /* ══ Legend --*/
        .chart-legend-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap
        }

        .cl-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            color: #6b7280;
            font-weight: 600
        }

        .cl-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%
        }

        /* ══ Two-col ══ */
        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px
        }

        /* ══ Donut ══ */
        .donut-wrap {
            position: relative;
            height: 180px;
            padding: 16px
        }

        .donut-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none
        }

        .dc-val {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            line-height: 1
        }

        .dc-lbl {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: .4px
        }

        /* ══ Legend List ══ */
        .legend-list {
            padding: 0 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 8px
        }

        .ll-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #6b7280
        }

        .ll-row strong {
            margin-left: auto;
            color: #111827;
            font-weight: 700
        }

        .ll-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0
        }

        /* ══ Loan Book ══ */
        .lb-hero {
            padding: 20px 20px 8px
        }

        .lb-lbl {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: .4px
        }

        .lb-big {
            font-size: 26px;
            font-weight: 800;
            color: #111827;
            margin: 4px 0
        }

        .lb-trend {
            font-size: 12px;
            color: #16a34a;
            font-weight: 700
        }

        .lb-pair {
            display: flex;
            gap: 24px;
            padding: 12px 20px;
            border-top: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6
        }

        .lb-stat {
            flex: 1
        }

        .lbs-lbl {
            font-size: 11px;
            color: #9ca3af;
            font-weight: 600
        }

        .lbs-val {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin-top: 2px
        }

        .seg-block {
            padding: 14px 20px
        }

        .seg-title {
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: .4px
        }

        .seg-row {
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px
        }

        .seg-label {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            width: 90px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .seg-bar-wrap {
            height: 5px;
            background: #f3f4f6;
            border-radius: 100px;
            overflow: hidden
        }

        .seg-bar {
            height: 100%;
            background: #3b82f6;
            border-radius: 100px
        }

        .seg-pct {
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            width: 34px;
            text-align: right
        }

        /* ══ Table ══ */
        .tbl-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch
        }

        .dtbl {
            width: 100%;
            border-collapse: collapse;
            min-width: 540px
        }

        .dtbl th {
            padding: 10px 16px;
            font-size: 11px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: .4px;
            border-bottom: 1px solid #f3f4f6;
            white-space: nowrap;
            background: #fafafa
        }

        .dtbl td {
            padding: 12px 16px;
            font-size: 13px;
            color: #374151;
            border-bottom: 1px solid #f9fafb;
            vertical-align: middle
        }

        .dtbl tr:last-child td {
            border-bottom: none
        }

        .dtbl.dtbl-sm th,
        .dtbl.dtbl-sm td {
            padding: 10px 14px;
            font-size: 12px
        }

        .td-id {
            font-family: monospace;
            font-weight: 700;
            color: #3b82f6 !important;
            white-space: nowrap
        }

        .td-name {
            font-weight: 600;
            color: #111827 !important;
            white-space: nowrap
        }

        .td-amount {
            font-weight: 700;
            color: #111827 !important;
            white-space: nowrap
        }

        .td-date {
            white-space: nowrap;
            color: #9ca3af !important;
            font-size: 12px
        }

        .seg-pill {
            background: #f3f4f6;
            color: #6b7280;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 100px
        }

        .status-pill {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 100px;
            white-space: nowrap
        }

        .sp-submitted {
            background: #eff6ff;
            color: #1d4ed8
        }

        .sp-approved {
            background: #f0fdf4;
            color: #15803d
        }

        .sp-under_review {
            background: #fefce8;
            color: #92400e
        }

        .sp-declined {
            background: #fef2f2;
            color: #b91c1c
        }

        .ico-btn {
            width: 28px;
            height: 28px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            transition: background .15s
        }

        .ico-btn:hover {
            background: #f3f4f6
        }

        /* ══ P&L ══ */
        .pl-grid {
            padding: 16px 20px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px
        }

        .pl-item .pl-lbl {
            font-size: 10px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 3px
        }

        .pl-item .pl-val {
            font-size: 14px;
            font-weight: 700;
            color: #111827
        }

        .pl-total .pl-val {
            color: #4f46e5;
            font-size: 15px
        }

        .pl-profit .pl-val {
            color: #15803d;
            font-size: 15px
        }

        .pl-margin {
            font-size: 11px;
            color: #16a34a;
            font-weight: 600;
            margin-top: 2px
        }

        .pl-divider {
            grid-column: 1/-1;
            border-top: 1px dashed #e5e7eb;
            margin: 4px 0
        }

        /* ══ Collections ══ */
        .col-trio {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6
        }

        .cs-lbl {
            font-size: 10px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 3px
        }

        .cs-val {
            font-size: 15px;
            font-weight: 800;
            color: #111827
        }

        .cs-green {
            color: #16a34a !important
        }

        .aging-block {
            padding: 14px 20px
        }

        .aging-head {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 10px
        }

        .aging-total {
            color: #dc2626
        }

        .aging-row {
            display: grid;
            grid-template-columns: 70px 1fr 70px;
            align-items: center;
            gap: 8px;
            margin-bottom: 7px
        }

        .aging-label {
            font-size: 11px;
            color: #6b7280;
            font-weight: 600
        }

        .aging-bar-wrap {
            height: 5px;
            background: #f3f4f6;
            border-radius: 100px;
            overflow: hidden
        }

        .aging-bar {
            height: 100%;
            background: #ef4444;
            border-radius: 100px;
            opacity: .7
        }

        .aging-val {
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            text-align: right
        }

        /* ══ Alerts ══ */
        .alerts-wrap {
            padding: 12px
        }

        .alert-row {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 6px
        }

        .alert-warn {
            background: #fffbeb
        }

        .alert-info {
            background: #f0f9ff
        }

        .alert-ok {
            background: #f0fdf4
        }

        .alert-ico {
            font-style: normal;
            font-size: 14px;
            margin-top: 1px;
            flex-shrink: 0;
            width: 18px;
            text-align: center
        }

        .alert-warn .alert-ico {
            color: #d97706
        }

        .alert-info .alert-ico {
            color: #0284c7
        }

        .alert-ok .alert-ico {
            color: #16a34a
        }

        .alert-msg {
            font-size: 12px;
            color: #1e293b;
            line-height: 1.4
        }

        .alert-msg strong {
            font-weight: 700
        }

        .alert-time {
            font-size: 11px;
            color: #9ca3af;
            font-weight: 600;
            margin-top: 3px
        }

        /* ══ Funnel ══ */
        .funnel-list {
            padding: 12px 16px
        }

        .fn-step {
            display: grid;
            grid-template-columns: 24px 1fr auto;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px
        }

        .fn-num {
            width: 24px;
            height: 24px;
            background: #f3f4f6;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0
        }

        .fn-label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 3px
        }

        .fn-bar-wrap {
            height: 4px;
            background: #f3f4f6;
            border-radius: 100px;
            overflow: hidden
        }

        .fn-bar {
            height: 100%;
            background: #3b82f6;
            border-radius: 100px
        }

        .fn-right {
            text-align: right
        }

        .fn-val {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            white-space: nowrap
        }

        .fn-pct {
            font-size: 11px;
            color: #6b7280;
            font-weight: 600
        }

        .fn-footer {
            border-top: 1px solid #f3f4f6;
            padding-top: 10px;
            margin-top: 4px;
            font-size: 12px;
            color: #6b7280;
            font-weight: 600
        }

        .fn-footer strong {
            color: #111827
        }

        /* ══ Payouts ══ */
        .payout-list {
            padding: 12px 20px
        }

        .po-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f9fafb;
            font-size: 13px;
            color: #6b7280
        }

        .po-row:last-child {
            border-bottom: none
        }

        .po-row strong {
            color: #111827;
            font-weight: 700
        }

        .po-paid strong {
            color: #16a34a
        }

        .po-pending {
            background: #f0f9ff;
            padding: 8px 10px;
            border-radius: 7px
        }

        .po-pending strong {
            color: #0284c7
        }

        .po-muted {
            opacity: .55
        }

        /* ══ Responsive ══ */
        @media(max-width:1280px) {
            .kpi-row-5 {
                grid-template-columns: repeat(3, 1fr)
            }
        }

        @media(max-width:1100px) {
            .body-grid {
                grid-template-columns: 1fr
            }

            .body-right {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px
            }

            .kpi-row-4 {
                grid-template-columns: repeat(2, 1fr)
            }

            .kpi-row-5 {
                grid-template-columns: repeat(3, 1fr)
            }
        }

        @media(max-width:768px) {
            .d-wrap {
                padding: 16px
            }

            .kpi-row-4,
            .kpi-row-5 {
                grid-template-columns: repeat(2, 1fr)
            }

            .body-right {
                grid-template-columns: 1fr
            }

            .two-col {
                grid-template-columns: 1fr
            }

            .pl-grid {
                grid-template-columns: 1fr 1fr
            }

            .col-trio {
                grid-template-columns: 1fr 1fr
            }
        }
    </style>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                /* ── Applications Trend ── */
                new Chart(document.getElementById('chartTrend'), {
                    type: 'line',
                    data: {
                        labels: {!! json_encode(collect($monthlyChart)->pluck('month')) !!},
                        datasets: [
                            { label: 'Submitted', data: {!! json_encode(collect($monthlyChart)->pluck('applications')) !!}, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.08)', tension: .4, fill: true, pointRadius: 3, pointBackgroundColor: '#3b82f6' },
                            { label: 'Approved', data: {!! json_encode(collect($monthlyChart)->pluck('approved')) !!}, borderColor: '#10b981', tension: .4, pointRadius: 3, pointBackgroundColor: '#10b981' },
                            { label: 'Disbursed', data: {!! json_encode(collect($monthlyChart)->map(fn($m) => $m['disbursed'] / 1000)) !!}, borderColor: '#4f46e5', tension: .4, pointRadius: 3, pointBackgroundColor: '#4f46e5' }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' }, color: '#9ca3af' } },
                            y: { grid: { color: '#f3f4f6', borderDash: [4, 4] }, ticks: { font: { size: 11, weight: '600' }, color: '#9ca3af' } }
                        }
                    }
                });

                /* ── Status Donut ── */
                new Chart(document.getElementById('chartStatus'), {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [{{ $stats['apps_submitted'] }},{{ $stats['apps_approved'] }},{{ $stats['apps_declined'] }}],
                            backgroundColor: ['#3b82f6', '#10b981', '#ef4444'],
                            borderWidth: 3, borderColor: '#ffffff', hoverOffset: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '78%' }
                });

                /* ── Segment Donut ── */
                new Chart(document.getElementById('chartSeg'), {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: {!! json_encode(collect($segmentBreakdown)->pluck('portfolio')) !!},
                            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#7c3aed', '#ec4899', '#f97316'],
                            borderWidth: 3, borderColor: '#ffffff', hoverOffset: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '78%' }
                });

                /* ── PAR Trend ── */
                new Chart(document.getElementById('chartPAR'), {
                    type: 'line',
                    data: {
                        labels: ['Apr 1', 'Apr 8', 'Apr 15', 'Apr 22', 'Apr 30'],
                        datasets: [
                            { label: 'PAR 1', data: [2.0, 2.5, 2.2, 2.8, 2.5], borderColor: '#10b981', tension: .4, borderWidth: 2, pointRadius: 2 },
                            { label: 'PAR 7', data: [1.5, 1.8, 1.6, 2.0, 1.8], borderColor: '#f59e0b', tension: .4, borderWidth: 2, pointRadius: 2 },
                            { label: 'PAR 30', data: [4.0, 4.2, 4.1, 4.5, 4.3], borderColor: '#ef4444', tension: .4, borderWidth: 2, pointRadius: 2 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' }, color: '#9ca3af' } },
                            y: { min: 0, max: 5, grid: { display: false }, ticks: { display: false } }
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection