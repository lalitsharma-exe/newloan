@extends('admin.layouts.app')
@section('title', 'MyBill Loan: ' . $loan->loan_number)
@section('bc')
<a href="{{ route('admin.mybill.dashboard') }}">MyBill</a> / <a href="{{ route('admin.mybill.loans') }}">Loans</a> / {{ $loan->loan_number }}
@endsection
@section('content')

@if(session('success'))<div style="padding:12px 16px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:8px;margin-bottom:20px;font-size:14px"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
  
  {{-- Left Column --}}
  <div>
    {{-- Main Info --}}
    <div class="card mb6" style="padding:0;overflow:hidden">
      <div style="padding:20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:flex-start;background:#f8fafc">
        <div>
          <div style="font-size:12px;color:#64748b;font-weight:600;text-transform:uppercase;margin-bottom:4px">Loan Status</div>
          <div style="display:flex;align-items:center;gap:12px">
            <h2 style="font-size:24px;font-weight:800;margin:0;color:var(--ink)">{{ $loan->loan_number }}</h2>
            <span style="font-size:11px;padding:4px 10px;border-radius:6px;font-weight:700;background:{{ $loan->status === 'settled' ? '#ecfdf5' : ($loan->status === 'active' ? '#fffbeb' : '#fef2f2') }};color:{{ $loan->status === 'settled' ? '#059669' : ($loan->status === 'active' ? '#d97706' : '#dc2626') }}">
              {{ strtoupper($loan->status) }}
            </span>
          </div>
          <div style="font-size:13px;color:#64748b;margin-top:8px">Created: {{ $loan->created_at->format('d M Y, H:i') }}</div>
        </div>
        <div style="text-align:right">
          <div style="font-size:12px;color:#64748b;font-weight:600;text-transform:uppercase;margin-bottom:4px">Category</div>
          <span style="padding:4px 12px;border-radius:20px;font-size:13px;font-weight:600;background:var(--blue);color:#fff">
            <i class="bi bi-tag-fill"></i> {{ ucfirst($loan->bill_category) }}
          </span>
        </div>
      </div>

      <div style="padding:20px">
        <h3 style="font-size:14px;font-weight:700;margin-bottom:16px;color:#475569">Financial Breakdown</h3>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px">
          <div style="background:#f1f5f9;border-radius:10px;padding:16px">
            <div style="font-size:12px;color:#64748b;font-weight:600;margin-bottom:4px">Bill Value Disbursed</div>
            <div style="font-size:20px;font-weight:800">M {{ number_format($loan->bill_value, 2) }}</div>
          </div>
          <div style="background:#f1f5f9;border-radius:10px;padding:16px">
            <div style="font-size:12px;color:#64748b;font-weight:600;margin-bottom:4px">Total Loan (w/ Fee)</div>
            <div style="font-size:20px;font-weight:800">M {{ number_format($loan->loan_amount, 2) }}</div>
          </div>
          <div style="background:{{ $loan->outstanding_amount > 0 ? '#fffbeb' : '#ecfdf5' }};border-radius:10px;padding:16px">
            <div style="font-size:12px;color:{{ $loan->outstanding_amount > 0 ? '#92400e' : '#065f46' }};font-weight:600;margin-bottom:4px">Outstanding Balance</div>
            <div style="font-size:20px;font-weight:800;color:{{ $loan->outstanding_amount > 0 ? '#b45309' : '#059669' }}">M {{ number_format($loan->outstanding_amount, 2) }}</div>
          </div>
        </div>

        <table style="width:100%;font-size:14px;border-collapse:collapse">
          <tr style="border-bottom:1px solid #e2e8f0"><td style="padding:10px 0;color:#64748b">Tier Selection</td><td style="padding:10px 0;text-align:right;font-weight:600">{{ $loan->tier === '30' ? 'Standard (30%)' : 'No Upfront (40%)' }}</td></tr>
          <tr style="border-bottom:1px solid #e2e8f0"><td style="padding:10px 0;color:#64748b">Upfront Amount Paid</td><td style="padding:10px 0;text-align:right;font-weight:600;color:#059669">M {{ number_format($loan->upfront_amount, 2) }}</td></tr>
          <tr style="border-bottom:1px solid #e2e8f0"><td style="padding:10px 0;color:#64748b">Expected Payday Deduction</td><td style="padding:10px 0;text-align:right;font-weight:600;color:#dc2626">M {{ number_format($loan->payday_amount, 2) }}</td></tr>
          <tr><td style="padding:10px 0;color:#64748b">Total Settled So Far</td><td style="padding:10px 0;text-align:right;font-weight:600">M {{ number_format($loan->settled_amount, 2) }}</td></tr>
        </table>
      </div>
    </div>

    {{-- Repayment History --}}
    <div class="card" style="padding:0">
      <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
        <h3 style="font-size:15px;font-weight:700;margin:0">Payment History</h3>
      </div>
      @if($loan->repayments->isEmpty())
        <div style="padding:30px;text-align:center;color:#64748b;font-size:14px">No payments recorded yet.</div>
      @else
        <table class="dt" style="margin:0">
          <thead>
            <tr>
              <th>Date</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Ref</th>
            </tr>
          </thead>
          <tbody>
            @foreach($loan->repayments as $rep)
            <tr>
              <td style="font-size:13px">{{ $rep->processed_at?->format('d M y, H:i') ?? $rep->created_at->format('d M y, H:i') }}</td>
              <td style="font-size:13px;font-weight:600;color:var(--blue)">{{ ucfirst($rep->deduction_type) }}</td>
              <td style="font-size:13px;font-weight:600;color:#059669">M {{ number_format($rep->amount, 2) }}</td>
              <td><span style="font-size:10px;padding:2px 6px;border-radius:4px;background:#ecfdf5;color:#059669;font-weight:600">{{ ucfirst($rep->status) }}</span></td>
              <td style="font-size:11px;font-family:monospace;color:#64748b">{{ $rep->salary_credit_ref ?? '-' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>

  {{-- Right Column --}}
  <div>
    {{-- Client Details --}}
    <div class="card mb6" style="padding:20px">
      <h3 style="font-size:14px;font-weight:700;margin-bottom:16px;color:#475569;border-bottom:1px solid #e2e8f0;padding-bottom:10px">Client Details</h3>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
        <div style="width:48px;height:48px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#64748b">
          {{ substr($loan->user->name ?? 'A', 0, 1) }}
        </div>
        <div>
          <div style="font-weight:700;font-size:15px"><a href="{{ route('admin.users.show', $loan->user_id) }}" style="color:var(--ink);text-decoration:none">{{ $loan->user->name ?? 'Unknown' }}</a></div>
          <div style="font-size:12px;color:#64748b">{{ $loan->user->phone ?? 'No phone' }}</div>
        </div>
      </div>
      <div style="font-size:13px;color:#64748b;display:flex;justify-content:space-between">
        <span>Email:</span> <span style="font-weight:500;color:var(--ink)">{{ $loan->user->email ?? '-' }}</span>
      </div>
    </div>

    {{-- Provider Details --}}
    <div class="card" style="padding:20px">
      <h3 style="font-size:14px;font-weight:700;margin-bottom:16px;color:#475569;border-bottom:1px solid #e2e8f0;padding-bottom:10px">Provider Details</h3>
      
      @if($loan->meter_number)
      <div style="margin-bottom:12px">
        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600">Meter Number</div>
        <div style="font-size:14px;font-weight:600;font-family:monospace">{{ $loan->meter_number }}</div>
      </div>
      @endif

      @if($loan->phone_number)
      <div style="margin-bottom:12px">
        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600">Target Phone</div>
        <div style="font-size:14px;font-weight:600">{{ $loan->phone_number }} ({{ $loan->airtime_type }})</div>
      </div>
      @endif

      @if($loan->policy_number)
      <div style="margin-bottom:12px">
        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600">Policy Number</div>
        <div style="font-size:14px;font-weight:600">{{ $loan->policy_number }}</div>
      </div>
      @endif

      @if($loan->ticket_reference)
      <div style="margin-bottom:12px">
        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600">Ticket Ref</div>
        <div style="font-size:14px;font-weight:700;color:var(--blue);font-family:monospace">{{ $loan->ticket_reference }}</div>
      </div>
      @endif

      <div style="margin-bottom:12px">
        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600">CPay Txn ID</div>
        <div style="font-size:13px;font-family:monospace">{{ $loan->cpay_txn_id ?? 'N/A' }}</div>
      </div>

      @if($loan->failure_reason)
      <div style="margin-top:16px;padding:12px;background:#fef2f2;border-radius:8px;border:1px solid #fecaca">
        <div style="font-size:11px;color:#dc2626;font-weight:700;margin-bottom:4px">FAILURE REASON</div>
        <div style="font-size:13px;color:#991b1b">{{ $loan->failure_reason }}</div>
      </div>
      @endif
      
      <div style="margin-top:16px">
        <button type="button" onclick="document.getElementById('rawJson').style.display='block';this.style.display='none'" class="btn btn-o" style="width:100%;font-size:12px;justify-content:center">View Raw CPay Response</button>
        <pre id="rawJson" style="display:none;background:#1e293b;color:#a5b4fc;padding:12px;border-radius:8px;font-size:11px;overflow-x:auto;max-height:300px;overflow-y:auto">{{ json_encode(json_decode($loan->provider_response), JSON_PRETTY_PRINT) }}</pre>
      </div>
    </div>
  </div>
</div>

@endsection
