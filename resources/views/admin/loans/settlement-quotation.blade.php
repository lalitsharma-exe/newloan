<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Settlement Quotation — {{ $loan->loan_number }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #1e3a5f;
    --success: #10b981;
    --danger: #ef4444;
    --border: #e2e8f0;
    --bg-light: #f8fafc;
    --text-dark: #0f172a;
    --text-muted: #64748b;
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter', sans-serif}
body{background:#f1f5f9;padding:40px 20px;color:var(--text-dark)}
.doc{max-width:700px;margin:0 auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.08);position:relative}
.doc::before {content:'';position:absolute;top:0;left:0;right:0;height:6px;background:linear-gradient(90deg, #1e3a5f, #3b82f6)}

.header{padding:40px 40px 30px;display:flex;justify-content:space-between;align-items:flex-start;border-bottom:1px solid var(--border)}
.logo-area img {height:55px;width:auto;display:block}
.header-info {text-align:right}
.header-info h1 {font-size:20px;font-weight:800;color:var(--primary);margin-bottom:8px;text-transform:uppercase;letter-spacing:1px}
.meta-row {font-size:12px;color:var(--text-muted);margin-bottom:4px}
.meta-row strong {color:var(--text-dark);font-weight:700}

.body{padding:40px}
.page-title{font-size:28px;font-weight:800;color:var(--primary);margin-bottom:30px;text-align:center;letter-spacing:-0.5px}

.notice-box {background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:16px 20px;display:flex;gap:12px;margin-bottom:30px}
.notice-box i {color:#d97706;font-size:18px}
.notice-text {font-size:13px;color:#92400e;line-height:1.5}

.section-title {font-size:12px;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.section-title::after {content:'';flex:1;height:1px;background:var(--border)}

.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:35px}
.info-item .lbl{font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.info-item .val{font-size:14px;font-weight:600;color:var(--text-dark)}

.breakdown-table {width:100%;border-collapse:separate;border-spacing:0;margin-bottom:35px;border:1px solid var(--border);border-radius:12px;overflow:hidden}
.breakdown-table th {background:var(--bg-light);padding:14px 20px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid var(--border)}
.breakdown-table td {padding:14px 20px;font-size:14px;border-bottom:1px solid var(--border)}
.breakdown-table tr:last-child td {border-bottom:none}
.breakdown-table .total-row {background:var(--primary);color:#fff}
.breakdown-table .total-row td {font-weight:800;font-size:16px;padding:20px}

.payment-box {display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:40px}
.p-instr {background:#f0f7ff;border:1px solid #cce3ff;border-radius:12px;padding:20px}
.p-bank {background:var(--bg-light);border:1px solid var(--border);border-radius:12px;padding:20px}
.box-lbl {font-size:11px;font-weight:800;color:var(--primary);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px}

.bank-list {list-style:none}
.bank-list li {display:flex;justify-content:space-between;font-size:13px;padding:6px 0;border-bottom:1px solid rgba(0,0,0,0.03)}
.bank-list li:last-child {border-bottom:none}
.bank-list .b-lbl {color:var(--text-muted)}
.bank-list .b-val {font-weight:700;color:var(--text-dark)}

.sig-section {display:flex;justify-content:space-between;align-items:flex-end;margin-top:20px;padding-top:30px;border-top:1px dashed var(--border)}
.sig-details {font-size:12px;color:var(--text-muted)}
.sig-name {font-size:15px;font-weight:800;color:var(--primary);margin-top:10px}

.footer {background:var(--bg-light);padding:20px 40px;display:flex;justify-content:space-between;align-items:center;font-size:11px;color:var(--text-muted);border-top:1px solid var(--border)}
.btn-print {background:#fff;border:1px solid var(--border);padding:8px 18px;border-radius:8px;font-size:12px;font-weight:700;color:var(--text-dark);cursor:pointer;display:flex;align-items:center;gap:8px;box-shadow:0 2px 4px rgba(0,0,0,0.05)}

@media print{
    body{background:#fff;padding:0}
    .doc{box-shadow:none;border-radius:0;max-width:none}
    .btn-print {display:none}
    .footer {background:#fff}
}
</style>
</head>
<body>
<div class="doc">
    <div class="header">
        <div class="logo-area">
            <img src="{{ asset(config('app.logo')) }}" alt="MyLoan Logo">
        </div>
        <div class="header-info">
            <h1>Settlement Quote</h1>
            <div class="meta-row">Reference: <strong>{{ $loan->loan_number }}</strong></div>
            <div class="meta-row">Generated: <strong>{{ now()->format('d M Y') }}</strong></div>
            <div class="meta-row">Valid Until: <strong style="color:var(--danger)">{{ $validUntil }}</strong></div>
        </div>
    </div>

    <div class="body">
        <div class="notice-box">
            <div class="notice-text">
                <strong>Important Notice:</strong> This quotation is valid until the date shown above. Please ensure payment is made exactly as indicated to avoid any additional interest or penalties.
            </div>
        </div>

        <div class="section-title">Borrower Information</div>
        <div class="info-grid">
            <div class="info-item"><div class="lbl">Full Name</div><div class="val">{{ $loan->user?->name ?? '—' }}</div></div>
            <div class="info-item"><div class="lbl">Loan Reference</div><div class="val">{{ $loan->loan_number }}</div></div>
            <div class="info-item"><div class="lbl">Product Type</div><div class="val">{{ $loan->loanProduct?->name ?? '—' }}</div></div>
            <div class="info-item"><div class="lbl">Maturity Date</div><div class="val">{{ $loan->maturity_date?->format('d M Y') ?? '—' }}</div></div>
        </div>

        <div class="section-title">Settlement Breakdown</div>
        <table class="breakdown-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align:right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Initial Principal Amount</td>
                    <td style="text-align:right">M {{ number_format($loan->principal_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Agreement Amount (incl. fees)</td>
                    <td style="text-align:right">M {{ number_format($loan->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="color:var(--success)">Total Amount Paid to Date</td>
                    <td style="text-align:right;color:var(--success)">- M {{ number_format($loan->total_amount - $loan->outstanding_balance, 2) }}</td>
                </tr>
                @php $overdue = $loan->installments->where('status','overdue')->sum('late_fee') @endphp
                @if($overdue > 0)
                <tr>
                    <td style="color:var(--danger)">Accumulated Late Fees & Penalties</td>
                    <td style="text-align:right;color:var(--danger)">+ M {{ number_format($overdue, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL SETTLEMENT AMOUNT</td>
                    <td style="text-align:right">M {{ number_format($outstanding, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="payment-box">
            <div class="p-instr">
                <div class="box-lbl">Payment Instructions</div>
                <div style="font-size:13px;line-height:1.6;color:#1e40af">
                    Please pay <strong>M {{ number_format($outstanding, 2) }}</strong> by <strong>{{ $validUntil }}</strong> to clear this loan account.
                    <br><br>
                    <strong>Use Reference:</strong>
                    <div style="background:#fff;padding:10px;border-radius:8px;border:1px solid #bfdbfe;font-size:18px;font-weight:800;text-align:center;margin-top:10px;color:var(--primary);letter-spacing:1px">
                        {{ $loan->loan_number }}
                    </div>
                </div>
            </div>
            <div class="p-bank">
                <div class="box-lbl">Banking Details</div>
                <ul class="bank-list">
                    <li><span class="b-lbl">Bank Name</span> <span class="b-val">{{ \App\Models\SystemSetting::get('bank_name', 'Standard Lesotho Bank') }}</span></li>
                    <li><span class="b-lbl">Branch / City</span> <span class="b-val">{{ \App\Models\SystemSetting::get('bank_branch_name', 'City Branch') }}</span></li>
                    <li><span class="b-lbl">Account Name</span> <span class="b-val">{{ \App\Models\SystemSetting::get('bank_account_name', 'MyLoan Limited') }}</span></li>
                    <li><span class="b-lbl">Account No.</span> <span class="b-val" style="color:var(--primary)">{{ \App\Models\SystemSetting::get('bank_account_number', '9080006273560') }}</span></li>
                    <li><span class="b-lbl">Branch Code</span> <span class="b-val">{{ \App\Models\SystemSetting::get('bank_branch_code', '060667') }}</span></li>
                    <li><span class="b-lbl">Swift Code</span> <span class="b-val">{{ \App\Models\SystemSetting::get('bank_swift_code', 'SBIC LSMX') }}</span></li>
                </ul>
            </div>
        </div>

        <div class="sig-section">
            <div class="sig-details">
                @php
                    $sigVal = \App\Models\SystemSetting::get('director_signature');
                    $sigDataUrl = null;
                    if ($sigVal && \Illuminate\Support\Facades\Storage::disk('public')->exists($sigVal)) {
                        $sigDataUrl = 'data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($sigVal));
                    }
                    $directorName = \App\Models\SystemSetting::get('director_name', 'Tjale Maila');
                    $directorTitle = \App\Models\SystemSetting::get('director_title', 'Managing Director');
                @endphp
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px">Authorised Signatory:</div>
                @if($sigDataUrl)
                    <img src="{{ $sigDataUrl }}" style="height:60px;max-width:200px;object-fit:contain;display:block" alt="Signature">
                @else
                    <div style="height:60px;width:180px;border-bottom:1px solid var(--text-dark);margin-bottom:10px"></div>
                @endif
                <div class="sig-name">{{ $directorName }}</div>
                <div>{{ $directorTitle }}</div>
                <div style="font-weight:700;color:var(--primary);margin-top:2px">MyLoan Limited</div>
            </div>
            <div class="sig-stamp">
                @php
                    $qrVal = \App\Models\SystemSetting::get('system_qr');
                    $qrDataUrl = null;
                    if ($qrVal && \Illuminate\Support\Facades\Storage::disk('public')->exists($qrVal)) {
                        try { $qrDataUrl = 'data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($qrVal)); } catch (\Exception $e) {}
                    }
                @endphp
                @if($qrDataUrl)
                    <div style="text-align:center">
                        <img src="{{ $qrDataUrl }}" style="width:100px;height:100px" alt="QR Authentication">
                        <div style="font-size:8px;color:var(--text-muted);margin-top:6px;text-transform:uppercase;letter-spacing:1px">Verified Digital Doc</div>
                    </div>
                @else
                    <div style="width:100px;height:100px;border-radius:50%;border:2px dashed var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:10px;text-align:center;text-transform:uppercase;font-weight:700;letter-spacing:1px">Official<br>Stamp</div>
                @endif
            </div>
        </div>
    </div>

    <div class="footer">
        <div>MyLoan Limited · Maseru, Lesotho · Generated at {{ now()->format('H:i') }}</div>
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
            Print Quotation
        </button>
    </div>
</div>
</body>
</html>
