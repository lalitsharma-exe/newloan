<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Consolidated Settlement Confirmation — {{ $user->name }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #065f46;
    --success: #10b981;
    --border: #e2e8f0;
    --bg-light: #f8fafc;
    --text-dark: #0f172a;
    --text-muted: #64748b;
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter', sans-serif}
body{background:#f1f5f9;padding:40px 20px;color:var(--text-dark)}
.doc{max-width:750px;margin:0 auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.08);position:relative}
.doc::before {content:'';position:absolute;top:0;left:0;right:0;height:6px;background:linear-gradient(90deg, #059669, #10b981)}

.header{padding:40px 40px 30px;display:flex;justify-content:space-between;align-items:flex-start;border-bottom:1px solid var(--border)}
.logo-area img {height:55px;width:auto;display:block}
.header-info {text-align:right}
.header-info h1 {font-size:20px;font-weight:800;color:var(--primary);margin-bottom:8px;text-transform:uppercase;letter-spacing:1px}
.meta-row {font-size:12px;color:var(--text-muted);margin-bottom:4px}
.meta-row strong {color:var(--text-dark);font-weight:700}

.body{padding:40px}

.cleared-banner {background:#ecfdf5;border:1px solid #a7f3d0;border-radius:16px;padding:24px;text-align:center;margin-bottom:40px}
.cleared-icon {font-size:40px;margin-bottom:12px}
.cleared-banner h2 {font-size:22px;font-weight:800;color:#065f46;text-transform:uppercase;letter-spacing:1px}
.cleared-banner p {font-size:14px;color:#047857;margin-top:6px}

.letter-content {font-size:15px;line-height:1.8;color:#334155;margin-bottom:40px}
.letter-content p {margin-bottom:20px}
.letter-content strong {color:var(--text-dark)}

.summary-card {background:var(--bg-light);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:40px}
.summary-title {font-size:11px;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:10px}

.breakdown-table {width:100%;border-collapse:separate;border-spacing:0;margin-bottom:20px}
.breakdown-table th {text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;padding:10px 0}
.breakdown-table td {padding:10px 0;font-size:13px;border-bottom:1px solid rgba(0,0,0,0.03)}
.breakdown-table tr:last-child td {border-bottom:none}

.sig-section {display:flex;justify-content:space-between;align-items:flex-end;padding-top:30px;border-top:1px dashed var(--border)}
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
            <img src="{{ asset(config('app.logo')) }}" alt="Prosperity Loans Logo">
        </div>
        <div class="header-info">
            <h1>Clearance Certificate</h1>
            <div class="meta-row">National ID: <strong>{{ $user->national_id }}</strong></div>
            <div class="meta-row">Phone Number: <strong>{{ $user->phone }}</strong></div>
            <div class="meta-row">Date Issued: <strong>{{ now()->format('d M Y') }}</strong></div>
        </div>
    </div>

    <div class="body">
        <div class="cleared-banner">
            <div class="cleared-icon">⚖️</div>
            <h2>Consolidated Clearance Confirmed</h2>
            <p>All loan accounts associated with this borrower have been successfully settled.</p>
        </div>

        <div class="letter-content">
            <p>
                To: <strong>{{ $user->name }}</strong><br>
                <small style="color:var(--text-muted)">{{ $user->employment?->postal_address ?? $user->address ?? 'N/A' }}</small>
            </p>
            
            <p>Dear <strong>{{ $user->name }}</strong>,</p>
            
            <p>
                We are pleased to formally confirm that all your loan accounts with <strong>Prosperity Loans Limited</strong> have been 
                <strong>fully settled and closed</strong> as of <strong>{{ now()->format('d M Y') }}</strong>.
            </p>

            <p>
                A comprehensive review of our records indicates that all financial obligations, including principal, interest, 
                and related fees across your multiple accounts, have been met in full. Your total outstanding balance 
                with Prosperity Loans Limited now stands at <strong>M 0.00</strong>.
            </p>

            <p>
                The following loan accounts are included in this consolidated clearance:
            </p>
        </div>

        <div class="summary-card">
            <div class="summary-title">Settled Accounts Summary</div>
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Loan Number</th>
                        <th>Product</th>
                        <th style="text-align:right">Final Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loans as $loan)
                    <tr>
                        <td style="font-weight:700; color:var(--primary)">{{ $loan->loan_number }}</td>
                        <td>{{ $loan->loanProduct?->name ?? 'Loan' }}</td>
                        <td style="text-align:right"><span style="color:var(--success); font-weight:700">FULLY PAID</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="letter-content" style="margin-top:-20px">
            <p>
                This certificate may be presented to any financial institution or regulatory body as official proof 
                that you have no further liabilities with our company. 
            </p>
            <p>
                We thank you for your patronage and look forward to assisting you with any future financial requirements.
            </p>
        </div>

        <div class="sig-section">
            <div class="sig-details">
                @php
                    $sigVal = \App\Models\SystemSetting::get('director_signature');
                    $sigDataUrl = null;
                    if ($sigVal && \Illuminate\Support\Facades\Storage::disk('public')->exists($sigVal)) {
                        try { 
                            $ext = pathinfo($sigVal, PATHINFO_EXTENSION);
                            $mime = ($ext === 'svg') ? 'image/svg+xml' : ($ext === 'webp' ? 'image/webp' : 'image/png');
                            $sigDataUrl = 'data:' . $mime . ';base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($sigVal)); 
                        } catch (\Exception $e) {}
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
                <div style="font-weight:700;color:var(--primary);margin-top:2px">Prosperity Loans Limited</div>
            </div>
            <div class="sig-stamp">
                <div style="text-align:center">
                    @php 
                        $qrVal = \App\Models\SystemSetting::get('system_qr');
                        $qrDataUrl = null;
                        if ($qrVal && \Illuminate\Support\Facades\Storage::disk('public')->exists($qrVal)) {
                            try { 
                                $ext = pathinfo($qrVal, PATHINFO_EXTENSION);
                                $mime = ($ext === 'svg') ? 'image/svg+xml' : ($ext === 'webp' ? 'image/webp' : 'image/png');
                                $qrDataUrl = 'data:' . $mime . ';base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($qrVal)); 
                            } catch (\Exception $e) {}
                        }
                    @endphp
                    @if($qrDataUrl)
                        <img src="{{ $qrDataUrl }}" style="width:100px;height:100px" alt="Official QR">
                    @else
                        @php $verifyUrl = route('borrower.login'); @endphp
                        <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{ urlencode($verifyUrl) }}&choe=UTF-8" style="width:100px;height:100px" alt="QR Code">
                    @endif
                    <div style="font-size:8px;color:var(--text-muted);margin-top:6px;text-transform:uppercase;letter-spacing:1px">Verified Digital Doc</div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div>Prosperity Loans Limited · Maseru, Lesotho · Generated at {{ now()->format('H:i') }}</div>
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
            Print Certificate
        </button>
    </div>
</div>
</body>
</html>
