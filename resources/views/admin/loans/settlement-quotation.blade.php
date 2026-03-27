<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Settlement Quotation — {{ $loan->loan_number }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif}
body{background:#f3f4f6;padding:30px}
.doc{max-width:640px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.1)}
.header{background:#1e3a5f;color:#fff;padding:28px 36px;display:flex;justify-content:space-between;align-items:flex-start}
.header p{font-size:12px;opacity:.7;margin-top:3px}
.header-right{text-align:right;font-size:13px;line-height:1.6}
.header-right strong{opacity:.7;font-weight:600}
.body{padding:32px 36px}
.page-title{font-size:24px;color:#1e3a5f;margin-bottom:20px;text-align:center;text-transform:uppercase;letter-spacing:1px;font-weight:800}
.notice{background:#fef3c7;border:1px solid #fbd38d;border-radius:8px;padding:12px 16px;font-size:13px;color:#92400e;margin-bottom:24px;text-align:center}
h2{font-size:15px;font-weight:700;color:#1e3a5f;margin-bottom:12px;padding-bottom:6px;border-bottom:2px solid #e0e7ff;text-transform:uppercase;letter-spacing:0.5px}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:28px}
.info-item .lbl{font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px}
.info-item .val{font-size:14px;font-weight:600;color:#1f2937;margin-top:2px}
.breakdown{width:100%;border-collapse:collapse;font-size:14px;margin-bottom:24px}
.breakdown th{background:#f8fafc;padding:12px 14px;text-align:left;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #e2e8f0;font-weight:700}
.breakdown td{padding:12px 14px;border-bottom:1px solid #f1f5f9}
.breakdown tr:last-child td{border-bottom:none}
.total-row{background:#f0fdf4}
.total-row td{font-weight:800;color:#065f46;font-size:16px;padding:16px 14px}
.footer{border-top:1px solid #e2e8f0;padding:16px 36px;font-size:12px;color:#9ca3af;display:flex;justify-content:space-between;align-items:center}
@media print{
    body{background:#fff;padding:0;}
    .doc{box-shadow:none;border-radius:0;max-width:none;}
    .header{background:#fff !important;color:#1e3a5f !important;padding:20px 0;border-bottom:2px solid #e2e8f0;}
    .header img{filter:none !important;}
    .header .header-right strong{color:#475569 !important;}
    .footer button{display:none;}
}
</style>
</head>
<body>
<div class="doc">
    <div class="header">
        <div>
            <div style="display:flex;align-items:center;gap:12px;">
              <img src="{{ config('app.logo') }}" alt="MyLoan" style="height:46px;width:auto;filter:brightness(0) invert(1)">
            </div>
        </div>
        <div class="header-right">
            <div><strong>Ref:</strong> <span style="font-weight:800;letter-spacing:0.5px">{{ $loan->loan_number }}</span></div>
            <div><strong>Date:</strong> {{ now()->format('d M Y') }}</div>
            <div><strong>Valid Until:</strong> {{ $validUntil }}</div>
        </div>
    </div>
    <div class="body">
        
        <div class="page-title">Settlement Quotation</div>

        <div class="notice">
            <strong>⚠ Important:</strong> This quotation is valid until <strong>{{ $validUntil }}</strong>.
            Full settlement must be received before this date to clear the loan. No discount applies on early settlement.
        </div>

        <h2>Borrower Details</h2>
        <div class="info-grid">
            <div class="info-item"><div class="lbl">Full Name</div><div class="val">{{ $loan->user?->name ?? 'Deleted User' }}</div></div>
            <div class="info-item"><div class="lbl">Loan Number</div><div class="val">{{ $loan->loan_number }}</div></div>
            <div class="info-item"><div class="lbl">Employer</div><div class="val">{{ $loan->application?->employment?->employer_name ?? '—' }}</div></div>
            <div class="info-item"><div class="lbl">Employee ID</div><div class="val">{{ $loan->application?->employment?->employment_number ?? '—' }}</div></div>
            <div class="info-item"><div class="lbl">Product</div><div class="val">{{ $loan->loanProduct?->name ?? '—' }}</div></div>
            <div class="info-item"><div class="lbl">Disbursement Date</div><div class="val">{{ $loan->disbursement_date?->format('d M Y') ?? '—' }}</div></div>
            <div class="info-item"><div class="lbl">Maturity Date</div><div class="val">{{ $loan->maturity_date?->format('d M Y') ?? '—' }}</div></div>
            <div class="info-item"><div class="lbl">Loan Status</div><div class="val">{{ ucfirst(str_replace('_',' ',$loan->status)) }}</div></div>
        </div>

        <h2>Settlement Breakdown</h2>
        @php
            $sigVal = \App\Models\SystemSetting::get('director_signature');
            $sigDataUrl = null;
            if ($sigVal && \Illuminate\Support\Facades\Storage::disk('public')->exists($sigVal)) {
                $sigDataUrl = 'data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($sigVal));
            }
            $directorName = \App\Models\SystemSetting::get('director_name', 'Tjale Maila');
            $directorTitle = \App\Models\SystemSetting::get('director_title', 'Managing Director');
        @endphp
        <table class="breakdown">
            <thead><tr><th>Description</th><th style="text-align:right">Amount</th></tr></thead>
            <tbody>
                <tr><td>Original Principal</td><td style="text-align:right">M {{ number_format($loan->principal_amount, 2) }}</td></tr>
                <tr><td>Total Loan Amount (incl. all fees)</td><td style="text-align:right">M {{ number_format($loan->total_amount, 2) }}</td></tr>
                <tr><td>Amount Paid to Date</td><td style="text-align:right;color:#059669">- M {{ number_format($loan->total_amount - $loan->outstanding_balance, 2) }}</td></tr>
                @php $overdue = $loan->installments->where('status','overdue')->sum('late_fee') @endphp
                @if($overdue > 0)
                <tr><td>Accumulated Penalties</td><td style="text-align:right;color:#dc2626">+ M {{ number_format($overdue, 2) }}</td></tr>
                @endif
                <tr class="total-row"><td>SETTLEMENT AMOUNT DUE</td><td style="text-align:right">M {{ number_format($outstanding, 2) }}</td></tr>
            </tbody>
        </table>

        <div style="display:flex;flex-wrap:wrap;gap:20px;margin-bottom:32px;">
            <div style="flex:1 1 200px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:18px;">
                <div style="font-size:11px;color:#1d4ed8;font-weight:800;text-transform:uppercase;margin-bottom:8px;letter-spacing:0.5px">Payment Instructions</div>
                <div style="font-size:13px;color:#1e3a8a;line-height:1.6">
                    To settle this loan please pay <strong>M {{ number_format($outstanding, 2) }}</strong> by <strong>{{ $validUntil }}</strong>.<br><br>
                    Quote Reference on all payments:<br>
                    <div style="display:inline-block;background:#fff;padding:6px 12px;border-radius:6px;border:1px solid #bfdbfe;font-size:15px;font-weight:800;margin-top:6px;color:#1e3a8a">{{ $loan->loan_number }}</div>
                </div>
            </div>
            
            <div style="flex:1 1 220px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:18px;">
                <div style="font-size:11px;color:#475569;font-weight:800;text-transform:uppercase;margin-bottom:8px;letter-spacing:0.5px">Banking Details</div>
                <ul style="list-style:none;font-size:13px;color:#334155;line-height:1.8">
                    <li><span style="color:#64748b;display:inline-block;width:95px">Bank Name:</span> <strong>{{ \App\Models\SystemSetting::get('bank_name', 'Standard Lesotho Bank') }}</strong></li>
                    <li><span style="color:#64748b;display:inline-block;width:95px">Account Name:</span> <strong>{{ \App\Models\SystemSetting::get('bank_account_name', 'MyLoan Limited') }}</strong></li>
                    <li><span style="color:#64748b;display:inline-block;width:95px">Account No:</span> <strong style="color:#0f172a">{{ \App\Models\SystemSetting::get('bank_account_number', 'XXXXXXXXX') }}</strong></li>
                    <li><span style="color:#64748b;display:inline-block;width:95px">Branch Code:</span> <strong>{{ \App\Models\SystemSetting::get('bank_branch_code', 'XXXX') }}</strong></li>
                </ul>
            </div>
        </div>

        <div style="margin-top:20px;padding-top:20px;border-top:1px dashed #ced4da;display:flex;justify-content:space-between">
            <div style="font-size:12px;color:#6b7280;min-width:200px">
                <div style="margin-bottom:8px;text-transform:uppercase;font-size:10px;letter-spacing:1px;font-weight:700">Authorised by:</div>
                @if($sigDataUrl)
                    <div style="height:50px">
                        <img src="{{ $sigDataUrl }}" style="height:100%;max-width:200px;object-fit:contain;object-position:left" alt="Signature">
                    </div>
                @else
                    <div style="margin-top:40px;border-top:1px solid #374151;width:100%;padding-top:6px;font-size:12px;color:#374151">Authorised Signatory</div>
                @endif
                <div style="margin-top:10px;font-weight:700;color:#1e3a5f;font-size:14px;">{{ $directorName }}</div>
                <div style="font-size:11px;margin-bottom:2px">{{ $directorTitle }}</div>
                <div style="font-weight:800;color:#1e3a5f;font-size:15px;letter-spacing:0.5px">MyLoan Limited</div>
            </div>
            
            <div style="text-align:right;align-self:flex-end">
                <div style="width:90px;height:90px;border-radius:50%;border:2px dashed #cbd5e1;display:flex;align-items:center;justify-content:center;color:#cbd5e1;font-size:10px;text-transform:uppercase;font-weight:700;letter-spacing:1px">Official<br>Stamp</div>
            </div>
        </div>
    </div>
    <div class="footer">
        <span>MyLoan Limited · Lesotho · Generated {{ now()->format('d M Y H:i') }}</span>
        <button onclick="window.print()" style="border:1px solid #d1d5db;background:#fff;padding:6px 16px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;color:#374151;box-shadow:0 1px 2px rgba(0,0,0,0.05)">🖨 Print Quotation</button>
    </div>
</div>
</body>
</html>
