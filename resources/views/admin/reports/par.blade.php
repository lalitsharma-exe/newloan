@extends('admin.layouts.app')
@section('title','PAR Report')
@section('page-title','Portfolio at Risk (PAR)')
@section('bc')
<a href="{{ route('admin.reports.index') }}">Reports</a> / PAR
@endsection
@section('content')

<div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px 20px;margin-bottom:18px;font-size:13px;color:#374151">
  <i class="bi bi-info-circle-fill" style="color:var(--info)"></i>
  <strong>What is PAR?</strong> Portfolio at Risk measures what percentage of the loan portfolio has at least one overdue payment. PAR 30 means loans with 30+ days past due. Investors and regulators watch PAR 30 closely — keep it below 5%.
</div>

<div class="g4 mb6">
  @foreach($data['par'] as $label => $d)
  @php $color = $d['pct'] < 5 ? 'ok' : ($d['pct'] < 10 ? 'w' : 'e'); @endphp
  <div class="sc">
    <div class="si {{ $color }}"><i class="bi bi-shield-{{ $d['pct'] < 5 ? 'check' : 'exclamation' }}"></i></div>
    <div>
      <div class="sv" style="color:{{ $d['pct'] < 5 ? 'var(--ok)' : ($d['pct'] < 10 ? 'var(--warn)' : 'var(--err)') }}">{{ $d['pct'] }}%</div>
      <div class="sl">{{ $label }}</div>
      <div style="font-size:11px;color:var(--muted);margin-top:2px">M{{ number_format($d['amount'],0) }}</div>
    </div>
  </div>
  @endforeach
</div>

<div class="g2 mb6" style="gap:16px">
  <div class="card">
    <div class="card-hdr"><span class="card-title">PAR Summary</span></div>
    <div style="padding:14px 18px">
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px"><span class="muted">Total Portfolio</span><strong>M{{ number_format($data['totalPortfolio'],0) }}</strong></div>
      @foreach($data['par'] as $label => $d)
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px">
        <span>{{ $label }}</span>
        <div style="text-align:right">
          <strong>M{{ number_format($d['amount'],0) }}</strong>
          <span class="badge {{ $d['pct'] < 5 ? 'bok' : ($d['pct'] < 10 ? 'bw' : 'be') }}" style="margin-left:6px">{{ $d['pct'] }}%</span>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  <div class="card">
    <div class="card-hdr"><span class="card-title">PAR Benchmark Guide</span></div>
    <div style="padding:14px 18px;font-size:13px">
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border)"><span class="badge bok">Below 5%</span><span>Excellent — healthy portfolio</span></div>
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border)"><span class="badge bw">5% – 10%</span><span>Acceptable — monitor closely</span></div>
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border)"><span class="badge be">10% – 20%</span><span>Concerning — take action</span></div>
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0"><span class="badge be">Above 20%</span><span>Critical — immediate action</span></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-hdr"><span class="card-title">PAR 30+ Loans ({{ $data['par30Loans']->count() }})</span></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>Loan #</th><th>Borrower</th><th>Phone</th><th>Product</th><th>Outstanding</th><th>Status</th></tr></thead>
    <tbody>
    @forelse($data['par30Loans'] as $l)
    <tr>
      <td><a href="{{ route('admin.loans.show',$l) }}" style="font-weight:700;color:var(--p);font-size:12px">{{ $l->loan_number }}</a></td>
      <td style="font-weight:600;font-size:13px">{{ $l->user->name??'—' }}</td>
      <td style="color:var(--p)">{{ $l->user->phone??'—' }}</td>
      <td class="muted">{{ $l->loanProduct->name??'—' }}</td>
      <td style="font-weight:700;color:var(--err)">M{{ number_format($l->outstanding_balance,0) }}</td>
      <td><span class="badge be">{{ ucfirst($l->status) }}</span></td>
    </tr>
    @empty<tr><td colspan="6"><div class="empty"><i class="bi bi-shield-check"></i><p>No PAR 30 loans — portfolio is healthy!</p></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>
@endsection
