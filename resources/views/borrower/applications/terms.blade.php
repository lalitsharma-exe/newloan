@extends('borrower.layouts.app')
@section('title','Accept Loan Terms')
@section('content')
<div class="card">
  <div class="card-hdr"><span class="card-title">Loan Terms — {{ $application->application_number }}</span></div>
  <div class="card-body">
    <div class="alert a-ok"><i class="bi bi-check-circle-fill"></i><strong>Your loan has been approved!</strong> Review the terms below before accepting.</div>
    @php $loan=$application->loan; @endphp
    @if($loan)
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">
      @foreach(['Principal'=>'M '.number_format($loan->principal_amount,2),'Interest Rate'=>$loan->interest_rate.'% / month','Term'=>$loan->term_months.' months','Monthly Instalment'=>'M '.number_format($loan->monthly_installment,2),'Initiation Fee'=>'M '.number_format($loan->processing_fee,2),'Total Repayable'=>'M '.number_format($loan->total_amount,2)] as $l=>$v)
      <div style="background:#f8fafc;border-radius:10px;padding:12px;border:1px solid var(--border)">
        <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase">{{ $l }}</div>
        <div style="font-size:16px;font-weight:800;margin-top:3px">{{ $v }}</div>
      </div>
      @endforeach
    </div>
    @endif
    <div style="background:#fef9c3;border:1px solid #fde047;border-radius:10px;padding:14px;margin-bottom:20px;font-size:13px;color:#713f12;line-height:1.7">
      By accepting these terms you confirm you have read and agree to the loan agreement, and that all information provided is accurate and true.
    </div>
    <form method="POST" action="{{ route('borrower.applications.accept-terms.post',$application) }}">@csrf
      <div style="display:flex;gap:12px;justify-content:flex-end">
        <a href="{{ route('borrower.applications.show',$application) }}" class="btn btn-o">Cancel</a>
        <button type="submit" class="btn btn-ok"><i class="bi bi-check-circle-fill"></i> Accept Terms</button>
      </div>
    </form>
  </div>
</div>
@endsection
