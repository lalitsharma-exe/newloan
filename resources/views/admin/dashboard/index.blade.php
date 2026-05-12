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
        <div style="margin-top:32px"></div>

        {{-- ══════════════ EXECUTIVE SUMMARY ══════════════ --}}
        <div class="section-title">Executive Summary</div>
        <div class="kpi-grid kpi-row-5">
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Total Revenue</span>
                    @if(isset($chg['revenue']))
                        <div class="kpi-badge kpi-badge--{{ $chg['revenue'] >= 0 ? 'up' : 'down' }}">
                            {{ $chg['revenue'] >= 0 ? '↑' : '↓' }} {{ abs($chg['revenue']) }}%
                        </div>
                    @endif
                </div>
                <div class="kpi-val">M{{ number_format($stats['exec_revenue'], 2) }}</div>
                <div class="kpi-sub-val">All-time earnings</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Net Profit</span>
                    @if(isset($chg['profit']))
                        <div class="kpi-badge kpi-badge--{{ $chg['profit'] >= 0 ? 'up' : 'down' }}">
                            {{ $chg['profit'] >= 0 ? '↑' : '↓' }} {{ abs($chg['profit']) }}%
                        </div>
                    @endif
                </div>
                <div class="kpi-val">M{{ number_format($stats['exec_net_profit'], 2) }}</div>
                <div class="kpi-sub-val">Margin: <strong>{{ $stats['exec_profit_margin'] }}%</strong></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">Portfolio</span></div>
                <div class="kpi-val">M{{ number_format($stats['exec_portfolio'], 2) }}</div>
                <div class="kpi-sub-val">Outstanding balance</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">PAR 30</span></div>
                <div class="kpi-val" style="color:{{ $stats['exec_par30'] > 5 ? '#dc2626' : '#111827' }}">{{ $stats['exec_par30'] }}%</div>
                <div class="kpi-sub-val">Portfolio at risk</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">Collection Rate</span></div>
                <div class="kpi-val">{{ $stats['exec_collection_rate'] }}%</div>
                <div class="kpi-sub-val">MTD efficiency</div>
            </div>
        </div>

        {{-- ══════════════ LIQUIDITY & CASHFLOW ══════════════ --}}
        <div class="section-title" style="margin-top:24px">Liquidity & Cashflow</div>
        <div class="kpi-grid kpi-row-6">
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">Cash Available</span></div>
                <div class="kpi-val" style="color:#16a34a">M{{ number_format($stats['liq_cash_available'], 2) }}</div>
                <div class="kpi-sub-val">Bank balance (est)</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">Undisbursed Funds</span></div>
                <div class="kpi-val">M{{ number_format($stats['liq_undisbursed'], 2) }}</div>
                <div class="kpi-sub-val">Approved & Pending</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">Expected Inflows</span></div>
                <div class="kpi-val">M{{ number_format($stats['liq_expected_inflows'], 2) }}</div>
                <div class="kpi-sub-val">Next 30 days</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">Expected Outflows</span></div>
                <div class="kpi-val">M{{ number_format($stats['liq_expected_outflows'], 2) }}</div>
                <div class="kpi-sub-val">Projections + Approved</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">Net Liquidity</span></div>
                <div class="kpi-val" style="color:{{ $stats['liq_net_liquidity'] < 0 ? '#dc2626' : '#111827' }}">M{{ number_format($stats['liq_net_liquidity'], 2) }}</div>
                <div class="kpi-sub-val">30d Forecast</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">Runway</span></div>
                <div class="kpi-val">{{ $stats['liq_runaway'] }} {{ is_numeric($stats['liq_runaway']) ? 'Months' : '' }}</div>
                <div class="kpi-sub-val">Liquidity duration</div>
            </div>
        </div>

        <div style="margin-top:24px"></div>

        {{-- ══════════════ 1. BORROWER METRICS ══════════════ --}}
        <div class="section-title">Borrower Metrics</div>
        <div class="kpi-grid kpi-row-6">
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Total Borrowers</span>
                    @if(isset($chg['total_borrowers']))
                        <div class="kpi-badge kpi-badge--{{ $chg['total_borrowers'] >= 0 ? 'up' : 'down' }}">
                            {{ $chg['total_borrowers'] >= 0 ? '↑' : '↓' }} {{ abs($chg['total_borrowers']) }}%
                        </div>
                    @endif
                </div>
                <div class="kpi-val">{{ number_format($stats['total_borrowers']) }}</div>
                <div class="kpi-sub-val">Registered users</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Active Borrowers</span>
                    @if(isset($chg['active_borrowers']))
                        <div class="kpi-badge kpi-badge--{{ $chg['active_borrowers'] >= 0 ? 'up' : 'down' }}">
                            {{ $chg['active_borrowers'] >= 0 ? '↑' : '↓' }} {{ abs($chg['active_borrowers']) }}%
                        </div>
                    @endif
                </div>
                <div class="kpi-val">{{ number_format($stats['active_borrowers']) }}</div>
                <div class="kpi-sub-val">With live loans</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">New Borrowers</span>
                    @if(isset($chg['new_borrowers']))
                        <div class="kpi-badge kpi-badge--{{ $chg['new_borrowers'] >= 0 ? 'up' : 'down' }}">
                            {{ $chg['new_borrowers'] >= 0 ? '↑' : '↓' }} {{ abs($chg['new_borrowers']) }}%
                        </div>
                    @endif
                </div>
                <div class="kpi-val">{{ number_format($stats['new_borrowers']) }}</div>
                <div class="kpi-sub-val">Joined this period</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">Repeat Borrowers</span></div>
                <div class="kpi-val">{{ number_format($stats['repeat_borrowers']) }}</div>
                <div class="kpi-sub-val">> 1 loan history</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">Repeat Rate</span></div>
                <div class="kpi-val">{{ $stats['repeat_rate'] }}%</div>
                <div class="kpi-sub-val">Customer loyalty</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">Avg Loans / Borrower</span></div>
                <div class="kpi-val">{{ $stats['avg_loans_per_borrower'] }}</div>
                <div class="kpi-sub-val">Portfolio density</div>
            </div>
        </div>

        {{-- ══════════════ 2. LOAN STATUS ══════════════ --}}
        <div class="section-title" style="margin-top:24px">Loan Status</div>
        <div class="kpi-grid kpi-row-4">
            <a href="{{ route('admin.applications.index') }}" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Application Submitted</span>
                    @if(isset($chg['applications']))
                        <div class="kpi-badge kpi-badge--{{ $chg['applications'] >= 0 ? 'up' : 'down' }}">
                            {{ $chg['applications'] >= 0 ? '↑' : '↓' }} {{ abs($chg['applications']) }}%
                        </div>
                    @endif
                </div>
                <div class="kpi-val">{{ number_format($stats['apps_submitted']) }}</div>
                <div class="kpi-sub-val">Total inflow</div>
            </a>
            <a href="{{ route('admin.applications.index', ['status'=>'approved']) }}" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Approved</span>
                    @if(isset($chg['approved']))
                        <div class="kpi-badge kpi-badge--{{ $chg['approved'] >= 0 ? 'up' : 'down' }}">
                            {{ $chg['approved'] >= 0 ? '↑' : '↓' }} {{ abs($chg['approved']) }}%
                        </div>
                    @endif
                </div>
                <div class="kpi-val">{{ number_format($stats['apps_approved']) }}</div>
                <div class="kpi-sub-val">Ready for disbursement</div>
            </a>
            <a href="{{ route('admin.loans.index') }}" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Disbursed</span>
                    @if(isset($chg['disbursed']))
                        <div class="kpi-badge kpi-badge--{{ $chg['disbursed'] >= 0 ? 'up' : 'down' }}">
                            {{ $chg['disbursed'] >= 0 ? '↑' : '↓' }} {{ abs($chg['disbursed']) }}%
                        </div>
                    @endif
                </div>
                <div class="kpi-val">M{{ number_format($stats['total_disbursed'], 2) }}</div>
                <div class="kpi-sub-val">Total capital out</div>
            </a>
            <a href="{{ route('admin.loans.index', ['status'=>'active']) }}" class="kpi-card">
                <div class="kpi-top">
                    <span class="kpi-label">Active Loans</span>
                    @if(isset($chg['active_loans']))
                        <div class="kpi-badge kpi-badge--{{ $chg['active_loans'] >= 0 ? 'up' : 'down' }}">
                            {{ $chg['active_loans'] >= 0 ? '↑' : '↓' }} {{ abs($chg['active_loans']) }}%
                        </div>
                    @endif
                </div>
                <div class="kpi-val">{{ number_format($stats['active_loans']) }}</div>
                <div class="kpi-sub-val">Live contracts</div>
            </a>
        </div>

        <div class="two-col" style="margin-top: 12px; margin-bottom: 24px;">
            <div class="card">
                <div class="card-head"><div class="card-title">Portfolio Summary</div></div>
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
            </div>
            <div class="card">
                <div class="card-head"><div class="card-title">Status Distribution</div></div>
                <div class="donut-wrap" style="height: 140px;">
                    <canvas id="chartStatus"></canvas>
                    <div class="donut-center">
                        <div class="dc-val">{{ number_format($stats['apps_submitted']) }}</div>
                        <div class="dc-lbl">Total</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════ 3. ACTIONABLE ITEMS ══════════════ --}}
        <div class="section-title" style="margin-top:24px">Actionable Items</div>
        <div class="kpi-grid kpi-row-3">
            <a href="{{ route('admin.applications.index', ['status'=>'submitted']) }}" class="kpi-card kpi-card--urgent">
                <div class="kpi-top">
                    <span class="kpi-label">⚡ Pending Review</span>
                </div>
                <div class="kpi-val" style="color:#d97706">{{ number_format($stats['apps_pending_review']) }}</div>
                <div class="kpi-sub-val">Needs verification</div>
            </a>
            <a href="#" class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">📅 Loans Due Today</span></div>
                <div class="kpi-val">{{ number_format($stats['loans_due_today']) }}</div>
                <div class="kpi-sub-val">Collection target</div>
            </a>
            <div class="kpi-card">
                <div class="kpi-top"><span class="kpi-label">🔔 Alerts Feed</span></div>
                <div class="kpi-val" style="font-size: 14px; font-weight: 500; color: #6b7280; margin-top: 5px;">
                    Latest: PAR 30 rose to 3.65%
                </div>
                <div class="kpi-sub-val">Real-time signals</div>
            </div>
        </div>

        <div class="body-grid">
            {{-- LEFT column --}}
            <div class="body-left">
                {{-- ══════════════ 4. RECENT APPLICATIONS TABLE ══════════════ --}}
                <div class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Applications Trend</div>
                            <div class="card-sub">Daily inflow</div>
                        </div>
                        <div class="chart-legend-row">
                            <span class="cl-item"><span class="cl-dot" style="background:#3b82f6"></span>Submitted</span>
                            <span class="cl-item"><span class="cl-dot" style="background:#10b981"></span>Approved</span>
                        </div>
                    </div>
                    <div style="position:relative;height:200px;padding:0 20px 16px">
                        <canvas id="chartTrend"></canvas>
                    </div>
                </div>

                <div class="card" style="margin-top: 16px;">
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
                                        <td><span class="status-pill sp-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                                        <td class="td-date">{{ $app->created_at->format('d M Y') }}</td>
                                        <td><a href="{{ route('admin.applications.show', $app->id) }}" class="ico-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></svg></a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="two-col">
                    {{-- ══════════════ 6. COLLECTIONS & AGING ══════════════ --}}
                    <div class="card">
                        <div class="card-head">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div class="card-title">Collections</div>
                                <span class="card-badge">MTD</span>
                            </div>
                            <a href="#" class="card-link">Report →</a>
                        </div>
                        <div class="col-trio">
                            <div class="cs-item">
                                <div class="cs-lbl">Total Due</div>
                                <div class="cs-val">M{{ number_format($stats['month_expected'], 2) }}</div>
                            </div>
                            <div class="cs-item">
                                <div class="cs-lbl">Collected</div>
                                <div class="cs-val cs-green">M{{ number_format($stats['month_collected'], 2) }}</div>
                            </div>
                            <div class="cs-item">
                                <div class="cs-lbl">Rate</div>
                                <div class="cs-val cs-green">{{ $stats['collection_pct'] }}%</div>
                            </div>
                        </div>
                        <div class="aging-block">
                            <div class="aging-head">
                                <span>Overdue Aging</span>
                                <span style="color: #dc2626; font-weight: 800;">M{{ number_format($stats['overdue_total'], 2) }}</span>
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
                                        <div class="aging-bar" style="width:{{ $a['pct'] }}%; background: #ef4444;"></div>
                                    </div>
                                    <span class="aging-val">M{{ number_format($a['val'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ══════════════ 7. SEGMENT RISK BREAKDOWN (PAR 30) ══════════════ --}}
                    <div class="card">
                        <div class="card-head">
                            <div class="card-title">Segment Risk (PAR 30)</div>
                        </div>
                        <div class="donut-wrap" style="height: 140px; margin-top: 10px;">
                            <canvas id="chartSeg"></canvas>
                        </div>
                        <div class="seg-block" style="padding: 0 20px 20px;">
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
                                        <div class="seg-bar" style="width:{{ $s['pct'] }}%; background: #ef4444;"></div>
                                    </div>
                                    <div class="seg-pct">{{ $s['pct'] }}%</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ══════════════ 9. FINANCIAL PERFORMANCE (P&L) ══════════════ --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">Financial Performance (P&L)</div>
                    </div>
                    <div class="pl-grid" style="grid-template-columns: repeat(3, 1fr); padding: 20px; gap: 20px;">
                        <div class="pl-item">
                            <div class="pl-lbl">Interest Income</div>
                            <div class="pl-val">M{{ number_format($stats['total_interest_revenue'], 2) }}</div>
                        </div>
                        <div class="pl-item">
                            <div class="pl-lbl">Fee Income</div>
                            <div class="pl-val">M{{ number_format($stats['total_fee_revenue'], 2) }}</div>
                        </div>
                        <div class="pl-item pl-total">
                            <div class="pl-lbl">Total Revenue</div>
                            <div class="pl-val">M{{ number_format($stats['total_revenue'], 2) }}</div>
                        </div>
                        <div class="pl-item">
                            <div class="pl-lbl">Operating Expenses</div>
                            <div class="pl-val">M0.00</div>
                        </div>
                        <div class="pl-item">
                            <div class="pl-lbl">Cost of Funds</div>
                            <div class="pl-val">M0.00</div>
                        </div>
                        @php $netProfit = $stats['total_revenue'] - $stats['written_off_amount']; @endphp
                        <div class="pl-item pl-profit">
                            <div class="pl-lbl">Net Profit</div>
                            <div class="pl-val">M{{ number_format($netProfit, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT column --}}
            <div class="body-right">
                {{-- ══════════════ 5. PAR METRICS ══════════════ --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">PAR Metrics</div>
                    </div>
                    <div class="par-metrics-list" style="padding: 10px 20px;">
                        <div class="ll-row"><span>PAR 1</span><strong style="color:#10b981">{{ $stats['par1_pct'] }}%</strong></div>
                        <div class="ll-row"><span>PAR 7</span><strong style="color:#f59e0b">{{ $stats['par7_pct'] }}%</strong></div>
                        <div class="ll-row"><span>PAR 30</span><strong style="color:#ef4444">{{ $stats['par30_pct'] }}%</strong></div>
                    </div>
                    <div style="height: 120px; padding: 0 20px 10px;">
                        <canvas id="chartPAR"></canvas>
                    </div>
                </div>

                {{-- ══════════════ 8. VINTAGE ANALYSIS ══════════════ --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">Vintage Analysis</div>
                    </div>
                    <div style="padding: 20px; text-align: center; color: #6b7280;">
                        <div style="font-size: 12px; margin-bottom: 10px;">Cohort performance (simplified)</div>
                        <div style="display: flex; align-items: flex-end; gap: 4px; height: 60px; justify-content: center;">
                            <div style="width:20px; height: 40%; background: #3b82f6; border-radius: 2px;"></div>
                            <div style="width:20px; height: 60%; background: #3b82f6; border-radius: 2px;"></div>
                            <div style="width:20px; height: 85%; background: #3b82f6; border-radius: 2px;"></div>
                            <div style="width:20px; height: 75%; background: #3b82f6; border-radius: 2px;"></div>
                            <div style="width:20px; height: 95%; background: #3b82f6; border-radius: 2px;"></div>
                        </div>
                        <div style="font-size: 10px; margin-top: 8px;">Latest 5 Cohorts</div>
                    </div>
                </div>

                {{-- ══════════════ 10. REFERRAL AND MARKETING ══════════════ --}}
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">Referral & Marketing</div>
                    </div>
                    <div class="funnel-list">
                        @php
                            $funnel = [
                                ['label' => 'Link Clicks', 'val' => 12842, 'pct' => null],
                                ['label' => 'Apps Started', 'val' => 3421, 'pct' => 26.6],
                                ['label' => 'Submitted', 'val' => $stats['apps_submitted'], 'pct' => 36.5],
                                ['label' => 'Disbursed', 'val' => $stats['apps_approved'], 'pct' => 67.5],
                                ['label' => '1st Payment', 'val' => 632, 'pct' => 75.1],
                            ];
                        @endphp
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
                                    @if($f['pct'])<div class="fn-pct">{{ $f['pct'] }}%</div>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>{{-- /body-right --}}
        </div>{{-- /body-grid --}}
    </div>{{-- /d-wrap --}}

    <style>
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            padding-left: 4px;
            border-left: 4px solid #3b82f6;
        }

        .kpi-row-6 {
            grid-template-columns: repeat(6, 1fr);
        }

        .kpi-row-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        @media (max-width: 1200px) {
            .kpi-row-6 { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 768px) {
            .kpi-row-6 { grid-template-columns: repeat(2, 1fr); }
            .kpi-row-4 { grid-template-columns: repeat(2, 1fr); }
            .kpi-row-3 { grid-template-columns: 1fr; }
        }

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