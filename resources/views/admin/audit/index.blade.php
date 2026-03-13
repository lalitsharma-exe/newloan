@extends('admin.layouts.app')
@section('title','Audit Log')
@section('page-title','Audit Log')
@section('bc','<a href="'.route('admin.dashboard').'">Home</a> / Audit Log')
@section('content')

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
  @foreach([['Today',$stats['today'],'clock-history','#4f46e5','rgba(79,70,229,.1)'],['This Week',$stats['week'],'calendar-week','#0891b2','rgba(8,145,178,.1)'],['This Month',$stats['month'],'calendar-month','#10b981','rgba(16,185,129,.1)'],['Total',$stats['total'],'archive','#64748b','rgba(100,116,139,.1)']] as [$label,$val,$icon,$color,$bg])
  <div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px;display:flex;align-items:center;gap:14px">
    <div style="width:48px;height:48px;border-radius:13px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;font-size:20px;color:{{ $color }};flex-shrink:0"><i class="bi bi-{{ $icon }}"></i></div>
    <div><div style="font-size:24px;font-weight:800;color:var(--dark)">{{ number_format($val) }}</div><div style="font-size:12px;color:var(--muted);font-weight:500">{{ $label }}</div></div>
  </div>
  @endforeach
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.audit.index') }}" style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px 18px;margin-bottom:20px">
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div class="fg" style="margin-bottom:0;flex:2;min-width:180px">
      <label class="fl">Search Description</label>
      <input type="text" name="search" class="fc" placeholder="Search log entries…" value="{{ $filters['search']??'' }}">
    </div>
    <div class="fg" style="margin-bottom:0;min-width:140px">
      <label class="fl">Module</label>
      <select name="module" class="fc">
        <option value="">All Modules</option>
        @foreach($modules as $m)<option value="{{ $m }}" {{ ($filters['module']??'')===$m?'selected':'' }}>{{ ucfirst($m) }}</option>@endforeach
      </select>
    </div>
    <div class="fg" style="margin-bottom:0;min-width:140px">
      <label class="fl">User</label>
      <input type="text" name="user" class="fc" placeholder="User name…" value="{{ $filters['user']??'' }}">
    </div>
    <div class="fg" style="margin-bottom:0;min-width:130px">
      <label class="fl">From</label>
      <input type="date" name="date_from" class="fc" value="{{ $filters['date_from']??'' }}">
    </div>
    <div class="fg" style="margin-bottom:0;min-width:130px">
      <label class="fl">To</label>
      <input type="date" name="date_to" class="fc" value="{{ $filters['date_to']??'' }}">
    </div>
    <div style="display:flex;gap:8px">
      <button type="submit" class="btn btn-p"><i class="bi bi-funnel"></i> Filter</button>
      <a href="{{ route('admin.audit.index') }}" class="btn btn-o">Clear</a>
    </div>
  </div>
</form>

{{-- Table --}}
<div class="card">
  <div class="card-hdr">
    <div style="display:flex;align-items:center;gap:10px">
      <span class="card-title">Audit Log</span>
      <span style="background:var(--bg);color:var(--muted);font-size:12px;font-weight:600;padding:3px 9px;border-radius:20px">{{ $logs->total() }}</span>
    </div>
    <a href="{{ route('admin.audit.export', request()->query()) }}" class="btn btn-sm btn-o"><i class="bi bi-download"></i> Export CSV</a>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Action</th>
          <th>Module</th>
          <th>User</th>
          <th>Subject</th>
          <th>Description</th>
          <th>IP</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
        @php
        $moduleColors = ['loan'=>'#4f46e5','application'=>'#0891b2','payment'=>'#10b981','user'=>'#f59e0b','product'=>'#8b5cf6','setting'=>'#64748b'];
        $mc = $moduleColors[$log->module ?? ''] ?? '#64748b';
        @endphp
        <tr>
          <td>
            <code style="font-size:11.5px;background:var(--bg);padding:3px 7px;border-radius:6px;color:var(--dark)">{{ $log->action }}</code>
          </td>
          <td>
            <span style="background:{{ $mc }}18;color:{{ $mc }};font-size:11.5px;font-weight:600;padding:3px 9px;border-radius:20px">{{ ucfirst($log->module ?? '—') }}</span>
          </td>
          <td>
            <div style="font-size:13px;font-weight:600">{{ $log->user_name ?? '—' }}</div>
          </td>
          <td>
            @if($log->subject_label)
            <span style="font-size:12.5px;font-family:monospace;color:var(--p)">{{ $log->subject_label }}</span>
            @else<span style="color:var(--muted)">—</span>@endif
          </td>
          <td style="max-width:280px">
            <div style="font-size:12.5px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $log->description }}</div>
          </td>
          <td style="font-size:12px;color:var(--muted);font-family:monospace">{{ $log->ip_address }}</td>
          <td style="font-size:12px;color:var(--muted);white-space:nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
          <td>
            <a href="{{ route('admin.audit.show', $log) }}" class="btn btn-xs btn-o"><i class="bi bi-eye"></i></a>
          </td>
        </tr>
        @empty
        <tr><td colspan="8">
          <div style="text-align:center;padding:60px;color:var(--muted)">
            <i class="bi bi-journal-x" style="font-size:44px;opacity:.2;display:block;margin-bottom:12px"></i>
            <div style="font-weight:600">No audit entries found</div>
          </div>
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($logs->hasPages())<div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $logs->withQueryString()->links() }}</div>@endif
</div>
@endsection