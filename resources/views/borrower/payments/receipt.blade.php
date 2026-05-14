<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt {{ $payment->payment_reference }}</title>
<style>
    @page { margin: 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Helvetica', 'Arial', sans-serif; }
    body { background: #f4f7fa; padding: 40px 20px; color: #334155; line-height: 1.5; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    
    .receipt-container {
        max-width: 700px;
        margin: 0 auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 1px solid #e2e8f0;
        position: relative;
    }
    
    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 80px;
        color: rgba(30, 27, 75, 0.03); /* Navy */
        font-weight: 900;
        z-index: 0;
        pointer-events: none;
        text-transform: uppercase;
    }

    .header {
        background: #1e1b4b; /* Navy Blue */
        color: #fff;
        padding: 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .header-center h1 { font-size: 24px; font-weight: 900; margin-bottom: 4px; letter-spacing: 1px; }
    .header-center p { font-size: 13px; opacity: 0.8; font-weight: 500; }
    
    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 100px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .content { padding: 40px; position: relative; z-index: 1; }
    
    .amount-section {
        text-align: center;
        margin-bottom: 40px;
        padding: 35px;
        background: #f8fafc;
        border-radius: 20px;
        border: 1px solid #f1f5f9;
    }
    .amount-label { font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 10px; display: block; letter-spacing: 1px; }
    .amount-value { font-size: 52px; font-weight: 900; color: #1e1b4b; letter-spacing: -1px; }
    .settlement-type { 
        display: inline-block; margin-top: 12px; font-size: 12px; font-weight: 800; 
        padding: 6px 14px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px;
        @if($payment->loan && $payment->loan->outstanding_balance <= 0)
            background: #dcfce7; color: #15803d; 
        @else
            background: #e0e7ff; color: #1e1b4b;
        @endif
    }

    .info-grid { display: flex; flex-wrap: wrap; margin: 0 -15px; }
    .info-col { width: 50%; padding: 0 15px; margin-bottom: 30px; }
    .info-item { margin-bottom: 18px; }
    .info-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 4px; display: block; }
    .info-value { font-size: 14px; font-weight: 700; color: #0f172a; display: block; }

    .divider { border-top: 1px solid #f1f5f9; margin: 25px 0; }

    .loan-summary {
        background: #1e1b4b;
        color: #fff;
        padding: 28px;
        border-radius: 20px;
        display: flex;
        justify-content: space-between;
    }
    .ls-item { text-align: center; flex: 1; }
    .ls-label { font-size: 10px; font-weight: 700; opacity: 0.7; text-transform: uppercase; margin-bottom: 6px; display: block; letter-spacing: 1px; }
    .ls-value { font-size: 17px; font-weight: 800; }

    .footer {
        padding: 40px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    
    .company-info { font-size: 12px; color: #64748b; }
    .company-info strong { color: #1e1b4b; font-size: 15px; display: block; margin-bottom: 6px; font-weight: 900; }
    
    .qr-box { text-align: right; }
    .qr-box img { width: 90px; height: 90px; border: 1px solid #e2e8f0; padding: 6px; border-radius: 12px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    .qr-text { font-size: 10px; color: #94a3b8; margin-top: 6px; display: block; font-weight: 600; }

    .actions {
        max-width: 700px;
        margin: 25px auto 50px;
        display: flex;
        gap: 12px;
        justify-content: center;
    }
    .btn {
        padding: 14px 35px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .btn-primary { background: #1e1b4b; color: #fff; box-shadow: 0 10px 20px rgba(30, 27, 75, 0.2); }
    .btn-outline { background: #fff; color: #1e1b4b; border: 2px solid #1e1b4b; }
    .btn:hover { opacity: 0.9; transform: translateY(-2px); }

    @media print {
        body { background: #fff; padding: 0; }
        .receipt-container { box-shadow: none; border: 1px solid #e2e8f0; width: 100%; max-width: 100%; border-radius: 0; }
        .actions { display: none; }
        .header { -webkit-print-color-adjust: exact; background-color: #1e1b4b !important; color: #fff !important; }
        .loan-summary { -webkit-print-color-adjust: exact; background-color: #1e1b4b !important; color: #fff !important; }
        .btn-primary { background-color: #1e1b4b !important; color: #fff !important; }
    }
</style>
</head>
<body>

<div class="receipt-container">
    <div class="watermark">Official</div>
    
    <div class="header">
        <div style="width: 25%;">
            <img src="{{ config('app.logo') }}" alt="Logo" style="height: 50px; width: auto; filter: brightness(0) invert(1);">
        </div>
        
        <div class="header-center" style="width: 50%; text-align: center;">
            <h1>PAYMENT RECEIPT</h1>
            <p>Ref: {{ $payment->payment_reference }}</p>
        </div>
        
        <div style="width: 25%; text-align: right;">
            <div class="status-badge">
                {{ $payment->status === 'verified' ? 'Verified' : 'Payment Received' }}
            </div>
        </div>
    </div>
    
    <div class="content">
        <div class="amount-section">
            <span class="amount-label">Amount Paid</span>
            <div class="amount-value">M {{ number_format($payment->amount, 2) }}</div>
            <div class="settlement-type">
                {{ $payment->loan->outstanding_balance <= 0 ? 'FULL SETTLEMENT' : 'PARTIAL REPAYMENT' }}
            </div>
        </div>
        
        <div class="info-grid">
            <div class="info-col">
                <div class="info-item">
                    <span class="info-label">Customer Name & Address</span>
                    <span class="info-value">
                        {{ $payment->user?->name }}<br>
                        <small style="color: #64748b; font-weight: 500;">{{ $payment->user?->employment?->postal_address ?? $payment->user?->address ?? 'N/A' }}</small>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">National ID</span>
                    <span class="info-value">{{ $payment->user?->national_id ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone Number</span>
                    <span class="info-value">{{ $payment->user?->phone ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Loan Account</span>
                    <span class="info-value">{{ $payment->loan?->loan_number ?? 'N/A' }}</span>
                </div>
            </div>
            
            <div class="info-col">
                <div class="info-item">
                    <span class="info-label">Payment Date</span>
                    <span class="info-value">{{ ($payment->verified_at ?? $payment->created_at)->format('d M Y, H:i') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Channel / Method</span>
                    <span class="info-value">{{ ucwords(str_replace('_', ' ', $payment->method)) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">{{ ucfirst($payment->status) }}</span>
                </div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="loan-summary">
            <div class="ls-item">
                <span class="ls-label">Balance Before</span>
                <div class="ls-value">M {{ number_format($balanceBefore, 2) }}</div>
            </div>
            <div style="width: 1px; background: rgba(255,255,255,0.2); margin: 0 20px;"></div>
            <div class="ls-item">
                <span class="ls-label">Balance After</span>
                <div class="ls-value">M {{ number_format($payment->loan->outstanding_balance, 2) }}</div>
            </div>
            <div style="width: 1px; background: rgba(255,255,255,0.2); margin: 0 20px;"></div>
            <div class="ls-item">
                <span class="ls-label">Next Due Date</span>
                <div class="ls-value">{{ $nextDue ? $nextDue->due_date->format('d M Y') : 'N/A' }}</div>
            </div>
        </div>
        
        @if($payment->notes)
        <div style="margin-top: 25px; font-size: 13px; font-style: italic; color: #64748b; border-left: 3px solid #1e1b4b; padding-left: 15px;">
            <strong>Note:</strong> {{ $payment->notes }}
        </div>
        @endif

        <div style="margin-top: 35px; padding-top: 25px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: flex-end;">
            <div style="font-size: 12px; color: #64748b;">
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
                <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Authorized By:</div>
                @if($sigDataUrl)
                    <img src="{{ $sigDataUrl }}" style="height: 50px; max-width: 180px; object-fit: contain; display: block; margin-bottom: 5px;" alt="Signature">
                @else
                    <div style="height: 40px; width: 150px; border-bottom: 1px solid #1e1b4b; margin-bottom: 10px;"></div>
                @endif
                <div style="font-weight: 800; color: #1e1b4b; font-size: 14px;">{{ $directorName }}</div>
                <div style="font-size: 11px;">{{ $directorTitle }}</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 10px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Document Ref</div>
                <div style="font-size: 13px; font-weight: 800; color: #1e1b4b;">{{ strtoupper($payment->payment_reference) }}</div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <div class="company-info">
            <strong>MyLoan Limited</strong>
            <p>L&M Complex, Ha Thamae, Maseru</p>
            <p>Phone: (+266) 59 229 149</p>
            <p>Email: info@myloan.co.ls</p>
        </div>
        
        <div class="qr-box">
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
                <img src="{{ $qrDataUrl }}" alt="Official QR">
            @else
                @php $verifyUrl = route('borrower.login'); @endphp
                <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{ urlencode($verifyUrl) }}&choe=UTF-8" alt="QR Code">
            @endif
            <span class="qr-text">Scan to Verify</span>
        </div>
    </div>
</div>

<div class="actions">
    <button onclick="window.print()" class="btn btn-outline">Print Receipt</button>
    <a href="{{ route('borrower.payments.index') }}" class="btn btn-primary">Back to History</a>
</div>

</body>
</html>
