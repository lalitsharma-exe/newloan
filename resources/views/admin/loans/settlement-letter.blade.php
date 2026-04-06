<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Settlement Letter — {{ $loan->loan_number }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif}
body{background:#f3f4f6;padding:30px}
.doc{max-width:620px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.1)}
.header{background:#065f46;color:#fff;padding:28px 36px;display:flex;justify-content:space-between;align-items:flex-start}
.header h1{font-size:22px;font-weight:800}
.header p{font-size:12px;opacity:.7;margin-top:3px}
.header-right{text-align:right;font-size:12px;opacity:.8}
.body{padding:36px}
.cleared-badge{background:#d1fae5;border:2px solid #10b981;border-radius:10px;padding:16px 20px;text-align:center;margin-bottom:28px}
.cleared-badge .icon{font-size:36px;margin-bottom:6px}
.cleared-badge h2{font-size:18px;font-weight:800;color:#065f46}
.cleared-badge p{font-size:13px;color:#047857;margin-top:4px}
.date-line{font-size:13px;color:#6b7280;margin-bottom:24px}
.letter-body{font-size:14px;color:#374151;line-height:1.8;margin-bottom:24px}
.letter-body strong{color:#1f2937}
.summary{background:#f8fafc;border-radius:10px;padding:16px 20px;margin:20px 0}
.summary-row{display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid #e2e8f0}
.summary-row:last-child{border-bottom:none;font-weight:700;font-size:14px;color:#065f46}
.signature-area{margin-top:36px;padding-top:20px;border-top:1px dashed #e2e8f0;display:flex;justify-content:space-between}
.sig-block{font-size:12px;color:#6b7280}
.sig-block .line{border-top:1px solid #374151;width:160px;margin-bottom:6px;margin-top:32px}
.footer{border-top:1px solid #e2e8f0;padding:14px 36px;font-size:11px;color:#9ca3af;display:flex;justify-content:space-between;align-items:center}
@media print{
    body{background:#fff;padding:0}
    .doc{box-shadow:none}
    .header{background:#fff !important;color:#065f46 !important;padding:20px 0;border-bottom:2px solid #e2e8f0}
    .header-right{color:#374151 !important}
    .footer button{display:none}
}
</style>
</head>
<body>
<div class="doc">
    <div class="header">
        <div>
            <h1>MyLoan Limited</h1>
            <p>Settlement Confirmation Letter</p>
        </div>
        <div class="header-right">
            <div>Ref: {{ $loan->loan_number }}</div>
            <div>Date: {{ now()->format('d M Y') }}</div>
        </div>
    </div>
    <div class="body">
        <div class="cleared-badge">
            <div class="icon">✅</div>
            <h2>LOAN FULLY SETTLED</h2>
            <p>This confirms that all obligations under loan {{ $loan->loan_number }} have been satisfied.</p>
        </div>

        <div class="date-line">{{ now()->format('d F Y') }}</div>

        <div class="letter-body">
            <p>Dear <strong>{{ $loan->user?->name }}</strong>,</p>
            <br>
            <p>
                We are pleased to confirm that your loan account with MyLoan Limited has been
                <strong>fully settled and closed</strong> as of
                <strong>{{ $loan->closed_at?->format('d M Y') ?? now()->format('d M Y') }}</strong>.
            </p>
            <br>
            <p>
                All payments due under this agreement have been received in full and your loan account
                now carries a <strong>zero balance</strong>. No further payments are required.
            </p>
            <br>
            <p>
                This letter serves as your official confirmation of settlement and may be used as proof
                that you have fulfilled all obligations under loan reference <strong>{{ $loan->loan_number }}</strong>.
            </p>
        </div>

        @php
            $sigVal = \App\Models\SystemSetting::get('director_signature');
            $sigDataUrl = null;
            if ($sigVal && \Illuminate\Support\Facades\Storage::disk('public')->exists($sigVal)) {
                $sigDataUrl = 'data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($sigVal));
            }
            $directorName = \App\Models\SystemSetting::get('director_name', 'Tjale Maila');
            $directorTitle = \App\Models\SystemSetting::get('director_title', 'Managing Director');
            $companyName = \App\Models\SystemSetting::get('app_name', 'MyLoan Limited');
        @endphp

        <div class="summary">
            <div style="font-weight:700;color:#1e3a5f;margin-bottom:10px;font-size:13px">LOAN SUMMARY</div>
            <div class="summary-row"><span>Loan Reference</span><span>{{ $loan->loan_number }}</span></div>
            <div class="summary-row"><span>Loan Product</span><span>{{ $loan->loanProduct?->name ?? '—' }}</span></div>
            <div class="summary-row"><span>Original Principal</span><span>M {{ number_format($loan->principal_amount, 2) }}</span></div>
            <div class="summary-row"><span>Total Amount Repaid</span><span>M {{ number_format($totalPaid, 2) }}</span></div>
            <div class="summary-row"><span>Settlement Date</span><span>{{ $loan->closed_at?->format('d M Y') ?? now()->format('d M Y') }}</span></div>
            <div class="summary-row"><span>Outstanding Balance</span><span>M 0.00 — CLEARED</span></div>
        </div>

        <p style="font-size:14px;color:#374151;line-height:1.7">
            We thank you for your business and wish you continued financial success.
            Should you require any further assistance please contact our offices.
        </p>

        <div class="signature-area">
            <div class="sig-block">
                @if($sigDataUrl)
                    <div style="margin-top:10px;height:40px;margin-bottom:10px">
                        <img src="{{ $sigDataUrl }}" style="max-height:40px;max-width:200px" alt="Signature">
                    </div>
                @else
                    <div class="line"></div>
                    <div>Authorised Signatory</div>
                @endif
                <div style="font-weight:600;color:#1e3a5f;margin-top:6px;font-size:13px">{{ $directorName }}</div>
                <div>{{ $directorTitle }}</div>
                <div style="margin-top:2px">{{ $companyName }}</div>
            </div>
            <div class="sig-block" style="text-align:right">
                @php
                    $qrVal = \App\Models\SystemSetting::get('system_qr');
                    $qrDataUrl = null;
                    if ($qrVal && \Illuminate\Support\Facades\Storage::disk('public')->exists($qrVal)) {
                        try {
                            $qrDataUrl = 'data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($qrVal));
                        } catch (\Exception $e) {}
                    }
                @endphp
                @if($qrDataUrl)
                    <div style="text-align:center">
                        <img src="{{ $qrDataUrl }}" style="width:80px;height:80px;object-fit:contain;" alt="System QR">
                        <div style="font-size:7px;color:#9ca3af;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px">Digital Authentication</div>
                    </div>
                @else
                    <div style="margin-top:32px;margin-bottom:6px;font-size:13px;color:#374151">Official Stamp</div>
                    <div style="width:80px;height:80px;border:2px dashed #d1d5db;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;color:#9ca3af;margin-left:auto">STAMP</div>
                @endif
            </div>
        </div>
    </div>
    <div class="footer">
        <span>MyLoan Limited · Maseru, Lesotho · Generated {{ now()->format('d M Y H:i') }}</span>
        <button onclick="window.print()" style="border:1px solid #d1d5db;background:#fff;padding:6px 16px;border-radius:6px;cursor:pointer;font-size:12px">🖨 Print</button>
    </div>
</div>
</body>
</html>
