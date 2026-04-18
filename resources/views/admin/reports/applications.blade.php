@extends('admin.layouts.app')
@section('title','Application Report')
@section('page-title','Application Report')
@section('bc')
<a href="{{ route('admin.reports.index') }}">Reports</a> / Applications
@endsection
@section('content')
<div class="g4 mb6">
  <div class="sc"><div class="si i"><i class="bi bi-file-earmark-text-fill"></i></div><div><div class="sv">{{ $data['apps']->count() }}</div><div class="sl">Total in Period</div></div></div>
  <div class="sc"><div class="si ok"><i class="bi bi-check-circle-fill"></i></div><div><div class="sv">{{ $data['approved'] }}</div><div class="sl">Approved</div></div></div>
  <div class="sc"><div class="si e"><i class="bi bi-x-circle-fill"></i></div><div><div class="sv">{{ $data['declined'] }}</div><div class="sl">Declined</div></div></div>
  <div class="sc"><div class="si w"><i class="bi bi-hourglass-split"></i></div><div><div class="sv">{{ $data['pending'] }}</div><div class="sl">Pending Review</div></div></div>
</div>
<div class="g2 mb6" style="gap:16px">
  <div class="sc"><div class="si p"><i class="bi bi-percent"></i></div><div><div class="sv" style="color:{{ $data['approvalRate'] >= 70 ? 'var(--ok)' : 'var(--warn)' }}">{{ $data['approvalRate'] }}%</div><div class="sl">Approval Rate</div></div></div>
  <div class="sc"><div class="si s"><i class="bi bi-send-fill"></i></div><div><div class="sv">{{ $data['submitted'] }}</div><div class="sl">Under Review</div></div></div>
</div>
<form method="GET" class="filter-bar">
  <div class="fg" style="margin-bottom:0"><label class="fl">From</label><input type="date" name="date_from" class="fc" value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">To</label><input type="date" name="date_to" class="fc" value="{{ $filters['date_to'] ?? now()->format('Y-m-d') }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">Status</label>
    <select name="status" class="fc">
      <option value="">All</option>
      @foreach(['submitted','under_review','approved','declined','disbursed','on_hold'] as $s)
      <option value="{{ $s }}" {{ ($filters['status']??'')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
      @endforeach
    </select>
  </div>
  <div class="fg" style="margin-bottom:0"><label class="fl">Category</label>
    <select name="category" class="fc">
      <option value="">— All Categories —</option>
      @foreach(['Defence','Nss','Police','Lcs','Pensioner','Civil servants','Teacher'] as $cat)
        <option value="{{ $cat }}" {{ ($filters['category'] ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
      @endforeach
    </select>
  </div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p btn-sm"><i class="bi bi-funnel"></i> Filter</button><a href="{{ route('admin.reports.applications') }}" class="btn btn-o btn-sm">Clear</a></div>
</form>
<div class="card">
  <div class="card-hdr"><span class="card-title">Applications ({{ $data['apps']->count() }})</span><form method="POST" action="{{ route('admin.reports.export') }}" style="display:inline">@csrf<input type="hidden" name="type" value="applications"><button class="btn btn-o btn-sm"><i class="bi bi-download"></i> CSV</button></form></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>App #</th><th>Borrower</th><th>Product</th><th>Amount</th><th>Officer</th><th>Submitted</th><th>Status</th></tr></thead>
    <tbody>
    @forelse($data['apps'] as $a)
    @php $b=['submitted'=>'bp','under_review'=>'bi','approved'=>'bok','disbursed'=>'bok','declined'=>'be','on_hold'=>'bw','info_requested'=>'bw']; @endphp
    <tr>
      <td><a href="{{ route('admin.applications.show',$a) }}" style="font-weight:700;color:var(--p);font-size:12px">{{ $a->application_number }}</a></td>
      <td style="font-weight:600;font-size:13px">{{ $a->user->name??'—' }}</td>
      <td class="muted">{{ $a->loanProduct->name??'—' }}</td>
      <td>M{{ number_format($a->requested_amount,0) }}</td>
      <td class="muted">{{ $a->assignedOfficer->name??'Unassigned' }}</td>
      <td class="muted">{{ $a->created_at->format('d M Y') }}</td>
      <td><span class="badge {{ $b[$a->status]??'bs' }}">{{ ucfirst(str_replace('_',' ',$a->status)) }}</span></td>
    </tr>
    @empty<tr><td colspan="7"><div class="empty"><i class="bi bi-file-earmark-x"></i><p>No applications in period</p></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>
@endsection
