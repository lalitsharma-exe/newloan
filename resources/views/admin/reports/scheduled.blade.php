@extends('admin.layouts.app')
@section('title','Scheduled Reports')
@section('page-title','Scheduled Reports')
@section('bc','<a href="'.route('admin.reports.index').'">Reports</a> / Scheduled')
@section('content')
<div style="max-width:860px">

{{-- Create form --}}
<div class="card" style="margin-bottom:24px">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-calendar-plus" style="color:var(--p)"></i> Schedule New Report</span></div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.reports.scheduled.store') }}">@csrf
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:14px">
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Report Type *</label>
          <select name="report_type" class="fc" required>
            @foreach(['portfolio'=>'Loan Portfolio','disbursement'=>'Disbursement','repayment'=>'Repayment','arrears'=>'Arrears','collections'=>'Collections','product_performance'=>'Product Performance','officer_performance'=>'Officer Performance','income_statement'=>'Income Statement'] as $v=>$l)
            <option value="{{ $v }}">{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Frequency *</label>
          <select name="frequency" class="fc" required>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly" selected>Monthly</option>
          </select>
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Send to Email *</label>
          <input type="email" name="email" class="fc" placeholder="email@domain.com" required>
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Format *</label>
          <select name="format" class="fc" required>
            <option value="csv">CSV</option>
            <option value="pdf">PDF</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-p"><i class="bi bi-plus-lg"></i> Create Schedule</button>
    </form>
  </div>
</div>

{{-- Existing schedules --}}
<div class="card">
  <div class="card-hdr">
    <span class="card-title">Active Schedules</span>
    <span style="background:var(--bg);color:var(--muted);font-size:12px;font-weight:600;padding:3px 9px;border-radius:20px">{{ count($scheduled) }}</span>
  </div>
  @if(count($scheduled) > 0)
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>Report</th><th>Frequency</th><th>Email</th><th>Format</th><th>Created</th><th style="text-align:right">Action</th></tr></thead>
      <tbody>
        @foreach($scheduled as $s)
        @php
        $freq = ['daily'=>['#10b981','rgba(16,185,129,.1)'],'weekly'=>['#4f46e5','rgba(79,70,229,.1)'],'monthly'=>['#f59e0b','rgba(245,158,11,.1)']];
        [$fc,$fb] = $freq[$s->frequency] ?? ['#64748b','rgba(100,116,139,.1)'];
        @endphp
        <tr>
          <td style="font-weight:600;font-size:13px">{{ ucfirst(str_replace('_',' ',$s->report_type)) }}</td>
          <td><span style="background:{{ $fb }};color:{{ $fc }};font-size:11.5px;font-weight:600;padding:3px 10px;border-radius:20px">{{ ucfirst($s->frequency) }}</span></td>
          <td style="font-size:13px;color:var(--muted)">{{ $s->email }}</td>
          <td><span style="background:var(--bg);font-size:11.5px;font-weight:600;padding:3px 9px;border-radius:20px;text-transform:uppercase;color:var(--muted)">{{ $s->format }}</span></td>
          <td style="font-size:12px;color:var(--muted)">{{ \Carbon\Carbon::parse($s->created_at)->format('d M Y') }}</td>
          <td style="text-align:right">
            <form method="POST" action="{{ route('admin.reports.scheduled.destroy', $s->id) }}">@csrf @method('DELETE')
              <button class="btn btn-xs btn-e"><i class="bi bi-trash"></i> Remove</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
  <div style="text-align:center;padding:60px;color:var(--muted)">
    <i class="bi bi-calendar-x" style="font-size:44px;opacity:.2;display:block;margin-bottom:12px"></i>
    <div style="font-weight:600;margin-bottom:6px">No scheduled reports yet</div>
    <div style="font-size:13px">Create a schedule above to automatically receive reports by email</div>
  </div>
  @endif
</div>
</div>
@endsection