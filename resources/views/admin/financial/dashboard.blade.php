@extends('admin.layouts.app')

@section('title', 'Financial Intelligence Dashboard')
@section('page-title', 'Treasury & Liquidity Management')

@section('content')
<div style="display:flex; flex-direction:column; gap:20px">
    <!-- Survival Metrics -->
    <div class="g4">
        <div class="sc" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: white; border: none; flex-direction: column; align-items: flex-start; justify-content: center; position: relative; overflow: hidden;">
            <div style="position: absolute; right: -10px; top: -10px; opacity: 0.1; font-size: 80px;"><i class="bi bi-bank"></i></div>
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 5px;">Cash Available</div>
            <div style="font-size: 26px; font-weight: 800;">L {{ number_format($stats['cash_available'], 2) }}</div>
            <div style="font-size: 11px; margin-top: 10px; color: rgba(255,255,255,0.5);">Across {{ $accounts->count() }} accounts</div>
        </div>

        <div class="sc" style="flex-direction: column; align-items: flex-start; justify-content: center;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--muted); margin-bottom: 5px;">Expected Inflows (30d)</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--ok);">L {{ number_format($stats['expected_inflows'], 2) }}</div>
            <div style="font-size: 11px; margin-top: 10px; color: var(--ok); font-weight: 600;"><i class="bi bi-graph-up-arrow"></i> Weighted Probability</div>
        </div>

        <div class="sc" style="flex-direction: column; align-items: flex-start; justify-content: center;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--muted); margin-bottom: 5px;">Expected Outflows (30d)</div>
            <div style="font-size: 26px; font-weight: 800; color: var(--err);">L {{ number_format($stats['expected_outflows'], 2) }}</div>
            <div style="font-size: 11px; margin-top: 10px; color: var(--muted);">Obligations & Disbursements</div>
        </div>

        @php
            $statusColor = $stats['liquidity_status'] === 'healthy' ? 'var(--ok)' : ($stats['liquidity_status'] === 'warning' ? 'var(--warn)' : 'var(--err)');
            $statusBg = $stats['liquidity_status'] === 'healthy' ? 'rgba(16,185,129,0.1)' : ($stats['liquidity_status'] === 'warning' ? 'rgba(245,158,11,0.1)' : 'rgba(239,68,68,0.1)');
        @endphp
        <div class="sc" style="background: {{ $statusBg }}; border-color: {{ $statusColor }}; flex-direction: column; align-items: flex-start; justify-content: center;">
            <div style="display:flex; width:100%; justify-content:space-between; align-items:center; margin-bottom:5px">
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: {{ $statusColor }};">Net Liquidity</div>
                <span class="badge" style="background:{{ $statusColor }}; color:#fff; font-size:9px">{{ strtoupper($stats['liquidity_status']) }}</span>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: {{ $statusColor }};">L {{ number_format($stats['net_liquidity'], 2) }}</div>
            <div style="font-size: 11px; margin-top: 10px; font-weight: 700; color: {{ $statusColor }};">{{ $stats['runway_days'] ?? '0' }} Days Runway</div>
        </div>
    </div>

    <!-- Trend Chart -->
    <div class="card">
        <div class="card-hdr"><span class="card-title">Liquidity Trend (Last 30 Snapshots)</span></div>
        <div style="padding: 20px;">
            <div style="height: 280px; width: 100%;">
                <canvas id="liquidityChart"></canvas>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 320px; gap:20px; align-items: start;">
        <!-- Left Column: Tables -->
        <div style="display:flex; flex-direction:column; gap:20px">
            <!-- Treasury Accounts -->
            <div class="card">
                <div class="card-hdr">
                    <span class="card-title">Treasury Accounts</span>
                    <a href="{{ route('admin.financial.accounts') }}" class="btn btn-sm btn-o">Manage</a>
                </div>
                <div style="overflow-x:auto">
                    <table class="dt">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Type</th>
                                <th>Currency</th>
                                <th style="text-align:right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accounts as $acc)
                            <tr>
                                <td style="display:flex; align-items:center; gap:12px">
                                    <div style="width:36px; height:36px; background:var(--bg); border-radius:8px; display:flex; align-items:center; justify-content:center; color:var(--p)">
                                        @if($acc->type === 'bank') <i class="bi bi-bank"></i>
                                        @elseif($acc->type === 'mobile_wallet') <i class="bi bi-phone"></i>
                                        @else <i class="bi bi-cash-stack"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-weight:700">{{ $acc->name }}</div>
                                        <div class="muted" style="font-size:11px">{{ $acc->institution }}</div>
                                    </div>
                                </td>
                                <td><span class="badge bs">{{ ucfirst(str_replace('_',' ',$acc->type)) }}</span></td>
                                <td>{{ $acc->currency }}</td>
                                <td style="text-align:right">
                                    <div style="font-weight:800; font-size:16px">L {{ number_format($acc->balance, 2) }}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-hdr"><span class="card-title">Recent Ledger Activity</span></div>
                <div style="overflow-x:auto">
                    <table class="dt">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th style="text-align:right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $tx)
                            <tr>
                                <td class="muted" style="font-size:11.5px">{{ $tx->created_at->format('d M, H:i') }}</td>
                                <td>
                                    <div style="font-weight:600">{{ $tx->description ?? ucfirst($tx->type) }}</div>
                                    <div class="muted" style="font-size:11px">{{ $tx->account->name }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $tx->direction === 'in' ? 'bok' : 'be' }}" style="font-size:9px">
                                        {{ strtoupper($tx->type) }}
                                    </span>
                                </td>
                                <td style="text-align:right">
                                    <div style="font-weight:800; color: {{ $tx->direction === 'in' ? 'var(--ok)' : 'var(--err)' }}">
                                        {{ $tx->direction === 'in' ? '+' : '-' }} L {{ number_format($tx->amount, 2) }}
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Sidebar -->
        <div style="display:flex; flex-direction:column; gap:20px">
            <div class="card">
                <div class="card-hdr"><span class="card-title">Quick Actions</span></div>
                <div style="padding: 16px; display:flex; flex-direction:column; gap:10px">
                    <a href="{{ route('admin.financial.expenses') }}" class="btn btn-p" style="justify-content:center; padding:12px">
                        <i class="bi bi-plus-circle"></i> New Expense
                    </a>
                    <form action="{{ route('admin.financial.forecasts.refresh') }}" method="POST" style="width:100%">
                        @csrf
                        <button class="btn btn-o" style="width:100%; justify-content:center; padding:12px">
                            <i class="bi bi-arrow-repeat"></i> Refresh Forecasts
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-hdr"><span class="card-title">Monthly Burn (Last 30d)</span></div>
                <div style="padding: 20px">
                    <div style="background: var(--pd); border-radius: 12px; padding: 18px; color: #fff; text-align: center; margin-bottom: 20px">
                        <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.4); margin-bottom: 4px">Total Outflow</div>
                        <div style="font-size: 24px; font-weight: 800">L {{ number_format($expenseCategories->sum('total'), 2) }}</div>
                        <div style="font-size: 11px; margin-top: 8px; color: rgba(255,255,255,0.3)">Avg Daily: L {{ number_format($stats['daily_burn_rate'], 2) }}</div>
                    </div>

                    @foreach($expenseCategories as $cat)
                    <div style="margin-bottom: 14px">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px">
                            <span style="font-size:11px; font-weight:700; color:var(--muted)">{{ $cat->category }}</span>
                            <span style="font-size:11.5px; font-weight:800; color:var(--p)">L {{ number_format($cat->total, 2) }}</span>
                        </div>
                        <div style="height:6px; background:var(--bg); border-radius:10px; overflow:hidden">
                            @php $perc = $expenseCategories->sum('total') > 0 ? ($cat->total / $expenseCategories->sum('total')) * 100 : 0; @endphp
                            <div style="height:100%; width:{{ $perc }}%; background:var(--p); border-radius:10px"></div>
                        </div>
                    </div>
                    @endforeach

                    @if($expenseCategories->isEmpty())
                        <div class="empty" style="padding: 30px 0">
                            <i class="bi bi-pie-chart" style="font-size:30px"></i>
                            <p style="font-size:12px">No data for last 30 days</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('liquidityChart').getContext('2d');
    const snapshots = @json($snapshots);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: snapshots.map(s => new Date(s.snapshot_date).toLocaleDateString('en-GB', {day: '2-digit', month: 'short'})),
            datasets: [
                {
                    label: 'Cash Available',
                    data: snapshots.map(s => s.cash_available),
                    borderColor: '#1e3370',
                    backgroundColor: 'rgba(30, 51, 112, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2
                },
                {
                    label: 'Net Liquidity',
                    data: snapshots.map(s => s.net_liquidity),
                    borderColor: '#3d60d4',
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4,
                    borderWidth: 1.5,
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', align: 'end', labels: { boxWidth: 10, usePointStyle: true, font: { family: "'Inter', sans-serif", size: 11, weight: '600' } } },
                tooltip: { backgroundColor: '#0d1b3e', titleFont: { size: 12 }, bodyFont: { size: 11 }, padding: 10, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: false, grid: { borderDash: [3, 3], color: '#e2e8f0' }, ticks: { font: { size: 10, weight: '500' }, color: '#64748b' } },
                x: { grid: { display: false }, ticks: { font: { size: 10, weight: '500' }, color: '#64748b' } }
            }
        }
    });
});
</script>
@endpush
@endsection
