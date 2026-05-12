@extends('admin.layouts.app')
@section('title', 'Decline Tracker')
@section('page-title', 'Decline Reason Tracker')
@section('bc', 'Decline Tracker')

@section('content')
<div class="tabs">
    <a href="{{ route('admin.declines.index') }}" class="tab active">Decline Log</a>
    <a href="{{ route('admin.declines.report') }}" class="tab">Summary Report</a>
</div>

<div class="filter-bar">
    <form method="GET" action="{{ route('admin.declines.index') }}" class="flex aic gap3" style="width:100%">
        <div class="fg">
            <label class="fl">Category</label>
            <select name="category_id" class="fc">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="fg">
            <label class="fl">From Date</label>
            <input type="date" name="from" class="fc" value="{{ request('from') }}">
        </div>
        <div class="fg">
            <label class="fl">To Date</label>
            <input type="date" name="to" class="fc" value="{{ request('to') }}">
        </div>
        <div style="margin-bottom:0; align-self:flex-end">
            <button type="submit" class="btn btn-p"><i class="bi bi-filter"></i> Apply Filters</button>
            @if(request()->anyFilled(['category_id', 'from', 'to']))
            <a href="{{ route('admin.declines.index') }}" class="btn btn-o">Clear</a>
            @endif
        </div>
    </form>
</div>

<div class="card">
    <div class="card-hdr">
        <span class="card-title">Decline Records ({{ $records->total() }})</span>
        <div class="flex gap2">
            {{-- Export button could go here --}}
        </div>
    </div>
    <div style="overflow-x:auto">
        <table class="dt">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>App Ref</th>
                    <th>Applicant</th>
                    <th>Category</th>
                    <th>Specific Reason</th>
                    <th>Amount</th>
                    <th>Officer</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                @php
                    $colors = [
                        1 => ['bg' => '#FAECE7', 'text' => '#993C1D'],
                        2 => ['bg' => '#FCEBEB', 'text' => '#A32D2D'],
                        3 => ['bg' => '#FAEEDA', 'text' => '#854F0B'],
                        4 => ['bg' => '#E6F1FB', 'text' => '#185FA5'],
                        5 => ['bg' => '#EEEDFE', 'text' => '#3C3489'],
                        6 => ['bg' => '#E1F5EE', 'text' => '#0F6E56'],
                        7 => ['bg' => '#EAF3DE', 'text' => '#3B6D11'],
                        8 => ['bg' => '#FBEAF0', 'text' => '#993556'],
                    ];
                    $c = $colors[$r->category_id] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
                @endphp
                <tr>
                    <td>{{ $r->declined_at->format('d M Y') }}</td>
                    <td><a href="{{ route('admin.applications.show', $r->application_id) }}" style="color:var(--p);font-weight:700">{{ $r->application_number }}</a></td>
                    <td>
                        <div style="font-weight:600">{{ $r->applicant_name }}</div>
                        @if($r->application->user)
                        <div class="muted">{{ $r->application->user->email }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background:{{ $c['bg'] }}; color:{{ $c['text'] }}; border:1px solid {{ $c['text'] }}20">
                            {{ $r->category->name }}
                        </span>
                    </td>
                    <td style="max-width:250px">
                        <div style="font-size:13px; font-weight:500">{{ $r->reason }}</div>
                        @if($r->notes)
                        <div class="muted" style="font-size:11px; margin-top:2px">{{ Str::limit($r->notes, 60) }}</div>
                        @endif
                    </td>
                    <td style="font-weight:700">M{{ number_format($r->loan_amount, 2) }}</td>
                    <td>
                        <div class="flex aic gap2">
                            <div class="av av-sm">{{ strtoupper(substr($r->creator->name, 0, 1)) }}</div>
                            <span style="font-size:12px">{{ explode(' ', $r->creator->name)[0] }}</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty">
                            <i class="bi bi-journal-x"></i>
                            <p>No decline records found matching your filters.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div style="padding:20px; border-top:1px solid var(--border)">
        {{ $records->appends(request()->all())->links() }}
    </div>
    @endif
</div>
@endsection
