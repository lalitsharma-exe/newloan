@extends('admin.layouts.app')

@section('title', 'Treasury Management')
@section('page-title', 'Manage Bank Accounts & Wallets')

@section('content')
<div style="display:grid; grid-template-columns: 340px 1fr; gap:24px; align-items: start">
    <!-- Registration Form -->
    <div class="card">
        <div class="card-hdr"><span class="card-title">Register New Account</span></div>
        <div style="padding: 20px">
            <form action="{{ route('admin.financial.accounts.store') }}" method="POST">
                @csrf
                <div class="fg">
                    <label class="fl">Account Name *</label>
                    <input type="text" name="name" class="fc" placeholder="e.g. FNB Main Float" required>
                </div>
                <div class="fg">
                    <label class="fl">Institution</label>
                    <input type="text" name="institution" class="fc" placeholder="e.g. First National Bank">
                </div>
                <div class="fg">
                    <label class="fl">Account Type *</label>
                    <select name="type" class="fc" required>
                        <option value="bank">Commercial Bank Account</option>
                        <option value="mobile_wallet">Mobile Money (MPesa/EcoCash)</option>
                        <option value="cash_float">Office Petty Cash</option>
                        <option value="investment">Investment / Funding</option>
                    </select>
                </div>
                <div class="fg">
                    <label class="fl">Account Number</label>
                    <input type="text" name="account_number" class="fc" placeholder="Optional">
                </div>
                <div class="fg">
                    <label class="fl">Opening Balance *</label>
                    <div style="display:flex; align-items:center; position:relative">
                        <span style="position:absolute; left:12px; font-weight:700; color:var(--muted)">L</span>
                        <input type="number" step="0.01" name="balance" class="fc" value="0.00" style="padding-left:30px" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-p" style="width:100%; justify-content:center; padding:12px">
                    <i class="bi bi-shield-plus"></i> Initialize Account
                </button>
            </form>
        </div>
    </div>

    <!-- Accounts Registry -->
    <div class="card">
        <div class="card-hdr"><span class="card-title">Treasury Account Registry</span></div>
        <div style="overflow-x:auto">
            <table class="dt">
                <thead>
                    <tr>
                        <th>Institution / Account</th>
                        <th>Type</th>
                        <th>Ledger Entries</th>
                        <th style="text-align:right">Current Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $acc)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px">
                                <div style="width:40px; height:40px; background:var(--bg); border-radius:10px; display:flex; align-items:center; justify-content:center; color:var(--p); font-size:18px">
                                    @if($acc->type === 'bank') <i class="bi bi-bank"></i>
                                    @elseif($acc->type === 'mobile_wallet') <i class="bi bi-phone"></i>
                                    @else <i class="bi bi-cash-stack"></i>
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight:700; font-size:14px">{{ $acc->name }}</div>
                                    <div class="muted" style="font-size:11.5px">{{ $acc->institution }} {{ $acc->account_number ? '('.$acc->account_number.')' : '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bs" style="font-size:10px">{{ ucfirst(str_replace('_',' ',$acc->type)) }}</span></td>
                        <td class="muted" style="font-size:12.5px">{{ $acc->transactions_count }} entries</td>
                        <td style="text-align:right">
                            <div style="font-weight:800; font-size:18px; color: {{ $acc->balance < 0 ? 'var(--err)' : 'var(--p)' }}">
                                L {{ number_format($acc->balance, 2) }}
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @if($accounts->isEmpty())
                    <tr>
                        <td colspan="4" class="empty" style="padding: 60px">
                            <i class="bi bi-safe2"></i>
                            <p>No treasury accounts registered yet.</p>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
