@extends('agent.layouts.app')
@section('page-title', 'My Applications')
@section('content')

<div class="card">
  <div class="card-hdr">
    <div class="card-title">Submitted Applications</div>
    <a href="{{ route('agent.applications.create') }}" class="btn btn-p btn-sm"><i class="bi bi-plus-circle"></i> New Application</a>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr><th>Ref</th><th>Client Name</th><th>Amount</th><th>Term</th><th>Status</th><th>Submitted</th><th></th></tr>
      </thead>
      <tbody>
        @forelse($applications as $app)
        <tr>
          <td style="font-weight:600;color:#0f766e">{{ $app->application_number }}</td>
          <td>{{ $app->applicant_name }}</td>
          <td>M{{ number_format($app->requested_amount, 2) }}</td>
          <td>{{ $app->requested_term }} mo</td>
          <td>
            @php
              $bc = match($app->status) {
                'approved','disbursed' => 'bok',
                'declined' => 'be',
                'under_review' => 'bp',
                'info_requested' => 'bw',
                default => 'bs',
              };
            @endphp
            <span class="badge {{ $bc }}">{{ ucfirst(str_replace('_', ' ', $app->status)) }}</span>
          </td>
          <td style="font-size:12px;color:#64748b">{{ $app->submitted_at?->format('d M Y') }}</td>
          <td><a href="{{ route('agent.applications.show', $app) }}" class="btn btn-o btn-xs">View</a></td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:#64748b;padding:30px">No applications yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($applications->hasPages())
  <div style="padding:16px 22px;border-top:1px solid var(--border)">{{ $applications->links() }}</div>
  @endif
</div>

@endsection
