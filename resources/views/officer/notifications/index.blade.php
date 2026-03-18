@extends('officer.layouts.app')
@section('title','Notifications')
@section('page-title','Notifications')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
  <div style="font-size:13.5px;color:var(--muted)">Your recent activity alerts</div>
  @if($notifications->where('is_read',false)->count() > 0)
  <form method="POST" action="{{ route('officer.notifications.read-all') }}">@csrf
    <button class="btn btn-o btn-sm"><i class="bi bi-check2-all"></i> Mark All as Read</button>
  </form>
  @endif
</div>

@if(session('success'))
<div class="alert a-ok" style="margin-bottom:16px"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif

<div class="card">
  @forelse($notifications as $notif)
  <div style="padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:14px;background:{{ $notif->is_read ? '#fff' : 'rgba(26,92,46,.02)' }};transition:background .2s">
    {{-- Icon --}}
    <div style="width:40px;height:40px;border-radius:11px;background:{{ $notif->is_read?'var(--bg)':'rgba(26,92,46,.1)' }};display:flex;align-items:center;justify-content:center;font-size:17px;color:{{ $notif->is_read?'var(--muted)':'var(--p)' }};flex-shrink:0">
      <i class="bi bi-{{ $notif->is_read?'bell':'bell-fill' }}"></i>
    </div>

    {{-- Content --}}
    <div style="flex:1;min-width:0">
      <div style="font-size:13.5px;color:var(--dark);line-height:1.5;{{ !$notif->is_read?'font-weight:600':'' }}">
        {{ $notif->message ?? $notif->title ?? 'Notification' }}
      </div>
      @if($notif->body)
      <div style="font-size:12.5px;color:var(--muted);margin-top:3px">{{ $notif->body }}</div>
      @endif
      <div style="font-size:11px;color:var(--muted);margin-top:5px">
        <i class="bi bi-clock"></i> {{ $notif->created_at->diffForHumans() }}
        @if(!$notif->is_read)<span style="margin-left:8px;background:var(--p);color:#fff;font-size:10px;padding:1px 7px;border-radius:10px;font-weight:700">New</span>@endif
      </div>
    </div>

    {{-- Mark read --}}
    @if(!$notif->is_read)
    <form method="POST" action="{{ route('officer.notifications.read', $notif->id) }}" style="flex-shrink:0">@csrf
      <button class="btn btn-xs btn-o" title="Mark as read"><i class="bi bi-check2"></i></button>
    </form>
    @endif
  </div>
  @empty
  <div style="text-align:center;padding:70px;color:var(--muted)">
    <i class="bi bi-bell-slash" style="font-size:52px;opacity:.2;display:block;margin-bottom:14px"></i>
    <div style="font-weight:600;font-size:15px;margin-bottom:6px">No Notifications</div>
    <div style="font-size:13px">You're all caught up!</div>
  </div>
  @endforelse

  @if($notifications->hasPages())
  <div style="padding:14px 22px;border-top:1px solid var(--border)">{{ $notifications->links() }}</div>
  @endif
</div>
@endsection
