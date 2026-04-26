@extends('borrower.layouts.app')
@section('title','MyBill — History')
@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
  <div>
    <h2 style="font-size:20px;font-weight:800;margin:0"><i class="bi bi-clock-history" style="color:var(--blue)"></i> Transaction History</h2>
    <div style="font-size:12px;color:var(--muted);margin-top:4px">Available credit: M {{ number_format($limit->available_amount, 2) }}</div>
  </div>
  <a href="{{ route('borrower.mybill.index') }}" style="padding:8px 16px;border:1px solid var(--border);border-radius:10px;font-size:13px;color:var(--muted);text-decoration:none;font-weight:500">
    <i class="bi bi-arrow-left"></i> MyBill
  </a>
</div>

{{-- Filter tabs --}}
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
  @foreach(['' => 'All', 'electricity' => '⚡ Electricity', 'airtime' => '📱 Airtime', 'insurance' => '🛡️ Insurance', 'ticket' => '🎫 Tickets'] as $val => $label)
  <a href="{{ route('borrower.mybill.history', array_merge(request()->only('status'), $val ? ['category' => $val] : [])) }}"
     style="padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid {{ (request('category', '') === $val) ? 'var(--blue)' : 'var(--border)' }};color:{{ (request('category', '') === $val) ? 'var(--blue)' : 'var(--muted)' }};background:{{ (request('category', '') === $val) ? 'rgba(43,75,173,.05)' : '#fff' }}">
    {{ $label }}
  </a>
  @endforeach
</div>

<div style="display:flex;gap:8px;margin-bottom:22px;flex-wrap:wrap">
  @foreach(['' => 'All Status', 'active' => 'Active', 'settled' => 'Settled', 'partial' => 'Partial', 'failed' => 'Failed'] as $val => $label)
  <a href="{{ route('borrower.mybill.history', array_merge(request()->only('category'), $val ? ['status' => $val] : [])) }}"
     style="padding:6px 12px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;border:1px solid {{ (request('status', '') === $val) ? 'var(--blue)' : '#e2e8f0' }};color:{{ (request('status', '') === $val) ? 'var(--blue)' : 'var(--muted)' }};background:{{ (request('status', '') === $val) ? 'rgba(43,75,173,.05)' : '#f8fafc' }}">
    {{ $label }}
  </a>
  @endforeach
</div>

{{-- Transactions --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden">
  @forelse($loans as $loan)
  @php
    $icons = ['electricity'=>'lightning-charge-fill','airtime'=>'phone-fill','insurance'=>'shield-fill','ticket'=>'ticket-perforated-fill'];
    $colors = ['electricity'=>'#f59e0b','airtime'=>'#06b6d4','insurance'=>'#8b5cf6','ticket'=>'#ec4899'];
  @endphp
  <a href="{{ route('borrower.mybill.show', $loan) }}" style="display:flex;align-items:center;padding:16px 22px;border-bottom:1px solid #f1f5f9;text-decoration:none;color:inherit;transition:background .2s"
     onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
    <div style="width:42px;height:42px;border-radius:10px;background:{{ $colors[$loan->bill_category] }}15;display:flex;align-items:center;justify-content:center;margin-right:14px;flex-shrink:0">
      <i class="bi bi-{{ $icons[$loan->bill_category] }}" style="color:{{ $colors[$loan->bill_category] }};font-size:18px"></i>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-weight:600;font-size:13.5px">{{ ucfirst($loan->bill_category) }}</div>
      <div style="font-size:11px;color:var(--muted)">
        {{ $loan->loan_number }} ·
        {{ $loan->meter_number ?? $loan->phone_number ?? $loan->policy_number ?? '' }}
        · {{ $loan->created_at->format('d M Y') }}
      </div>
    </div>
    <div style="text-align:right;flex-shrink:0;margin-left:10px">
      <div style="font-weight:700;font-size:14px">M {{ number_format($loan->bill_value, 2) }}</div>
      <div style="display:flex;align-items:center;gap:6px;justify-content:flex-end;margin-top:3px">
        <span style="font-size:10px;padding:2px 8px;border-radius:6px;font-weight:600;background:{{ $loan->tier === '30' ? 'rgba(43,75,173,.08)' : '#f3f4f6' }};color:{{ $loan->tier === '30' ? 'var(--blue)' : '#6b7280' }}">{{ $loan->tier }}%</span>
        <span style="font-size:10px;padding:2px 8px;border-radius:6px;font-weight:600;background:{{ $loan->status === 'settled' ? '#ecfdf5' : ($loan->status === 'active' ? '#fffbeb' : ($loan->status === 'partial' ? '#eff6ff' : '#fef2f2')) }};color:{{ $loan->status === 'settled' ? '#059669' : ($loan->status === 'active' ? '#d97706' : ($loan->status === 'partial' ? '#2563eb' : '#dc2626')) }}">{{ ucfirst($loan->status) }}</span>
      </div>
    </div>
  </a>
  @empty
  <div style="text-align:center;padding:48px 20px;color:var(--muted)">
    <i class="bi bi-receipt" style="font-size:40px;display:block;margin-bottom:12px;opacity:.3"></i>
    <div style="font-weight:600;margin-bottom:4px">No transactions found</div>
    <div style="font-size:12px">Try adjusting your filters</div>
  </div>
  @endforelse
</div>

@if($loans->hasPages())
<div style="margin-top:16px">{{ $loans->withQueryString()->links() }}</div>
@endif

@endsection
