@extends('admin.layouts.app')
@section('title','Loan '.$loan->loan_number)@section('page-title','Loan '.$loan->loan_number)
@section('bc')
<a href="{{ route('admin.loans.index') }}">Loans</a> / Detail
@endsection
@section('content')
<div style="display:flex;gap:18px;align-items:flex-start">
<div style="flex:2;min-width:0">
<div class="card mb4">
  <div style="padding:18px 22px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div class="flex aic gap3"><div class="av av-lg">{{ strtoupper(substr($loan->user->name??'U',0,1)) }}</div><div><div style="font-size:17px;font-weight:800">{{$loan->user->name}}</div><div class="muted">{{$loan->loan_number}} · {{$loan->loanProduct->name??'—'}}</div></div></div>
    <div class="flex gap2" style="flex-wrap:wrap">
      <a href="{{ route('admin.loans.agreement',$loan) }}" class="btn btn-sm btn-o"><i class="bi bi-file-pdf"></i> Agreement</a>
      <a href="{{ route('admin.loans.statement',$loan) }}" class="btn btn-sm btn-o"><i class="bi bi-file-earmark-text"></i> Statement</a>
      <a href="{{ route('admin.loans.settlement-quotation',$loan) }}" class="btn btn-sm btn-o"><i class="bi bi-receipt"></i> Quotation</a>
      @if(in_array($loan->status,['paid_off','closed']))
      <a href="{{ route('admin.loans.settlement-letter',$loan) }}" class="btn btn-sm btn-ok"><i class="bi bi-patch-check"></i> Settlement Letter</a>
      @endif
      @if($loan->status==='active')
      <button onclick="openModal('payModal')" class="btn btn-sm btn-ok"><i class="bi bi-cash"></i> Record Payment</button>
      <button onclick="openModal('closeModal')" class="btn btn-sm btn-e"><i class="bi bi-x-lg"></i> Close Loan</button>
      @endif
      @if(in_array($loan->status,['active','overdue']))
      <button onclick="openModal('writeOffModal')" class="btn btn-sm btn-e"><i class="bi bi-trash3"></i> Write Off</button>
      <button onclick="openModal('defaultModal')" class="btn btn-sm btn-w"><i class="bi bi-exclamation-triangle"></i> Mark Defaulted</button>
      @endif
    </div>
  </div>
</div>
<div class="g4 mb4">
  <div class="sc"><div class="si p"><i class="bi bi-cash-coin"></i></div><div><div class="sv">M{{ number_format($loan->principal_amount,0) }}</div><div class="sl">Principal</div></div></div>
  <div class="sc"><div class="si e"><i class="bi bi-wallet2"></i></div><div><div class="sv">M{{ number_format($loan->outstanding_balance,0) }}</div><div class="sl">Outstanding</div></div></div>
  <div class="sc"><div class="si w"><i class="bi bi-percent"></i></div><div><div class="sv">{{ $loan->interest_rate }}%</div><div class="sl">Monthly Rate</div></div></div>
  <div class="sc"><div class="si ok"><i class="bi bi-calendar-check"></i></div><div><div class="sv">{{ $loan->term_months }}mo</div><div class="sl">Term</div></div></div>
</div>
<div class="card mb4">
  <div class="card-hdr"><span class="card-title">Repayment Schedule</span></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>#</th><th>Due Date</th><th>Principal</th><th>Interest</th><th>Initiation</th><th>Admin</th><th>Total</th><th>Paid</th><th>Outstanding</th><th>Penalty</th><th>Status</th></tr></thead>
    <tbody>@foreach($loan->installments as $i)
      <tr style="{{ $i->status==='overdue'?'background:#fef2f2':'' }}">
        <td>{{$i->installment_number}}</td><td>{{$i->due_date->format('d M Y')}}</td>
        <td>M{{ number_format($i->principal_amount,2) }}</td>
        <td>M{{ number_format($i->interest_amount,2) }}</td>
        <td>M{{ number_format($i->initiation_fee_amount??0,2) }}</td>
        <td>M{{ number_format($i->admin_fee_amount??0,2) }}</td>
        <td><strong>M{{ number_format($i->total_amount,2) }}</strong></td>
        <td style="color:#10b981">M{{ number_format($i->paid_amount,2) }}</td>
        <td style="color:#ef4444">M{{ number_format($i->outstanding_amount,2) }}</td>
        <td style="color:#dc2626">{{ $i->late_fee > 0 ? 'M'.number_format($i->late_fee,2) : '—' }}</td>
        <td><span class="badge {{ $i->status==='paid'?'bok':($i->status==='overdue'?'be':($i->status==='partial'?'bw':'bs')) }}">{{ ucfirst($i->status) }}</span></td>
      </tr>@endforeach
    </tbody>
  </table></div>
</div>
<div class="card">
  <div class="card-hdr"><span class="card-title">Payment History</span></div>
  <div style="overflow-x:auto"><table class="dt">
    <thead><tr><th>Reference</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>@forelse($loan->payments as $p)
      <tr><td><span style="font-weight:700;color:#4f46e5;font-size:11.5px">{{$p->payment_reference}}</span></td><td><strong>M{{ number_format($p->amount,2) }}</strong></td><td>{{ ucfirst(str_replace('_',' ',$p->method)) }}</td><td><span class="badge b{{$p->status_badge}}">{{ ucfirst($p->status) }}</span></td><td class="muted">{{ $p->created_at->format('d M Y H:i') }}</td></tr>
      @empty<tr><td colspan="5"><div class="empty" style="padding:20px"><i class="bi bi-credit-card"></i><p>No payments</p></div></td></tr>@endforelse
    </tbody>
  </table></div>
