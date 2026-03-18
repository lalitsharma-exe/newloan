@extends('officer.layouts.app')
@section('title','My Clients')
@section('page-title','My Clients')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
  <div style="font-size:13.5px;color:var(--muted)">Borrowers assigned to you</div>
  <a href="{{ route('officer.walk-in.create') }}" class="btn btn-p"><i class="bi bi-person-plus-fill"></i> Register Walk-in Client</a>
</div>

{{-- Search --}}
<div class="card" style="margin-bottom:16px">
  <div class="card-body" style="padding:14px 20px">
    <form method="GET" style="display:flex;gap:10px">
      <input type="text" name="search" class="fc" placeholder="Search name, phone, national ID..." value="{{ $filters['search'] ?? '' }}" style="flex:1">
      <button type="submit" class="btn btn-p btn-sm"><i class="bi bi-search"></i> Search</button>
      @if(!empty($filters['search']))
      <a href="{{ route('officer.clients.index') }}" class="btn btn-o btn-sm">Clear</a>
      @endif
    </form>
  </div>
</div>

<div class="card">
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Client</th>
          <th>National ID</th>
          <th>Phone</th>
          <th>Registered</th>
          <th>Applications</th>
          <th>Last Activity</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($clients as $client)
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0">
                {{ strtoupper(substr($client->name,0,1)) }}
              </div>
              <div>
                <div style="font-weight:600;font-size:13px">{{ $client->name }}</div>
                <div style="font-size:11.5px;color:var(--muted)">{{ $client->email ?? '—' }}</div>
              </div>
            </div>
          </td>
          <td style="font-family:monospace;font-size:12.5px">{{ $client->national_id ?? '—' }}</td>
          <td style="font-size:13px">{{ $client->phone ?? '—' }}</td>
          <td style="font-size:12px;color:var(--muted)">{{ $client->created_at->format('d M Y') }}</td>
          <td>
            @php $appCount = $client->loanApplications->count(); @endphp
            @if($appCount > 0)
            <span class="badge bp">{{ $appCount }} app{{ $appCount>1?'s':'' }}</span>
            @else
            <span class="badge bs">None</span>
            @endif
          </td>
          <td style="font-size:12px;color:var(--muted)">
            @php $lastApp = $client->loanApplications->first(); @endphp
            @if($lastApp)
            <span class="badge b{{ $lastApp->status_badge }}" style="font-size:10px">{{ ucfirst(str_replace('_',' ',$lastApp->status)) }}</span>
            @else
            —
            @endif
          </td>
          <td>
            <div style="display:flex;gap:5px">
              <a href="{{ route('officer.clients.show', $client) }}" class="btn btn-xs btn-o"><i class="bi bi-eye"></i> View</a>
              <a href="{{ route('officer.walk-in.apply', $client) }}" class="btn btn-xs btn-p"><i class="bi bi-plus"></i> New App</a>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7">
            <div style="text-align:center;padding:60px;color:var(--muted)">
              <i class="bi bi-people" style="font-size:48px;opacity:.2;display:block;margin-bottom:12px"></i>
              <div style="font-weight:600;margin-bottom:6px">No clients found</div>
              <div style="font-size:13px;margin-bottom:16px">{{ !empty($filters['search']) ? 'Try a different search term.' : 'Register your first walk-in client to get started.' }}</div>
              @if(empty($filters['search']))
              <a href="{{ route('officer.walk-in.create') }}" class="btn btn-p btn-sm"><i class="bi bi-person-plus-fill"></i> Register Walk-in</a>
              @endif
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($clients->hasPages())
  <div style="padding:14px 22px;border-top:1px solid var(--border)">{{ $clients->withQueryString()->links() }}</div>
  @endif
</div>
@endsection
