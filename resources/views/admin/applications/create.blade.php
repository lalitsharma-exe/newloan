@extends('admin.layouts.app')
@section('title','New Application')
@section('page-title','New Application')
@section('bc','<a href="'.route('admin.applications.index').'">Applications</a> / Create')
@section('content')

<div style="max-width:900px">

@if($errors->any())
<div style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);color:#991b1b;padding:14px 18px;border-radius:12px;font-size:13px;margin-bottom:22px;display:flex;gap:10px">
  <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0;margin-top:1px"></i>
  <ul style="margin:0;padding-left:16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.applications.store') }}" id="appForm">
@csrf

{{-- Step indicators --}}
<div style="display:flex;gap:0;margin-bottom:30px;background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden">
  @foreach([['1','person','Applicant'],['2','currency-dollar','Loan Details'],['3','file-earmark','Review']] as [$n,$icon,$label])
  <div id="step-ind-{{ $n }}" style="flex:1;padding:14px 18px;display:flex;align-items:center;gap:10px;border-right:1px solid var(--border);cursor:pointer;transition:background .2s;{{ $loop->first?'background:rgba(79,70,229,.05)':'' }}" onclick="goStep({{ $n }})">
    <div id="step-circle-{{ $n }}" style="width:30px;height:30px;border-radius:50%;background:{{ $loop->first?'var(--p)':'var(--border)' }};display:flex;align-items:center;justify-content:center;color:{{ $loop->first?'#fff':'var(--muted)' }};font-weight:700;font-size:13px;flex-shrink:0;transition:all .3s">{{ $n }}</div>
    <div>
      <div style="font-size:13px;font-weight:600;color:{{ $loop->first?'var(--p)':'var(--muted)' }}" id="step-lbl-{{ $n }}">{{ $label }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- STEP 1: Applicant --}}
<div id="step-1" class="step-panel">
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-person-fill" style="color:var(--p)"></i> Applicant Details</span>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
      <div class="fg">
        <label class="fl">Borrower *</label>
        <select name="user_id" class="fc" id="borrowerSelect" required onchange="fetchBorrowerInfo(this.value)">
          <option value="">— Select Borrower —</option>
          @foreach($borrowers as $b)
          <option value="{{ $b->id }}" {{ old('user_id')==$b->id?'selected':'' }}>{{ $b->name }} ({{ $b->email }})</option>
          @endforeach
        </select>
        @error('user_id')<span class="iv">{{ $message }}</span>@enderror
        <div class="ft">Select an existing borrower account, or <a href="{{ route('admin.users.create') }}" target="_blank" style="color:var(--p)">create one first</a></div>
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

    <div style="border-top:1px solid var(--border);padding-top:18px;margin-top:6px">
      <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px">Personal Info (for application)</div>
      <div style="display:grid;grid-template-columns:80px 1fr 1fr;gap:12px;margin-bottom:14px">
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Title</label>
          <select name="title" class="fc">
            <option value="">—</option>
            @foreach(['Mr','Mrs','Ms','Dr','Prof'] as $t)<option {{ old('title')===$t?'selected':'' }}>{{ $t }}</option>@endforeach
          </select>
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">First Name *</label>
          <input type="text" name="first_name" class="fc @error('first_name') err @enderror" value="{{ old('first_name') }}" required>
          @error('first_name')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Surname *</label>
          <input type="text" name="surname" class="fc @error('surname') err @enderror" value="{{ old('surname') }}" required>
          @error('surname')<span class="iv">{{ $message }}</span>@enderror
        </div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px">
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
            <option {{ old('gender')==='male'?'selected':'' }} value="male">Male</option>
            <option {{ old('gender')==='female'?'selected':'' }} value="female">Female</option>
            <option {{ old('gender')==='other'?'selected':'' }} value="other">Other</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Marital Status</label>
          <select name="marital_status" class="fc">
            <option value="">—</option>
            @foreach(['single','married','divorced','widowed'] as $ms)<option value="{{ $ms }}" {{ old('marital_status')===$ms?'selected':'' }}>{{ ucfirst($ms) }}</option>@endforeach
          </select>
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Cell Number</label>
          <input type="text" name="cell_number" class="fc" value="{{ old('cell_number') }}" placeholder="+266 5000 0000">
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">Email (for app)</label>
          <input type="email" name="email" class="fc" value="{{ old('email') }}">
        </div>
      </div>
    </div>
  </div>
