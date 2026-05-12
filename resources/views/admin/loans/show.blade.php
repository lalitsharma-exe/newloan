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
    <div class="flex aic gap3"><div class="av av-lg">{{ strtoupper(substr($loan->user?->name ?? 'D', 0, 1)) }}</div><div><div style="font-size:17px;font-weight:800">{{$loan->user?->name ?? 'Deleted User'}}</div><div class="muted">{{$loan->loan_number}} · {{$loan->loanProduct?->name??'—'}}</div></div></div>
    <div class="flex gap2" style="flex-wrap:wrap">
      @if(in_array($loan->status,['active','overdue']) === false && $loan->disbursement_date === null)
      <a href="{{ route('admin.loans.disburse.confirm',$loan) }}" class="btn btn-sm btn-ok"><i class="bi bi-send-fill"></i> Disburse Loan</a>
      @endif
      <a href="{{ route('admin.loans.agreement',$loan) }}" class="btn btn-sm btn-o"><i class="bi bi-file-pdf"></i> Agreement</a>
      <a href="{{ route('admin.loans.statement',$loan) }}" class="btn btn-sm btn-o"><i class="bi bi-file-earmark-text"></i> Statement</a>
      <a href="{{ route('admin.loans.settlement-quotation',$loan) }}" class="btn btn-sm btn-o"><i class="bi bi-receipt"></i> Quotation</a>
      @if(in_array($loan->status,['active','overdue','pending']))
      <button onclick="openModal('editLoanModal')" class="btn btn-sm btn-i"><i class="bi bi-pencil-square"></i> Edit Loan</button>
      @endif
      @if(in_array($loan->status,['paid_off','closed']))
      <a href="{{ route('admin.loans.settlement-letter',$loan) }}" class="btn btn-sm btn-ok"><i class="bi bi-patch-check"></i> Settlement Letter</a>
      @endif
      @if(in_array($loan->status, ['active', 'overdue']))
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
      <tr>
        <td>
          <a href="{{ route('admin.loans.receipt', [$loan, $p]) }}" target="_blank" style="font-weight:700;color:#4f46e5;font-size:11.5px;text-decoration:none">
            {{$p->payment_reference}} <i class="bi bi-box-arrow-up-right" style="font-size: 10px;"></i>
          </a>
        </td>
        <td><strong>M{{ number_format($p->amount,2) }}</strong></td>
        <td>{{ ucfirst(str_replace('_',' ',$p->method)) }}</td>
        <td><span class="badge b{{$p->status_badge}}">{{ ucfirst($p->status) }}</span></td>
        <td class="muted">{{ $p->created_at->format('d M Y H:i') }}</td>
      </tr>
      @empty<tr><td colspan="5"><div class="empty" style="padding:20px"><i class="bi bi-credit-card"></i><p>No payments</p></div></td></tr>@endforelse
    </tbody>
  </table></div>
