@extends('admin.layouts.app')
@section('title', $campaign->name)
@section('page-title', 'Campaign Details')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Dashboard</a> / <a href="{{ route('admin.bulk-sms.index') }}">Bulk SMS</a> / {{ Str::limit($campaign->name, 30) }}
@endsection

@section('content')
@push('styles')
<style>
.camp-stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 22px;
}
.camp-progress-bar {
    height: 12px;
    background: #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 6px;
}
.camp-progress-fill {
    height: 100%;
    border-radius: 6px;
    transition: width .5s ease;
}
.camp-message-box {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 18px 20px;
    font-size: 14px;
    line-height: 1.7;
    color: var(--dark);
    white-space: pre-wrap;
    word-break: break-word;
}
.live-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--ok);
    margin-right: 6px;
    animation: blink 1.2s infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

@media (max-width: 768px) {
    .camp-stat-row { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

{{-- ── CAMPAIGN HEADER ─────────────────────────────────── --}}
<div class="card" style="margin-bottom:18px;">
    <div class="card-body" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
        <div>
            <h4 style="font-weight:700; margin-bottom:4px;">{{ $campaign->name }}</h4>
            <div class="muted" style="font-size:12.5px;">
                Created by {{ $campaign->creator->name ?? 'System' }} · {{ $campaign->created_at->format('d M Y, H:i') }}
                · Audience: <strong>{{ ucwords(str_replace('_', ' ', $campaign->audience)) }}</strong>
            </div>
        </div>
        <div>
            @switch($campaign->status)
                @case('draft')      <span class="badge bs" style="font-size:13px; padding:6px 14px;">Draft</span>       @break
                @case('queued')     <span class="badge bi" style="font-size:13px; padding:6px 14px;">Queued</span>      @break
                @case('processing') <span class="badge bw" style="font-size:13px; padding:6px 14px;"><span class="live-dot"></span>Processing</span> @break
                @case('completed')  <span class="badge bok" style="font-size:13px; padding:6px 14px;">Completed</span>  @break
                @case('failed')     <span class="badge be" style="font-size:13px; padding:6px 14px;">Failed</span>      @break
            @endswitch
        </div>
    </div>
</div>

{{-- ── STATS ───────────────────────────────────────────── --}}
<div class="camp-stat-row" id="statsRow">
    <div class="sc">
        <div class="si p"><i class="bi bi-people-fill"></i></div>
        <div>
            <div class="sv" id="statTotal">{{ number_format($campaign->total_recipients) }}</div>
            <div class="sl">Total Recipients</div>
        </div>
    </div>
    <div class="sc">
        <div class="si ok"><i class="bi bi-check-circle-fill"></i></div>
        <div>
            <div class="sv" id="statSent">{{ number_format($campaign->sent_count) }}</div>
            <div class="sl">Sent</div>
        </div>
    </div>
    <div class="sc">
        <div class="si e"><i class="bi bi-x-circle-fill"></i></div>
        <div>
            <div class="sv" id="statFailed">{{ number_format($campaign->failed_count) }}</div>
            <div class="sl">Failed</div>
        </div>
    </div>
    <div class="sc">
        <div class="si w"><i class="bi bi-clock-fill"></i></div>
        <div>
            <div class="sv" id="statPending">{{ number_format($campaign->total_recipients - $campaign->sent_count - $campaign->failed_count) }}</div>
            <div class="sl">Pending</div>
        </div>
    </div>
</div>

{{-- ── PROGRESS BAR ────────────────────────────────────── --}}
<div class="card" style="margin-bottom:18px;">
    <div class="card-body">
        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
            <span style="font-weight:600; font-size:13px;">Progress</span>
            <span style="font-weight:700; font-size:13px;" id="progressPct">{{ $campaign->progress }}%</span>
        </div>
        <div class="camp-progress-bar">
            <div class="camp-progress-fill" id="progressFill"
                style="width:{{ $campaign->progress }}%; background:{{ $campaign->status === 'completed' ? 'var(--ok)' : 'linear-gradient(90deg, var(--pl), var(--blue2))' }};"></div>
        </div>
        <div class="muted" style="font-size:11.5px;" id="progressDesc">
            @if($campaign->status === 'completed')
                Completed at {{ $campaign->completed_at?->format('d M Y, H:i') ?? '—' }}
            @elseif(in_array($campaign->status, ['queued', 'processing']))
                <span class="live-dot"></span> Campaign is processing in the background...
            @else
                —
            @endif
        </div>
    </div>
</div>

{{-- ── MESSAGE ─────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:18px;">
    <div class="card-hdr"><span class="card-title">Message Content</span></div>
    <div class="card-body">
        <div class="camp-message-box">{{ $campaign->message }}</div>
        <div class="muted" style="font-size:11.5px; margin-top:8px;">
            {{ strlen($campaign->message) }} characters · {{ ceil(strlen($campaign->message) / 160) }} SMS segment(s)
        </div>
    </div>
</div>

{{-- ── RECIPIENTS TABLE ────────────────────────────────── --}}
<div class="card">
    <div class="card-hdr">
        <span class="card-title">Recipients</span>
        <span class="muted">{{ $messages->total() }} recipient{{ $messages->total() !== 1 ? 's' : '' }}</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="dt">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Recipient</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Sent At</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                <tr>
                    <td class="muted" style="font-size:12px;">{{ $loop->iteration + ($messages->currentPage() - 1) * $messages->perPage() }}</td>
                    <td style="font-weight:600;">{{ $msg->recipient_name ?? '—' }}</td>
                    <td class="muted">{{ $msg->phone }}</td>
                    <td>
                        @switch($msg->status)
                            @case('pending') <span class="badge bs">Pending</span>  @break
                            @case('sent')    <span class="badge bok">Sent</span>    @break
                            @case('failed')  <span class="badge be">Failed</span>   @break
                        @endswitch
                    </td>
                    <td class="muted" style="font-size:12px;">{{ $msg->sent_at?->format('H:i:s') ?? '—' }}</td>
                    <td class="muted" style="font-size:12px; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ $msg->error ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty">
                        <i class="bi bi-envelope"></i>
                        No messages in this campaign.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($messages->hasPages())
    <div style="padding:14px 22px; border-top:1px solid var(--border); display:flex; justify-content:center;">
        {{ $messages->links() }}
    </div>
    @endif
</div>

@if(in_array($campaign->status, ['queued', 'processing']))
@push('scripts')
<script>
// Live polling for progress updates
(function pollProgress() {
    const interval = setInterval(async () => {
        try {
            const res = await fetch('{{ route('admin.bulk-sms.refresh', $campaign) }}');
            const data = await res.json();

            document.getElementById('statSent').textContent    = data.sent_count.toLocaleString();
            document.getElementById('statFailed').textContent  = data.failed_count.toLocaleString();
            document.getElementById('statPending').textContent = (data.total - data.sent_count - data.failed_count).toLocaleString();
            document.getElementById('progressPct').textContent = data.progress + '%';
            document.getElementById('progressFill').style.width = data.progress + '%';

            if (data.status === 'completed' || data.status === 'failed') {
                clearInterval(interval);
                location.reload(); // Reload to show final state
            }
        } catch (e) { /* ignore fetch errors */ }
    }, 3000); // Poll every 3 seconds
})();
</script>
@endpush
@endif
@endsection
