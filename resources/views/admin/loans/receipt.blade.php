<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt {{ $payment->payment_reference }}</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',Arial,sans-serif}
  body{background:#f3f4f6;padding:40px 20px;color:#1e293b}
  .receipt-card {
    max-width: 540px; margin: 0 auto; background: #fff; border-radius: 16px;
    border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
    overflow: hidden;
  }
  .receipt-header {
    background: #f8fafc; padding: 40px 32px; border-bottom: 1px solid #f1f5f9;
    text-align: center;
  }
  .receipt-logo { height: 48px; width: auto; margin-bottom: 20px; }
  .receipt-status-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 16px; border-radius: 30px; font-size: 13px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;
  }
  .receipt-body { padding: 40px 32px; }
  .receipt-amount-box {
    text-align: center; margin-bottom: 32px; padding-bottom: 28px;
    border-bottom: 1px dashed #e2e8f0;
  }
  .receipt-amt-lbl { font-size: 14px; color: #64748b; margin-bottom: 6px; font-weight: 500; }
  .receipt-amt-val { font-size: 42px; font-weight: 800; color: #0f172a; letter-spacing: -1px; }
  
  .receipt-grid { display: grid; gap: 20px; }
  .receipt-row { display: flex; justify-content: space-between; align-items: flex-start; }
  .receipt-lbl { font-size: 14px; color: #64748b; font-weight: 500; }
  .receipt-val { font-size: 15px; font-weight: 600; color: #1e293b; text-align: right; }
  
  .receipt-footer {
    padding: 32px; background: #f8fafc; border-top: 1px solid #f1f5f9;
    text-align: center; font-size: 13px; color: #94a3b8; line-height: 1.5;
  }
  .print-actions { text-align: center; margin-top: 30px; }
  .btn-print {
    background: #1e293b; color: #fff; border: none;
    padding: 12px 32px; border-radius: 8px; font-weight: 600; font-size: 14px;
    cursor: pointer; transition: all 0.2s;
  }
  .btn-print:hover { background: #0f172a; }

  @media print {
    body { background: #fff; padding: 0; color: #000; }
    .receipt-card { border: none; box-shadow: none; max-width: 100%; }
    .print-actions { display: none; }
    .receipt-status-badge { border: 1px solid #16a34a !important; color: #16a34a !important; -webkit-print-color-adjust: exact; }
  }
</style>
</head>
<body>

<div class="receipt-card">
  <div class="receipt-header">
    <img src="{{ asset(config('app.logo')) }}" alt="MyLoan" class="receipt-logo">
    <div>
       <div class="receipt-status-badge">Official Payment Receipt</div>
    </div>
  </div>
  
  <div class="receipt-body">
    <div class="receipt-amount-box">
      <div class="receipt-amt-lbl">Amount Received</div>
      <div class="receipt-amt-val">M {{ number_format($payment->amount, 2) }}</div>
    </div>
    
    <div class="receipt-grid">
      <div class="receipt-row">
        <span class="receipt-lbl">Receipt No</span>
        <span class="receipt-val">{{ $payment->payment_reference }}</span>
      </div>
      <div class="receipt-row">
        <span class="receipt-lbl">Loan Reference</span>
        <span class="receipt-val">{{ $payment->loan?->loan_number }}</span>
      </div>
       <div class="receipt-row">
        <span class="receipt-lbl">Customer</span>
        <span class="receipt-val">{{ $payment->user?->name }}</span>
      </div>
      <div class="receipt-row">
        <span class="receipt-lbl">Payment Method</span>
        <span class="receipt-val">{{ ucwords(str_replace('_', ' ', $payment->method)) }}</span>
      </div>
      <div class="receipt-row">
        <span class="receipt-lbl">Date & Time</span>
        <span class="receipt-val">{{ $payment->verified_at ? $payment->verified_at->format('d M Y, H:i') : $payment->created_at->format('d M Y, H:i') }}</span>
      </div>
      @if($payment->notes)
      <div class="receipt-row">
        <span class="receipt-lbl">Notes</span>
        <span class="receipt-val">{{ $payment->notes }}</span>
      </div>
      @endif
      <div class="receipt-row" style="margin-top: 10px; padding-top: 15px; border-top: 1px solid #f1f5f9;">
        <span class="receipt-lbl">Remaining Balance</span>
        <span class="receipt-val" style="color: #4f46e5;">M {{ number_format($payment->loan?->outstanding_balance, 2) }}</span>
      </div>
    </div>
  </div>
  
  <div class="receipt-footer">
    <p>This is a computer-generated receipt.</p>
    <p><strong>MyLoan Limited</strong> · Lesotho</p>
    <p style="font-size: 11px; margin-top: 8px;">Generated on {{ now()->format('d M Y, H:i') }}</p>
  </div>
</div>

<div class="print-actions">
  <button onclick="window.print()" class="btn-print">Print Receipt</button>
</div>

</body>
</html>
