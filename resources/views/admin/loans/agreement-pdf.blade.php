<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body{font-family:Arial,sans-serif;font-size:13px;color:#333;padding:30px}
h1{color:#4f46e5;text-align:center;margin-bottom:5px}
.sub{text-align:center;color:#64748b;margin-bottom:30px}
table{width:100%;border-collapse:collapse;margin:20px 0}
th,td{padding:9px 12px;border:1px solid #e2e8f0;text-align:left}th{background:#f1f5f9;font-weight:700}
.sig{margin-top:50px;display:flex;justify-content:space-between}
.sig-box{width:45%;border-top:2px solid #333;padding-top:10px}
</style></head><body>
<h1>LOAN AGREEMENT</h1>
<p class="sub">LoanPlatform — Agreement #{{ $loan->loan_number }}</p>
<table><tr><th>Borrower</th><td>{{ $loan->user->name }}</td><th>Loan #</th><td>{{ $loan->loan_number }}</td></tr>
<tr><th>Principal</th><td>L {{ number_format($loan->principal_amount,2) }}</td><th>Interest Rate</th><td>{{ $loan->interest_rate }}% per month</td></tr>
<tr><th>Term</th><td>{{ $loan->term_months }} months</td><th>Monthly Installment</th><td>L {{ number_format($loan->monthly_installment,2) }}</td></tr>
<tr><th>Disbursement Date</th><td>{{ $loan->disbursement_date?->format('d M Y') }}</td><th>Maturity Date</th><td>{{ $loan->maturity_date?->format('d M Y') }}</td></tr>
<tr><th>Total Repayable</th><td colspan="3"><strong>L {{ number_format($loan->total_amount,2) }}</strong></td></tr></table>
<h3>Repayment Schedule</h3>
<table><thead><tr><th>#</th><th>Due Date</th><th>Principal</th><th>Interest</th><th>Total</th></tr></thead>
<tbody>@foreach($loan->installments as $i)<tr><td>{{$i->installment_number}}</td><td>{{$i->due_date->format('d M Y')}}</td><td>L{{number_format($i->principal_amount,2)}}</td><td>L{{number_format($i->interest_amount,2)}}</td><td>L{{number_format($i->total_amount,2)}}</td></tr>@endforeach</tbody></table>
<div class="sig"><div class="sig-box"><p>Borrower Signature</p><br><p>Date: _______________</p></div><div class="sig-box"><p>Authorised Signatory</p><br><p>Date: _______________</p></div></div>
</body></html>
