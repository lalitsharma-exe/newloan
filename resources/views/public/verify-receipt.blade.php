<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt Verification | Prosperity Loans</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Helvetica', 'Arial', sans-serif; }
    body { background: #f8fafc; color: #334155; line-height: 1.5; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
    
    .verify-card {
        max-width: 450px;
        width: 100%;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.05);
        padding: 40px;
        text-align: center;
        border: 1px solid #e2e8f0;
    }
    
    .logo { height: 40px; margin-bottom: 30px; }
    
    .status-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 40px;
    }
    
    .status-success { background: #dcfce7; color: #15803d; }
    .status-error { background: #fee2e2; color: #b91c1c; }
    
    h1 { font-size: 20px; font-weight: 800; color: #0a192f; margin-bottom: 10px; }
    p { font-size: 14px; color: #64748b; margin-bottom: 30px; }
    
    .details-box {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 20px;
        text-align: left;
        margin-bottom: 30px;
    }
    
    .detail-item { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px; }
    .detail-label { color: #64748b; font-weight: 500; }
    .detail-value { color: #0a192f; font-weight: 700; }
    
    .btn {
        display: block;
        width: 100%;
        padding: 12px;
        background: #0a192f;
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
    }
    .btn:hover { background: #0f172a; }
    
    .footer { font-size: 11px; color: #94a3b8; margin-top: 30px; }
</style>
</head>
<body>

<div class="verify-card">
    <img src="{{ config('app.logo') }}" alt="Prosperity Loans" class="logo">
    
    @if($payment)
        <div class="status-icon status-success">✓</div>
        <h1>Receipt Verified</h1>
        <p>This is an authentic official payment receipt issued by Prosperity Loans Limited.</p>
        
        <div class="details-box">
            <div class="detail-item">
                <span class="detail-label">Reference</span>
                <span class="detail-value">{{ $payment->payment_reference }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Customer</span>
                <span class="detail-value">{{ $payment->user?->name }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Amount Paid</span>
                <span class="detail-value">M {{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Date</span>
                <span class="detail-value">{{ ($payment->verified_at ?? $payment->created_at)->format('d M Y') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Status</span>
                <span class="detail-value" style="color: #15803d;">FULLY VALID</span>
            </div>
        </div>
    @else
        <div class="status-icon status-error">✕</div>
        <h1>Invalid Receipt</h1>
        <p>We could not find a verified payment record for reference <strong>{{ $ref ?? 'N/A' }}</strong>.</p>
        <p style="margin-top: -20px;">If you believe this is an error, please contact our support team.</p>
    @endif
    
    <a href="https://prosperityloans.co.ls" class="btn">Return to Website</a>
    
    <div class="footer">
        &copy; {{ date('Y') }} Prosperity Loans Limited. All rights reserved.
    </div>
</div>

</body>
</html>
