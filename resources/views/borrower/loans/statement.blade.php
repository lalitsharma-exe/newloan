@extends('borrower.layouts.app')
@section('title','Loan Statement')
@section('content')

<div style="max-width:800px;margin:0 auto">
  {{-- Print / Download bar --}}
  <div style="display:flex;gap:10px;margin-bottom:20px;justify-content:flex-end">
    <button onclick="window.print()" class="btn btn-o"><i class="bi bi-printer"></i> Print</button>
  </div>

  <div class="card" id="statementDoc" style="padding:0">
    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;padding:30px 36px;border-radius:16px 16px 0 0">
      <div style="display:flex;justify-content:space-between;align-items:flex-start">
        <div>
          <div style="font-size:22px;font-weight:800;letter-spacing:-.5px">Loan Statement</div>
          <div style="opacity:.8;font-size:13px;margin-top:5px">{{ now()->format('d M Y') }}</div>
        </div>
        <div style="text-align:right">
          <div style="font-size:15px;font-weight:700">{{ $loan->loan_number }}</div>
          <div style="opacity:.8;font-size:13px">{{ $loan->loanProduct->name ?? '—' }}</div>
        </div>
      </div>
    </div>

    <div style="padding:28px 36px">
      {{-- Borrower + Loan summary --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px">
        <div>
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Borrower</div>
          <div style="font-size:15px;font-weight:700;color:var(--dark)">{{ $loan->user->name }}</div>
          <div style="font-size:13px;color:var(--muted)">{{ $loan->user->email }}</div>
          <div style="font-size:13px;color:var(--muted)">{{ $loan->user->phone ?? '—' }}</div>
        </div>
        <div>
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Loan Summary</div>
          @foreach(['Principal'=>'M '.number_format($loan->principal_amount,2),'Interest Rate'=>$loan->interest_rate.'%/mo','Term'=>$loan->term_months.' months','Monthly Payment'=>'M '.number_format($loan->monthly_installment,2),'Disbursed'=>$loan->disbursement_date?->format('d M Y')??'—','Outstanding'=>'M '.number_format($loan->outstanding_balance,2)] as $l=>$v)
          <div style="display:flex;justify-content:space-between;font-size:12.5px;padding:3px 0;border-bottom:1px solid var(--border)">
            <span style="color:var(--muted)">{{ $l }}</span><span style="font-weight:600">{{ $v }}</span>
          </div>
          @endforeach
        </div>
      </div>

      {{-- Payment history --}}
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:12px">Payment History</div>
      <table style="width:100%;border-collapse:collapse;margin-bottom:28px">
        <thead>
          <tr style="background:#f8fafc">
            <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);border-bottom:2px solid var(--border)">Date</th>
            <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);border-bottom:2px solid var(--border)">Reference</th>
            <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);border-bottom:2px solid var(--border)">Method</th>
            <th style="padding:10px 12px;text-align:right;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);border-bottom:2px solid var(--border)">Amount</th>
            <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);border-bottom:2px solid var(--border)">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($loan->payments->where('status','verified') as $p)
          <tr>
            <td style="padding:10px 12px;font-size:12.5px;border-bottom:1px solid var(--border);color:var(--muted)">{{ $p->created_at->format('d M Y') }}</td>
            <td style="padding:10px 12px;font-size:12.5px;border-bottom:1px solid var(--border);font-family:monospace;color:var(--p)">{{ $p->payment_reference }}</td>
            <td style="padding:10px 12px;font-size:12.5px;border-bottom:1px solid var(--border);color:var(--muted)">{{ ucfirst(str_replace('_',' ',$p->method)) }}</td>
            <td style="padding:10px 12px;font-size:13px;font-weight:700;border-bottom:1px solid var(--border);text-align:right">M{{ number_format($p->amount,2) }}</td>
            <td style="padding:10px 12px;border-bottom:1px solid var(--border)"><span style="background:#d1fae5;color:#065f46;font-size:11.5px;font-weight:600;padding:2px 8px;border-radius:20px">Verified</span></td>
          </tr>
          @empty
          <tr><td colspan="5" style="padding:30px;text-align:center;color:var(--muted)">No payments recorded yet</td></tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr style="background:#f8fafc">
            <td colspan="3" style="padding:12px;font-weight:700;font-size:13.5px">Total Paid</td>
            <td style="padding:12px;font-weight:800;font-size:15px;color:#10b981;text-align:right">M{{ number_format($loan->payments->where('status','verified')->sum('amount'),2) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>

      {{-- Installment schedule --}}
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:12px">Repayment Schedule</div>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="background:#f8fafc">
            @foreach(['#','Due Date','Principal','Interest','Initiation','Admin','Total','Paid','Penalty','Outstanding','Status'] as $h)
            <th style="padding:9px 10px;text-align:left;font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);border-bottom:2px solid var(--border)">{{ $h }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($loan->installments as $inst)
          @php $sc=['paid'=>['#059669','#d1fae5'],'partial'=>['#d97706','#fef3c7'],'overdue'=>['#dc2626','#fee2e2'],'pending'=>['#64748b','#f1f5f9'],'waived'=>['#7c3aed','#ede9fe']]; [$tc,$bc]=$sc[$inst->status]??['#64748b','#f1f5f9']; @endphp
          <tr>
            <td style="padding:8px 10px;font-size:12px;border-bottom:1px solid var(--border)">{{ $inst->installment_number }}</td>
            <td style="padding:8px 10px;font-size:12px;border-bottom:1px solid var(--border);white-space:nowrap">{{ $inst->due_date->format('d M Y') }}</td>
            <td style="padding:8px 10px;font-size:12px;border-bottom:1px solid var(--border);color:var(--p)">M{{ number_format($inst->principal_amount,2) }}</td>
            <td style="padding:8px 10px;font-size:12px;border-bottom:1px solid var(--border);color:#f59e0b">M{{ number_format($inst->interest_amount,2) }}</td>
            <td style="padding:8px 10px;font-size:12px;border-bottom:1px solid var(--border);color:#8b5cf6">M{{ number_format($inst->initiation_fee_amount??0,2) }}</td>
            <td style="padding:8px 10px;font-size:12px;border-bottom:1px solid var(--border);color:#64748b">M{{ number_format($inst->admin_fee_amount??0,2) }}</td>
            <td style="padding:8px 10px;font-size:12px;border-bottom:1px solid var(--border);font-weight:600">M{{ number_format($inst->total_amount,2) }}</td>
            <td style="padding:8px 10px;font-size:12px;border-bottom:1px solid var(--border);color:#10b981">M{{ number_format($inst->paid_amount,2) }}</td>
            <td style="padding:8px 10px;font-size:12px;border-bottom:1px solid var(--border);color:#ef4444">{{ $inst->late_fee > 0 ? 'M'.number_format($inst->late_fee,2) : '—' }}</td>
            <td style="padding:8px 10px;font-size:12px;border-bottom:1px solid var(--border)">M{{ number_format($inst->outstanding_amount,2) }}</td>
            <td style="padding:8px 10px;border-bottom:1px solid var(--border)"><span style="background:{{ $bc }};color:{{ $tc }};font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">{{ ucfirst($inst->status) }}</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
@media print {
  .topbar, .sidebar, .btn, header, nav, footer { display: none !important; }
  body, html, .main { margin: 0 !important; padding: 0 !important; width: 100%; background: #fff !important; }
  .card { border: none !important; box-shadow: none !important; margin: 0; padding: 0; }
  div[style*="max-width"] { max-width: none !important; margin: 0 !important; }
}
@page {
  margin: 30px;
}
</style>
@endsection
