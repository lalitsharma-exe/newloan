@extends('admin.layouts.app')
@section('title','Credit Bureau')
@section('page-title','Credit Bureau')
@section('content')
<div class="card mb-4">
    <div class="card-hdr">
        <span class="card-title">Credit Reports</span>
        <div style="display:flex;gap:8px">
            <button onclick="openModal('pullModal')" class="btn btn-p btn-sm"><i class="bi bi-cloud-download"></i> Pull Report</button>
            <form method="POST" action="{{ route('admin.credit.submit-monthly') }}">@csrf<button class="btn btn-o btn-sm"><i class="bi bi-send"></i> Submit Monthly File</button></form>
        </div>
    </div>
    <div style="overflow-x:auto"><table class="dt">
        <thead><tr><th>User</th><th>National ID</th><th>Provider</th><th>Type</th><th>Score</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody>
        @forelse($reports as $r)
        <tr>
            <td>{{ $r->user->name??'—' }}</td>
            <td>{{ $r->national_id }}</td>
            <td>{{ $r->provider }}</td>
            <td><span class="badge badge-{{ $r->check_type==='hard'?'danger':'info' }}">{{ ucfirst($r->check_type) }}</span></td>
            <td><span style="font-weight:700;color:{{ $r->credit_score>=700?'#10b981':($r->credit_score>=500?'#f59e0b':'#ef4444') }}">{{ $r->credit_score??'—' }}</span></td>
            <td><span class="badge badge-success">{{ ucfirst($r->status) }}</span></td>
            <td>{{ $r->retrieved_at?->format('d M Y H:i')??'—' }}</td>
            <td><a href="{{ route('admin.credit.view-report',$r) }}" class="btn btn-xs btn-outline">View</a></td>
        </tr>
        @empty<tr><td colspan="8"><div class="empty"><i class="bi bi-shield-x"></i><p>No reports yet</p></div></td></tr>@endforelse
        </tbody>
    </table></div>
    @if($reports->hasPages())<div style="padding:16px 20px;border-top:1px solid #f1f5f9">{{ $reports->withQueryString()->links() }}</div>@endif
</div>

<div class="mo" id="pullModal">
    <div class="mb">

        <div class="mh">
            <span class="mt">Pull Credit Report</span>
            <button class="mc" onclick="closeModal('pullModal')">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.credit.pull-report') }}">
            @csrf

            <div class="mbody">

                <div class="fg">
                    <label class="fl">User ID</label>
                    <input type="number" name="user_id" class="fc" required placeholder="Enter user ID">
                </div>

                <div class="fg">
                    <label class="fl">National ID</label>
                    <input type="text" name="national_id" class="fc" required>
                </div>

                <div class="fg">
                    <label class="fl">Check Type</label>
                    <select name="check_type" class="fc">
                        <option value="soft">Soft Check</option>
                        <option value="hard">Hard Check</option>
                    </select>
                </div>

            </div>

            <div class="mf flex gap2">
                <button type="button" class="btn btn-o" onclick="closeModal('pullModal')">Cancel</button>
                <button type="submit" class="btn btn-p">
                    <i class="bi bi-cloud-download"></i> Pull
                </button>
            </div>

        </form>

    </div>
</div>
@endsection