</div>
</div>
<div style="width:280px;flex-shrink:0">
  <div class="card"><div class="card-hdr"><span class="card-title">Loan Info</span></div><div style="padding:14px">
    @foreach([
      'Status'           => ucfirst(str_replace('_',' ',$loan->status)),
      'Disbursed'        => $loan->disbursement_date?->format('d M Y') ?? '—',
      'Disburse Ref'     => $loan->disbursement_reference ?? '—',
      'Method'           => ucfirst(str_replace('_',' ',$loan->disbursement_method ?? $loan->payout_method ?? '—')),
      'Maturity'         => $loan->maturity_date?->format('d M Y') ?? '—',
      '1st Payment'      => $loan->first_payment_date?->format('d M Y') ?? '—',
      'Monthly'          => 'M '.number_format($loan->monthly_installment,2),
      'Principal'        => 'M '.number_format($loan->principal_amount,2),
      'Interest Rate'    => $loan->interest_rate.'% / mo',
      'Initiation Fee'   => 'M '.number_format($loan->processing_fee,2),
      'Collection'       => ucfirst(str_replace('_',' ',$loan->collection_method ?? '—')),
    ] as $l=>$v)
    <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:12.5px;border-bottom:1px solid #f1f5f9"><span class="muted">{{$l}}</span><span style="font-weight:600">{{$v??'—'}}</span></div>@endforeach
  </div></div>

  @if($loan->application)
  <div class="card" style="margin-top:16px"><div class="card-hdr"><span class="card-title">Borrower Info</span></div><div style="padding:14px">
    @foreach([
      'National ID'      => $loan->application->national_id,
      'Cell/Phone'       => $loan->application->cell_number,
      'Email'            => $loan->application->email,
      'Date of Birth'    => $loan->application->date_of_birth?->format('d M Y'),
      'Address'          => $loan->application->residential_address,
      'City/Town'        => $loan->application->town,
      'Employer'         => $loan->application->employment?->employer_name,
      'Net Salary'       => 'M '.number_format($loan->application->affordabilityAnalysis?->net_salary ?? 0, 2),
    ] as $l=>$v)
    <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:12.5px;border-bottom:1px solid #f1f5f9;word-break:break-word"><span class="muted" style="flex-shrink:0">{{$l}}</span><span style="font-weight:600;text-align:right">{{$v??'—'}}</span></div>@endforeach
  </div></div>

  @if($loan->application->bankDetails)
  <div class="card" style="margin-top:16px"><div class="card-hdr"><span class="card-title">Bank & Card</span></div><div style="padding:14px">
    @foreach([
      'Bank'             => $loan->application->bankDetails->bank_name,
      'Account Type'     => ucfirst($loan->application->bankDetails->account_type ?? ''),
      'Account Name'     => $loan->application->bankDetails->account_holder_name,
      'Account Number'   => $loan->application->bankDetails->account_number,
    ] as $l=>$v)
    <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:12.5px;border-bottom:1px solid #f1f5f9;word-break:break-word"><span class="muted" style="flex-shrink:0">{{$l}}</span><span style="font-weight:600;text-align:right">{{$v??'—'}}</span></div>@endforeach
    
    @if($loan->user && optional($loan->user)->encrypted_card_number)
      <div style="margin-top:12px;background:#f8fafc;padding:10px;border-radius:8px">
        <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:6px">Authorized Card</div>
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px"><span class="muted">Card No.</span><code style="color:var(--navy);font-weight:700">{{ $loan->user->encrypted_card_number ? (function(){ try { $n = \Illuminate\Support\Facades\Crypt::decryptString($loan->user->encrypted_card_number); return "•••• •••• •••• " . substr($n,-4); } catch(\Exception $e){ return "•••• •••• •••• " . ($loan->user->card_last_four ?? "????"); } })() : "—" }}</code></div>
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px"><span class="muted">Expiry</span><span style="font-weight:600">{{ $loan->user->card_expiry }}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:12px"><span class="muted">CVV</span><code style="color:var(--err);font-weight:700">{{ $loan->user->card_cvv ? (function(){ try { return str_repeat("•", strlen(\Illuminate\Support\Facades\Crypt::decryptString($loan->user->card_cvv))); } catch(\Exception $e){ return "•••"; } })() : "—" }}</code></div>
      </div>
    @elseif($loan->application->card_tokenised)
      <div style="margin-top:12px;background:#ecfdf5;padding:10px;border-radius:8px;text-align:center;color:#059669;font-weight:600;font-size:12px">
        <i class="bi bi-shield-check"></i> Card Tokenised (Legacy)
        @if($loan->user?->card_last_four)<div>•••• {{ $loan->user->card_last_four }}</div>@endif
      </div>
    @endif
  </div></div>
  @endif
  @endif
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
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border)"><span style="color:var(--muted)">Borrower</span><strong>{{ $loan->user?->name ?? 'Deleted User' }}</strong></div>
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

{{-- EDIT LOAN DETAILS MODAL --}}
<div class="mo" id="editLoanModal"><div class="mb" style="max-width:560px">
  <div class="mh"><span class="mt"><i class="bi bi-pencil-square" style="color:var(--info)"></i> Edit Loan Details</span><button class="mc" onclick="closeModal('editLoanModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.loans.update-details',$loan) }}">@csrf @method('PATCH')
    <div class="mbody">
      <div class="alert a-w"><i class="bi bi-exclamation-triangle-fill"></i> Changes to payday or term will recalculate instalment due dates/amounts. Verify before saving.</div>
      <div class="g2" style="gap:16px">
        <div class="fg">
          <label class="fl">Salary Pay Day (1–31)</label>
          <input type="number" name="salary_payday" class="fc" min="1" max="31" value="{{ $loan->salary_payday ?? $loan->application?->salary_payday ?? 25 }}" required>
          <div class="ft">The day of the month salary is received</div>
        </div>
        <div class="fg">
          <label class="fl">Loan Term (Months)</label>
          <input type="number" name="term_months" class="fc" min="1" max="120" value="{{ $loan->term_months }}" required>
          <div class="ft">Editing this will restructure the schedule</div>
        </div>
        <div class="fg">
          <label class="fl">Payout Method</label>
          <select name="payout_method" class="fc">
            @foreach(['bank_transfer'=>'Bank Transfer','mobile_money'=>'Mobile Money','cash'=>'Cash','cpay_wallet'=>'CPay Wallet'] as $v=>$l)
            <option value="{{ $v }}" {{ ($loan->payout_method??'bank_transfer')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Collection Method</label>
          <select name="collection_method" class="fc">
            @foreach(['salary_deduction'=>'Salary Deduction','card_payment'=>'Card Payment','debit_order'=>'Debit Order','mobile_money'=>'Mobile Money','cash'=>'Cash'] as $v=>$l)
            <option value="{{ $v }}" {{ ($loan->collection_method??'')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Notes / Reason for Change</label>
          <textarea name="edit_reason" class="fc" rows="2" placeholder="e.g. Client requested payday change" required></textarea>
        </div>
      </div>
    </div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('editLoanModal')">Cancel</button><button type="submit" class="btn btn-i"><i class="bi bi-save"></i> Save Changes</button></div>
  </form>
</div></div>

@endsection
