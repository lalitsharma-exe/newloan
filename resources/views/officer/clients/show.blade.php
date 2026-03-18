@extends('officer.layouts.app')
@section('title', $client->name)
@section('page-title','Client Profile')
@section('bc','<a href="'.route('officer.clients.index').'">Clients</a> / '.$client->name)

@section('content')

{{-- Client header --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:22px 26px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
  <div style="display:flex;align-items:center;gap:16px">
    <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:22px;flex-shrink:0">
      {{ strtoupper(substr($client->name,0,1)) }}
    </div>
    <div>
      <div style="font-size:20px;font-weight:800">{{ $client->name }}</div>
      <div style="font-size:13px;color:var(--muted);margin-top:3px">
        {{ $client->phone }}
        @if($client->national_id) &nbsp;·&nbsp; ID: {{ $client->national_id }} @endif
        @if($client->email) &nbsp;·&nbsp; {{ $client->email }} @endif
      </div>
      <div style="margin-top:6px;display:flex;gap:6px">
        <span class="badge {{ $client->is_active ? 'bok' : 'be' }}">{{ $client->is_active ? 'Active' : 'Inactive' }}</span>
        <span class="badge bs">Borrower since {{ $client->created_at->format('M Y') }}</span>
      </div>
    </div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a href="{{ route('officer.walk-in.apply', $client) }}" class="btn btn-p btn-sm"><i class="bi bi-plus-circle"></i> New Application</a>
  </div>
</div>

{{-- Stats row --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
  @php
  $totalApps  = $client->loanApplications->count();
  $activeLoans = $client->loans->where('status','active')->count();
  $totalBorrowed = $client->loans->sum('amount');
  $totalPaid = $client->loans->flatMap->payments->sum('amount');
  @endphp
  @foreach([['Total Applications',$totalApps,'file-earmark-person','p'],['Active Loans',$activeLoans,'cash-coin','ok'],['Total Borrowed','M'.number_format($totalBorrowed,0),'currency-dollar','i'],['Total Paid','M'.number_format($totalPaid,0),'check-circle','ok']] as [$lbl,$val,$icon,$cls])
  <div class="sc">
    <div class="si {{ $cls }}"><i class="bi bi-{{ $icon }}"></i></div>
    <div><div class="sv" style="font-size:20px">{{ $val }}</div><div class="sl">{{ $lbl }}</div></div>
  </div>
  @endforeach
</div>

{{-- Tabs --}}
<div class="tabs">
  <button class="tab active" onclick="switchTab('cl','profile')"><i class="bi bi-person"></i> Profile</button>
  <button class="tab" onclick="switchTab('cl','apps')"><i class="bi bi-file-earmark-person"></i> Applications <span style="background:var(--bg);color:var(--muted);font-size:10px;padding:1px 6px;border-radius:10px;margin-left:3px">{{ $totalApps }}</span></button>
  <button class="tab" onclick="switchTab('cl','loans')"><i class="bi bi-bank"></i> Loans <span style="background:var(--bg);color:var(--muted);font-size:10px;padding:1px 6px;border-radius:10px;margin-left:3px">{{ $client->loans->count() }}</span></button>
  <button class="tab" onclick="switchTab('cl','docs')"><i class="bi bi-files"></i> Documents</button>
  <button class="tab" onclick="switchTab('cl','notes')"><i class="bi bi-chat-text"></i> Notes</button>
</div>

{{-- PROFILE TAB --}}
<div class="tpanel active" data-pg="cl" data-p="profile">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div class="card">
      <div class="card-hdr"><span class="card-title">Personal Details</span></div>
      <div class="card-body">
        @foreach(['Name'=>$client->name,'Phone'=>$client->phone,'Email'=>$client->email??'—','National ID'=>$client->national_id??'—','Date of Birth'=>$client->date_of_birth?->format('d M Y')??'—','Address'=>$client->address??'—','Member Since'=>$client->created_at->format('d M Y')] as $l=>$v)
        <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:13px;border-bottom:1px solid var(--border)">
          <span style="color:var(--muted);font-weight:500">{{ $l }}</span><strong>{{ $v }}</strong>
        </div>
        @endforeach
      </div>
    </div>

    @php $latestApp = $client->loanApplications->first(); $emp = $latestApp?->employment; $bank = $latestApp?->bankDetails; @endphp

    <div style="display:flex;flex-direction:column;gap:16px">
      @if($emp)
      <div class="card">
        <div class="card-hdr"><span class="card-title"><i class="bi bi-briefcase" style="color:var(--p)"></i> Employment</span></div>
        <div class="card-body" style="padding:14px 18px">
          @foreach(['Employer'=>$emp->employer_name,'Type'=>ucfirst($emp->employer_type??''),'Job Title'=>$emp->job_title,'Department'=>$emp->department??'—'] as $l=>$v)
          <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
            <span style="color:var(--muted)">{{ $l }}</span><strong>{{ $v }}</strong>
          </div>
          @endforeach
        </div>
      </div>
      @endif

      @if($bank)
      <div class="card">
        <div class="card-hdr"><span class="card-title"><i class="bi bi-bank2" style="color:var(--p)"></i> Bank Details</span></div>
        <div class="card-body" style="padding:14px 18px">
          @foreach(['Bank'=>$bank->bank_name,'Account Name'=>$bank->account_holder_name,'Account #'=>$bank->account_number,'Type'=>ucfirst($bank->account_type??'')] as $l=>$v)
          <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
            <span style="color:var(--muted)">{{ $l }}</span><strong>{{ $v }}</strong>
          </div>
          @endforeach
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

{{-- APPLICATIONS TAB --}}
<div class="tpanel" data-pg="cl" data-p="apps">
  <div class="card">
    <div class="card-hdr">
      <span class="card-title">Loan Applications</span>
      <a href="{{ route('officer.walk-in.apply', $client) }}" class="btn btn-sm btn-p"><i class="bi bi-plus"></i> New Application</a>
    </div>
    <div style="overflow-x:auto">
      <table class="dt">
        <thead>
          <tr><th>App #</th><th>Product</th><th>Amount</th><th>Term</th><th>Submitted</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          @forelse($client->loanApplications as $app)
          <tr>
            <td style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p)">{{ $app->application_number }}</td>
            <td style="font-size:12.5px">{{ $app->loanProduct?->name ?? '—' }}</td>
            <td style="font-weight:700">M{{ number_format($app->requested_amount??0,0) }}</td>
            <td style="font-size:12.5px;color:var(--muted)">{{ $app->requested_term ?? '—' }} mo</td>
            <td style="font-size:12px;color:var(--muted)">{{ $app->submitted_at?->format('d M Y') ?? $app->created_at->format('d M Y') }}</td>
            <td><span class="badge b{{ $app->status_badge }}">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span></td>
            <td><a href="{{ route('officer.applications.show', $app) }}" class="btn btn-xs btn-o"><i class="bi bi-eye"></i> View</a></td>
          </tr>
          @empty
          <tr><td colspan="7"><div style="text-align:center;padding:40px;color:var(--muted)">No applications yet</div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- LOANS TAB --}}
<div class="tpanel" data-pg="cl" data-p="loans">
  <div class="card">
    <div class="card-hdr"><span class="card-title">Loan Accounts</span></div>
    <div style="overflow-x:auto">
      <table class="dt">
        <thead>
          <tr><th>Loan #</th><th>Product</th><th>Amount</th><th>Balance</th><th>Disbursed</th><th>Status</th></tr>
        </thead>
        <tbody>
          @forelse($client->loans as $loan)
          @php $balance = $loan->installments->where('status','pending')->sum('amount'); @endphp
          <tr>
            <td style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p)">{{ $loan->loan_number }}</td>
            <td style="font-size:12.5px">{{ $loan->loanProduct?->name ?? '—' }}</td>
            <td style="font-weight:700">M{{ number_format($loan->principal_amount??0,2) }}</td>
            <td style="font-weight:700;color:{{ $balance>0?'var(--warn)':'var(--ok)' }}">M{{ number_format($balance,2) }}</td>
            <td style="font-size:12px;color:var(--muted)">{{ $loan->disbursement_date?->format('d M Y') ?? '—' }}</td>
            <td>
              <span class="badge {{ in_array($loan->status,['active','disbursed'])?'bok':($loan->status==='closed'?'bs':($loan->status==='overdue'?'be':'bw')) }}">
                {{ ucfirst($loan->status) }}
              </span>
            </td>
          </tr>
          @empty
          <tr><td colspan="6"><div style="text-align:center;padding:40px;color:var(--muted)">No active loans</div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- DOCUMENTS TAB --}}
