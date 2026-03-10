@extends('admin.layouts.app')
@section('title','Loans')@section('page-title','Loan Management')
@section('content')
<div class="g4 mb6">
  <div class="sc"><div class="si ok"><i class="bi bi-check-circle-fill"></i></div><div><div class="sv">{{$stats['total_active']}}</div><div class="sl">Active</div></div></div>
  <div class="sc"><div class="si e"><i class="bi bi-exclamation-circle-fill"></i></div><div><div class="sv">{{$stats['total_overdue']}}</div><div class="sl">Overdue</div></div></div>
  <div class="sc"><div class="si p"><i class="bi bi-graph-up"></i></div><div><div class="sv">L{{ number_format($stats['total_portfolio'],0) }}</div><div class="sl">Portfolio</div></div></div>
  <div class="sc"><div class="si i"><i class="bi bi-trophy-fill"></i></div><div><div class="sv">{{$stats['paid_off']}}</div><div class="sl">Paid Off</div></div></div>
</div>
<form method="GET" action="{{ route('admin.loans.index') }}" class="filter-bar">
  <div class="fg" style="flex:2;min-width:180px"><label class="fl">Search</label><input type="text" name="search" class="fc" placeholder="Loan#, borrower…" value="{{ $filters['search']??'' }}"></div>
  <div class="fg"><label class="fl">Status</label><select name="status" class="fc"><option value="">All</option>@foreach(['active','overdue','paid_off','closed','defaulted'] as $s)<option value="{{$s}}" {{ ($filters['status']??'')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div>
  <div class="fg"><label class="fl">Product</label><select name="product" class="fc"><option value="">All</option>@foreach($products as $p)<option value="{{$p->id}}" {{ ($filters['product']??'')==$p->id?'selected':'' }}>{{$p->name}}</option>@endforeach</select></div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p"><i class="bi bi-search"></i> Filter</button><a href="{{ route('admin.loans.index') }}" class="btn btn-o">Clear</a></div>
</form>
<div class="card">
  <div class="card-hdr"><span class="card-title">All Loans ({{ $loans->total() }})</span></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>Loan#</th><th>Borrower</th><th>Product</th><th>Amount</th><th>Outstanding</th><th>Next Due</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
      @forelse($loans as $l)
      <tr>
        <td><span style="font-weight:700;color:#4f46e5;font-size:11.5px">{{$l->loan_number}}</span></td>
        <td><div class="flex aic gap2"><div class="av av-sm">{{ strtoupper(substr($l->user->name??'U',0,1)) }}</div><div><div style="font-size:12.5px;font-weight:600">{{$l->user->name}}</div><div class="muted">{{$l->user->phone}}</div></div></div></td>
        <td style="font-size:12.5px">{{$l->loanProduct->name??'—'}}</td>
        <td><strong>L{{ number_format($l->principal_amount,0) }}</strong></td>
        <td><span style="font-weight:700;color:{{ $l->outstanding_balance>0?'#ef4444':'#10b981' }}">L{{ number_format($l->outstanding_balance,0) }}</span></td>
        <td class="muted">@php $nx=$l->installments->where('status','pending')->first() @endphp{{ $nx?->due_date->format('d M Y')??'—' }}</td>
        <td><span class="badge {{ $l->status==='active'?'bok':($l->status==='overdue'?'be':($l->status==='paid_off'?'bp':'bs')) }}">{{ ucfirst(str_replace('_',' ',$l->status)) }}</span></td>
        <td><a href="{{ route('admin.loans.show',$l) }}" class="btn btn-sm btn-p"><i class="bi bi-eye"></i> View</a></td>
      </tr>
      @empty<tr><td colspan="8"><div class="empty"><i class="bi bi-wallet2"></i><p>No loans</p></div></td></tr>@endforelse
    </tbody>
  </table></div>
  @if($loans->hasPages())<div style="padding:14px 18px;border-top:1px solid #f1f5f9">{{ $loans->withQueryString()->links() }}</div>@endif
</div>
@endsection
