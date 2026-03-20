@extends('borrower.layouts.app')
@section('title','Payment Successful')
@section('content')
<div style="text-align:center;padding:50px 20px">
  <div style="font-size:56px;margin-bottom:16px">🎉</div>
  <div style="font-size:24px;font-weight:900;margin-bottom:10px">Payment Successful!</div>
  <div style="font-size:14px;color:var(--muted);margin-bottom:8px">Reference: <strong>{{ $payment?->payment_reference }}</strong></div>
  <div style="font-size:14px;color:var(--muted);margin-bottom:32px">Amount: <strong>M{{ number_format($payment?->amount??0,2) }}</strong></div>
  <div style="display:flex;gap:12px;justify-content:center">
    @if($payment)<a href="{{ route('borrower.payments.receipt',$payment) }}" class="btn btn-ok">View Receipt</a>@endif
    <a href="{{ route('borrower.dashboard') }}" class="btn btn-o">Dashboard</a>
  </div>
</div>
@endsection
