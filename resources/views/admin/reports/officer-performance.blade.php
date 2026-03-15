@extends('admin.layouts.app')
@section('title','Officer Performance')
@section('page-title','Officer Performance Report')
@section('bc')
<a href="{{ route('admin.reports.index') }}">Reports</a> / Officer Performance
@endsection
@section('content')
<form method="GET" class="filter-bar">
  <div class="fg" style="margin-bottom:0"><label class="fl">From</label><input type="date" name="date_from" class="fc" value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}"></div>
  <div class="fg" style="margin-bottom:0"><label class="fl">To</label><input type="date" name="date_to" class="fc" value="{{ $filters['date_to'] ?? now()->format('Y-m-d') }}"></div>
  <div class="flex gap2 aic" style="align-self:flex-end"><button type="submit" class="btn btn-p btn-sm"><i class="bi bi-funnel"></i> Filter</button><a href="{{ route('admin.reports.officer-performance') }}" class="btn btn-o btn-sm">Clear</a></div>
</form>
<div class="card">
  <div class="card-hdr"><span class="card-title">Officer Performance Summary</span></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead>
      <tr>
        <th>Officer</th><th>Total Apps</th><th>Approved</th><th>Declined</th>
        <th>Active Loans</th><th>Portfolio Value</th><th>Collection Rate</th><th>Default Rate</th>
      </tr>
    </thead>
    <tbody>
    @forelse($data['officers'] as $row)
    <tr>
      <td>
        <div style="font-weight:700;font-size:13px">{{ $row['officer']->name??'—' }}</div>
        <div class="muted">{{ $row['officer']->email??'' }}</div>
      </td>
      <td><strong>{{ $row['total_apps'] }}</strong></td>
      <td><span class="badge bok">{{ $row['approved'] }}</span></td>
      <td><span class="badge be">{{ $row['declined'] }}</span></td>
      <td>{{ $row['active_loans'] }}</td>
      <td><strong>M{{ number_format($row['portfolio'],0) }}</strong></td>
      <td>
        <span class="badge {{ $row['coll_rate'] >= 90 ? 'bok' : ($row['coll_rate'] >= 70 ? 'bw' : 'be') }}">
          {{ $row['coll_rate'] }}%
        </span>
      </td>
      <td>
        <span class="badge {{ $row['def_rate'] < 3 ? 'bok' : ($row['def_rate'] < 8 ? 'bw' : 'be') }}">
          {{ $row['def_rate'] }}%
        </span>
      </td>
    </tr>
    @empty
    <tr><td colspan="8"><div class="empty"><i class="bi bi-person-badge"></i><p>No loan officers found</p></div></td></tr>
    @endforelse
    </tbody>
  </table></div>
</div>
@endsection
