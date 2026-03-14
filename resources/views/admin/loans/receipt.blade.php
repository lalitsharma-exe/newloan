<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Receipt {{ $payment->payment_reference }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif}
body{background:#f3f4f6;padding:30px}
.receipt{max-width:520px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.12)}
.header{background:linear-gradient(135deg,#1e3a5f,#4f46e5);color:#fff;padding:28px 32px;text-align:center}
.header h1{font-size:26px;font-weight:800;letter-spacing:1px}
.header p{font-size:13px;opacity:.8;margin-top:4px}
.paid-stamp{display:inline-block;border:3px solid #10b981;color:#10b981;font-size:22px;font-weight:900;padding:6px 20px;border-radius:6px;letter-spacing:3px;margin-top:12px;transform:rotate(-3deg)}
.body{padding:28px 32px}
.ref{text-align:center;font-size:13px;color:#6b7280;margin-bottom:20px}
.ref span{font-weight:700;color:#1e3a5f;font-size:15px}
.row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:14px}
.row:last-child{border-bottom:none}
.row .lbl{color:#6b7280}
.row .val{font-weight:600;color:#1f2937}
.amount-row{background:#f0fdf4;border-radius:10px;padding:14px 18px;margin:16px 0;display:flex;justify-content:space-between;align-items:center}
.amount-row .lbl{font-size:13px;color:#065f46;font-weight:600}
.amount-row .val{font-size:26px;font-weight:800;color:#059669}
.balance-row{background:#eff6ff;border-radius:10px;padding:12px 18px;display:flex;justify-content:space-between;align-items:center}
.balance-row .lbl{font-size:13px;color:#1d4ed8;font-weight:600}
.balance-row .val{font-size:18px;font-weight:800;color:#1d4ed8}
.footer{border-top:1px solid #e2e8f0;padding:16px 32px;text-align:center;font-size:12px;color:#9ca3af}
@media print{body{background:#fff;padding:0}.receipt{box-shadow:none}}
</style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <h1>MyLoan Limited</h1>
        <p>Payment Receipt</p>
        <div class="paid-stamp">PAID</div>
    </div>
    <div class="body">
        <div class="ref">Receipt No: <span>{{ $payment->payment_reference }}</span></div>

        <div class="amount-row">
            <span class="lbl">Amount Paid</span>
            <span class="val">M {{ number_format($payment->amount, 2) }}</span>
        </div>

        <div class="row"><span class="lbl">Borrower</span><span class="val">{{ $loan->user->name }}</span></div>
        <div class="row"><span class="lbl">Loan Reference</span><span class="val">{{ $loan->loan_number }}</span></div>
        <div class="row"><span class="lbl">Loan Product</span><span class="val">{{ $loan->loanProduct?->name ?? '—' }}</span></div>
        <div class="row"><span class="lbl">Payment Method</span><span class="val">{{ ucwords(str_replace('_',' ',$payment->method)) }}</span></div>
        <div class="row"><span class="lbl">Payment Date</span><span class="val">{{ $payment->verified_at?->format('d M Y H:i') ?? now()->format('d M Y') }}</span></div>
        @if($payment->reference)
        <div class="row"><span class="lbl">Reference #</span><span class="val">{{ $payment->reference }}</span></div>
        @endif
        @if($payment->notes)
        <div class="row"><span class="lbl">Notes</span><span class="val">{{ $payment->notes }}</span></div>
        @endif

        <div class="balance-row" style="margin-top:16px">
            <span class="lbl">Remaining Balance</span>
            <span class="val">M {{ number_format($loan->outstanding_balance, 2) }}</span>
        </div>
    </div>
    <div class="footer">
        Generated: {{ now()->format('d M Y H:i') }} &nbsp;·&nbsp; MyLoan Limited, Lesotho
        <br><br>
        <button onclick="window.print()" style="border:1px solid #d1d5db;background:#fff;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:13px">
            🖨 Print Receipt
        </button>
    </div>
</div>
</body>
</html>
