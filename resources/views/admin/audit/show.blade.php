@extends('admin.layouts.app')
@section('title','Audit Entry')
@section('page-title','Audit Entry Detail')
@section('bc','<a href="'.route('admin.audit.index').'">Audit Log</a> / Detail')
@section('content')
<div style="max-width:760px">
<div class="card">
  <div class="card-hdr">
    <span class="card-title"><code style="background:var(--bg);padding:3px 8px;border-radius:7px;font-size:13px">{{ $log->action }}</code></span>
    <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-o"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
  <div class="card-body">
    <div class="info-grid" style="margin-bottom:24px">
      <div><div class="info-lbl">Action</div><div class="info-val">{{ $log->action }}</div></div>
      <div><div class="info-lbl">Module</div><div class="info-val">{{ ucfirst($log->module ?? '—') }}</div></div>
      <div><div class="info-lbl">Performed By</div><div class="info-val">{{ $log->user_name ?? 'System' }}</div></div>
      <div><div class="info-lbl">IP Address</div><div class="info-val" style="font-family:monospace">{{ $log->ip_address ?? '—' }}</div></div>
      <div><div class="info-lbl">Subject</div><div class="info-val" style="color:var(--p)">{{ $log->subject_label ?? '—' }}</div></div>
      <div><div class="info-lbl">Date & Time</div><div class="info-val">{{ $log->created_at->format('d M Y H:i:s') }}</div></div>
    </div>
    <div class="fg"><div class="info-lbl" style="margin-bottom:6px">Description</div>
      <div style="background:#f8fafc;border-radius:10px;padding:14px;font-size:13.5px;color:#334155;line-height:1.6">{{ $log->description ?? '—' }}</div>
    </div>
    @if($log->old_values || $log->new_values)
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:18px">
      @if($log->old_values)
      <div>
        <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">Before</div>
        <div style="background:#fef2f2;border:1px solid #fee2e2;border-radius:10px;padding:12px;font-family:monospace;font-size:12px;white-space:pre-wrap;color:#991b1b">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</div>
      </div>
      @endif
      @if($log->new_values)
      <div>
        <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">After</div>
        <div style="background:#f0fdf4;border:1px solid #d1fae5;border-radius:10px;padding:12px;font-family:monospace;font-size:12px;white-space:pre-wrap;color:#065f46">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</div>
      </div>
      @endif
    </div>
    @endif
  </div>
</div>
</div>
@endsection