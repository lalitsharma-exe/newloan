@extends('admin.layouts.app')
@section('title','Monthly Submissions')
@section('page-title','Monthly Bureau Submissions')
@section('bc','<a href="'.route('admin.credit.index').'">Credit Bureau</a> / Monthly Submissions')
@section('content')

<div class="card">
  <div class="card-hdr">
    <span class="card-title">Monthly Submissions</span>
    <div style="display:flex;gap:10px">
      <form method="POST" action="{{ route('admin.credit.submit-monthly') }}">
        @csrf
        <button type="submit" class="btn btn-p btn-sm" onclick="return confirm('Submit this month\'s credit data to the bureau?')">
          <i class="bi bi-cloud-upload"></i> Submit This Month
        </button>
      </form>
    </div>
  </div>
  <div style="padding:40px;text-align:center;color:var(--muted)">
    <i class="bi bi-calendar-check" style="font-size:48px;opacity:.3;display:block;margin-bottom:12px"></i>
    <div style="font-size:15px;font-weight:600;margin-bottom:8px">Monthly Submission Tracking</div>
    <div style="font-size:13px">Monthly credit bureau submissions will be listed here once submitted.<br>Use the "Submit This Month" button to send current month's data.</div>
  </div>
</div>
@endsection

