@extends('borrower.layouts.app')
@section('title','Application #'.$application->application_number)
@section('content')

@php
$sc = ['submitted'=>'bi','under_review'=>'bi','info_requested'=>'bw','approved'=>'bok','declined'=>'be','disbursed'=>'bp','draft'=>'bs','on_hold'=>'bw'];
$a  = $application->affordability;
@endphp

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:20px">
  <div>
    <div style="font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:700;color:var(--navy)">{{ $application->application_number }}</div>
    <div style="font-size:13px;color:var(--muted)">{{ $application->loanProduct?->name ?? '—' }} &nbsp;·&nbsp; Submitted {{ $application->submitted_at?->format('d M Y') ?? 'Draft' }}</div>
  </div>
  <span class="badge {{ $sc[$application->status]??'bs' }}" style="font-size:14px;padding:8px 18px">{{ ucfirst(str_replace('_',' ',$application->status)) }}</span>
</div>

@if(empty($application->signature_path))
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
  <div style="display:flex;gap:12px;align-items:center;">
    <i class="bi bi-exclamation-triangle-fill" style="color:#ef4444;font-size:24px"></i>
    <div>
      <div style="font-weight:700;color:#991b1b;font-size:15px">Missing Signature</div>
      <div style="color:#991b1b;font-size:13px;opacity:0.9">Your application requires a digital signature to be complete.</div>
    </div>
  </div>
  <button type="button" onclick="document.getElementById('sigModal').style.display='flex'" class="btn btn-e">Sign Application</button>
</div>

<div id="sigModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:20px">
  <div style="background:#fff;border-radius:12px;width:100%;max-width:440px;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
      <h3 style="margin:0;font-size:16px">Sign Application</h3>
      <button type="button" onclick="document.getElementById('sigModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer">&times;</button>
    </div>
    <form id="sigForm" method="POST" action="{{ route('borrower.applications.signature', $application) }}">
      @csrf
      <div style="padding:20px">
        <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:8px;text-align:center">
          <canvas id="signature-pad" style="width:100%;height:150px;touch-action:none;border-radius:4px;background:#fff"></canvas>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
            <span style="font-size:11px;color:#64748b">Sign above</span>
            <button type="button" class="btn btn-o btn-sm" onclick="sigPad.clear()" style="padding:4px 8px;font-size:11px">Clear</button>
          </div>
        </div>
        <input type="hidden" name="signature_data" id="signature_data">
      </div>
      <div style="padding:16px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px">
        <button type="button" onclick="document.getElementById('sigModal').style.display='none'" class="btn btn-o">Cancel</button>
        <button type="submit" class="btn btn-p"><i class="bi bi-check-lg"></i> Submit Signature</button>
      </div>
    </form>
  </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
let sigPad;
document.addEventListener("DOMContentLoaded", function() {
    const canvas = document.getElementById('signature-pad');
    if (canvas) {
        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }
        
        // Wait till modal is visible to resize canvas to avoid zero-width bug
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutationRecord) {
                if (document.getElementById('sigModal').style.display === 'flex') {
                    resizeCanvas();
                }
            });    
        });
        observer.observe(document.getElementById('sigModal'), { attributes : true, attributeFilter : ['style'] });
        
        window.addEventListener("resize", resizeCanvas);
        sigPad = new SignaturePad(canvas, { backgroundColor: '#ffffff', penColor: '#0f172a' });

        document.getElementById('sigForm').addEventListener('submit', function(e) {
            if (sigPad.isEmpty()) {
                e.preventDefault();
                alert('Please provide your digital signature before submitting.');
            } else {
                document.getElementById('signature_data').value = sigPad.toDataURL('image/png');
            }
        });
    }
});
</script>
@endpush
@endif

{{-- Action alerts --}}
@if($application->status === 'info_requested')
<div class="alert a-w">
  <i class="bi bi-exclamation-triangle-fill"></i>
  <div>
    <strong>Additional Information Required</strong><br>
    {{ $application->admin_notes ?: 'Please check messages below.' }}
    <div style="margin-top:8px;font-size:13px"><a href="#chatSection" style="font-weight:600;color:#92400e;text-decoration:none">Reply through the chat below <i class="bi bi-arrow-down-short"></i></a></div>
  </div>
</div>
@endif

@if($application->status === 'approved')
<div class="alert a-ok">
  <i class="bi bi-check-circle-fill"></i>
  <div>
    <strong>Congratulations! Your loan has been approved.</strong><br>
    <a href="{{ route('borrower.applications.accept-terms',$application) }}" class="btn btn-ok btn-sm" style="margin-top:8px">Accept Terms &amp; Proceed</a>
  </div>
</div>
@endif

@if($application->status === 'declined')
<div class="alert a-e">
  <i class="bi bi-x-circle-fill"></i>
  <div><strong>Application Declined</strong>@if($application->decline_reason)<br><span style="font-size:13px">{{ $application->decline_reason }}</span>@endif</div>
</div>
@endif

