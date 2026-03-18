@extends('officer.layouts.app')
@section('title','Applications')
@section('page-title', $view === 'pending' ? 'Pending Review' : ($view === 'all' ? 'All Applications' : 'My Assigned Applications'))

@section('content')
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;align-items:center;justify-content:space-between">
  <div style="display:flex;gap:6px">
    <a href="{{ route('officer.applications.assigned') }}" class="btn btn-sm {{ $view==='assigned'?'btn-p':'btn-o' }}">My Assigned</a>
    <a href="{{ route('officer.applications.pending') }}"  class="btn btn-sm {{ $view==='pending'?'btn-p':'btn-o' }}">Pending Review</a>
    <a href="{{ route('officer.applications.all') }}"     class="btn btn-sm {{ $view==='all'?'btn-p':'btn-o' }}">All Applications</a>
  </div>
  <a href="{{ route('officer.walk-in.create') }}" class="btn btn-sm btn-p"><i class="bi bi-person-plus-fill"></i> Register Walk-in</a>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 20px">
    <form method="GET" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
      <div style="flex:1;min-width:200px">
        <input type="text" name="search" class="fc" placeholder="Search name, phone, app number..." value="{{ $filters['search'] ?? '' }}">
      </div>
      <div>
        <select name="status" class="fc" style="width:160px">
          <option value="">All Status</option>
          @foreach(['submitted'=>'Submitted','under_review'=>'Under Review','info_requested'=>'Info Requested','on_hold'=>'On Hold','approved'=>'Approved','declined'=>'Declined','disbursed'=>'Disbursed'] as $val=>$label)
          <option value="{{ $val }}" {{ ($filters['status']??'')===$val?'selected':'' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-sm btn-p"><i class="bi bi-search"></i> Filter</button>
      @php $clearRoute = match($view) { 'pending' => 'officer.applications.pending', 'all' => 'officer.applications.all', default => 'officer.applications.assigned' }; @endphp
      <a href="{{ route($clearRoute) }}" class="btn btn-sm btn-o">Clear</a>
    </form>
  </div>
</div>

<div class="card">
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr><th>App #</th><th>Applicant</th><th>Product</th><th>Amount</th><th>Submitted</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        @forelse($applications as $app)
        <tr>
          <td style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p)">{{ $app->application_number }}</td>
          <td>
            <div style="font-size:13px;font-weight:600">{{ $app->applicant_name }}</div>
            <div style="font-size:11.5px;color:var(--muted)">{{ $app->cell_number ?? $app->user?->phone }}</div>
          </td>
          <td style="font-size:12.5px;color:var(--muted)">{{ $app->loanProduct?->name ?? '—' }}</td>
          <td style="font-weight:700">M{{ number_format($app->requested_amount ?? 0, 0) }}</td>
          <td style="font-size:12px;color:var(--muted)">{{ $app->submitted_at?->format('d M Y') ?? $app->created_at->format('d M Y') }}</td>
          <td><span class="badge b{{ $app->status_badge }}">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span></td>
          <td><a href="{{ route('officer.applications.show', $app) }}" class="btn btn-xs btn-o"><i class="bi bi-eye"></i> Review</a></td>
        </tr>
        @empty
        <tr><td colspan="7"><div style="text-align:center;padding:50px;color:var(--muted)"><i class="bi bi-inbox" style="font-size:44px;opacity:.25;display:block;margin-bottom:12px"></i><div style="font-weight:600">No applications found</div></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($applications->hasPages())
  <div style="padding:14px 22px;border-top:1px solid var(--border)">{{ $applications->withQueryString()->links() }}</div>
  @endif
</div>
@endsection
