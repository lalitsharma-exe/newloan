@extends('borrower.layouts.app')
@section('title','Notifications')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
  <div style="font-size:20px;font-weight:800">Notifications</div>
  <form method="POST" action="{{ route('borrower.notifications.read-all') }}">@csrf<button type="submit" class="btn btn-o btn-sm"><i class="bi bi-check-all"></i> Mark All Read</button></form>
</div>
<div class="card">
  @forelse($notifications as $n)
  <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;gap:12px;background:{{ $n->is_read?'':'rgba(26,92,46,.02)' }}">
    <div style="width:36px;height:36px;border-radius:50%;background:{{ $n->is_read?'var(--bg)':'rgba(26,92,46,.1)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--p)"><i class="bi bi-bell{{ $n->is_read?'':'-fill' }}"></i></div>
    <div style="flex:1">
      <div style="font-size:13.5px;font-weight:{{ $n->is_read?'400':'700' }}">{{ $n->title ?? $n->message ?? 'Notification' }}</div>
      @if($n->message && $n->title)<div style="font-size:12.5px;color:var(--muted);margin-top:2px">{{ $n->message }}</div>@endif
      <div style="font-size:11.5px;color:var(--muted);margin-top:4px">{{ $n->created_at->diffForHumans() }}</div>
    </div>
    @if(!$n->is_read)<form method="POST" action="{{ route('borrower.notifications.read',$n->id) }}">@csrf<button type="submit" class="btn btn-xs btn-o">Read</button></form>@endif
  </div>
  @empty
  <div style="text-align:center;padding:50px;color:var(--muted)"><i class="bi bi-bell-slash" style="font-size:44px;opacity:.25;display:block;margin-bottom:12px"></i><div style="font-weight:600">No notifications</div></div>
  @endforelse
</div>
@if($notifications->hasPages())<div style="margin-top:14px">{{ $notifications->links() }}</div>@endif
@endsection
