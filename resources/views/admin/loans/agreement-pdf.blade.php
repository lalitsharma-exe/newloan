<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 11px; color: #222; line-height: 1.6; padding: 30px 40px; }
  h1  { font-size: 18px; text-align: center; color: #1a5c2e; margin-bottom: 4px; }
  h2  { font-size: 13px; color: #1a5c2e; margin: 18px 0 6px; border-bottom: 2px solid #1a5c2e; padding-bottom: 3px; }
  h3  { font-size: 12px; font-weight: 700; margin: 12px 0 4px; }
  .center { text-align: center; }
  .muted  { color: #64748b; font-size: 10px; }
  .divider { border: none; border-top: 1px solid #d1d5db; margin: 14px 0; }
  table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 11px; }
  th, td { border: 1px solid #d1d5db; padding: 6px 10px; text-align: left; }
  th { background: #f1f5f9; font-weight: 700; }
  .summary-table th { width: 25%; }
  .summary-table td { width: 25%; }
  .schedule-table th { text-align: center; background: #1a5c2e; color: #fff; }
  .schedule-table td { text-align: center; }
  .schedule-table tr:nth-child(even) td { background: #f8fafc; }
  .lender-block, .borrower-block { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin: 8px 0; }
  .sig-grid { display: flex; justify-content: space-between; margin-top: 40px; gap: 20px; }
  .sig-box  { flex: 1; border-top: 2px solid #222; padding-top: 8px; }
  .clause   { margin-bottom: 10px; }
  .clause p { margin-top: 4px; }
  ul.clause-list { padding-left: 20px; margin: 4px 0; }
  ul.clause-list li { margin-bottom: 2px; }
  .page-break { page-break-before: always; }
  .highlight { background: #fef9c3; padding: 1px 3px; border-radius: 2px; }
  @media print {
    body { padding: 15px 20px; }
    .no-print { display: none; }
  }
  @page {
    margin: 30px;
    @bottom-right {
      content: "Page " counter(page) " of " counter(pages);
      font-size: 10px;
      color: #64748b;
    }
  }
</style>
</head>
<body>

{{-- No-print actions --}}
<div class="no-print" style="text-align:right;margin-bottom:16px;display:flex;gap:10px;justify-content:flex-end">
  <button onclick="window.print()" style="background:#1a5c2e;color:#fff;border:none;padding:9px 20px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:600">
    🖨 Print / Save PDF
  </button>
  <a href="javascript:history.back()" style="background:#f1f5f9;color:#374151;border:none;padding:9px 20px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:600;text-decoration:none">← Back</a>
</div>

{{-- Header: Regulatory statement + Logo (no address repetition) --}}
<div class="center" style="margin-bottom:18px">
  <div style="border:1.5px solid #9ca3af;border-radius:4px;padding:10px 24px;display:inline-block;max-width:680px;margin:0 auto">
    <p style="font-size:11px;color:#374151;text-align:center;line-height:1.7;margin:0">
      Myloan Limited is a company licensed under the Financial Institutions Act 2012 and Financial Institutions (Credit only and Deposit taking Financial Institutions) (Amendment) Regulations 2014, as amended in 2018, as a Credit Only Micro Finance Institution tier II and regulated by the Central Bank of Lesotho.
    </p>
  </div>
  <div style="margin-top:10px">
    <img src="https://ik.imagekit.io/ygydr1m84/png.webp" style="height:60px;margin-bottom:8px" alt="MyLoan Logo">
  </div>
  <div style="margin-top:14px;font-size:17px;font-weight:800;color:#0f172a;letter-spacing:.5px">LOAN AGREEMENT</div>
  <div style="font-size:11px;color:#64748b;margin-top:3px">Agreement Reference: <strong>{{ $loan->loan_number }}</strong></div>
</div>

<hr class="divider">

{{-- Parties --}}
<h2>PARTIES TO THIS AGREEMENT</h2>
<div class="lender-block">
  <strong>LENDER:</strong> MyLoan Limited &nbsp;·&nbsp; L&amp;M Complex, Ha Thamae, Maseru, Lesotho<br>
  <span class="muted">Hereinafter referred to as "the Lender"</span>
</div>
<div class="borrower-block">
  <strong>BORROWER:</strong> {{ $loan->user->name }}<br>
  <strong>ID Number:</strong> {{ $loan->user->national_id ?? $loan->application?->national_id ?? '_______________' }}<br>
  <strong>Phone Number:</strong> {{ $loan->user->phone ?? $loan->application?->cell_number ?? '_______________' }}<br>
  @if($loan->application?->employment)
  <strong>Employer:</strong> {{ $loan->application->employment->employer_name ?? '—' }}<br>
  <strong>Employment No.:</strong> {{ $loan->application->employment->employment_number ?? '—' }}<br>
  @endif
  <strong>Residential Address:</strong> {{ implode(', ', array_filter([$loan->application?->village, $loan->application?->town, $loan->application?->district])) ?: ($loan->user->address ?? '_______________________________________________') }}<br>
  <span class="muted">The above address shall serve as the Domicilium Citandi et Executandi for the delivery of all notices.</span><br>
  <span class="muted">Hereinafter referred to as "the Borrower"</span>
</div>

{{-- 1. Loan Summary --}}
<h2>1. LOAN SUMMARY</h2>
@php
  $product     = $loan->loanProduct;
  $principal   = (float) $loan->principal_amount;
  $term        = (int)   $loan->term_months;
  $rate        = (float) $loan->interest_rate;
  $initRate    = ($product?->initiation_fee_rate ?? 40);
  $initFee     = round($principal * $initRate / 100, 2);
  $adminMonth  = (float) ($product?->admin_fee_fixed ?? 50);
  $adminTotal  = $adminMonth * $term;
  $totalInt    = round($principal * ($rate/100) * $term, 2);
  $totalFees   = $initFee + $adminTotal;
  $totalRepay  = $loan->total_amount;
  $monthly     = $loan->monthly_installment;
@endphp
<table class="summary-table">
  <tr>
    <th>Loan ID</th><td><strong>{{ $loan->loan_number }}</strong></td>
    <th>Borrower Name</th><td>{{ $loan->user->name }}</td>
  </tr>
  <tr>
    <th>ID Number</th><td>{{ $loan->user->national_id ?? $loan->application?->national_id ?? '—' }}</td>
    <th>Phone Number</th><td>{{ $loan->user->phone ?? '—' }}</td>
  </tr>
  <tr>
    <th>Employer</th><td>{{ $loan->application?->employment?->employer_name ?? '—' }}</td>
    <th>Employment Number</th><td>{{ $loan->application?->employment?->employment_number ?? '—' }}</td>
  </tr>
  <tr>
    <th>Principal Amount</th><td><strong>M {{ number_format($principal, 2) }}</strong></td>
    <th>Loan Term</th><td>{{ $term }} months</td>
  </tr>
  <tr>
    <th>Initiation Fee ({{ $initRate }}%)</th><td>M {{ number_format($initFee, 2) }}</td>
    <th>Interest Rate</th><td>{{ $rate }}% per month</td>
  </tr>
  <tr>
    <th>Monthly Admin Fee</th><td>M {{ number_format($adminMonth, 2) }}</td>
    <th>Total Interest</th><td>M {{ number_format($totalInt, 2) }}</td>
  </tr>
  <tr>
    <th>Total Fees (Init+Admin)</th><td>M {{ number_format($totalFees, 2) }}</td>
    <th>Total Repayment</th><td><strong>M {{ number_format($totalRepay, 2) }}</strong></td>
  </tr>
  <tr>
    <th>Monthly Installment</th><td><strong>M {{ number_format($monthly, 2) }}</strong></td>
    <th>Disb. Method</th><td>{{ ucfirst(str_replace('_',' ', $loan->disbursement_method ?? $loan->payout_method ?? '—')) }}</td>
  </tr>
  <tr>
    <th>Disb. Reference</th><td>{{ $loan->disbursement_reference ?? '—' }}</td>
    <th>Disb. Date</th><td>{{ $loan->disbursement_date?->format('d F Y') ?? '—' }}</td>
  </tr>
  <tr>
    <th>First Payment Date</th><td>{{ $loan->first_payment_date?->format('d F Y') ?? '—' }}</td>
    <th>Maturity Date</th><td>{{ $loan->maturity_date?->format('d F Y') ?? '—' }}</td>
  </tr>
</table>
<p style="font-size:10.5px;margin-top:6px;color:#64748b">The borrower confirms that the above loan summary accurately reflects the loan granted.</p>

{{-- 2. Repayment Schedule --}}
<h2>2. LOAN REPAYMENT SCHEDULE</h2>
<table class="schedule-table">
  <thead>
    <tr>
      <th>#</th>
      <th>Due Date</th>
      <th>Principal</th>
      <th>Interest</th>
      <th>Initiation Fee</th>
      <th>Admin Fee</th>
      <th>Total Payment</th>
      <th>Balance</th>
    </tr>
  </thead>
  <tbody>
    @php
      $runBalance = $principal;
      $schedTotals = ['principal'=>0,'interest'=>0,'init'=>0,'admin'=>0,'total'=>0];
    @endphp
    @foreach($loan->installments as $inst)
    @php
      $runBalance -= $inst->principal_amount;
      $schedTotals['principal'] += $inst->principal_amount;
      $schedTotals['interest']  += $inst->interest_amount;
      $schedTotals['init']      += $inst->initiation_fee_amount ?? 0;
      $schedTotals['admin']     += $inst->admin_fee_amount ?? 0;
      $schedTotals['total']     += $inst->total_amount;
    @endphp
    <tr>
      <td>{{ $inst->installment_number }}</td>
      <td>{{ $inst->due_date->format('d M Y') }}</td>
      <td>M{{ number_format($inst->principal_amount,2) }}</td>
      <td>M{{ number_format($inst->interest_amount,2) }}</td>
      <td>M{{ number_format($inst->initiation_fee_amount??0,2) }}</td>
      <td>M{{ number_format($inst->admin_fee_amount??0,2) }}</td>
      <td><strong>M{{ number_format($inst->total_amount,2) }}</strong></td>
      <td>M{{ number_format(max(0,$runBalance),2) }}</td>
    </tr>
    @endforeach
    <tr style="font-weight:700;background:#1a5c2e;color:#fff">
      <td colspan="2">TOTALS</td>
      <td>M{{ number_format($schedTotals['principal'],2) }}</td>
      <td>M{{ number_format($schedTotals['interest'],2) }}</td>
      <td>M{{ number_format($schedTotals['init'],2) }}</td>
      <td>M{{ number_format($schedTotals['admin'],2) }}</td>
      <td>M{{ number_format($schedTotals['total'],2) }}</td>
      <td>—</td>
    </tr>
  </tbody>
</table>

<div class="page-break"></div>

{{-- Clauses 3–14 --}}
<h2>3. TERMS AND CONDITIONS</h2>

<div class="clause"><h3>3.1. INTEREST RATE</h3>
<p>Interest shall be charged on the outstanding capital at the fixed monthly rate of <strong>{{ $rate }}% per month</strong> as stated in the Loan Summary. Interest is calculated monthly and continues until the loan is fully repaid.</p></div>

<div class="clause"><h3>3.2. INITIATION FEE</h3>
<p>A once-off Initiation Fee of <strong>M{{ number_format($initFee,2) }} ({{ $initRate }}% of the principal)</strong> is charged when the loan is granted. This fee covers loan processing, credit assessment, and risk management. This fee forms part of the total repayment obligation.</p></div>

<div class="clause"><h3>3.3. ADMINISTRATION FEE</h3>
<p>A monthly administration fee of <strong>M{{ number_format($adminMonth,2) }}</strong> is charged during the loan term to cover the cost of managing the loan account. This fee is reflected in the repayment schedule above.</p></div>

<div class="clause"><h3>3.4. DISBURSEMENT OF LOAN</h3>
<p>The loan may be disbursed through Mobile Money or Bank Transfer. Once the loan has been disbursed, this agreement becomes fully binding and the repayment schedule becomes effective.</p></div>

<div class="clause"><h3>3.5. REPAYMENT OBLIGATIONS</h3>
<p>The borrower agrees to pay all instalments on or before the due date; ensure sufficient funds are available for repayment; and not intentionally avoid or delay repayment. Repayments may be made through: Card deduction, Stop order, Debit order, Mobile money payment, or Bank transfer/deposit.</p></div>

<div class="clause"><h3>3.6. EARLY SETTLEMENT</h3>
<p>The borrower may settle the loan at any time before the final due date. However, no discount shall apply for early settlement, and the borrower remains liable for all interest and fees agreed in this contract unless otherwise determined by the Lender.</p></div>

<div class="clause"><h3>3.7. LATE PAYMENT PENALTIES</h3>
<p>If the Borrower fails to make payment of any instalment on the due date, the loan account will be considered in arrears. A late payment fee of <strong>M20.00</strong> will be charged for every ten (10) days that the required instalment remains unpaid. The penalty will continue to accumulate every 10 days until the overdue instalment is paid in full. These penalty charges are in addition to any outstanding instalment amounts.</p></div>

<div class="clause"><h3>3.8. DEFAULT</h3>
<p>The borrower will be considered in default if any instalment remains unpaid after the due date, the borrower provides false or misleading information, or the borrower fails to comply with any terms of this agreement. In the event of default, the Lender may demand immediate payment of the full outstanding balance, refer the account to a debt collection agency, or initiate legal proceedings.</p></div>

<div class="clause"><h3>3.9. ASSIGNMENT OF LOAN</h3>
<p>The Lender reserves the right to assign, transfer, or sell this loan agreement or any outstanding balance to a third party. The Borrower agrees that repayment obligations remain valid and payable to the new creditor.</p></div>

<div class="clause"><h3>3.10. CREDIT BUREAU CONSENT</h3>
<p>The Borrower authorises the Lender to obtain credit information from credit bureaus and to report repayment behaviour to credit bureaus. This may include both positive and negative credit records.</p></div>

<div class="clause"><h3>3.11. COMMUNICATION CONSENT</h3>
<p>The Borrower agrees that the Lender may communicate via phone calls, SMS, WhatsApp, and email (if available). Communications may include repayment reminders, loan notices, account statements, and collection notifications. Any communication sent to the registered phone number shall be considered valid delivery.</p></div>

<div class="clause"><h3>3.12. CONTACT INFORMATION OBLIGATION</h3>
<p>The Borrower must maintain an active phone number throughout the loan duration and must notify the Lender within five (5) business days of any changes to phone number, residential address, employment details, or banking details.</p></div>

<div class="page-break"></div>

<div class="clause"><h3>3.13. TRACING PERMISSION</h3>
<p>If the Borrower becomes unreachable or fails to repay, the Borrower authorises the Lender to contact the employer, next of kin, and references provided during the loan application for tracing purposes.</p></div>

<div class="clause"><h3>3.14. COLLECTION AND LEGAL COSTS</h3>
<p>If the loan is referred for debt collection or legal proceedings, the Borrower agrees to pay all reasonable costs associated with recovery of the debt, including legal costs, debt collection fees, and tracing costs, as permitted by law.</p></div>

<div class="clause"><h3>3.15. ELECTRONIC RECORDS AND DIGITAL SIGNATURE</h3>
<p>By proceeding, you consent to the use of electronic records and agree that all loan documents may be issued and stored digitally. You authorize the use of a digital signature, including signing on a device using your finger, stylus, or mouse. You also agree that a One-Time Password (OTP) sent to your registered mobile number may be used as your electronic signature. You acknowledge that such electronic signatures are legally binding and equivalent to a handwritten signature. Once the loan is approved it can be disbursed to the specified payment method.</p></div>

<div class="clause"><h3>3.16. ACKNOWLEDGEMENT OF DEBT</h3>
<p>The Borrower acknowledges that the loan amount, interest, and fees reflected in the Loan Summary constitute a lawful debt owed to the Lender. The Borrower undertakes to repay the loan in full according to the repayment schedule.</p></div>

<div class="clause"><h3>3.17. SETTLEMENT QUOTATION</h3>
<p>The Lender shall provide a settlement quotation on request, or the Borrower can download it, showing the total outstanding principal, accrued interest, and fees required to fully settle the loan at any given date.</p></div>

<div class="clause"><h3>3.18. ACCOUNT STATEMENTS</h3>
<p>The Borrower may request or download a statement of account from the Lender or portal during the term of the loan.</p></div>

<div class="clause"><h3>3.19. RIGHT OF SET-OFF</h3>
<p>The Borrower agrees that if the Borrower holds or later opens any account or financial service with the Lender, the Lender shall have the right to deduct any overdue loan amount directly from such account or balance without further notice to the Borrower, to be applied toward the settlement of outstanding loan obligations.</p></div>

<div class="clause"><h3>3.20. ELECTRONIC TRANSACTION CONFIRMATION</h3>
<p>The Borrower acknowledges that all electronic transactions related to this loan shall be valid and legally binding, including loan applications submitted electronically, OTP verification, SMS confirmations, electronic records stored by the Lender's system, and mobile money or bank transaction records.</p></div>

<div class="clause"><h3>3.21. WAIVER OF DEFENCE</h3>
<p>The Borrower acknowledges that the loan amount disbursed under this agreement constitutes a valid debt and agrees not to dispute the existence of the debt on the basis that the agreement was signed electronically, accepted through OTP verification, or concluded through a digital platform. The Borrower agrees that electronic acceptance of this agreement shall be fully enforceable in a court of law.</p></div>

<div class="clause"><h3>3.22. BORROWER OBLIGATION AND NO SETTLEMENT AVOIDANCE</h3>
<p>The Borrower acknowledges that the loan amount, interest, fees, and charges constitute a lawful and binding debt. The Borrower shall not attempt to evade repayment by changing phone numbers without notification, leaving the registered address without notice, claiming the loan was taken by another person, or attempting to cancel/reverse digital transactions. Any attempt to avoid payment shall be considered a material breach and the Lender may take all legal actions to recover the debt.</p></div>

<div class="clause"><h3>3.23. BORROWER DECLARATION</h3>
<p>The Borrower confirms that all information provided in the loan application is true and correct; the Borrower understands the terms of this agreement; and the Borrower is not under administration or debt review.</p></div>

{{-- Signatures --}}
<h2>4. SIGNATURES</h2>
<p style="margin-bottom:16px">Signed on this date: <span style="border-bottom:1px solid #222;display:inline-block;width:160px">&nbsp;</span></p>

<div class="sig-grid">
  <div class="sig-box">
    <div style="font-weight:700;margin-bottom:12px">BORROWER</div>
    <div style="margin-bottom:24px;font-size:12px">Name: {{ $loan->user->name }}</div>
    
    @php
        $sigData = '';
        if ($loan->application && $loan->application->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($loan->application->signature_path)) {
            $sigData = 'data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($loan->application->signature_path));
        }
    @endphp

    @if($sigData)
        <div style="height:60px;margin-bottom:6px">
            <img src="{{ $sigData }}" style="max-height:60px;max-width:200px" alt="Signature">
        </div>
    @else
        <div style="height:60px;border-bottom:1px dashed #9ca3af;margin-bottom:6px"></div>
    @endif

    <div style="font-size:10px;color:#64748b">Borrower Signature</div>
    <div style="margin-top:14px;font-size:12px">Phone: {{ $loan->user->phone ?? '—' }}</div>
    <div style="margin-top:8px;font-size:12px">Date: ___________________________</div>
  </div>

  <div class="sig-box">
    @php
        $dirSigVal = \App\Models\SystemSetting::get('director_signature');
        $dirSigDataUrl = null;
        if ($dirSigVal && \Illuminate\Support\Facades\Storage::disk('public')->exists($dirSigVal)) {
            $dirSigDataUrl = 'data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($dirSigVal));
        }
        $directorName = \App\Models\SystemSetting::get('director_name', 'Tjale Maila');
        $directorTitle = \App\Models\SystemSetting::get('director_title', 'Managing Director');
        $companyName = \App\Models\SystemSetting::get('app_name', 'MyLoan Limited');
    @endphp
    <div style="font-weight:700;margin-bottom:12px">FOR {{ strtoupper($companyName) }} (LENDER)</div>
    <div style="margin-bottom:4px;font-size:12px">Authorised Representative: <strong>{{ $directorName }} ({{ $directorTitle }})</strong></div>
    
    @if($dirSigDataUrl)
      <div style="height:70px;display:flex;align-items:center;padding:4px 0">
         <img src="{{ $dirSigDataUrl }}" style="max-height:70px;max-width:200px" alt="Signature">
      </div>
    @else
      <div style="height:70px;display:flex;align-items:center;padding:4px 0">
        <div style="font-family:'Brush Script MT', cursive; font-size:32px; color:#1a5c2e; transform:rotate(-5deg)">{{ $directorName }}</div>
      </div>
    @endif
    
    <div style="font-size:10px;color:#64748b;margin-top:2px">Signature — {{ $directorName }} ({{ $directorTitle }})</div>
    <div style="margin-top:10px;font-size:12px">Date: {{ now()->format('d F Y') }}</div>
  </div>
</div>

<hr class="divider" style="margin-top:30px">
<p class="center muted" style="margin-top:8px">MyLoan Limited · L&amp;M Complex, Ha Thamae, Maseru, Lesotho · Generated: {{ now()->format('d F Y H:i') }}</p>

</body>
</html>