</div>

<div style="display:flex;justify-content:flex-end">
  <button type="button" onclick="goStep(2)" class="btn btn-p">Next: Loan Details <i class="bi bi-arrow-right"></i></button>
</div>
</div>

{{-- STEP 2: Loan Details --}}
<div id="step-2" class="step-panel" style="display:none">
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-bank" style="color:var(--p)"></i> Loan Details</span>
  </div>
  <div class="card-body">

    {{-- Product selector cards --}}
    <div class="fg">
      <label class="fl">Loan Product *</label>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px" id="productCards">
        @foreach($products as $prod)
        <label style="cursor:pointer">
          <input type="radio" name="loan_product_id" value="{{ $prod->id }}" {{ old('loan_product_id')==$prod->id?'checked':'' }} class="prod-radio" style="display:none" data-rate="{{ $prod->interest_rate }}" data-min="{{ $prod->min_amount }}" data-max="{{ $prod->max_amount }}" data-minterms="{{ $prod->min_term_months }}" data-maxterms="{{ $prod->max_term_months }}">
          <div class="prod-card" style="border:2px solid {{ old('loan_product_id')==$prod->id?'var(--p)':'var(--border)' }};border-radius:13px;padding:14px;transition:all .2s">
            <div style="font-weight:700;font-size:13px;margin-bottom:4px">{{ $prod->name }}</div>
            <div style="font-size:11.5px;color:var(--muted)">{{ $prod->interest_rate }}% p.m.</div>
            <div style="font-size:11px;color:var(--muted);margin-top:3px">L{{ number_format($prod->min_amount,0) }} – L{{ number_format($prod->max_amount,0) }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $prod->min_term_months }}–{{ $prod->max_term_months }} months</div>
          </div>
        </label>
        @endforeach
      </div>
      @error('loan_product_id')<span class="iv">{{ $message }}</span>@enderror
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:6px">
      <div class="fg">
        <label class="fl">Requested Amount (L) *</label>
        <input type="number" name="requested_amount" id="reqAmount" class="fc @error('requested_amount') err @enderror" value="{{ old('requested_amount') }}" step="0.01" min="1" required oninput="calcMonthly()">
        <span class="ft" id="amtHint"></span>
        @error('requested_amount')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Term (months) *</label>
        <input type="number" name="requested_term" id="reqTerm" class="fc @error('requested_term') err @enderror" value="{{ old('requested_term',12) }}" min="1" max="120" required oninput="calcMonthly()">
        <span class="ft" id="termHint"></span>
        @error('requested_term')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Payout Method *</label>
        <select name="payout_method" class="fc" required>
          <option value="">— Select —</option>
          @foreach(['bank_transfer'=>'Bank Transfer','cash'=>'Cash','mobile_money'=>'Mobile Money','cheque'=>'Cheque'] as $v=>$l)
          <option value="{{ $v }}" {{ old('payout_method')===$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
        @error('payout_method')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Collection Method *</label>
        <select name="collection_method" class="fc" required>
          <option value="">— Select —</option>
          @foreach(['salary_deduction'=>'Salary Deduction','standing_order'=>'Standing Order','cash'=>'Cash','mobile_money'=>'Mobile Money'] as $v=>$l)
          <option value="{{ $v }}" {{ old('collection_method')===$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
        @error('collection_method')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg" style="grid-column:span 2">
        <label class="fl">Loan Purpose *</label>
        <textarea name="loan_purpose" class="fc @error('loan_purpose') err @enderror" rows="2" required>{{ old('loan_purpose') }}</textarea>
        @error('loan_purpose')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg" style="grid-column:span 2">
        <label class="fl">Admin Notes (internal)</label>
        <textarea name="admin_notes" class="fc" rows="2" placeholder="Internal notes about this application…">{{ old('admin_notes') }}</textarea>
      </div>
    </div>

    {{-- Live calc preview --}}
    <div id="calcPreview" style="display:none;background:linear-gradient(135deg,rgba(79,70,229,.06),rgba(14,165,233,.04));border:1px solid rgba(79,70,229,.15);border-radius:13px;padding:16px;margin-top:6px">
      <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px"><i class="bi bi-calculator"></i> Repayment Estimate</div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;text-align:center">
        <div><div style="font-size:11px;color:var(--muted)">Monthly Payment</div><div style="font-size:20px;font-weight:800;color:var(--p)" id="previewMonthly">—</div></div>
        <div><div style="font-size:11px;color:var(--muted)">Total Repayment</div><div style="font-size:20px;font-weight:800;color:var(--dark)" id="previewTotal">—</div></div>
        <div><div style="font-size:11px;color:var(--muted)">Total Interest</div><div style="font-size:20px;font-weight:800;color:#f59e0b" id="previewInterest">—</div></div>
        <div><div style="font-size:11px;color:var(--muted)">Interest Rate</div><div style="font-size:20px;font-weight:800;color:var(--muted)" id="previewRate">—</div></div>
      </div>
    </div>

  </div>
</div>

<div style="display:flex;justify-content:space-between">
  <button type="button" onclick="goStep(1)" class="btn btn-o"><i class="bi bi-arrow-left"></i> Back</button>
  <button type="button" onclick="goStep(3)" class="btn btn-p">Next: Review <i class="bi bi-arrow-right"></i></button>
</div>
</div>

{{-- STEP 3: Review --}}
<div id="step-3" class="step-panel" style="display:none">
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-check-circle" style="color:var(--p)"></i> Review & Submit</span>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
      <div>
        <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border)">Applicant</div>
        <div id="rev-applicant" style="font-size:13.5px;line-height:2"></div>
      </div>
      <div>
        <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border)">Loan</div>
        <div id="rev-loan" style="font-size:13.5px;line-height:2"></div>
      </div>
    </div>
    <div style="background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.2);border-radius:11px;padding:14px 16px;margin-top:18px;display:flex;align-items:center;gap:11px">
      <i class="bi bi-info-circle-fill" style="color:#10b981;font-size:18px;flex-shrink:0"></i>
      <div style="font-size:13px;color:#065f46">Application will be created with status <strong>Submitted</strong>. You can then approve, decline, or assign it to a loan officer for review.</div>
    </div>
  </div>
</div>

<div style="display:flex;justify-content:space-between">
  <button type="button" onclick="goStep(2)" class="btn btn-o"><i class="bi bi-arrow-left"></i> Back</button>
  <button type="submit" class="btn btn-ok" style="padding:11px 28px;font-size:14px">
    <i class="bi bi-check-lg"></i> Submit Application
  </button>
</div>
</div>

</form>
</div>

<script>
let currentStep = 1;
let selectedRate = 0;

function goStep(n) {
  // Validate step 1 before moving forward
  if (n > 1 && currentStep === 1) {
    const uid = document.querySelector('[name=user_id]').value;
    const fn  = document.querySelector('[name=first_name]').value;
    const sn  = document.querySelector('[name=surname]').value;
    if (!uid || !fn || !sn) { alert('Please fill in Borrower, First Name and Surname.'); return; }
  }
  if (n > 2 && currentStep <= 2) {
    const pid = document.querySelector('[name=loan_product_id]:checked');
    const amt = document.querySelector('[name=requested_amount]').value;
    const trm = document.querySelector('[name=requested_term]').value;
    const pp  = document.querySelector('[name=payout_method]').value;
    const pm  = document.querySelector('[name=collection_method]').value;
    const lp  = document.querySelector('[name=loan_purpose]').value;
    if (!pid || !amt || !trm || !pp || !pm || !lp) { alert('Please complete all required loan fields.'); return; }
    buildReview();
  }
  document.querySelectorAll('.step-panel').forEach(p => p.style.display = 'none');
  document.getElementById('step-' + n).style.display = 'block';
  currentStep = n;

  // Update indicators
  for (let i = 1; i <= 3; i++) {
    const active = i <= n;
    const circle = document.getElementById('step-circle-' + i);
    const lbl    = document.getElementById('step-lbl-' + i);
    const ind    = document.getElementById('step-ind-' + i);
    circle.style.background = i === n ? 'var(--p)' : (i < n ? '#10b981' : 'var(--border)');
    circle.style.color      = i <= n ? '#fff' : 'var(--muted)';
    if (i < n) circle.innerHTML = '<i class="bi bi-check-lg" style="font-size:13px"></i>';
    else circle.textContent = i;
    lbl.style.color = i === n ? 'var(--p)' : (i < n ? '#10b981' : 'var(--muted)');
    ind.style.background = i === n ? 'rgba(79,70,229,.05)' : 'transparent';
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function buildReview() {
  const name    = (document.querySelector('[name=title]').value + ' ' + document.querySelector('[name=first_name]').value + ' ' + document.querySelector('[name=surname]').value).trim();
  const borrower= document.querySelector('[name=user_id] option:checked').text;
  const product = document.querySelector('[name=loan_product_id]:checked')?.closest('label').querySelector('.prod-card div').textContent ?? '—';
  const amount  = parseFloat(document.querySelector('[name=requested_amount]').value || 0);
  const term    = document.querySelector('[name=requested_term]').value;
  const pout    = document.querySelector('[name=payout_method] option:checked').text;
  const coll    = document.querySelector('[name=collection_method] option:checked').text;
  const monthly = calcMonthly(true);

  document.getElementById('rev-applicant').innerHTML =
    `<b>Name:</b> ${name}<br><b>Account:</b> ${borrower}<br><b>Cell:</b> ${document.querySelector('[name=cell_number]').value || '—'}<br><b>Email:</b> ${document.querySelector('[name=email]').value || '—'}`;

  document.getElementById('rev-loan').innerHTML =
    `<b>Product:</b> ${product}<br><b>Amount:</b> L ${amount.toLocaleString()}<br><b>Term:</b> ${term} months<br><b>Est. Monthly:</b> L ${monthly}<br><b>Payout:</b> ${pout}<br><b>Collection:</b> ${coll}`;
}

// Product card selection
document.querySelectorAll('.prod-radio').forEach(r => {
  r.addEventListener('change', function() {
    document.querySelectorAll('.prod-card').forEach(c => { c.style.borderColor = 'var(--border)'; c.style.background = ''; });
    this.nextElementSibling.style.borderColor = 'var(--p)';
    this.nextElementSibling.style.background  = 'rgba(79,70,229,.04)';
    selectedRate = parseFloat(this.dataset.rate);
    document.getElementById('amtHint').textContent  = `L${Number(this.dataset.min).toLocaleString()} – L${Number(this.dataset.max).toLocaleString()}`;
    document.getElementById('termHint').textContent = `${this.dataset.minterms} – ${this.dataset.maxterms} months`;
    calcMonthly();
  });
});

function calcMonthly(returnVal = false) {
  const amount = parseFloat(document.getElementById('reqAmount')?.value || 0);
  const term   = parseInt(document.getElementById('reqTerm')?.value || 0);
  const rate   = selectedRate / 100;
  if (!amount || !term || !selectedRate) { if (!returnVal) document.getElementById('calcPreview').style.display = 'none'; return '—'; }

  const monthly = rate > 0
    ? amount * (rate * Math.pow(1+rate,term)) / (Math.pow(1+rate,term)-1)
    : amount / term;
  const total    = monthly * term;
  const interest = total - amount;

  if (!returnVal) {
    document.getElementById('calcPreview').style.display  = 'block';
    document.getElementById('previewMonthly').textContent  = 'L ' + monthly.toFixed(2);
    document.getElementById('previewTotal').textContent    = 'L ' + total.toFixed(2);
    document.getElementById('previewInterest').textContent = 'L ' + interest.toFixed(2);
    document.getElementById('previewRate').textContent     = selectedRate + '%/mo';
  }
  return monthly.toFixed(2);
}
</script>
@endsection