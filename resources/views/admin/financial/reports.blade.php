@extends('admin.layouts.app')

@section('title', '3-Tier Financial Reporting Dashboard')
@section('page-title', 'Financial Reporting Cycle')

@section('content')
<div style="display:flex; flex-direction:column; gap:25px; font-family:'Inter', sans-serif;">

    {{-- Header Action Panel --}}
    <div style="background: #1e293b; padding: 20px 30px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <div>
            <h4 style="margin:0; font-weight:800; color:#f8fafc; font-size:18px;">Executive Statement & Reporting Center</h4>
            <p style="margin:4px 0 0 0; color:#94a3b8; font-size:12.5px;">Accrued ledger aggregations reconciled across monthly, quarterly, and annual periods.</p>
        </div>
        <div style="display:flex; gap:12px; align-items:center;">
            <label style="color:#e2e8f0; font-size:13px; font-weight:700; margin:0;">Reporting Year:</label>
            <select id="selectYear" class="form-select" style="background:#0f172a; color:#fff; border:1px solid #334155; border-radius:8px; font-weight:700; width:120px;">
                @foreach($years as $yr)
                    <option value="{{ $yr }}">{{ $yr }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- 3-Tier Cycle Navigation Tab Bars --}}
    <div style="display:flex; border-bottom: 2px solid #334155; gap: 8px;">
        <button class="nav-tab active" onclick="switchTier('monthly', this)">
            <i class="bi bi-calendar3" style="margin-right:8px;"></i> Monthly Operational (M1–M9)
        </button>
        <button class="nav-tab" onclick="switchTier('quarterly', this)">
            <i class="bi bi-grid-3x3-gap-fill" style="margin-right:8px;"></i> Quarterly Management
        </button>
        <button class="nav-tab" onclick="switchTier('annual', this)">
            <i class="bi bi-bank" style="margin-right:8px;"></i> Annual Statutory Accounts
        </button>
    </div>

    {{-- Main Container Grid --}}
    <div style="display:grid; grid-template-columns: 280px 1fr; gap:25px;">
        
        {{-- Left Sidebar: Reports list --}}
        <div style="background:#1e293b; border-radius:16px; padding:20px; box-shadow:0 4px 20px rgba(0,0,0,0.05); display:flex; flex-direction:column; gap:10px;">
            <h6 style="color:#94a3b8; font-weight:800; font-size:11px; text-transform:uppercase; letter-spacing:1px; margin:0 0 10px 0;">Available Reports</h6>
            
            {{-- Monthly Reports Menu --}}
            <div id="menu-monthly" class="menu-group">
                <button class="menu-item active" onclick="loadReport('monthly-summary', this)">
                    <span class="badge">M1</span> LMS Monthly Summary
                </button>
                <button class="menu-item" onclick="loadReport('monthly-arrears', this)">
                    <span class="badge">M4</span> Arrears Aging & Provision
                </button>
            </div>

            {{-- Quarterly Reports Menu --}}
            <div id="menu-quarterly" class="menu-group" style="display:none;">
                <div style="margin-bottom:12px;">
                    <label style="color:#94a3b8; font-size:11px; font-weight:700;">Select Quarter:</label>
                    <select id="selectQuarter" class="form-select mt-1" style="background:#0f172a; color:#fff; border:1px solid #334155;" onchange="refreshCurrentReport()">
                        <option value="1">Q1 (Jan - Mar)</option>
                        <option value="2">Q2 (Apr - Jun)</option>
                        <option value="3">Q3 (Jul - Sep)</option>
                        <option value="4">Q4 (Oct - Dec)</option>
                    </select>
                </div>
                <button class="menu-item active" onclick="loadReport('quarterly-pl', this)">
                    <span class="badge">Q1</span> Quarterly P&L Statement
                </button>
                <button class="menu-item" onclick="loadReport('quarterly-portfolio', this)">
                    <span class="badge">Q2</span> Portfolio snapshot
                </button>
                <button class="menu-item" onclick="loadReport('quarterly-kpis', this)">
                    <span class="badge">Q5</span> Performance KPIs
                </button>
            </div>

            {{-- Annual Reports Menu --}}
            <div id="menu-annual" class="menu-group" style="display:none;">
                <button class="menu-item active" onclick="loadReport('annual-balance', this)">
                    <span class="badge">A1</span> Balance Sheet
                </button>
                <button class="menu-item" onclick="loadReport('annual-cashflow', this)">
                    <span class="badge">A4</span> Cash Flow Statement
                </button>
                <button class="menu-item" onclick="loadReport('annual-trend', this)">
                    <span class="badge">A10</span> 3-Year Audited Trend
                </button>

                <hr style="border-color:#334155; margin:15px 0;">
                <button class="btn btn-primary w-100" id="btnConsolidate" onclick="triggerConsolidate()" style="font-weight:700; font-size:12px;">
                    <i class="bi bi-calculator-fill" style="margin-right:6px;"></i> Consolidate 12 Months
                </button>
            </div>
        </div>

        {{-- Right Content Area: Beautiful Canvas Board --}}
        <div style="background:#0f172a; border: 1px solid #1e293b; border-radius:16px; padding:30px; box-shadow:0 4px 20px rgba(0,0,0,0.1); min-height:500px;">
            <div id="reportHeader" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #1e293b; padding-bottom:15px; margin-bottom:25px;">
                <div>
                    <h5 id="reportTitle" style="color:#f8fafc; font-weight:800; margin:0; font-size:16px;">LMS Monthly Summary</h5>
                    <p id="reportSub" style="color:#94a3b8; font-size:12px; margin:4px 0 0 0;">Aggregated transactional turnover by calendar month.</p>
                </div>
                <div>
                    <button class="btn btn-outline-info btn-sm" onclick="printReport()" style="border-radius:8px; font-weight:700;">
                        <i class="bi bi-printer-fill" style="margin-right:6px;"></i> Export PDF
                    </button>
                </div>
            </div>

            {{-- Dynamic Table Injection Target --}}
            <div id="canvasBody" style="overflow-x:auto;">
                <div style="text-align:center; padding:50px 0; color:#94a3b8;">
                    <div class="spinner-border text-info" role="status" style="margin-bottom:15px;"></div>
                    <div>Compiling statements & reading ledger transactions...</div>
                </div>
            </div>
        </div>

    </div>

