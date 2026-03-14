@extends('admin.layouts.app')
@section('title','User Applications')
@section('page-title','Applications')
@section('bc','<a href="'.route('admin.users.index').'">Users</a> / <a href="'.route('admin.users.show',$user).'">{{ $user->name }}</a> / Applications')
@section('content')

<div style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
  <div class="av av-lg">{{ strtoupper(substr($user->name,0,1)) }}</div>
  <div>
    <div style="font-size:18px;font-weight:700">{{ $user->name }}</div>
    <div class="muted">{{ $user->email }}</div>
  </div>
  <a href="{{ route('admin.users.show',$user) }}" class="btn btn-o btn-sm" style="margin-left:auto"><i class="bi bi-arrow-left"></i> Back to Profile</a>
</div>

<div class="card">
  <div class="card-hdr"><span class="card-title">All Applications ({{ $user->loanApplications->count() }})</span></div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>App #</th><th>Product</th><th>Amount Requested</th><th>Term</th><th>Submitted</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        @forelse($user->loanApplications as $app)
        @php
          $badges=['submitted'=>'bp','under_review'=>'bi','approved'=>'bok','declined'=>'be','disbursed'=>'bp','on_hold'=>'bw','info_requested'=>'bw','draft'=>'bs'];
          $badge=$badges[$app->status]??'bs';
        @endphp
        <tr>
          <td><span style="font-weight:700;color:var(--p);font-size:12px">{{ $app->application_number }}</span></td>
          <td style="font-size:13px">{{ $app->loanProduct->name ?? '—' }}</td>
          <td><strong>M{{ number_format($app->requested_amount,2) }}</strong></td>
          <td class="muted">{{ $app->requested_term ?? '—' }} months</td>
          <td class="muted">{{ $app->submitted_at?->format('d M Y') ?? $app->created_at->format('d M Y') }}</td>
          <td><span class="badge {{ $badge }}">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span></td>
          <td><a href="{{ route('admin.applications.show',$app) }}" class="btn btn-xs btn-p"><i class="bi bi-eye"></i> View</a></td>
        </tr>
        @empty
        <tr><td colspan="7"><div class="empty"><i class="bi bi-file-earmark-x"></i><p>No applications yet</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