<div class="tpanel" data-pg="cl" data-p="docs">
  <div class="card">
    <div class="card-hdr"><span class="card-title">Documents</span></div>
    @php
    $allDocs = $client->loanApplications->flatMap->documents->sortByDesc('created_at');
    @endphp
    @if($allDocs->isEmpty())
    <div style="text-align:center;padding:50px;color:var(--muted)">
      <i class="bi bi-files" style="font-size:44px;opacity:.2;display:block;margin-bottom:10px"></i>
      No documents uploaded
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="dt">
        <thead><tr><th>Type</th><th>Application</th><th>Status</th><th>Uploaded</th><th></th></tr></thead>
        <tbody>
          @foreach($allDocs as $doc)
          <tr>
            <td style="font-weight:600">{{ ucfirst(str_replace('_',' ',$doc->type)) }}</td>
            <td style="font-size:12px;color:var(--muted)">{{ $doc->application?->application_number ?? '—' }}</td>
            <td><span class="badge {{ $doc->status==='verified'?'bok':($doc->status==='rejected'?'be':'bw') }}">{{ ucfirst($doc->status) }}</span></td>
            <td style="font-size:12px;color:var(--muted)">{{ $doc->created_at->format('d M Y') }}</td>
            <td>
              <a href="{{ route('officer.documents.download', $doc) }}" class="btn btn-xs btn-o"><i class="bi bi-download"></i></a>
              @if($doc->status==='pending')
              <a href="{{ route('officer.documents.show', $doc) }}" class="btn btn-xs btn-i"><i class="bi bi-eye"></i> Verify</a>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>

