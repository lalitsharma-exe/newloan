@extends('admin.layouts.app')
@section('page-title', 'Active Agents')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Dashboard</a> / Active Agents
@endsection
@section('content')

{{-- Stats Row --}}
<div class="g4" style="margin-bottom:22px">
  <div class="sc">
    <div class="si ok"><i class="bi bi-people-fill"></i></div>
    <div><div class="sv">{{ $stats['total_active'] }}</div><div class="sl">Active Agents</div></div>
  </div>
  <div class="sc">
    <div class="si e"><i class="bi bi-person-x-fill"></i></div>
    <div><div class="sv">{{ $stats['total_inactive'] }}</div><div class="sl">Suspended</div></div>
  </div>
  <div class="sc">
    <div class="si" style="background:rgba(45,212,191,.1);color:#0f766e"><i class="bi bi-shop"></i></div>
    <div><div class="sv">{{ $stats['total_shops'] }}</div><div class="sl">Shop Owners</div></div>
  </div>
  <div class="sc">
    <div class="si" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="bi bi-person-badge"></i></div>
    <div><div class="sv">{{ $stats['total_individual'] }}</div><div class="sl">Individuals</div></div>
  </div>
</div>

{{-- Filters --}}
<div class="filter-bar">
  <form method="GET" action="{{ route('admin.agents.index') }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;width:100%">
    <div class="fg" style="flex:1;min-width:200px">
      <label class="fl">Search Agents</label>
      <input type="text" name="search" class="fc" value="{{ request('search') }}" placeholder="Name, Agent ID, Phone, Email...">
    </div>
    <button type="submit" class="btn btn-p btn-sm"><i class="bi bi-search"></i> Search</button>
    @if(request('search'))
    <a href="{{ route('admin.agents.index') }}" class="btn btn-o btn-sm">Clear</a>
    @endif
  </form>
</div>

{{-- Table --}}
<div class="card">
  <div class="card-hdr"><div class="card-title">Registered Agents</div></div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Agent ID</th>
          <th>Name</th>
          <th>Type</th>
          <th>Shop/Location</th>
          <th>Payout Method</th>
          <th>Total Earned</th>
          <th>Pending</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($agents as $agent)
        @php $profile = $agent->agentProfile; @endphp
        <tr>
          <td style="font-weight:700;color:var(--p)">{{ $profile->agent_id ?? 'N/A' }}</td>
          <td>
            <div style="font-weight:600">{{ $agent->name }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $agent->email }} | {{ $agent->phone }}</div>
          </td>
          <td>
            @if(($profile->agent_type ?? '') === 'shop')
            <span class="badge" style="background:rgba(45,212,191,.1);color:#0f766e"><i class="bi bi-shop"></i> Shop</span>
            @else
            <span class="badge" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="bi bi-person-badge"></i> Individual</span>
            @endif
          </td>
          <td>
            @if(($profile->agent_type ?? '') === 'shop')
            <div style="font-weight:500">{{ $profile->shop_name }}</div>
            @endif
            <div style="font-size:12px;color:var(--muted)">{{ $profile->shop_location ?? 'N/A' }}</div>
          </td>
          <td>
            <div style="font-weight:500">{{ $profile->payout_method ?? 'N/A' }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $profile->payout_number_or_details ?? 'N/A' }}</div>
          </td>
          <td style="font-weight:700;color:#10b981">M{{ number_format($profile->total_earned ?? 0, 2) }}</td>
          <td style="font-weight:600;color:#f59e0b">M{{ number_format($profile->pending_earnings ?? 0, 2) }}</td>
          <td>
            <span class="badge {{ $agent->is_active ? 'bok' : 'be' }}">
              {{ $agent->is_active ? 'Active' : 'Suspended' }}
            </span>
          </td>
          <td>
            <div style="display:flex;gap:6px">
              <a href="{{ route('admin.agents.show', $agent) }}" class="btn btn-o btn-xs"><i class="bi bi-eye"></i> View</a>
              <form method="POST" action="{{ route('admin.agents.toggle-status', $agent) }}" style="display:inline">
                @csrf
                <button type="submit" class="btn {{ $agent->is_active ? 'btn-e' : 'btn-ok' }} btn-xs" onclick="return confirm('Toggle status for this agent?')">
                  {{ $agent->is_active ? 'Suspend' : 'Activate' }}
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="9" class="empty" style="padding:40px"><i class="bi bi-people"></i><br>No active agents found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($agents->hasPages())
  <div style="padding:16px 22px;border-top:1px solid var(--border)">{{ $agents->links() }}</div>
  @endif
</div>

@endsection
