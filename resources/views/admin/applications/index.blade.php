@extends('admin.layouts.app')
@section('title','Applications')@section('page-title','Applications')
@section('bc','<a href="'.route('admin.dashboard').'">Home</a> / Applications')
@section('content')
<div class="g4 mb6">
  <div class="sc"><div class="si p"><i class="bi bi-collection-fill"></i></div><div><div class="sv">{{$stats['total']}}</div><div class="sl">Total</div></div></div>
  <div class="sc"><div class="si w"><i class="bi bi-clock-fill"></i></div><div><div class="sv">{{$stats['pending']}}</div><div class="sl">Pending</div></div></div>
  <div class="sc"><div class="si ok"><i class="bi bi-check-circle-fill"></i></div><div><div class="sv">{{$stats['approved_today']}}</div><div class="sl">Approved Today</div></div></div>
  <div class="sc"><div class="si e"><i class="bi bi-x-circle-fill"></i></div><div><div class="sv">{{$stats['declined_today']}}</div><div class="sl">Declined Today</div></div></div>
</div>
<form method="GET" action="{{ route('admin.applications.index') }}" class="filter-bar">
  <div class="fg" style="flex:2;min-width:180px"><label class="fl">Search</label><input type="text" name="search" class="fc" placeholder="Name, App#, email…" value="{{ $filters['search']??'' }}"></div>
  <div class="fg"><label class="fl">Status</label><select name="status" class="fc"><option value="">All</option>@foreach(['submitted','under_review','info_requested','on_hold','approved','declined','disbursed'] as $s)<option value="{{$s}}" {{ ($filters['status']??'')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div>
  <div class="fg"><label class="fl">Product</label><select name="product" class="fc"><option value="">All</option>@foreach($products as $p)<option value="{{$p->id}}" {{ ($filters['product']??'')==$p->id?'selected':'' }}>{{$p->name}}</option>@endforeach</select></div>
  <div class="fg"><label class="fl">From</label><input type="date" name="date_from" class="fc" value="{{ $filters['date_from']??'' }}"></div>
  <div class="fg"><label class="fl">To</label><input type="date" name="date_to" class="fc" value="{{ $filters['date_to']??'' }}"></div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p"><i class="bi bi-search"></i> Filter</button><a href="{{ route('admin.applications.index') }}" class="btn btn-o">Clear</a></div>
</form>
<div class="card">
  <div class="card-hdr"><span class="card-title">Applications ({{ $applications->total() }})</span></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>App#</th><th>Applicant</th><th>Product</th><th>Amount</th><th>Score</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
    <tbody>
      @forelse($applications as $a)
      <tr>
        <td><span style="font-weight:700;color:#4f46e5;font-size:11.5px">{{$a->application_number}}</span></td>
        <td><div class="flex aic gap2"><div class="av av-sm">{{ strtoupper(substr($a->user->name??'U',0,1)) }}</div><div><div style="font-size:12.5px;font-weight:600">{{$a->user->name}}</div><div class="muted">{{$a->user->email}}</div></div></div></td>
        <td style="font-size:12.5px">{{$a->loanProduct->name??'—'}}</td>
        <td><strong>L{{ number_format($a->requested_amount??0,0) }}</strong></td>
        <td>@if($a->risk_score)<span class="badge {{ $a->risk_score>=700?'bok':($a->risk_score>=500?'bw':'be') }}">{{$a->risk_score}}</span>@else<span class="muted">—</span>@endif</td>
        <td><span class="badge b{{$a->status_badge}}">{{ ucfirst(str_replace('_',' ',$a->status)) }}</span></td>
        <td class="muted">{{ $a->submitted_at?->format('d M Y')??$a->created_at->format('d M Y') }}</td>
        <td><a href="{{ route('admin.applications.show',$a) }}" class="btn btn-sm btn-p"><i class="bi bi-eye"></i> Review</a></td>
      </tr>
      @empty<tr><td colspan="8"><div class="empty"><i class="bi bi-inbox"></i><p>No applications found</p></div></td></tr>@endforelse
    </tbody>
  </table></div>
  @if($applications->hasPages())<div style="padding:14px 18px;border-top:1px solid #f1f5f9">{{ $applications->withQueryString()->links() }}</div>@endif
</div>
@endsection
