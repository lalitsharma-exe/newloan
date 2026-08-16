@extends('officer.layouts.app')
@section('title','Documents – '.$application->application_number)
@section('page-title','Application Documents')
@section('bc','<a href="'.route('officer.applications.assigned').'">Applications</a> / <a href="'.route('officer.applications.show',$application).'">#'.$application->application_number.'</a> / Documents')

@section('content')

{{-- Header --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px 22px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div style="display:flex;align-items:center;gap:12px">
    <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:16px">
      {{ strtoupper(substr($application->applicant_name,0,1)) }}
    </div>
    <div>
      <div style="font-weight:700;font-size:15px">{{ $application->applicant_name }}</div>
      <div style="font-size:12.5px;color:var(--muted)">{{ $application->application_number }} &nbsp;·&nbsp; {{ $application->loanProduct?->name ?? '—' }}</div>
    </div>
  </div>
  <div style="display:flex;gap:8px">
    <a href="{{ route('officer.applications.show', $application) }}" class="btn btn-o btn-sm"><i class="bi bi-arrow-left"></i> Back to Application</a>
    <button onclick="openModal('reqModal')" class="btn btn-w btn-sm"><i class="bi bi-envelope"></i> Request Documents</button>
  </div>
</div>

@if(session('success'))
<div class="alert a-ok" style="margin-bottom:16px"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

{{-- Document checklist --}}
@php
$requiredTypes = [
  'id_document'    => ['ID Document',     'person-badge',    'Required — National ID'],
  'payslip'        => ['Recent Payslip',  'receipt',         'Latest month'],
  'bank_statement' => ['Bank Statement',  'bank',            'Last 1–3 months'],
  'photo'          => ['Half-Body Photo', 'camera',          'Clear, recent photo'],
];
$docsByType = $docs->keyBy('type');
$pendingCount   = $docs->where('status','pending')->count();
$verifiedCount  = $docs->where('status','verified')->count();
$rejectedCount  = $docs->where('status','rejected')->count();
$missingCount   = count($requiredTypes) - $docs->count();
@endphp

{{-- Summary bar --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
  @foreach([['Verified',$verifiedCount,'ok','check-circle-fill'],['Pending',$pendingCount,'w','hourglass-split'],['Rejected',$rejectedCount,'e','x-circle-fill'],['Missing',$missingCount,'s','dash-circle']] as [$lbl,$val,$cls,$icon])
  <div class="sc">
    <div class="si {{ $cls }}"><i class="bi bi-{{ $icon }}"></i></div>
    <div><div class="sv" style="font-size:20px">{{ $val }}</div><div class="sl">{{ $lbl }}</div></div>
  </div>
  @endforeach
</div>

{{-- Required documents grid --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
  @foreach($requiredTypes as $type => [$label, $icon, $note])
  @php $doc = $docsByType->get($type); @endphp
  <div style="background:#fff;border:1.5px solid {{ $doc ? ($doc->status==='verified'?'rgba(22,163,74,.4)':($doc->status==='rejected'?'rgba(239,68,68,.4)':'rgba(245,158,11,.4)')) : 'var(--border)' }};border-radius:14px;padding:18px">
    <div style="display:flex;align-items:center;gap:11px;margin-bottom:14px">
      <div style="width:42px;height:42px;border-radius:11px;background:{{ $doc ? ($doc->status==='verified'?'rgba(22,163,74,.1)':($doc->status==='rejected'?'rgba(239,68,68,.1)':'rgba(245,158,11,.1)')) : 'var(--bg)' }};display:flex;align-items:center;justify-content:center;font-size:18px;color:{{ $doc ? ($doc->status==='verified'?'var(--ok)':($doc->status==='rejected'?'var(--err)':'var(--warn)')) : 'var(--muted)' }};flex-shrink:0">
        <i class="bi bi-{{ $icon }}"></i>
      </div>
      <div style="flex:1">
        <div style="font-weight:700;font-size:13.5px">{{ $label }}</div>
        <div style="font-size:11.5px;color:var(--muted)">{{ $note }}</div>
      </div>
      @if($doc)
      <span class="badge {{ $doc->status==='verified'?'bok':($doc->status==='rejected'?'be':'bw') }}">
        {{ ucfirst($doc->status) }}
      </span>
      @else
      <span class="badge bs">Missing</span>
      @endif
    </div>

    @if($doc)
    {{-- File info --}}
    <div style="background:#f8fafc;border-radius:9px;padding:10px 13px;margin-bottom:12px;display:flex;align-items:center;gap:9px">
      <i class="bi bi-file-earmark" style="color:var(--muted);font-size:16px"></i>
      <span style="font-size:12px;color:var(--dark);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $doc->original_name ?? 'Uploaded file' }}</span>
      <span style="font-size:11px;color:var(--muted)">{{ $doc->created_at->format('d M Y') }}</span>
    </div>

    @if($doc->notes)
    <div style="background:rgba(239,68,68,.05);border:1px solid rgba(239,68,68,.15);border-radius:8px;padding:9px 12px;font-size:12px;color:#991b1b;margin-bottom:12px">
      <i class="bi bi-chat-text"></i> {{ $doc->notes }}
    </div>
    @endif

    {{-- Actions --}}
    <div style="display:flex;gap:6px">
      <a href="{{ route('officer.applications.documents.download', [$application, $doc]) }}" class="btn btn-sm btn-o" style="flex:1;justify-content:center">
        <i class="bi bi-download"></i> Download
      </a>
      @if($doc->status === 'pending')
      <form method="POST" action="{{ route('officer.applications.documents.verify', [$application, $doc]) }}" style="flex:1">@csrf
        <button class="btn btn-sm btn-ok" style="width:100%;justify-content:center"><i class="bi bi-check-lg"></i> Verify</button>
      </form>
      <button onclick="openModal('rej_{{ $doc->id }}')" class="btn btn-sm btn-e"><i class="bi bi-x-lg"></i></button>
      @elseif($doc->status === 'verified')
      <span style="flex:1;text-align:center;font-size:12px;color:var(--ok);font-weight:600;display:flex;align-items:center;justify-content:center;gap:5px">
        <i class="bi bi-check-circle-fill"></i> Verified
      </span>
      @elseif($doc->status === 'rejected')
      <button onclick="openModal('rej_{{ $doc->id }}')" class="btn btn-sm btn-o" style="flex:1;justify-content:center"><i class="bi bi-arrow-repeat"></i> Re-review</button>
      @endif
    </div>

    {{-- Reject modal --}}
    @if(in_array($doc->status, ['pending','rejected']))
    <div class="mo" id="rej_{{ $doc->id }}"><div class="mb" style="max-width:440px">
      <div class="mh"><span class="mt">Reject {{ $label }}</span><button class="mc" onclick="closeModal('rej_{{ $doc->id }}')">×</button></div>
      <form method="POST" action="{{ route('officer.applications.documents.reject', [$application, $doc]) }}">@csrf
        <div class="mbody">
          <div class="fg"><label class="fl">Reason for Rejection *</label>
            <textarea name="notes" class="fc" rows="3" required placeholder="e.g. Blurry image, expired document, wrong type...">{{ $doc->notes }}</textarea>
          </div>
        </div>
        <div class="mf">
          <button type="button" class="btn btn-o" onclick="closeModal('rej_{{ $doc->id }}')">Cancel</button>
          <button type="submit" class="btn btn-e"><i class="bi bi-x-circle"></i> Reject Document</button>
        </div>
      </form>
    </div></div>
    @endif

    @else
    {{-- Missing document --}}
    <div style="text-align:center;padding:16px;color:var(--muted)">
      <i class="bi bi-cloud-upload" style="font-size:28px;opacity:.3;display:block;margin-bottom:8px"></i>
      <div style="font-size:12.5px;margin-bottom:10px">Not uploaded yet</div>
      <button onclick="openModal('reqModal')" class="btn btn-w btn-sm" style="width:100%;justify-content:center">
        <i class="bi bi-envelope"></i> Request from Borrower
      </button>
    </div>
    @endif
  </div>
  @endforeach
</div>

{{-- Any additional docs (not in required list) --}}
@php $extra = $docs->whereNotIn('type', array_keys($requiredTypes)); @endphp
@if($extra->count() > 0)
<div class="card" style="margin-bottom:20px">
  <div class="card-hdr"><span class="card-title">Additional Documents</span></div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>Type</th><th>File</th><th>Status</th><th>Uploaded</th><th>Actions</th></tr></thead>
      <tbody>
        @foreach($extra as $doc)
        <tr>
          <td style="font-weight:600">{{ ucfirst(str_replace('_',' ',$doc->type)) }}</td>
          <td style="font-size:12.5px;color:var(--muted)">{{ $doc->original_name }}</td>
          <td><span class="badge {{ $doc->status==='verified'?'bok':($doc->status==='rejected'?'be':'bw') }}">{{ ucfirst($doc->status) }}</span></td>
          <td style="font-size:12px;color:var(--muted)">{{ $doc->created_at->format('d M Y') }}</td>
          <td>
            <div style="display:flex;gap:5px">
              <a href="{{ route('officer.applications.documents.download',[$application,$doc]) }}" class="btn btn-xs btn-o"><i class="bi bi-download"></i></a>
              @if($doc->status==='pending')
              <form method="POST" action="{{ route('officer.applications.documents.verify',[$application,$doc]) }}">@csrf
                <button class="btn btn-xs btn-ok"><i class="bi bi-check-lg"></i></button>
              </form>
              @endif
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- Request Documents Modal --}}
<div class="mo" id="reqModal"><div class="mb" style="max-width:480px">
  <div class="mh"><span class="mt"><i class="bi bi-envelope"></i> Request Documents from Borrower</span><button class="mc" onclick="closeModal('reqModal')">×</button></div>
  <form method="POST" action="{{ route('officer.applications.documents.request', $application) }}">@csrf
    <div class="mbody">
      <div class="alert a-i" style="margin-bottom:16px"><i class="bi bi-info-circle-fill"></i> The borrower will be notified and can upload from their portal.</div>
      <div class="fg"><label class="fl">Message to Borrower *</label>
        <textarea name="message" class="fc" rows="4" required placeholder="e.g. Please upload a clear copy of your ID document and your most recent payslip..."></textarea>
      </div>
    </div>
    <div class="mf">
      <button type="button" class="btn btn-o" onclick="closeModal('reqModal')">Cancel</button>
      <button type="submit" class="btn btn-w"><i class="bi bi-send"></i> Send Request</button>
    </div>
  </form>
</div></div>

@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.mo.open').forEach(m=>m.classList.remove('open'))})
</script>
@endpush
@endsection
