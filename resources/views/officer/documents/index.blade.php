@extends('officer.layouts.app')
@section('title','Document Verification Queue')
@section('page-title','Document Verification')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
  <div style="font-size:13.5px;color:var(--muted)">All pending documents from your assigned applications</div>
  <span class="badge be" style="font-size:12px;padding:5px 12px">{{ $docs->total() }} Pending</span>
</div>

<div class="card">
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Client</th>
          <th>Application</th>
          <th>Document Type</th>
          <th>File</th>
          <th>Uploaded</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($docs as $doc)
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:9px">
              <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:11px;flex-shrink:0">
                {{ strtoupper(substr($doc->application?->applicant_name??'?',0,1)) }}
              </div>
              <div>
                <div style="font-size:13px;font-weight:600">{{ $doc->application?->applicant_name ?? '—' }}</div>
                <div style="font-size:11.5px;color:var(--muted)">{{ $doc->application?->user?->phone ?? '' }}</div>
              </div>
            </div>
          </td>
          <td>
            @if($doc->application)
            <a href="{{ route('officer.applications.show', $doc->application) }}" style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p);text-decoration:none">
              {{ $doc->application->application_number }}
            </a>
            <div style="font-size:11px;color:var(--muted)">{{ $doc->application->loanProduct?->name ?? '—' }}</div>
            @else
            <span style="color:var(--muted)">—</span>
            @endif
          </td>
          <td>
            <div style="display:flex;align-items:center;gap:8px">
              @php
              $docIcon = ['id_document'=>'person-badge','payslip'=>'receipt','bank_statement'=>'bank','photo'=>'camera'][$doc->type] ?? 'file-earmark';
              @endphp
              <div style="width:30px;height:30px;border-radius:8px;background:rgba(245,158,11,.1);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--warn)">
                <i class="bi bi-{{ $docIcon }}"></i>
              </div>
              <span style="font-weight:600;font-size:13px">{{ ucfirst(str_replace('_',' ',$doc->type)) }}</span>
            </div>
          </td>
          <td style="font-size:12.5px;color:var(--muted);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            {{ $doc->original_name ?? 'file' }}
          </td>
          <td style="font-size:12px;color:var(--muted)">{{ $doc->created_at->format('d M Y H:i') }}</td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap">
              <a href="{{ route('officer.documents.show', $doc) }}" class="btn btn-xs btn-i"><i class="bi bi-eye"></i> Review</a>
              <a href="{{ route('officer.documents.download', $doc) }}" class="btn btn-xs btn-o"><i class="bi bi-download"></i></a>
              <form method="POST" action="{{ route('officer.documents.verify', $doc) }}">@csrf
                <button class="btn btn-xs btn-ok"><i class="bi bi-check-lg"></i> Verify</button>
              </form>
              <button onclick="openModal('rejDoc{{ $doc->id }}')" class="btn btn-xs btn-e"><i class="bi bi-x-lg"></i></button>
            </div>

            {{-- Reject Modal --}}
            <div class="mo" id="rejDoc{{ $doc->id }}"><div class="mb" style="max-width:440px">
              <div class="mh"><span class="mt">Reject Document</span><button class="mc" onclick="closeModal('rejDoc{{ $doc->id }}')">×</button></div>
              <form method="POST" action="{{ route('officer.documents.reject', $doc) }}">@csrf
                <div class="mbody">
                  <div style="margin-bottom:12px;font-size:13.5px"><strong>{{ ucfirst(str_replace('_',' ',$doc->type)) }}</strong> for {{ $doc->application?->applicant_name }}</div>
                  <div class="fg"><label class="fl">Reason for Rejection *</label>
                    <textarea name="notes" class="fc" rows="3" required placeholder="e.g. Blurry image, wrong document, expired..."></textarea>
                  </div>
                </div>
                <div class="mf">
                  <button type="button" class="btn btn-o" onclick="closeModal('rejDoc{{ $doc->id }}')">Cancel</button>
                  <button type="submit" class="btn btn-e"><i class="bi bi-x-circle"></i> Reject</button>
                </div>
              </form>
            </div></div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6">
            <div style="text-align:center;padding:60px;color:var(--muted)">
              <i class="bi bi-check-circle-fill" style="font-size:48px;color:#10b981;opacity:.5;display:block;margin-bottom:12px"></i>
              <div style="font-weight:600;font-size:15px;margin-bottom:6px">All Clear!</div>
              <div style="font-size:13px">No documents pending verification</div>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($docs->hasPages())
  <div style="padding:14px 22px;border-top:1px solid var(--border)">{{ $docs->links() }}</div>
  @endif
</div>

@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.mo.open').forEach(m=>m.classList.remove('open'))})
</script>
@endpush
@endsection
