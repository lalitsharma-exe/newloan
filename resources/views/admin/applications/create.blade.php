@extends('admin.layouts.app')
@section('title','New Application')
@section('page-title','New Application')
@section('bc')
<a href="{{ route('admin.applications.index') }}">Applications</a> / Create
@endsection
@section('content')

<div style="max-width:920px">

@if($errors->any())
<div class="alert a-e"><i class="bi bi-exclamation-triangle-fill"></i>
  <ul style="margin:0;padding-left:16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.applications.store') }}" id="appForm">
@csrf

{{-- ── STEP INDICATORS (4 steps) ─────────────────────────── --}}
<div style="display:flex;gap:0;margin-bottom:28px;background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden">
  @foreach([['1','person-fill','Applicant'],['2','calculator-fill','Affordability'],['3','bank','Loan Details'],['4','check-circle-fill','Review']] as [$n,$icon,$label])
  <div id="step-ind-{{ $n }}" onclick="goStep({{ $n }})"
    style="flex:1;padding:13px 16px;display:flex;align-items:center;gap:10px;border-right:1px solid var(--border);cursor:pointer;transition:background .2s;{{ $loop->first?'background:rgba(26,92,46,.05)':'' }}{{ $loop->last?'border-right:none':'' }}">
    <div id="step-circle-{{ $n }}"
      style="width:28px;height:28px;border-radius:50%;background:{{ $loop->first?'var(--p)':'var(--border)' }};display:flex;align-items:center;justify-content:center;color:{{ $loop->first?'#fff':'var(--muted)' }};font-weight:700;font-size:12px;flex-shrink:0;transition:all .3s">
      {{ $n }}
    </div>
    <div style="font-size:13px;font-weight:600;color:{{ $loop->first?'var(--p)':'var(--muted)' }}" id="step-lbl-{{ $n }}">{{ $label }}</div>
  </div>
  @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════
     STEP 1 — APPLICANT
══════════════════════════════════════════════════════════ --}}
<div id="step-1" class="step-panel">
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-person-fill" style="color:var(--p)"></i> Applicant Details</span></div>
  <div class="card-body">

    <div class="g2" style="gap:16px;margin-bottom:16px">
      <div class="fg">
        <label class="fl">Borrower Account *</label>
        <select name="user_id" class="fc" id="borrowerSelect" required onchange="fetchBorrowerInfo(this.value)">
          <option value="">— Select Borrower —</option>
          @foreach($borrowers as $b)
          <option value="{{ $b->id }}" data-name="{{ $b->name }}" data-email="{{ $b->email }}" data-phone="{{ $b->phone }}"
            {{ old('user_id')==$b->id?'selected':'' }}>
            {{ $b->name }} ({{ $b->email }})
          </option>
          @endforeach
        </select>
        @error('user_id')<span class="iv">{{ $message }}</span>@enderror
        <span class="ft">Or <a href="{{ route('admin.users.create') }}" target="_blank" style="color:var(--p)">create a new borrower</a></span>
      </div>
      <div class="fg">
        <label class="fl">Assigned Officer</label>
        <select name="assigned_officer_id" class="fc">
          <option value="">— Unassigned —</option>
          @foreach($officers as $o)
          <option value="{{ $o->id }}" {{ old('assigned_officer_id')==$o->id?'selected':'' }}>{{ $o->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Auto-filled from borrower — read-only --}}
    <div style="background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:14px" id="borrowerInfoBox" style="display:none">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px">
        <i class="bi bi-lock-fill" style="color:var(--p)"></i> Borrower Info (auto-filled, read-only)
      </div>
      <div class="g2" style="gap:12px">
        <div class="fg" style="margin-bottom:0">
          <label class="fl">First Name *</label>
          <input type="text" name="first_name" id="fieldFirstName" class="fc" value="{{ old('first_name') }}" required readonly style="background:#f1f5f9;cursor:not-allowed">
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Surname *</label>
          <input type="text" name="surname" id="fieldSurname" class="fc" value="{{ old('surname') }}" required readonly style="background:#f1f5f9;cursor:not-allowed">
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Cell Number</label>
          <input type="text" name="cell_number" id="fieldCell" class="fc" value="{{ old('cell_number') }}" readonly style="background:#f1f5f9;cursor:not-allowed">
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Email</label>
          <input type="email" name="email" id="fieldEmail" class="fc" value="{{ old('email') }}" readonly style="background:#f1f5f9;cursor:not-allowed">
        </div>
      </div>
    </div>

    {{-- Editable personal fields --}}
    <div class="g2" style="gap:12px">
      <div class="fg" style="margin-bottom:0">
        <label class="fl">Title</label>
        <select name="title" class="fc">
          <option value="">—</option>
          @foreach(['Mr','Mrs','Ms','Dr','Prof'] as $t)<option {{ old('title')===$t?'selected':'' }}>{{ $t }}</option>@endforeach
        </select>
      </div>
      <div class="fg" style="margin-bottom:0">
        <label class="fl">National ID</label>
        <input type="text" name="national_id" class="fc" value="{{ old('national_id') }}">
      </div>
      <div class="fg" style="margin-bottom:0">
        <label class="fl">Date of Birth</label>
        <input type="date" name="date_of_birth" class="fc" value="{{ old('date_of_birth') }}">
      </div>
      <div class="fg" style="margin-bottom:0">
        <label class="fl">Gender</label>
        <select name="gender" class="fc">
          <option value="">—</option>
          <option value="male"   {{ old('gender')==='male'  ?'selected':'' }}>Male</option>
          <option value="female" {{ old('gender')==='female'?'selected':'' }}>Female</option>
          <option value="other"  {{ old('gender')==='other' ?'selected':'' }}>Other</option>
        </select>
      </div>
      <div class="fg" style="margin-bottom:0">
        <label class="fl">Marital Status</label>
        <select name="marital_status" class="fc">
          <option value="">—</option>
          @foreach(['single','married','divorced','widowed'] as $ms)
          <option value="{{ $ms }}" {{ old('marital_status')===$ms?'selected':'' }}>{{ ucfirst($ms) }}</option>
          @endforeach
        </select>
      </div>
    </div>

  </div>
