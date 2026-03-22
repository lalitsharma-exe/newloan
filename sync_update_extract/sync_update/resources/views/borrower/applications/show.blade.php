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

{{-- Action alerts --}}
@if($application->status === 'info_requested')
<div class="alert a-w">
  <i class="bi bi-exclamation-triangle-fill"></i>
  <div>
    <strong>Additional Information Required</strong><br>
    {{ $application->admin_notes }}
    <form method="POST" action="{{ route('borrower.applications.respond-info',$application) }}" style="margin-top:10px">@csrf
      <textarea name="response" class="fc" rows="3" placeholder="Your response..." required></textarea>
      <button type="submit" class="btn btn-p btn-sm" style="margin-top:8px">Submit Response</button>
    </form>
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

{{-- ── MESSAGES FROM LENDER ─────────────────────────────── --}}
@if($application->notes->count())
<div class="card">
  <div class="card-hdr"><span class="card-title">Messages from MyLoan</span></div>
  <div class="card-body">
    @foreach($application->notes->where('is_internal',false) as $note)
    <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;margin-bottom:10px;border:1px solid var(--border)">
      <div style="font-size:11px;color:var(--muted);margin-bottom:4px">{{ $note->created_at->format('d M Y H:i') }}</div>
      <div style="font-size:13.5px;color:var(--ink)">{{ $note->content }}</div>
    </div>
    @endforeach
  </div>
</div>
@endif

@endsection
