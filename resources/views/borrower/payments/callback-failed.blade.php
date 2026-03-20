@extends('borrower.layouts.app')
@section('title','Payment Failed')
@section('content')
<div style="text-align:center;padding:50px 20px">
  <div style="font-size:56px;margin-bottom:16px">❌</div>
  <div style="font-size:24px;font-weight:900;margin-bottom:10px">Payment Failed</div>
  <div style="font-size:14px;color:var(--muted);margin-bottom:32px">Your payment could not be processed. Please try again.</div>
  <a href="{{ route('borrower.payments.make') }}" class="btn btn-p">Try Again</a>
</div>
@endsection
