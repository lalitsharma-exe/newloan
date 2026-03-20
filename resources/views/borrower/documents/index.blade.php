@extends('borrower.layouts.app')
@section('title','Documents')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
  <div style="font-size:20px;font-weight:800">My Documents</div>
  <a href="{{ route('borrower.documents.upload') }}" class="btn btn-p btn-sm"><i class="bi bi-upload"></i> Upload</a>
</div>
<div class="card">
  <div style="overflow-x:auto">
    <table class="dt"><thead><tr><th>Type</th><th>File</th><th>Application</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody>
      @forelse($documents as $doc)
      <tr>
        <td style="font-weight:600">{{ ucfirst(str_replace('_',' ',$doc->type)) }}</td>
        <td style="font-size:12.5px;color:var(--muted)">{{ $doc->original_name }}</td>
        <td style="font-size:12px;color:var(--p)">{{ $doc->application?->application_number ?? '—' }}</td>
        <td><span class="badge {{ $doc->status==='verified'?'bok':($doc->status==='rejected'?'be':'bw') }}">{{ ucfirst($doc->status) }}</span></td>
        <td style="font-size:12px;color:var(--muted)">{{ $doc->created_at->format('d M Y') }}</td>
        <td style="display:flex;gap:5px">
          <a href="{{ route('borrower.documents.download',$doc) }}" class="btn btn-xs btn-o"><i class="bi bi-download"></i></a>
          @if($doc->status==='pending')<form method="POST" action="{{ route('borrower.documents.destroy',$doc) }}">@csrf @method('DELETE')<button class="btn btn-xs btn-e" onclick="return confirm('Remove this document?')"><i class="bi bi-trash3"></i></button></form>@endif
        </td>
      </tr>
      @empty
      <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No documents uploaded yet</td></tr>
      @endforelse
    </tbody></table>
  </div>
</div>
@endsection
