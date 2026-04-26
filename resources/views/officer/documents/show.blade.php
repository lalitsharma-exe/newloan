@extends('officer.layouts.app')
@section('title','Review Document')
@section('page-title','Document Review')
@section('bc')
<a href="{{ route('officer.documents.index') }}">Documents</a> / Review
@endsection

@section('content')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start">

  {{-- Document viewer --}}
  <div>
    <div class="card">
      <div class="card-hdr">
        <span class="card-title"><i class="bi bi-file-earmark-text" style="color:var(--p)"></i> {{ ucfirst(str_replace('_',' ',$doc->type)) }}</span>
        <a href="{{ route('officer.documents.download', $doc) }}" class="btn btn-sm btn-o"><i class="bi bi-download"></i> Download</a>
      </div>
      <div style="padding:20px">
        @php
        $ext = strtolower(pathinfo($doc->original_name ?? '', PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg','jpeg','png','webp','gif']);
        $isPdf = $ext === 'pdf';
        @endphp

        @if($isImage)
        <div style="text-align:center;background:#f8fafc;border-radius:12px;padding:20px;border:1px solid var(--border)">
          <img src="{{ Storage::url($doc->path) }}" alt="{{ $doc->original_name }}"
            style="max-width:100%;max-height:600px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,.1)">
        </div>
        @elseif($isPdf)
        <div style="background:#f8fafc;border-radius:12px;border:1px solid var(--border);overflow:hidden">
          <iframe src="{{ Storage::url($doc->path) }}" style="width:100%;height:600px;border:none"></iframe>
        </div>
        @else
        <div style="text-align:center;padding:60px;color:var(--muted)">
          <i class="bi bi-file-earmark" style="font-size:60px;opacity:.3;display:block;margin-bottom:16px"></i>
          <div style="font-weight:600;margin-bottom:8px">{{ $doc->original_name }}</div>
          <div style="margin-bottom:16px;font-size:13px">Preview not available for this file type.</div>
          <a href="{{ route('officer.documents.download', $doc) }}" class="btn btn-p"><i class="bi bi-download"></i> Download to View</a>
        </div>
        @endif

        @if($doc->notes)
        <div class="alert a-w" style="margin-top:16px">
          <i class="bi bi-chat-left-text"></i>
          <div><strong>Notes:</strong> {{ $doc->notes }}</div>
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Actions sidebar --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Status --}}
    <div class="card">
      <div class="card-hdr"><span class="card-title">Document Status</span></div>
      <div class="card-body">
        <div style="text-align:center;margin-bottom:14px">
          <span class="badge {{ $doc->status==='verified'?'bok':($doc->status==='rejected'?'be':'bw') }}" style="font-size:14px;padding:7px 18px">
            @if($doc->status==='verified')<i class="bi bi-check-circle-fill"></i>@elseif($doc->status==='rejected')<i class="bi bi-x-circle-fill"></i>@else<i class="bi bi-hourglass-split"></i>@endif
            {{ ucfirst($doc->status) }}
          </span>
        </div>
        @if($doc->status==='pending')
        <div style="display:flex;flex-direction:column;gap:8px">
          <form method="POST" action="{{ route('officer.documents.verify', $doc) }}">@csrf
            <div class="fg"><label class="fl">Verification Notes (optional)</label>
              <textarea name="notes" class="fc" rows="2" placeholder="e.g. Document matches ID on file..."></textarea>
            </div>
            <button type="submit" class="btn btn-ok" style="width:100%;justify-content:center"><i class="bi bi-check-circle-fill"></i> Verify Document</button>
          </form>
          <button onclick="openModal('rejectModal')" class="btn btn-e" style="width:100%;justify-content:center"><i class="bi bi-x-circle"></i> Reject Document</button>
        </div>
        @elseif($doc->status==='verified')
        <div style="background:rgba(16,185,129,.06);border-radius:10px;padding:14px;text-align:center">
          <i class="bi bi-check-circle-fill" style="color:var(--ok);font-size:24px;display:block;margin-bottom:6px"></i>
          <div style="font-size:12.5px;color:var(--muted)">Verified by {{ $doc->verifiedBy?->name ?? 'Staff' }}<br>{{ $doc->verified_at?->format('d M Y H:i') }}</div>
        </div>
        @elseif($doc->status==='rejected')
        <div style="background:rgba(239,68,68,.06);border-radius:10px;padding:14px">
          <div style="font-size:12px;color:var(--err);font-weight:600;margin-bottom:4px"><i class="bi bi-x-circle-fill"></i> Rejected</div>
          <div style="font-size:12.5px;color:var(--muted)">{{ $doc->notes }}</div>
        </div>
        @endif
      </div>
    </div>

    {{-- Document info --}}
    <div class="card">
      <div class="card-hdr"><span class="card-title">Document Info</span></div>
      <div class="card-body" style="padding:14px 18px">
        @foreach(['Type'=>ucfirst(str_replace('_',' ',$doc->type)),'File Name'=>$doc->original_name??'—','Uploaded'=>$doc->created_at->format('d M Y H:i')] as $l=>$v)
        <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
          <span style="color:var(--muted)">{{ $l }}</span><strong style="text-align:right;max-width:160px;overflow:hidden;text-overflow:ellipsis">{{ $v }}</strong>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Client info --}}
    @if($doc->application)
    <div class="card">
      <div class="card-hdr"><span class="card-title">Client</span></div>
      <div class="card-body" style="padding:14px 18px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
          <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px">
            {{ strtoupper(substr($doc->application->applicant_name,0,1)) }}
          </div>
          <div>
            <div style="font-weight:700;font-size:13px">{{ $doc->application->applicant_name }}</div>
            <div style="font-size:11.5px;color:var(--muted)">{{ $doc->application->application_number }}</div>
          </div>
        </div>
        <a href="{{ route('officer.applications.show', $doc->application) }}" class="btn btn-o btn-sm" style="width:100%;justify-content:center"><i class="bi bi-file-earmark-person"></i> View Application</a>
      </div>
    </div>
    @endif

    <a href="{{ route('officer.documents.index') }}" class="btn btn-o" style="justify-content:center"><i class="bi bi-arrow-left"></i> Back to Queue</a>
  </div>
</div>

{{-- Reject modal --}}
<div class="mo" id="rejectModal"><div class="mb" style="max-width:440px">
  <div class="mh"><span class="mt">Reject Document</span><button class="mc" onclick="closeModal('rejectModal')">×</button></div>
  <form method="POST" action="{{ route('officer.documents.reject', $doc) }}">@csrf
    <div class="mbody">
      <div class="fg"><label class="fl">Reason for Rejection *</label>
        <textarea name="notes" class="fc" rows="3" required placeholder="e.g. Blurry image, wrong document, expired ID..."></textarea>
      </div>
    </div>
    <div class="mf">
      <button type="button" class="btn btn-o" onclick="closeModal('rejectModal')">Cancel</button>
      <button type="submit" class="btn btn-e"><i class="bi bi-x-circle"></i> Reject Document</button>
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
