@extends('admin.layouts.app')
@section('title','User Loans')
@section('page-title','Loans')
@section('bc','<a href="'.route('admin.users.index').'">Users</a> / <a href="'.route('admin.users.show',$user).'">{{ $user->name }}</a> / Loans')
@section('content')

<div style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
  <div class="av av-lg">{{ strtoupper(substr($user->name,0,1)) }}</div>
  <div>
    <div style="font-size:18px;font-weight:700">{{ $user->name }}</div>
    <div class="muted">{{ $user->email }}</div>
  </div>
  <a href="{{ route('admin.users.show',$user) }}" class="btn btn-o btn-sm" style="margin-left:auto"><i class="bi bi-arrow-left"></i> Back to Profile</a>
</div>

{{-- Loan stats --}}
<div class="g4 mb6">
  @php
    $active   = $user->loans->where('status','active');
    $overdue  = $user->loans->where('status','overdue');
    $paidOff  = $user->loans->where('status','paid_off');
    $outstanding = $user->loans->sum('outstanding_balance');
  @endphp
  <div class="sc"><div class="si ok"><i class="bi bi-check-circle-fill"></i></div><div><div class="sv">{{ $active->count() }}</div><div class="sl">Active Loans</div></div></div>
  <div class="sc"><div class="si e"><i class="bi bi-exclamation-circle-fill"></i></div><div><div class="sv">{{ $overdue->count() }}</div><div class="sl">Overdue</div></div></div>
  <div class="sc"><div class="si i"><i class="bi bi-trophy-fill"></i></div><div><div class="sv">{{ $paidOff->count() }}</div><div class="sl">Paid Off</div></div></div>
  <div class="sc"><div class="si w"><i class="bi bi-currency-dollar"></i></div><div><div class="sv">M{{ number_format($outstanding,0) }}</div><div class="sl">Total Outstanding</div></div></div>
</div>

<div class="card">
  <div class="card-hdr"><span class="card-title">All Loans ({{ $user->loans->count() }})</span></div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>Loan #</th><th>Product</th><th>Principal</th><th>Monthly</th><th>Outstanding</th><th>Disbursed</th><th>Maturity</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        @forelse($user->loans as $loan)
        <tr>
          <td><span style="font-weight:700;color:var(--p);font-size:12px">{{ $loan->loan_number }}</span></td>
          <td style="font-size:13px">{{ $loan->loanProduct->name ?? '—' }}</td>
          <td><strong>M{{ number_format($loan->principal_amount,2) }}</strong></td>
          <td>M{{ number_format($loan->monthly_installment,2) }}</td>
          <td style="font-weight:700;color:{{ $loan->outstanding_balance>0?'#ef4444':'#10b981' }}">M{{ number_format($loan->outstanding_balance,2) }}</td>
          <td class="muted">{{ $loan->disbursement_date?->format('d M Y') ?? '—' }}</td>
          <td class="muted">{{ $loan->maturity_date?->format('d M Y') ?? '—' }}</td>
          <td><span class="badge {{ $loan->status==='active'?'bok':($loan->status==='overdue'?'be':($loan->status==='paid_off'?'bp':'bs')) }}">{{ ucfirst(str_replace('_',' ',$loan->status)) }}</span></td>
          <td><a href="{{ route('admin.loans.show',$loan) }}" class="btn btn-xs btn-p"><i class="bi bi-eye"></i> View</a></td>
        </tr>
        @empty
        <tr><td colspan="9"><div class="empty"><i class="bi bi-wallet2"></i><p>No loans for this borrower</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
