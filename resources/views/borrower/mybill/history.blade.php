@extends('borrower.layouts.app')
@section('title','MyBill — History')
@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px">
  <div>
    <h2 style="font-size:22px;font-weight:900;color:#0f172a;margin:0">Transaction History</h2>
    <div style="font-size:12.5px;color:var(--muted);margin-top:4px">Account Limit: <strong style="color:#0f172a">M {{ number_format($limit->total_limit, 2) }}</strong></div>
  </div>
  <a href="{{ route('borrower.mybill.index') }}" style="width:40px;height:40px;border-radius:12px;background:#fff;border:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;color:#64748b;text-decoration:none;box-shadow:0 2px 8px rgba(0,0,0,0.02)">
    <i class="bi bi-arrow-left"></i>
  </a>
</div>

{{-- Filter Categories --}}
<div style="display:flex;gap:10px;margin-bottom:24px;overflow-x:auto;padding-bottom:4px;scrollbar-width:none">
  @foreach(['' => 'All Services', 'electricity' => 'Electricity', 'airtime' => 'Airtime & Data', 'insurance' => 'Insurance', 'ticket' => 'Tickets'] as $val => $label)
  <a href="{{ route('borrower.mybill.history', array_merge(request()->only('status'), $val ? ['category' => $val] : [])) }}"
     style="padding:10px 18px;border-radius:100px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap;border:1px solid {{ (request('category', '') === $val) ? '#0f172a' : '#f1f5f9' }};color:{{ (request('category', '') === $val) ? '#fff' : '#64748b' }};background:{{ (request('category', '') === $val) ? '#0f172a' : '#fff' }};transition:all .2s">
    {{ $label }}
  </a>
  @endforeach
</div>

{{-- Transactions List --}}
<div style="background:#fff;border:1px solid #f1f5f9;border-radius:24px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.02)">
  <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;background:#fcfcfd">
    <span style="font-size:14px;font-weight:800;color:#0f172a">All Transactions</span>
    <div style="display:flex;gap:12px">
      @foreach(['' => 'All Status', 'active' => 'Active', 'settled' => 'Settled'] as $val => $label)
        <a href="{{ route('borrower.mybill.history', array_merge(request()->only('category'), $val ? ['status' => $val] : [])) }}" 
           style="font-size:11px;font-weight:700;color:{{ request('status', '') === $val ? '#3b82f6' : '#94a3b8' }};text-decoration:none;text-transform:uppercase;letter-spacing:0.5px">{{ $label }}</a>
      @endforeach
    </div>
  </div>

  <div style="padding:8px">
    @forelse($loans as $loan)
    <a href="{{ route('borrower.mybill.show', $loan) }}" class="txn-item">
      <div class="txn-icon">
        <i class="bi bi-{{ $loan->category_icon }}"></i>
      </div>
      <div style="flex:1">
        <div style="font-weight:700;font-size:14px;color:#1e293b">{{ ucfirst($loan->bill_category) }}</div>
        <div style="font-size:12px;color:#64748b">{{ $loan->created_at->format('d M Y') }} · {{ $loan->loan_number }}</div>
      </div>
      <div style="text-align:right">
        <div style="font-weight:800;font-size:15px;color:#0f172a">M {{ number_format($loan->bill_value, 2) }}</div>
        <div style="font-size:10px;font-weight:700;color:{{ $loan->status === 'settled' ? '#10b981' : '#f59e0b' }};text-transform:uppercase;letter-spacing:0.5px">{{ $loan->status }}</div>
      </div>
    </a>
    @empty
    <div style="padding:80px 20px;text-align:center;color:var(--muted)">
      <div style="font-size:14px;font-weight:600">No transactions found</div>
      <div style="font-size:12px">Try adjusting your filters</div>
    </div>
    @endforelse
  </div>
</div>

<style>
.txn-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border-radius: 14px;
  text-decoration: none;
  transition: background 0.2s;
}
.txn-item:hover {
  background: #f8fafc;
}
.txn-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-size: 18px;
}
</style>

@if($loans->hasPages())
<div style="margin-top:16px">{{ $loans->withQueryString()->links() }}</div>
@endif

@endsection