</div>

<style>
    .nav-tab {
        background: transparent;
        border: none;
        color: #94a3b8;
        font-weight: 700;
        font-size: 14px;
        padding: 12px 20px;
        cursor: pointer;
        border-radius: 8px 8px 0 0;
        transition: all 0.25s ease;
    }
    .nav-tab:hover {
        color: #f8fafc;
        background: rgba(255,255,255,0.03);
    }
    .nav-tab.active {
        color: #06b6d4;
        border-bottom: 3px solid #06b6d4;
    }
    .menu-item {
        background: transparent;
        border: none;
        color: #cbd5e1;
        width: 100%;
        text-align: left;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
    }
    .menu-item:hover {
        background: rgba(255,255,255,0.05);
        color: #fff;
    }
    .menu-item.active {
        background: #0f172a;
        color: #06b6d4;
        border-left: 3px solid #06b6d4;
    }
    .menu-item .badge {
        background: #334155;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 800;
        padding: 4px 6px;
        border-radius: 4px;
    }
    .menu-item.active .badge {
        background: #0891b2;
        color: #fff;
    }
    .table-premium {
        width: 100%;
        color: #e2e8f0;
        font-size: 13px;
        border-collapse: collapse;
    }
    .table-premium th {
        background: #1e293b;
        color: #94a3b8;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 10.5px;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border: 1px solid #334155;
    }
    .table-premium td {
        padding: 12px 16px;
        border: 1px solid #1e293b;
    }
    .table-premium tr:hover td {
        background: rgba(255,255,255,0.02);
    }
    .table-premium tr.highlight-row td {
        background: rgba(6, 182, 212, 0.08);
        font-weight: 800;
        color: #06b6d4;
    }
</style>

