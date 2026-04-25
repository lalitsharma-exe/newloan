@extends('borrower.layouts.app')
@section('title','Payment Receipt')

@push('styles')
<style>
  @media print {
    .topnav, .bottomnav, .btn-print-wrap { display: none !important; }
    body { background: #fff !important; padding: 0 !important; }
    .wrap { padding: 0 !important; max-width: none !important; }
    .receipt-card { border: none !important; box-shadow: none !important; padding: 10px !important; }
    .receipt-status-badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
  .receipt-card {
    max-width: 540px; margin: 0 auto; background: #fff; border-radius: 16px;
    border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
    overflow: hidden;
  }
  .receipt-header {
    background: #f8fafc; padding: 32px; border-bottom: 1px solid #f1f5f9;
    text-align: center;
  }
  .receipt-logo { height: 42px; width: auto; margin-bottom: 16px; }
  .receipt-status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;
  }
  .receipt-body { padding: 32px; }
  .receipt-amount-box {
    text-align: center; margin-bottom: 32px; padding-bottom: 24px;
    border-bottom: 1px dashed #e2e8f0;
  }
  .receipt-amt-lbl { font-size: 13px; color: #64748b; margin-bottom: 4px; font-weight: 500; }
  .receipt-amt-val { font-size: 38px; font-weight: 800; color: #1e293b; letter-spacing: -1px; }
  
  .receipt-grid { display: grid; gap: 18px; }
  .receipt-row { display: flex; justify-content: space-between; align-items: flex-start; }
  .receipt-lbl { font-size: 13px; color: #64748b; font-weight: 500; }
  .receipt-val { font-size: 14px; font-weight: 600; color: #1e293b; text-align: right; }
  
  .receipt-footer {
    padding: 24px 32px; background: #f8fafc; border-top: 1px solid #f1f5f9;
    text-align: center; font-size: 12px; color: #94a3b8;
  }
  .btn-print {
    background: #fff; border: 1px solid #e2e8f0; color: #475569;
    padding: 8px 20px; border-radius: 8px; font-weight: 600; font-size: 13px;
    cursor: pointer; transition: all 0.2s;
  }
  .btn-print:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
</style>
@endpush

@section('content')
<div class="receipt-card">
  <div class="receipt-header">
    <img src="{{ asset(config('app.logo')) }}" alt="MyLoan" class="receipt-logo">
    <div>
       <div class="receipt-status-badge">
         <i class="bi bi-check-circle-fill"></i> Verified Payment
       </div>
    </div>
  </div>
  
  <div class="receipt-body">
    <div class="receipt-amount-box">
      <div class="receipt-amt-lbl">Total Amount Paid</div>
      <div class="receipt-amt-val">M {{ number_format($payment->amount, 2) }}</div>
    </div>
    
    <div class="receipt-grid">
      <div class="receipt-row">
        <span class="receipt-lbl">Payment Reference</span>
        <span class="receipt-val">{{ $payment->payment_reference }}</span>
      </div>
      <div class="receipt-row">
        <span class="receipt-lbl">Loan Number</span>
        <span class="receipt-val">{{ $payment->loan?->loan_number }}</span>
      </div>
      <div class="receipt-row">
        <span class="receipt-lbl">Payment Method</span>
        <span class="receipt-val">{{ ucwords(str_replace('_', ' ', $payment->method)) }}</span>
      </div>
      <div class="receipt-row">
        <span class="receipt-lbl">Transaction Date</span>
        <span class="receipt-val">{{ $payment->verified_at ? $payment->verified_at->format('d M Y, H:i') : $payment->created_at->format('d M Y, H:i') }}</span>
      </div>
      <div class="receipt-row">
        <span class="receipt-lbl">Customer Name</span>
        <span class="receipt-val">{{ auth('borrower')->user()->name }}</span>
      </div>
      <div class="receipt-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9;">
        <span class="receipt-lbl">Remaining Loan Balance</span>
        <span class="receipt-val" style="color: #4f46e5;">M {{ number_format($payment->loan?->outstanding_balance, 2) }}</span>
      </div>
    </div>
  </div>
  
  <div class="receipt-footer">
    <p>Thank you for using MyLoan Services.</p>
    <p style="margin-top: 4px;">MyLoan Limited · Lesotho</p>
  </div>
</div>

<div class="btn-print-wrap" style="text-align:center; margin-top: 24px;">
  <button onclick="window.print()" class="btn-print">
    <i class="bi bi-printer"></i> Print Receipt
  </button>
  <div style="margin-top: 16px;">
    <a href="{{ route('borrower.payments.index') }}" style="font-size: 13px; color: #64748b; text-decoration: none;">
      <i class="bi bi-arrow-left"></i> Back to Payments
    </a>
  </div>
</div>
@endsection
