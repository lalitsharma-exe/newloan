<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt {{ $payment->payment_reference }}</title>
<style>
    @page { margin: 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Helvetica', 'Arial', sans-serif; }
    body { background: #f4f7fa; padding: @if($isPdf) 0 @else 40px 20px @endif; color: #334155; line-height: 1.5; }
    
    .receipt-container {
        max-width: 700px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
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
        color: rgba(10, 25, 47, 0.03); /* Dark Navy */
        font-weight: 900;
        z-index: 0;
        pointer-events: none;
        text-transform: uppercase;
    }

    .header {
        background: #0a192f; /* Dark Navy Blue */
        color: #fff;
        padding: 35px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .header-center h1 { font-size: 22px; font-weight: 800; margin-bottom: 2px; letter-spacing: 1px; }
    .header-center p { font-size: 13px; opacity: 0.9; font-weight: 500; }
    
    .status-badge {
        display: inline-block;
        padding: 6px 16px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .content { padding: 40px; position: relative; z-index: 1; }
    
    .amount-section {
        text-align: center;
        margin-bottom: 40px;
        padding: 30px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
    }
    .amount-label { font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .amount-value { font-size: 48px; font-weight: 800; color: #0a192f; }
    .settlement-type { 
        display: inline-block; margin-top: 10px; font-size: 12px; font-weight: 700; 
        padding: 4px 12px; border-radius: 4px;
        @if($payment->loan->outstanding_balance <= 0)
            background: #dcfce7; color: #15803d; 
        @else
            background: #e0e7ff; color: #0a192f;
        @endif
    }

    .info-grid { display: flex; flex-wrap: wrap; margin: 0 -15px; }
    .info-col { width: 50%; padding: 0 15px; margin-bottom: 30px; }
    .info-item { margin-bottom: 15px; }
    .info-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block; }
    .info-value { font-size: 14px; font-weight: 600; color: #1e293b; display: block; }

    .divider { border-top: 1px solid #f1f5f9; margin: 20px 0; }

    .loan-summary {
        background: #0a192f;
        color: #fff;
        padding: 25px;
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
    }
    .ls-item { text-align: center; flex: 1; }
    .ls-label { font-size: 10px; font-weight: 600; opacity: 0.7; text-transform: uppercase; margin-bottom: 5px; display: block; }
    .ls-value { font-size: 16px; font-weight: 700; }

    .footer {
        padding: 40px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    
    .company-info { font-size: 12px; color: #64748b; }
    .company-info strong { color: #0a192f; font-size: 14px; display: block; margin-bottom: 5px; }
    
    .qr-box { text-align: right; }
    .qr-box img { width: 80px; height: 80px; border: 1px solid #e2e8f0; padding: 4px; border-radius: 8px; background: #fff; }
    .qr-text { font-size: 10px; color: #94a3b8; margin-top: 5px; display: block; }

    .actions {
        max-width: 700px;
        margin: 20px auto 40px;
        display: flex;
        gap: 10px;
        justify-content: center;
    }
    .btn {
        padding: 12px 30px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
    }
    .btn-primary { background: #0a192f; color: #fff; }
    .btn-outline { background: #fff; color: #0a192f; border: 1px solid #0a192f; }
    .btn:hover { opacity: 0.9; transform: translateY(-1px); }

    @media print {
        body { background: #fff; padding: 0; }
        .receipt-container { box-shadow: none; border: 1px solid #eee; width: 100%; max-width: 100%; }
        .actions { display: none; }
    }
</style>
</head>
<body>

<div class="receipt-container">
    <div class="watermark">Official</div>
    
    <div class="header">
        <!-- Left: Logo -->
        <div style="width: 25%;">
            <img src="{{ config('app.logo') }}" alt="Logo" style="height: 50px; width: auto; filter: brightness(0) invert(1);">
        </div>
        
        <!-- Center: Receipt Title & Ref -->
        <div class="header-center" style="width: 50%; text-align: center;">
            <h1>PAYMENT RECEIPT</h1>
            <p>Reference: {{ $payment->payment_reference }}</p>
        </div>
        
        <!-- Right: Status Badge -->
        <div style="width: 25%; text-align: right;">
            <div class="status-badge">
                {{ $payment->status === 'verified' ? 'Verified' : 'Processing' }}
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
                    <span class="info-label">Customer Name</span>
                    <span class="info-value">{{ $payment->user?->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">National ID</span>
                    <span class="info-value">{{ $payment->user?->national_id ?? '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Loan Account</span>
                    <span class="info-value">{{ $payment->loan?->loan_number }}</span>
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
                    <span class="info-label">Staff / Agent</span>
                    <span class="info-value">{{ $payment->verifiedBy?->name ?? 'System Automated' }}</span>
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
        <div style="margin-top: 30px; font-size: 13px; font-style: italic; color: #64748b; border-left: 3px solid #0a192f; padding-left: 15px;">
            <strong>Note:</strong> {{ $payment->notes }}
        </div>
        @endif
    </div>
    
    <div class="footer">
        <div class="company-info">
            <strong>Prosperity Loans Limited</strong>
            <p>Ha Matala, KK Building, Maseru</p>
            <p>Phone: (+266) 5694 7028 / 5724 7936 / 6321 8591</p>
            <p>Email: prosperityloans1@gmail.com</p>
            <p>Website: www.prosperityloans.co.ls</p>
        </div>
        
        <div class="qr-box">
            @php $verifyUrl = route('receipt.verify', ['ref' => $payment->payment_reference]); @endphp
            <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{ urlencode($verifyUrl) }}&choe=UTF-8" alt="QR Code">
            <span class="qr-text">Scan to Verify Authenticity</span>
        </div>
    </div>
</div>

@if(!$isPdf)
<div class="actions">
    <button onclick="window.print()" class="btn btn-outline">Print Receipt</button>
    <a href="{{ route('admin.loans.receipt.download', [$payment->loan, $payment]) }}" class="btn btn-primary">Download PDF</a>
</div>
@endif

</body>
</html>
