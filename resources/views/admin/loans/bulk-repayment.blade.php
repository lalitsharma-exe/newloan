@extends('admin.layouts.app')
@section('title','Bulk Repayment')
@section('page-title','Bulk Repayment Entry')
@section('bc','<a href="'.route('admin.loans.index').'">Loans</a> / Bulk Repayment')
@section('content')

@if(session('success'))
<div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);color:#065f46;padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px;margin-bottom:18px">
  <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#991b1b;padding:12px 16px;border-radius:11px;font-size:13px;margin-bottom:18px">
  <i class="bi bi-x-circle-fill"></i> {{ session('error') }}
</div>
@endif

{{-- Results from last submission --}}
@if(session('bulk_results'))
@php $res = session('bulk_results'); @endphp
<div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px 22px;margin-bottom:20px">
  <div style="font-weight:700;font-size:15px;margin-bottom:12px">Submission Results</div>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px">
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;text-align:center">
      <div style="font-size:28px;font-weight:800;color:#059669">{{ $res['success'] }}</div>
      <div style="font-size:12px;color:#065f46;font-weight:600">Recorded</div>
    </div>
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px;text-align:center">
      <div style="font-size:28px;font-weight:800;color:#dc2626">{{ $res['failed'] }}</div>
      <div style="font-size:12px;color:#991b1b;font-weight:600">Failed</div>
    </div>
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px;text-align:center">
      <div style="font-size:28px;font-weight:800;color:#1d4ed8">{{ $res['success'] + $res['failed'] }}</div>
      <div style="font-size:12px;color:#1e40af;font-weight:600">Total Rows</div>
    </div>
  </div>
  @if(!empty($res['errors']))
  <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 14px;font-size:12.5px;color:#991b1b">
    <strong>Errors:</strong>
    <ul style="margin:6px 0 0 16px">
      @foreach($res['errors'] as $err)<li>{{ $err }}</li>@endforeach
    </ul>
  </div>
  @endif
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">

{{-- Main entry form --}}
<div class="card">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-collection" style="color:var(--p)"></i> Enter Up to 30 Repayments</span>
    <div style="display:flex;gap:8px">
      <button type="button" onclick="addRow()" class="btn btn-sm btn-p"><i class="bi bi-plus-lg"></i> Add Row</button>
      <button type="button" onclick="clearAll()" class="btn btn-sm btn-o"><i class="bi bi-trash3"></i> Clear All</button>
    </div>
  </div>
  <form method="POST" action="{{ route('admin.loans.bulk-repayment.post') }}" id="bulkForm">
    @csrf
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
      <div class="form-group" style="margin:0;min-width:200px">
        <label class="form-label">Payment Method (applies to all) *</label>
        <select name="method" class="form-control" required>
          <option value="cash">Cash</option>
          <option value="bank_transfer">Bank Transfer</option>
          <option value="mobile_money">Mobile Money</option>
          <option value="card">Debit Card</option>
          <option value="payroll" selected>Payroll Deduction</option>
          <option value="cheque">Cheque</option>
        </select>
      </div>
      <div style="font-size:12px;color:var(--muted);padding-bottom:2px">
        Collector: <strong>{{ auth('admin')->user()->name }}</strong> &nbsp;·&nbsp; Date: <strong>{{ now()->format('d M Y') }}</strong>
      </div>
    </div>

    <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;min-width:700px">
        <thead>
          <tr style="background:#f8fafc;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)">
            <th style="padding:10px 12px;border-bottom:1px solid var(--border);width:36px">#</th>
            <th style="padding:10px 12px;border-bottom:1px solid var(--border)">Loan Number *</th>
            <th style="padding:10px 12px;border-bottom:1px solid var(--border)">Borrower Name (auto)</th>
            <th style="padding:10px 12px;border-bottom:1px solid var(--border)">Amount Due</th>
            <th style="padding:10px 12px;border-bottom:1px solid var(--border)">Amount Paying *</th>
            <th style="padding:10px 12px;border-bottom:1px solid var(--border)">Notes</th>
            <th style="padding:10px 12px;border-bottom:1px solid var(--border);width:40px"></th>
          </tr>
        </thead>
        <tbody id="rowsBody">
          {{-- JS will populate rows --}}
        </tbody>
      </table>
    </div>

    <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
      <div style="font-size:13px;color:var(--muted)">
        <span id="rowCount">0</span> rows &nbsp;·&nbsp; Total: <strong id="totalAmt">M0.00</strong>
      </div>
      <div style="display:flex;gap:10px">
        <a href="{{ route('admin.loans.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
          <i class="bi bi-check2-all"></i> Record All Payments
        </button>
      </div>
    </div>
  </form>
