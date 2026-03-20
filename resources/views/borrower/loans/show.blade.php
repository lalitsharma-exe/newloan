@extends('borrower.layouts.app')
@section('title','Loan '.$loan->loan_number)
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px">
  <div>
    <div style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p)">{{ $loan->loan_number }}</div>
    <div style="font-size:20px;font-weight:800;margin-top:2px">{{ $loan->loanProduct?->name }}</div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a href="{{ route('borrower.loans.agreement',$loan) }}" class="btn btn-o btn-sm"><i class="bi bi-file-pdf"></i> Agreement</a>
    <a href="{{ route('borrower.loans.statement',$loan) }}" class="btn btn-o btn-sm"><i class="bi bi-file-earmark-text"></i> Statement</a>
    @if(in_array($loan->status,['active','overdue']))<a href="{{ route('borrower.payments.make') }}" class="btn btn-p btn-sm"><i class="bi bi-cash"></i> Make Payment</a>@endif
  </div>
</div>

<div class="stat-grid" style="margin-bottom:20px">
  @foreach(['Principal'=>'M '.number_format($loan->principal_amount,2),'Outstanding'=>'M '.number_format($loan->outstanding_balance,2),'Monthly'=>'M '.number_format($loan->monthly_installment,2),'Maturity'=>$loan->maturity_date?->format('d M Y')??'—'] as $l=>$v)
  <div class="stat"><div class="stat-val" style="font-size:18px">{{ $v }}</div><div class="stat-lbl">{{ $l }}</div></div>
  @endforeach
</div>

<div class="card">
  <div class="card-hdr"><span class="card-title">Repayment Schedule</span></div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>#</th><th>Due Date</th><th>Amount</th><th>Paid</th><th>Outstanding</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($loan->installments as $i)
        <tr style="{{ $i->status==='overdue'?'background:#fef2f2':'' }}">
          <td>{{ $i->installment_number }}</td>
          <td>{{ $i->due_date->format('d M Y') }}</td>
          <td style="font-weight:700">M{{ number_format($i->total_amount,2) }}</td>
          <td style="color:var(--ok)">M{{ number_format($i->paid_amount,2) }}</td>
          <td style="color:{{ $i->outstanding_amount>0?'var(--err)':'var(--ok)' }}">M{{ number_format($i->outstanding_amount,2) }}</td>
          <td><span class="badge {{ $i->status==='paid'?'bok':($i->status==='overdue'?'be':($i->status==='partial'?'bw':'bs')) }}">{{ ucfirst($i->status) }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