</div>
<div style="display:flex;justify-content:flex-end">
  <button type="button" onclick="goStep(2)" class="btn btn-p">Next: Affordability <i class="bi bi-arrow-right"></i></button>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════
     STEP 2 — AFFORDABILITY
══════════════════════════════════════════════════════════ --}}
<div id="step-2" class="step-panel" style="display:none">
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-calculator-fill" style="color:var(--p)"></i> Affordability Assessment</span>
    <span class="badge bs">30% of net salary rule</span>
  </div>
  <div class="card-body">

    <div class="g2" style="gap:16px;margin-bottom:18px">
      <div class="fg">
        <label class="fl">Gross Monthly Salary (M)</label>
        <input type="number" id="grossSalary" class="fc" placeholder="0.00" step="0.01" min="0" oninput="calcAffordability()">
      </div>
      <div class="fg">
        <label class="fl">Net Monthly Salary (M) *</label>
        <input type="number" id="netSalary" name="net_salary_preview" class="fc" placeholder="0.00" step="0.01" min="0" oninput="calcAffordability()" required>
        <span class="ft">After tax and all deductions</span>
      </div>
      <div class="fg">
        <label class="fl">Existing Loan Deductions (M)</label>
        <input type="number" id="existingLoans" class="fc" placeholder="0.00" step="0.01" min="0" value="0" oninput="calcAffordability()">
      </div>
      <div class="fg">
        <label class="fl">Other Monthly Deductions (M)</label>
        <input type="number" id="otherDeductions" class="fc" placeholder="0.00" step="0.01" min="0" value="0" oninput="calcAffordability()">
      </div>
    </div>

    {{-- Affordability result box --}}
    <div id="affordResult" style="display:none;border-radius:12px;padding:18px;margin-bottom:14px">
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;text-align:center;margin-bottom:14px">
        <div style="background:#fff;border-radius:10px;padding:14px;border:1px solid var(--border)">
          <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase">Net Salary</div>
          <div style="font-size:22px;font-weight:800;color:var(--dark)" id="ar-net">M0</div>
        </div>
        <div style="background:#fff;border-radius:10px;padding:14px;border:1px solid var(--border)">
          <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase">30% Limit</div>
          <div style="font-size:22px;font-weight:800;color:var(--p)" id="ar-limit">M0</div>
        </div>
        <div style="background:#fff;border-radius:10px;padding:14px;border:1px solid var(--border)">
          <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase">You Qualify For</div>
          <div style="font-size:22px;font-weight:800" id="ar-qualify">M0</div>
        </div>
      </div>
      <div id="ar-message" style="font-size:13px;padding:10px 14px;border-radius:8px;text-align:center"></div>
    </div>

    {{-- Hidden field to carry affordability data to step 3 --}}
    <input type="hidden" name="affordability_net_salary" id="hiddenNetSalary">
    <input type="hidden" name="affordability_max_loan" id="hiddenMaxLoan">

  </div>
