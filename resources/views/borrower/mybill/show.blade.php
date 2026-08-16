@extends('borrower.layouts.app')
@section('title','MyBill — ' . $loan->loan_number)
@section('content')

@php
  $icons = ['electricity'=>'lightning-charge-fill','airtime'=>'phone-fill','insurance'=>'shield-fill','ticket'=>'ticket-perforated-fill'];
  $colors = ['electricity'=>'#f59e0b', 'airtime'=>'#0ea5e9', 'insurance'=>'#8b5cf6', 'ticket'=>'#ec4899'];
  $selectedColor = $colors[$loan->bill_category] ?? '#3b82f6';
@endphp

<div style="max-width:560px; margin:0 auto">
    
    {{-- Back link --}}
    <a href="{{ route('borrower.mybill.history') }}" class="glass-back-link">
        <i class="bi bi-arrow-left"></i> <span>Back to History</span>
    </a>

    @if(session('success'))
        <div class="glass-alert-light a-ok mb-4"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif

    {{-- Status Header Card --}}
    <div class="light-glass-card" style="padding:40px; margin-bottom:24px; text-align:center">
        <div class="status-icon-wrap" style="color: {{ $loan->status === 'settled' ? '#10b981' : ($loan->status === 'active' ? '#f59e0b' : '#f87171') }}; background: rgba(0,0,0,0.03)">
            <i class="bi bi-{{ $loan->status === 'settled' ? 'shield-check' : ($loan->status === 'active' ? 'hourglass-split' : 'exclamation-circle') }}"></i>
        </div>
        <div style="font-size:24px; font-weight:900; color:#0f172a; letter-spacing:-0.5px">
            {{ $loan->status === 'settled' ? 'Fully Settled' : ($loan->status === 'active' ? 'Payment Pending' : ucfirst($loan->status)) }}
        </div>
        <div style="font-size:13px; color:#94a3b8; margin-top:8px; font-weight:700; text-transform:uppercase; letter-spacing:1px">
            #{{ $loan->loan_number }} · {{ $loan->created_at->format('d M Y, H:i') }}
        </div>
        
        <div style="margin-top:30px; padding-top:30px; border-top:1px solid rgba(0,0,0,0.05); display:flex; justify-content:center">
            @if($loan->bill_category === 'electricity')
                <img src="/assets/logos/lec_logo.png" style="height:28px">
            @elseif($loan->bill_category === 'airtime')
                <div style="display:flex; gap:16px">
                    <img src="/assets/logos/vodacom.png" style="height:18px">
                    <img src="/assets/logos/econet.png" style="height:18px">
                </div>
            @else
                <i class="bi bi-{{ $loan->category_icon }}" style="font-size:32px; color:{{ $selectedColor }}"></i>
            @endif
        </div>
    </div>

    {{-- Transaction Details --}}
    <div class="light-glass-card" style="padding:0; overflow:hidden; margin-bottom:24px">
        <div style="padding:20px 24px; border-bottom:1px solid rgba(0,0,0,0.05); display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.4)">
            <span style="font-weight:900; font-size:14px; color:#0f172a; text-transform:uppercase; letter-spacing:1px">{{ ucfirst($loan->bill_category) }} Receipt</span>
            <span style="font-size:11px; font-weight:900; color:#64748b; text-transform:uppercase">
                {{ $loan->tier === '30' ? 'Standard' : 'No-Upfront' }}
            </span>
        </div>

        <div style="padding:24px">
            @foreach([
                'Bill Amount'      => 'M ' . number_format($loan->bill_value, 2),
                'Upfront Paid'     => 'M ' . number_format($loan->upfront_amount, 2),
                'Total Due'        => 'M ' . number_format($loan->payday_amount, 2),
                'Already Settled'  => 'M ' . number_format($loan->settled_amount, 2),
                'Remaining'        => 'M ' . number_format($loan->outstanding_amount, 2),
            ] as $label => $val)
            <div class="receipt-row">
                <span>{{ $label }}</span>
                <strong style="color: {{ $label === 'Remaining' && $loan->outstanding_amount > 0 ? '#f59e0b' : '#0f172a' }}">{{ $val }}</strong>
            </div>
            @endforeach
        </div>

        {{-- Provider Info --}}
        @if($loan->meter_number || $loan->phone_number)
        <div style="padding:24px; background:rgba(0,0,0,0.02); border-top:1px solid rgba(0,0,0,0.05)">
            @if($loan->meter_number)
            <div class="receipt-row"><span>Meter Number</span><strong>{{ $loan->meter_number }}</strong></div>
            @endif
            @if($loan->phone_number)
            <div class="receipt-row"><span>Recipient</span><strong>{{ $loan->phone_number }}</strong></div>
            @endif

            {{-- Token Display --}}
            @if($loan->bill_category === 'electricity' && $loan->provider_response)
                @php
                $resp = json_decode($loan->provider_response, true);
                $token = $resp['data']['additionalData']['token'] ?? $resp['additional']['token'] ?? null;
                @endphp
                @if($token)
                <div class="token-box">
                    <div style="font-size:11px; font-weight:900; color:#92400e; margin-bottom:10px; letter-spacing:1px">ELECTRICITY TOKEN</div>
                    <div style="font-size:26px; font-weight:900; letter-spacing:4px; color:#0f172a; font-family:monospace">{{ $token }}</div>
                </div>
                @endif
            @endif
        </div>
        @endif
    </div>

    {{-- Payment History --}}
    @if($loan->repayments->isNotEmpty())
    <div class="light-glass-card" style="padding:0; overflow:hidden">
        <div style="padding:20px 24px; border-bottom:1px solid rgba(0,0,0,0.05); font-weight:900; font-size:14px; color:#0f172a; text-transform:uppercase; letter-spacing:1px">
            Payment History
        </div>
        @foreach($loan->repayments as $rep)
        <div style="display:flex; align-items:center; padding:18px 24px; border-bottom:1px solid rgba(0,0,0,0.02)">
            <div class="rep-icon" style="background: rgba(0,0,0,0.03); color: #64748b">
                <i class="bi bi-{{ $rep->deduction_type === 'upfront' ? 'credit-card-2-front' : 'calendar-check' }}"></i>
            </div>
            <div style="flex:1">
                <div style="font-weight:800; color:#1e293b; font-size:14px">{{ ucfirst($rep->deduction_type) }} Payment</div>
                <div style="font-size:12px; color:#94a3b8; font-weight:600">{{ $rep->processed_at?->format('d M Y, H:i') ?? $rep->created_at->format('d M Y, H:i') }}</div>
            </div>
            <div style="text-align:right">
                <div style="font-weight:900; color:#0f172a; font-size:16px">M {{ number_format($rep->amount, 2) }}</div>
                <div style="font-size:10px; font-weight:900; color:#10b981; text-transform:uppercase">SUCCESS</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<style>