</div>

{{-- Side panel: instructions + quick lookup --}}
<div style="display:flex;flex-direction:column;gap:16px">
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-search"></i> Quick Loan Lookup</span></div>
    <div style="padding:14px 16px">
      <input type="text" id="loanLookup" class="form-control" placeholder="Type loan number…" oninput="lookupLoan(this.value)">
      <div id="lookupResult" style="margin-top:10px;font-size:13px;min-height:40px"></div>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-info-circle"></i> Instructions</span></div>
    <div style="padding:14px 16px;font-size:13px;color:var(--muted);line-height:1.8">
      <p>1. Select the payment method at the top — it applies to all rows.</p>
      <p style="margin-top:8px">2. Enter each borrower's loan number and the amount they paid.</p>
      <p style="margin-top:8px">3. The system will auto-fill the borrower name and amount due when you tab out of the loan number field.</p>
      <p style="margin-top:8px">4. You can enter up to <strong>30 rows</strong> at once.</p>
      <p style="margin-top:8px">5. Excess payments automatically carry over to the next installment.</p>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-upload"></i> Or Upload CSV</span></div>
    <div style="padding:14px 16px;font-size:13px">
      <p style="color:var(--muted);margin-bottom:10px">CSV format: <code>loan_number,amount,notes</code></p>
      <input type="file" id="csvUpload" accept=".csv,.txt" class="form-control" onchange="loadCsv(this)">
    </div>
  </div>
</div>

</div>

<script>
let rowIndex = 0;
const MAX_ROWS = 30;

function makeRow(i, loanNum='', amount='', notes='') {
  return `<tr id="row_${i}" style="font-size:13px">
    <td style="padding:8px 12px;border-bottom:1px solid var(--border);color:var(--muted);text-align:center">${i+1}</td>
    <td style="padding:6px 8px;border-bottom:1px solid var(--border)">
      <input type="text" name="rows[${i}][loan_number]" class="form-control loan-num" value="${loanNum}"
        placeholder="LN-00001" onblur="fetchLoanInfo(this, ${i})" style="min-width:110px">
    </td>
    <td style="padding:6px 8px;border-bottom:1px solid var(--border)">
      <span id="name_${i}" style="color:var(--muted);font-size:12px">—</span>
    </td>
    <td style="padding:6px 8px;border-bottom:1px solid var(--border)">
      <span id="due_${i}" style="color:#ef4444;font-weight:600;font-size:12px">—</span>
    </td>
    <td style="padding:6px 8px;border-bottom:1px solid var(--border)">
      <input type="number" name="rows[${i}][amount]" class="form-control pay-amt" value="${amount}"
        placeholder="0.00" step="0.01" min="0.01" oninput="updateTotal()" style="min-width:100px">
    </td>
    <td style="padding:6px 8px;border-bottom:1px solid var(--border)">
      <input type="text" name="rows[${i}][notes]" class="form-control" value="${notes}" placeholder="Optional…" style="min-width:120px">
    </td>
    <td style="padding:6px 8px;border-bottom:1px solid var(--border);text-align:center">
      <button type="button" onclick="removeRow(${i})" style="background:none;border:none;cursor:pointer;color:#ef4444;font-size:16px">
        <i class="bi bi-x-circle"></i>
      </button>
    </td>
  </tr>`;
}