</div>
<div style="display:flex;justify-content:space-between">
  <button type="button" onclick="goStep(1)" class="btn btn-o"><i class="bi bi-arrow-left"></i> Back</button>
  <button type="button" onclick="goStep(3)" class="btn btn-p">Next: Loan Details <i class="bi bi-arrow-right"></i></button>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════
     STEP 3 — LOAN DETAILS
══════════════════════════════════════════════════════════ --}}
<div id="step-3" class="step-panel" style="display:none">
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-bank" style="color:var(--p)"></i> Loan Details</span></div>
  <div class="card-body">

    <div class="fg">
      <label class="fl">Loan Product *</label>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px" id="productCards">
        @foreach($products as $prod)
        <label style="cursor:pointer">
          <input type="radio" name="loan_product_id" value="{{ $prod->id }}"
            {{ old('loan_product_id')==$prod->id?'checked':'' }}
            class="prod-radio" style="display:none"
            data-rate="{{ $prod->interest_rate }}"
            data-initiation="{{ $prod->initiation_fee_rate ?? 40 }}"
            data-admin="{{ $prod->admin_fee_fixed ?? 50 }}"
            data-min="{{ $prod->min_amount }}"
            data-max="{{ $prod->max_amount }}"
            data-minterms="{{ $prod->min_term_months }}"
            data-maxterms="{{ $prod->max_term_months }}">
          <div class="prod-card" style="border:2px solid {{ old('loan_product_id')==$prod->id?'var(--p)':'var(--border)' }};border-radius:13px;padding:14px;transition:all .2s">
            <div style="font-weight:700;font-size:13px;margin-bottom:6px">{{ $prod->name }}</div>
            <div style="font-size:11.5px;color:var(--muted)"><i class="bi bi-percent"></i> {{ $prod->interest_rate }}%/mo flat</div>
            <div style="font-size:11px;color:var(--muted);margin-top:3px">M{{ number_format($prod->min_amount,0) }} – M{{ number_format($prod->max_amount,0) }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $prod->min_term_months }}–{{ $prod->max_term_months }} months</div>
          </div>
        </label>
        @endforeach
      </div>
      @error('loan_product_id')<span class="iv">{{ $message }}</span>@enderror
    </div>

    <div class="g2" style="gap:16px;margin-top:6px">
      <div class="fg">
        <label class="fl">Requested Amount (M) *</label>
        <div style="position:relative">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--muted)">M</span>
          <input type="number" name="requested_amount" id="reqAmount" class="fc @error('requested_amount') err @enderror"
            value="{{ old('requested_amount') }}" step="0.01" min="1" required oninput="calcMonthly()"
            style="padding-left:28px">
        </div>
        <span class="ft" id="amtHint"></span>
        <span class="ft" id="affordHint" style="color:var(--p)"></span>
        @error('requested_amount')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Term (months) *</label>
        <input type="number" name="requested_term" id="reqTerm" class="fc @error('requested_term') err @enderror"
          value="{{ old('requested_term',3) }}" min="1" max="24" required oninput="calcMonthly()">
        <span class="ft" id="termHint">Max 24 months</span>
        @error('requested_term')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Payout Method *</label>
        <select name="payout_method" class="fc" required>
          <option value="">— Select —</option>
          <option value="bank_transfer" {{ old('payout_method')==='bank_transfer'?'selected':'' }}>Bank Transfer</option>
          <option value="mobile_money"  {{ old('payout_method')==='mobile_money' ?'selected':'' }}>Mobile Money</option>
        </select>
        @error('payout_method')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Collection Method *</label>
        <select name="collection_method" class="fc" required>
          <option value="">— Select —</option>
          <option value="salary_deduction" {{ old('collection_method')==='salary_deduction'?'selected':'' }}>Salary Deduction</option>
          <option value="debit_order"      {{ old('collection_method')==='debit_order'     ?'selected':'' }}>Debit Order</option>
          <option value="card_payment"     {{ old('collection_method')==='card_payment'    ?'selected':'' }}>Card Payment</option>
          <option value="mobile_money"     {{ old('collection_method')==='mobile_money'    ?'selected':'' }}>Mobile Money</option>
          <option value="cash"             {{ old('collection_method')==='cash'            ?'selected':'' }}>Cash</option>
        </select>
        @error('collection_method')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg" style="grid-column:span 2">
        <label class="fl">Loan Purpose *</label>
        <textarea name="loan_purpose" class="fc @error('loan_purpose') err @enderror" rows="2" required>{{ old('loan_purpose') }}</textarea>
        @error('loan_purpose')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg" style="grid-column:span 2">
        <label class="fl">Internal Notes</label>
        <textarea name="admin_notes" class="fc" rows="2" placeholder="Admin notes (not visible to borrower)…">{{ old('admin_notes') }}</textarea>
      </div>
    </div>

    {{-- ── FLAT INTEREST LIVE CALCULATOR ── --}}
    <div id="calcPreview" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:13px;padding:18px;margin-top:14px">
      <div style="font-size:11px;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px">
        <i class="bi bi-calculator-fill"></i> Flat Interest Breakdown (MyLoan rules)
      </div>
      <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;text-align:center;margin-bottom:12px">
        <div style="background:#fff;border-radius:9px;padding:11px;border:1px solid #d1fae5">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Monthly</div>
          <div style="font-size:20px;font-weight:800;color:var(--p)" id="previewMonthly">—</div>
        </div>
        <div style="background:#fff;border-radius:9px;padding:11px;border:1px solid #d1fae5">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Interest</div>
          <div style="font-size:16px;font-weight:700;color:#f59e0b" id="previewInterest">—</div>
        </div>
        <div style="background:#fff;border-radius:9px;padding:11px;border:1px solid #d1fae5">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Initiation</div>
          <div style="font-size:16px;font-weight:700;color:#8b5cf6" id="previewInitiation">—</div>
        </div>
        <div style="background:#fff;border-radius:9px;padding:11px;border:1px solid #d1fae5">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Admin</div>
          <div style="font-size:16px;font-weight:700;color:#64748b" id="previewAdmin">—</div>
        </div>
        <div style="background:#fff;border-radius:9px;padding:11px;border:1px solid #d1fae5">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Total Repay</div>
          <div style="font-size:16px;font-weight:800;color:#1e3a5f" id="previewTotal">—</div>
        </div>
      </div>
      <div id="affordWarning" style="display:none;background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:9px 14px;font-size:12.5px;color:#92400e">
        <i class="bi bi-exclamation-triangle-fill"></i> <span id="affordWarningText"></span>
      </div>
    </div>

  </div>
