@extends('admin.layouts.app')

@section('title', 'Review Float Application')
@section('page-title', 'Float Details: ' . $float->user->name)

@section('content')
<div style="display:flex; gap:20px; align-items:start">
    <div style="flex:2">
        <div class="card mb4">
            <div class="card-hdr"><span class="card-title">Application Detail</span></div>
            <div style="padding:24px">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px">
                    <div>
                        <div class="muted uppercase mb1" style="font-size:10px; font-weight:700">Customer Identity</div>
                        <div style="font-size:18px; font-weight:800">{{ $float->user->name }}</div>
                        <div class="muted mb3">{{ $float->user->national_id }} · {{ $float->user->phone }}</div>

                        <div class="muted uppercase mb1" style="font-size:10px; font-weight:700">Requested Purpose</div>
                        <div style="font-size:14px; background:var(--bg); padding:10px; border-radius:8px">
                            {{ $float->purpose ?: 'No purpose specified' }}
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div class="muted uppercase mb1" style="font-size:10px; font-weight:700">Current Status</div>
                        @php
                            $colors = [
                                'pending' => 'var(--warn)',
                                'approved' => 'var(--ok)',
                                'disbursed' => 'var(--p)',
                                'due' => 'var(--navy)',
                                'overdue' => 'var(--err)',
                                'closed' => 'var(--muted)',
                                'rejected' => 'var(--err)',
                            ];
                            $c = $colors[$float->status] ?? 'var(--muted)';
                        @endphp
                        <div style="font-size:24px; font-weight:900; color:{{ $c }}">{{ strtoupper($float->status) }}</div>
                        <div class="muted">Applied: {{ $float->applied_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>

                <div style="margin-top:40px; display:grid; grid-template-columns: repeat(3, 1fr); gap:20px">
                    <div style="background:var(--bg); padding:20px; border-radius:12px">
                        <div class="muted uppercase" style="font-size:9px; font-weight:700">Principal</div>
                        <div style="font-size:20px; font-weight:800">M{{ number_format($float->principal_amount, 2) }}</div>
                    </div>
                    <div style="background:var(--bg); padding:20px; border-radius:12px">
                        <div class="muted uppercase" style="font-size:9px; font-weight:700">Service Charge</div>
                        <div style="font-size:20px; font-weight:800">M{{ number_format($float->charge_amount, 2) }}</div>
                    </div>
                    <div style="background:rgba(61, 96, 212, 0.05); padding:20px; border:1px solid var(--p); border-radius:12px">
                        <div style="color:var(--p); font-size:9px; font-weight:700; text-transform:uppercase">Total Repayment</div>
                        <div style="font-size:20px; font-weight:800; color:var(--p)">M{{ number_format($float->outstanding_balance, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($float->penaltyLogs->count() > 0)
        <div class="card mb4">
            <div class="card-hdr"><span class="card-title">Penalty History</span></div>
            <table class="dt">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount Added</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($float->penaltyLogs as $log)
                    <tr>
                        <td>{{ $log->penalty_date->format('d M Y') }}</td>
                        <td style="color:var(--err); font-weight:700">+M{{ number_format($log->penalty_amount, 2) }}</td>
                        <td>M{{ number_format($log->balance_before, 2) }}</td>
                        <td style="font-weight:800">M{{ number_format($log->balance_after, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div style="flex:1">
        <div class="card mb4">
            <div class="card-hdr"><span class="card-title">Affordability Gate</span></div>
            <div style="padding:20px; text-align:center">
                <div style="font-size:12px; margin-bottom:5px" class="muted">Disposable Income</div>
                <div style="font-size:32px; font-weight:900; color:{{ $affordability['result'] == 'pass' ? 'var(--ok)' : 'var(--err)' }}">
                    M{{ number_format($affordability['disposable_income'], 0) }}
                </div>
                <div class="badge {{ $affordability['result'] == 'pass' ? 'bok' : 'berr' }} w-100 mt2" style="padding:10px; font-size:14px">
                    {{ strtoupper($affordability['result']) }}
                </div>
                <div class="muted mt3" style="font-size:11px">
                    Calculated as: Salary - Deductions - Existing Instalments. Required: M625.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-hdr"><span class="card-title">Decision</span></div>
            <div style="padding:20px; display:flex; flex-direction:column; gap:12px">
                @if($float->status == 'pending')
                <form action="{{ route('admin.float.approve', $float) }}" method="POST">
                    @csrf
                    <button class="btn btn-ok w-100" style="justify-content:center"><i class="bi bi-check-lg"></i> Approve Application</button>
                </form>
                <button onclick="document.getElementById('rejectForm').style.display='block'" class="btn btn-o btn-e w-100" style="justify-content:center"><i class="bi bi-x-lg"></i> Reject Application</button>
                
                <div id="rejectForm" style="display:none; margin-top:15px; border-top:1px solid var(--border); padding-top:15px">
                    <form action="{{ route('admin.float.reject', $float) }}" method="POST">
                        @csrf
                        <textarea name="reason" class="form-control mb2" placeholder="Reason for rejection..." required></textarea>
                        <button class="btn btn-e w-100" style="justify-content:center">Confirm Rejection</button>
                    </form>
                </div>
                @endif

                @if($float->status == 'approved')
                <form action="{{ route('admin.float.disburse', $float) }}" method="POST">
                    @csrf
                    <button class="btn btn-p w-100" style="justify-content:center; background:var(--navy)"><i class="bi bi-cash"></i> Mark as Disbursed</button>
                </form>
                @endif

                @if(in_array($float->status, ['disbursed', 'due', 'overdue']))
                    <div style="padding:15px; background:var(--bg); border-radius:10px; text-align:center">
                        <div class="muted" style="font-size:11px">Awaiting payment or penalty cycle</div>
                    </div>
                @endif

                <div style="margin-top:15px; border-top:1px solid var(--border); padding-top:15px">
                    <div class="muted uppercase mb2" style="font-size:9px; font-weight:700">Risk Control</div>
                    <button onclick="document.getElementById('freezeForm').style.display='block'" class="btn btn-sm btn-o btn-e w-100" style="justify-content:center"><i class="bi bi-snow"></i> Freeze User Access</button>
                    
                    <div id="freezeForm" style="display:none; margin-top:10px">
                        <form action="{{ route('admin.float.freeze', $float->user) }}" method="POST">
                            @csrf
                            <input type="text" name="reason" class="form-control form-control-sm mb2" placeholder="Freeze reason..." required>
                            <button class="btn btn-sm btn-e w-100">Confirm Freeze</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
