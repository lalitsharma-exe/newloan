@extends('agent.layouts.app')
@section('page-title', 'Dashboard')
@section('content')

{{-- Commission Banner --}}
<div style="background:linear-gradient(135deg,#0d1b3e,#2b4bad);border-radius:16px;padding:22px 26px;margin-bottom:22px;color:#fff;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
  <div style="width:52px;height:52px;background:rgba(255,255,255,.15);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0">
    <i class="bi bi-wallet2"></i>
  </div>
  <div style="flex:1;min-width:200px">
    <div style="font-size:13px;opacity:.7">Your Commission</div>
    <div style="font-size:28px;font-weight:800;line-height:1.2">M{{ number_format($stats['total_earned'], 2) }}</div>
    <div style="font-size:12px;opacity:.6;margin-top:2px">Pending: M{{ number_format($stats['pending_earnings'], 2) }}</div>
  </div>
  <div style="font-size:12px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:10px 14px;max-width:260px">
    <i class="bi bi-info-circle"></i> You earn <strong>M50</strong> per application when the client makes their first repayment.
  </div>
</div>

{{-- Stats Grid --}}
<div class="g4" style="margin-bottom:22px">
  <div class="sc">
    <div class="si p"><i class="bi bi-send-fill"></i></div>
    <div><div class="sv">{{ $stats['total_submitted'] }}</div><div class="sl">Total Submitted</div></div>
  </div>
  <div class="sc">
    <div class="si i"><i class="bi bi-hourglass-split"></i></div>
    <div><div class="sv">{{ $stats['under_review'] }}</div><div class="sl">Under Review</div></div>
  </div>
  <div class="sc">
    <div class="si ok"><i class="bi bi-check-circle-fill"></i></div>
    <div><div class="sv">{{ $stats['approved'] }}</div><div class="sl">Approved</div></div>
  </div>
  <div class="sc">
    <div class="si s"><i class="bi bi-cash-coin"></i></div>
    <div><div class="sv">{{ $stats['disbursed'] }}</div><div class="sl">Disbursed</div></div>
  </div>
</div>

{{-- Quick Action --}}
<div style="margin-bottom:22px">
  <a href="{{ route('agent.applications.create') }}" class="btn btn-p" style="font-size:15px;padding:14px 28px;border-radius:12px">
    <i class="bi bi-plus-circle-fill"></i> New Client Application
  </a>
</div>

{{-- How it Works --}}
<div class="how-it-works">
  <div style="font-size:15px;font-weight:700;margin-bottom:14px"><i class="bi bi-lightbulb"></i> How it Works</div>
  <div class="hiw-step">
    <div class="hiw-num">1</div>
    <div class="hiw-text"><strong>Capture Client Details</strong><span>Fill in client's personal info, employment, and loan request.</span></div>
  </div>
  <div class="hiw-step">
    <div class="hiw-num">2</div>
    <div class="hiw-text"><strong>Upload Documents</strong><span>Take photos of client's national ID, payslip, and a selfie holding their ID.</span></div>
  </div>
  <div class="hiw-step">
    <div class="hiw-num">3</div>
    <div class="hiw-text"><strong>Submit Application</strong><span>MyLoan reviews the application and makes the lending decision.</span></div>
  </div>
  <div class="hiw-step">
    <div class="hiw-num">4</div>
    <div class="hiw-text"><strong>Earn Commission</strong><span>You earn M50 when the client makes their first repayment.</span></div>
  </div>
</div>

{{-- Recent Applications --}}
<div class="card">
  <div class="card-hdr">
    <div class="card-title">Recent Applications</div>
    <a href="{{ route('agent.applications.index') }}" class="btn btn-o btn-sm">View All</a>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr><th>Ref</th><th>Client</th><th>Amount</th><th>Status</th><th>Date</th></tr>
      </thead>
      <tbody>
        @forelse($recentApps as $app)
        <tr>
          <td><a href="{{ route('agent.applications.show', $app) }}" style="color:#2b4bad;font-weight:600;text-decoration:none">{{ $app->application_number }}</a></td>
          <td>{{ $app->applicant_name }}</td>
          <td>M{{ number_format($app->requested_amount, 2) }}</td>
          <td>
            @php
              $badgeClass = match($app->status) {
                'approved','disbursed' => 'bok',
                'declined' => 'be',
                'under_review' => 'bp',
                'info_requested' => 'bw',
                default => 'bs',
              };
            @endphp
            <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $app->status)) }}</span>
          </td>
          <td style="color:#64748b;font-size:12px">{{ $app->submitted_at?->format('d M Y') ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:#64748b;padding:30px">No applications yet. <a href="{{ route('agent.applications.create') }}" style="color:#2b4bad">Submit your first one →</a></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
