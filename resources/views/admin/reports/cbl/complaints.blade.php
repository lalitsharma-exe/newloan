@extends('admin.layouts.app')

@section('title', 'Complaints Register')
@section('page-title', 'Customer Grievance Management')

@section('content')
<div style="display:grid; grid-template-columns: 360px 1fr; gap:24px; align-items: start">
    <!-- Log Complaint -->
    <div class="card">
        <div class="card-hdr"><span class="card-title">Log New Complaint</span></div>
        <div style="padding: 20px">
            <form action="{{ route('admin.reports.cbl.complaints.store') }}" method="POST">
                @csrf
                <div class="fg">
                    <label class="fl">Search Borrower (Phone or Email)</label>
                    <input type="text" id="user_search" class="fc" placeholder="Type to search..." autocomplete="off">
                    <input type="hidden" name="user_id" id="selected_user_id" required>
                    <div id="search_results" style="background:#fff; border:1px solid var(--border); border-radius:8px; display:none; margin-top:5px; position:absolute; width:100%; z-index:100; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1)"></div>
                </div>
                <div class="fg">
                    <label class="fl">Complaint Type</label>
                    <select name="complaint_type" class="fc" required>
                        <option value="Billing Discrepancy">Billing Discrepancy</option>
                        <option value="Service Quality">Service Quality</option>
                        <option value="Interest Rate Dispute">Interest Rate Dispute</option>
                        <option value="Collection Behavior">Collection Behavior</option>
                        <option value="Privacy / Data Breach">Privacy / Data Breach</option>
                        <option value="Fraud Allegation">Fraud Allegation</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="fg">
                    <label class="fl">Complaint Date</label>
                    <input type="date" name="complaint_date" class="fc" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="fg">
                    <label class="fl">Full Description</label>
                    <textarea name="description" class="fc" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-p" style="width:100%; justify-content:center; padding:12px">
                    <i class="bi bi-journal-plus"></i> Record Complaint
                </button>
            </form>
        </div>
    </div>

    <!-- Complaints List -->
    <div class="card">
        <div class="card-hdr"><span class="card-title">Active Complaints Log</span></div>
        <div style="overflow-x:auto">
            <table class="dt">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Borrower</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($complaints as $complaint)
                    <tr>
                        <td>{{ $complaint->complaint_date->format('d M, Y') }}</td>
                        <td>
                            <div style="font-weight:700">{{ $complaint->user->name }}</div>
                            <div class="muted" style="font-size:11px">{{ $complaint->user->phone }}</div>
                        </td>
                        <td>{{ $complaint->complaint_type }}</td>
                        <td>
                            @php
                                $statusClass = match($complaint->status) {
                                    'OPEN' => 'bw',
                                    'RESOLVED_INTERNALLY' => 'bok',
                                    'REFERRED_TO_CBL' => 'bp',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ str_replace('_', ' ', $complaint->status) }}</span>
                        </td>
                        <td style="text-align:right">
                            <button class="btn btn-sm btn-o">Manage</button>
                        </td>
                    </tr>
                    @endforeach
                    @if($complaints->isEmpty())
                    <tr>
                        <td colspan="5" class="empty" style="padding: 60px">
                            <i class="bi bi-chat-left-dots"></i>
                            <p>No complaints recorded yet. Use the form to log borrower grievances.</p>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if($complaints->hasPages())
            <div style="padding:15px; border-top:1px solid var(--border)">{{ $complaints->links() }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    const searchInput = document.getElementById('user_search');
    const resultsDiv = document.getElementById('search_results');
    const selectedUserId = document.getElementById('selected_user_id');

    searchInput.addEventListener('input', function() {
        const query = this.value;
        if (query.length < 3) {
            resultsDiv.style.display = 'none';
            return;
        }

        fetch(`/admin/reports/cbl/search-users?q=${query}`)
            .then(res => res.json())
            .then(data => {
                resultsDiv.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(user => {
                        const div = document.createElement('div');
                        div.style.padding = '10px 15px';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid var(--border)';
                        div.innerHTML = `<div style="font-weight:700">${user.name}</div><div class="muted" style="font-size:11px">${user.phone}</div>`;
                        div.onclick = () => {
                            searchInput.value = user.name;
                            selectedUserId.value = user.id;
                            resultsDiv.style.display = 'none';
                        };
                        div.onmouseover = () => div.style.background = 'var(--bg)';
                        div.onmouseout = () => div.style.background = '#fff';
                        resultsDiv.appendChild(div);
                    });
                    resultsDiv.style.display = 'block';
                } else {
                    resultsDiv.style.display = 'none';
                }
            });
    });
</script>
@endpush
@endsection
