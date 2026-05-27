@extends('agent.layouts.app')
@section('page-title', 'Commission Tracker')
@section('content')

<div class="g2" style="margin-bottom:22px">
  <div class="sc">
    <div class="si ok"><i class="bi bi-wallet-fill"></i></div>
    <div><div class="sv">M{{ number_format($totalEarned, 2) }}</div><div class="sl">Total Earned</div></div>
  </div>
  <div class="sc">
    <div class="si w"><i class="bi bi-hourglass-split"></i></div>
    <div><div class="sv">M{{ number_format($pendingAmount, 2) }}</div><div class="sl">Pending (awaiting first repayment)</div></div>
  </div>
</div>

<div style="background:rgba(45,212,191,.08);border:1px solid rgba(45,212,191,.2);border-radius:12px;padding:14px 18px;margin-bottom:22px;font-size:13px;color:#0f766e">
  <i class="bi bi-info-circle"></i> You earn <strong>M50</strong> for each application where the client makes their first loan repayment. Commission is paid within 48 hours to your registered payout account.
</div>

<div class="card">
  <div class="card-hdr"><div class="card-title">Commission Log</div></div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr><th>Client</th><th>Loan Amount</th><th>Submitted</th><th>Commission</th><th>Status</th></tr>
      </thead>
      <tbody>
        @forelse($commissionLog as $entry)
        <tr>
          <td>{{ $entry->client_name }}</td>
          <td>M{{ number_format($entry->loan_amount, 2) }}</td>
          <td style="font-size:12px;color:#64748b">{{ $entry->submitted_at?->format('d M Y') }}</td>
          <td style="font-weight:700;color:#0f766e">{{ $entry->commission > 0 ? 'M50.00' : '—' }}</td>
          <td>
            @php
              $sc = match($entry->status) {
                'earned' => 'bok',
                'awaiting_first_payment' => 'bw',
                default => 'bs',
              };
              $sl = match($entry->status) {
                'earned' => 'Earned',
                'awaiting_first_payment' => 'Awaiting 1st Payment',
                default => 'Pending',
              };
            @endphp
            <span class="badge {{ $sc }}">{{ $sl }}</span>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:#64748b;padding:30px">No commission entries yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($commissionLog->hasPages())
  <div style="padding:16px 22px;border-top:1px solid var(--border)">{{ $commissionLog->links() }}</div>
  @endif
</div>
@endsection
