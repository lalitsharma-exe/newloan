@extends('borrower.layouts.app')
@section('title','MyBill — ' . $loan->loan_number)
@section('content')

@if(session('success'))<div class="alert a-ok mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif

<div style="max-width:560px;margin:0 auto">
  {{-- Back link --}}
  <a href="{{ route('borrower.mybill.index') }}" style="display:inline-flex;align-items:center;gap:8px;color:#64748b;text-decoration:none;font-size:14px;font-weight:600;margin-bottom:24px;transition:color .2s" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">
    <i class="bi bi-arrow-left"></i> Back to MyBill
  </a>

  {{-- Status Header --}}
  <div style="background:#fff;border:1px solid #f1f5f9;border-radius:24px;padding:32px;margin-bottom:24px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,0.02)">
    <div style="width:64px;height:64px;border-radius:50%;background:#f8fafc;border:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:{{ $loan->status === 'settled' ? '#10b981' : ($loan->status === 'active' ? '#f59e0b' : '#ef4444') }}">
      <i class="bi bi-{{ $loan->status === 'settled' ? 'shield-check' : ($loan->status === 'active' ? 'hourglass-split' : 'exclamation-circle') }}"></i>
    </div>
    <div style="font-size:20px;font-weight:900;color:#0f172a">
      {{ $loan->status === 'settled' ? 'Fully Settled' : ($loan->status === 'active' ? 'Payment Pending' : ucfirst($loan->status)) }}
    </div>
    <div style="font-size:13px;color:#64748b;margin-top:4px">
      {{ $loan->loan_number }} · {{ $loan->created_at->format('d M Y, H:i') }}
    </div>
    
    @if($loan->bill_category === 'electricity')
      <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f1f5f9;display:flex;justify-content:center">
        <img src="/assets/logos/lec_logo.png" style="height:28px">
      </div>
    @elseif($loan->bill_category === 'airtime')
      <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f1f5f9;display:flex;justify-content:center;gap:16px">
        <img src="/assets/logos/vodacom.png" style="height:18px">
        <img src="/assets/logos/econet.png" style="height:18px">
      </div>
    @elseif($loan->bill_category === 'insurance')
      <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f1f5f9;display:flex;justify-content:center">
        <i class="bi bi-shield-fill-check" style="color:#8b5cf6;font-size:28px"></i>
      </div>
    @elseif($loan->bill_category === 'ticket')
      <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f1f5f9;display:flex;justify-content:center">
        <i class="bi bi-ticket-perforated-fill" style="color:#ec4899;font-size:28px"></i>
      </div>
    @endif
  </div>

  {{-- Transaction Details --}}
  <div style="background:#fff;border:1px solid #f1f5f9;border-radius:24px;overflow:hidden;margin-bottom:24px;box-shadow:0 4px 20px rgba(0,0,0,0.02)">
    <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between">
      <span style="font-weight:800;font-size:16px;color:#0f172a">{{ ucfirst($loan->bill_category) }} Purchase</span>
      <span style="font-size:10px;padding:4px 10px;border-radius:100px;font-weight:700;background:#f8fafc;border:1px solid #f1f5f9;color:#64748b;text-transform:uppercase;letter-spacing:0.5px">
        {{ $loan->tier === '30' ? 'Standard Tier' : 'No-Upfront Tier' }}
      </span>
    </div>

    <div style="padding:16px 20px">
      @foreach([
        'Bill Amount'         => 'M ' . number_format($loan->bill_value, 2),
        'Fee Charged'         => 'M ' . number_format($loan->payday_amount + $loan->upfront_amount - $loan->bill_value, 2),
        'Upfront Paid'        => 'M ' . number_format($loan->upfront_amount, 2),
        'Payday Deduction'    => 'M ' . number_format($loan->payday_amount, 2),
        'Settled So Far'      => 'M ' . number_format($loan->settled_amount, 2),
        'Outstanding'         => 'M ' . number_format($loan->outstanding_amount, 2),
      ] as $label => $val)
      <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:13px;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9' : '' }}">
        <span style="color:var(--muted)">{{ $label }}</span>
        <span style="font-weight:600;{{ $label === 'Outstanding' && $loan->outstanding_amount > 0 ? 'color:var(--err)' : '' }}">{{ $val }}</span>
      </div>
      @endforeach
    </div>

    {{-- Category-specific info --}}
    @if($loan->meter_number || $loan->phone_number || $loan->policy_number || $loan->ticket_reference)
    <div style="padding:16px 20px;border-top:1px solid var(--border);background:#f8fafc">
      <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:8px">Provider Details</div>
      @if($loan->meter_number)
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px">
        <span style="color:var(--muted)">Meter Number</span>
        <code style="font-weight:600">{{ $loan->meter_number }}</code>
      </div>
      @endif
      @if($loan->phone_number)
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px">
        <span style="color:var(--muted)">Phone Number</span>
        <span style="font-weight:600">{{ $loan->phone_number }}</span>
      </div>
      @endif
      @if($loan->policy_number)
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px">
        <span style="color:var(--muted)">Policy Number</span>
        <span style="font-weight:600">{{ $loan->policy_number }}</span>
      </div>
      @endif
      @if($loan->ticket_reference)
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px">
        <span style="color:var(--muted)">Ticket Ref</span>
        <code style="font-weight:700;color:var(--blue)">{{ $loan->ticket_reference }}</code>
      </div>
      @endif
      @if($loan->cpay_txn_id)
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px">
        <span style="color:var(--muted)">CPay Ref</span>
        <code style="font-size:11px">{{ $loan->cpay_txn_id }}</code>
      </div>
      @endif

      {{-- Electricity Token Display --}}
      @if($loan->bill_category === 'electricity' && $loan->provider_response)
        @php
          $resp = json_decode($loan->provider_response, true);
          $token = $resp['data']['additionalData']['token'] ?? $resp['additional']['token'] ?? null;
        @endphp
        @if($token)
        <div style="margin-top:12px;background:#fff;border:2px dashed #f59e0b;border-radius:10px;padding:14px;text-align:center">
          <div style="font-size:11px;font-weight:700;color:var(--muted);margin-bottom:6px">YOUR ELECTRICITY TOKEN</div>
          <div style="font-size:20px;font-weight:800;letter-spacing:2px;color:var(--ink);font-family:monospace">{{ $token }}</div>
        </div>
        @endif
      @endif
    </div>
    @endif
  </div>

  {{-- Repayment History --}}
  @if($loan->repayments->isNotEmpty())
  <div style="background:#fff;border:1px solid #f1f5f9;border-radius:24px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.02)">
    <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;font-weight:800;font-size:15px;color:#0f172a">
      Payment History
    </div>
    @foreach($loan->repayments as $rep)
    <div style="display:flex;align-items:center;padding:16px 24px;border-bottom:1px solid #f8fafc;font-size:14px">
      <div style="width:40px;height:40px;border-radius:12px;background:#f8fafc;border:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;margin-right:16px;flex-shrink:0;color:#64748b">
        <i class="bi bi-{{ $rep->deduction_type === 'upfront' ? 'credit-card-2-front' : 'calendar-check' }}"></i>
      </div>
      <div style="flex:1">
        <div style="font-weight:700;color:#1e293b">{{ ucfirst($rep->deduction_type) }} Payment</div>
        <div style="font-size:12px;color:#64748b">{{ $rep->processed_at?->format('d M Y, H:i') ?? $rep->created_at->format('d M Y, H:i') }}</div>
      </div>
      <div style="text-align:right">
        <div style="font-weight:800;color:#0f172a">M {{ number_format($rep->amount, 2) }}</div>
        <div style="font-size:10px;font-weight:700;color:{{ $rep->status === 'success' ? '#10b981' : '#ef4444' }};text-transform:uppercase;letter-spacing:0.5px">{{ $rep->status }}</div>
      </div>
    </div>
    @endforeach
  </div>
  @endif
</div>

<style>
.alert{padding:16px 20px;border-radius:16px;font-size:14px;display:flex;align-items:center;gap:12px;font-weight:600}
.a-ok{background:#f0fdf4;color:#166534;border:1px solid #dcfce7}
.mb-3{margin-bottom:24px}
</style>
@endsection