{{-- Quick stats --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
  @foreach([
    'Amount'  => 'M '.number_format($application->requested_amount??0,2),
    'Term'    => ($application->requested_term??'—').' months',
    'Product' => $application->loanProduct?->name??'—'
  ] as $l=>$v)
  <div style="background:#f0f4ff;border-radius:10px;padding:14px;border:1px solid var(--border)">
    <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em">{{ $l }}</div>
    <div style="font-size:16px;font-weight:800;margin-top:4px;color:var(--navy)">{{ $v }}</div>
  </div>
  @endforeach
</div>

{{-- ── AFFORDABILITY SECTION ────────────────────────────── --}}
@if($a)
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-calculator" style="color:var(--blue);margin-right:6px"></i>Your Affordability</span>
    <span class="badge {{ ($a->disposable_income??0)>=0?'bok':'be' }}">{{ ($a->disposable_income??0)>=0?'Passes':'Fails' }}</span>
  </div>
  <div class="card-body">

    {{-- 3 summary boxes --}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:18px">
      <div style="background:#f0f4ff;border-radius:10px;padding:14px;text-align:center;border:1px solid var(--border)">
        <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Gross Salary</div>
        <div style="font-size:20px;font-weight:800;color:var(--navy)">M{{ number_format($a->monthly_earnings??0,0) }}</div>
      </div>
      <div style="background:#fff7f0;border-radius:10px;padding:14px;text-align:center;border:1px solid #fde8d0">
        <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Total Deductions</div>
        @php $totalDed = ($a->tax_deduction??0)+($a->existing_loans_deduction??0)+($a->other_deductions??0); @endphp
        <div style="font-size:20px;font-weight:800;color:#ea580c">M{{ number_format($totalDed,0) }}</div>
      </div>
      <div style="background:#f0fdf4;border-radius:10px;padding:14px;text-align:center;border:1px solid #bbf7d0">
        <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Net Salary</div>
        <div style="font-size:20px;font-weight:800;color:#16a34a">M{{ number_format($a->net_salary??0,0) }}</div>
      </div>
    </div>

    {{-- Disposable income --}}
    <div style="background:{{ ($a->disposable_income??0)>=0?'#f0fdf4':'#fef2f2' }};border-radius:10px;padding:14px;display:flex;align-items:center;justify-content:space-between;border:1px solid {{ ($a->disposable_income??0)>=0?'#bbf7d0':'#fecaca' }}">
      <div>
        <div style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em">Disposable Income (after all expenses)</div>
        <div style="font-size:11px;color:var(--muted);margin-top:2px">Net salary minus monthly living expenses</div>
      </div>
      <div style="font-size:26px;font-weight:800;color:{{ ($a->disposable_income??0)>=0?'#16a34a':'#dc2626' }}">M{{ number_format($a->disposable_income??0,2) }}</div>
    </div>

  </div>
</div>
@endif

{{-- ── DOCUMENTS ────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr">
    <span class="card-title">Documents</span>
    <span style="font-size:12px;color:var(--muted)">{{ $application->documents->count() }} uploaded</span>
  </div>

  {{-- Document list --}}
  @if($application->documents->count())
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>Type</th><th>File</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($application->documents as $doc)
        <tr>
          <td style="font-weight:600">{{ ucfirst(str_replace('_',' ',$doc->type)) }}</td>
          <td style="font-size:12.5px;color:var(--muted)">{{ $doc->original_name }}</td>
          <td><span class="badge {{ $doc->status==='verified'?'bok':($doc->status==='rejected'?'be':'bw') }}">{{ ucfirst($doc->status) }}</span></td>
          <td><a href="{{ route('borrower.documents.download',$doc) }}" class="btn btn-xs btn-o"><i class="bi bi-download"></i></a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  {{-- Upload additional --}}
  <div style="padding:16px 20px;border-top:1px solid var(--border);background:#fafbff">
    <div style="font-size:13px;font-weight:600;color:var(--navy);margin-bottom:10px"><i class="bi bi-upload" style="color:var(--blue);margin-right:6px"></i>Upload a Document</div>
    <form method="POST" action="{{ route('borrower.documents.upload.application',$application) }}" enctype="multipart/form-data">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end">
        <div>
          <label style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:4px;display:block">Document Type</label>
          <select name="type" class="fc" required>
            <option value="">— Select —</option>
            @foreach(['national_id'=>'National ID','payslip'=>'Payslip','bank_statement'=>'Bank Statement','photo'=>'Passport Photo','other'=>'Other'] as $v=>$l)
            <option value="{{ $v }}">{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:4px;display:block">File (PDF/JPG/PNG)</label>
          <input type="file" name="file" class="fc" accept=".pdf,.jpg,.jpeg,.png" required style="padding:8px 10px">
        </div>
        <button type="submit" class="btn btn-p"><i class="bi bi-upload"></i> Upload</button>
      </div>
    </form>
  </div>
</div>

</div>

{{-- ── FLOATING CHAT WIDGET ─────────────────────────────────── --}}
<div id="chatWidget" style="position:fixed;bottom:24px;right:24px;z-index:9999;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
  {{-- The Chat Popup Window --}}
  <div id="chatPopup" style="display:none;width:350px;height:500px;background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,.15);flex-direction:column;overflow:hidden;margin-bottom:16px;border:1px solid var(--border);">
    <div style="background:var(--navy);padding:16px 20px;color:#fff;font-weight:700;display:flex;justify-content:space-between;align-items:center;">
      <div style="display:flex;align-items:center;gap:10px">
        <i class="bi bi-headset" style="font-size:18px"></i> Conversation with MyLoan
      </div>
      <button onclick="toggleChat()" style="background:none;border:none;color:#fff;cursor:pointer;"><i class="bi bi-x-lg"></i></button>
    </div>
    
    <div id="chatMessages" style="flex:1;background:#f8fafc;padding:20px;overflow-y:auto;display:flex;flex-direction:column;gap:16px">
      {{-- Messages will be loaded here via AJAX --}}
    </div>

    <div style="padding:16px;background:#fff;border-top:1px solid var(--border)">
      <form id="chatForm" onsubmit="sendChatMessage(event)">
        <div style="display:flex;gap:10px">
          <input type="text" id="chatInput" placeholder="Type a message..." required style="flex:1;border-radius:99px;padding:10px 16px;border:1px solid #cbd5e1;outline:none;font-size:14px;">
          <button type="submit" id="chatSendBtn" style="background:var(--p);color:#fff;border:none;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .1s;"><i class="bi bi-send-fill" style="margin-left:-2px"></i></button>
        </div>
      </form>
    </div>
  </div>

  {{-- The Floating Button --}}
  <button id="chatFloatingBtn" onclick="toggleChat()" style="background:var(--p);color:#fff;border:none;width:60px;height:60px;border-radius:50%;box-shadow:0 6px 20px rgba(79,70,229,.4);display:flex;align-items:center;justify-content:center;font-size:24px;cursor:pointer;cursor:pointer;transition:transform .2s;float:right;position:relative;">
    <i class="bi bi-chat-dots-fill"></i>
  </button>
</div>

<script>
let chatOpen = false;
const appId = {{ $application->id }};
const msgUrl = "{{ route('borrower.applications.messages.get', $application) }}";
const sendUrl = "{{ route('borrower.applications.messages.send', $application) }}";
const csrf = "{{ csrf_token() }}";

function toggleChat() {
  chatOpen = !chatOpen;
  document.getElementById('chatPopup').style.display = chatOpen ? 'flex' : 'none';
  if(chatOpen) {
    loadMessages();
    setTimeout(() => document.getElementById('chatInput').focus(), 100);
  }
}

function renderMessage(msg) {
  const isMine = msg.sender_type === 'borrower';
  const align = isMine ? 'flex-end' : 'flex-start';
  const flexDirection = isMine ? 'row-reverse' : 'row';
  const bg = isMine ? 'var(--p)' : '#fff';
  const textCol = isMine ? '#000' : '#334155';
  const radius = isMine ? '16px 16px 0 16px' : '16px 16px 16px 0';
  const avatarBg = isMine ? 'rgba(79,70,229,.2)' : '#e2e8f0';
  const avatarCol = isMine ? 'var(--p)' : '#475569';
  const avatarText = isMine ? msg.sender_initial : 'ML';
  const shadow = isMine ? '0 4px 12px rgba(79,70,229,.2)' : '0 2px 8px rgba(0,0,0,.05)';

  return `
    <div style="display:flex;gap:12px;align-self:${align};max-width:85%;flex-direction:${flexDirection}">
      <div style="width:32px;height:32px;border-radius:50%;background:${avatarBg};color:${avatarCol};display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;font-size:12px">${avatarText}</div>
      <div style="display:flex;flex-direction:column;align-items:${align}">
        <div style="background:${bg};color:${textCol};padding:12px 16px;border-radius:${radius};font-size:13.5px;line-height:1.5;box-shadow:${shadow};box-sizing:border-box;">${msg.content}</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">${msg.created_at}</div>
      </div>
    </div>
  `;
}

function loadMessages() {
  fetch(msgUrl)
    .then(r => r.json())
    .then(data => {
      const container = document.getElementById('chatMessages');
      if (data.length === 0) {
         container.innerHTML = '<div style="text-align:center;padding:40px;color:var(--muted);margin:auto"><i class="bi bi-chat-heart" style="font-size:40px;opacity:.25;display:block;margin-bottom:10px"></i><div>How can we help you?</div></div>';
      } else {
         container.innerHTML = data.map(renderMessage).join('');
         container.scrollTop = container.scrollHeight;
      }
    });
}

function sendChatMessage(e) {
  e.preventDefault();
  const input = document.getElementById('chatInput');
  const btn = document.getElementById('chatSendBtn');
  const content = input.value.trim();
  if(!content) return;

  btn.style.transform = 'scale(0.9)';
  btn.style.opacity = '0.7';

  fetch(sendUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    body: JSON.stringify({ message: content })
  }).then(r => r.json()).then(res => {
    btn.style.transform = 'none';
    btn.style.opacity = '1';
    if(res.success) {
      input.value = '';
      loadMessages();
    }
  });
}
</script>

@endsection