function addRow(loanNum='', amount='', notes='') {
  if (document.querySelectorAll('#rowsBody tr').length >= MAX_ROWS) {
    alert('Maximum 30 rows allowed.');
    return;
  }
  document.getElementById('rowsBody').insertAdjacentHTML('beforeend', makeRow(rowIndex, loanNum, amount, notes));
  rowIndex++;
  updateTotal();
}

function removeRow(i) {
  const row = document.getElementById('row_'+i);
  if (row) row.remove();
  updateTotal();
}

function clearAll() {
  if (confirm('Clear all rows?')) {
    document.getElementById('rowsBody').innerHTML = '';
    rowIndex = 0;
    updateTotal();
  }
}

function updateTotal() {
  const amts  = [...document.querySelectorAll('.pay-amt')].map(i => parseFloat(i.value)||0);
  const total = amts.reduce((a,b) => a+b, 0);
  const count = document.querySelectorAll('#rowsBody tr').length;
  document.getElementById('rowCount').textContent = count;
  document.getElementById('totalAmt').textContent = 'M' + total.toFixed(2);
  document.getElementById('submitBtn').disabled = count === 0;
}

async function fetchLoanInfo(input, rowIdx) {
  const ln = input.value.trim();
  if (!ln) return;
  try {
    const res = await fetch(`/admin/loans/lookup?loan_number=${encodeURIComponent(ln)}`, {
      headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
    });
    if (!res.ok) throw new Error('Not found');
    const d = await res.json();
    document.getElementById('name_'+rowIdx).textContent = d.borrower_name || '—';
    document.getElementById('due_'+rowIdx).textContent  = d.next_due ? 'M'+parseFloat(d.next_due).toFixed(2) : '—';
    // Auto-fill amount if empty
    const amtInput = document.querySelector(`[name="rows[${rowIdx}][amount]"]`);
    if (amtInput && !amtInput.value && d.next_due) amtInput.value = parseFloat(d.next_due).toFixed(2);
    updateTotal();
  } catch(e) {
    document.getElementById('name_'+rowIdx).innerHTML = '<span style="color:#ef4444">Not found</span>';
    document.getElementById('due_'+rowIdx).textContent = '—';
  }
}

async function lookupLoan(val) {
  const el = document.getElementById('lookupResult');
  if (val.length < 3) { el.innerHTML = ''; return; }
  try {
    const res = await fetch(`/admin/loans/lookup?loan_number=${encodeURIComponent(val)}`, {
      headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
    });
    const d = await res.json();
    el.innerHTML = `<div style="background:#f0fdf4;border-radius:8px;padding:10px 12px">
      <div style="font-weight:700;color:#059669">${d.loan_number}</div>
      <div style="color:#374151">${d.borrower_name}</div>
      <div style="color:#ef4444;font-size:12px;margin-top:4px">Due: M${parseFloat(d.next_due||0).toFixed(2)}</div>
      <button type="button" onclick="addRow('${d.loan_number}','${d.next_due||''}','')" class="btn btn-sm btn-ok" style="margin-top:8px">
        + Add to list
      </button>
    </div>`;
  } catch(e) {
    el.innerHTML = '<span style="color:#ef4444;font-size:12px">No loan found</span>';
  }
}

function loadCsv(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const lines = e.target.result.split('\n').filter(l => l.trim());
    const header = lines.shift().toLowerCase();
    lines.forEach(line => {
      const cols = line.split(',').map(c => c.trim().replace(/^"|"$/g,''));
      if (cols[0]) addRow(cols[0], cols[1]||'', cols[2]||'');
    });
  };
  reader.readAsText(file);
  input.value = '';
}

// Start with 5 empty rows
for (let i = 0; i < 5; i++) addRow();
</script>
@endsection
