@extends('admin.layouts.app')
@section('title','Credit Reports')
@section('page-title','Credit Reports')
@section('bc','<a href="'.route('admin.credit.index').'">Credit Bureau</a> / Reports')
@section('content')

<div class="card">
  <div class="card-hdr">
    <span class="card-title">All Credit Reports ({{ $reports->total() }})</span>
    <a href="{{ route('admin.credit.index') }}" class="btn btn-o btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>Borrower</th><th>National ID</th><th>Type</th><th>Provider</th><th>Score</th><th>Status</th><th>Retrieved</th><th>Action</th></tr></thead>
      <tbody>
        @forelse($reports as $r)
        <tr>
          <td>
            <div style="font-weight:600;font-size:13px">{{ $r->user->name ?? '—' }}</div>
            <div class="muted">{{ $r->user->email ?? '' }}</div>
          </td>
          <td class="muted">{{ $r->national_id }}</td>
          <td><span class="badge {{ $r->check_type==='hard'?'be':'bi' }}">{{ ucfirst($r->check_type) }}</span></td>
          <td class="muted">{{ $r->provider }}</td>
          <td>
            @if($r->credit_score)
              <span style="font-weight:700;color:{{ $r->credit_score>=700?'#10b981':($r->credit_score>=500?'#f59e0b':'#ef4444') }}">
                {{ $r->credit_score }}
              </span>
            @else
              <span class="muted">—</span>
            @endif
          </td>
          <td><span class="badge {{ $r->status==='retrieved'?'bok':($r->status==='failed'?'be':'bs') }}">{{ ucfirst($r->status) }}</span></td>
          <td class="muted">{{ $r->retrieved_at?->format('d M Y H:i') ?? $r->created_at->format('d M Y') }}</td>
          <td><a href="{{ route('admin.credit.view-report',$r) }}" class="btn btn-xs btn-p"><i class="bi bi-eye"></i> View</a></td>
        </tr>
        @empty
        <tr><td colspan="8"><div class="empty"><i class="bi bi-shield-x"></i><p>No credit reports yet</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($reports->hasPages())
  <div style="padding:14px 18px;border-top:1px solid var(--border)">{{ $reports->links() }}</div>
  @endif
</div>
@endsection
