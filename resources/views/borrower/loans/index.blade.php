@extends('borrower.layouts.app')
@section('title','My Loans')
@section('content')
<div style="font-size:20px;font-weight:800;margin-bottom:18px">My Loans</div>
@forelse($loans as $loan)
<div class="card" style="cursor:pointer" onclick="window.location='{{ route('borrower.loans.show',$loan) }}'">
  <div class="card-body">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div>
        <div style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p);margin-bottom:4px">{{ $loan->loan_number }}</div>
        <div style="font-size:16px;font-weight:800">M{{ number_format($loan->outstanding_balance,2) }} <span style="font-size:13px;font-weight:400;color:var(--muted)">outstanding</span></div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:2px">{{ $loan->loanProduct?->name }} &nbsp;·&nbsp; {{ $loan->term_months }} months &nbsp;·&nbsp; M{{ number_format($loan->monthly_installment,2) }}/mo</div>
      </div>
      <div style="text-align:right">
        <span class="badge {{ $loan->status==='active'?'bok':($loan->status==='overdue'?'be':($loan->status==='paid_off'?'bp':'bs')) }}" style="margin-bottom:8px;display:block">{{ ucfirst(str_replace('_',' ',$loan->status)) }}</span>
        <div style="font-size:11.5px;color:var(--muted)">Disbursed {{ $loan->disbursement_date?->format('d M Y') ?? '—' }}</div>
      </div>
    </div>
  </div>
</div>
@empty
<div style="text-align:center;padding:60px;color:var(--muted)"><i class="bi bi-bank" style="font-size:44px;opacity:.25;display:block;margin-bottom:12px"></i><div style="font-weight:600;margin-bottom:10px">No active loans</div><a href="{{ route('borrower.apply.start') }}" class="btn btn-p btn-sm">Apply Now</a></div>
@endforelse
@if($loans->hasPages())<div style="margin-top:14px">{{ $loans->links() }}</div>@endif
@endsection