</div>
<div style="display:flex;justify-content:space-between">
  <button type="button" onclick="goStep(2)" class="btn btn-o"><i class="bi bi-arrow-left"></i> Back</button>
  <button type="button" onclick="goStep(4)" class="btn btn-p">Next: Review <i class="bi bi-arrow-right"></i></button>
</div>
</div>

{{-- ══════════════════════════════════════════════════════════
     STEP 4 — REVIEW
══════════════════════════════════════════════════════════ --}}
<div id="step-4" class="step-panel" style="display:none">
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-check-circle-fill" style="color:var(--ok)"></i> Review & Submit</span></div>
  <div class="card-body">
    <div class="g2" style="gap:20px">
      <div>
        <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid var(--border)">Applicant</div>
        <div id="rev-applicant" style="font-size:13.5px;line-height:2.1"></div>
      </div>
      <div>
        <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid var(--border)">Loan</div>
        <div id="rev-loan" style="font-size:13.5px;line-height:2.1"></div>
      </div>
    </div>
    {{-- Flat interest summary on review --}}
    <div id="rev-calc" style="margin-top:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;display:none">
      <div style="font-size:11px;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px"><i class="bi bi-calculator-fill"></i> Repayment Summary</div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;text-align:center" id="rev-calc-grid"></div>
    </div>
    <div class="alert a-i" style="margin-top:16px;margin-bottom:0">
      <i class="bi bi-info-circle-fill"></i>
      Application will be submitted for review. You can then approve, decline, or assign it to a loan officer.
    </div>
  </div>
