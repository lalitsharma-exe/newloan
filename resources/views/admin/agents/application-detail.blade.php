@extends('admin.layouts.app')
@section('page-title', 'Application Detail')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Dashboard</a> / <a href="{{ route('admin.agents.applications') }}">Agent Applications</a> / {{ $application->application_ref }}
@endsection
@section('content')

<div style="display:flex;gap:22px;flex-wrap:wrap">
  {{-- Left: Applicant Details --}}
  <div style="flex:1;min-width:400px">
    <div class="card" style="margin-bottom:22px">
      <div class="card-hdr">
        <div class="card-title"><i class="bi bi-person-fill" style="color:#0f766e"></i> {{ $application->application_ref }}</div>
        @php
          $bc = match($application->status) {
            'approved' => 'bok', 'rejected' => 'be', 'pending' => 'bw',
            'documents_requested' => 'bi', default => 'bs',
          };
        @endphp
        <span class="badge {{ $bc }}">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
      </div>
      <div class="card-body">
        <div class="info-grid" style="gap:16px">
          <div><div class="info-lbl">First Name</div><div class="info-val">{{ $application->first_name }}</div></div>
          <div><div class="info-lbl">Last Name</div><div class="info-val">{{ $application->last_name }}</div></div>
          <div><div class="info-lbl">National ID</div><div class="info-val">{{ $application->national_id }}</div></div>
          <div><div class="info-lbl">Mobile</div><div class="info-val">{{ $application->mobile_number }}</div></div>
          <div><div class="info-lbl">Agent Type</div><div class="info-val">{{ ucfirst($application->agent_type) }}</div></div>
          <div><div class="info-lbl">Location</div><div class="info-val">{{ $application->shop_location }}</div></div>
          @if($application->shop_name)
          <div><div class="info-lbl">Shop Name</div><div class="info-val">{{ $application->shop_name }}</div></div>
          @endif
          @if($application->business_type)
          <div><div class="info-lbl">Business Type</div><div class="info-val">{{ $application->business_type }}</div></div>
          @endif
          <div><div class="info-lbl">Applied On</div><div class="info-val">{{ $application->created_at->format('d M Y H:i') }}</div></div>
        </div>
      </div>
    </div>

    {{-- Payout Setup --}}
    <div class="card" style="margin-bottom:22px">
      <div class="card-hdr"><div class="card-title"><i class="bi bi-wallet2" style="color:#0f766e"></i> Payout Details</div></div>
      <div class="card-body">
        <div class="info-grid">
          <div><div class="info-lbl">Method</div><div class="info-val">{{ $application->payout_method }}</div></div>
          <div><div class="info-lbl">Account Name</div><div class="info-val">{{ $application->payout_account_name }}</div></div>
          @if($application->payout_number_or_details)
          <div><div class="info-lbl">Number/Details</div><div class="info-val">{{ $application->payout_number_or_details }}</div></div>
          @endif
          @if($application->payout_bank_name)
          <div><div class="info-lbl">Bank</div><div class="info-val">{{ $application->payout_bank_name }}</div></div>
          @endif
        </div>
      </div>
    </div>

    {{-- Uploaded Documents --}}
    <div class="card">
      <div class="card-hdr"><div class="card-title"><i class="bi bi-images" style="color:#0f766e"></i> Uploaded Documents</div></div>
      <div class="card-body">
        <div class="g3" style="gap:14px">
          @if($application->national_id_path)
          <div style="border:1px solid var(--border);border-radius:12px;overflow:hidden">
            <img src="{{ Storage::url($application->national_id_path) }}" alt="National ID" style="width:100%;height:150px;object-fit:cover" onerror="this.src='https://ui-avatars.com/api/?name=ID&bg=d1e7dd&color=0f766e&size=300'">
            <div style="padding:10px;text-align:center;font-size:12px;font-weight:600;color:var(--muted)">National ID</div>
          </div>
          @endif
          @if($application->selfie_holding_id_path)
          <div style="border:1px solid var(--border);border-radius:12px;overflow:hidden">
            <img src="{{ Storage::url($application->selfie_holding_id_path) }}" alt="Selfie" style="width:100%;height:150px;object-fit:cover" onerror="this.src='https://ui-avatars.com/api/?name=Selfie&bg=d1e7dd&color=0f766e&size=300'">
            <div style="padding:10px;text-align:center;font-size:12px;font-weight:600;color:var(--muted)">Selfie Holding ID</div>
          </div>
          @endif
          @if($application->business_licence_path)
          <div style="border:1px solid var(--border);border-radius:12px;overflow:hidden">
            <img src="{{ Storage::url($application->business_licence_path) }}" alt="Licence" style="width:100%;height:150px;object-fit:cover" onerror="this.src='https://ui-avatars.com/api/?name=Licence&bg=d1e7dd&color=0f766e&size=300'">
            <div style="padding:10px;text-align:center;font-size:12px;font-weight:600;color:var(--muted)">Business Licence</div>
          </div>
          @endif
        </div>
        @if(!$application->national_id_path && !$application->selfie_holding_id_path)
        <div class="empty"><i class="bi bi-images"></i><br>No documents uploaded</div>
        @endif
      </div>
    </div>
  </div>

  {{-- Right: Actions Panel --}}
  <div style="width:340px;flex-shrink:0">
    @if($application->status === 'pending' || $application->status === 'documents_requested')
    {{-- Approve --}}
    <div class="card" style="margin-bottom:16px;border-color:rgba(22,163,74,.3)">
      <div class="card-hdr" style="background:rgba(22,163,74,.04)">
        <div class="card-title" style="color:#065f46"><i class="bi bi-check-circle-fill"></i> Approve Agent</div>
      </div>
      <div class="card-body">
        <p style="font-size:12.5px;color:var(--muted);margin-bottom:14px">
          This will create a <strong>user account</strong> (role=agent), generate an <strong>AGT-XXXX</strong> agent ID,
          and create their <strong>agent profile</strong>. Login credentials will be generated automatically.
        </p>
        <form method="POST" action="{{ route('admin.agents.applications.approve', $application) }}">
          @csrf
          <button type="submit" class="btn btn-ok" style="width:100%;justify-content:center" onclick="return confirm('Approve this agent? This will create their login account.')">
            <i class="bi bi-check-lg"></i> Approve & Create Account
          </button>
        </form>
      </div>
    </div>

    {{-- Request Documents --}}
    <div class="card" style="margin-bottom:16px;border-color:rgba(6,182,212,.3)">
      <div class="card-hdr" style="background:rgba(6,182,212,.04)">
        <div class="card-title" style="color:#0c4a6e"><i class="bi bi-file-earmark-arrow-up"></i> Request Documents</div>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.agents.applications.request-documents', $application) }}">
          @csrf
          <div class="fg">
            <label class="fl">Message to Applicant</label>
            <textarea name="feedback" class="fc" rows="3" placeholder="e.g. Please re-upload a clearer copy of your national ID." required></textarea>
          </div>
          <button type="submit" class="btn btn-i" style="width:100%;justify-content:center">
            <i class="bi bi-envelope"></i> Send Request
          </button>
        </form>
      </div>
    </div>

    {{-- Reject --}}
    <div class="card" style="border-color:rgba(239,68,68,.3)">
      <div class="card-hdr" style="background:rgba(239,68,68,.04)">
        <div class="card-title" style="color:#991b1b"><i class="bi bi-x-circle-fill"></i> Reject Application</div>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.agents.applications.reject', $application) }}">
          @csrf
          <div class="fg">
            <label class="fl">Reason for Rejection</label>
            <textarea name="feedback" class="fc" rows="3" placeholder="e.g. Incomplete documentation, invalid ID." required></textarea>
          </div>
          <button type="submit" class="btn btn-e" style="width:100%;justify-content:center" onclick="return confirm('Reject this application?')">
            <i class="bi bi-x-lg"></i> Reject
          </button>
        </form>
      </div>
    </div>
    @endif

    @if($application->status === 'approved')
    <div class="card" style="border-color:rgba(22,163,74,.3)">
      <div class="card-body" style="text-align:center">
        <div style="width:56px;height:56px;background:rgba(22,163,74,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#10b981"><i class="bi bi-check-circle-fill"></i></div>
        <div style="font-size:16px;font-weight:700;color:#065f46;margin-bottom:6px">Approved</div>
        <div style="font-size:12.5px;color:var(--muted)">This agent has been approved and their account has been created.</div>
      </div>
    </div>
    @endif

    @if($application->status === 'rejected')
    <div class="card" style="border-color:rgba(239,68,68,.3)">
      <div class="card-body" style="text-align:center">
        <div style="width:56px;height:56px;background:rgba(239,68,68,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:#ef4444"><i class="bi bi-x-circle-fill"></i></div>
        <div style="font-size:16px;font-weight:700;color:#991b1b;margin-bottom:6px">Rejected</div>
        @if($application->admin_feedback)
        <div style="font-size:12.5px;color:var(--muted);background:#fef2f2;border-radius:8px;padding:10px;margin-top:8px;text-align:left">
          <strong>Reason:</strong> {{ $application->admin_feedback }}
        </div>
        @endif
      </div>
    </div>
    @endif
  </div>
</div>

@endsection
