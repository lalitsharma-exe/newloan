@extends('officer.layouts.app')
@section('title','Affordability – '.$application->application_number)
@section('page-title','Affordability Assessment')
@section('bc','<a href="'.route('officer.applications.assigned').'">Applications</a> / <a href="'.route('officer.applications.show',$application).'">#'.$application->application_number.'</a> / Affordability')

@section('content')
@php $a = $application->affordability; @endphp

<div style="max-width:900px">

<div class="alert a-i" style="margin-bottom:20px">
  <i class="bi bi-info-circle-fill"></i>
  <div>Complete the affordability assessment for <strong>{{ $application->applicant_name }}</strong>. Results calculate in real-time.</div>
</div>

@if(session('success'))
<div class="alert a-ok" style="margin-bottom:18px"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if($errors->any())
<div class="alert a-e" style="margin-bottom:18px"><i class="bi bi-exclamation-circle-fill"></i>
  <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
</div>
@endif

<form method="POST" action="{{ route('officer.applications.affordability.save', $application) }}" id="affordForm">
@csrf @method('POST')

<div class="card" style="margin-bottom:18px">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-cash-stack" style="color:var(--p)"></i> Income & Deductions</span>
  </div>
  <div class="card-body">
    <div class="g2" style="gap:16px">
      <div class="fg">
        <label class="fl">Total Gross Earnings (M) *</label>
        <div style="position:relative">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--muted)">M</span>
          <input type="number" name="monthly_earnings" id="fe_earn" class="fc" style="padding-left:28px"
            step="0.01" min="0" value="{{ old('monthly_earnings',$a?->monthly_earnings??'') }}" oninput="calcAfford()" required>
        </div>
        @error('monthly_earnings')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Tax Deduction (M)</label>
        <div style="position:relative">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--muted)">M</span>
          <input type="number" name="tax_deduction" id="fe_tax" class="fc" style="padding-left:28px"
            step="0.01" min="0" value="{{ old('tax_deduction',$a?->tax_deduction??0) }}" oninput="calcAfford()">
        </div>
      </div>
      <div class="fg">
        <label class="fl">Existing Loan Deductions (M)</label>
        <div style="position:relative">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--muted)">M</span>
          <input type="number" name="existing_loans_deduction" id="fe_loans" class="fc" style="padding-left:28px"
            step="0.01" min="0" value="{{ old('existing_loans_deduction',$a?->existing_loans_deduction??0) }}" oninput="calcAfford()">
        </div>
      </div>
      <div class="fg">
        <label class="fl">Other Monthly Deductions (M)</label>
        <div style="position:relative">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--muted)">M</span>
          <input type="number" name="other_deductions" id="fe_other_ded" class="fc" style="padding-left:28px"
            step="0.01" min="0" value="{{ old('other_deductions',$a?->other_deductions??0) }}" oninput="calcAfford()">
        </div>
      </div>
    </div>

    {{-- Net Pay (calculated) --}}
    <div id="netPayBox" style="background:rgba(26,92,46,.05);border:1px solid rgba(26,92,46,.15);border-radius:10px;padding:14px 18px;margin-top:6px;display:flex;align-items:center;justify-content:space-between">
      <span style="font-weight:600;color:var(--muted)"><i class="bi bi-calculator"></i> Net Pay (auto-calculated)</span>
      <span style="font-size:22px;font-weight:800;color:var(--p)" id="netPayDisplay">M—</span>
    </div>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-house" style="color:var(--p)"></i> Monthly Living Expenses</span>
    <span style="font-size:12px;color:var(--muted)">Total: <strong id="totalExpDisplay">M0.00</strong></span>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
      @php
      $expFields = [
        'transport'            => ['Transport', 'truck'],
        'groceries'            => ['Groceries', 'cart3'],
        'utilities'            => ['Utilities', 'lightning-charge'],
        'rent'                 => ['Rent / Bond', 'house'],
        'education'            => ['Education', 'mortarboard'],
        'communication'        => ['Communication', 'phone'],
        'medical'              => ['Medical / Insurance', 'heart-pulse'],
        'family_support'       => ['Family Support', 'people'],
        'other_loan_repayments'=> ['Other Loan Repayments', 'cash-coin'],
        'other_expenses'       => ['Other Expenses', 'three-dots'],
      ];
      @endphp
      @foreach($expFields as $field => [$label, $icon])
      <div class="fg" style="margin-bottom:0">
        <label class="fl" style="display:flex;align-items:center;gap:5px">
          <i class="bi bi-{{ $icon }}" style="color:var(--p);font-size:13px"></i> {{ $label }} (M)
        </label>
        <div style="position:relative">
          <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--muted);font-size:12px">M</span>
          <input type="number" name="{{ $field }}" id="fe_{{ $field }}" class="fc expense-input" style="padding-left:24px"
            step="0.01" min="0" value="{{ old($field,$a?->$field??0) }}" oninput="calcAfford()">
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>