</div>
<div style="display:flex;justify-content:space-between">
  <button type="button" onclick="goStep(3)" class="btn btn-o"><i class="bi bi-arrow-left"></i> Back</button>
  <button type="submit" class="btn btn-ok" style="padding:11px 28px;font-size:14px">
    <i class="bi bi-check-lg"></i> Submit Application
  </button>
</div>
</div>

</form>
</div>

<script>
let currentStep = 1;
let selectedProduct = null; // holds the selected product radio element

// ── BORROWER AUTO-FILL ─────────────────────────────────────────────────────
function fetchBorrowerInfo(userId) {
  const sel = document.getElementById('borrowerSelect');
  const opt = sel.options[sel.selectedIndex];
  if (!userId || !opt) {
    document.getElementById('borrowerInfoBox').style.display = 'none';
    return;
  }
  const name  = opt.dataset.name  || '';
  const email = opt.dataset.email || '';
  const phone = opt.dataset.phone || '';
  const parts = name.trim().split(' ');
  const first = parts[0] || '';
  const last  = parts.slice(1).join(' ') || '';

  document.getElementById('fieldFirstName').value = first;
  document.getElementById('fieldSurname').value   = last;
  document.getElementById('fieldCell').value      = phone;
  document.getElementById('fieldEmail').value     = email;
  document.getElementById('borrowerInfoBox').style.display = 'block';
}

// ── AFFORDABILITY CALCULATOR ───────────────────────────────────────────────
function calcAffordability() {
  const net     = parseFloat(document.getElementById('netSalary').value) || 0;
  const existing= parseFloat(document.getElementById('existingLoans').value) || 0;
  const other   = parseFloat(document.getElementById('otherDeductions').value) || 0;
  if (!net) { document.getElementById('affordResult').style.display='none'; return; }

  const limit       = net * 0.30;
  const available   = Math.max(0, limit - existing - other);
  const qualifies   = available > 0;

  document.getElementById('ar-net').textContent     = 'M' + net.toLocaleString('en',{minimumFractionDigits:2,maximumFractionDigits:2});
  document.getElementById('ar-limit').textContent   = 'M' + limit.toFixed(2);
  document.getElementById('ar-qualify').textContent = 'M' + available.toFixed(2);
  document.getElementById('ar-qualify').style.color = qualifies ? 'var(--ok)' : 'var(--err)';

  const msg = document.getElementById('ar-message');
  if (qualifies) {
    msg.style.background = '#d1fae5'; msg.style.color = '#065f46'; msg.style.border = '1px solid #a7f3d0';
    msg.innerHTML = '<i class="bi bi-check-circle-fill"></i> Borrower qualifies for a maximum monthly installment of <strong>M' + available.toFixed(2) + '</strong>';
  } else {
    msg.style.background = '#fee2e2'; msg.style.color = '#991b1b'; msg.style.border = '1px solid #fca5a5';
    msg.innerHTML = '<i class="bi bi-x-circle-fill"></i> Borrower does not qualify — existing deductions exceed 30% limit';
  }

  document.getElementById('affordResult').style.color = 'inherit';
  document.getElementById('affordResult').style.background = 'transparent';
  document.getElementById('affordResult').style.display = 'block';

  // Carry to step 3
  document.getElementById('hiddenNetSalary').value = net;
  document.getElementById('hiddenMaxLoan').value   = available;

  // Show hint in step 3
  document.getElementById('affordHint').textContent =
    available > 0 ? 'Max monthly installment: M' + available.toFixed(2) : '';
}

