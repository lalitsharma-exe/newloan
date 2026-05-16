@extends('admin.layouts.app')

@section('title', 'Expense Management')

@section('content')
<div class="d-wrap">
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 24px; font-weight: 800; color: #1e293b; margin-bottom: 4px;">Myloan Limited — Finance & Operations</h2>
        <p style="color: #64748b; font-size: 14px;">Expense recording • KPI monitoring • Profitability intelligence</p>
    </div>

    {{-- TABS --}}
    <div style="display: flex; gap: 12px; margin-bottom: 24px;">
        <button class="tab-btn active" onclick="switchTab('record')"><i class="bi bi-journal-plus"></i> Record expense</button>
        <button class="tab-btn" onclick="switchTab('log')"><i class="bi bi-list-task"></i> Expense log</button>
        <button class="tab-btn" onclick="switchTab('kpi')"><i class="bi bi-graph-up"></i> KPI dashboard</button>
        <button class="tab-btn" onclick="switchTab('action')"><i class="bi bi-lightning-charge"></i> Action centre</button>
    </div>

    @if(session('success'))
        <div class="alert bok mb-4"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert berror mb-4"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    {{-- TAB CONTENT: RECORD EXPENSE --}}
    <div id="tab-record" class="tab-pane active">
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px; align-items: start;">
            <div class="card">
                <div class="card-hdr"><span class="card-title">New Disbursement Entry</span></div>
                <div class="card-body" style="padding: 24px;">
                    <form action="{{ route('admin.financial.expenses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="fg">
                                <label class="fl">Category</label>
                                <select id="category_id" class="fc" onchange="loadSubcategories(this.value)" required>
                                    <option value="">— Select category —</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->ref_code }} {{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="fg">
                                <label class="fl">Subcategory</label>
                                <select id="subcategory_id" class="fc" onchange="loadTaxonomyItems(this.value)" required disabled>
                                    <option value="">— Select subcategory —</option>
                                </select>
                            </div>
                        </div>

                        <div class="fg" style="margin-bottom: 20px;">
                            <label class="fl">Description (Taxonomy Item)</label>
                            <select name="taxonomy_item_id" id="taxonomy_item_id" class="fc" required disabled>
                                <option value="">— Select description —</option>
                            </select>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="fg">
                                <label class="fl">Amount (LSL)</label>
                                <input type="number" step="0.01" name="amount" class="fc" placeholder="0.00" required>
                            </div>
                            <div class="fg">
                                <label class="fl">Date</label>
                                <input type="date" name="due_date" class="fc" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="fg">
                                <label class="fl">Disburse from Account</label>
                                <select name="treasury_account_id" class="fc">
                                    <option value="">— Mark as unpaid —</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} (M{{ number_format($acc->balance, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="fg">
                                <label class="fl">Attach Receipt</label>
                                <input type="file" name="receipt" class="fc">
                            </div>
                        </div>

                        <div class="fg" style="margin-bottom: 24px;">
                            <label class="fl">Notes (optional)</label>
                            <textarea name="notes" class="fc" rows="3" placeholder="Reference, invoice #, etc..."></textarea>
                        </div>

                        <div style="display: flex; gap: 12px;">
                            <button type="submit" class="btn btn-p" style="height:48px; flex:1; font-weight: 800;"><i class="bi bi-save2"></i> Save Expense</button>
                            <button type="reset" class="btn btn-o" style="height:48px; padding:0 24px;">Clear</button>
                        </div>
                    </form>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div class="card" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; border: none;">
                    <div class="card-body" style="padding: 24px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; opacity: 0.6; margin-bottom: 8px;">Total MTD Expense</div>
                        <div style="font-size: 28px; font-weight: 800;">M{{ number_format($mtdTotal, 2) }}</div>
                        <div style="font-size: 11px; margin-top: 12px; display: flex; align-items: center; gap: 4px;">
                            <span style="color: #4ade80;"><i class="bi bi-graph-up"></i> Calculated Profitability</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-hdr"><span class="card-title">Entry Guidance</span></div>
                    <div class="card-body" style="font-size: 13px; color: #64748b; line-height: 1.6;">
                        <p>1. Select the correct <strong>Category</strong> first to narrow down options.</p>
                        <p>2. If paying immediately, select a <strong>Treasury Account</strong> to update cash balances.</p>
                        <p>3. Attach a clear photo/PDF of the <strong>receipt</strong> for audit compliance.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB CONTENT: EXPENSE LOG --}}
    <div id="tab-log" class="tab-pane" style="display: none;">
        <div class="card">
            <div class="card-hdr" style="justify-content: space-between;">
                <span class="card-title">Expense History Ledger</span>
                <div style="display: flex; gap: 10px;">
                    <input type="text" placeholder="Search..." class="fc" style="height: 32px; width: 180px; font-size: 12px;">
                    <button class="btn btn-sm btn-o"><i class="bi bi-filter"></i> Filter</button>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="padding-left: 24px;">Item Ref</th>
                            <th>Description / Category</th>
                            <th style="text-align: right;">Amount</th>
                            <th>Status</th>
                            <th>Recorded</th>
                            <th style="text-align: right; padding-right: 24px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $e)
                        <tr>
                            <td style="padding-left: 24px;"><code style="font-weight: 700; color: var(--p);">{{ $e->taxonomyItem->ref_code ?? 'N/A' }}</code></td>
                            <td>
                                <div style="font-weight: 700;">{{ $e->title }}</div>
                                <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8;">{{ $e->category }}</div>
                            </td>
                            <td style="text-align: right; font-weight: 800;">M{{ number_format($e->amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $e->status === 'paid' ? 'bok' : 'bw' }}" style="font-size: 10px; font-weight: 800;">
                                    {{ strtoupper($e->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 12px; font-weight: 600;">{{ $e->due_date->format('d M, Y') }}</div>
                                <div style="font-size: 10px; color: #64748b;">By {{ $e->recorder->name ?? 'System' }}</div>
                            </td>
                            <td style="text-align: right; padding-right: 24px;">
                                @if($e->status === 'pending')
                                    <button onclick="openPayModal({{ $e->id }}, '{{ $e->title }}', {{ $e->amount }})" class="btn btn-sm btn-p" style="font-weight: 700;">PAY</button>
                                @else
                                    <button class="btn btn-sm btn-o" style="opacity: 0.5;"><i class="bi bi-eye"></i></button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="empty">No expenses recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div style="padding: 24px; border-top: 1px solid #f1f5f9;">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- TAB CONTENT: KPI DASHBOARD --}}
    <div id="tab-kpi" class="tab-pane" style="display: none;">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 24px;">
            <div class="card">
                <div class="card-body" style="padding: 24px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Pending</div>
                    <div style="font-size: 24px; font-weight: 800; color: #ef4444; margin: 8px 0;">M{{ number_format($pendingTotal, 2) }}</div>
                    <div style="font-size: 12px; color: #64748b;">{{ $pendingCount }} accounts awaiting payment</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="padding: 24px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Highest Category</div>
                    <div style="font-size: 24px; font-weight: 800; color: var(--p); margin: 8px 0;">{{ $categoryStats->sortByDesc('total')->first()->name ?? 'N/A' }}</div>
                    <div style="font-size: 12px; color: #64748b;">Dominating {{ round(($categoryStats->max('total') / ($mtdTotal ?: 1)) * 100) }}% of monthly burn</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="padding: 24px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Daily Avg Burn</div>
                    <div style="font-size: 24px; font-weight: 800; color: #1e293b; margin: 8px 0;">M{{ number_format($mtdTotal / date('d'), 2) }}</div>
                    <div style="font-size: 12px; color: #64748b;">Average across past {{ date('d') }} days</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-hdr"><span class="card-title">Expense Distribution by Category</span></div>
            <div class="card-body" style="padding: 24px;">
                @foreach($categoryStats as $stat)
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-weight: 700; color: #1e293b;">{{ $stat->name }}</span>
                        <span style="font-weight: 800; color: var(--p);">M{{ number_format($stat->total, 2) }}</span>
                    </div>
                    <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                        @php $p = ($stat->total / ($categoryStats->sum('total') ?: 1)) * 100; @endphp
                        <div style="width: {{ $p }}%; height: 100%; background: var(--p); border-radius: 4px;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- TAB CONTENT: ACTION CENTRE --}}
    <div id="tab-action" class="tab-pane" style="display: none;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;">
            <div class="card">
                <div class="card-hdr"><span class="card-title">Reports & Data</span></div>
                <div class="card-body" style="padding: 24px;">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <a href="{{ route('admin.financial.expenses.export') }}" class="btn btn-o" style="justify-content: flex-start; height: 50px;"><i class="bi bi-file-earmark-spreadsheet"></i> Export Monthly Ledger (.csv)</a>
                        <button class="btn btn-o" style="justify-content: flex-start; height: 50px;"><i class="bi bi-file-earmark-pdf"></i> Download Audit Report (.pdf)</button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-hdr"><span class="card-title">System Settings</span></div>
                <div class="card-body" style="padding: 24px;">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <a href="#" class="btn btn-o" style="justify-content: flex-start; height: 50px;"><i class="bi bi-tags"></i> Manage Taxonomy Categories</a>
                        <button class="btn btn-o" style="justify-content: flex-start; height: 50px; color: #ef4444;"><i class="bi bi-trash"></i> Purge Temporary Logs</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- PAYMENT MODAL --}}
<div id="payModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; width:100%; max-width:500px; border-radius:16px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
        <div style="padding:20px; background:var(--navy); color:#fff; display:flex; justify-content:space-between; align-items:center">
            <h3 style="font-size:18px; font-weight:700">Disburse Expense Payment</h3>
            <button onclick="closeModal()" style="background:transparent; border:none; color:#fff; font-size:24px; cursor:pointer">&times;</button>
        </div>
        <form id="payForm" method="POST" style="padding:25px">
            @csrf
            <div style="margin-bottom: 20px;">
                <div style="font-size: 14px; color: #64748b;">Expense: <strong id="m_title" style="color: #1e293b;"></strong></div>
                <div style="font-size: 14px; color: #64748b;">Amount: <strong id="m_amount" style="color: #1e293b;"></strong></div>
            </div>
            <div class="fg" style="margin-bottom: 20px;">
                <label class="fl">Disburse from Account <span style="color:red">*</span></label>
                <select name="treasury_account_id" class="fc" required>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} (M{{ number_format($acc->balance, 2) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="fg" style="margin-bottom: 20px;">
                <label class="fl">Payment Date <span style="color:red">*</span></label>
                <input type="date" name="payment_date" class="fc" value="{{ date('Y-m-d') }}" required>
            </div>
            <button type="submit" class="btn btn-p" style="width:100%; justify-content:center; height:50px; font-size: 16px; font-weight: 800;">Confirm Disbursement</button>
        </form>
    </div>
</div>

<style>
    .tab-btn {
        padding: 10px 20px; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff;
        color: #64748b; font-weight: 600; cursor: pointer; transition: all 0.2s;
        display: flex; align-items: center; gap: 8px; font-size: 14px;
    }
    .tab-btn:hover { background: #f8fafc; border-color: #cbd5e1; }
    .tab-btn.active { background: #fff; border-color: #1e293b; color: #1e293b; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    
    .fc:disabled { background-color: #f8fafc; cursor: not-allowed; }
</style>

@push('scripts')
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        
        document.getElementById('tab-' + tabId).style.display = 'block';
        event.currentTarget.classList.add('active');
    }

    function loadSubcategories(catId) {
        const subSelect = document.getElementById('subcategory_id');
        const itemSelect = document.getElementById('taxonomy_item_id');
        
        subSelect.innerHTML = '<option value="">— Select subcategory —</option>';
        itemSelect.innerHTML = '<option value="">— Select description —</option>';
        subSelect.disabled = true;
        itemSelect.disabled = true;

        if (!catId) return;

        fetch(`{{ route('admin.financial.expenses.subcategories') }}?category_id=${catId}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id;
                    opt.innerText = `${sub.ref_code} ${sub.name}`;
                    subSelect.appendChild(opt);
                });
                subSelect.disabled = false;
            });
    }

    function loadTaxonomyItems(subId) {
        const itemSelect = document.getElementById('taxonomy_item_id');
        itemSelect.innerHTML = '<option value="">— Select description —</option>';
        itemSelect.disabled = true;

        if (!subId) return;

        fetch(`{{ route('admin.financial.expenses.taxonomy-items') }}?subcategory_id=${subId}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.innerText = `${item.ref_code} ${item.name}`;
                    itemSelect.appendChild(opt);
                });
                itemSelect.disabled = false;
            });
    }

    function openPayModal(id, title, amount) {
        document.getElementById('m_title').innerText = title;
        document.getElementById('m_amount').innerText = 'M' + amount.toLocaleString();
        document.getElementById('payForm').action = `{{ url('/admin/financial/expenses') }}/${id}/pay`;
        document.getElementById('payModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('payModal').style.display = 'none';
    }
</script>
@endpush
@endsection
