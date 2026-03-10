@extends('admin.layouts.app')
@section('title','Credit Report')
@section('page-title','Credit Report')
@section('content')
<div style="max-width:700px">
<div class="card">
    <div class="card-header"><span class="card-title">Credit Report — {{ $report->user->name }}</span></div>
    <div class="card-body">
        <div style="text-align:center;padding:24px 0;margin-bottom:24px;background:#f8fafc;border-radius:12px">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:.05em">Credit Score</div>
            <div style="font-size:64px;font-weight:800;color:{{ $report->credit_score>=700?'#10b981':($report->credit_score>=500?'#f59e0b':'#ef4444') }}">{{ $report->credit_score??'N/A' }}</div>
            <div style="font-size:13px;color:#94a3b8">via {{ $report->provider }} · {{ $report->check_type }} check</div>
        </div>
        <div class="info-grid">
            @foreach(['User'=>$report->user->name,'National ID'=>$report->national_id,'Provider'=>$report->provider,'Type'=>ucfirst($report->check_type),'Status'=>ucfirst($report->status),'Retrieved'=>$report->retrieved_at?->format('d M Y H:i')] as $l=>$v)
            <div class="info-item"><div class="info-label">{{ $l }}</div><div class="info-value">{{ $v??'—' }}</div></div>
            @endforeach
        </div>
        @if($report->report_data)
        <div style="margin-top:20px;padding:16px;background:#f8fafc;border-radius:12px">
            <div style="font-weight:700;margin-bottom:12px">Report Summary</div>
            <pre style="font-size:12px;color:#334155;white-space:pre-wrap">{{ json_encode($report->report_data, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
    </div>
</div>
</div>
@endsection
