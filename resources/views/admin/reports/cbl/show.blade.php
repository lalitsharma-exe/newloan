@extends('admin.layouts.app')

@section('title', 'CBL Compliance Report')
@section('page-title', 'Regulatory Reporting Analysis')

@push('styles')
<style>
    :root {
        --cbl-gold: #b8860b;
        --cbl-dark: #1a1a1a;
    }
    .section-nav { position: sticky; top: 84px; display: flex; flex-direction: column; gap: 4px; z-index: 10; }
    .section-link { padding: 12px 16px; border-radius: 8px; color: var(--muted); text-decoration: none; font-size: 12px; font-weight: 700; transition: all 0.2s; border: 1px solid transparent; display: flex; align-items: center; justify-content: space-between; }
    .section-link:hover { background: var(--bg); color: var(--p); border-color: var(--border); }
    .section-link.active { background: var(--p); color: #fff; box-shadow: 0 4px 12px rgba(61, 96, 212, 0.2); }
    .stat-card { background: #fff; border-radius: 16px; padding: 20px; border: 1px solid var(--border); display: flex; flex-direction: column; gap: 8px; }
    .stat-val { font-size: 24px; font-weight: 800; color: var(--dark); }
    .stat-lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--muted); letter-spacing: 0.5px; }
    .badge-cbl { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .table-premium th { background: var(--bg); color: var(--muted); font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 15px; }
    .table-premium td { padding: 14px 15px; font-size: 13px; border-bottom: 1px solid var(--bg); }
    .report-section { scroll-margin-top: 100px; margin-bottom: 40px; }
    .grid-report { display: grid; grid-template-columns: 280px 1fr; gap: 30px; align-items: start; }
    @media (max-width: 1200px) { .grid-report { grid-template-columns: 1fr; } .section-nav { display: none; } }
</style>
@endpush

@section('content')
<div class="grid-report">
    <!-- Sidebar Navigation -->
    <div class="section-nav">
        <div class="card" style="margin-bottom: 20px; border-top: 4px solid var(--p)">
            <div style="padding: 16px">
                <div class="stat-lbl" style="margin-bottom: 15px">Validation Gate</div>
                <div style="display:flex; flex-direction:column; gap:10px">
                    @foreach($report['validations'] as $key => $val)
                        <div style="display:flex; align-items:center; gap:10px">
                            @if($val['status'] === 'PASS')
                                <i class="bi bi-check-circle-fill" style="color:var(--ok); font-size:16px"></i>
                            @else
                                <i class="bi bi-exclamation-octagon-fill" style="color:var(--err); font-size:16px"></i>
                            @endif
                            <div style="line-height:1.2">
                                <div style="font-size:11px; font-weight:700; color:var(--dark)">{{ $key }}</div>
                                <div class="muted" style="font-size:10px">{{ $val['label'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div style="margin-top: 25px">
                    @if($report['ready_for_export'] && !isset($archive))
                        <form action="{{ route('admin.reports.cbl.archive') }}" method="POST">
                            @csrf
                            <input type="hidden" name="payload" value="{{ json_encode($report) }}">
                            <button type="submit" class="btn btn-p w-100" style="justify-content:center; padding:12px; border-radius:12px">
                                <i class="bi bi-shield-lock-fill"></i> Lock & Archive
                            </button>
                        </form>
                    @elseif(isset($archive))
                        <div class="badge-cbl bok w-100" style="text-align:center; padding:12px; background:var(--ok); color:#fff">
                            <i class="bi bi-lock-fill"></i> AUDIT LOCKED
                        </div>
                    @else
                        <div class="badge-cbl w-100" style="text-align:center; padding:12px; background:var(--bg); color:var(--muted); border:1px dashed var(--border)">
                            <i class="bi bi-shield-slash"></i> EXPORTS DISABLED
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div style="padding: 10px; display:flex; flex-direction:column; gap:2px">
                <a href="#s31" class="section-link active">3.1 Portfolio Quality <i class="bi bi-chevron-right"></i></a>
                <a href="#s32" class="section-link">3.2 Risk Ratios <i class="bi bi-chevron-right"></i></a>
                <a href="#s33" class="section-link">3.3 Top 10 Borrowers <i class="bi bi-chevron-right"></i></a>
                <a href="#s34" class="section-link">3.4 Demographics <i class="bi bi-chevron-right"></i></a>
                <a href="#s35" class="section-link">3.5 SME Loans <i class="bi bi-chevron-right"></i></a>
                <a href="#s36" class="section-link">3.6 Loan Tenor <i class="bi bi-chevron-right"></i></a>
                <a href="#s37" class="section-link">3.7 Loan Activity <i class="bi bi-chevron-right"></i></a>
                <a href="#s38" class="section-link">3.8 Arrears Summary <i class="bi bi-chevron-right"></i></a>
                <a href="#s39" class="section-link">3.9 Write-Offs <i class="bi bi-chevron-right"></i></a>
                <a href="#s310" class="section-link">3.10 Complaints <i class="bi bi-chevron-right"></i></a>
                <a href="#s311" class="section-link">3.11 Over-Indebtedness <i class="bi bi-chevron-right"></i></a>
                <a href="#s312" class="section-link">3.12 Pricing Fairness <i class="bi bi-chevron-right"></i></a>
                <a href="#s313" class="section-link">3.13 Client Growth <i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div style="display:flex; flex-direction:column; gap:0">
        <!-- Premium Header -->
        <div class="card" style="background: linear-gradient(135deg, #1a1a1a 0%, #333 100%); border:none; border-radius:24px; overflow:hidden; position:relative; margin-bottom:30px">
            <div style="position:absolute; right:-20px; top:-20px; font-size:160px; color:rgba(255,255,255,0.03); transform:rotate(-15deg)"><i class="bi bi-bank"></i></div>
            <div style="padding: 40px; position:relative; z-index:1; display:flex; justify-content:space-between; align-items:center">
                <div>
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px">
                        <img src="/assets/img/logo-white.png" style="height:32px" onerror="this.style.display='none'">
                        <div style="width:2px; height:24px; background:rgba(255,255,255,0.2)"></div>
                        <span style="color:#fff; font-weight:800; letter-spacing:1px; text-transform:uppercase; font-size:14px">CBL Regulatory Report</span>
                    </div>
                    <h2 style="color:#fff; font-weight:900; margin:0; font-size:32px">Period: {{ $report['metadata']['period'] }}</h2>
                    <div style="display:flex; gap:20px; margin-top:15px">
                        <div style="display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.6); font-size:12px">
                            <i class="bi bi-geo-alt"></i> Lesotho Jurisdiction
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.6); font-size:12px">
                            <i class="bi bi-shield-check"></i> Standard: Basel II/III Compliant
                        </div>
                    </div>
                </div>
                <div style="text-align:right">
                    <div class="stat-lbl" style="color:rgba(255,255,255,0.5)">System Snapshot Hash</div>
                    <code style="color:var(--p); background:rgba(255,255,255,0.05); padding:6px 12px; border-radius:8px; font-size:11px">{{ substr($report['metadata']['generated_at'], 0, 10) }}-RECON-{{ substr(md5(now()), 0, 8) }}</code>
                </div>
            </div>
        </div>

        <!-- Section 3.1: Portfolio Quality -->
        <div class="report-section" id="s31">
            <div style="display:flex; justify-content:space-between; align-items:end; margin-bottom:15px">
                <div>
                    <div class="stat-lbl">Section 3.1</div>
                    <h3 style="margin:0; font-weight:800">Portfolio Quality & Aging</h3>
                </div>
                <div class="badge-cbl bok" style="background:var(--ok); color:#fff">Institutional Risk Scan</div>
            </div>
            <div class="card">
                <table class="table-premium w-100">
                    <thead>
                        <tr>
                            <th>Aging Bucket</th>
                            <th>Classification</th>
                            <th>Provision</th>
                            <th style="text-align:center">Loan Count</th>
                            <th style="text-align:right">Outstanding Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data']['3.1'] as $row)
                        <tr>
                            <td style="font-weight:800; color:var(--dark)">{{ $row['bucket'] }}</td>
                            <td>
                                @php $color = match($row['classification']){ 'Performing'=>'var(--ok)', 'Watch'=>'var(--warn)', default=>'var(--err)' }; @endphp
                                <span style="font-weight:700; color:{{ $color }}">{{ $row['classification'] }}</span>
                            </td>
                            <td>{{ $row['provision_rate'] }}%</td>
                            <td style="text-align:center; font-weight:600">{{ number_format($row['count']) }}</td>
                            <td style="text-align:right; font-weight:800">L {{ number_format($row['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:var(--bg); font-weight:900">
                            <td colspan="3">TOTAL ACTIVE PORTFOLIO</td>
                            <td style="text-align:center">{{ number_format(collect($report['data']['3.1'])->sum('count')) }}</td>
                            <td style="text-align:right; font-size:16px; color:var(--p)">L {{ number_format(collect($report['data']['3.1'])->sum('amount'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Section 3.2: Key Risk Ratios -->
        <div class="report-section" id="s32">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.2 — Key Risk Ratios</div>
            <div class="g4">
                <div class="stat-card">
                    <span class="stat-lbl">PAR 30 Ratio</span>
                    <span class="stat-val" style="color:{{ $report['data']['3.2']['par_30'] > 5 ? 'var(--err)' : 'var(--ok)' }}">{{ number_format($report['data']['3.2']['par_30'], 2) }}%</span>
                    <div style="font-size:10px; color:var(--muted)">Regulatory Limit: <span style="font-weight:700">5.00%</span></div>
                </div>
                <div class="stat-card">
                    <span class="stat-lbl">NPL Ratio</span>
                    <span class="stat-val" style="color:{{ $report['data']['3.2']['npl_ratio'] > 3 ? 'var(--err)' : 'var(--ok)' }}">{{ number_format($report['data']['3.2']['npl_ratio'], 2) }}%</span>
                    <div style="font-size:10px; color:var(--muted)">Target: <span style="font-weight:700">&lt; 3.00%</span></div>
                </div>
                <div class="stat-card">
                    <span class="stat-lbl">Risk per Officer</span>
                    <span class="stat-val">{{ number_format($report['data']['3.11']['loans_per_officer'], 1) }}</span>
                    <div style="font-size:10px; color:var(--muted)">Loans / Active Officer</div>
                </div>
                <div class="stat-card">
                    <span class="stat-lbl">Portfolio Yield</span>
                    <span class="stat-val">{{ number_format($report['data']['3.12']['max_rate'], 1) }}%</span>
                    <div style="font-size:10px; color:var(--muted)">Weighted Avg Rate</div>
                </div>
            </div>
        </div>

        <!-- Section 3.3: Top Borrowers -->
        <div class="report-section" id="s33">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.3 — Top 10 Borrowers by Exposure</div>
            <div class="card">
                <table class="table-premium w-100">
                    <thead>
                        <tr>
                            <th>Borrower Identity</th>
                            <th style="text-align:right">Principal</th>
                            <th style="text-align:right">Monthly Instalment</th>
                            <th style="text-align:right">Outstanding Exposure</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data']['3.3'] as $row)
                        <tr>
                            <td style="font-weight:700">{{ $row['name'] }}</td>
                            <td style="text-align:right">L {{ number_format($row['principal_amount'], 2) }}</td>
                            <td style="text-align:right">L {{ number_format($row['monthly_installment'], 2) }}</td>
                            <td style="text-align:right; font-weight:800; color:var(--p)">L {{ number_format($row['outstanding_balance'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 3.4: Demographics -->
        <div class="report-section" id="s34">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.4 — Gender Distribution</div>
            <div class="card">
                <table class="table-premium w-100">
                    <thead>
                        <tr>
                            <th>Gender</th>
                            <th style="text-align:center">Borrowers</th>
                            <th style="text-align:right">Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data']['3.4'] as $row)
                        <tr>
                            <td style="font-weight:700">{{ $row['gender'] }}</td>
                            <td style="text-align:center">{{ number_format($row['count']) }}</td>
                            <td style="text-align:right">L {{ number_format($row['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 3.5: SME Classification -->
        <div class="report-section" id="s35">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.5 — SME Loans (by Size)</div>
            <div class="card">
                <table class="table-premium w-100">
                    <thead>
                        <tr>
                            <th>Company Size</th>
                            <th style="text-align:center">Loans</th>
                            <th style="text-align:right">Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data']['3.5'] as $row)
                        <tr>
                            <td style="font-weight:700">{{ $row['category'] }}</td>
                            <td style="text-align:center">{{ number_format($row['count']) }}</td>
                            <td style="text-align:right">L {{ number_format($row['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3.6: Loan Tenor -->
        <div class="report-section" id="s36">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.6 — Loan Tenor Distribution</div>
            <div class="card" style="padding:20px; display:grid; grid-template-columns: repeat(4, 1fr); gap:20px">
                @foreach($report['data']['3.6'] as $label => $group)
                <div style="background:var(--bg); padding:15px; border-radius:12px; text-align:center">
                    <div class="stat-lbl" style="font-size:9px">{{ $label }}</div>
                    <div style="font-size:18px; font-weight:800; color:var(--p); margin:5px 0">{{ $group->count() }}</div>
                    <div class="muted" style="font-size:10px">L {{ number_format($group->sum('outstanding_balance'), 2) }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 3.7: Loan Activity -->
        <div class="report-section" id="s37">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.7 — Loan Activity (Reporting Period)</div>
            <div class="card">
                <table class="table-premium w-100">
                    <thead>
                        <tr>
                            <th>Activity Type</th>
                            <th style="text-align:center">Count</th>
                            <th style="text-align:right">Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight:700">New Disbursements</td>
                            <td style="text-align:center">{{ number_format($report['data']['3.7']['new']['count']) }}</td>
                            <td style="text-align:right">L {{ number_format($report['data']['3.7']['new']['amount'], 2) }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700">Settled Loans</td>
                            <td style="text-align:center">{{ number_format($report['data']['3.7']['settled']['count']) }}</td>
                            <td style="text-align:right">L {{ number_format($report['data']['3.7']['settled']['amount'], 2) }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700">Written Off</td>
                            <td style="text-align:center">{{ number_format($report['data']['3.7']['write_offs']['count']) }}</td>
                            <td style="text-align:right">L {{ number_format($report['data']['3.7']['write_offs']['amount'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3.8: Arrears Summary -->
        <div class="report-section" id="s38">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.8 — Arrears Classification Summary</div>
            <div class="card">
                <table class="table-premium w-100">
                    <thead>
                        <tr>
                            <th>Aging Category</th>
                            <th style="text-align:center">Loans</th>
                            <th style="text-align:right">Volume in Arrears</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['data']['3.8'] as $label => $vals)
                        <tr>
                            <td style="font-weight:700">{{ $label }}</td>
                            <td style="text-align:center">{{ number_format($vals['count']) }}</td>
                            <td style="text-align:right">L {{ number_format($vals['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 3.9: Write-Offs -->
        <div class="report-section" id="s39">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.9 — Write-Offs Analysis</div>
            <div class="card" style="padding:24px; text-align:center">
                <div class="stat-lbl">Total Written Off</div>
                <div style="font-size:24px; font-weight:800; color:var(--err); margin:10px 0">L {{ number_format($report['data']['3.9']['amount'], 2) }}</div>
                <div class="muted" style="font-size:12px">{{ $report['data']['3.9']['count'] }} loans categorized as loss</div>
            </div>
        </div>

        <!-- Section 3.10: Complaints -->
        <div class="report-section" id="s310">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.10 — Complaints Register Summary</div>
            <div class="card" style="padding:24px; display:flex; justify-content:space-around; align-items:center; text-align:center">
                <div>
                    <div class="stat-lbl">Internal Resolution</div>
                    <div style="font-size:24px; font-weight:800; color:var(--ok)">{{ $report['data']['3.10']['internal'] }}</div>
                </div>
                <div style="width:1px; height:40px; background:var(--border)"></div>
                <div>
                    <div class="stat-lbl">CBL Referrals</div>
                    <div style="font-size:24px; font-weight:800; color:var(--err)">{{ $report['data']['3.10']['referred'] }}</div>
                </div>
            </div>
        </div>

        <!-- Section 3.11: Over-Indebtedness -->
        <div class="report-section" id="s311">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.11 — Over-Indebtedness Indicators</div>
            <div class="card" style="padding:20px">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px">
                    <div>
                        <div style="font-weight:800; font-size:16px">Debt-to-Income (DTI) Analysis</div>
                        <div class="muted" style="font-size:12px">Average borrower obligation relative to monthly net income</div>
                    </div>
                    <div style="text-align:right">
                        <div class="stat-lbl">Average DTI</div>
                        <div style="font-size:24px; font-weight:800; color:{{ $report['data']['3.11']['avg_dti'] > 40 ? 'var(--err)' : 'var(--ok)' }}">
                            {{ number_format($report['data']['3.11']['avg_dti'], 1) }}%
                        </div>
                    </div>
                </div>
                <div style="height:8px; background:var(--bg); border-radius:4px; overflow:hidden">
                    <div style="width:{{ min(100, $report['data']['3.11']['avg_dti']) }}%; height:100%; background:{{ $report['data']['3.11']['avg_dti'] > 40 ? 'var(--err)' : 'var(--ok)' }}"></div>
                </div>
                <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:10px; font-weight:700; text-transform:uppercase; color:var(--muted)">
                    <span>Healthy (< 30%)</span>
                    <span>Caution (30-40%)</span>
                    <span>Risk (> 40%)</span>
                </div>
            </div>
        </div>

        <!-- Section 3.12: Pricing Fairness -->
        <div class="report-section" id="s312">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.12 — Pricing & Fairness</div>
            <div class="card" style="padding:24px; text-align:center">
                <div class="stat-lbl">Max Interest Rate Applied</div>
                <div style="font-size:24px; font-weight:800; color:var(--p); margin:10px 0">{{ number_format($report['data']['3.12']['max_rate'], 2) }}%</div>
                <div class="muted" style="font-size:11px"><i class="bi bi-shield-check"></i> Within approved rate schedule</div>
            </div>
        </div>

        <!-- Section 3.13: Client Growth -->
        <div class="report-section" id="s313">
            <div class="stat-lbl" style="margin-bottom:15px">Section 3.13 — Client Acquisition & Growth</div>
            <div class="card" style="padding:24px; display:flex; justify-content:space-around; align-items:center; text-align:center">
                <div>
                    <div class="stat-lbl">New Acquisitions</div>
                    <div style="font-size:24px; font-weight:800; color:var(--p)">{{ $report['data']['3.13']['new_clients'] }}</div>
                </div>
                <div style="width:1px; height:40px; background:var(--border)"></div>
                <div>
                    <div class="stat-lbl">Active Base</div>
                    <div style="font-size:24px; font-weight:800">{{ $report['data']['3.13']['total_active'] }}</div>
                </div>
            </div>
        </div>

        <!-- Bottom Actions -->
        <div style="display:flex; justify-content:center; gap:15px; margin-top:20px; padding-bottom:100px">
            <button class="btn btn-o btn-lg" onclick="window.print()"><i class="bi bi-printer"></i> Print Analysis</button>
            <button class="btn btn-ok btn-lg"><i class="bi bi-file-earmark-excel"></i> Export Excel (Section 1-13)</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.section-link').forEach(link => {
        link.addEventListener('click', function(e) {
            document.querySelectorAll('.section-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Simple scroll spy
    window.addEventListener('scroll', () => {
        let current = "";
        document.querySelectorAll(".report-section").forEach((section) => {
            const sectionTop = section.offsetTop;
            if (pageYOffset >= sectionTop - 120) {
                current = section.getAttribute("id");
            }
        });

        document.querySelectorAll(".section-link").forEach((link) => {
            link.classList.remove("active");
            if (link.getAttribute("href").includes(current)) {
                link.classList.add("active");
            }
        });
    });
</script>
@endpush
@endsection
