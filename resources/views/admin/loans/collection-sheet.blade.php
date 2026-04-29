@extends('admin.layouts.app')
@section('title','Collection Sheet')
@section('page-title','Collection Sheet')
@section('bc','<a href="'.route('admin.loans.index').'">Loans</a> / Collection Sheet')
@section('content')

{{-- Filter bar --}}
<form method="GET" action="{{ route('admin.loans.collection-sheet') }}"
  style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px 18px;margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
  <div class="form-group" style="margin:0;min-width:160px">
    <label class="form-label">Date (due on or before)</label>
    <input type="date" name="date" class="form-control" value="{{ $date }}">
  </div>
  <div class="form-group" style="margin:0;min-width:200px">
    <label class="form-label">Loan Officer</label>
    <select name="officer_id" class="form-control">
      <option value="">All Officers</option>
      @foreach($officers as $o)
      <option value="{{ $o->id }}" {{ $officerId == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
      @endforeach
    </select>
  </div>
  <div style="display:flex;gap:8px">
    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
    <button type="button" onclick="window.print()" class="btn btn-outline"><i class="bi bi-printer"></i> Print</button>
  </div>
</form>

{{-- Summary cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px">
  @foreach([
    ['Total Due', 'M '.number_format($data['total_due'],2), 'cash-coin', '#4f46e5', 'rgba(79,70,229,.08)'],
    ['Clients', $data['total_count'], 'people-fill', '#0891b2', 'rgba(8,145,178,.08)'],
    ['Overdue', $data['overdue_count'], 'exclamation-circle-fill', '#dc2626', 'rgba(220,38,38,.08)'],
    ['Officers', count($data['by_officer']), 'person-badge-fill', '#059669', 'rgba(5,150,105,.08)'],
  ] as [$lbl,$val,$icon,$color,$bg])
  <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px;display:flex;align-items:center;gap:14px">
    <div style="width:46px;height:46px;border-radius:12px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;color:{{ $color }};font-size:20px;flex-shrink:0">
      <i class="bi bi-{{ $icon }}"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:var(--dark);line-height:1">{{ $val }}</div>
      <div style="font-size:11.5px;color:var(--muted);margin-top:2px">{{ $lbl }}</div>
    </div>
  </div>
  @endforeach
</div>

@if($data['installments']->isEmpty())
<div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:60px;text-align:center;color:var(--muted)">
  <i class="bi bi-check-circle-fill" style="font-size:48px;color:#10b981;display:block;margin-bottom:12px;opacity:.5"></i>
  <div style="font-size:16px;font-weight:700;margin-bottom:6px">All Clear</div>
  <div style="font-size:13px">No payments due on or before {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</div>
</div>
@else

{{-- By officer grouping --}}
@foreach($data['by_officer'] as $officerName => $installments)
<div class="card" style="margin-bottom:20px">
  <div class="card-hdr" style="background:linear-gradient(90deg,rgba(79,70,229,.06),transparent)">
    <div style="display:flex;align-items:center;gap:10px">
      <div style="width:34px;height:34px;border-radius:50%;background:#e0e7ff;display:flex;align-items:center;justify-content:center;color:#4f46e5;font-weight:700;font-size:14px">
        {{ strtoupper(substr($officerName,0,1)) }}
      </div>
      <div>
        <div style="font-weight:700;font-size:14px">{{ $officerName }}</div>
        <div style="font-size:11px;color:var(--muted)">{{ $installments->count() }} clients &nbsp;·&nbsp; Total due: <strong style="color:#ef4444">M{{ number_format($installments->sum('outstanding_amount'),2) }}</strong></div>
      </div>
    </div>
    <button type="button" onclick="printSection('officer_{{ $loop->index }}')" class="btn btn-sm btn-o no-print">
      <i class="bi bi-printer"></i> Print This Officer
    </button>
  </div>

  <div id="officer_{{ $loop->index }}" style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>#</th>
          <th>Borrower</th>
          <th>Phone</th>
          <th>Loan #</th>
          <th>Due Date</th>
          <th>Branch Code</th>
          <th>Installment</th>
          <th>Penalty</th>
          <th>Total Due</th>
          <th>Status</th>
          <th class="no-print">Collection Method</th>
          <th class="no-print print-signature">Signature / Receipt #</th>
        </tr>
      </thead>
      <tbody>
        @foreach($installments as $i => $inst)
        <tr style="{{ $inst->status==='overdue'?'background:#fef2f2':'' }}">
          <td style="font-size:12px;color:var(--muted)">{{ $i+1 }}</td>
          <td>
            <div style="font-weight:600;font-size:13px">{{ $inst->loan->user->name ?? 'Deleted User' }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $inst->loan->user->phone ?? '—' }}</div>
          </td>
          <td style="font-size:12.5px">{{ $inst->loan->user->phone ?? '—' }}</td>
          <td><span style="font-weight:700;color:#4f46e5;font-size:12px">{{ $inst->loan->loan_number ?? '—' }}</span></td>
          <td style="font-size:12.5px;{{ now()->isAfter($inst->due_date)?'color:#ef4444;font-weight:600':'' }}">
            {{ $inst->due_date->format('d M Y') }}
            @if(now()->isAfter($inst->due_date))
            <div style="font-size:10px">{{ now()->diffInDays($inst->due_date) }} days overdue</div>
            @endif
          </td>
          <td style="font-size:12.5px"><code>{{ str_pad($inst->loan->application?->bankDetails?->branch_code ?? '', 6, '0', STR_PAD_LEFT) }}</code></td>
          <td style="font-size:12.5px">M{{ number_format($inst->total_amount,2) }}</td>
          <td style="color:#ef4444;font-size:12.5px">{{ $inst->late_fee > 0 ? 'M'.number_format($inst->late_fee,2) : '—' }}</td>
          <td><strong style="color:#1e3a5f">M{{ number_format($inst->outstanding_amount,2) }}</strong></td>
          <td>
            <span class="badge {{ $inst->status==='overdue'?'be':($inst->status==='partial'?'bw':'bs') }}">
              {{ ucfirst($inst->status) }}
            </span>
          </td>
          <td class="no-print" style="font-size:12px">{{ ucwords(str_replace('_',' ',$inst->loan->collection_method??'')) }}</td>
          <td class="print-signature" style="border:1px dashed #d1d5db;min-width:140px;height:36px"></td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="background:#f8fafc;font-weight:700">
          <td colspan="8" style="padding:10px 12px;font-size:13px;text-align:right">Officer Total:</td>
          <td style="padding:10px 12px;color:#1e3a5f;font-size:14px">M{{ number_format($installments->sum('outstanding_amount'),2) }}</td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endforeach

{{-- Grand total --}}
<div style="background:#1e3a5f;border-radius:12px;padding:16px 22px;display:flex;justify-content:space-between;align-items:center;color:#fff">
  <div style="font-size:14px;font-weight:600">Grand Total — {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</div>
  <div style="font-size:28px;font-weight:800">M{{ number_format($data['total_due'],2) }}</div>
</div>
@endif

<style>
@media print {
  .no-print, .sidebar, nav, .topbar, form, .filter-bar { display:none!important }
  .print-signature { display:table-cell!important }
  body { font-size:11px }
  .card { border:1px solid #000!important;break-inside:avoid }
}
</style>

<script>
function printSection(id) {
  const el = document.getElementById(id);
  const w = window.open('','_blank');
  w.document.write('<html><head><title>Collection Sheet</title><style>body{font-family:Arial;font-size:11px}table{width:100%;border-collapse:collapse}th,td{border:0.5px solid #ccc;padding:6px 8px;text-align:left}th{background:#f3f4f6;font-weight:600}</style></head><body>'+el.outerHTML+'</body></html>');
  w.document.close();
  w.print();
}
</script>
@endsection
