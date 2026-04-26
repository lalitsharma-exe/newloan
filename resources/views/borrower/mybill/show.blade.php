@extends('borrower.layouts.app')
@section('title','MyBill — ' . $loan->loan_number)
@section('content')

@if(session('success'))<div class="alert a-ok mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif

@php
  $icons = ['electricity'=>'lightning-charge-fill','airtime'=>'phone-fill','insurance'=>'shield-fill','ticket'=>'ticket-perforated-fill'];
  $colors = ['electricity'=>'#f59e0b','airtime'=>'#06b6d4','insurance'=>'#8b5cf6','ticket'=>'#ec4899'];
@endphp

<div style="max-width:560px;margin:0 auto">
  {{-- Back link --}}
  <a href="{{ route('borrower.mybill.index') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--muted);text-decoration:none;font-size:13px;font-weight:500;margin-bottom:20px">
    <i class="bi bi-arrow-left"></i> Back to MyBill
  </a>

  {{-- Status Banner --}}
  <div style="background:{{ $loan->status === 'settled' ? 'linear-gradient(135deg,#ecfdf5,#d1fae5)' : ($loan->status === 'active' ? 'linear-gradient(135deg,#fffbeb,#fef3c7)' : 'linear-gradient(135deg,#fef2f2,#fecaca)') }};border-radius:16px;padding:24px;margin-bottom:20px;text-align:center">
    <div style="width:56px;height:56px;border-radius:50%;background:{{ $loan->status === 'settled' ? '#10b981' : ($loan->status === 'active' ? '#f59e0b' : '#ef4444') }};display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#fff">
      <i class="bi bi-{{ $loan->status === 'settled' ? 'check-lg' : ($loan->status === 'active' ? 'clock-history' : 'x-lg') }}"></i>
    </div>
    <div style="font-size:18px;font-weight:800;color:{{ $loan->status === 'settled' ? '#065f46' : ($loan->status === 'active' ? '#92400e' : '#991b1b') }}">
      {{ $loan->status === 'settled' ? 'Fully Settled' : ($loan->status === 'active' ? 'Awaiting Payday' : ucfirst($loan->status)) }}
    </div>
    <div style="font-size:12px;color:{{ $loan->status === 'settled' ? '#059669' : ($loan->status === 'active' ? '#d97706' : '#dc2626') }};margin-top:4px">
      {{ $loan->loan_number }} · {{ $loan->created_at->format('d M Y, H:i') }}
    </div>
  </div>

  {{-- Transaction Details --}}
  <div style="background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
      <div style="width:36px;height:36px;border-radius:10px;background:{{ $colors[$loan->bill_category] }}15;display:flex;align-items:center;justify-content:center">
        <i class="bi bi-{{ $icons[$loan->bill_category] }}" style="color:{{ $colors[$loan->bill_category] }};font-size:16px"></i>
      </div>
      <span style="font-weight:700;font-size:15px">{{ ucfirst($loan->bill_category) }} Payment</span>
      <span style="margin-left:auto;font-size:11px;padding:3px 10px;border-radius:6px;font-weight:700;background:{{ $loan->tier === '30' ? 'rgba(43,75,173,.1)' : '#f3f4f6' }};color:{{ $loan->tier === '30' ? 'var(--blue)' : '#6b7280' }}">
        {{ $loan->tier === '30' ? 'Standard 30%' : 'No-Upfront 40%' }}
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
  <div style="background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-weight:700;font-size:14px">
      <i class="bi bi-clock-history" style="color:var(--blue)"></i> Payment History
    </div>
    @foreach($loan->repayments as $rep)
    <div style="display:flex;align-items:center;padding:12px 20px;border-bottom:1px solid #f1f5f9;font-size:13px">
      <div style="width:32px;height:32px;border-radius:8px;background:{{ $rep->deduction_type === 'upfront' ? 'rgba(43,75,173,.1)' : 'rgba(16,185,129,.1)' }};display:flex;align-items:center;justify-content:center;margin-right:12px;flex-shrink:0">
        <i class="bi bi-{{ $rep->deduction_type === 'upfront' ? 'arrow-up-circle' : 'calendar-check' }}" style="color:{{ $rep->deduction_type === 'upfront' ? 'var(--blue)' : '#10b981' }};font-size:14px"></i>
      </div>
      <div style="flex:1">
        <div style="font-weight:600">{{ ucfirst($rep->deduction_type) }} Payment</div>
        <div style="font-size:11px;color:var(--muted)">{{ $rep->processed_at?->format('d M Y, H:i') ?? $rep->created_at->format('d M Y, H:i') }}</div>
      </div>
      <div style="text-align:right">
        <div style="font-weight:700;color:#10b981">M {{ number_format($rep->amount, 2) }}</div>
        <div style="font-size:10px;color:{{ $rep->status === 'success' ? '#059669' : '#dc2626' }}">{{ ucfirst($rep->status) }}</div>
      </div>
    </div>
    @endforeach
  </div>
  @endif
</div>

<style>
.alert{padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px}
.a-ok{background:rgba(16,185,129,.08);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.mb-3{margin-bottom:16px}
</style>
@endsection
