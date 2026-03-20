@extends('borrower.layouts.app')
@section('title','Application Submitted')
@section('content')
<div style="text-align:center;padding:50px 20px">
  <div style="width:80px;height:80px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px">✅</div>
  <div style="font-size:26px;font-weight:900;margin-bottom:10px">Application Submitted!</div>
  <div style="font-size:15px;color:var(--muted);max-width:480px;margin:0 auto 8px;line-height:1.7">Your application <strong>{{ $application->application_number }}</strong> has been received. We will review it and get back to you shortly.</div>
  <div style="font-size:13px;color:var(--muted);margin-bottom:32px">You will be notified via SMS when there is an update.</div>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a href="{{ route('borrower.applications.show',$application) }}" class="btn btn-o">Track Application</a>
    <a href="{{ route('borrower.dashboard') }}" class="btn btn-p">Back to Dashboard</a>
  </div>
</div>
@endsection