{{-- NOTES TAB --}}
<div class="tpanel" data-pg="cl" data-p="notes">
  <div class="card">
    <div class="card-hdr"><span class="card-title">Client Notes</span></div>
    <div class="card-body">
      <form method="POST" action="{{ route('officer.clients.notes.store', $client) }}" style="margin-bottom:20px">
        @csrf
        <div class="fg"><label class="fl">Add Note</label>
          <textarea name="content" class="fc" rows="3" placeholder="Add a note about this client..." required></textarea>
        </div>
        <button type="submit" class="btn btn-p btn-sm"><i class="bi bi-plus-circle"></i> Add Note</button>
      </form>

      @php
      $clientNotes = $client->loanApplications->flatMap->notes->sortByDesc('created_at');
      @endphp
      <div style="display:flex;flex-direction:column;gap:10px">
        @forelse($clientNotes as $note)
        <div style="background:#f8fafc;border-radius:10px;padding:14px;border:1px solid var(--border)">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
            <div style="display:flex;align-items:center;gap:8px">
              <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700">
                {{ strtoupper(substr($note->createdBy?->name??'?',0,1)) }}
              </div>
              <div>
                <div style="font-size:12.5px;font-weight:600">{{ $note->createdBy?->name ?? '—' }}</div>
                <div style="font-size:11px;color:var(--muted)">{{ $note->created_at->format('d M Y H:i') }}</div>
              </div>
            </div>
            <span class="badge bs" style="font-size:10px">{{ ucfirst($note->type) }}</span>
          </div>
          <div style="font-size:13px;color:#374151;line-height:1.6">{{ $note->content }}</div>
        </div>
        @empty
        <div style="text-align:center;padding:30px;color:var(--muted)">
          <i class="bi bi-chat-text" style="font-size:32px;opacity:.3;display:block;margin-bottom:8px"></i>No notes yet
        </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function switchTab(group, panel) {
  document.querySelectorAll(`.tab`).forEach(t => t.classList.remove('active'));
  document.querySelectorAll(`.tpanel[data-pg="${group}"]`).forEach(p => p.classList.remove('active'));
  document.querySelector(`.tpanel[data-pg="${group}"][data-p="${panel}"]`)?.classList.add('active');
  event.currentTarget.classList.add('active');
}
</script>
@endpush
@endsection
