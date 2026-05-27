@extends('admin.layouts.app')
@section('page-title', 'Agent Details')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Dashboard</a> / <a href="{{ route('admin.agents.index') }}">Active Agents</a> / Agent Profile
@endsection
@section('content')

<div style="display:flex;gap:22px;flex-wrap:wrap;margin-bottom:22px">
  {{-- Profile Info Card --}}
  <div style="flex:1;min-width:360px">
    <div class="card" style="height:100%">
      <div class="card-hdr">
        <div class="card-title"><i class="bi bi-person-circle" style="color:var(--p)"></i> {{ $user->name }}</div>
        <span class="badge {{ $user->is_active ? 'bok' : 'be' }}">{{ $user->is_active ? 'Active' : 'Suspended' }}</span>
      </div>
      <div class="card-body">
        @php $profile = $user->agentProfile; @endphp
        <div class="info-grid" style="gap:16px;grid-template-columns:repeat(auto-fill, minmax(170px, 1fr))">
          <div><div class="info-lbl">Agent ID</div><div class="info-val" style="color:var(--p);font-size:16px;font-weight:800">{{ $profile->agent_id ?? 'N/A' }}</div></div>
          <div><div class="info-lbl">Email</div><div class="info-val">{{ $user->email }}</div></div>
          <div><div class="info-lbl">Phone</div><div class="info-val">{{ $user->phone }}</div></div>
          <div><div class="info-lbl">Agent Type</div><div class="info-val">{{ ucfirst($profile->agent_type ?? 'N/A') }}</div></div>
          @if(($profile->agent_type ?? '') === 'shop')
          <div><div class="info-lbl">Shop Name</div><div class="info-val">{{ $profile->shop_name }}</div></div>
          <div><div class="info-lbl">Business Type</div><div class="info-val">{{ $profile->business_type }}</div></div>
          @endif
          <div><div class="info-lbl">Location</div><div class="info-val">{{ $profile->shop_location ?? 'N/A' }}</div></div>
          <div><div class="info-lbl">Contract Reference</div><div class="info-val">{{ $profile->contract_ref ?? 'Pending Signature' }}</div></div>
          <div><div class="info-lbl">Signed At</div><div class="info-val">{{ $profile->signed_at?->format('d M Y H:i') ?? 'N/A' }}</div></div>
        </div>

        <hr style="margin:20px 0;border:none;border-top:1px solid var(--border)">
        
        <div style="font-size:13px;font-weight:700;margin-bottom:10px"><i class="bi bi-credit-card"></i> Payout Settings</div>
        <div class="info-grid" style="gap:16px;grid-template-columns:repeat(auto-fill, minmax(170px, 1fr))">
          <div><div class="info-lbl">Method</div><div class="info-val">{{ $profile->payout_method ?? 'N/A' }}</div></div>
          <div><div class="info-lbl">Account Holder</div><div class="info-val">{{ $profile->payout_account_name ?? 'N/A' }}</div></div>
          @if($profile->payout_bank_name)
          <div><div class="info-lbl">Bank Name</div><div class="info-val">{{ $profile->payout_bank_name }}</div></div>
          @endif
          <div><div class="info-lbl">Payout Details</div><div class="info-val">{{ $profile->payout_number_or_details ?? 'N/A' }}</div></div>
        </div>

        <div style="margin-top:24px;display:flex;gap:10px">
          <form method="POST" action="{{ route('admin.agents.toggle-status', $user) }}">
            @csrf
            <button type="submit" class="btn {{ $user->is_active ? 'btn-e' : 'btn-ok' }}" onclick="return confirm('Toggle status?')">
              <i class="bi {{ $user->is_active ? 'bi-person-x-fill' : 'bi-person-check-fill' }}"></i> 
              {{ $user->is_active ? 'Suspend Agent Account' : 'Activate Agent Account' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Performance Card --}}
  <div style="width:340px;flex-shrink:0">
    <div class="card" style="height:100%">
      <div class="card-hdr"><div class="card-title"><i class="bi bi-graph-up-arrow" style="color:var(--p)"></i> Performance Statistics</div></div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:16px">
        <div style="background:#f0fdf4;border:1px solid #10b981;border-radius:12px;padding:16px">
          <div style="font-size:12px;color:#065f46;font-weight:600">TOTAL COMMISSION EARNED</div>
          <div style="font-size:32px;font-weight:800;color:#047857;line-height:1.2;margin-top:4px">
            M{{ number_format($profile->total_earned ?? 0, 2) }}
          </div>
          <div style="font-size:11px;color:#059669;margin-top:4px">M50 per paid repayment</div>
        </div>

        <div style="background:#fffbeb;border:1px solid #f59e0b;border-radius:12px;padding:16px">
          <div style="font-size:12px;color:#92400e;font-weight:600">PENDING COMMISSION</div>
          <div style="font-size:24px;font-weight:800;color:#b45309;line-height:1.2;margin-top:4px">
            M{{ number_format($profile->pending_earnings ?? 0, 2) }}
          </div>
          <div style="font-size:11px;color:#d97706;margin-top:4px">Awaiting first repayment</div>
        </div>

        <div class="g2" style="gap:10px">
          <div style="border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center">
            <div style="font-size:20px;font-weight:700;color:var(--p)">{{ $stats['total_apps'] }}</div>
            <div style="font-size:11px;color:var(--muted)">Submissions</div>
          </div>
          <div style="border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center">
            <div style="font-size:20px;font-weight:700;color:#10b981">{{ $stats['approved'] + $stats['disbursed'] }}</div>
            <div style="font-size:11px;color:var(--muted)">Approved</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Client Applications List --}}
<div class="card">
  <div class="card-hdr">
    <div class="card-title"><i class="bi bi-collection-fill" style="color:var(--p)"></i> Client Submissions by {{ $user->name }}</div>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Reference #</th>
          <th>Applicant Name</th>
          <th>National ID</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Date Submitted</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($applications as $app)
        <tr>
          <td style="font-weight:600;color:var(--p)">{{ $app->application_number }}</td>
          <td>
            <div style="font-weight:600">{{ $app->applicant_name }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $app->cell_number }}</div>
          </td>
          <td>{{ $app->national_id }}</td>
          <td style="font-weight:600">M{{ number_format($app->requested_amount, 2) }}</td>
          <td>
            @php
              $bc = match($app->status) {
                'approved','disbursed' => 'bok',
                'declined' => 'be',
                'under_review' => 'bp',
                'info_requested' => 'bw',
                default => 'bs',
              };
            @endphp
            <span class="badge {{ $bc }}">{{ ucfirst(str_replace('_', ' ', $app->status)) }}</span>
          </td>
          <td style="font-size:12px;color:var(--muted)">{{ $app->submitted_at?->format('d M Y') ?? '-' }}</td>
          <td>
            <a href="{{ route('admin.applications.show', $app) }}" class="btn btn-o btn-xs">View Application</a>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="empty" style="padding:40px"><i class="bi bi-folder-x"></i><br>No client applications submitted by this agent.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($applications->hasPages())
  <div style="padding:16px 22px;border-top:1px solid var(--border)">{{ $applications->links() }}</div>
  @endif
</div>

@endsection
