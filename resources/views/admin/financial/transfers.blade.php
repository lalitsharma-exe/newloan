@extends('admin.layouts.app')
@section('title', 'Internal Fund Transfers')
@section('page-title', 'Treasury Liquidity Management')

@section('content')
<div style="display:grid; grid-template-columns: 1fr 420px; gap:28px; align-items: start;">
    
    {{-- Left: Transfer Ledger --}}
    <div style="display:flex; flex-direction:column; gap:24px">
        <!-- Summary Banner -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 20px; padding: 28px; color: white; display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);">
            <div style="position: absolute; right: -20px; bottom: -20px; font-size: 140px; opacity: 0.05; color: white;"><i class="bi bi-arrow-left-right"></i></div>
            <div style="position: relative; z-index: 1;">
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin-bottom: 8px;">Transfer Ledger</div>
                <h2 style="font-size: 28px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">Intra-Account <span style="color: #3b82f6;">Liquidity</span></h2>
                <p style="font-size: 13px; color: #64748b; margin-top: 8px; max-width: 400px;">Monitor and confirm fund movements between corporate bank accounts and mobile money liquidity pools.</p>
            </div>
            <div style="text-align: right; position: relative; z-index: 1;">
                <div class="badge" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 6px 12px; border-radius: 30px; font-weight: 700;">
                    <i class="bi bi-shield-check"></i> Audit Ready
                </div>
            </div>
        </div>

        <div class="card" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-radius: 16px;">
            <div class="card-hdr" style="padding: 20px 24px; background: #fff; border-bottom: 1px solid #f1f5f9;">
                <span class="card-title" style="font-size: 15px; font-weight: 800; color: #1e293b;">Recent Movements</span>
            </div>
            <div style="overflow-x:auto">
                <table class="dt" style="width:100%">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Transaction</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Route</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Amount</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Status</th>
                            <th style="padding: 14px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Authorization</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $t)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#fcfdfe'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 18px 24px;">
                                <div style="font-weight: 800; color: #1e293b; font-size: 13.5px;">{{ $t->uuid ? substr($t->uuid, 0, 8) : 'TRF-'.str_pad($t->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">{{ $t->transfer_date->format('d M, Y') }}</div>
                            </td>
                            <td style="padding: 18px 24px;">
                                <div style="display:flex; align-items:center; gap:10px">
                                    <div style="text-align: right;">
                                        <div style="font-size:12px; font-weight:700; color:#475569">{{ $t->fromAccount->name }}</div>
                                        <div style="font-size:10px; color:#94a3b8">{{ $t->fromAccount->institution }}</div>
                                    </div>
                                    <div style="color: #cbd5e1;"><i class="bi bi-chevron-right"></i></div>
                                    <div>
                                        <div style="font-size:12px; font-weight:700; color:#475569">{{ $t->toAccount->name }}</div>
                                        <div style="font-size:10px; color:#94a3b8">{{ $t->toAccount->institution }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 18px 24px; text-align: right;">
                                <div style="font-weight: 900; color: #0f172a; font-size: 15px;">L {{ number_format($t->amount, 2) }}</div>
                                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Ref: {{ $t->bank_reference }}</div>
                            </td>
                            <td style="padding: 18px 24px;">
                                @if($t->status === 'confirmed')
                                    <span class="badge bok" style="padding: 6px 10px; border-radius: 6px; font-weight: 800; letter-spacing: 0.5px; background: rgba(16, 185, 129, 0.1); color: #10b981; border: none;"><i class="bi bi-check-all"></i> SECURED</span>
                                @else
                                    <span class="badge bw" style="padding: 6px 10px; border-radius: 6px; font-weight: 800; letter-spacing: 0.5px; background: rgba(245, 158, 11, 0.1); color: #d97706; border: none; animation: pulse 2s infinite;"><i class="bi bi-clock-history"></i> PENDING</span>
                                @endif
                            </td>
                            <td style="padding: 18px 24px; text-align: right;">
                                @if($t->status === 'pending')
                                    <button class="btn btn-sm btn-ok" onclick="openConfirmModal({{ $t->id }}, {{ $t->amount }}, '{{ $t->toAccount->name }}')" style="background: #10b981; border: none; font-weight: 700; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);">
                                        Confirm Receipt
                                    </button>
                                @else
                                    <div style="display:flex; align-items:center; gap:8px; justify-content: flex-end;">
                                        <div style="text-align: right;">
                                            <div style="font-size:11px; font-weight:700; color:#1e293b">{{ $t->confirmer->name ?? 'System' }}</div>
                                            <div style="font-size:9px; color:#94a3b8">Confirmed @ {{ $t->updated_at->format('H:i') }}</div>
                                        </div>
                                        <div style="width:28px; height:28px; background:#f0fdf4; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#10b981; font-size:14px;"><i class="bi bi-patch-check-fill"></i></div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="padding: 80px 24px; text-align: center;">
                                <div style="font-size: 40px; color: #f1f5f9; margin-bottom: 15px;"><i class="bi bi-arrow-left-right"></i></div>
                                <div style="font-weight: 700; color: #94a3b8; font-size: 15px;">No transfers found in the ledger</div>
                                <div style="font-size: 12px; color: #cbd5e1; margin-top: 5px;">Initiated fund movements will appear here for audit.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transfers->hasPages())
            <div style="padding: 16px 24px; border-top: 1px solid #f1f5f9;">
                {{ $transfers->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Right: Initiation Panel --}}
    <div style="display:flex; flex-direction:column; gap:24px; position: sticky; top: 20px;">
        <div class="card" style="border: none; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08); border-radius: 20px; overflow: hidden;">
            <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 24px; color: white;">
                <h3 style="margin: 0; font-size: 17px; font-weight: 800; display:flex; align-items:center; gap:10px;">
                    <i class="bi bi-lightning-charge-fill"></i> Initiate Transfer
                </h3>
                <p style="margin: 8px 0 0; font-size: 12px; color: rgba(255,255,255,0.7); font-weight: 500;">Moving capital into mobile liquidity pools.</p>
            </div>
            <div class="card-body" style="padding: 28px;">
                <form action="{{ route('admin.financial.transfers.initiate') }}" method="POST">
                    @csrf
                    <div class="fg" style="margin-bottom: 20px;">
                        <label class="fl" style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Source Account (Bank)</label>
                        <select name="from_account_id" class="fc" required style="height: 48px; border-radius: 10px; border: 1px solid #e2e8f0; font-weight: 600; padding: 0 16px;">
                            <option value="">Select Funding Account</option>
                            @foreach($accounts->where('type', 'bank') as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} (L {{ number_format($acc->balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:flex; justify-content: center; margin: -10px 0 10px; position: relative; z-index: 2;">
                        <div style="width:36px; height:36px; background:#f1f5f9; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#3b82f6; border: 2px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05);"><i class="bi bi-arrow-down"></i></div>
                    </div>

                    <div class="fg" style="margin-bottom: 20px;">
                        <label class="fl" style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Destination Wallet</label>
                        <select name="to_account_id" class="fc" required style="height: 48px; border-radius: 10px; border: 1px solid #e2e8f0; font-weight: 600; padding: 0 16px;">
                            <option value="">Select Target Wallet</option>
                            @foreach($accounts->where('type', 'mobile_wallet') as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} (L {{ number_format($acc->balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom: 20px;">
                        <div class="fg" style="margin:0">
                            <label class="fl" style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Amount (L)</label>
                            <input type="number" name="amount" class="fc" step="0.01" required placeholder="0.00" style="height: 48px; border-radius: 10px; font-weight: 700; border: 1px solid #e2e8f0; padding: 0 16px;">
                        </div>
                        <div class="fg" style="margin:0">
                            <label class="fl" style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">EFT Date</label>
                            <input type="date" name="transfer_date" class="fc" value="{{ today()->format('Y-m-d') }}" required style="height: 48px; border-radius: 10px; font-weight: 600; border: 1px solid #e2e8f0; padding: 0 16px;">
                        </div>
                    </div>

                    <div class="fg" style="margin-bottom: 24px;">
                        <label class="fl" style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Bank Statement Reference</label>
                        <input type="text" name="bank_reference" class="fc" required placeholder="Enter EFT Reference" style="height: 48px; border-radius: 10px; font-weight: 600; border: 1px solid #e2e8f0; padding: 0 16px;">
                        <div style="font-size: 10px; color: #94a3b8; margin-top: 6px;">Used to reconcile against bank statements.</div>
                    </div>

                    <button type="submit" class="btn" style="width:100%; height: 54px; background: #2563eb; color: #fff; border: none; border-radius: 12px; font-weight: 800; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2); cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <i class="bi bi-send-check-fill"></i> Initiate Transfer
                    </button>
                </form>
            </div>
        </div>

        <!-- Compliance Note -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; display:flex; gap:12px; align-items: flex-start;">
            <div style="color: #64748b; font-size: 18px;"><i class="bi bi-info-circle-fill"></i></div>
            <div style="font-size: 12px; color: #475569; line-height: 1.5;">
                <strong style="color: #1e293b; display:block; margin-bottom: 4px;">Liquidity Policy:</strong>
                All internal transfers must be initiated by an authorized officer and confirmed by the receiving officer once funds reflect in the target wallet.
            </div>
        </div>
    </div>
</div>

{{-- Confirmation Modal --}}
<div id="confirmModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.8);z-index:9999;backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff; width:100%; max-width:440px; border-radius:24px; overflow:hidden; box-shadow:0 30px 100px rgba(0,0,0,0.4); animation: mIn 0.3s ease;">
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 32px; color: white; text-align: center;">
            <div style="width:64px; height:64px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:32px;"><i class="bi bi-check-circle"></i></div>
            <h3 style="margin:0; font-size:22px; font-weight:800;">Confirm Fund Receipt</h3>
            <div style="margin-top:10px; font-size:14px; opacity:0.9;">Verify and secure the intra-account transfer.</div>
        </div>
        <div style="padding: 32px;">
            <div style="text-align:center; margin-bottom:28px;">
                <div style="font-size:12px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Amount Received in <span id="modalTarget" style="color:#10b981"></span></div>
                <div style="font-size:36px; font-weight:900; color:#1e293b;">L <span id="modalAmount"></span></div>
            </div>
            
            <form id="confirmForm" method="POST">
                @csrf
                <div class="fg" style="margin-bottom:28px;">
                    <label class="fl" style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">M-Pesa / Mobile Confirmation Code *</label>
                    <input type="text" name="mpesa_confirmation" class="fc" required placeholder="e.g. M-Pesa Message ID" style="height: 52px; border-radius: 12px; font-weight: 700; border: 2px solid #e2e8f0; padding: 0 18px; text-align: center; font-size: 16px; letter-spacing: 1px;">
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 10px; text-align:center;">This serves as the definitive proof of receipt for audit.</div>
                </div>
                
                <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap:14px">
                    <button type="button" class="btn" onclick="closeConfirmModal()" style="height:52px; border-radius:12px; border:1px solid #e2e8f0; background:#fff; font-weight:700; color:#64748b; cursor:pointer;">Cancel</button>
                    <button type="submit" class="btn" style="height:52px; border-radius:12px; background:#10b981; color:#fff; border:none; font-weight:800; font-size:15px; cursor:pointer; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);">Complete Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.6; }
    100% { opacity: 1; }
}
@keyframes mIn {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

@push('scripts')
<script>
function openConfirmModal(id, amount, target) {
    document.getElementById('modalAmount').innerText = amount.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('modalTarget').innerText = target;
    document.getElementById('confirmForm').action = `/admin/financial/transfers/${id}/confirm`;
    document.getElementById('confirmModal').style.display = 'flex';
}
function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}
</script>
@endpush
@endsection
