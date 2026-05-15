@extends('admin.layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Business Intelligence Overview')
@section('bc') Dashboard @endsection
@section('content')

    @php
        $cur  = $periodStats['current'];
        $prev = $periodStats['previous'];
        $chg  = $periodStats['changes'];
        $quarterNum = now()->quarter;
        $quarterNames = [1=>'1st Quarter', 2=>'2nd Quarter', 3=>'3rd Quarter', 4=>'4th Quarter'];
    @endphp

    @endphp

    <div class="d-wrap">

        {{-- ══════════════ ALERT FEED & ACTIONS ══════════════ --}}
        @if($stats['exec_par30'] > 0 || $stats['liq_runaway'] == 0 || $stats['loans_due_today'] > 0)
        <div class="alert-bar" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 12px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 15px;">
            <div style="background: #fef3c7; color: #d97706; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div style="flex: 1;">
                <div style="font-size: 13px; font-weight: 800; color: #92400e;">Operational Alerts</div>
                <div style="font-size: 12px; color: #b45309;">
                    @if($stats['exec_par30'] > 0) <span style="margin-right: 15px;">• PAR 30 is currently {{ $stats['exec_par30'] }}%</span> @endif
                    @if($stats['liq_runaway'] == 0) <span style="margin-right: 15px;">• Liquidity runway is critical (0 days)</span> @endif
                    @if($stats['loans_due_today'] > 0) <span>• {{ $stats['loans_due_today'] }} installments due today</span> @endif
                </div>
            </div>
            <a href="{{ route('admin.applications.index', ['status'=>'overdue']) }}" class="card-link" style="color: #92400e;">Take Action →</a>
        </div>
        @endif

        {{-- ══════════════ HEADER ══════════════ --}}
        <div class="d-header">
            <div>
                <h1 class="d-title" style="font-size: 24px; font-weight: 800; color: #1e293b;">Executive Overview</h1>
                <p class="d-sub">Real-time operational and risk intelligence · {{ now()->format('l, d F Y') }}</p>
            </div>
            <div class="d-header-right">
                <form action="{{ route('admin.dashboard') }}" method="GET" class="d-period">
                    <select name="period" onchange="this.form.submit()" class="d-period-sel">
                        <option value="month" {{ $activePeriod == 'month' ? 'selected' : '' }}>Month View</option>
                        <option value="quarter" {{ $activePeriod == 'quarter' ? 'selected' : '' }}>Quarterly View</option>
                        <option value="year" {{ $activePeriod == 'year' ? 'selected' : '' }}>Annual View</option>
                    </select>
                </form>
                <a href="{{ route('admin.applications.index') }}" class="d-btn-primary">+ New Loan</a>
            </div>
        </div>

        {{-- ══════════════ EXECUTIVE SUMMARY ══════════════ --}}
        <div class="sec-label"><i class="bi bi-graph-up"></i> EXECUTIVE SUMMARY</div>
        <div class="kpi-grid kpi-row-5">
            <div class="kpi-card" style="border-top: 3px solid #3b82f6;">
                <div class="kpi-head"><div class="kpi-icon" style="background: #eff6ff; color: #3b82f6;"><i class="bi bi-currency-dollar"></i></div></div>
                <div class="kpi-label">TOTAL REVENUE ({{ $periodLabel }})</div>
                @if(isset($chg['revenue'])) <div class="kpi-trend {{ $chg['revenue'] >= 0 ? 'trend-up' : 'trend-down' }}">{{ $chg['revenue'] >= 0 ? '↑' : '↓' }} {{ abs($chg['revenue']) }}%</div> @endif
                <div class="kpi-val">M{{ number_format($cur['revenue'], 2) }}</div>
                <div class="kpi-sub-val">Total: M{{ number_format($stats['exec_revenue'], 2) }}</div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #8b5cf6;">
                <div class="kpi-head"><div class="kpi-icon" style="background: #f5f3ff; color: #8b5cf6;"><i class="bi bi-wallet2"></i></div></div>
                <div class="kpi-label">NET PROFIT ({{ $periodLabel }})</div>
                @if(isset($chg['profit'])) <div class="kpi-trend {{ $chg['profit'] >= 0 ? 'trend-up' : 'trend-down' }}">{{ $chg['profit'] >= 0 ? '↑' : '↓' }} {{ abs($chg['profit']) }}%</div> @endif
                <div class="kpi-val">M{{ number_format($cur['profit'], 2) }}</div>
                <div class="kpi-sub-val">All-time: M{{ number_format($stats['exec_net_profit'], 2) }}</div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #10b981;">
                <div class="kpi-head"><div class="kpi-icon" style="background: #f0fdf4; color: #10b981;"><i class="bi bi-bank"></i></div></div>
                <div class="kpi-label">OUTSTANDING PORTFOLIO</div>
                <div class="kpi-trend trend-up">↑ Yielding</div>
                <div class="kpi-val">M{{ number_format($stats['exec_portfolio'], 2) }}</div>
                <div class="kpi-sub-val" title="Includes M{{ number_format($stats['accrued_charges'], 2) }} in accrued interest and fees">
                    Incl. M{{ number_format($stats['accrued_charges'] / 1000, 0) }}k Accrued Fees ⓘ
                </div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid {{ $stats['exec_par30'] > 0 ? '#f59e0b' : '#84cc16' }};">
                <div class="kpi-head"><div class="kpi-icon" style="background: {{ $stats['exec_par30'] > 0 ? '#fffbeb' : '#f7fee7' }}; color: {{ $stats['exec_par30'] > 0 ? '#f59e0b' : '#84cc16' }};"><i class="bi bi-arrow-repeat"></i></div></div>
                <div class="kpi-label">PAR 30</div>
                <div class="kpi-trend {{ $stats['exec_par30'] > 5 ? 'trend-down' : ($stats['exec_par30'] > 0 ? 'trend-neutral' : 'trend-up') }}">
                    {{ $stats['exec_par30'] > 5 ? '↑ Critical' : ($stats['exec_par30'] > 0 ? '↑ Warning' : '↑ Healthy') }}
                </div>
                <div class="kpi-val" style="color: {{ $stats['exec_par30'] > 0 ? '#f59e0b' : '#1e293b' }}">{{ $stats['exec_par30'] }}%</div>
                <div class="kpi-sub-val">Portfolio at risk</div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #f59e0b;">
                <div class="kpi-head"><div class="kpi-icon" style="background: #fffbeb; color: #f59e0b;"><i class="bi bi-bullseye"></i></div></div>
                <div class="kpi-label">COLLECTION RATE</div>
                <div class="kpi-trend {{ $stats['exec_collection_rate'] < 90 ? 'trend-down' : 'trend-up' }}">{{ $stats['exec_collection_rate'] < 90 ? '↓ Low' : '↑ High' }}</div>
                <div class="kpi-val">{{ $stats['exec_collection_rate'] }}%</div>
                <div class="kpi-sub-val">MTD efficiency</div>
            </div>
        </div>

        {{-- ══════════════ LIQUIDITY & CASHFLOW ══════════════ --}}
        <div class="sec-label"><i class="bi bi-water"></i> LIQUIDITY & CASHFLOW</div>
        <div class="kpi-grid kpi-row-6">
            <div class="kpi-card" style="border-top: 3px solid {{ $stats['liq_cash_available'] <= $stats['liq_undisbursed'] ? '#dc2626' : '#16a34a' }};">
                <div class="kpi-label">CASH AVAILABLE</div>
                <div class="kpi-val" style="color: {{ $stats['liq_cash_available'] <= $stats['liq_undisbursed'] ? '#dc2626' : '#16a34a' }};">
                    M{{ number_format($stats['liq_cash_available'], 2) }}
                </div>
                <div class="kpi-sub-val">{{ $stats['liq_cash_available'] <= 0 ? 'No funds available' : 'Bank balance (est)' }}</div>
                <div class="kpi-footer">
                    <span>Utilization</span>
                    <div class="kpi-progress"><div class="progress-bar" style="width: 25%; background: {{ $stats['liq_cash_available'] <= $stats['liq_undisbursed'] ? '#dc2626' : '#16a34a' }};"></div></div>
                </div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #ea580c;">
                <div class="kpi-label">UNDISBURSED FUNDS</div>
                <div class="kpi-val">M{{ number_format($stats['liq_undisbursed'], 2) }}</div>
                <div class="kpi-sub-val">Approved & Pending</div>
                <div class="kpi-tag">PENDING ACTION</div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #0284c7;">
                <div class="kpi-label">EXPECTED INFLOWS</div>
                <div class="kpi-val" style="color: #0284c7;">M{{ number_format($stats['liq_expected_inflows'], 2) }}</div>
                <div class="kpi-sub-val">Next 30 days</div>
                <div class="kpi-footer">
                    <span>vs Outflow</span>
                    <div class="kpi-progress"><div class="progress-bar" style="width: 73%; background: #0284c7;"></div></div>
                    <span class="pct">73%</span>
                </div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #92400e;">
                <div class="kpi-label">EXPECTED OUTFLOWS</div>
                <div class="kpi-val">M{{ number_format($stats['liq_expected_outflows'], 2) }}</div>
                <div class="kpi-sub-val">Projections + Approved</div>
                <div class="kpi-footer">
                    <span>vs Inflow</span>
                    <div class="kpi-progress"><div class="progress-bar" style="width: 37%; background: #ea580c;"></div></div>
                    <span class="pct">37%</span>
                </div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #dc2626;">
                <div class="kpi-label">NET LIQUIDITY</div>
                @if($stats['liq_net_liquidity'] < 0) <div class="kpi-trend trend-down" style="background:#fef2f2; color:#dc2626">⚠ Alert</div> @else <div class="kpi-trend trend-up">Stable</div> @endif
                <div class="kpi-val" style="color: {{ $stats['liq_net_liquidity'] < 0 ? '#dc2626' : '#1e293b' }}">M{{ number_format($stats['liq_net_liquidity'], 2) }}</div>
                <div class="kpi-sub-val">30d Forecast</div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid {{ $stats['liq_runaway'] == 0 ? '#dc2626' : '#64748b' }};">
                <div class="kpi-label">RUNWAY</div>
                <div class="kpi-tag" style="background: {{ $stats['liq_runaway'] == 0 ? '#fef2f2' : '#f1f5f9' }}; color: {{ $stats['liq_runaway'] == 0 ? '#dc2626' : '#64748b' }};">
                    {{ $stats['liq_runaway'] == 0 ? 'CRITICAL' : 'Sustained' }}
                </div>
                <div class="kpi-val" style="color: {{ $stats['liq_runaway'] == 0 ? '#dc2626' : '#1e293b' }}">
                    {{ $stats['liq_runaway'] == 0 ? '0 days' : ($stats['liq_runaway'] === '∞' ? '∞' : $stats['liq_runaway'] . ' mo') }}
                </div>
                <div class="kpi-sub-val">{{ $stats['liq_runaway'] == 0 ? 'Insufficient cash' : 'Liquidity duration' }}</div>
            </div>
        </div>

        {{-- ══════════════ MIDDLE SECTION: TRENDS & DISTRIBUTIONS ══════════════ --}}
        <div class="body-grid" style="margin-top: 24px;">
            <div class="body-left">
                <div class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Applications & Approval Trend</div>
                            <div class="card-sub">Periodic volume monitoring</div>
                        </div>
                        <div class="chart-legend">
                            <span class="cl-item"><span class="cl-dot" style="background:#3b82f6"></span>Submitted</span>
                            <span class="cl-item"><span class="cl-dot" style="background:#10b981"></span>Approved</span>
                            <span class="cl-item"><span class="cl-dot" style="background:#8b5cf6"></span>Disbursed</span>
                        </div>
                    </div>
                    <div style="height:280px; padding:20px"><canvas id="chartTrend"></canvas></div>
                </div>

                <div class="two-col" style="margin-top: 20px;">
                    <div class="card">
                        <div class="card-head"><div class="card-title">Status Distribution</div></div>
                        <div class="donut-wrap" style="height: 180px;">
                            <canvas id="chartStatus"></canvas>
                            <div class="donut-center">
                                <div class="dc-val">{{ number_format($stats['apps_submitted']) }}</div>
                                <div class="dc-lbl">Total</div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-head"><div class="card-title">Segment Risk (PAR 30)</div></div>
                        <div class="donut-wrap" style="height: 180px;">
                            <canvas id="chartSeg"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="body-right">
                <div class="card" style="height: 100%;">
                    <div class="card-head"><div class="card-title">PAR & Arrears Profile</div></div>
                    <div style="padding: 20px;">
                        <div class="par-row"><span>PAR 1 (Early)</span><strong style="color:#10b981">{{ $stats['par1_pct'] }}%</strong></div>
                        <div class="par-row"><span>PAR 7 (Critical)</span><strong style="color:#f59e0b">{{ $stats['par7_pct'] }}%</strong></div>
                        <div class="par-row"><span>PAR 30 (Default)</span><strong style="color:#ef4444">{{ $stats['par30_pct'] }}%</strong></div>
                        <div style="height:150px; margin-top:20px"><canvas id="chartPAR"></canvas></div>
                    </div>
                    <div class="aging-block" style="padding: 20px; border-top: 1px solid #f1f5f9;">
                        <div style="font-size: 11px; font-weight: 800; color: #94a3b8; margin-bottom: 15px;">OVERDUE AGING (M{{ number_format($stats['overdue_total'], 2) }})</div>
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
                        <div class="aging-row" style="margin-bottom:10px">
                            <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:4px">
                                <span>{{ $a['label'] }}</span>
                                <span style="font-weight:700">M{{ number_format($a['val'], 2) }}</span>
                            </div>
                            <div class="kpi-progress"><div class="progress-bar" style="width:{{ $a['pct'] }}%; background: #ef4444;"></div></div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════ BORROWER & LOAN METRICS ══════════════ --}}
        <div class="sec-label"><i class="bi bi-people"></i> BORROWER & LOAN METRICS</div>
        <div class="kpi-grid kpi-row-6">
            <div class="kpi-card" style="border-top: 3px solid #0d9488;">
                <div class="kpi-head"><div class="kpi-icon" style="background:#f0fdfa; color:#0d9488"><i class="bi bi-person"></i></div></div>
                <div class="kpi-label">TOTAL BORROWERS</div>
                @if(isset($chg['total_borrowers'])) <div class="kpi-trend trend-up">↑ {{ abs($chg['total_borrowers']) }}%</div> @endif
                <div class="kpi-val">{{ number_format($stats['total_borrowers']) }}</div>
                <div class="kpi-sub-val">Registered users</div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #10b981;">
                <div class="kpi-head"><div class="kpi-icon" style="background:#f0fdf4; color:#10b981"><i class="bi bi-person-check"></i></div></div>
                <div class="kpi-label">ACTIVE BORROWERS</div>
                @if(isset($chg['active_borrowers'])) <div class="kpi-trend trend-up">↑ {{ abs($chg['active_borrowers']) }}%</div> @endif
                <div class="kpi-val">{{ number_format($stats['active_borrowers']) }}</div>
                <div class="kpi-sub-val">With live loans</div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #ef4444;">
                <div class="kpi-head"><div class="kpi-icon" style="background:#fef2f2; color:#ef4444"><i class="bi bi-person-plus"></i></div></div>
                <div class="kpi-label">NEW BORROWERS</div>
                <div class="kpi-trend trend-down">↓ 99.5%</div>
                <div class="kpi-val">{{ number_format($stats['new_borrowers']) }}</div>
                <div class="kpi-sub-val">Joined this period</div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #6366f1;">
                <div class="kpi-head"><div class="kpi-icon" style="background:#eef2ff; color:#6366f1"><i class="bi bi-arrow-repeat"></i></div></div>
                <div class="kpi-label">REPEAT BORROWERS</div>
                <div class="kpi-trend trend-neutral">—</div>
                <div class="kpi-val">{{ number_format($stats['repeat_borrowers']) }}</div>
                <div class="kpi-sub-val">> 1 loan history</div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #db2777;">
                <div class="kpi-head"><div class="kpi-icon" style="background:#fdf2f8; color:#db2777"><i class="bi bi-heart"></i></div></div>
                <div class="kpi-label">REPEAT RATE</div>
                <div class="kpi-trend trend-neutral">—</div>
                <div class="kpi-val">{{ $stats['repeat_rate'] }}%</div>
                <div class="kpi-sub-val">Customer loyalty</div>
            </div>
            <div class="kpi-card" style="border-top: 3px solid #475569;">
                <div class="kpi-head"><div class="kpi-icon" style="background:#f8fafc; color:#475569"><i class="bi bi-stack"></i></div></div>
                <div class="kpi-label">AVG LOANS / BORROWER</div>
                <div class="kpi-trend trend-neutral">—</div>
                <div class="kpi-val">{{ $stats['avg_loans_per_borrower'] }}</div>
                <div class="kpi-sub-val">Portfolio density</div>
            </div>
        </div>

        {{-- ══════════════ FINANCIAL PERFORMANCE & RECENT LOGS ══════════════ --}}
        <div class="body-grid" style="margin-top: 24px;">
            <div class="body-left">
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">Recent Loan Applications</div>
                        <a href="{{ route('admin.applications.index') }}" class="card-link">View Register →</a>
                    </div>
                    <div style="overflow-x:auto">
                        <table class="dtbl">
                            <thead>
                                <tr>
                                    <th>App ID</th>
                                    <th>Borrower</th>
                                    <th>Product</th>
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
                                    <td><span class="seg-pill">Unsecured</span></td>
                                    <td class="td-amount">M{{ number_format($app->requested_amount, 2) }}</td>
                                    <td><span class="status-pill sp-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                                    <td style="color:#94a3b8; font-size:12px">{{ $app->created_at->format('d M Y') }}</td>
                                    <td><a href="{{ route('admin.applications.show', $app->id) }}" class="ico-btn"><i class="bi bi-eye"></i></a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card" style="margin-top:20px">
                    <div class="card-head"><div class="card-title">Financial Performance (P&L Summary)</div></div>
                    <div class="pl-grid">
                        <div class="pl-item"><div class="pl-lbl">Interest Income</div><div class="pl-val">M{{ number_format($stats['total_interest_revenue'], 2) }}</div></div>
                        <div class="pl-item"><div class="pl-lbl">Fee Income</div><div class="pl-val">M{{ number_format($stats['total_fee_revenue'], 2) }}</div></div>
                        <div class="pl-item pl-total"><div class="pl-lbl">Total Revenue</div><div class="pl-val">M{{ number_format($stats['total_revenue'], 2) }}</div></div>
                        <div class="pl-item">
                            <div class="pl-lbl">Operating Exp</div>
                            <div class="pl-val" style="font-size: {{ $stats['operating_expenses'] == 0 ? '10px' : '15px' }}; color: {{ $stats['operating_expenses'] == 0 ? '#94a3b8' : '#1e293b' }}">
                                {{ $stats['operating_expenses'] == 0 ? 'Not yet recorded' : 'M'.number_format($stats['operating_expenses'], 2) }}
                            </div>
                        </div>
                        <div class="pl-item">
                            <div class="pl-lbl">Cost of Funds</div>
                            <div class="pl-val" style="font-size: {{ $stats['cost_of_funds'] == 0 ? '10px' : '15px' }}; color: {{ $stats['cost_of_funds'] == 0 ? '#94a3b8' : '#1e293b' }}">
                                {{ $stats['cost_of_funds'] == 0 ? 'Not yet recorded' : 'M'.number_format($stats['cost_of_funds'], 2) }}
                            </div>
                        </div>
                        <div class="pl-item pl-profit">
                            <div class="pl-lbl">Net Profit</div>
                            <div class="pl-val">M{{ number_format($stats['exec_net_profit'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="body-right">
                <div class="card">
                    <div class="card-head"><div class="card-title">Marketing & Referral Funnel</div></div>
                    <div class="funnel-list" style="padding:20px">
                        @php
                            $funnel = [
                                ['label' => 'Link Clicks', 'val' => 12842, 'pct' => 100],
                                ['label' => 'Apps Started', 'val' => 3421, 'pct' => 26.6],
                                ['label' => 'Submitted', 'val' => $stats['apps_submitted'], 'pct' => 12.5],
                                ['label' => 'Disbursed', 'val' => $stats['apps_approved'], 'pct' => 8.2],
                            ];
                        @endphp
                        @foreach($funnel as $f)
                        <div class="fn-step" style="margin-bottom:12px">
                            <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:4px">
                                <span>{{ $f['label'] }}</span>
                                <span style="font-weight:700">{{ number_format($f['val']) }} ({{ $f['pct'] }}%)</span>
                            </div>
                            <div class="kpi-progress"><div class="progress-bar" style="width:{{ $f['pct'] }}%; background: #3b82f6;"></div></div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="card" style="margin-top:20px">
                    <div class="card-head"><div class="card-title">Vintage Analysis</div></div>
                    <div style="padding:20px; text-align:center">
                        <div style="display: flex; align-items: flex-end; gap: 4px; height: 80px; justify-content: center;">
                            <div style="width:15px; height: 40%; background: #eff6ff; border: 1px solid #3b82f6; border-radius: 2px;"></div>
                            <div style="width:15px; height: 60%; background: #eff6ff; border: 1px solid #3b82f6; border-radius: 2px;"></div>
                            <div style="width:15px; height: 85%; background: #3b82f6; border-radius: 2px;"></div>
                            <div style="width:15px; height: 75%; background: #3b82f6; border-radius: 2px;"></div>
                            <div style="width:15px; height: 95%; background: #3b82f6; border-radius: 2px;"></div>
                        </div>
                        <div style="font-size: 10px; margin-top: 10px; color:#94a3b8">Latest 5 Cohorts Performance</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        /* ══ Global Design ══ */
        .d-wrap { font-family: 'Inter', sans-serif; background: #f8fafc; padding: 24px; color: #334155; }
        .d-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .d-title { font-size: 24px; font-weight: 800; color: #1e293b; letter-spacing: -0.02em; }
        .d-sub { font-size: 13px; color: #64748b; margin-top: 4px; }
        .d-header-right { display: flex; align-items: center; gap: 12px; }
        .d-period { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0 12px; height: 42px; display: flex; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .d-period-sel { border: none; outline: none; font-size: 13px; font-weight: 700; color: #1e293b; cursor: pointer; background: transparent; }
        .d-btn-primary { background: #1e293b; color: #fff; border-radius: 10px; padding: 0 20px; height: 42px; display: flex; align-items: center; font-size: 13px; font-weight: 700; text-decoration: none; transition: all 0.2s; }
        .d-btn-primary:hover { background: #000; transform: translateY(-1px); }
        
        .sec-label { font-size: 11px; font-weight: 800; color: #94a3b8; letter-spacing: 0.12em; margin: 36px 0 18px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; }
        
        /* ══ KPI Grid ══ */
        .kpi-grid { display: grid; gap: 16px; }
        .kpi-row-6 { grid-template-columns: repeat(6, 1fr); }
        .kpi-row-5 { grid-template-columns: repeat(5, 1fr); }
        .kpi-row-4 { grid-template-columns: repeat(4, 1fr); }

        .kpi-card { background: #fff; border-radius: 14px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; flex-direction: column; position: relative; text-decoration: none; transition: all 0.2s; border: 1px solid transparent; }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08); border-color: #e2e8f0; }
        .kpi-head { display: flex; justify-content: flex-start; margin-bottom: 14px; }
        .kpi-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .kpi-label { font-size: 10px; font-weight: 800; color: #94a3b8; margin-bottom: 5px; }
        .kpi-trend { font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 6px; width: fit-content; margin-bottom: 10px; }
        .trend-up { background: #f0fdf4; color: #16a34a; }
        .trend-down { background: #fef2f2; color: #ef4444; }
        .trend-neutral { background: #f8fafc; color: #94a3b8; }
        .kpi-val { font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 4px; letter-spacing: -0.02em; }
        .kpi-sub-val { font-size: 11px; color: #94a3b8; font-weight: 500; }
        .kpi-tag { font-size: 9px; font-weight: 800; padding: 3px 8px; background: #f1f5f9; color: #64748b; border-radius: 5px; width: fit-content; margin-top: 10px; }
        .kpi-footer { margin-top: auto; padding-top: 14px; }
        .kpi-footer span { font-size: 10px; color: #94a3b8; font-weight: 700; display: block; margin-bottom: 5px; }
        .kpi-footer .pct { display: inline; float: right; margin-top: -16px; font-size: 10px; font-weight: 800; color: #475569; }
        .kpi-progress { height: 5px; background: #f1f5f9; border-radius: 3px; overflow: hidden; }
        .progress-bar { height: 100%; transition: width 0.6s ease; }

        /* ══ Body Grids ══ */
        .body-grid { display: grid; grid-template-columns: 1fr 340px; gap: 20px; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; border: 1px solid #f1f5f9; }
        .card-head { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .card-title { font-size: 15px; font-weight: 800; color: #1e293b; letter-spacing: -0.01em; }
        .card-sub { font-size: 12px; color: #94a3b8; margin-top: 2px; }
        .card-link { font-size: 12px; font-weight: 700; color: #3b82f6; text-decoration: none; }
        .chart-legend { display: flex; gap: 12px; }
        .cl-item { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #64748b; }
        .cl-dot { width: 8px; height: 8px; border-radius: 50%; }

        .dtbl { width: 100%; border-collapse: collapse; }
        .dtbl th { background: #fcfdfe; padding: 12px 20px; font-size: 11px; text-align: left; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #f1f5f9; }
        .dtbl td { padding: 16px 20px; border-bottom: 1px solid #f8fafc; font-size: 13px; color: #475569; }
        .td-id { font-family: monospace; font-weight: 800; color: #3b82f6; }
        .td-name { font-weight: 700; color: #1e293b; }
        .td-amount { font-weight: 800; color: #1e293b; }
        .ico-btn { width: 32px; height: 32px; border: 1px solid #e2e8f0; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #64748b; text-decoration: none; transition: all 0.2s; }
        .ico-btn:hover { background: #f8fafc; color: #3b82f6; border-color: #3b82f6; }
        
        .status-pill { font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 100px; text-transform: uppercase; }
        .sp-submitted { background: #eff6ff; color: #2563eb; }
        .sp-approved { background: #f0fdf4; color: #16a34a; }
        .sp-under_review { background: #fffbeb; color: #d97706; }
        .sp-declined { background: #fef2f2; color: #dc2626; }
        
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .donut-wrap { position: relative; display: flex; align-items: center; justify-content: center; padding: 10px; }
        .donut-center { position: absolute; text-align: center; }
        .dc-val { font-size: 24px; font-weight: 800; color: #1e293b; line-height: 1; }
        .dc-lbl { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-top: 2px; }

        .par-row { display: flex; justify-content: space-between; font-size: 12px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #64748b; }
        .par-row strong { color: #1e293b; font-weight: 800; }
        
        .pl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 20px; }
        .pl-item .pl-lbl { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; }
        .pl-item .pl-val { font-size: 15px; font-weight: 800; color: #1e293b; }
        .pl-total .pl-val { color: #3b82f6; }
        .pl-profit .pl-val { color: #16a34a; }
    </style>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#94a3b8';

            // 1. Applications Trend
            new Chart(document.getElementById('chartTrend'), {
                type: 'line',
                data: {
                    labels: {!! json_encode(collect($monthlyChart)->pluck('month')) !!},
                    datasets: [
                        { label: 'Submitted', data: {!! json_encode(collect($monthlyChart)->pluck('applications')) !!}, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.05)', fill: true, tension: 0.4, borderWidth: 3, pointRadius: 4, pointBackgroundColor: '#fff' },
                        { label: 'Approved', data: {!! json_encode(collect($monthlyChart)->pluck('approved')) !!}, borderColor: '#10b981', tension: 0.4, borderWidth: 3, pointRadius: 4, pointBackgroundColor: '#fff' },
                        { label: 'Disbursed', data: {!! json_encode(collect($monthlyChart)->map(fn($m) => round($m['disbursed'] / 1000, 1))) !!}, borderColor: '#8b5cf6', tension: 0.4, borderWidth: 3, pointRadius: 4, pointBackgroundColor: '#fff' }
                    ]
                },
                options: { 
                    responsive: true, maintainAspectRatio: false, 
                    plugins: { legend: { display: false } },
                    scales: { 
                        y: { border: { display: false }, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11, weight: '600' } } },
                        x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } }
                    }
                }
            });

            // 2. Status Distribution
            new Chart(document.getElementById('chartStatus'), {
                type: 'doughnut',
                data: {
                    labels: ['Submitted', 'Approved', 'Declined'],
                    datasets: [{
                        data: [{{ $stats['apps_submitted'] }}, {{ $stats['apps_approved'] }}, {{ $stats['apps_declined'] }}],
                        backgroundColor: ['#3b82f6', '#10b981', '#ef4444'],
                        borderWidth: 4, borderColor: '#fff', hoverOffset: 4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '75%' }
            });

            // 3. Segment Risk
            new Chart(document.getElementById('chartSeg'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(collect($segmentBreakdown)->pluck('employer_type')) !!},
                    datasets: [{
                        data: {!! json_encode(collect($segmentBreakdown)->pluck('portfolio')) !!},
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                        borderWidth: 4, borderColor: '#fff'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '75%' }
            });

            // 4. PAR Chart
            new Chart(document.getElementById('chartPAR'), {
                type: 'bar',
                data: {
                    labels: ['PAR 1', 'PAR 7', 'PAR 30'],
                    datasets: [{
                        data: [{{ $stats['par1_pct'] }}, {{ $stats['par7_pct'] }}, {{ $stats['par30_pct'] }}],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderRadius: 6, barThickness: 20
                    }]
                },
                options: { 
                    responsive: true, maintainAspectRatio: false, 
                    plugins: { legend: { display: false } },
                    scales: { 
                        y: { display: false, min: 0, max: Math.max({{ $stats['par30_pct'] }}, 10) + 5 },
                        x: { grid: { display: false }, ticks: { font: { size: 10, weight: '800' } } }
                    }
                }
            });
        });
    </script>
    @endpush
@endsection