// ── FLAT INTEREST CALCULATOR (matches backend exactly) ─────────────────────
// Formula: total = principal + (principal × rate × term) + (principal × initiation%) + (admin × term)
// monthly = total ÷ term

function calcMonthly() {
  const amount  = parseFloat(document.getElementById('reqAmount')?.value)  || 0;
  const term    = parseInt(document.getElementById('reqTerm')?.value)       || 0;
  if (!amount || !term || !selectedProduct) {
    document.getElementById('calcPreview').style.display = 'none'; return;
  }

  const rate        = parseFloat(selectedProduct.dataset.rate)       || 15;
  const initPct     = parseFloat(selectedProduct.dataset.initiation) || 40;
  const adminPerMo  = parseFloat(selectedProduct.dataset.admin)      || 50;

  const interest    = amount * (rate / 100) * term;
  const initiation  = amount * (initPct / 100);
  const adminTotal  = adminPerMo * term;
  const total       = amount + interest + initiation + adminTotal;
  const monthly     = total / term;

  document.getElementById('calcPreview').style.display = 'block';
  document.getElementById('previewMonthly').textContent    = 'M ' + monthly.toFixed(2);
  document.getElementById('previewInterest').textContent   = 'M ' + interest.toFixed(2);
  document.getElementById('previewInitiation').textContent = 'M ' + initiation.toFixed(2);
  document.getElementById('previewAdmin').textContent      = 'M ' + adminTotal.toFixed(2);
  document.getElementById('previewTotal').textContent      = 'M ' + total.toFixed(2);

  // Affordability warning
  const maxMonthly = parseFloat(document.getElementById('hiddenMaxLoan').value) || 0;
  const warn = document.getElementById('affordWarning');
  if (maxMonthly > 0 && monthly > maxMonthly) {
    document.getElementById('affordWarningText').textContent =
      'Monthly installment M' + monthly.toFixed(2) + ' exceeds affordability limit of M' + maxMonthly.toFixed(2);
    warn.style.display = 'block';
  } else {
    warn.style.display = 'none';
  }

  return { monthly, total, interest, initiation, adminTotal };
}

// ── PRODUCT CARD SELECTION ─────────────────────────────────────────────────
document.querySelectorAll('.prod-radio').forEach(r => {
  r.addEventListener('change', function() {
    document.querySelectorAll('.prod-card').forEach(c => {
      c.style.borderColor = 'var(--border)'; c.style.background = '';
    });
    this.nextElementSibling.style.borderColor = 'var(--p)';
    this.nextElementSibling.style.background  = 'rgba(26,92,46,.04)';
    selectedProduct = this;
    const min = Number(this.dataset.min); const max = Number(this.dataset.max);
    document.getElementById('amtHint').textContent  = 'M' + min.toLocaleString() + ' – M' + max.toLocaleString();
    document.getElementById('termHint').textContent = this.dataset.minterms + ' – ' + this.dataset.maxterms + ' months (max 24)';
    // Update term field limits
    document.getElementById('reqTerm').min = this.dataset.minterms;
    document.getElementById('reqTerm').max = Math.min(this.dataset.maxterms, 24);
    calcMonthly();
  });
});

