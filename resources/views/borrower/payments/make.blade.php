@extends('borrower.layouts.app')
@section('title','Make Payment')
@section('content')
<div style="max-width:520px">
<div style="font-size:20px;font-weight:800;margin-bottom:18px">Make a Payment</div>
@forelse($loans as $loan)
<div class="card" style="margin-bottom:14px">
  <div class="card-body">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
      <div>
        <div style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p)">{{ $loan->loan_number }}</div>
        <div style="font-size:16px;font-weight:800;margin-top:2px">M{{ number_format($loan->outstanding_balance,2) }} <span style="font-size:12px;font-weight:400;color:var(--muted)">outstanding</span></div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:2px">Next instalment: M{{ number_format($loan->installments->whereIn('status',['pending','overdue','partial'])->sortBy('due_date')->first()?->outstanding_amount ?? $loan->monthly_installment, 2) }}</div>
      </div>
      <span class="badge {{ $loan->status==='overdue'?'be':'bok' }}">{{ ucfirst($loan->status) }}</span>
    </div>
    <form method="POST" action="{{ route('borrower.payments.initiate') }}">@csrf
      <input type="hidden" name="loan_id" value="{{ $loan->id }}">
      <div class="fg"><label class="fl">Amount (M) *</label><input type="number" name="amount" class="fc" step="0.01" min="1" value="{{ $loan->installments->whereIn('status',['pending','overdue','partial'])->sortBy('due_date')->first()?->outstanding_amount ?? $loan->monthly_installment }}" required></div>
      <div class="fg"><label class="fl">Payment Method</label>
        <select name="method" class="fc"><option value="mobile_money">Mobile Money</option><option value="bank_transfer">Bank Transfer</option><option value="cash">Cash</option></select>
      </div>
      <button type="submit" class="btn btn-p" style="width:100%;justify-content:center"><i class="bi bi-cash-coin"></i> Pay Now</button>
    </form>
  </div>
</div>
@empty
<div style="text-align:center;padding:50px;color:var(--muted)"><i class="bi bi-check-circle-fill" style="font-size:44px;color:var(--ok);display:block;margin-bottom:12px"></i><div style="font-weight:600">No active loans</div></div>
@endforelse
</div>
@endsection
