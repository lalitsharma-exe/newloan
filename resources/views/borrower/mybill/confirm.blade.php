@extends('borrower.layouts.app')
@section('title','MyBill — Confirm Purchase')
@section('content')

@php
  $tierData = $tier === '30' ? $quote['standard'] : $quote['no_upfront'];
  $icons = ['electricity'=>'lightning-charge-fill','airtime'=>'phone-fill','insurance'=>'shield-fill','ticket'=>'ticket-perforated-fill'];
  $colors = ['electricity'=>'#f59e0b','airtime'=>'#06b6d4','insurance'=>'#8b5cf6','ticket'=>'#ec4899'];
@endphp

<div style="max-width:520px;margin:0 auto">

  {{-- Header --}}
  <div style="text-align:center;margin-bottom:28px">
    <div style="width:64px;height:64px;border-radius:16px;background:{{ $colors[$category] }}15;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px">
      <i class="bi bi-{{ $icons[$category] }}" style="color:{{ $colors[$category] }}"></i>
    </div>
    <h2 style="font-size:22px;font-weight:800;margin:0 0 4px">Confirm Purchase</h2>
    <div style="font-size:13px;color:var(--muted)">Review your {{ ucfirst($category) }} bill payment details</div>
  </div>

  {{-- Summary Card --}}
  <div style="background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px">
    <div style="padding:20px 22px;border-bottom:1px solid var(--border)">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
        <span style="font-size:13px;color:var(--muted)">Bill Amount</span>
        <span style="font-size:24px;font-weight:800">M {{ number_format($quote['bill_value'], 2) }}</span>
      </div>

      @if(!empty($data['meter_number']))
      <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:13px;border-top:1px solid #f1f5f9">
        <span style="color:var(--muted)">Meter Number</span>
        <span style="font-weight:600;font-family:monospace">{{ $data['meter_number'] }}</span>
      </div>
      @endif

      @if(!empty($data['phone_number']))
      <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:13px;border-top:1px solid #f1f5f9">
        <span style="color:var(--muted)">Recipient Phone</span>
        <span style="font-weight:600">{{ $data['phone_number'] }}</span>
      </div>
      @endif

      @if(!empty($data['policy_number']))
      <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:13px;border-top:1px solid #f1f5f9">
        <span style="color:var(--muted)">Policy Number</span>
        <span style="font-weight:600">{{ $data['policy_number'] }}</span>
      </div>
      @endif
    </div>

    {{-- Fee Breakdown --}}
    <div style="padding:20px 22px;background:#f8fafc">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
        <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;background:{{ $tier === '30' ? 'rgba(43,75,173,.1)' : '#f3f4f6' }};color:{{ $tier === '30' ? 'var(--blue)' : '#6b7280' }}">
          {{ $tier === '30' ? 'STANDARD — 30%' : 'NO UPFRONT — 40%' }}
        </span>
      </div>

      @foreach([
        'Bill Value'         => 'M ' . number_format($quote['bill_value'], 2),
        'Fee (' . $tierData['fee_percent'] . '%)' => 'M ' . number_format($tierData['total_cost'], 2),
        'Upfront Payment'    => 'M ' . number_format($tierData['upfront'], 2),
        'Disbursed to Provider' => 'M ' . number_format($tierData['disbursed'], 2),
      ] as $label => $val)
      <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px">
        <span style="color:var(--muted)">{{ $label }}</span>
        <span style="font-weight:600">{{ $val }}</span>
      </div>
      @endforeach

      <div style="display:flex;justify-content:space-between;padding:10px 0;font-size:15px;border-top:2px solid var(--border);margin-top:8px">
        <span style="font-weight:700;color:var(--ink)">Payday Deduction</span>
        <span style="font-weight:800;color:var(--err)">M {{ number_format($tierData['payday_amount'], 2) }}</span>
      </div>
    </div>
  </div>

  {{-- Warning --}}
  <div style="background:#fffbeb;border:1px solid rgba(245,158,11,.2);border-radius:12px;padding:14px 16px;margin-bottom:20px;font-size:12.5px;color:#92400e;display:flex;gap:10px;align-items:flex-start">
    <i class="bi bi-exclamation-triangle-fill" style="font-size:16px;flex-shrink:0;margin-top:1px"></i>
    <div>
      <strong>Important:</strong> The full payday amount of <strong>M {{ number_format($tierData['payday_amount'], 2) }}</strong> will be deducted from your account on your next salary day. By confirming, you agree to this deduction.
    </div>
  </div>

  {{-- Actions --}}
  <form method="POST" action="{{ route('borrower.mybill.store') }}" id="confirmForm">
    @csrf
    <input type="hidden" name="bill_value" value="{{ $quote['bill_value'] }}">
    <input type="hidden" name="bill_category" value="{{ $category }}">
    <input type="hidden" name="tier" value="{{ $tier }}">
    @foreach(['meter_number','phone_number','airtime_type','policy_number','insurance_partner_id','event_id','ticket_id'] as $f)
      @if(!empty($data[$f]))<input type="hidden" name="{{ $f }}" value="{{ $data[$f] }}">@endif
    @endforeach

    <button type="submit" id="confirmBtn"
            style="width:100%;padding:15px;border:none;border-radius:12px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:16px;font-weight:700;cursor:pointer;font-family:inherit;margin-bottom:12px;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .3s"
            onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(16,185,129,.3)'"
            onmouseout="this.style.transform='';this.style.boxShadow=''"
            onclick="this.disabled=true;this.innerHTML='<i class=\'bi bi-hourglass-split\'></i> Processing...';this.closest('form').submit()">
      <i class="bi bi-check-circle-fill"></i> Confirm & Pay
    </button>

    <a href="{{ route('borrower.mybill.purchase', $category) }}"
       style="display:block;text-align:center;padding:12px;border:1px solid var(--border);border-radius:12px;color:var(--muted);text-decoration:none;font-size:14px;font-weight:500">
      <i class="bi bi-arrow-left"></i> Go Back
    </a>
  </form>
</div>

@endsection
