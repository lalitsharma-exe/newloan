@extends('officer.layouts.app')
@section('title','Walk-in Application – Step '.$step)
@section('page-title','Walk-in Application')
@section('bc','<a href="'.route('officer.dashboard').'">Dashboard</a> / <a href="'.route('officer.walk-in.create').'">Walk-in</a> / Step {{ $step }}')

@section('content')
@php
$client = $application->user;
$steps = [
  1 => ['icon'=>'person-fill',       'label'=>'Personal'],
  2 => ['icon'=>'geo-alt-fill',      'label'=>'Address'],
  3 => ['icon'=>'briefcase-fill',    'label'=>'Employment'],
  4 => ['icon'=>'bank2',             'label'=>'Bank Details'],
  5 => ['icon'=>'people-fill',       'label'=>'Next of Kin'],
  6 => ['icon'=>'calculator-fill',   'label'=>'Affordability'],
  7 => ['icon'=>'cash-stack',        'label'=>'Loan Details'],
  8 => ['icon'=>'cloud-upload-fill', 'label'=>'Documents'],
  9 => ['icon'=>'check-circle-fill', 'label'=>'Review'],
];
@endphp

{{-- Client Banner --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:14px 22px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
  <div style="display:flex;align-items:center;gap:12px">
    <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:16px">{{ strtoupper(substr($client->name,0,1)) }}</div>
    <div>
      <div style="font-weight:700;font-size:15px">{{ $client->name }}</div>
      <div style="font-size:12px;color:var(--muted)">{{ $client->phone }} &nbsp;·&nbsp; ID: {{ $client->national_id }} &nbsp;·&nbsp; App: {{ $application->application_number }}</div>
    </div>
  </div>
  <span class="badge bs">Walk-in Application</span>
</div>

{{-- Step progress bar --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:24px">
  <div style="display:flex">
    @foreach($steps as $n => $s)
    <div style="flex:1;padding:11px 6px;display:flex;flex-direction:column;align-items:center;gap:4px;border-right:{{ $n < 9 ? '1px solid var(--border)':'' }};background:{{ $n==$step?'rgba(26,92,46,.06)':($n<$step?'rgba(16,185,129,.04)':'') }};cursor:{{ $n<=$application->step?'pointer':'default' }}" {{ $n<=$application->step?'onclick="location.href=\''.route('officer.walk-in.step.show',[$application,$n]).'\'"':'' }}>
      <div style="width:26px;height:26px;border-radius:50%;background:{{ $n==$step?'var(--p)':($n<$step?'var(--ok)':'var(--border)') }};display:flex;align-items:center;justify-content:center;color:{{ $n<=$step?'#fff':'var(--muted)' }};font-size:11px;font-weight:700">
        @if($n < $step)<i class="bi bi-check-lg" style="font-size:11px"></i>@else{{ $n }}@endif
      </div>
      <div style="font-size:10px;font-weight:600;color:{{ $n==$step?'var(--p)':($n<$step?'var(--ok)':'var(--muted)') }};display:none;@media(min-width:900px){display:block}">{{ $s['label'] }}</div>
    </div>
    @endforeach
  </div>
  <div style="height:3px;background:linear-gradient(to right,var(--p) {{ ($step-1)*100/8 }}%,var(--border) {{ ($step-1)*100/8 }}%)"></div>
</div>

@if(session('success'))
<div class="alert a-ok" style="margin-bottom:18px"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if($errors->any())
<div class="alert a-e" style="margin-bottom:18px"><i class="bi bi-exclamation-circle-fill"></i>
  <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
</div>
@endif

<form method="POST" action="{{ route('officer.walk-in.step.save', [$application, $step]) }}" enctype="multipart/form-data" id="stepForm">
@csrf

{{-- ═══════════ STEP 1: PERSONAL ═══════════ --}}
@if($step == 1)
<div class="card">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-person-fill" style="color:var(--p)"></i> Personal Information</span></div>
  <div class="card-body">
    <div class="g3" style="gap:14px">
      <div class="fg">
        <label class="fl">Title</label>
        <select name="title" class="fc">
          <option value="">—</option>
          @foreach(['Mr','Mrs','Miss','Dr','Prof'] as $t)<option value="{{ $t }}" {{ $application->title===$t?'selected':'' }}>{{ $t }}</option>@endforeach
        </select>
      </div>
      <div class="fg">
        <label class="fl">First Name *</label>
        <input type="text" name="first_name" class="fc" value="{{ old('first_name',$application->first_name) }}" required>
        @error('first_name')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Surname *</label>
        <input type="text" name="surname" class="fc" value="{{ old('surname',$application->surname) }}" required>
        @error('surname')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">National ID *</label>
        <input type="text" name="national_id" class="fc" value="{{ old('national_id',$application->national_id) }}" required>
      </div>
      <div class="fg">
        <label class="fl">Date of Birth *</label>
        <input type="date" name="date_of_birth" class="fc" value="{{ old('date_of_birth',$application->date_of_birth?->format('Y-m-d')) }}" max="{{ now()->subYears(18)->format('Y-m-d') }}" required>
        <span class="ft">Must be 18+</span>
      </div>
      <div class="fg">
        <label class="fl">Gender</label>
        <select name="gender" class="fc">
          <option value="">—</option>
          <option value="male" {{ $application->gender==='male'?'selected':'' }}>Male</option>
          <option value="female" {{ $application->gender==='female'?'selected':'' }}>Female</option>
          <option value="other" {{ $application->gender==='other'?'selected':'' }}>Other</option>
        </select>
      </div>
      <div class="fg">
        <label class="fl">Marital Status</label>
        <select name="marital_status" class="fc">
          <option value="">—</option>
          @foreach(['single'=>'Single','married'=>'Married','divorced'=>'Divorced','widowed'=>'Widowed'] as $v=>$l)
          <option value="{{ $v }}" {{ $application->marital_status===$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <div class="fg">
        <label class="fl">Cell Number *</label>
        <input type="tel" name="cell_number" class="fc" value="{{ old('cell_number',$application->cell_number) }}" required>
        @error('cell_number')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Email Address</label>
        <input type="email" name="email" class="fc" value="{{ old('email',$application->email) }}">
      </div>
    </div>
  </div>
</div>

{{-- ═══════════ STEP 2: ADDRESS ═══════════ --}}
@elseif($step == 2)
<div class="card">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-geo-alt-fill" style="color:var(--p)"></i> Address Information</span></div>
  <div class="card-body">
    <div class="fg">
      <label class="fl">Current Address *</label>
      <textarea name="current_address" class="fc" rows="3" placeholder="Street, City, Zone/Area" required>{{ old('current_address', $application->user?->address) }}</textarea>
      @error('current_address')<span class="iv">{{ $message }}</span>@enderror
    </div>
    <div class="fg">
      <label class="fl">Home Address <span style="font-weight:400;color:var(--muted)">(if different from current)</span></label>
      <textarea name="home_address" class="fc" rows="3" placeholder="Leave blank if same as current address">{{ old('home_address') }}</textarea>
    </div>
  </div>
</div>

{{-- ═══════════ STEP 3: EMPLOYMENT ═══════════ --}}
@elseif($step == 3)
@php $emp = $application->employment; @endphp
<div class="card">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-briefcase-fill" style="color:var(--p)"></i> Employment Information</span></div>
  <div class="card-body">
    <div class="g2" style="gap:14px">
      <div class="fg">
        <label class="fl">Employer Name *</label>
        <input type="text" name="employment[employer_name]" class="fc" value="{{ old('employment.employer_name',$emp?->employer_name) }}" required>
        @error('employment.employer_name')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Employer Type *</label>
        <select name="employment[employer_type]" class="fc" required>
          <option value="">— Select —</option>
          @foreach(['private'=>'Private','government'=>'Government','ngo'=>'NGO','self_employed'=>'Self-Employed','other'=>'Other'] as $v=>$l)
          <option value="{{ $v }}" {{ old('employment.employer_type',$emp?->employer_type)===$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <div class="fg">
        <label class="fl">Position / Job Title *</label>
        <input type="text" name="employment[job_title]" class="fc" value="{{ old('employment.job_title',$emp?->job_title) }}" required>
      </div>
      <div class="fg">
        <label class="fl">Department</label>
        <input type="text" name="employment[department]" class="fc" value="{{ old('employment.department',$emp?->department) }}">
      </div>
      <div class="fg">
        <label class="fl">Employment / Staff ID</label>
        <input type="text" name="employment[employment_number]" class="fc" value="{{ old('employment.employment_number',$emp?->employment_number) }}">
      </div>
      <div class="fg">
        <label class="fl">HR Contact Number</label>
        <input type="tel" name="employment[contact_number]" class="fc" value="{{ old('employment.contact_number',$emp?->contact_number) }}">
      </div>
      <div class="fg">
        <label class="fl">Employment Expiry Date</label>
        <input type="date" name="employment[expiry_date]" class="fc" value="{{ old('employment.expiry_date',$emp?->expiry_date?->format('Y-m-d')) }}">
        <span class="ft">Leave blank for permanent staff</span>
      </div>
    </div>
  </div>
</div>

{{-- ═══════════ STEP 4: BANK DETAILS ═══════════ --}}
@elseif($step == 4)
@php $bank = $application->bankDetails; @endphp
<div class="card">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-bank2" style="color:var(--p)"></i> Bank Details</span></div>
  <div class="card-body">
    <div class="g2" style="gap:14px">
      <div class="fg">
        <label class="fl">Bank Name *</label>
        <select name="bank_details[bank_name]" class="fc" required>
          <option value="">— Select Bank —</option>
          @foreach(['Lesotho Bank','Standard Lesotho Bank','Nedbank Lesotho','First National Bank','Capitec','PostBank Lesotho','Other'] as $b)
          <option value="{{ $b }}" {{ old('bank_details.bank_name',$bank?->bank_name)===$b?'selected':'' }}>{{ $b }}</option>
          @endforeach
        </select>
        @error('bank_details.bank_name')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Account Holder Name *</label>
        <input type="text" name="bank_details[account_holder_name]" class="fc" value="{{ old('bank_details.account_holder_name',$bank?->account_holder_name ?? $application->applicant_name) }}" required>
        <span class="ft">Must match client's full name</span>
      </div>
      <div class="fg">
        <label class="fl">Account Number *</label>
        <input type="text" name="bank_details[account_number]" class="fc" value="{{ old('bank_details.account_number',$bank?->account_number) }}" required>
        @error('bank_details.account_number')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Account Type *</label>
        <select name="bank_details[account_type]" class="fc" required>
          <option value="">— Select —</option>
          <option value="savings" {{ old('bank_details.account_type',$bank?->account_type)==='savings'?'selected':'' }}>Savings</option>
          <option value="current" {{ old('bank_details.account_type',$bank?->account_type)==='current'?'selected':'' }}>Current / Checking</option>
        </select>
      </div>
    </div>
  </div>
</div>

{{-- ═══════════ STEP 5: NEXT OF KIN ═══════════ --}}
@elseif($step == 5)
@php $noks = $application->nextOfKin; $nok1 = $noks->get(0); $nok2 = $noks->get(1); @endphp
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
  @foreach([1,2] as $i)
  @php $nok = $i===1 ? $nok1 : $nok2; @endphp
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-person-heart" style="color:var(--p)"></i> Next of Kin {{ $i }}</span></div>
    <div class="card-body">
      <div class="fg">
        <label class="fl">Relationship *</label>
        <select name="nok[{{ $i }}][relationship]" class="fc" required>
          <option value="">— Select —</option>
          @foreach(['parent'=>'Parent','spouse'=>'Spouse','sibling'=>'Sibling','friend'=>'Friend','other'=>'Other'] as $v=>$l)
          <option value="{{ $v }}" {{ old("nok.$i.relationship",$nok?->relationship)===$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <div class="fg">
        <label class="fl">First Name *</label>
        <input type="text" name="nok[{{ $i }}][first_name]" class="fc" value="{{ old("nok.$i.first_name",$nok?->first_name) }}" required>
      </div>
      <div class="fg">
        <label class="fl">Surname *</label>
        <input type="text" name="nok[{{ $i }}][surname]" class="fc" value="{{ old("nok.$i.surname",$nok?->surname) }}" required>
      </div>
      <div class="fg">
        <label class="fl">Contact Number *</label>
        <input type="tel" name="nok[{{ $i }}][contact_number]" class="fc" value="{{ old("nok.$i.contact_number",$nok?->contact_number) }}" required>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- ═══════════ STEP 6: AFFORDABILITY ═══════════ --}}
@elseif($step == 6)
@php $a = $application->affordability; @endphp
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-calculator-fill" style="color:var(--p)"></i> Affordability Assessment</span>
    <span class="badge bs" id="affordBadge">Fill fields to calculate</span>
  </div>
  <div class="card-body">
    {{-- Income --}}
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid var(--border)">Income & Deductions</div>
    <div class="g2" style="gap:14px;margin-bottom:18px">
      <div class="fg">
        <label class="fl">Total Gross Earnings (M) *</label>
        <input type="number" name="monthly_earnings" id="fe_earn" class="fc" step="0.01" min="0" value="{{ old('monthly_earnings',$a?->monthly_earnings) }}" oninput="calcAfford()" required>
      </div>
      <div class="fg">
        <label class="fl">Tax Deduction (M)</label>
        <input type="number" name="tax_deduction" id="fe_tax" class="fc" step="0.01" min="0" value="{{ old('tax_deduction',$a?->tax_deduction??0) }}" oninput="calcAfford()">
      </div>
      <div class="fg">
        <label class="fl">Existing Loan Deductions (M)</label>
        <input type="number" name="existing_loans_deduction" id="fe_loans" class="fc" step="0.01" min="0" value="{{ old('existing_loans_deduction',$a?->existing_loans_deduction??0) }}" oninput="calcAfford()">
      </div>
      <div class="fg">
        <label class="fl">Other Deductions (M)</label>
        <input type="number" name="other_deductions" id="fe_other_ded" class="fc" step="0.01" min="0" value="{{ old('other_deductions',$a?->other_deductions??0) }}" oninput="calcAfford()">
      </div>
    </div>

    {{-- Living Expenses --}}
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid var(--border)">Monthly Living Expenses</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px">
      @php
      $expFields = [
        'transport'=>'Transport','groceries'=>'Groceries','utilities'=>'Utilities',
        'rent'=>'Rent','education'=>'Education','communication'=>'Communication',
        'medical'=>'Medical','family_support'=>'Family Support',
        'other_loan_repayments'=>'Other Loan Repayments','other_expenses'=>'Other Expenses'
      ];
      @endphp
      @foreach($expFields as $field => $label)
      <div class="fg" style="margin-bottom:0">
        <label class="fl">{{ $label }} (M)</label>
        <input type="number" name="{{ $field }}" id="fe_{{ $field }}" class="fc expense-input" step="0.01" min="0" value="{{ old($field,$a?->$field??0) }}" oninput="calcAfford()">
      </div>
      @endforeach
    </div>

    {{-- Live Results --}}
    <div id="affordResult" style="border-radius:12px;padding:18px;background:rgba(26,92,46,.04);border:1px solid rgba(26,92,46,.12)">
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;text-align:center">
        @foreach([['ar_net','Net Pay','var(--dark)'],['ar_disp','Disposable','var(--p)'],['ar_sugg','Suggested Loan','var(--ok)'],['ar_status','Status','inherit']] as [$id,$lbl,$color])
        <div style="background:#fff;border-radius:10px;padding:14px;border:1px solid var(--border)">
          <div style="font-size:10px;color:var(--muted);font-weight:700;text-transform:uppercase;margin-bottom:6px">{{ $lbl }}</div>
          <div style="font-size:20px;font-weight:800;color:{{ $color }}" id="{{ $id }}">—</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

{{-- ═══════════ STEP 7: LOAN DETAILS ═══════════ --}}
@elseif($step == 7)
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-cash-stack" style="color:var(--p)"></i> Loan Details</span></div>
  <div class="card-body">
    {{-- Product selector --}}
    <div class="fg" style="margin-bottom:18px">
      <label class="fl">Loan Product *</label>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px" id="productCards">
        @foreach($products as $prod)
        <label style="cursor:pointer">
          <input type="radio" name="loan_product_id" value="{{ $prod->id }}"
            {{ $application->loan_product_id==$prod->id?'checked':'' }}
            class="prod-radio" style="display:none"
            data-rate="{{ $prod->interest_rate }}"
            data-initiation="{{ $prod->initiation_fee_rate ?? 40 }}"
            data-admin="{{ $prod->admin_fee_fixed ?? 50 }}"
            data-min="{{ $prod->min_amount }}"
            data-max="{{ $prod->max_amount }}"
            data-minterms="{{ $prod->min_term_months }}"
            data-maxterms="{{ $prod->max_term_months }}">
          <div class="prod-card" style="border:2px solid {{ $application->loan_product_id==$prod->id?'var(--p)':'var(--border)' }};border-radius:13px;padding:14px;transition:all .2s">
            <div style="font-weight:700;font-size:13px;margin-bottom:5px">{{ $prod->name }}</div>
            <div style="font-size:11.5px;color:var(--muted)"><i class="bi bi-percent"></i> {{ $prod->interest_rate }}%/mo flat</div>
            <div style="font-size:11px;color:var(--muted);margin-top:2px">M{{ number_format($prod->min_amount,0) }} – M{{ number_format($prod->max_amount,0) }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $prod->min_term_months }}–{{ $prod->max_term_months }} months</div>
          </div>
        </label>
        @endforeach
      </div>
      @error('loan_product_id')<span class="iv">{{ $message }}</span>@enderror
    </div>

    <div class="g2" style="gap:14px">
      <div class="fg">
        <label class="fl">Requested Amount (M) *</label>
        <div style="position:relative">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:var(--muted)">M</span>
          <input type="number" name="requested_amount" id="reqAmount" class="fc" style="padding-left:28px"
            value="{{ $application->requested_amount }}" step="0.01" min="1" required oninput="calcPreview()">
        </div>
        <span class="ft" id="amtHint"></span>
        @error('requested_amount')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Term (months) *</label>
        <input type="number" name="requested_term" id="reqTerm" class="fc"
          value="{{ $application->requested_term ?? 6 }}" min="1" max="24" required oninput="calcPreview()">
        <span class="ft" id="termHint"></span>
        @error('requested_term')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">First Payment Date</label>
        <input type="date" name="first_payment_date" class="fc"
          value="{{ old('first_payment_date', preg_match('/\[first_payment_date:([^\]]+)\]/', $application->admin_notes ?? '', $m) ? $m[1] : now()->addMonth()->format('Y-m-d')) }}">
          <span class="ft">Stored as metadata — applied at loan disbursement</span>
      </div>
      <div class="fg">
        <label class="fl">Payout Method *</label>
        <select name="payout_method" class="fc" required>
          <option value="">— Select —</option>
          <option value="bank_transfer" {{ $application->payout_method==='bank_transfer'?'selected':'' }}>Bank Transfer</option>
          <option value="mobile_money" {{ $application->payout_method==='mobile_money'?'selected':'' }}>Mobile Money</option>
          <option value="cash" {{ $application->payout_method==='cash'?'selected':'' }}>Cash</option>
        </select>
        @error('payout_method')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Collection Method *</label>
        <select name="collection_method" class="fc" required>
          <option value="">— Select —</option>
          <option value="salary_deduction" {{ $application->collection_method==='salary_deduction'?'selected':'' }}>Salary Deduction</option>
          <option value="debit_order" {{ $application->collection_method==='debit_order'?'selected':'' }}>Debit Order</option>
          <option value="mobile_money" {{ $application->collection_method==='mobile_money'?'selected':'' }}>Mobile Money</option>
          <option value="cash" {{ $application->collection_method==='cash'?'selected':'' }}>Cash</option>
        </select>
        @error('collection_method')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg" style="grid-column:span 2">
        <label class="fl">Reason for Loan *</label>
        <textarea name="loan_purpose" class="fc" rows="2" placeholder="e.g. School Fees, Medical, Home Improvement..." required>{{ $application->loan_purpose }}</textarea>
        @error('loan_purpose')<span class="iv">{{ $message }}</span>@enderror
      </div>
    </div>

    {{-- Live calc preview --}}
    <div id="calcPreview" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:13px;padding:18px;margin-top:16px">
      <div style="font-size:11px;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px">
        <i class="bi bi-calculator-fill"></i> Flat Interest Breakdown
      </div>
      <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;text-align:center">
        @foreach([['previewMonthly','Monthly','var(--p)'],['previewInterest','Interest','#f59e0b'],['previewInitiation','Initiation','#8b5cf6'],['previewAdmin','Admin Fees','#64748b'],['previewTotal','Total Repay','#1e3a5f']] as [$id,$lbl,$color])
        <div style="background:#fff;border-radius:9px;padding:11px;border:1px solid #d1fae5">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:4px">{{ $lbl }}</div>
          <div style="font-size:18px;font-weight:800;color:{{ $color }}" id="{{ $id }}">—</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

{{-- Repayment Schedule Preview --}}
<div class="card">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-table" style="color:var(--p)"></i> Payment Schedule Preview</span></div>
  <div id="scheduleTable">
    <div style="text-align:center;padding:30px;color:var(--muted)"><i class="bi bi-calculator" style="font-size:36px;opacity:.3;display:block;margin-bottom:8px"></i>Select a product and enter amount to preview schedule</div>
  </div>
</div>

{{-- ═══════════ STEP 8: DOCUMENTS ═══════════ --}}
@elseif($step == 8)
<div class="card">
  <div class="card-hdr">
    <span class="card-title"><i class="bi bi-cloud-upload-fill" style="color:var(--p)"></i> Document Uploads</span>
    <span class="badge bs">Max 5MB per file · PDF, JPG, PNG</span>
  </div>
  <div class="card-body">
    <div class="alert a-i" style="margin-bottom:20px">
      <i class="bi bi-info-circle-fill"></i>
      <div>Upload clear, readable copies. Each document will be marked as <strong>Pending Verification</strong> until reviewed.</div>
    </div>

    @php
    $docTypes = [
      'id_document' => ['ID Document', 'bi-person-badge', 'Required'],
      'payslip'     => ['Recent Payslip', 'bi-receipt', 'Latest month'],
      'bank_statement' => ['Bank Statement', 'bi-bank', 'Last 1–3 months'],
      'photo'       => ['Half-Body Photo', 'bi-camera', 'Clear, recent photo'],
    ];
    $existing = $application->documents->keyBy('type');
    @endphp

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
      @foreach($docTypes as $type => [$label, $icon, $note])
      @php $doc = $existing->get($type); @endphp
      <div style="border:1.5px solid {{ $doc?'rgba(16,185,129,.4)':'var(--border)' }};border-radius:13px;padding:16px;background:{{ $doc?'rgba(16,185,129,.03)':'#fafafa' }}">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
          <div style="width:38px;height:38px;border-radius:10px;background:{{ $doc?'rgba(16,185,129,.1)':'rgba(26,92,46,.08)' }};display:flex;align-items:center;justify-content:center;font-size:17px;color:{{ $doc?'var(--ok)':'var(--p)' }}">
            <i class="bi {{ $icon }}"></i>
          </div>
          <div>
            <div style="font-weight:700;font-size:13px">{{ $label }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $note }}</div>
          </div>
          @if($doc)
          <span class="badge {{ $doc->status==='verified'?'bok':($doc->status==='rejected'?'be':'bw') }}" style="margin-left:auto">{{ ucfirst($doc->status) }}</span>
          @endif
        </div>
        @if($doc)
        <div style="background:#fff;border:1px solid var(--border);border-radius:9px;padding:10px 12px;font-size:12px;margin-bottom:10px;display:flex;align-items:center;gap:8px">
          <i class="bi bi-file-earmark-check" style="color:var(--ok)"></i>
          <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $doc->original_name }}</span>
        </div>
        <div style="font-size:11.5px;color:var(--muted);margin-bottom:6px">Replace with new file:</div>
        @endif
        <input type="file" name="documents[{{ $type }}]" id="doc_{{ $type }}" class="fc" accept=".pdf,.jpg,.jpeg,.png" style="padding:7px">
        @error("documents.$type")<span class="iv">{{ $message }}</span>@enderror
      </div>
      @endforeach
    </div>
  </div>
</div>

{{-- ═══════════ STEP 9: REVIEW & SUBMIT ═══════════ --}}
@elseif($step == 9)
<div class="alert a-ok" style="margin-bottom:20px">
  <i class="bi bi-check-circle-fill"></i>
  <div><strong>Almost done!</strong> Review all details below, then submit for admin approval.</div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">
  {{-- Personal --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-person" style="color:var(--p)"></i> Personal</span></div>
    <div class="card-body" style="padding:14px 18px">
      @foreach(['Name'=>$application->applicant_name,'ID Number'=>$application->national_id,'DOB'=>$application->date_of_birth?->format('d M Y'),'Gender'=>ucfirst($application->gender??'—'),'Cell'=>$application->cell_number,'Email'=>$application->email??'—'] as $l=>$v)
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
        <span style="color:var(--muted)">{{ $l }}</span><strong>{{ $v }}</strong>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Employment --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-briefcase" style="color:var(--p)"></i> Employment</span></div>
    <div class="card-body" style="padding:14px 18px">
      @if($application->employment)
      @foreach(['Employer'=>$application->employment->employer_name,'Type'=>ucfirst($application->employment->employer_type??'—'),'Title'=>$application->employment->job_title,'Department'=>$application->employment->department??'—'] as $l=>$v)
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
        <span style="color:var(--muted)">{{ $l }}</span><strong>{{ $v }}</strong>
      </div>
      @endforeach
      @else<div style="color:var(--muted);font-size:13px;text-align:center;padding:20px">Not provided</div>@endif
    </div>
  </div>

  {{-- Loan --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-cash-stack" style="color:var(--p)"></i> Loan Request</span></div>
    <div class="card-body" style="padding:14px 18px">
      @foreach(['Product'=>$application->loanProduct?->name??'—','Amount'=>'M '.number_format($application->requested_amount??0,2),'Term'=>($application->requested_term??'—').' months','Purpose'=>$application->loan_purpose??'—','Payout'=>ucfirst(str_replace('_',' ',$application->payout_method??'—')),'Collection'=>ucfirst(str_replace('_',' ',$application->collection_method??'—'))] as $l=>$v)
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
        <span style="color:var(--muted)">{{ $l }}</span>
        <strong style="{{ $l==='Amount'?'color:var(--p);font-size:15px':'' }}">{{ $v }}</strong>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Affordability --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title"><i class="bi bi-calculator" style="color:var(--p)"></i> Affordability</span></div>
    <div class="card-body" style="padding:14px 18px">
      @if($application->affordability)
      @php $af = $application->affordability; @endphp
      @foreach(['Gross Earnings'=>'M '.number_format($af->monthly_earnings??0,2),'Net Pay'=>'M '.number_format($af->net_salary??0,2),'Total Expenses'=>'M '.number_format($af->total_living_expenses??0,2),'Disposable Income'=>'M '.number_format($af->disposable_income??0,2)] as $l=>$v)
      <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
        <span style="color:var(--muted)">{{ $l }}</span>
        <strong style="{{ $l==='Disposable Income'?'color:var(--p)':'' }}">{{ $v }}</strong>
      </div>
      @endforeach
      @else<div style="color:var(--warn);text-align:center;padding:20px;font-size:13px"><i class="bi bi-exclamation-triangle"></i> Affordability not completed</div>@endif
    </div>
  </div>
</div>

{{-- Documents summary --}}
<div class="card" style="margin-bottom:18px">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-files" style="color:var(--p)"></i> Documents</span></div>
  <div style="display:flex;gap:10px;padding:16px;flex-wrap:wrap">
    @foreach(['id_document'=>'ID Document','payslip'=>'Payslip','bank_statement'=>'Bank Statement','photo'=>'Photo'] as $type=>$label)
    @php $d = $application->documents->where('type',$type)->first(); @endphp
    <div style="border:1px solid {{ $d?'rgba(16,185,129,.4)':'rgba(239,68,68,.3)' }};background:{{ $d?'rgba(16,185,129,.04)':'rgba(239,68,68,.04)' }};border-radius:9px;padding:10px 14px;font-size:12.5px;display:flex;align-items:center;gap:7px">
      <i class="bi bi-{{ $d?'check-circle-fill':'x-circle' }}" style="color:{{ $d?'var(--ok)':'var(--err)' }}"></i>
      {{ $label }}
    </div>
    @endforeach
  </div>
</div>

{{-- Officer note --}}
<div class="card">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-chat-text" style="color:var(--p)"></i> Officer Notes (optional)</span></div>
  <div class="card-body">
    <textarea name="officer_notes" class="fc" rows="3" placeholder="Any notes for the admin reviewer...">{{ old('officer_notes') }}</textarea>
  </div>
</div>
@endif

{{-- Navigation --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px">
  <div>
    @if($step > 1)
    <a href="{{ route('officer.walk-in.step.show', [$application, $step-1]) }}" class="btn btn-o"><i class="bi bi-arrow-left"></i> Back</a>
    @endif
  </div>
  <div style="display:flex;gap:10px">
    @if($step < 9)
    <button type="submit" class="btn btn-p">Save & Continue <i class="bi bi-arrow-right"></i></button>
    @else
    <button type="button" onclick="openModal('submitModal')" class="btn btn-ok"><i class="bi bi-send-fill"></i> Submit Application</button>
    @endif
  </div>
</div>

</form>

{{-- Submit confirmation modal --}}
@if($step == 9)
<div class="mo" id="submitModal"><div class="mb" style="max-width:460px">
  <div class="mh"><span class="mt">Confirm Submission</span><button class="mc" onclick="closeModal('submitModal')">×</button></div>
  <div class="mbody">
    <div class="alert a-ok" style="margin-bottom:16px"><i class="bi bi-check-circle-fill"></i> Application will be submitted to admin for review.</div>
    <p style="font-size:13.5px;color:var(--muted)">Client <strong>{{ $application->applicant_name }}</strong> will receive login credentials via SMS/email and can track their application status.</p>
  </div>
  <div class="mf">
    <button type="button" class="btn btn-o" onclick="closeModal('submitModal')">Cancel</button>
    <form method="POST" action="{{ route('officer.walk-in.submit', $application) }}">@csrf
      @if(!empty(old('officer_notes')))
      <input type="hidden" name="officer_notes" value="{{ old('officer_notes') }}">
      @endif
      <button type="submit" class="btn btn-ok"><i class="bi bi-send-fill"></i> Confirm & Submit</button>
    </form>
  </div>
</div></div>
@endif

@push('scripts')
<script>
// Modal helpers
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.mo.open').forEach(m=>m.classList.remove('open'))})

@if($step == 6)
// ── AFFORDABILITY CALCULATOR ──
function calcAfford(){
  const earn = parseFloat(document.getElementById('fe_earn').value)||0;
  const tax  = parseFloat(document.getElementById('fe_tax').value)||0;
  const loans= parseFloat(document.getElementById('fe_loans').value)||0;
  const oD   = parseFloat(document.getElementById('fe_other_ded').value)||0;

  const expFields = ['transport','groceries','utilities','rent','education','communication','medical','family_support','other_loan_repayments','other_expenses'];
  let totalExp = 0;
  expFields.forEach(f=>{
    const el = document.getElementById('fe_'+f);
    if(el) totalExp += parseFloat(el.value)||0;
  });

  const netPay = earn - tax - loans - oD;
  const disposable = netPay - totalExp;
  const suggested = Math.max(0, disposable * 0.6);

  document.getElementById('ar_net').textContent = 'M'+netPay.toFixed(2);
  document.getElementById('ar_disp').textContent = 'M'+disposable.toFixed(2);
  document.getElementById('ar_disp').style.color = disposable >= 0 ? 'var(--p)' : 'var(--err)';
  document.getElementById('ar_sugg').textContent = 'M'+suggested.toFixed(2);

  const statusEl = document.getElementById('ar_status');
  if(earn > 0){
    statusEl.textContent = disposable >= 0 ? '✓ Qualifies' : '✗ Fails';
    statusEl.style.color = disposable >= 0 ? 'var(--ok)' : 'var(--err)';
    document.getElementById('affordBadge').textContent = 'Disposable: M'+disposable.toFixed(2);
  }
}
calcAfford();
@endif

@if($step == 7)
// ── LOAN PRODUCT SELECTOR ──
let selectedProduct = null;
document.querySelectorAll('.prod-radio').forEach(radio=>{
  radio.addEventListener('change',function(){
    document.querySelectorAll('.prod-card').forEach(c=>{c.style.border='2px solid var(--border)'});
    this.closest('label').querySelector('.prod-card').style.border='2px solid var(--p)';
    selectedProduct = {
      rate: parseFloat(this.dataset.rate),
      initiation: parseFloat(this.dataset.initiation),
      admin: parseFloat(this.dataset.admin),
      min: parseFloat(this.dataset.min),
      max: parseFloat(this.dataset.max),
      minTerms: parseInt(this.dataset.minterms),
      maxTerms: parseInt(this.dataset.maxterms),
    };
    document.getElementById('amtHint').textContent = `M${this.dataset.min} – M${this.dataset.max}`;
    document.getElementById('termHint').textContent = `${this.dataset.minterms}–${this.dataset.maxterms} months`;
    calcPreview();
    loadSchedule();
  });
});

function calcPreview(){
  if(!selectedProduct) return;
  const amount = parseFloat(document.getElementById('reqAmount').value)||0;
  const term   = parseInt(document.getElementById('reqTerm').value)||0;
  if(!amount||!term){ document.getElementById('calcPreview').style.display='none'; return; }

  const rate = selectedProduct.rate/100;
  const initFee = amount * (selectedProduct.initiation/100);
  const interest = amount * rate * term;
  const admin = selectedProduct.admin * term;
  const totalRepay = amount + interest + initFee + admin;
  const monthly = totalRepay / term;

  document.getElementById('previewMonthly').textContent = 'M'+monthly.toFixed(2);
  document.getElementById('previewInterest').textContent = 'M'+interest.toFixed(2);
  document.getElementById('previewInitiation').textContent = 'M'+initFee.toFixed(2);
  document.getElementById('previewAdmin').textContent = 'M'+admin.toFixed(2);
  document.getElementById('previewTotal').textContent = 'M'+totalRepay.toFixed(2);
  document.getElementById('calcPreview').style.display = 'block';
}

function loadSchedule(){
  const amount = parseFloat(document.getElementById('reqAmount').value)||0;
  const term   = parseInt(document.getElementById('reqTerm').value)||0;
  if(!selectedProduct||!amount||!term) return;

  const appId = {{ $application->id }};
  fetch(`/officer/applications/${appId}/schedule-preview?amount=${amount}&term=${term}`)
    .then(r=>r.json())
    .then(data=>{
      let html = `<div style="overflow-x:auto"><table class="dt"><thead><tr><th>#</th><th>Principal</th><th>Interest</th><th>Init Fee</th><th>Admin Fee</th><th>Payment</th><th>Balance</th></tr></thead><tbody>`;
      data.schedule.forEach(row=>{
        html += `<tr>
          <td>${row.no}</td>
          <td>M${row.principal.toFixed(2)}</td>
          <td>M${row.interest.toFixed(2)}</td>
          <td>M${row.initiation_fee.toFixed(2)}</td>
          <td>M${row.admin_fee.toFixed(2)}</td>
          <td style="font-weight:700;color:var(--p)">M${row.payment.toFixed(2)}</td>
          <td>M${row.balance.toFixed(2)}</td>
        </tr>`;
      });
      html += `</tbody><tfoot><tr style="background:#f0fdf4;font-weight:700">
        <td>Total</td><td>M${data.cash_received.toFixed(2)}</td>
        <td>M${data.total_interest.toFixed(2)}</td>
        <td>M${data.total_initiation.toFixed(2)}</td>
        <td>M${data.total_admin.toFixed(2)}</td>
        <td style="color:var(--p)">M${data.total_repay.toFixed(2)}</td>
        <td>M0.00</td>
      </tr></tfoot></table></div>`;
      document.getElementById('scheduleTable').innerHTML = html;
    })
    .catch(()=>{});
}

document.getElementById('reqAmount')?.addEventListener('input',loadSchedule);
document.getElementById('reqTerm')?.addEventListener('input',loadSchedule);
@endif
</script>
@endpush
@endsection
