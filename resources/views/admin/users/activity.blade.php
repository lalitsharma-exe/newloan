@extends('admin.layouts.app')
@section('title','User Activity')
@section('page-title','Activity Log')
@section('bc','<a href="'.route('admin.users.index').'">Users</a> / <a href="'.route('admin.users.show',$user).'">{{ $user->name }}</a> / Activity')
@section('content')

<div style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
  <div class="av av-lg">{{ strtoupper(substr($user->name,0,1)) }}</div>
  <div>
    <div style="font-size:18px;font-weight:700">{{ $user->name }}</div>
    <div class="muted">{{ $user->email }} &nbsp;·&nbsp; <span class="badge {{ $user->role==='admin'?'be':'bi' }}">{{ ucfirst(str_replace('_',' ',$user->role)) }}</span></div>
  </div>
  <a href="{{ route('admin.users.show',$user) }}" class="btn btn-o btn-sm" style="margin-left:auto"><i class="bi bi-arrow-left"></i> Back to Profile</a>
</div>

<div class="card">
  <div class="card-hdr">
    <span class="card-title">Activity Log ({{ $logs->total() }} entries)</span>
    <span class="muted" style="font-size:12px">Most recent first</span>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>Time</th><th>Action</th><th>Module</th><th>Description</th><th>IP Address</th></tr></thead>
      <tbody>
        @forelse($logs as $log)
        <tr>
          <td class="muted" style="white-space:nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
          <td><span class="badge bs" style="font-family:monospace;font-size:11px">{{ $log->action }}</span></td>
          <td><span class="badge bp">{{ $log->module ?? ucfirst(explode('.',$log->action)[0]) }}</span></td>
          <td style="font-size:13px">{{ $log->description }}</td>
          <td class="muted" style="font-size:12px">{{ $log->ip_address ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="5"><div class="empty"><i class="bi bi-journal-x"></i><p>No activity recorded for this user</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($logs->hasPages())
  <div style="padding:14px 18px;border-top:1px solid var(--border)">{{ $logs->links() }}</div>
  @endif
</div>
@endsection
