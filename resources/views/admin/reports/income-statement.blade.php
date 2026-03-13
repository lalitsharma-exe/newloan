@extends('admin.layouts.app')
@section('title','Income Statement')
@section('page-title','Income Statement')
@section('bc','<a href="'.route('admin.reports.index').'">Reports</a> / Income Statement')
@section('content')

<form method="GET" action="{{ route('admin.reports.income-statement') }}" style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px 18px;margin-bottom:24px;display:flex;gap:12px;align-items:flex-end">
  <div class="fg" style="margin-bottom:0">
    <label class="fl">From</label>
    <input type="date" name="date_from" class="fc" value="{{ $filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d') }}">
  </div>
  <div class="fg" style="margin-bottom:0">
    <label class="fl">To</label>
    <input type="date" name="date_to" class="fc" value="{{ $filters['date_to'] ?? now()->format('Y-m-d') }}">
  </div>
  <button type="submit" class="btn btn-p"><i class="bi bi-funnel"></i> Apply</button>
  <a href="{{ route('admin.reports.export', array_merge(request()->query(),['type'=>'income_statement','format'=>'csv'])) }}" class="btn btn-o"><i class="bi bi-download"></i> Export CSV</a>
</form>

<div style="max-width:700px">
  <div class="card">
    <div class="card-hdr">
      <span class="card-title">Income Statement</span>
      <span style="font-size:12.5px;color:var(--muted)">{{ \Carbon\Carbon::parse($data['from'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($data['to'])->format('d M Y') }}</span>
    </div>
    <div class="card-body">
      {{-- Income section --}}
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:12px">Income</div>

      @foreach([
        ['Interest Income',      $data['interest'],  '#10b981'],
        ['Processing Fees',      $data['fees'],      '#4f46e5'],
        ['Late Payment Fees',    $data['lateFees'],  '#f59e0b'],
      ] as [$label,$amount,$color])
      <div style="display:flex;justify-content:space-between;align-items:center;padding:11px 14px;border-radius:10px;margin-bottom:6px;background:#f8fafc">
        <div style="font-size:13.5px;font-weight:500;color:var(--dark)">{{ $label }}</div>
        <div style="font-size:14px;font-weight:700;color:{{ $color }}">L {{ number_format($amount,2) }}</div>
      </div>
      @endforeach

      @php $totalIncome = $data['interest'] + $data['fees'] + $data['lateFees']; @endphp
      <div style="display:flex;justify-content:space-between;align-items:center;padding:13px 14px;border-radius:10px;background:rgba(79,70,229,.06);border:1px solid rgba(79,70,229,.15);margin-bottom:24px">
        <div style="font-size:14px;font-weight:700;color:var(--p)">Total Income</div>
        <div style="font-size:18px;font-weight:800;color:var(--p)">L {{ number_format($totalIncome,2) }}</div>
      </div>

      {{-- Outflow section --}}
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:12px">Outflows / Provisions</div>

      <div style="display:flex;justify-content:space-between;align-items:center;padding:11px 14px;border-radius:10px;margin-bottom:6px;background:#f8fafc">
        <div style="font-size:13.5px;font-weight:500;color:var(--dark)">Loans Disbursed</div>
        <div style="font-size:14px;font-weight:700;color:#64748b">L {{ number_format($data['disbursed'],2) }}</div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:11px 14px;border-radius:10px;margin-bottom:6px;background:#f8fafc">
        <div style="font-size:13.5px;font-weight:500;color:var(--dark)">Write-offs</div>
        <div style="font-size:14px;font-weight:700;color:#ef4444">L {{ number_format($data['writeOffs'],2) }}</div>
      </div>

      {{-- Net --}}
      @php $net = $totalIncome - $data['writeOffs']; @endphp
      <div style="border-top:2px solid var(--border);margin:16px 0;padding-top:16px">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-radius:12px;background:{{ $net >= 0 ? 'rgba(16,185,129,.07)' : 'rgba(239,68,68,.07)' }};border:1px solid {{ $net >= 0 ? 'rgba(16,185,129,.2)' : 'rgba(239,68,68,.2)' }}">
          <div style="font-size:15px;font-weight:700;color:var(--dark)">Net Income (after write-offs)</div>
          <div style="font-size:22px;font-weight:800;color:{{ $net >= 0 ? '#10b981' : '#ef4444' }}">L {{ number_format($net,2) }}</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection