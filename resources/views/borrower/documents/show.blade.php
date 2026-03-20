@extends('borrower.layouts.app')
@section('title','Document')
@section('content')
<div class="card" style="max-width:480px">
  <div class="card-hdr"><span class="card-title">{{ ucfirst(str_replace('_',' ',$document->type)) }}</span><span class="badge {{ $document->status==='verified'?'bok':($document->status==='rejected'?'be':'bw') }}">{{ ucfirst($document->status) }}</span></div>
  <div class="card-body">
    @foreach(['File Name'=>$document->original_name,'Type'=>ucfirst(str_replace('_',' ',$document->type)),'Uploaded'=>$document->created_at->format('d M Y H:i'),'Application'=>$document->application?->application_number??'—'] as $l=>$v)
    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13.5px"><span style="color:var(--muted)">{{ $l }}</span><span style="font-weight:600">{{ $v }}</span></div>
    @endforeach
    <div style="margin-top:16px;display:flex;gap:8px">
      <a href="{{ route('borrower.documents.download',$document) }}" class="btn btn-p btn-sm"><i class="bi bi-download"></i> Download</a>
      <a href="{{ route('borrower.documents.index') }}" class="btn btn-o btn-sm">Back</a>
    </div>
  </div>
</div>
@endsection
