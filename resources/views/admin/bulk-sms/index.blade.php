@extends('admin.layouts.app')
@section('title', 'Bulk SMS')
@section('page-title', 'Bulk SMS Campaigns')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Dashboard</a> / Bulk SMS
@endsection

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <div></div>
    <a href="{{ route('admin.bulk-sms.create') }}" class="btn btn-p">
        <i class="bi bi-plus-lg"></i> New Campaign
    </a>
</div>

<div class="card">
    <div class="card-hdr">
        <span class="card-title">All Campaigns</span>
        <span class="muted">{{ $campaigns->total() }} campaign{{ $campaigns->total() !== 1 ? 's' : '' }}</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="dt">
            <thead>
                <tr>
                    <th>Campaign</th>
                    <th>Audience</th>
                    <th>Recipients</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Date</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $c)
                <tr>
                    <td>
                        <div style="font-weight:600;">{{ $c->name }}</div>
                        <div class="muted" style="font-size:11.5px; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ Str::limit($c->message, 50) }}
                        </div>
                    </td>
                    <td>
                        <span class="badge bs" style="text-transform:capitalize;">{{ str_replace('_', ' ', $c->audience) }}</span>
                    </td>
                    <td style="font-weight:600;">{{ number_format($c->total_recipients) }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="flex:1; height:6px; background:#e2e8f0; border-radius:3px; min-width:60px;">
                                <div style="height:100%; width:{{ $c->progress }}%; background:{{ $c->status === 'completed' ? 'var(--ok)' : 'var(--pl)' }}; border-radius:3px; transition:width .3s;"></div>
                            </div>
                            <span style="font-size:11px; font-weight:600; color:var(--muted);">{{ $c->progress }}%</span>
                        </div>
                        <div class="muted" style="font-size:11px; margin-top:2px;">
                            {{ $c->sent_count }} sent · {{ $c->failed_count }} failed
                        </div>
                    </td>
                    <td>
                        @switch($c->status)
                            @case('draft')      <span class="badge bs">Draft</span>       @break
                            @case('queued')      <span class="badge bi">Queued</span>      @break
                            @case('processing')  <span class="badge bw">Processing</span>  @break
                            @case('completed')   <span class="badge bok">Completed</span>  @break
                            @case('failed')      <span class="badge be">Failed</span>      @break
                        @endswitch
                    </td>
                    <td class="muted" style="font-size:12.5px;">{{ $c->creator->name ?? '—' }}</td>
                    <td class="muted" style="font-size:12.5px;">{{ $c->created_at->format('d M Y H:i') }}</td>
                    <td style="text-align:right;">
                        <a href="{{ route('admin.bulk-sms.show', $c) }}" class="btn btn-sm btn-o">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty">
                        <i class="bi bi-chat-dots"></i>
                        No campaigns yet. Create your first bulk SMS campaign.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($campaigns->hasPages())
    <div style="padding:14px 22px; border-top:1px solid var(--border); display:flex; justify-content:center;">
        {{ $campaigns->links() }}
    </div>
    @endif
</div>
@endsection