{{-- Results --}}
<div class="card" style="margin-bottom:20px">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-graph-up" style="color:var(--p)"></i> Assessment Results</span></div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px">
      <div style="background:#f8fafc;border-radius:12px;padding:18px;text-align:center;border:1px solid var(--border)">
        <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;margin-bottom:6px">Gross Earnings</div>
        <div style="font-size:22px;font-weight:800;color:var(--dark)" id="r_earn">M—</div>
      </div>
      <div style="background:#f8fafc;border-radius:12px;padding:18px;text-align:center;border:1px solid var(--border)">
        <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;margin-bottom:6px">Net Pay</div>
        <div style="font-size:22px;font-weight:800;color:var(--p)" id="r_net">M—</div>
      </div>
      <div style="background:#f8fafc;border-radius:12px;padding:18px;text-align:center;border:1px solid var(--border)">
        <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;margin-bottom:6px">Disposable Income</div>
        <div style="font-size:22px;font-weight:800" id="r_disp">M—</div>
      </div>
      <div style="background:#f8fafc;border-radius:12px;padding:18px;text-align:center;border:1px solid var(--border)">
        <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;margin-bottom:6px">Suggested Loan</div>
        <div style="font-size:22px;font-weight:800;color:var(--ok)" id="r_sugg">M—</div>
        <div style="font-size:10px;color:var(--muted);margin-top:3px">~60% of disposable</div>
      </div>
    </div>

    <div id="affordMessage" style="margin-top:14px;border-radius:10px;padding:14px 18px;font-size:13.5px;display:none"></div>

    {{-- Requested vs affordable comparison --}}
    @if($application->requested_amount)
    <div id="comparisonBox" style="margin-top:14px;background:#f8fafc;border-radius:10px;padding:14px 18px;font-size:13px">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <span style="color:var(--muted);font-weight:600">Requested Amount</span>
        <strong style="font-size:16px">M{{ number_format($application->requested_amount,2) }}</strong>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
        <span style="color:var(--muted);font-weight:600">Suggested Loan Amount</span>
        <strong id="compSugg" style="font-size:16px">—</strong>
      </div>
      <div id="compResult" style="margin-top:10px;font-size:12.5px"></div>
    </div>
    @endif
  </div>
</div>

<div style="display:flex;justify-content:space-between">
  <a href="{{ route('officer.applications.show', $application) }}" class="btn btn-o"><i class="bi bi-arrow-left"></i> Back to Application</a>
  <button type="submit" class="btn btn-p"><i class="bi bi-save"></i> Save Affordability</button>
</div>
</form>
</div>

@push('scripts')
<script>
const reqAmount = {{ $application->requested_amount ?? 0 }};

function calcAfford(){
  const earn  = parseFloat(document.getElementById('fe_earn')?.value)||0;
  const tax   = parseFloat(document.getElementById('fe_tax')?.value)||0;
  const loans = parseFloat(document.getElementById('fe_loans')?.value)||0;
  const oDed  = parseFloat(document.getElementById('fe_other_ded')?.value)||0;

  const expFields = ['transport','groceries','utilities','rent','education','communication','medical','family_support','other_loan_repayments','other_expenses'];
  let totalExp = 0;
  expFields.forEach(f=>{
    const el = document.getElementById('fe_'+f);
    if(el) totalExp += parseFloat(el.value)||0;
  });

  const netPay = earn - tax - loans - oDed;
  const disposable = netPay - totalExp;
  const suggested = Math.max(0, disposable * 0.6);

  document.getElementById('netPayDisplay').textContent = 'M'+netPay.toFixed(2);
  document.getElementById('totalExpDisplay').textContent = 'M'+totalExp.toFixed(2);
  document.getElementById('r_earn').textContent = 'M'+earn.toFixed(2);
  document.getElementById('r_net').textContent = 'M'+netPay.toFixed(2);

  const dispEl = document.getElementById('r_disp');
  dispEl.textContent = 'M'+disposable.toFixed(2);
  dispEl.style.color = disposable >= 0 ? 'var(--p)' : 'var(--err)';

  document.getElementById('r_sugg').textContent = 'M'+suggested.toFixed(2);
  const suggEl = document.getElementById('compSugg');
  if(suggEl) suggEl.textContent = 'M'+suggested.toFixed(2);

  const msgEl = document.getElementById('affordMessage');
  if(earn > 0){
    msgEl.style.display = 'block';
    if(disposable >= 0){
      msgEl.style.background='rgba(16,185,129,.08)';
      msgEl.style.border='1px solid rgba(16,185,129,.25)';
      msgEl.style.color='#065f46';
      msgEl.innerHTML='<i class="bi bi-check-circle-fill"></i> <strong>Passes affordability check.</strong> Disposable income is positive.';
    } else {
      msgEl.style.background='rgba(239,68,68,.08)';
      msgEl.style.border='1px solid rgba(239,68,68,.25)';
      msgEl.style.color='#991b1b';
      msgEl.innerHTML='<i class="bi bi-exclamation-circle-fill"></i> <strong>Fails affordability check.</strong> Expenses exceed net pay.';
    }
  }

  // Comparison
  const compEl = document.getElementById('compResult');
  if(compEl && reqAmount > 0 && suggested > 0){
    if(reqAmount <= suggested){
      compEl.innerHTML='<span style="color:var(--ok)"><i class="bi bi-check-circle-fill"></i> Requested amount is within affordable range</span>';
    } else {
      const diff = reqAmount - suggested;
      compEl.innerHTML=`<span style="color:var(--err)"><i class="bi bi-exclamation-circle-fill"></i> Requested M${reqAmount.toFixed(2)} exceeds suggested by M${diff.toFixed(2)}</span>`;
    }
  }
}

// Run on load
calcAfford();
</script>
@endpush
@endsection
