@extends('admin.layouts.app')
@section('title','Loan Schedule')
@section('page-title','Repayment Schedule')
@section('bc','<a href="'.route('admin.loans.index').'">Loans</a> / <a href="'.route('admin.loans.show',$loan).'">{{ $loan->loan_number }}</a> / Schedule')
@section('content')

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start">
  <div class="card">
    <div class="card-hdr"><span class="card-title">Loan Summary</span></div>
    <div style="padding:14px 18px;font-size:13px">
      @foreach([
        'Loan #'        => $loan->loan_number,
        'Borrower'      => $loan->user->name,
        'Principal'     => 'M'.number_format($loan->principal_amount,2),
        'Total Amount'  => 'M'.number_format($loan->total_amount,2),
        'Monthly'       => 'M'.number_format($loan->monthly_installment,2),
        'Outstanding'   => 'M'.number_format($loan->outstanding_balance,2),
        'Disbursed'     => $loan->disbursement_date?->format('d M Y') ?? '—',
        'Maturity'      => $loan->maturity_date?->format('d M Y') ?? '—',
        'Status'        => ucfirst(str_replace('_',' ',$loan->status)),
      ] as $l => $v)
      <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border)">
        <span style="color:var(--muted)">{{ $l }}</span>
        <span style="font-weight:600">{{ $v }}</span>
      </div>
      @endforeach
    </div>
    <div style="padding:12px 18px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:8px">
      <a href="{{ route('admin.loans.show',$loan) }}" class="btn btn-outline btn-sm"><i class="bi bi-arrow-left"></i> Back to Loan</a>
      <a href="{{ route('admin.loans.statement',$loan) }}" class="btn btn-outline btn-sm"><i class="bi bi-file-earmark-text"></i> Full Statement</a>
      <button onclick="window.print()" class="btn btn-outline btn-sm"><i class="bi bi-printer"></i> Print Schedule</button>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr"><span class="card-title">Full Repayment Schedule</span></div>
    <div style="overflow-x:auto">
      <table class="dt">
        <thead>
          <tr>
            <th>#</th><th>Due Date</th><th>Principal</th><th>Interest</th>
            <th>Initiation</th><th>Admin</th><th>Penalty</th><th>Total</th>
            <th>Paid</th><th>Outstanding</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
          @php $cumPaid = 0; @endphp
          @foreach($loan->installments as $i)
          @php $cumPaid += $i->paid_amount; @endphp
          <tr style="{{ $i->status==='overdue'?'background:#fef2f2':($i->status==='paid'?'background:#f0fdf4':''  ) }}">
            <td style="font-weight:700;color:var(--muted);font-size:12px">{{ $i->installment_number }}</td>
            <td style="{{ now()->isAfter($i->due_date) && $i->status!='paid'?'color:#ef4444;font-weight:600':'' }}">
              {{ $i->due_date->format('d M Y') }}
            </td>
            <td>M{{ number_format($i->principal_amount,2) }}</td>
            <td style="color:#f59e0b">M{{ number_format($i->interest_amount,2) }}</td>
            <td style="color:#8b5cf6">M{{ number_format($i->initiation_fee_amount??0,2) }}</td>
            <td style="color:#64748b">M{{ number_format($i->admin_fee_amount??0,2) }}</td>
            <td style="color:#ef4444">{{ $i->late_fee>0?'M'.number_format($i->late_fee,2):'—' }}</td>
            <td style="font-weight:700">M{{ number_format($i->total_amount,2) }}</td>
            <td style="color:#10b981">M{{ number_format($i->paid_amount,2) }}</td>
            <td style="{{ $i->outstanding_amount>0?'color:#ef4444':'' }}">M{{ number_format($i->outstanding_amount,2) }}</td>
            <td>
              <span class="badge {{ $i->status==='paid'?'bok':($i->status==='overdue'?'be':($i->status==='partial'?'bw':'bs')) }}">
                {{ ucfirst($i->status) }}
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr style="background:#f8fafc;font-weight:700">
            <td colspan="7" style="padding:10px 12px;text-align:right">Totals:</td>
            <td style="padding:10px 12px">M{{ number_format($loan->total_amount,2) }}</td>
            <td style="padding:10px 12px;color:#10b981">M{{ number_format($loan->installments->sum('paid_amount'),2) }}</td>
            <td style="padding:10px 12px;color:#ef4444">M{{ number_format($loan->outstanding_balance,2) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

@push('scripts')
@endpush
@endsection
