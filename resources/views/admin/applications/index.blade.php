@extends('admin.layouts.app')
@section('title','Applications')
@section('page-title','Applications')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Home</a> / Applications
@endsection
@section('content')


@if(session('success'))
<div style="background:rgba(16,185,129,.08);color:#065f46;border:1px solid rgba(16,185,129,.2);padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px;margin-bottom:20px">
  <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
  @php
  $cards = [
    ['Total',          $stats['total'],          'collection-fill',    '#4f46e5','rgba(79,70,229,.1)', ''],
    ['Pending Review', $stats['pending'],         'hourglass-split',    '#f59e0b','rgba(245,158,11,.1)', 'submitted'],
    ['Approved Today', $stats['approved_today'],  'check-circle-fill',  '#10b981','rgba(16,185,129,.1)','approved'],
    ['Declined Today', $stats['declined_today'],  'x-circle-fill',      '#ef4444','rgba(239,68,68,.1)', 'declined'],
  ];
  @endphp
  @foreach($cards as [$label,$val,$icon,$color,$bg,$status])
  <a href="{{ $status ? route('admin.applications.index',['status'=>$status]) : route('admin.applications.index') }}" style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px;display:flex;align-items:center;gap:16px;text-decoration:none;transition:all .2s" onmouseover="this.style.boxShadow='0 6px 20px rgba(0,0,0,.07)'" onmouseout="this.style.boxShadow='none'">
    <div style="width:52px;height:52px;border-radius:14px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;font-size:22px;color:{{ $color }};flex-shrink:0">
      <i class="bi bi-{{ $icon }}"></i>
    </div>
    <div>
      <div style="font-size:26px;font-weight:800;color:var(--dark);line-height:1">{{ number_format($val) }}</div>
      <div style="font-size:12px;color:var(--muted);margin-top:3px;font-weight:500">{{ $label }}</div>
    </div>
  </a>
  @endforeach
</div>

