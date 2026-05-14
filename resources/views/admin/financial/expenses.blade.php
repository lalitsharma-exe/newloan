@extends('admin.layouts.app')

@section('title', 'Expense Management')
@section('page-title', 'Operational Spending & Approvals')

@section('content')
<div style="display:grid; grid-template-columns: 340px 1fr; gap:24px; align-items: start">
    <!-- Expense Form -->
    <div class="card">
        <div class="card-hdr"><span class="card-title">Record New Expense</span></div>
        <div style="padding: 20px">
            <form action="{{ route('admin.financial.expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="fg">
                    <label class="fl">Category *</label>
                    <select name="category" class="fc" required>
                        <option value="STAFF">STAFF (Salaries, Bonus)</option>
                        <option value="TECHNOLOGY">TECHNOLOGY (Hosting, APIs)</option>
                        <option value="OPERATIONS">OPERATIONS (Rent, Utilities)</option>
                        <option value="MARKETING">MARKETING (Ads, Campaigns)</option>
                        <option value="COLLECTIONS">COLLECTIONS (Field, Legal)</option>
                        <option value="FINANCE">FINANCE (Bank Charges, Fees)</option>
                        <option value="COMPLIANCE">COMPLIANCE (Licenses, Audit)</option>
                        <option value="CREDIT_RISK">CREDIT & RISK (Bureau, KYC)</option>
                        <option value="BAD_DEBT">BAD DEBT (Write-offs)</option>
                    </select>
                </div>
                <div class="fg">
                    <label class="fl">Title / Description *</label>
                    <input type="text" name="title" class="fc" placeholder="e.g. AWS Hosting Fee" required>
                </div>
                <div class="fg">
                    <label class="fl">Amount *</label>
                    <div style="display:flex; align-items:center; position:relative">
                        <span style="position:absolute; left:12px; font-weight:700; color:var(--muted)">L</span>
                        <input type="number" step="0.01" name="amount" class="fc" style="padding-left:30px" placeholder="0.00" required>
                    </div>
                </div>
                <div class="fg">
                    <label class="fl">Upload Receipt (Image/PDF)</label>
                    <input type="file" name="receipt" class="fc" accept="image/*,application/pdf">
                    <div class="muted" style="font-size:10px; margin-top:4px">Max size 2MB</div>
                </div>
                <div class="fg">
                    <label class="fl">Due Date *</label>
                    <input type="date" name="due_date" class="fc" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="fg" style="display:flex; align-items:center; gap:10px; margin-top:10px">
                    <input type="checkbox" name="is_recurring" value="1" id="is_recurring" style="width:18px; height:18px; accent-color:var(--p)">
                    <label for="is_recurring" style="font-size:13px; font-weight:600; cursor:pointer">Recurring Monthly</label>
                </div>
                <button type="submit" class="btn btn-p" style="width:100%; justify-content:center; padding:12px; margin-top:10px">
                    <i class="bi bi-journal-plus"></i> Log Obligation
                </button>
            </form>
        </div>
    </div>

    <!-- Expense Table -->
    <div class="card">
        <div class="card-hdr"><span class="card-title">Operational Expense Ledger</span></div>
        <div style="overflow-x:auto">
            <table class="dt">
                <thead>
                    <tr>
                        <th>Details</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $ex)
                    <tr>
                        <td>
                            <div style="font-weight:700; font-size:14px">{{ $ex->title }}</div>
                            <div class="muted" style="font-size:11.5px; display:flex; align-items:center; gap:8px">
                                <span><i class="bi bi-calendar-event"></i> Due {{ $ex->due_date->format('d M, Y') }}</span>
                                @if($ex->receipt_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($ex->receipt_path) }}" target="_blank" style="color:var(--p); text-decoration:none; display:flex; align-items:center; gap:3px">
                                        <i class="bi bi-file-earmark-image"></i> View Receipt
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td><span class="badge bs" style="font-size:10px">{{ $ex->category }}</span></td>
                        <td style="font-weight:800; font-size:15px">L {{ number_format($ex->amount, 2) }}</td>
                        <td>
                            @if($ex->status === 'paid')
                                <span class="badge bok" style="font-size:9.5px"><i class="bi bi-check-circle-fill"></i> SETTLED</span>
                            @else
                                <span class="badge bw" style="font-size:9.5px"><i class="bi bi-clock-fill"></i> PENDING</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            @if($ex->status === 'pending')
                                <button class="btn btn-sm btn-ok" onclick="openModal('payModal{{ $ex->id }}')">Authorize</button>

                                <div class="mo" id="payModal{{ $ex->id }}"><div class="mb" style="max-width:400px">
                                    <div class="mh"><span class="mt">Authorize Payment</span><button class="mc" onclick="closeModal('payModal{{ $ex->id }}')">&times;</button></div>
                                    <form action="{{ route('admin.financial.expenses.pay', $ex) }}" method="POST">
                                        @csrf
                                        <div class="mbody" style="text-align:left">
                                            <div style="background:var(--bg); border-radius:12px; padding:16px; margin-bottom:20px">
                                                <div class="muted" style="font-size:10px; font-weight:700; text-transform:uppercase">Settlement Amount</div>
                                                <div style="font-size:24px; font-weight:800; color:var(--p)">L {{ number_format($ex->amount, 2) }}</div>
                                                <div class="muted" style="font-size:11px; margin-top:4px">{{ $ex->title }}</div>
                                            </div>
                                            <div class="fg">
                                                <label class="fl">Source Treasury Account *</label>
                                                <select name="treasury_account_id" class="fc" required>
                                                    @foreach($accounts as $acc)
                                                        <option value="{{ $acc->id }}">{{ $acc->name }} (L {{ number_format($acc->balance, 2) }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="fg">
                                                <label class="fl">Payment Date *</label>
                                                <input type="date" name="payment_date" class="fc" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                        </div>
                                        <div class="mf">
                                            <button type="button" class="btn btn-o" onclick="closeModal('payModal{{ $ex->id }}')">Cancel</button>
                                            <button type="submit" class="btn btn-ok">Confirm Payment</button>
                                        </div>
                                    </form>
                                </div></div>
                            @else
                                <div style="font-weight:700; font-size:12px">Settled on {{ $ex->payment_date?->format('d M') }}</div>
                                <div class="muted" style="font-size:11px">{{ $ex->account?->name ?? 'Treasury' }}</div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div style="padding:15px; border-top:1px solid var(--border)">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>
@endsection
