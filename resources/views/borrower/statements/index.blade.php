@extends('borrower.layouts.app')
@section('title','Statements')
@section('content')
<div style="font-size:20px;font-weight:800;margin-bottom:18px">Loan Statements</div>
@forelse($loans as $loan)
<div class="card" style="margin-bottom:12px">
  <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
      <div style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p)">{{ $loan->loan_number }}</div>
      <div style="font-size:15px;font-weight:700;margin-top:2px">{{ $loan->loanProduct?->name }}</div>
      <div style="font-size:12.5px;color:var(--muted)">M{{ number_format($loan->principal_amount,0) }} &nbsp;·&nbsp; {{ ucfirst(str_replace('_',' ',$loan->status)) }}</div>
    </div>
    <a href="{{ route('borrower.statements.loan',$loan) }}" class="btn btn-o btn-sm"><i class="bi bi-file-earmark-text"></i> View Statement</a>
  </div>
</div>
@empty
<div style="text-align:center;padding:50px;color:var(--muted)"><i class="bi bi-file-earmark-text" style="font-size:44px;opacity:.25;display:block;margin-bottom:12px"></i><div style="font-weight:600">No loans yet</div></div>
@endforelse
@endsection