</div>
</div>
<div style="width:280px;flex-shrink:0">
  <div class="card"><div class="card-hdr"><span class="card-title">Loan Info</span></div><div style="padding:14px">
    @foreach(['Status'=>ucfirst(str_replace('_',' ',$loan->status)),'Disbursed'=>$loan->disbursement_date?->format('d M Y'),'Maturity'=>$loan->maturity_date?->format('d M Y'),'1st Payment'=>$loan->first_payment_date?->format('d M Y'),'Monthly'=>"M ".number_format($loan->monthly_installment,2),'Processing Fee'=>"M ".number_format($loan->processing_fee,2)] as $l=>$v)
    <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:12.5px;border-bottom:1px solid #f1f5f9"><span class="muted">{{$l}}</span><span style="font-weight:600">{{$v??'—'}}</span></div>@endforeach
  </div></div>
</div>
</div>

<div class="mo" id="payModal"><div class="mb">
  <div class="mh"><span class="mt">Record Manual Payment</span><button class="mc" onclick="closeModal('payModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.loans.record-payment',$loan) }}">@csrf
    <div class="mbody">
      <div class="fg"><label class="fl">Installment</label><select name="installment_id" class="fc" required>@foreach($loan->installments->whereNotIn('status',['paid','waived']) as $i)<option value="{{$i->id}}">#{{$i->installment_number}} — {{$i->due_date->format('d M Y')}} (M{{ number_format($i->outstanding_amount,2) }})</option>@endforeach</select></div>
      <div class="g2" style="gap:14px">
        <div class="fg"><label class="fl">Amount (L)*</label><input type="number" name="amount" class="fc" step="0.01" required></div>
        <div class="fg"><label class="fl">Date*</label><input type="date" name="payment_date" class="fc" value="{{ today()->format('Y-m-d') }}" required></div>
        <div class="fg"><label class="fl">Method*</label><select name="method" class="fc"><option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="mobile_money">Mobile Money</option><option value="card">Card</option></select></div>
        <div class="fg"><label class="fl">Reference</label><input type="text" name="reference" class="fc"></div>
      </div>
    </div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('payModal')">Cancel</button><button type="submit" class="btn btn-ok"><i class="bi bi-check-lg"></i> Record</button></div>
  </form>
</div></div>

<div class="mo" id="closeModal"><div class="mb">
  <div class="mh"><span class="mt">Close Loan</span><button class="mc" onclick="closeModal('closeModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.loans.close',$loan) }}">@csrf
    <div class="mbody"><div class="alert a-w"><i class="bi bi-exclamation-triangle-fill"></i> This will permanently close the loan.</div><div class="fg"><label class="fl">Reason*</label><textarea name="reason" class="fc" rows="3" required></textarea></div></div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('closeModal')">Cancel</button><button type="submit" class="btn btn-e">Close Loan</button></div>
  </form>
</div></div>

{{-- WRITE OFF MODAL --}}
<div class="mo" id="writeOffModal"><div class="mb">
  <div class="mh"><span class="mt"><i class="bi bi-trash3" style="color:var(--err)"></i> Write Off Loan</span><button class="mc" onclick="closeModal('writeOffModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.loans.write-off',$loan) }}">@csrf
    <div class="mbody">
      <div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i> Writing off a loan marks it as unrecoverable. The outstanding balance will be recorded as a loss. This cannot be undone.</div>
      <div style="background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:16px;font-size:13px">
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border)"><span style="color:var(--muted)">Loan #</span><strong>{{ $loan->loan_number }}</strong></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border)"><span style="color:var(--muted)">Borrower</span><strong>{{ $loan->user->name }}</strong></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0"><span style="color:var(--muted)">Outstanding Balance</span><strong style="color:var(--err)">M{{ number_format($loan->outstanding_balance,2) }}</strong></div>
      </div>
      <div class="fg"><label class="fl">Reason for Write-Off *</label><textarea name="reason" class="fc" rows="3" required placeholder="e.g. Borrower deceased, untraceable, legal write-off…"></textarea></div>
    </div>
    <div class="mf">
      <button type="button" class="btn btn-o" onclick="closeModal('writeOffModal')">Cancel</button>
      <button type="submit" class="btn btn-e"><i class="bi bi-trash3"></i> Confirm Write Off</button>
    </div>
  </form>
</div></div>

{{-- MARK DEFAULTED MODAL --}}
<div class="mo" id="defaultModal"><div class="mb">
  <div class="mh"><span class="mt"><i class="bi bi-exclamation-triangle" style="color:var(--warn)"></i> Mark as Defaulted</span><button class="mc" onclick="closeModal('defaultModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.loans.mark-defaulted',$loan) }}">@csrf
    <div class="mbody">
      <div class="alert a-w"><i class="bi bi-exclamation-triangle-fill"></i> This marks the loan as defaulted. The borrower will be flagged for collections and credit bureau reporting.</div>
      <div class="fg"><label class="fl">Notes *</label><textarea name="reason" class="fc" rows="3" required placeholder="e.g. No response after 90 days, sent to collections…"></textarea></div>
    </div>
    <div class="mf">
      <button type="button" class="btn btn-o" onclick="closeModal('defaultModal')">Cancel</button>
      <button type="submit" class="btn btn-w"><i class="bi bi-exclamation-triangle"></i> Confirm Default</button>
    </div>
  </form>
</div></div>

@endsection
