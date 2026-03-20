@extends('borrower.layouts.app')
@section('title','Application #'.$application->application_number)
@section('content')
@php $sc=['submitted'=>'bi','under_review'=>'bi','info_requested'=>'bw','approved'=>'bok','declined'=>'be','disbursed'=>'bp','draft'=>'bs']; @endphp
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:20px">
  <div>
    <div style="font-size:20px;font-weight:800">{{ $application->application_number }}</div>
    <div style="font-size:13px;color:var(--muted)">{{ $application->loanProduct?->name ?? '—' }} &nbsp;·&nbsp; Submitted {{ $application->submitted_at?->format('d M Y') ?? 'Draft' }}</div>
  </div>
  <span class="badge {{ $sc[$application->status]??'bs' }}" style="font-size:14px;padding:8px 18px">{{ ucfirst(str_replace('_',' ',$application->status)) }}</span>
</div>

@if($application->status === 'info_requested')
<div class="alert a-w">
  <i class="bi bi-exclamation-triangle-fill"></i>
  <div><strong>Additional Information Required</strong><br>{{ $application->admin_notes }}<br><br>
  <form method="POST" action="{{ route('borrower.applications.respond-info',$application) }}" style="margin-top:10px">@csrf
    <textarea name="response" class="fc" rows="3" placeholder="Your response..." required></textarea>
    <button type="submit" class="btn btn-p btn-sm" style="margin-top:8px">Submit Response</button>
  </form></div>
</div>
@endif

@if($application->status === 'approved')
<div class="alert a-ok">
  <i class="bi bi-check-circle-fill"></i>
  <div><strong>Congratulations! Your loan has been approved.</strong><br>
  <a href="{{ route('borrower.applications.accept-terms',$application) }}" class="btn btn-ok btn-sm" style="margin-top:8px">Accept Terms & Proceed</a></div>
</div>
@endif

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
  @foreach(['Amount'=>'M '.number_format($application->requested_amount??0,2),'Term'=>($application->requested_term??'—').' months','Product'=>$application->loanProduct?->name??'—'] as $l=>$v)
  <div style="background:#f8fafc;border-radius:12px;padding:14px;border:1px solid var(--border)">
    <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em">{{ $l }}</div>
    <div style="font-size:16px;font-weight:800;margin-top:4px">{{ $v }}</div>
  </div>
  @endforeach
</div>

@if($application->documents->count())
<div class="card">
  <div class="card-hdr"><span class="card-title">Documents</span></div>
  <div style="overflow-x:auto">
    <table class="dt"><thead><tr><th>Type</th><th>File</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @foreach($application->documents as $doc)
      <tr>
        <td style="font-weight:600">{{ ucfirst(str_replace('_',' ',$doc->type)) }}</td>
        <td style="font-size:12.5px;color:var(--muted)">{{ $doc->original_name }}</td>
        <td><span class="badge {{ $doc->status==='verified'?'bok':($doc->status==='rejected'?'be':'bw') }}">{{ ucfirst($doc->status) }}</span></td>
        <td><a href="{{ route('borrower.documents.download',$doc) }}" class="btn btn-xs btn-o"><i class="bi bi-download"></i></a></td>
      </tr>
      @endforeach
    </tbody></table>
  </div>
</div>
@endif

@if($application->notes->count())
<div class="card">
  <div class="card-hdr"><span class="card-title">Messages from Lender</span></div>
  <div class="card-body">
    @foreach($application->notes as $note)
    <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;margin-bottom:10px">
      <div style="font-size:12px;color:var(--muted);margin-bottom:4px">{{ $note->created_at->format('d M Y H:i') }}</div>
      <div style="font-size:13.5px">{{ $note->content }}</div>
    </div>
    @endforeach
  </div>
</div>
@endif
@endsection
