@extends('admin.layouts.app')
@section('title','Import Loans')
@section('page-title','Bulk Loan Import')
@section('bc','<a href="'.route('admin.loans.index').'">Loans</a> / Import')
@section('content')

@if(session('success'))
<div style="background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.2);color:#065f46;padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px;margin-bottom:18px">
  <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

@if(session('import_results'))
@php $res = session('import_results'); @endphp
<div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px 22px;margin-bottom:24px">
  <div style="font-weight:700;font-size:15px;margin-bottom:12px"><i class="bi bi-bar-chart-fill" style="color:var(--p)"></i> Import Results</div>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px">
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;text-align:center">
      <div style="font-size:32px;font-weight:800;color:#059669">{{ $res['success'] }}</div>
      <div style="font-size:12px;color:#065f46;font-weight:600;margin-top:2px">Loans Imported</div>
    </div>
    <div style="background:{{ $res['failed']>0?'#fef2f2':'#f8fafc' }};border:1px solid {{ $res['failed']>0?'#fecaca':'#e2e8f0' }};border-radius:10px;padding:14px;text-align:center">
      <div style="font-size:32px;font-weight:800;color:{{ $res['failed']>0?'#dc2626':'#94a3b8' }}">{{ $res['failed'] }}</div>
      <div style="font-size:12px;color:{{ $res['failed']>0?'#991b1b':'#94a3b8' }};font-weight:600;margin-top:2px">Failed Rows</div>
    </div>
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px;text-align:center">
      <div style="font-size:32px;font-weight:800;color:#1d4ed8">{{ $res['success']+$res['failed'] }}</div>
      <div style="font-size:12px;color:#1e40af;font-weight:600;margin-top:2px">Total Rows</div>
    </div>
  </div>
  @if(!empty($res['errors']))
  <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;font-size:12.5px;color:#991b1b">
    <strong>Errors:</strong>
    <ul style="margin:8px 0 0 18px;line-height:1.9">
      @foreach($res['errors'] as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
  @endif
  @if($res['success']>0)
  <div style="margin-top:12px">
    <a href="{{ route('admin.loans.index') }}" class="btn btn-primary"><i class="bi bi-wallet2"></i> View Loans</a>
  </div>
  @endif
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">

{{-- Upload form --}}
<div class="card">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-file-earmark-arrow-up" style="color:var(--p)"></i> Upload CSV File</span></div>
  <form method="POST" action="{{ route('admin.loans.import.post') }}" enctype="multipart/form-data">
    @csrf
    <div style="padding:24px">
      {{-- Drop zone --}}
      <div id="dropZone" onclick="document.getElementById('csvFile').click()"
        style="border:2px dashed #c7d2fe;border-radius:14px;padding:40px 24px;text-align:center;cursor:pointer;transition:background .2s;background:#f8f9ff"
        ondragover="event.preventDefault();this.style.background='#ede9fe'"
        ondragleave="this.style.background='#f8f9ff'"
        ondrop="handleDrop(event)">
        <i class="bi bi-cloud-upload" style="font-size:40px;color:#6366f1;display:block;margin-bottom:10px"></i>
        <div style="font-weight:700;color:#1e3a5f;font-size:15px">Drop your CSV here</div>
        <div style="color:var(--muted);font-size:13px;margin-top:6px">or click to browse</div>
        <input type="file" name="file" id="csvFile" accept=".csv,.txt" style="display:none" onchange="showFileName(this)">
      </div>
      <div id="fileNameDisplay" style="text-align:center;margin-top:10px;font-size:13px;color:var(--p);font-weight:600"></div>

      @error('file')<div style="color:#ef4444;font-size:12px;margin-top:8px">{{ $message }}</div>@enderror
    </div>
    <div style="padding:0 24px 24px;display:flex;gap:12px;justify-content:flex-end;border-top:1px solid var(--border);padding-top:16px">
      <a href="{{ route('admin.loans.index') }}" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
        <i class="bi bi-upload"></i> Import Loans
      </button>
    </div>
  </form>
</div>

{{-- Instructions + template --}}
<div style="display:flex;flex-direction:column;gap:16px">

  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-file-earmark-text"></i> Required CSV Format</span></div>
    <div style="padding:14px 16px">
      <div style="background:#1e1e2e;border-radius:8px;padding:12px 14px;font-family:monospace;font-size:12px;color:#e2e8f0;overflow-x:auto;margin-bottom:12px">
        email,principal_amount,term_months,interest_rate,disbursement_date,collection_method,product,notes<br>
        john@email.com,1000,3,15,2025-01-15,payroll,Government Loan,Payroll client<br>
        mary@email.com,2500,6,15,2025-01-15,bank_transfer,,
      </div>
      <table style="width:100%;font-size:12px;border-collapse:collapse">
        <tr style="background:#f8fafc"><th style="padding:6px 8px;text-align:left;border-bottom:1px solid #e2e8f0">Column</th><th style="padding:6px 8px;text-align:left;border-bottom:1px solid #e2e8f0">Required?</th><th style="padding:6px 8px;text-align:left;border-bottom:1px solid #e2e8f0">Default</th></tr>
        @foreach([
          ['email','Yes','—'],
          ['principal_amount','Yes','—'],
          ['term_months','No','1'],
          ['interest_rate','No','15'],
          ['disbursement_date','No','Today'],
          ['collection_method','No','payroll'],
          ['product','No','First active product'],
          ['notes','No','—'],
        ] as [$col,$req,$def])
        <tr style="{{ $loop->even?'background:#f8fafc':'' }}">
          <td style="padding:6px 8px;border-bottom:1px solid #f1f5f9;font-family:monospace;color:#4f46e5">{{ $col }}</td>
          <td style="padding:6px 8px;border-bottom:1px solid #f1f5f9;color:{{ $req==='Yes'?'#dc2626':'#64748b' }}">{{ $req }}</td>
          <td style="padding:6px 8px;border-bottom:1px solid #f1f5f9;color:#64748b">{{ $def }}</td>
        </tr>
        @endforeach
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-download"></i> Download Template</span></div>
    <div style="padding:14px 16px">
      <p style="font-size:13px;color:var(--muted);margin-bottom:10px">Download a ready-to-fill template with the correct headers.</p>
      <a href="data:text/csv;charset=utf-8,email,principal_amount,term_months,interest_rate,disbursement_date,collection_method,product,notes%0Ajohn%40example.com,1000,3,15,{{ now()->format('Y-m-d') }},payroll,Government Loan,Example"
         download="loan-import-template.csv" class="btn btn-outline btn-sm">
        <i class="bi bi-file-earmark-spreadsheet"></i> Download Template CSV
      </a>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-exclamation-circle"></i> Important Notes</span></div>
    <div style="padding:14px 16px;font-size:13px;color:var(--muted);line-height:1.8">
      <p>• The borrower must already exist in the system (matched by email).</p>
      <p>• Calculations use flat interest: 15%/month + 40% initiation + M50 admin.</p>
      <p>• Max term is 6 months per product rules.</p>
      <p>• Duplicate imports are not prevented — check carefully.</p>
    </div>
  </div>

</div>
</div>

<script>
function showFileName(input) {
  const name = input.files[0]?.name;
  document.getElementById('fileNameDisplay').textContent = name ? '📄 ' + name : '';
  document.getElementById('uploadBtn').disabled = !name;
}
function handleDrop(e) {
  e.preventDefault();
  document.getElementById('dropZone').style.background = '#f8f9ff';
  const file = e.dataTransfer.files[0];
  if (file && (file.name.endsWith('.csv') || file.name.endsWith('.txt'))) {
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('csvFile').files = dt.files;
    showFileName(document.getElementById('csvFile'));
  }
}
</script>
@endsection