{{-- Toolbar: filters + actions --}}
<form method="GET" action="{{ route('admin.applications.index') }}" style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px 18px;margin-bottom:20px">
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div class="fg" style="margin-bottom:0;flex:2;min-width:180px">
      <label class="fl">Search</label>
      <div style="position:relative">
        <i class="bi bi-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px"></i>
        <input type="text" name="search" class="fc" style="padding-left:33px" placeholder="Name, App#, phone, email…" value="{{ $filters['search']??'' }}">
      </div>
    </div>
    <div class="fg" style="margin-bottom:0;min-width:145px">
      <label class="fl">Status</label>
      <select name="status" class="fc">
        <option value="">All Statuses</option>
        @foreach(['submitted'=>'Submitted','under_review'=>'Under Review','info_requested'=>'Info Requested','on_hold'=>'On Hold','approved'=>'Approved','declined'=>'Declined','disbursed'=>'Disbursed'] as $v=>$l)
        <option value="{{ $v }}" {{ ($filters['status']??'')===$v?'selected':'' }}>{{ $l }}</option>
        @endforeach
      </select>
    </div>
    <div class="fg" style="margin-bottom:0;min-width:145px">
      <label class="fl">Product</label>
      <select name="product" class="fc">
        <option value="">All Products</option>
        @foreach($products as $p)
        <option value="{{ $p->id }}" {{ ($filters['product']??'')==$p->id?'selected':'' }}>{{ $p->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="fg" style="margin-bottom:0;min-width:130px">
      <label class="fl">From</label>
      <input type="date" name="date_from" class="fc" value="{{ $filters['date_from']??'' }}">
    </div>
    <div class="fg" style="margin-bottom:0;min-width:130px">
      <label class="fl">To</label>
      <input type="date" name="date_to" class="fc" value="{{ $filters['date_to']??'' }}">
    </div>
    <div style="display:flex;gap:8px;flex-shrink:0">
      <button type="submit" class="btn btn-p"><i class="bi bi-funnel"></i> Filter</button>
      <a href="{{ route('admin.applications.index') }}" class="btn btn-o">Clear</a>
    </div>
  </div>
</form>

{{-- Table card --}}
<div class="card">
  <div class="card-hdr">
    <div style="display:flex;align-items:center;gap:10px">
      <span class="card-title">Applications</span>
      <span style="background:var(--bg);color:var(--muted);font-size:12px;font-weight:600;padding:3px 9px;border-radius:20px">{{ $applications->total() }}</span>
    </div>
    <div style="display:flex;gap:8px">
      <a href="{{ route('admin.applications.export', request()->query()) }}" class="btn btn-sm btn-o"><i class="bi bi-download"></i> Export CSV</a>
      <a href="{{ route('admin.applications.create') }}" class="btn btn-sm btn-p"><i class="bi bi-plus-lg"></i> New Application</a>
    </div>
  </div>

  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>App #</th>
          <th>Applicant</th>
          <th>Product</th>
          <th>Requested</th>
          <th>Risk</th>
          <th>Officer</th>
          <th>Status</th>
          <th>Submitted</th>
          <th style="text-align:right">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($applications as $app)
        <tr style="cursor:pointer" onclick="window.location='{{ route('admin.applications.show',$app) }}'">
          <td onclick="event.stopPropagation()">
            <a href="{{ route('admin.applications.show',$app) }}" style="font-weight:700;color:var(--p);font-size:12.5px;font-family:monospace;text-decoration:none">
              {{ $app->application_number }}
            </a>
          </td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0">
                {{ strtoupper(substr($app->applicant_name,0,1)) }}
              </div>
              <div>
                <div style="font-size:13px;font-weight:600">{{ $app->applicant_name }}</div>
                <div style="font-size:11.5px;color:var(--muted)">{{ $app->user->email ?? $app->email ?? '—' }}</div>
              </div>
            </div>
          </td>
          <td style="font-size:12.5px;color:var(--muted)">{{ $app->loanProduct->name ?? '—' }}</td>
          <td>
            <div style="font-weight:700;font-size:13.5px">L{{ number_format($app->requested_amount ?? 0, 0) }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $app->requested_term }}mo</div>
          </td>
          <td>
            @if($app->risk_score)
              @php $rc = $app->risk_score >= 700 ? '#10b981' : ($app->risk_score >= 500 ? '#f59e0b' : '#ef4444'); @endphp
              <span style="background:{{ $rc }}18;color:{{ $rc }};font-size:12px;font-weight:700;padding:3px 9px;border-radius:20px">{{ $app->risk_score }}</span>
            @else
              <span style="color:var(--muted);font-size:12px">—</span>
            @endif
          </td>
          <td>
            @if($app->assignedOfficer)
              <div style="font-size:12.5px;font-weight:600">{{ $app->assignedOfficer->name }}</div>
            @else
              <span style="color:var(--muted);font-size:12px;font-style:italic">Unassigned</span>
            @endif
          </td>
          <td>
            @php
            $badges = ['submitted'=>['#6366f1','#ede9fe'],'under_review'=>['#0891b2','#e0f2fe'],'info_requested'=>['#d97706','#fef3c7'],'on_hold'=>['#d97706','#fef3c7'],'approved'=>['#059669','#d1fae5'],'declined'=>['#dc2626','#fee2e2'],'disbursed'=>['#2563eb','#dbeafe']];
            [$tc,$bc] = $badges[$app->status] ?? ['#64748b','#f1f5f9'];
            @endphp
            <span style="background:{{ $bc }};color:{{ $tc }};font-size:11.5px;font-weight:600;padding:4px 10px;border-radius:20px;white-space:nowrap">
              {{ ucfirst(str_replace('_',' ',$app->status)) }}
            </span>
          </td>
          <td style="font-size:12px;color:var(--muted);white-space:nowrap">
            {{ ($app->submitted_at ?? $app->created_at)->format('d M Y') }}
          </td>
          <td onclick="event.stopPropagation()" style="text-align:right">
            <a href="{{ route('admin.applications.show',$app) }}" class="btn btn-xs btn-p">
              <i class="bi bi-eye"></i> Review
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9">
            <div style="text-align:center;padding:60px 20px;color:var(--muted)">
              <i class="bi bi-inbox" style="font-size:48px;opacity:.25;display:block;margin-bottom:14px"></i>
              <div style="font-weight:600;font-size:15px">No applications found</div>
              <div style="font-size:13px;margin-top:6px">Try adjusting your filters or create a new application</div>
              <a href="{{ route('admin.applications.create') }}" class="btn btn-p" style="margin-top:16px"><i class="bi bi-plus-lg"></i> New Application</a>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($applications->hasPages())
  <div style="padding:14px 20px;border-top:1px solid var(--border)">
    {{ $applications->withQueryString()->links() }}
  </div>
  @endif
</div>
@endsection