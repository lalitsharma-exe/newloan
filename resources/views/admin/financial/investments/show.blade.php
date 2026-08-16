@extends('admin.layouts.app')
@section('title', 'Agreement: ' . $investment->contract_ref)
@section('page-title', 'Capital Agreement Details')

@section('content')
<div style="display:grid; grid-template-columns: 1fr 420px; gap:28px; align-items: start;">
    
    {{-- Left: Contract Information & Accruals Calendar --}}
    <div style="display:flex; flex-direction:column; gap:24px">
        
        <!-- Summary Header -->
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #312e81 100%); border-radius: 20px; padding: 28px; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(30, 58, 138, 0.15);">
            <div style="position: absolute; right: -20px; bottom: -20px; font-size: 140px; opacity: 0.05; color: white;"><i class="bi bi-shield-shaded"></i></div>
            <div style="position: relative; z-index: 1; display:flex; justify-content:space-between; align-items:start;">
                <div>
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin-bottom: 8px;">Investment Tranche Details</div>
                    <h2 style="font-size: 26px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">{{ $investment->contract_ref }}</h2>
                    <p style="font-size: 13px; color: #94a3b8; margin-top: 8px; max-width: 450px;">
                        Created for <strong>{{ $investment->investor->full_name }}</strong> &middot; Type: {{ $investment->investor->investor_type === 'MD' ? 'Managing Director' : 'Public Investor' }}
                    </p>
                </div>
                <div>
                    @php
                        $statusCol = $investment->status === 'active' ? 'success' : ($investment->status === 'repaid' ? 'info' : ($investment->status === 'termination_pending' ? 'warning' : 'secondary'));
                    @endphp
                    <span class="badge bg-{{ $statusCol }}" style="font-size: 11px; padding: 6px 14px; border-radius: 30px; font-weight: 800; border: 1px solid rgba(255,255,255,0.1); color:#fff">
                        {{ strtoupper($investment->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Agreement Terms -->
        <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 16px; padding: 24px;">
            <h3 style="font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><i class="bi bi-info-circle"></i> Key Investment Parameters</h3>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; font-size:13.5px;">
                <div style="display:flex; flex-direction:column; gap:12px; color:#475569">
                    <div style="display:flex; justify-content:space-between"><span>Principal Amount:</span><strong style="color:#1e293b">LSL {{ number_format($investment->principal, 2) }}</strong></div>
                    <div style="display:flex; justify-content:space-between"><span>Flat Interest Rate:</span><strong style="color:#1e293b">{{ number_format($investment->interest_rate * 100, 2) }}% / month</strong></div>
                    <div style="display:flex; justify-content:space-between"><span>Value Date:</span><strong>{{ $investment->investment_date->format('d M, Y') }}</strong></div>
                    <div style="display:flex; justify-content:space-between"><span>Maturity Date:</span><strong>{{ $investment->maturity_date->format('d M, Y') }}</strong></div>
                </div>
                <div style="display:flex; flex-direction:column; gap:12px; color:#475569; border-left:1px solid #f1f5f9; padding-left:20px">
                    <div style="display:flex; justify-content:space-between"><span>Monthly Return:</span><strong>LSL {{ number_format($investment->monthly_interest, 2) }}</strong></div>
                    <div style="display:flex; justify-content:space-between"><span>Total Return Months:</span><strong>{{ $investment->total_months }} months</strong></div>
                    <div style="display:flex; justify-content:space-between"><span>Total Gross Return:</span><strong style="color:#10b981">+LSL {{ number_format($investment->total_interest, 2) }}</strong></div>
                    <div style="display:flex; justify-content:space-between; background:#f8fafc; padding:4px 8px; border-radius:4px; font-weight:700"><span>Total Repayable:</span><span style="color:#4f46e5">LSL {{ number_format($investment->total_repayable, 2) }}</span></div>
                </div>
            </div>
        </div>

        <!-- Accrual Calendar -->
        <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-radius: 16px;">
            <div class="card-hdr" style="padding: 20px 24px; background: #fff; border-bottom: 1px solid #f1f5f9;">
                <span class="card-title" style="font-size: 15px; font-weight: 800; color: #1e293b;"><i class="bi bi-calendar3"></i> Monthly Interest Accrual Schedule</span>
            </div>
            <div style="overflow-x:auto">
                <table class="dt" style="width:100%">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: center; width:80px">Month</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Accrual Date</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Accrual Amount</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: center;">Accrual Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($investment->accruals as $index => $acc)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 14px 24px; text-align: center; font-weight: 800; color: #64748b;">
                                {{ $index + 1 }}
                            </td>
                            <td style="padding: 14px 24px; font-size: 13px; color: #1e293b; font-weight:700">
                                {{ $acc->accrual_date->format('d F, Y') }}
                            </td>
                            <td style="padding: 14px 24px; text-align: right; font-weight: 800; color: #475569; font-size: 13px;">
                                LSL {{ number_format($acc->interest, 2) }}
                            </td>
                            <td style="padding: 14px 24px; text-align: center;">
                                @if($acc->status === 'posted')
                                    <span class="badge" style="background: rgba(22, 163, 74, 0.1); color: #10b981; border: 1px solid rgba(22, 163, 74, 0.2); font-size:9.5px; padding: 4px 10px; border-radius: 30px;"><i class="bi bi-check-circle"></i> Posted ({{ $acc->posted_at->format('d M') }})</span>
                                @elseif($acc->status === 'forfeited')
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); font-size:9.5px; padding: 4px 10px; border-radius: 30px;"><i class="bi bi-x-circle"></i> Forfeited</span>
                                @else
                                    <span class="badge bg-light text-dark" style="font-size:9.5px; padding: 4px 10px; border-radius: 30px;"><i class="bi bi-clock"></i> Pending Accrual</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8; font-size: 13px;">
                                <i class="bi bi-exclamation-triangle" style="font-size: 28px; display: block; margin-bottom: 8px;"></i>
                                No interest schedule created. Deferral investment.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right: Operations & Exit Management Panel --}}
    <div>
        <div class="card" style="border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border-radius: 20px; display:flex; flex-direction:column; gap:20px; padding:24px;">
            
            <!-- Download Agreement -->
            <div>
                <h3 style="font-size: 13.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 0; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;"><i class="bi bi-file-earmark-word"></i> Legal Agreement</h3>
                <a href="{{ route('admin.investments.contract.download', $investment->id) }}" class="btn btn-primary" style="width:100%; padding:12px; font-weight:800; border-radius:8px; display:flex; justify-content:center; align-items:center; gap:8px;">
                    <i class="bi bi-download"></i> Download Agreement (.doc)
                </a>
            </div>

            <!-- Operations Section -->
            <div>
                <h3 style="font-size: 13.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 0; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;"><i class="bi bi-gear-fill"></i> Agreement Operations</h3>
                
                @if($investment->status === 'active')
                    {{-- Early Termination Notice Form --}}
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:15px;">
                        <h4 style="font-size:12px; font-weight:800; color:#1e293b; margin-top:0; margin-bottom:8px; text-transform:uppercase;"><i class="bi bi-door-open-fill" style="color:#f59e0b"></i> Early Exit Notice</h4>
                        <p style="font-size:11.5px; color:#64748b; margin:0 0 12px 0;">Trigger the standard 30-day early termination notice window. Subject to 10% fee on remaining forfeited interest.</p>
                        
                        <form action="{{ route('admin.investments.terminate', $investment->id) }}" method="POST">
                            @csrf
                            <div style="margin-bottom:12px">
                                <label style="display:block; font-size:10px; font-weight:800; color:#64748b; margin-bottom:4px">REQUEST VALUE DATE</label>
                                <input type="date" name="request_date" value="{{ date('Y-m-d') }}" style="width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12px;">
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm" style="width:100%; padding:10px; font-weight:800; color:#fff" onclick="return confirm('Are you sure you want to initiate the 30-day early termination notice? Accruals will continue to accrue if month-ends fall in the notice period.');">
                                <i class="bi bi-bell-fill"></i> Initiate 30-Day Notice
                            </button>
                        </form>
                    </div>
                    
                @elseif($investment->status === 'termination_pending')
                    {{-- Notice Countdown & Action Card --}}
                    <div style="background: rgba(245, 158, 11, 0.05); border:1px solid rgba(245, 158, 11, 0.2); border-radius:12px; padding:15px; display:flex; flex-direction:column; gap:12px">
                        <div style="text-align:center;">
                            <span class="badge bg-warning" style="font-size:10px; padding:4px 10px; border-radius:30px; color:#fff">NOTICE ACTIVE</span>
                            <div style="font-size:24px; font-weight:800; color:#b45309; margin-top:8px;">
                                @php
                                    $remDays = max(0, now()->diffInDays($investment->termination_notice_expiry, false));
                                @endphp
                                {{ $remDays }} Days Remaining
                            </div>
                            <div style="font-size:11px; color:#92400e; margin-top:4px;">Notice Expiry: {{ $investment->termination_notice_expiry->format('d M, Y') }}</div>
                        </div>

                        <!-- Payout Preview Trigger -->
                        <button type="button" class="btn btn-light btn-sm" id="btnPreviewPayout" style="width:100%; font-weight:800; border:1px solid #cbd5e1; font-size:12px;"><i class="bi bi-calculator"></i> Preview Payout Breakdown</button>

                        <div id="payoutPreviewBlock" style="display:none; background:#fff; border:1px dashed #cbd5e1; border-radius:8px; padding:10px; font-size:11.5px; color:#475569">
                            <div style="display:flex; justify-content:space-between; margin-bottom:5px"><span>Principal:</span><strong>LSL <span id="pPrincipal">0.00</span></strong></div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:5px"><span>Earned Interest (<span id="pPosted">0</span> mths):</span><strong>+LSL <span id="pEarned">0.00</span></strong></div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:5px; color:#ef4444"><span>Termination Fee (10% of <span id="pForfeited">0</span> mths):</span><strong>-LSL <span id="pFee">0.00</span></strong></div>
                            <div style="display:flex; justify-content:space-between; font-weight:700; border-top:1px solid #f1f5f9; padding-top:6px; margin-top:4px;"><span>Net Payout:</span><span style="color:#10b981">LSL <span id="pNet">0.00</span></span></div>
                        </div>

                        <!-- Approval forms -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px">
                            <form action="{{ route('admin.investments.terminate.approve', $investment->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm" style="width:100%; font-weight:800; color:#fff" onclick="return confirm('Approve termination and disburse final net payout? Liquidity gate check will verify cash balances.');">Approve</button>
                            </form>
                            <form action="{{ route('admin.investments.terminate.decline', $investment->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-light btn-sm" style="width:100%; font-weight:800; border:1px solid #cbd5e1;" onclick="return confirm('Cancel termination and return contract to active status?');">Decline</button>
                            </form>
                        </div>
                    </div>

                @elseif($investment->status === 'matured')
                    {{-- Maturity Repayment Card --}}
                    <div style="background: rgba(22, 163, 74, 0.05); border:1px solid rgba(22, 163, 74, 0.2); border-radius:12px; padding:15px; text-align:center">
                        <div style="color:#065f46; font-weight:800; font-size:13px; margin-bottom:6px"><i class="bi bi-award-fill"></i> CONTRACT MATURED</div>
                        <p style="font-size:11.5px; color:#047857; margin:0 0 12px 0;">This contract matured on {{ $investment->maturity_date->format('d M, Y') }}. Full payout of principal + posted interest is ready.</p>
                        
                        <form action="{{ route('admin.investments.repay', $investment->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success" style="width:100%; font-weight:800; color:#fff; background:#10b981; border:none;">
                                <i class="bi bi-wallet2"></i> Process Maturity Repayment
                            </button>
                        </form>
                    </div>

                @elseif($investment->status === 'terminated')
                    {{-- Terminated Audit Block --}}
                    <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:12px; padding:15px; font-size:12px; color:#475569">
                        <div style="font-weight:800; color:#1e293b; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:5px; text-transform:uppercase;"><i class="bi bi-info-circle-fill"></i> Termination Audit</div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Exit Date:</span><strong>{{ $investment->terminated_at->format('d M Y H:i') }}</strong></div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Termination Fee:</span><strong style="color:#ef4444">-LSL {{ number_format($investment->termination_fee, 2) }}</strong></div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Net Payout:</span><strong style="color:#10b981">LSL {{ number_format($investment->net_payout, 2) }}</strong></div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Approved By:</span><strong>{{ $investment->approvedBy->name ?? 'System' }}</strong></div>
                    </div>

                @elseif($investment->status === 'repaid')
                    {{-- Repaid Audit Block --}}
                    <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:12px; padding:15px; font-size:12px; color:#475569">
                        <div style="font-weight:800; color:#1e293b; margin-bottom:10px; border-bottom:1px solid #e2e8f0; padding-bottom:5px; text-transform:uppercase;"><i class="bi bi-check-all"></i> Repayment Audit</div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Repaid Date:</span><strong>{{ $investment->repaid_at->format('d M Y H:i') }}</strong></div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px"><span>Repayment Amount:</span><strong style="color:#1e3a8a">LSL {{ number_format($investment->total_repayable, 2) }}</strong></div>
                    </div>
                @endif
            </div>

            <!-- Treasury Sync Account info -->
            <div style="border-top:1px solid #f1f5f9; padding-top:15px">
                <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Funding Destination Account</div>
                <div style="display:flex; align-items:center; gap:10px">
                    <div style="width:36px; height:36px; border-radius:8px; background:#f1f5f9; display:flex; justify-content:center; align-items:center; font-size:16px; color:#4f46e5"><i class="bi bi-piggy-bank"></i></div>
                    <div>
                        <div style="font-size:12.5px; font-weight:800; color:#1e293b">{{ $investment->treasuryAccount->name ?? 'None Linked' }}</div>
                        <div style="font-size:10.5px; color:#94a3b8">{{ $investment->treasuryAccount->institution ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnPreview = document.getElementById('btnPreviewPayout');
    const previewBlock = document.getElementById('payoutPreviewBlock');
    
    if (btnPreview) {
        btnPreview.addEventListener('click', function() {
            if (previewBlock.style.display === 'none') {
                btnPreview.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading Preview...';
                
                fetch('{{ route("admin.investments.terminate.preview", $investment->id) }}')
                    .then(res => res.json())
                    .then(data => {
                        if (data.err) {
                            alert(data.err);
                            btnPreview.innerHTML = '<i class="bi bi-calculator"></i> Preview Payout Breakdown';
                        } else {
                            document.getElementById('pPrincipal').innerText = data.principal;
                            document.getElementById('pEarned').innerText = data.earned_interest;
                            document.getElementById('pFee').innerText = data.termination_fee;
                            document.getElementById('pNet').innerText = data.net_payout;
                            
                            document.getElementById('pPosted').innerText = data.posted_months;
                            document.getElementById('pForfeited').innerText = data.forfeited_months;
                            
                            previewBlock.style.display = 'block';
                            btnPreview.innerHTML = '<i class="bi bi-chevron-up"></i> Hide Payout Breakdown';
                        }
                    })
                    .catch(e => {
                        alert('Could not fetch payout preview.');
                        btnPreview.innerHTML = '<i class="bi bi-calculator"></i> Preview Payout Breakdown';
                    });
            } else {
                previewBlock.style.display = 'none';
                btnPreview.innerHTML = '<i class="bi bi-calculator"></i> Preview Payout Breakdown';
            }
        });
    }
});
</script>
@endsection