.light-glass-card {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(0,0,0,0.05); border-radius: 32px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.04);
}
.glass-back-link {
    display: inline-flex; align-items: center; gap: 8px;
    color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 700;
    margin-bottom: 24px; transition: all 0.3s;
}
.glass-back-link:hover { color: #0f172a; transform: translateX(-4px); }

.status-icon-wrap {
    width: 72px; height: 72px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px; font-size: 32px;
}
.receipt-row { display: flex; justify-content: space-between; padding: 12px 0; font-size: 14px; }
.receipt-row span { color: #64748b; font-weight: 600; }
.receipt-row strong { color: #0f172a; font-weight: 800; }

.token-box {
    margin-top: 20px; background: rgba(245, 158, 11, 0.05);
    border: 2px dashed rgba(245, 158, 11, 0.3); border-radius: 20px;
    padding: 24px; text-align: center;
}

.rep-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; margin-right: 16px; font-size: 20px;
}
.glass-alert-light {
    padding: 16px 20px; border-radius: 18px; font-size: 14px; font-weight: 800;
    backdrop-filter: blur(10px); display: flex; align-items: center; gap: 12px;
    background: rgba(22, 163, 74, 0.08); color: #059669; border: 1px solid rgba(22, 163, 74, 0.2);
}
</style>

@endsection
