@extends('admin.layouts.app')
@section('title','Notifications')
@section('page-title','Notifications')
@section('bc','<a href="'.route('admin.dashboard').'">Home</a> / Notifications')
@section('content')
<div style="max-width:820px">
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px">
  <div>
    <div style="font-size:22px;font-weight:800;color:var(--dark)">Notifications</div>
    <div style="font-size:13px;color:var(--muted);margin-top:3px">
      @if($unreadCount > 0)<span style="color:var(--p);font-weight:700">{{ $unreadCount }}</span> unread@else All caught up @endif
    </div>
  </div>
  @if($unreadCount > 0)
  <form method="POST" action="{{ route('admin.notifications.read-all') }}">@csrf
    <button class="btn btn-p btn-sm"><i class="bi bi-check-all"></i> Mark All Read</button>
  </form>
  @endif
</div>
<div class="card">
  @forelse($notifications as $n)
  @php
  $isPayment = str_contains($n->type,'payment');
  $isApproved = str_contains($n->type,'approved')||str_contains($n->type,'verified');
  $isDanger = str_contains($n->type,'overdue')||str_contains($n->type,'declined');
  $iconBg = $isApproved ? 'rgba(22,163,74,.1)' : ($isPayment ? 'rgba(79,70,229,.1)' : ($isDanger ? 'rgba(239,68,68,.1)' : 'rgba(100,116,139,.1)'));
  $iconColor = $isApproved ? '#10b981' : ($isPayment ? '#4f46e5' : ($isDanger ? '#ef4444' : '#64748b'));
  $icon = $isPayment ? 'cash-coin' : (str_contains($n->type,'loan') ? 'bank' : (str_contains($n->type,'application') ? 'file-earmark-text' : 'bell'));
  @endphp
  <div style="display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border-bottom:1px solid var(--border);background:{{ !$n->is_read ? 'rgba(79,70,229,.025)' : 'transparent' }}">
    <div style="width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;background:{{ $iconBg }};color:{{ $iconColor }}">
      <i class="bi bi-{{ $icon }}"></i>
    </div>
    <div style="flex:1;min-width:0">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
        <span style="font-size:13.5px;font-weight:{{ $n->is_read ? '500' : '700' }};color:var(--dark)">{{ $n->title }}</span>
        @if(!$n->is_read)<span style="width:7px;height:7px;border-radius:50%;background:var(--p);flex-shrink:0;display:inline-block"></span>@endif
      </div>
      @if($n->body)<div style="font-size:13px;color:var(--muted);line-height:1.5;margin-bottom:5px">{{ $n->body }}</div>@endif
      <div style="font-size:11.5px;color:var(--muted)">{{ $n->created_at->diffForHumans() }} · {{ $n->created_at->format('d M Y H:i') }}</div>
    </div>
    <div style="display:flex;gap:6px;flex-shrink:0">
      @if($n->link)<a href="{{ $n->link }}" class="btn btn-xs btn-p"><i class="bi bi-arrow-right"></i></a>@endif
      @if(!$n->is_read)
      <form method="POST" action="{{ route('admin.notifications.read', $n->id) }}">@csrf
        <button class="btn btn-xs btn-o" title="Mark read"><i class="bi bi-check-lg"></i></button>
      </form>
      @endif
      <form method="POST" action="{{ route('admin.notifications.destroy', $n->id) }}">@csrf @method('DELETE')
        <button style="background:none;border:1px solid var(--border);color:var(--muted);padding:4px 8px;border-radius:7px;cursor:pointer;font-size:12px" title="Delete"><i class="bi bi-trash"></i></button>
      </form>
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:80px 20px;color:var(--muted)">
    <i class="bi bi-bell-slash" style="font-size:52px;opacity:.2;display:block;margin-bottom:16px"></i>
    <div style="font-size:16px;font-weight:600;margin-bottom:6px">No notifications</div>
    <div style="font-size:13px">You're all caught up!</div>
  </div>
  @endforelse
</div>
@if($notifications->hasPages())<div style="margin-top:16px">{{ $notifications->links() }}</div>@endif
</div>
@endsection 