// ── STEP NAVIGATION ────────────────────────────────────────────────────────
function goStep(n) {
  // Validate before advancing
  if (n > 1 && currentStep === 1) {
    const uid = document.querySelector('[name=user_id]').value;
    const fn  = document.getElementById('fieldFirstName').value;
    const sn  = document.getElementById('fieldSurname').value;
    if (!uid) { alert('Please select a borrower.'); return; }
    if (!fn || !sn) { alert('Borrower first name and surname are required.'); return; }
  }
  if (n > 2 && currentStep === 2) {
    const net = parseFloat(document.getElementById('netSalary').value) || 0;
    if (!net) { alert('Please enter the borrower\'s net monthly salary.'); return; }
  }
  if (n > 3 && currentStep === 3) {
    const pid = document.querySelector('[name=loan_product_id]:checked');
    const amt = document.getElementById('reqAmount').value;
    const trm = document.getElementById('reqTerm').value;
    const pp  = document.querySelector('[name=payout_method]').value;
    const pm  = document.querySelector('[name=collection_method]').value;
    const lp  = document.querySelector('[name=loan_purpose]').value;
    if (!pid || !amt || !trm || !pp || !pm || !lp) { alert('Please complete all required loan fields.'); return; }
    buildReview();
  }

  document.querySelectorAll('.step-panel').forEach(p => p.style.display = 'none');
  document.getElementById('step-' + n).style.display = 'block';
  currentStep = n;

  // Update step indicators
  const total = 4;
  for (let i = 1; i <= total; i++) {
    const circle = document.getElementById('step-circle-' + i);
    const lbl    = document.getElementById('step-lbl-' + i);
    const ind    = document.getElementById('step-ind-' + i);
    if (i < n) {
      circle.innerHTML   = '<i class="bi bi-check-lg" style="font-size:12px"></i>';
      circle.style.background = '#10b981'; circle.style.color = '#fff';
      lbl.style.color    = '#10b981';
      ind.style.background = 'transparent';
    } else if (i === n) {
      circle.textContent = i; circle.style.background = 'var(--p)'; circle.style.color = '#fff';
      lbl.style.color    = 'var(--p)';
      ind.style.background = 'rgba(26,92,46,.05)';
    } else {
      circle.textContent = i; circle.style.background = 'var(--border)'; circle.style.color = 'var(--muted)';
      lbl.style.color    = 'var(--muted)';
      ind.style.background = 'transparent';
    }
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── BUILD REVIEW PAGE ──────────────────────────────────────────────────────
function buildReview() {
  const borrower = document.querySelector('[name=user_id] option:checked').text;
  const first    = document.getElementById('fieldFirstName').value;
  const last     = document.getElementById('fieldSurname').value;
  const cell     = document.getElementById('fieldCell').value;
  const email    = document.getElementById('fieldEmail').value;
  const net      = document.getElementById('netSalary').value;

  const amount   = parseFloat(document.getElementById('reqAmount').value || 0);
  const term     = document.getElementById('reqTerm').value;
  const prodName = selectedProduct?.nextElementSibling?.querySelector('div')?.textContent?.trim() ?? '—';
  const pout     = document.querySelector('[name=payout_method] option:checked').text;
  const coll     = document.querySelector('[name=collection_method] option:checked').text;

  document.getElementById('rev-applicant').innerHTML =
    `<b>Account:</b> ${borrower}<br><b>Name:</b> ${first} ${last}<br><b>Cell:</b> ${cell||'—'}<br><b>Email:</b> ${email||'—'}<br><b>Net Salary:</b> M${parseFloat(net||0).toFixed(2)}`;

  const calc = calcMonthly();
  document.getElementById('rev-loan').innerHTML =
    `<b>Product:</b> ${prodName}<br><b>Amount:</b> M${amount.toLocaleString()}<br><b>Term:</b> ${term} months<br><b>Monthly:</b> M${calc?.monthly.toFixed(2)||'—'}<br><b>Payout:</b> ${pout}<br><b>Collection:</b> ${coll}`;

  if (calc) {
    document.getElementById('rev-calc').style.display = 'block';
    document.getElementById('rev-calc-grid').innerHTML = `
      <div style="background:#f8fafc;border-radius:8px;padding:12px;text-align:center;border:1px solid var(--border)"><div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Monthly</div><div style="font-size:18px;font-weight:800;color:var(--p)">M${calc.monthly.toFixed(2)}</div></div>
      <div style="background:#f8fafc;border-radius:8px;padding:12px;text-align:center;border:1px solid var(--border)"><div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Interest</div><div style="font-size:16px;font-weight:700;color:#f59e0b">M${calc.interest.toFixed(2)}</div></div>
      <div style="background:#f8fafc;border-radius:8px;padding:12px;text-align:center;border:1px solid var(--border)"><div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Initiation</div><div style="font-size:16px;font-weight:700;color:#8b5cf6">M${calc.initiation.toFixed(2)}</div></div>
      <div style="background:#f8fafc;border-radius:8px;padding:12px;text-align:center;border:1px solid var(--border)"><div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Total Repay</div><div style="font-size:16px;font-weight:800;color:#1e3a5f">M${calc.total.toFixed(2)}</div></div>
    `;
  }
}
</script>
@endsection
