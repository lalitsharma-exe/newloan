@extends('borrower.layouts.app')
@section('title','Payment Receipt')

@push('styles')
<style>
  @media print {
    .topnav, .bottomnav, .btn-print-wrap { display: none !important; }
    body { background: #fff !important; padding: 0 !important; }
    .wrap { padding: 0 !important; max-width: none !important; margin: 0 !important; }
    .receipt-container { border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; width: 100% !important; max-width: none !important; }
  }

  .receipt-container {
    max-width: 800px;
    margin: 40px auto;
    background: #fff;
    padding: 60px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: #1e293b;
  }

  .receipt-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 60px;
  }

  .company-info h2 {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: #0f172a;
  }

  .company-info p {
    margin: 2px 0;
    color: #64748b;
    font-size: 14px;
  }

  .receipt-logo-wrap {
    text-align: right;
  }

  .receipt-logo {
    max-height: 60px;
    margin-bottom: 10px;
  }

  .receipt-title-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 40px;
  }

  .billed-to h3 {
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #2563eb;
    margin-bottom: 12px;
  }

  .billed-to p {
    margin: 4px 0;
    font-weight: 600;
    font-size: 16px;
  }

  .billed-to span {
    display: block;
    color: #64748b;
    font-size: 14px;
    font-weight: 400;
    margin-top: 2px;
  }

  .receipt-meta {
    text-align: right;
  }

  .receipt-meta h1 {
    font-size: 48px;
    font-weight: 900;
    color: #1e40af;
    margin: 0 0 20px 0;
    letter-spacing: -0.02em;
    text-transform: uppercase;
  }

  .meta-grid {
    display: grid;
    grid-template-columns: auto 120px;
    gap: 8px 20px;
    text-align: right;
  }

  .meta-lbl {
    font-weight: 800;
    font-size: 13px;
    color: #1e40af;
    text-transform: uppercase;
  }

  .meta-val {
    font-weight: 500;
    font-size: 14px;
    color: #334155;
  }

  .receipt-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
  }

  .receipt-table th {
    background: #1e40af;
    color: #fff;
    text-align: left;
    padding: 12px 15px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
  }

  .receipt-table th:last-child,
  .receipt-table td:last-child,
  .receipt-table th:nth-child(3),
  .receipt-table td:nth-child(3) {
    text-align: right;
  }

  .receipt-table td {
    padding: 15px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 14px;
  }

  .totals-section {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
  }

  .total-row {
    display: grid;
    grid-template-columns: 150px 150px;
    gap: 20px;
    text-align: right;
  }

  .total-lbl {
    font-size: 14px;
    color: #64748b;
    font-weight: 600;
  }

  .total-val {
    font-size: 14px;
    color: #1e293b;
    font-weight: 600;
  }

  .final-total {
    border-top: 2px solid #1e40af;
    border-bottom: 2px solid #1e40af;
    padding: 12px 0;
    margin-top: 10px;
  }

  .final-total .total-lbl {
    color: #1e40af;
    font-weight: 800;
    font-size: 15px;
  }

  .final-total .total-val {
    color: #1e40af;
    font-weight: 800;
    font-size: 18px;
  }

  .receipt-notes {
    margin-top: 80px;
    border-top: 1px solid #f1f5f9;
    padding-top: 30px;
  }

  .notes-title {
    font-size: 14px;
    font-weight: 800;
    color: #1e40af;
    margin-bottom: 12px;
  }

  .notes-content {
    font-size: 13px;
    color: #64748b;
    line-height: 1.6;
  }

  .notes-footer {
    margin-top: 24px;
    font-size: 13px;
    color: #94a3b8;
  }

  .btn-print-wrap {
    max-width: 800px;
    margin: 0 auto 40px;
    text-align: center;
  }

  .btn-print {
    background: #1e40af;
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(30,64,175, 0.2);
    transition: all 0.2s;
  }

  .btn-print:hover {
    background: #1e3a8a;
    transform: translateY(-1px);
    box-shadow: 0 6px 12px -2px rgba(30,64,175, 0.3);
  }
</style>
@endpush

@section('content')
<div class="receipt-container">
  <div class="receipt-header">
    <div class="company-info">
      <h2>MyLoan Limited</h2>
      <p>Kingsway Road, Maseru</p>
      <p>Maseru, Lesotho 100</p>
    </div>
    <div class="receipt-logo-wrap">
      <img src="{{ asset(config('app.logo')) }}" alt="Logo" class="receipt-logo">
    </div>
  </div>

  <div class="receipt-title-section">
    <div class="billed-to">
      <h3>Billed To</h3>
      <p>{{ auth('borrower')->user()->name }}</p>
      <span>{{ auth('borrower')->user()->phone }}</span>
      @if(auth('borrower')->user()->email)
        <span>{{ auth('borrower')->user()->email }}</span>
      @endif
    </div>
    <div class="receipt-meta">
      <h1>RECEIPT</h1>
      <div class="meta-grid">
        <div class="meta-lbl">Receipt #</div>
        <div class="meta-val">{{ $payment->payment_reference }}</div>
        <div class="meta-lbl">Receipt Date</div>
        <div class="meta-val">{{ $payment->verified_at ? $payment->verified_at->format('d-m-Y') : $payment->created_at->format('d-m-Y') }}</div>
      </div>
    </div>
  </div>

  <table class="receipt-table">
    <thead>
      <tr>
        <th style="width: 60px;">QTY</th>
        <th>Description</th>
        <th style="width: 120px;">Unit Price</th>
        <th style="width: 120px;">Amount</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>
            <strong>Loan Repayment</strong><br>
            <span style="font-size: 12px; color: #64748b;">Loan #{{ $payment->loan?->loan_number }} - {{ $payment->loan?->loanProduct?->name }}</span>
        </td>
        <td>{{ number_format($payment->amount, 2) }}</td>
        <td>{{ number_format($payment->amount, 2) }}</td>
      </tr>
    </tbody>
  </table>

  <div class="totals-section">
    <div class="total-row">
      <div class="total-lbl">Subtotal</div>
      <div class="total-val">M {{ number_format($payment->amount, 2) }}</div>
    </div>
    <div class="total-row">
      <div class="total-lbl">Sales Tax (0%)</div>
      <div class="total-val">M 0.00</div>
    </div>
    <div class="total-row final-total">
      <div class="total-lbl">Total (LSL)</div>
      <div class="total-val">M {{ number_format($payment->amount, 2) }}</div>
    </div>
  </div>

  <div class="receipt-notes">
    <div class="notes-title">Notes</div>
    <div class="notes-content">
      Thank you for your payment! This receipt confirms that the amount stated above has been applied to your loan balance. 
      Please retain this receipt for your records.
    </div>
    <div class="notes-footer">
      For questions or support, contact us at support@myloan.ls or call +266 2231 1234
    </div>
  </div>
</div>

<div class="btn-print-wrap">
  <button onclick="window.print()" class="btn-print">
    <i class="bi bi-printer"></i> Print Receipt
  </button>
  <div style="margin-top: 16px;">
    <a href="{{ route('borrower.payments.index') }}" style="font-size: 13px; color: #64748b; text-decoration: none;">
      <i class="bi bi-arrow-left"></i> Back to Payment History
    </a>
  </div>
</div>
@endsection
