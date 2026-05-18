@extends('admin.layouts.app')
@section('title', 'Investor Profile: ' . $investor->full_name)
@section('page-title', 'Investor Management')

@section('content')
<div style="display:grid; grid-template-columns: 1fr 420px; gap:28px; align-items: start;">
    
    {{-- Left: Investor Profile & Investments --}}
    <div style="display:flex; flex-direction:column; gap:24px">
        
        <!-- Header Profile Card -->
        <div class="card" style="border: none; box-shadow: 0 4px 25px rgba(0,0,0,0.05); border-radius: 20px; padding: 28px; background: #fff; display: flex; gap: 24px; align-items: center; position: relative;">
            <div style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; display: flex; justify-content: center; align-items: center; font-size: 32px; font-weight: 800; box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);">
                {{ substr($investor->full_name, 0, 1) }}
            </div>
            <div style="flex: 1;">
                <div style="display:flex; align-items:center; gap:12px">
                    <h2 style="font-size: 22px; font-weight: 800; color: #1e293b; margin: 0;">{{ $investor->full_name }}</h2>
                    @if($investor->investor_type === 'MD')
                        <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.2); font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 30px;">Managing Director</span>
                    @else
                        <span class="badge" style="background: rgba(14, 116, 144, 0.1); color: #0e7490; border: 1px solid rgba(14, 116, 144, 0.2); font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 30px;">Public Investor</span>
                    @endif
                </div>
                <div style="font-size: 12.5px; color: #64748b; margin-top: 6px; display: flex; gap: 18px;">
                    <span><i class="bi bi-card-text"></i> ID Number: <strong>{{ $investor->id_number }}</strong></span>
                    <span><i class="bi bi-calendar2-event"></i> Joined: <strong>{{ $investor->created_at->format('d M, Y') }}</strong></span>
                </div>
            </div>
            
            <div style="text-align: right;">
                <span class="badge bg-{{ $investor->status === 'active' ? 'success' : 'danger' }}" style="font-size: 11px; padding: 5px 12px; border-radius: 30px; font-weight: 700; color:#fff">
                    {{ strtoupper($investor->status) }}
                </span>
            </div>
        </div>

        <!-- Details Grid -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px">
            <!-- Contact Card -->
            <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 16px; padding: 20px 24px;">
                <h3 style="font-size: 13.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><i class="bi bi-telephone"></i> Contact Details</h3>
                <div style="display:flex; flex-direction:column; gap:10px; font-size:13px; color:#475569">
                    <div style="display:flex; justify-content:space-between"><span>Phone:</span><strong>{{ $investor->phone ?: 'N/A' }}</strong></div>
                    <div style="display:flex; justify-content:space-between"><span>Email:</span><strong>{{ $investor->email ?: 'N/A' }}</strong></div>
                    <div style="display:flex; justify-content:space-between; flex-direction:column; gap:4px">
                        <span>Physical Address:</span>
                        <strong style="background:#f8fafc; padding:8px; border-radius:6px; font-weight:normal; border:1px solid #f1f5f9">{{ $investor->address ?: 'N/A' }}</strong>
                    </div>
                </div>
            </div>
            
            <!-- Banking Card -->
            <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 16px; padding: 20px 24px;">
                <h3 style="font-size: 13.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;"><i class="bi bi-bank"></i> Settlement Account</h3>
                <div style="display:flex; flex-direction:column; gap:10px; font-size:13px; color:#475569">
                    <div style="display:flex; justify-content:space-between"><span>Bank Name:</span><strong>{{ $investor->bank_name ?: 'N/A' }}</strong></div>
                    <div style="display:flex; justify-content:space-between"><span>Account Number:</span><strong>{{ $investor->account_number ?: 'N/A' }}</strong></div>
                    <div style="display:flex; justify-content:space-between"><span>Platform User:</span><strong>{{ $investor->user->name }}</strong></div>
                </div>
            </div>
        </div>

        <!-- Investments Ledger -->
        <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-radius: 16px;">
            <div class="card-hdr" style="padding: 20px 24px; background: #fff; border-bottom: 1px solid #f1f5f9;">
                <span class="card-title" style="font-size: 15px; font-weight: 800; color: #1e293b;">Tranches Ledgers</span>
            </div>
            <div style="overflow-x:auto">
                <table class="dt" style="width:100%">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Reference</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Principal</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Return rate</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Total Return</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Maturity</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Status</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($investor->investments as $inv)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 16px 24px;">
                                <a href="{{ route('admin.investments.show', $inv->id) }}" style="font-weight: 800; color: #1e3a8a; font-size: 13.5px; text-decoration: none;">{{ $inv->contract_ref }}</a>
                                <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">Date: {{ $inv->investment_date->format('d M, Y') }}</div>
                            </td>
                            <td style="padding: 16px 24px; text-align: right; font-weight: 800; color: #1e293b; font-size: 13.5px;">
                                LSL {{ number_format($inv->principal, 2) }}
                            </td>
                            <td style="padding: 16px 24px; text-align: right; font-size: 13px; color: #475569;">
                                {{ number_format($inv->interest_rate * 100, 1) }}%
                            </td>
                            <td style="padding: 16px 24px; text-align: right; font-weight: 700; color: #10b981; font-size: 13px;">
                                +LSL {{ number_format($inv->total_interest, 2) }}
                            </td>
                            <td style="padding: 16px 24px; font-size: 12.5px; color: #475569;">
                                {{ $inv->maturity_date->format('d M, Y') }}
                            </td>
                            <td style="padding: 16px 24px;">
                                @php
                                    $col = $inv->status === 'active' ? 'success' : ($inv->status === 'repaid' ? 'info' : ($inv->status === 'termination_pending' ? 'warning' : 'secondary'));
                                @endphp
                                <span class="badge bg-{{ $col }}" style="font-size: 9px; padding: 4px 8px; border-radius: 30px; color: #fff;">{{ strtoupper($inv->status) }}</span>
                            </td>
                            <td style="padding: 16px 24px; text-align: right;">
                                <a href="{{ route('admin.investments.show', $inv->id) }}" class="btn btn-sm btn-light" style="font-weight:700;"><i class="bi bi-eye"></i> View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8; font-size: 13.5px;">
                                <i class="bi bi-wallet2" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                                This partner hasn't contributed any investment tranches yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right: Record Investment Form --}}
    <div>
        <div class="card" style="border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border-radius: 20px; position: sticky; top: 24px;">
            <div class="card-hdr" style="padding: 24px; background: #fff; border-bottom: 1px solid #f1f5f9;">
                <h3 style="font-size: 16px; font-weight: 800; color: #1e293b; margin: 0;"><i class="bi bi-plus-square-fill" style="color: #10b981;"></i> Issue New Tranche</h3>
                <p style="font-size: 12px; color: #94a3b8; margin: 4px 0 0 0;">Create a new capital agreement for this investor. Principal is injected directly into a funding pool.</p>
            </div>
            <div style="padding: 24px;">
                <form action="{{ route('admin.investments.store') }}" method="POST" id="newInvestmentForm" style="display:flex; flex-direction:column; gap:18px">
                    @csrf
                    
                    <input type="hidden" name="investor_id" value="{{ $investor->id }}">

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Investment Partner</label>
                        <input type="text" value="{{ $investor->full_name }}" disabled style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; background-color:#f1f5f9; color:#475569; font-weight:700">
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Funding Destination Pool</label>
                        <select name="treasury_account_id" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; background-color:#fff;">
                            <option value="">-- Choose Treasury Account --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->name }} (Balance: LSL {{ number_format($acc->balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Principal Amount (LSL)</label>
                        <input type="number" step="0.01" name="principal" placeholder="0.00" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; font-weight:700">
                    </div>

                    <div>
                        <label style="display:block; font-size:11px; font-weight:800; text-transform:uppercase; color:#64748b; margin-bottom:6px">Investment Value Date</label>
                        <input type="date" name="investment_date" value="{{ date('Y-m-d') }}" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px;">
                    </div>

                    <div style="background-color: #f8fafc; padding: 12px; border-radius: 8px; border:1px dashed #e2e8f0; font-size: 11.5px; color:#475569">
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px">
                            <span>Classification Rate:</span>
                            <strong>{{ $investor->investor_type === 'MD' ? '5.0%' : '1.5%' }} flat / month</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span>Agreement Maturity:</span>
                            <strong style="color: #4f46e5;">31 December {{ date('Y') }}</strong>
                        </div>
                    </div>

                    <div id="decemberWarningBlock" style="display:none; background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); padding: 12px; border-radius: 8px; font-size: 12px; color: #d97706;">
                        <input type="checkbox" name="confirm_december" id="confirmDecember" value="1" style="margin-right:8px; vertical-align: middle;">
                        <label for="confirmDecember" style="vertical-align: middle; font-weight: 700;">I confirm December Investment Deferral Check</label>
                        <div style="margin-top:6px; font-size:11px;">December investments earn 0 months of interest because the interest start is January. Select to manually overwrite and defer to January.</div>
                    </div>

                    <button type="submit" class="btn btn-success" style="width:100%; padding:12px; font-weight:800; border-radius:8px; margin-top:10px; background-color:#10b981; border:none; color:white">
                        <i class="bi bi-shield-lock-fill"></i> Execute Agreement
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('input[name="investment_date"]');
    const warningBlock = document.getElementById('decemberWarningBlock');
    
    function checkMonth() {
        if (dateInput.value) {
            const date = new Date(dateInput.value);
            if (date.getMonth() === 11) { // 11 is December (0-indexed)
                warningBlock.style.display = 'block';
            } else {
                warningBlock.style.display = 'none';
            }
        }
    }
    
    dateInput.addEventListener('change', checkMonth);
    checkMonth();
});
</script>
@endsection
