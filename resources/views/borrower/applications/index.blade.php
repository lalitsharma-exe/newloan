@extends('borrower.layouts.app')
@section('title','My Applications')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
  <div style="font-size:20px;font-weight:800">My Applications</div>
  <a href="{{ route('borrower.apply.start') }}" class="btn btn-p btn-sm"><i class="bi bi-plus-circle-fill"></i> New Application</a>
</div>
<div class="card">
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>App #</th><th>Product</th><th>Amount</th><th>Status</th><th>Date</th><th></th></tr></thead>
      <tbody>
        @forelse($applications as $app)
        <tr>
          <td style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p)">{{ $app->application_number }}</td>
          <td>{{ $app->loanProduct?->name ?? '—' }}</td>
          <td style="font-weight:700">M{{ number_format($app->requested_amount??0,0) }}</td>
          <td>@php $sc=['submitted'=>'bi','under_review'=>'bi','info_requested'=>'bw','approved'=>'bok','declined'=>'be','disbursed'=>'bp','draft'=>'bs','on_hold'=>'bw']; @endphp<span class="badge {{ $sc[$app->status]??'bs' }}">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span></td>
          <td style="font-size:12px;color:var(--muted)">{{ $app->created_at->format('d M Y') }}</td>
          <td><a href="{{ route('borrower.applications.show',$app) }}" class="btn btn-sm btn-o">View</a></td>
        </tr>
        @empty
        <tr><td colspan="6"><div style="text-align:center;padding:50px;color:var(--muted)"><i class="bi bi-inbox" style="font-size:44px;opacity:.25;display:block;margin-bottom:12px"></i><div style="font-weight:600;margin-bottom:10px">No applications yet</div><a href="{{ route('borrower.apply.start') }}" class="btn btn-p btn-sm">Apply Now</a></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($applications->hasPages())<div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $applications->links() }}</div>@endif
</div>
@endsection
