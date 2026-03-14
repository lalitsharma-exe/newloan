<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Settlement Quotation — {{ $loan->loan_number }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif}
body{background:#f3f4f6;padding:30px}
.doc{max-width:620px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.1)}
.header{background:#1e3a5f;color:#fff;padding:28px 36px;display:flex;justify-content:space-between;align-items:flex-start}
.header h1{font-size:22px;font-weight:800}
.header p{font-size:12px;opacity:.7;margin-top:3px}
.header-right{text-align:right;font-size:12px;opacity:.8}
.body{padding:32px 36px}
.notice{background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:12px 16px;font-size:13px;color:#92400e;margin-bottom:24px}
h2{font-size:16px;font-weight:700;color:#1e3a5f;margin-bottom:12px;padding-bottom:6px;border-bottom:2px solid #e0e7ff}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:24px}
.info-item .lbl{font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px}
.info-item .val{font-size:14px;font-weight:600;color:#1f2937;margin-top:2px}
.breakdown{width:100%;border-collapse:collapse;font-size:14px;margin-bottom:20px}
.breakdown th{background:#f8fafc;padding:10px 14px;text-align:left;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #e2e8f0}
.breakdown td{padding:10px 14px;border-bottom:1px solid #f1f5f9}
.breakdown tr:last-child td{border-bottom:none}
.total-row{background:#f0fdf4}
.total-row td{font-weight:800;color:#065f46;font-size:16px;padding:14px}
.validity{background:#eff6ff;border-radius:8px;padding:14px 18px;font-size:13px;color:#1d4ed8;margin-bottom:24px}
.footer{border-top:1px solid #e2e8f0;padding:16px 36px;font-size:12px;color:#9ca3af;display:flex;justify-content:space-between;align-items:center}
@media print{body{background:#fff;padding:0}.doc{box-shadow:none}.footer button{display:none}}
</style>
</head>
<body>
<div class="doc">
    <div class="header">
        <div>
            <h1>MyLoan Limited</h1>
            <p>Settlement Quotation</p>
        </div>
        <div class="header-right">
            <div>Ref: {{ $loan->loan_number }}</div>
            <div>Date: {{ now()->format('d M Y') }}</div>
            <div>Valid Until: {{ $validUntil }}</div>
        </div>
    </div>
    <div class="body">
        <div class="notice">
            <strong>⚠ Important:</strong> This quotation is valid until <strong>{{ $validUntil }}</strong>.
            Full settlement must be received before this date to clear the loan. No discount applies on early settlement.
        </div>

        <h2>Borrower Details</h2>
        <div class="info-grid">
            <div class="info-item"><div class="lbl">Full Name</div><div class="val">{{ $loan->user->name }}</div></div>
            <div class="info-item"><div class="lbl">Loan Number</div><div class="val">{{ $loan->loan_number }}</div></div>
            <div class="info-item"><div class="lbl">Product</div><div class="val">{{ $loan->loanProduct?->name ?? '—' }}</div></div>
            <div class="info-item"><div class="lbl">Disbursement Date</div><div class="val">{{ $loan->disbursement_date?->format('d M Y') ?? '—' }}</div></div>
            <div class="info-item"><div class="lbl">Maturity Date</div><div class="val">{{ $loan->maturity_date?->format('d M Y') ?? '—' }}</div></div>
            <div class="info-item"><div class="lbl">Loan Status</div><div class="val">{{ ucfirst(str_replace('_',' ',$loan->status)) }}</div></div>
        </div>

        <h2>Settlement Breakdown</h2>
        <table class="breakdown">
            <thead><tr><th>Description</th><th style="text-align:right">Amount</th></tr></thead>
            <tbody>
                <tr><td>Original Principal</td><td style="text-align:right">M {{ number_format($loan->principal_amount, 2) }}</td></tr>
                <tr><td>Total Loan Amount (incl. all fees)</td><td style="text-align:right">M {{ number_format($loan->total_amount, 2) }}</td></tr>
                <tr><td>Amount Paid to Date</td><td style="text-align:right" style="color:#059669">M {{ number_format($loan->total_amount - $loan->outstanding_balance, 2) }}</td></tr>
                @php $overdue = $loan->installments->where('status','overdue')->sum('late_fee') @endphp
                @if($overdue > 0)
                <tr><td>Accumulated Penalties</td><td style="text-align:right;color:#dc2626">M {{ number_format($overdue, 2) }}</td></tr>
                @endif
                <tr class="total-row"><td><strong>SETTLEMENT AMOUNT DUE</strong></td><td style="text-align:right"><strong>M {{ number_format($outstanding, 2) }}</strong></td></tr>
            </tbody>
        </table>

        <div class="validity">
            <strong>Payment Instructions:</strong> To settle this loan please pay
            <strong>M {{ number_format($outstanding, 2) }}</strong> by <strong>{{ $validUntil }}</strong>.
            Quote reference <strong>{{ $loan->loan_number }}</strong> on all payments.
        </div>

        <div style="margin-top:20px;padding-top:20px;border-top:1px dashed #e2e8f0">
            <div style="font-size:12px;color:#6b7280">Authorised by:</div>
            <div style="margin-top:32px;border-top:1px solid #374151;width:200px;padding-top:6px;font-size:12px;color:#374151">Authorised Signatory</div>
        </div>
    </div>
    <div class="footer">
        <span>MyLoan Limited · Lesotho · Generated {{ now()->format('d M Y H:i') }}</span>
        <button onclick="window.print()" style="border:1px solid #d1d5db;background:#fff;padding:6px 16px;border-radius:6px;cursor:pointer;font-size:12px">🖨 Print</button>
    </div>
</div>
</body>
</html>
