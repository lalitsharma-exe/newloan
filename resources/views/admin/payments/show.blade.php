@extends('admin.layouts.app')
@section('title','Payment Detail')
@section('page-title','Payment ' . $payment->payment_reference)
@section('content')
<div style="max-width:700px">
<div class="card mb-4">
    <div class="card-header"><span class="card-title">Payment Details</span>
    <div style="display:flex;gap:8px">
        <a href="{{ route('borrower.payments.receipt', $payment) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-text"></i> View Receipt</a>
        @if($payment->status==='pending')
        <form method="POST" action="{{ route('admin.payments.verify',$payment) }}">@csrf<input type="hidden" name="status" value="verified"><button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Verify</button></form>
        <form method="POST" action="{{ route('admin.payments.verify',$payment) }}">@csrf<input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i> Reject</button></form>
        @endif
    </div>
    </div>
    <div class="card-body"><div class="info-grid">
        @foreach(['Reference'=>$payment->payment_reference,'Amount'=>'L '.number_format($payment->amount,2),'Method'=>ucfirst(str_replace('_',' ',$payment->method)),'Status'=>ucfirst($payment->status),'Loan #'=>$payment->loan->loan_number??'—','Borrower'=>$payment->loan->user->name??'—','Date'=>$payment->created_at->format('d M Y H:i'),'Verified By'=>$payment->verifiedBy->name??'—','Notes'=>$payment->notes??'—'] as $l=>$v)
        <div class="info-item"><div class="info-label">{{ $l }}</div><div class="info-value">{{ $v }}</div></div>
        @endforeach
    </div></div>
</div>
</div>
@endsection
