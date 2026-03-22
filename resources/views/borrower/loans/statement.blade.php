@extends('borrower.layouts.app')
@section('title','Statement — '.$loan->loan_number)
@section('content')
<div style="display:flex;justify-content:space-between;margin-bottom:20px"><div style="font-size:20px;font-weight:800">Loan Statement</div><button onclick="window.print()" class="btn btn-o btn-sm"><i class="bi bi-printer"></i> Print</button></div>
<div class="card" style="margin-bottom:16px">
  <div class="card-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      @foreach(['Loan #'=>$loan->loan_number,'Product'=>$loan->loanProduct?->name,'Principal'=>'M '.number_format($loan->principal_amount,2),'Total Repayable'=>'M '.number_format($loan->total_amount,2),'Outstanding'=>'M '.number_format($loan->outstanding_balance,2),'Disbursed'=>$loan->disbursement_date?->format('d M Y')??'—','Maturity'=>$loan->maturity_date?->format('d M Y')??'—','Status'=>ucfirst(str_replace('_',' ',$loan->status))] as $l=>$v)
      <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v }}</div></div>
      @endforeach
    </div>
  </div>
</div>
<div class="card">
  <div class="card-hdr"><span class="card-title">Payment History</span></div>
  <div style="overflow-x:auto">
    <table class="dt"><thead><tr><th>Reference</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
    <tbody>
      @forelse($loan->payments->where('status','verified') as $p)
      <tr><td style="font-family:monospace;font-size:12px">{{ $p->payment_reference }}</td><td style="font-weight:700;color:var(--ok)">M{{ number_format($p->amount,2) }}</td><td>{{ ucfirst(str_replace('_',' ',$p->method)) }}</td><td style="font-size:12px;color:var(--muted)">{{ $p->created_at->format('d M Y H:i') }}</td></tr>
      @empty
      <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--muted)">No payments yet</td></tr>
      @endforelse
    </tbody></table>
  </div>
</div>
<style>
@media print {
  .topbar, .sidebar, .btn, header, nav { display: none !important; }
  body, html, .main { margin: 0 !important; padding: 0 !important; width: 100%; background: #fff !important; }
  .card { border: none !important; box-shadow: none !important; margin: 0; padding: 0; }
  .dt { border: 1px solid #ddd; }
}
@page {
  margin: 30px;
}
</style>
@endsection