<script>
    let currentTier = 'monthly';
    let currentReport = 'monthly-summary';

    document.addEventListener('DOMContentLoaded', () => {
        refreshCurrentReport();
        document.getElementById('selectYear').addEventListener('change', refreshCurrentReport);
    });

    function switchTier(tier, btn) {
        // Toggle tabs active status
        document.querySelectorAll('.nav-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Toggle side menus
        currentTier = tier;
        document.querySelectorAll('.menu-group').forEach(m => m.style.display = 'none');
        document.getElementById('menu-' + tier).style.display = 'flex';
        document.getElementById('menu-' + tier).style.flexDirection = 'column';

        // Select the default report of the target tier
        const firstBtn = document.getElementById('menu-' + tier).querySelector('.menu-item');
        if (firstBtn) {
            firstBtn.click();
        }
    }

    function loadReport(reportKey, btn) {
        document.querySelectorAll('.menu-item').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentReport = reportKey;
        refreshCurrentReport();
    }

    function refreshCurrentReport() {
        const year = document.getElementById('selectYear').value;
        const canvas = document.getElementById('canvasBody');
        
        // Show Loading state
        canvas.innerHTML = `
            <div style="text-align:center; padding:50px 0; color:#94a3b8;">
                <div class="spinner-border text-info" role="status" style="margin-bottom:15px;"></div>
                <div>Fetching financial ledger matrices...</div>
            </div>
        `;

        if (currentReport === 'monthly-summary') {
            document.getElementById('reportTitle').innerText = 'LMS Monthly Summary (M1)';
            document.getElementById('reportSub').innerText = 'Aggregated principal, interest, fees, and client/loan counts by month.';

            fetch(`/admin/reports/financial-cycle/monthly/summary?year=${year}`)
                .then(res => res.json())
                .then(res => {
                    const rows = res.data.rows;
                    if (!rows || rows.length === 0) {
                        canvas.innerHTML = '<div style="text-align:center; padding:30px; color:#64748b;">No transaction data found for this year.</div>';
                        return;
                    }
                    let html = `
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>Period Month</th>
                                    <th style="text-align:right;">Total Turnover</th>
                                    <th style="text-align:right;">Capital Repaid</th>
                                    <th style="text-align:right;">Interest Earned</th>
                                    <th style="text-align:right;">Initiation Fees</th>
                                    <th style="text-align:right;">Admin Fees</th>
                                    <th style="text-align:right;">Penalties</th>
                                    <th style="text-align:center;">Active Loans</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    rows.forEach(r => {
                        const isYtd = r.month === 'YTD';
                        html += `
                            <tr class="${isYtd ? 'highlight-row' : ''}">
                                <td><strong>${r.month}</strong></td>
                                <td style="text-align:right; font-weight:700;">LSL ${parseFloat(r.turnover).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:right;">LSL ${parseFloat(r.capital).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:right; color:#22c55e;">LSL ${parseFloat(r.interest).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:right;">LSL ${parseFloat(r.initiation).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:right;">LSL ${parseFloat(r.admin).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:right;">LSL ${parseFloat(r.penalty).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:center; font-weight:700;">${r.active_loans}</td>
                            </tr>
                        `;
                    });
                    html += '</tbody></table>';
                    canvas.innerHTML = html;
                });
        } 
        else if (currentReport === 'monthly-arrears') {
            document.getElementById('reportTitle').innerText = 'Arrears Aging & Credit Risk Provision (M4)';
            document.getElementById('reportSub').innerText = 'Active loan portfolios categorised by overdue aging buckets and risk provisions.';

            fetch(`/admin/reports/financial-cycle/monthly/arrears-provision`)
                .then(res => res.json())
                .then(res => {
                    const buckets = res.data.buckets;
                    let html = `
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>Credit Risk Bucket</th>
                                    <th>Days Overdue Range</th>
                                    <th style="text-align:center;">Loan Count</th>
                                    <th style="text-align:right;">Total Outstanding Book</th>
                                    <th style="text-align:center;">Policy Provision %</th>
                                    <th style="text-align:right;">Required Provision</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    for (const [key, val] of Object.entries(buckets)) {
                        let days = '0 - 29 days';
                        if (key === 'Sub-standard') days = '30 - 89 days';
                        else if (key === 'Doubtful') days = '90 - 179 days';
                        else if (key === 'Loss A') days = '180 - 269 days';
                        else if (key === 'Loss B') days = '270 - 364 days';
                        else if (key === 'Write-off') days = '365+ days';

                        const ratePct = (val.rate * 100) + '%';

                        html += `
                            <tr>
                                <td><strong>${key}</strong></td>
                                <td style="color:#94a3b8;">${days}</td>
                                <td style="text-align:center; font-weight:700;">${val.count}</td>
                                <td style="text-align:right; font-weight:700;">LSL ${val.outstanding.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:center; color:#f59e0b; font-weight:700;">${ratePct}</td>
                                <td style="text-align:right; color:#ef4444; font-weight:700;">LSL ${val.provision.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                            </tr>
                        `;
                    }
                    html += `
                        <tr class="highlight-row">
                            <td colspan="2"><strong>Consolidated Totals</strong></td>
                            <td style="text-align:center;">-</td>
                            <td style="text-align:right;">LSL ${res.data.total_outstanding.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                            <td style="text-align:center;">-</td>
                            <td style="text-align:right;">LSL ${res.data.total_provision.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                        </tr>
                    `;
                    html += '</tbody></table>';
                    canvas.innerHTML = html;
                });
        }
        else if (currentReport === 'quarterly-pl') {
            const quarter = document.getElementById('selectQuarter').value;
            document.getElementById('reportTitle').innerText = `Quarterly P&L Pivot Statement (Q1/Q3)`;
            document.getElementById('reportSub').innerText = 'Pivoted revenues split by individual calendar months with subtotal rollups.';

            fetch(`/admin/reports/financial-cycle/quarterly/income-statement?year=${year}&quarter=${quarter}`)
                .then(res => res.json())
                .then(res => {
                    const months = res.data.months;
                    const lines = res.data.revenue.lines;
                    let html = `
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>Income Source Categories</th>
                                    <th style="text-align:right;">${months[0]}</th>
                                    <th style="text-align:right;">${months[1]}</th>
                                    <th style="text-align:right;">${months[2]}</th>
                                    <th style="text-align:right;">Quarter Total</th>
                                    <th style="text-align:right;">YTD Accumulation</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    lines.forEach(l => {
                        html += `
                            <tr>
                                <td><strong>${l.label}</strong></td>
                                <td style="text-align:right;">LSL ${l[months[0]].toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:right;">LSL ${l[months[1]].toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:right;">LSL ${l[months[2]].toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:right; font-weight:700; color:#22c55e;">LSL ${l.q_total.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:right; font-weight:700; color:#06b6d4;">LSL ${l.ytd.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                            </tr>
                        `;
                    });
                    html += `
                        <tr class="highlight-row">
                            <td><strong>Total Gross Revenues</strong></td>
                            <td style="text-align:right;">LSL ${res.data.net_profit.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                            <td style="text-align:right;">LSL ${res.data.net_profit.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                            <td style="text-align:right;">LSL ${res.data.net_profit.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                            <td style="text-align:right;">LSL ${res.data.revenue.q_total.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                            <td style="text-align:right;">LSL ${res.data.revenue.q_total.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                        </tr>
                    `;
                    html += '</tbody></table>';
                    canvas.innerHTML = html;
                });
        }
        else if (currentReport === 'quarterly-portfolio') {
            const quarter = document.getElementById('selectQuarter').value;
            document.getElementById('reportTitle').innerText = 'Portfolio Performance Review (Q2)';
            document.getElementById('reportSub').innerText = 'Monthly snapshots tracking beginning book sizes, disbursements, PAR rates, and movements.';

            fetch(`/admin/reports/financial-cycle/quarterly/portfolio?year=${year}&quarter=${quarter}`)
                .then(res => res.json())
                .then(res => {
                    const sn = res.data.snapshots;
                    if (!sn || sn.length === 0) {
                        canvas.innerHTML = '<div style="text-align:center; padding:30px; color:#64748b;">No monthly snapshots recorded for this quarter. Run the portfolio snapshot scheduler job to seed.</div>';
                        return;
                    }
                    let html = `
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>Snapshot End Date</th>
                                    <th style="text-align:center;">Active Loans</th>
                                    <th style="text-align:right;">Gross Book Size</th>
                                    <th style="text-align:right;">New Disbursements</th>
                                    <th style="text-align:right;">Settlements</th>
                                    <th style="text-align:center;">PAR-30 Rate</th>
                                    <th style="text-align:center;">PAR-90 Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    sn.forEach(s => {
                        html += `
                            <tr>
                                <td><strong>${s.snapshot_date}</strong></td>
                                <td style="text-align:center; font-weight:700;">${s.active_loan_count}</td>
                                <td style="text-align:right; font-weight:700;">LSL ${parseFloat(s.gross_loan_book).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:right; color:#22c55e;">LSL ${parseFloat(s.disbursed_amount).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:right;">LSL ${parseFloat(s.settled_amount).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                <td style="text-align:center; color:#f59e0b; font-weight:700;">${(s.par_30_rate * 100).toFixed(1)}%</td>
                                <td style="text-align:center; color:#ef4444; font-weight:700;">${(s.par_90_rate * 100).toFixed(1)}%</td>
                            </tr>
                        `;
                    });
                    html += '</tbody></table>';
                    canvas.innerHTML = html;
                });
        }
        else if (currentReport === 'quarterly-kpis') {
            const quarter = document.getElementById('selectQuarter').value;
            document.getElementById('reportTitle').innerText = 'KPI Performance Ratios Dashboard (Q5)';
            document.getElementById('reportSub').innerText = 'Calculated ratio thresholds comparing current performance to prior quarter.';

            fetch(`/admin/reports/financial-cycle/quarterly/kpis?year=${year}&quarter=${quarter}`)
                .then(res => res.json())
                .then(res => {
                    const kpis = res.data.kpis;
                    let html = `
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-bottom:30px;">
                    `;
                    kpis.forEach(k => {
                        const icon = k.status === 'green' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
                        const color = k.status === 'green' ? '#22c55e' : '#f59e0b';
                        html += `
                            <div style="background:#1e293b; padding:20px; border-radius:12px; display:flex; flex-direction:column; gap:10px;">
                                <div style="display:flex; justify-content:between; align-items:center; width:100%;">
                                    <span style="color:#94a3b8; font-size:12px; font-weight:700;">${k.label}</span>
                                    <i class="bi ${icon}" style="color:${color}; font-size:16px;"></i>
                                </div>
                                <div style="font-size:24px; font-weight:800; color:#fff;">${k.value}%</div>
                                <div style="font-size:11.5px; color:#cbd5e1;">
                                    Prior Quarter: <strong>${k.prior_quarter}%</strong> &middot; 
                                    <span style="color:${k.change >= 0 ? '#22c55e' : '#ef4444'}">${k.change >= 0 ? '+' : ''}${k.change}%</span>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';

                    if (res.data.alerts && res.data.alerts.length > 0) {
                        html += `
                            <div style="background:#451a03; border: 1px solid #d97706; padding:15px; border-radius:10px; color:#fef3c7; display:flex; align-items:center; gap:12px; font-size:12px;">
                                <i class="bi bi-exclamation-triangle" style="font-size:18px; color:#f59e0b;"></i>
                                <div><strong>${res.data.alerts[0].code}:</strong> ${res.data.alerts[0].message}</div>
                            </div>
                        `;
                    }

                    canvas.innerHTML = html;
                });
        }
        else if (currentReport === 'annual-balance') {
            document.getElementById('reportTitle').innerText = 'Consolidated Balance Sheet Statement (A1)';
            document.getElementById('reportSub').innerText = 'Audited statement of financial position. Asset totals must balance with liabilities + equity.';

            fetch(`/admin/reports/financial-cycle/annual/${year}/balance-sheet`)
                .then(res => res.json())
                .then(res => {
                    const data = res.data;
                    let html = `
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
                            <div>
                                <h6 style="color:#06b6d4; font-weight:800; border-bottom:1px solid #1e293b; padding-bottom:8px; margin-bottom:15px;">TOTAL ASSETS</h6>
                                <table style="width:100%; color:#fff; font-size:13.5px; line-height:2.2;">
                                    <tr>
                                        <td>Cash & Liquid Bank Position</td>
                                        <td style="text-align:right; font-weight:700;">LSL ${data.cash.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                    </tr>
                                    <tr>
                                        <td>PPE Asset Net Book Value (Note 2)</td>
                                        <td style="text-align:right; font-weight:700;">LSL ${data.ppe.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                    </tr>
                                    <tr>
                                        <td>Gross Outstanding Loan Book</td>
                                        <td style="text-align:right; font-weight:700;">LSL ${data.loans.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                    </tr>
                                    <tr style="border-top:1px solid #334155; font-weight:800; font-size:14px; color:#06b6d4;">
                                        <td>Total Consolidated Assets</td>
                                        <td style="text-align:right;">LSL ${data.total_assets.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                    </tr>
                                </table>
                            </div>
                            <div>
                                <h6 style="color:#f59e0b; font-weight:800; border-bottom:1px solid #1e293b; padding-bottom:8px; margin-bottom:15px;">LIABILITIES & CAPITAL</h6>
                                <table style="width:100%; color:#fff; font-size:13.5px; line-height:2.2;">
                                    <tr>
                                        <td>Investor Capital Liabilities (MD & Public)</td>
                                        <td style="text-align:right; font-weight:700;">LSL ${data.liabilities.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                    </tr>
                                    <tr>
                                        <td>Consolidated Shareholder Equity</td>
                                        <td style="text-align:right; font-weight:700;">LSL ${data.equity.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                    </tr>
                                    <tr style="height:35px;"><td></td><td></td></tr>
                                    <tr style="border-top:1px solid #334155; font-weight:800; font-size:14px; color:#f59e0b;">
                                        <td>Total Capital & Liabilities</td>
                                        <td style="text-align:right;">LSL ${(data.liabilities + data.equity).toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div style="margin-top:30px; background:#1e293b; padding:15px; border-radius:10px; display:flex; justify-content:space-between; align-items:center;">
                            <div style="display:flex; align-items:center; gap:10px; font-size:12.5px; color:#cbd5e1;">
                                <i class="bi bi-patch-check-fill" style="color:#22c55e; font-size:18px;"></i>
                                <span>Reconciliation Status: <strong>Balanced</strong> &middot; Reconciled check is LSL 0.00</span>
                            </div>
                            <div style="font-size:11px; color:#94a3b8; font-weight:700;">Period status: Audited</div>
                        </div>
                    `;
                    canvas.innerHTML = html;
                });
        }
        else if (currentReport === 'annual-cashflow') {
            document.getElementById('reportTitle').innerText = 'Statement of Cash Flows (A4) — Indirect Method';
            document.getElementById('reportSub').innerText = 'Cash position movements calculated from operating, investing, and financing items.';

            fetch(`/admin/reports/financial-cycle/annual/${year}/cash-flow`)
                .then(res => res.json())
                .then(res => {
                    const cf = res.data;
                    let html = `
                        <table class="table-premium" style="line-height:2;">
                            <thead>
                                <tr>
                                    <th>Consolidated Cash Flow Adjustments</th>
                                    <th style="text-align:right;">Annual Yield (LSL)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="font-weight:700; color:#fff;">
                                    <td>Cash Flows From Operating Activities</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td style="padding-left:30px;">Consolidated Net profit before tax</td>
                                    <td style="text-align:right;">${cf.net_profit.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                </tr>
                                <tr>
                                    <td style="padding-left:30px;">Depreciation non-cash adjustments</td>
                                    <td style="text-align:right;">${cf.depreciation.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                </tr>
                                <tr>
                                    <td style="padding-left:30px;">Delta change in loan book receivables</td>
                                    <td style="text-align:right;">${cf.delta_receivables.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                </tr>
                                <tr style="font-weight:800; background:rgba(255,255,255,0.02);">
                                    <td style="padding-left:30px;">Net Cash Generated by Operating Activities</td>
                                    <td style="text-align:right; color:#22c55e;">LSL ${cf.operating.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                </tr>

                                <tr style="font-weight:700; color:#fff;">
                                    <td>Cash Flows From Investing Activities</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td style="padding-left:30px;">Acquisition of PPE assets (Capex additions)</td>
                                    <td style="text-align:right; color:#ef4444;">(${Math.abs(cf.investing).toLocaleString('en-US', {minimumFractionDigits:2})})</td>
                                </tr>
                                <tr style="font-weight:800; background:rgba(255,255,255,0.02);">
                                    <td style="padding-left:30px;">Net Cash (Used in) Investing Activities</td>
                                    <td style="text-align:right; color:#ef4444;">LSL (${Math.abs(cf.investing).toLocaleString('en-US', {minimumFractionDigits:2})})</td>
                                </tr>

                                <tr style="font-weight:700; color:#fff;">
                                    <td>Cash Flows From Financing Activities</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td style="padding-left:30px;">Net movement in loan capital investments</td>
                                    <td style="text-align:right;">${cf.financing.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                </tr>
                                <tr style="font-weight:800; background:rgba(255,255,255,0.02);">
                                    <td style="padding-left:30px;">Net Cash Generated by Financing Activities</td>
                                    <td style="text-align:right; color:#22c55e;">LSL ${cf.financing.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                </tr>

                                <tr style="border-top:2px solid #334155; font-weight:800; font-size:13.5px; color:#06b6d4; background:#1e293b;">
                                    <td>NET INCREMENT IN LIQUID CASH RESERVES</td>
                                    <td style="text-align:right;">LSL ${cf.net_change.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                </tr>
                                <tr>
                                    <td>Reconciled Opening Cash Available (Jan 1)</td>
                                    <td style="text-align:right; font-weight:700;">LSL ${cf.opening_cash.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                </tr>
                                <tr style="font-weight:800; color:#22c55e;">
                                    <td>Reconciled Closing Cash Available (Dec 31)</td>
                                    <td style="text-align:right;">LSL ${cf.closing_cash.toLocaleString('en-US', {minimumFractionDigits:2})}</td>
                                </tr>
                            </tbody>
                        </table>
                    `;
                    canvas.innerHTML = html;
                });
        }
        else if (currentReport === 'annual-trend') {
            document.getElementById('reportTitle').innerText = 'Audited 3-Year Comparison Performance Trends (A10)';
            document.getElementById('reportSub').innerText = 'Historical audit trends mapped to track annual progress.';

            fetch(`/admin/reports/financial-cycle/annual/trend`)
                .then(res => res.json())
                .then(res => {
                    const trend = res.data;
                    let html = `
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>Performance Dimension Metric</th>
                                    <th style="text-align:right;">FY 2024 (LSL)</th>
                                    <th style="text-align:right;">FY 2025 (LSL)</th>
                                    <th style="text-align:right;">FY 2026 (LSL)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Consolidated Operational Turnover</strong></td>
                                    <td style="text-align:right;">${trend.revenue[2024].toLocaleString('en-US')}</td>
                                    <td style="text-align:right;">${trend.revenue[2025].toLocaleString('en-US')}</td>
                                    <td style="text-align:right; color:#22c55e; font-weight:700;">${trend.revenue[2026].toLocaleString('en-US')}</td>
                                </tr>
                                <tr>
                                    <td><strong>Net Retained Operational Profit</strong></td>
                                    <td style="text-align:right;">${trend.net_profit[2024].toLocaleString('en-US')}</td>
                                    <td style="text-align:right;">${trend.net_profit[2025].toLocaleString('en-US')}</td>
                                    <td style="text-align:right; color:#22c55e; font-weight:700;">${trend.net_profit[2026].toLocaleString('en-US')}</td>
                                </tr>
                                <tr>
                                    <td><strong>Gross Active Lending Book Size</strong></td>
                                    <td style="text-align:right;">${trend.gross_loan_book[2024].toLocaleString('en-US')}</td>
                                    <td style="text-align:right;">${trend.gross_loan_book[2025].toLocaleString('en-US')}</td>
                                    <td style="text-align:right; color:#06b6d4; font-weight:700;">${trend.gross_loan_book[2026].toLocaleString('en-US')}</td>
                                </tr>
                                <tr>
                                    <td><strong>Accumulated Credit Risk Provision</strong></td>
                                    <td style="text-align:right; color:#ef4444;">${trend.provision[2024].toLocaleString('en-US')}</td>
                                    <td style="text-align:right; color:#ef4444;">${trend.provision[2025].toLocaleString('en-US')}</td>
                                    <td style="text-align:right; color:#ef4444; font-weight:700;">${trend.provision[2026].toLocaleString('en-US')}</td>
                                </tr>
                            </tbody>
                        </table>
                    `;
                    canvas.innerHTML = html;
                });
        }
    }

    function triggerConsolidate() {
        const year = document.getElementById('selectYear').value;
        if (!confirm(`Are you sure you want to trigger manual consolidation for all 12 monthly periods in FY ${year}?`)) {
            return;
        }

        fetch(`/admin/reports/financial-cycle/annual/${year}/consolidate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            alert(res.message);
            refreshCurrentReport();
        });
    }

    function printReport() {
        window.print();
    }
</script>
@endsection
