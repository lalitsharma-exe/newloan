@extends('admin.layouts.app')
@section('title','Borrower Demographics')
@section('page-title','Borrower Demographics')
@section('bc','<a href="'.route('admin.reports.index').'">Reports</a> / Borrower Demographics')
@section('content')

<div class="g4 mb6">
  <div class="sc"><div class="si p"><i class="bi bi-people-fill"></i></div><div><div class="sv">{{ $data['total'] }}</div><div class="sl">Total Borrowers</div></div></div>
  <div class="sc"><div class="si ok"><i class="bi bi-person-check-fill"></i></div><div><div class="sv">{{ $data['active'] }}</div><div class="sl">Active Accounts</div></div></div>
  <div class="sc"><div class="si i"><i class="bi bi-bank"></i></div><div><div class="sv">{{ $data['with_loans'] }}</div><div class="sl">Have Loans</div></div></div>
  <div class="sc"><div class="si w"><i class="bi bi-person-plus-fill"></i></div><div><div class="sv">{{ $data['new_this_month'] }}</div><div class="sl">New This Month</div></div></div>
</div>

<div class="g2" style="gap:20px">

  <div class="card">
    <div class="card-hdr"><span class="card-title">Gender Breakdown</span></div>
    <div style="padding:20px">
      @if($data['by_gender']->isEmpty())
      <div class="empty" style="padding:30px"><i class="bi bi-bar-chart"></i><p>No gender data available</p></div>
      @else
      @php $total = $data['by_gender']->sum(); @endphp
      @foreach($data['by_gender'] as $gender => $count)
      @php $pct = $total > 0 ? round($count/$total*100) : 0; @endphp
      <div style="margin-bottom:16px">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
          <span style="font-weight:600;text-transform:capitalize">{{ $gender ?: 'Unknown' }}</span>
          <span class="muted">{{ $count }} ({{ $pct }}%)</span>
        </div>
        <div style="background:#f1f5f9;border-radius:6px;height:10px">
          <div style="background:var(--p);border-radius:6px;height:10px;width:{{ $pct }}%"></div>
        </div>
      </div>
      @endforeach
      @endif
    </div>
  </div>

  <div class="card">
    <div class="card-hdr"><span class="card-title">Loan Engagement</span></div>
    <div style="padding:20px">
      @php
        $withLoans = $data['with_loans'];
        $noLoans   = $data['total'] - $withLoans;
        $pctWith   = $data['total'] > 0 ? round($withLoans/$data['total']*100) : 0;
      @endphp
      <div style="margin-bottom:16px">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
          <span style="font-weight:600">Have at least 1 loan</span>
          <span class="muted">{{ $withLoans }} ({{ $pctWith }}%)</span>
        </div>
        <div style="background:#f1f5f9;border-radius:6px;height:10px">
          <div style="background:#10b981;border-radius:6px;height:10px;width:{{ $pctWith }}%"></div>
        </div>
      </div>
      <div style="margin-bottom:16px">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
          <span style="font-weight:600">No loans yet</span>
          <span class="muted">{{ $noLoans }} ({{ 100-$pctWith }}%)</span>
        </div>
        <div style="background:#f1f5f9;border-radius:6px;height:10px">
          <div style="background:#94a3b8;border-radius:6px;height:10px;width:{{ 100-$pctWith }}%"></div>
        </div>
      </div>
      <div style="margin-top:20px;font-size:13px">
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
          <span class="muted">Active accounts</span><strong>{{ $data['active'] }}</strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
          <span class="muted">Inactive / restricted</span><strong>{{ $data['total'] - $data['active'] }}</strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0">
          <span class="muted">New this month</span><strong style="color:#059669">{{ $data['new_this_month'] }}</strong>
        </div>
      </div>
    </div>
  </div>

</div>

<div style="margin-top:16px;text-align:right">
  <a href="{{ route('admin.reports.index') }}" class="btn btn-o"><i class="bi bi-arrow-left"></i> Back to Reports</a>
</div>
@endsection
