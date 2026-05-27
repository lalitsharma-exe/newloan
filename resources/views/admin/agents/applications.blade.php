@extends('admin.layouts.app')
@section('page-title', 'Agent Applications')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Dashboard</a> / Agent Applications
@endsection
@section('content')

{{-- Stats Row --}}
<div class="g4" style="margin-bottom:22px">
  <div class="sc">
    <div class="si" style="background:rgba(45,212,191,.1);color:#0f766e"><i class="bi bi-inbox-fill"></i></div>
    <div><div class="sv">{{ $counts['total'] }}</div><div class="sl">Total Applications</div></div>
  </div>
  <div class="sc">
    <div class="si w"><i class="bi bi-hourglass-split"></i></div>
    <div><div class="sv">{{ $counts['pending'] }}</div><div class="sl">Pending Review</div></div>
  </div>
  <div class="sc">
    <div class="si ok"><i class="bi bi-check-circle-fill"></i></div>
    <div><div class="sv">{{ $counts['approved'] }}</div><div class="sl">Approved</div></div>
  </div>
  <div class="sc">
    <div class="si e"><i class="bi bi-x-circle-fill"></i></div>
    <div><div class="sv">{{ $counts['rejected'] }}</div><div class="sl">Rejected</div></div>
  </div>
</div>

{{-- Filters --}}
<div class="filter-bar">
  <form method="GET" action="{{ route('admin.agents.applications') }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;width:100%">
    <div class="fg" style="min-width:200px">
      <label class="fl">Search</label>
      <input type="text" name="search" class="fc" value="{{ request('search') }}" placeholder="Name, ID, reference…">
    </div>
    <div class="fg" style="min-width:140px">
      <label class="fl">Status</label>
      <select name="status" class="fc">
        <option value="">All</option>
        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
        <option value="documents_requested" {{ request('status') == 'documents_requested' ? 'selected' : '' }}>Docs Requested</option>
      </select>
    </div>
    <div class="fg" style="min-width:140px">
      <label class="fl">Type</label>
      <select name="type" class="fc">
        <option value="">All</option>
        <option value="shop" {{ request('type') == 'shop' ? 'selected' : '' }}>Shop Owner</option>
        <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>Individual</option>
      </select>
    </div>
    <button type="submit" class="btn btn-p btn-sm"><i class="bi bi-search"></i> Filter</button>
    @if(request()->hasAny(['search','status','type']))
    <a href="{{ route('admin.agents.applications') }}" class="btn btn-o btn-sm">Clear</a>
    @endif
  </form>
</div>

{{-- Table --}}
<div class="card">
  <div class="card-hdr"><div class="card-title">Agent Registration Applications</div></div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr><th>Ref</th><th>Name</th><th>Type</th><th>Location</th><th>Mobile</th><th>Status</th><th>Applied</th><th></th></tr>
      </thead>
      <tbody>
        @forelse($applications as $app)
        <tr>
          <td><a href="{{ route('admin.agents.applications.show', $app) }}" style="color:var(--p);font-weight:600;text-decoration:none">{{ $app->application_ref }}</a></td>
          <td>{{ $app->first_name }} {{ $app->last_name }}</td>
          <td>
            @if($app->agent_type === 'shop')
            <span class="badge" style="background:rgba(45,212,191,.1);color:#0f766e"><i class="bi bi-shop"></i> Shop</span>
            @else
            <span class="badge" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="bi bi-person-badge"></i> Individual</span>
            @endif
          </td>
          <td>{{ $app->shop_location }}</td>
          <td>{{ $app->mobile_number }}</td>
          <td>
            @php
              $bc = match($app->status) {
                'approved' => 'bok',
                'rejected' => 'be',
                'pending' => 'bw',
                'documents_requested' => 'bi',
                default => 'bs',
              };
            @endphp
            <span class="badge {{ $bc }}">{{ ucfirst(str_replace('_', ' ', $app->status)) }}</span>
          </td>
          <td style="font-size:12px;color:var(--muted)">{{ $app->created_at->format('d M Y') }}</td>
          <td>
            <a href="{{ route('admin.agents.applications.show', $app) }}" class="btn btn-o btn-xs">Review</a>
          </td>
        </tr>
        @empty
        <tr><td colspan="8" class="empty" style="padding:40px"><i class="bi bi-inbox"></i><br>No agent applications yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($applications->hasPages())
  <div style="padding:16px 22px;border-top:1px solid var(--border)">{{ $applications->links() }}</div>
  @endif
</div>

@endsection
