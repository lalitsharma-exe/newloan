@extends('borrower.layouts.app')
@section('title','Payment Receipt')
@section('content')
<div style="max-width:480px;margin:0 auto">
<div style="text-align:center;margin-bottom:24px">
  <div style="width:64px;height:64px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:28px;color:var(--ok)"><i class="bi bi-check-circle-fill"></i></div>
  <div style="font-size:22px;font-weight:900">Payment Receipt</div>
</div>
<div class="card">
  <div class="card-body">
    @foreach(['Reference'=>$payment->payment_reference,'Loan #'=>$payment->loan?->loan_number,'Amount'=>'M '.number_format($payment->amount,2),'Method'=>ucfirst(str_replace('_',' ',$payment->method)),'Status'=>ucfirst($payment->status),'Date'=>$payment->created_at->format('d M Y H:i')] as $l=>$v)
    <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);font-size:13.5px">
      <span style="color:var(--muted)">{{ $l }}</span>
      <span style="font-weight:700{{ $l==='Amount'?';color:var(--ok)':'' }}">{{ $v }}</span>
    </div>
    @endforeach
  </div>
</div>
<div style="text-align:center;margin-top:16px"><button onclick="window.print()" class="btn btn-o btn-sm"><i class="bi bi-printer"></i> Print</button></div>
</div>
@endsection
