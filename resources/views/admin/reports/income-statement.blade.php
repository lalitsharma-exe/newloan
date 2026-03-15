@extends('admin.layouts.app')
@section('title','Income & Profit')
@section('page-title','Income & Profit Report')
@section('bc')
<a href="{{ route('admin.reports.index') }}">Reports</a> / Income & Profit
@endsection
@section('content')
<div class="g4 mb6">
  <div class="sc"><div class="si ok"><i class="bi bi-graph-up-arrow"></i></div><div><div class="sv">M{{ number_format($data['totalIncome'],0) }}</div><div class="sl">Total Income</div></div></div>
  <div class="sc"><div class="si p"><i class="bi bi-percent"></i></div><div><div class="sv">M{{ number_format($data['interestIncome'],0) }}</div><div class="sl">Interest Income</div></div></div>
  <div class="sc"><div class="si i"><i class="bi bi-receipt"></i></div><div><div class="sv">M{{ number_format($data['initiationIncome'],0) }}</div><div class="sl">Initiation Fees</div></div></div>
  <div class="sc"><div class="si {{ $data['netProfit'] >= 0 ? 'ok' : 'e' }}"><i class="bi bi-wallet2"></i></div><div><div class="sv" style="color:{{ $data['netProfit'] >= 0 ? 'var(--ok)' : 'var(--err)' }}">M{{ number_format($data['netProfit'],0) }}</div><div class="sl">Net Profit</div></div></div>
</div>
<form method="GET" class="filter-bar">
  <div class="fg" style="margin-bottom:0"><label class="fl">From</label><input type="date" name="date_from" class="fc" value="{{ $data['from'] }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">To</label><input type="date" name="date_to" class="fc" value="{{ $data['to'] }}"></div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p btn-sm"><i class="bi bi-funnel"></i> Filter</button><a href="{{ route('admin.reports.income-statement') }}" class="btn btn-o btn-sm">Clear</a></div>
</form>
<div class="g2 mb6" style="gap:16px">
  <div class="card">
    <div class="card-hdr"><span class="card-title">Income Breakdown</span></div>
    <div style="padding:14px 18px">
      @foreach(['Interest Income'=>$data['interestIncome'],'Initiation Fees'=>$data['initiationIncome'],'Admin Fees'=>$data['adminFeeIncome'],'Penalty Income'=>$data['penaltyIncome']] as $label => $amount)
      <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:13px">
        <span>{{ $label }}</span><strong>M{{ number_format($amount,2) }}</strong>
      </div>
      @endforeach
      <div style="display:flex;justify-content:space-between;padding:9px 0;font-size:14px;font-weight:700">
        <span>Total Income</span><span style="color:var(--ok)">M{{ number_format($data['totalIncome'],2) }}</span>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-hdr"><span class="card-title">Expenses & Losses</span></div>
    <div style="padding:14px 18px">
      <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:13px"><span>Capital Disbursed</span><strong>M{{ number_format($data['disbursed'],2) }}</strong></div>
      <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:13px"><span>Write-Offs (Bad Debt)</span><strong style="color:var(--err)">M{{ number_format($data['writeOffs'],2) }}</strong></div>
      <div style="display:flex;justify-content:space-between;padding:12px 0;font-size:14px;font-weight:700;border-top:2px solid var(--border);margin-top:4px">
        <span>Net Profit</span>
        <span style="color:{{ $data['netProfit'] >= 0 ? 'var(--ok)' : 'var(--err)' }}">M{{ number_format($data['netProfit'],2) }}</span>
      </div>
    </div>
  </div>
</div>
<div class="card">
  <div class="card-hdr"><span class="card-title">Monthly Income Trend (Last 6 Months)</span></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>Month</th><th>Collections</th></tr></thead>
    <tbody>
    @foreach($data['trend'] as $t)
    <tr><td>{{ $t['month'] }}</td><td><strong>M{{ number_format($t['income'],0) }}</strong></td></tr>
    @endforeach
    </tbody>
  </table></div>
</div>
@endsection
