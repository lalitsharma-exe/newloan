@extends('borrower.layouts.app')
@section('title','MyBill — Confirm')
@section('content')

@php
  $tierData = $tier === '30' ? $quote['standard'] : $quote['no_upfront'];
  $icons = ['electricity'=>'lightning-charge-fill','airtime'=>'phone-fill','insurance'=>'shield-fill','ticket'=>'ticket-perforated-fill'];
  $colors = ['electricity'=>'#f59e0b','airtime'=>'#0ea5e9','insurance'=>'#8b5cf6','ticket'=>'#ec4899'];
  $tints = ['electricity'=>'rgba(245,158,11,0.04)','airtime'=>'rgba(14,165,233,0.04)','insurance'=>'rgba(139,92,246,0.04)','ticket'=>'rgba(236,72,153,0.04)'];
  $selectedColor = $colors[$category];
  $selectedTint = $tints[$category];
@endphp

<div style="max-width:540px; margin:0 auto">
    
    {{-- Header --}}
    <div style="text-align:center; margin-bottom:32px">
        <div class="confirm-icon-wrap" style="background: {{ $selectedColor }}15; color: {{ $selectedColor }}">
            <i class="bi bi-{{ $icons[$category] }}"></i>
        </div>
        <h2 style="font-size:26px; font-weight:900; color:#0f172a; margin:0; letter-spacing:-1px">Confirm Payment</h2>
        <div style="font-size:14px; color:#64748b; margin-top:4px">Review your {{ ucfirst($category) }} purchase summary</div>
    </div>

    {{-- Summary Card (Light Glass) --}}
    <div class="light-glass-card" style="background: {{ $selectedTint }}; border-color: {{ $selectedColor }}20; padding:0; overflow:hidden">
        <div style="padding:32px; border-bottom:1px solid rgba(0,0,0,0.05); text-align:center">
            <div style="font-size:12px; color:#94a3b8; text-transform:uppercase; letter-spacing:1.5px; font-weight:800; margin-bottom:6px">Purchase Amount</div>
            <div style="font-size:42px; font-weight:900; color:#0f172a; letter-spacing:-1.5px">M {{ number_format($quote['bill_value'], 2) }}</div>
        </div>

        <div style="padding:24px; background:rgba(255,255,255,0.4)">
            @if(!empty($data['meter_number']))
            <div class="info-row"><span>Meter Number</span><strong>{{ $data['meter_number'] }}</strong></div>
            @endif
            @if(!empty($data['phone_number']))
            <div class="info-row"><span>Recipient</span><strong>{{ $data['phone_number'] }}</strong></div>
            @endif
        </div>

        {{-- Breakdown --}}
        <div style="padding:32px">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px">
                <span class="plan-badge" style="background: {{ $selectedColor }}15; color: {{ $selectedColor }}">
                    {{ $tier === '30' ? 'STANDARD — 30% UPFRONT' : 'NO UPFRONT — 40% FEE' }}
                </span>
            </div>

            @foreach([
                'Bill Value'         => 'M ' . number_format($quote['bill_value'], 2),
                'Service Fee (' . $tierData['fee_percent'] . '%)' => 'M ' . number_format($tierData['total_cost'], 2),
                'Upfront Payment'    => 'M ' . number_format($tierData['upfront'], 2),
            ] as $lbl => $val)
            <div class="fee-row"><span>{{ $lbl }}</span><strong>{{ $val }}</strong></div>
            @endforeach

            <div style="display:flex; justify-content:space-between; margin-top:20px; padding-top:20px; border-top:1px solid rgba(0,0,0,0.05)">
                <span style="font-weight:800; color:#0f172a">Payday Deduction</span>
                <span style="font-weight:900; color:#ef4444; font-size:20px">M {{ number_format($tierData['payday_amount'], 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Alert --}}
    <div class="glass-warning">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>
            <strong>Important:</strong> <strong>M {{ number_format($tierData['payday_amount'], 2) }}</strong> will be deducted from your account on your next salary date.
        </div>
    </div>

    {{-- Actions --}}
    <form method="POST" action="{{ route('borrower.mybill.store') }}">
        @csrf
        <input type="hidden" name="bill_value" value="{{ $quote['bill_value'] }}">
        <input type="hidden" name="bill_category" value="{{ $category }}">
        <input type="hidden" name="tier" value="{{ $tier }}">
        @foreach(['meter_number','phone_number','airtime_type','policy_number','insurance_partner_id','event_id','ticket_id'] as $f)
            @if(!empty($data[$f]))<input type="hidden" name="{{ $f }}" value="{{ $data[$f] }}">@endif
        @endforeach

        <button type="submit" class="glass-submit-btn" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 10px 30px rgba(16,185,129,0.25)" onclick="this.disabled=true; this.innerHTML='<i class=\'bi bi-hourglass-split\'></i> Processing...'; this.closest('form').submit()">
            <i class="bi bi-check-circle-fill"></i> Confirm & Pay
        </button>
        <a href="{{ route('borrower.mybill.purchase', $category) }}" class="back-text">Change Details</a>
    </form>
</div>

<style>
.light-glass-card {
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(0,0,0,0.05); border-radius: 32px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.04); margin-bottom: 24px;
}
.confirm-icon-wrap {
    width: 68px; height: 68px; border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; font-size: 32px;
}
.info-row, .fee-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 14px; }
.info-row span, .fee-row span { color: #64748b; font-weight: 500; }
.info-row strong, .fee-row strong { color: #1e293b; font-weight: 800; }

.plan-badge { font-size: 11px; font-weight: 900; padding: 4px 12px; border-radius: 8px; letter-spacing: 1px; }

.glass-warning {
    background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2);
    border-radius: 18px; padding: 18px; display: flex; gap: 14px; margin-bottom: 32px;
    color: #92400e; font-size: 13px; line-height: 1.5;
}
.glass-warning i { font-size: 20px; }

.glass-submit-btn {
    width: 100%; padding: 18px; border: none; border-radius: 20px;
    color: #fff; font-size: 16px; font-weight: 900; cursor: pointer; transition: all 0.3s;
    display: flex; align-items: center; justify-content: center; gap: 10px;
}
.glass-submit-btn:hover { transform: translateY(-2px); }

.back-text {
    display: block; text-align: center; margin-top: 16px; color: #94a3b8;
    text-decoration: none; font-size: 14px; font-weight: 700; transition: color 0.3s;
}
.back-text:hover { color: #0f172a; }
</style>

@endsection
