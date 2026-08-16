@extends('officer.layouts.app')
@section('title','Walk-in Application – Step '.$step)
@section('page-title','Walk-in Application')
@section('bc')
<a href="{{ route('officer.dashboard') }}">Dashboard</a> / <a href="{{ route('officer.walk-in.create') }}">Walk-in</a> / Step {{ $step }}
@endsection

@section('content')
@php
$client = $application->user;
$titles = [
  1=>'Personal Information',
  2=>'Address Details',
  3=>'Employment Details',
  4=>'Bank Details',
  5=>'Next of Kin',
  6=>'Affordability',
  7=>'Loan Details',
  8=>'Upload Documents',
  9=>'Review & Submit'
];
$stepTitle = $titles[(int)$step] ?? 'Step '.$step;
$totalSteps = 9;
@endphp

{{-- Client Banner --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:12px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
  <div style="display:flex;align-items:center;gap:12px">
    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px">{{ strtoupper(substr($client->name,0,1)) }}</div>
    <div>
      <div style="font-weight:700;font-size:14px;color:var(--navy)">{{ $client->name }}</div>
      <div style="font-size:11px;color:var(--muted)">App: {{ $application->application_number }} &nbsp;·&nbsp; ID: {{ $client->national_id }}</div>
    </div>
  </div>
  <div style="display:flex;gap:4px">
    <span class="badge bs" style="background:rgba(26,107,60,.08);color:var(--p);font-size:10px">Walk-in Application</span>
  </div>
</div>

{{-- Progress --}}
<div style="margin-bottom:24px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
    <div style="font-family:'Playfair Display',serif;font-size:20px;font-weight:700;color:var(--navy)">{{ $stepTitle }}</div>
    <div style="font-size:12px;color:var(--muted);font-weight:500">Step {{ $step }} of {{ $totalSteps }}</div>
  </div>
  <div style="background:#e2e8f0;border-radius:99px;height:5px">
    <div style="background:linear-gradient(90deg,var(--p),var(--p2));height:100%;width:{{ round($step/$totalSteps*100) }}%;border-radius:99px;transition:width .3s"></div>
  </div>
  <div style="display:flex;gap:0;margin-top:8px;overflow-x:auto;scrollbar-width:none">
    @foreach($titles as $n=>$t)
    <div style="flex:1;text-align:center;font-size:9.5px;color:{{ $n<=$step?'var(--p)':'var(--muted)' }};font-weight:{{ $n===$step?'700':'400' }};min-width:52px;padding:0 2px;cursor:{{ $n<=$application->step?'pointer':'default' }}" {{ $n<=$application->step?'onclick="location.href=\''.route('officer.walk-in.step.show',[$application,$n]).'\'"':'' }}>{{ substr($t,0,5) }}.</div>
    @endforeach
  </div>
</div>

<div class="card">
<form id="stepForm" method="POST" action="{{ $step === 9 ? route('officer.walk-in.submit', $application) : route('officer.walk-in.step.save', [$application, $step]) }}" enctype="multipart/form-data">
    @csrf
    <div class="card-body">


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
    <div class="g2" style="gap:14px">
      <div class="fg" style="grid-column:span 2"><label class="fl">Residential Address *</label><input type="text" name="residential_address" class="fc" value="{{ old('residential_address',$application->residential_address) }}" placeholder="e.g. Ha Matala, Block 5, House 23" required></div>
      <div class="fg"><label class="fl">Village / Area *</label><input type="text" name="village" class="fc" value="{{ old('village',$application->village) }}" required></div>
      <div class="fg"><label class="fl">Town / City *</label><input type="text" name="town" class="fc" value="{{ old('town',$application->town) }}" required></div>
      <div class="fg"><label class="fl">District *</label>
        <select name="district" class="fc" required>
          <option value="">— Select District —</option>
          @foreach(['Maseru','Berea','Leribe','Butha-Buthe','Mafeteng',"Mohale's Hoek","Qacha's Nek",'Quthing','Thaba-Tseka','Mokhotlong'] as $d)
          <option {{ old('district',$application->district)===$d?'selected':'' }}>{{ $d }}</option>
          @endforeach
        </select>
      </div>
      <div class="fg"><label class="fl">Duration at Address *</label>
        <select name="address_duration" class="fc" required>
          <option value="">—</option>
          @foreach(['less_than_6_months'=>'Less than 6 months','6_to_12_months'=>'6–12 months','1_to_3_years'=>'1–3 years','3_to_5_years'=>'3–5 years','more_than_5_years'=>'More than 5 years'] as $v=>$l)
          <option value="{{ $v }}" {{ old('address_duration',$application->address_duration)===$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <div class="fg"><label class="fl">Residence Type *</label>
        <select name="residence_type" class="fc" required>
          <option value="">—</option>
          <option value="own" {{ old('residence_type',$application->residence_type)==='own'?'selected':'' }}>Own</option>
          <option value="rent" {{ old('residence_type',$application->residence_type)==='rent'?'selected':'' }}>Rent</option>
          <option value="family" {{ old('residence_type',$application->residence_type)==='family'?'selected':'' }}>Family</option>
          <option value="employer" {{ old('residence_type',$application->residence_type)==='employer'?'selected':'' }}>Employer Provided</option>
        </select>
      </div>
      <div class="fg" style="grid-column:span 2"><label class="fl">Nearest Landmark *</label><input type="text" name="nearest_landmark" class="fc" value="{{ old('nearest_landmark',$application->nearest_landmark) }}" placeholder="e.g. Near Maseru West Primary School" required></div>
      <div class="fg" style="grid-column:span 2"><label class="fl">Directions to Home *</label><textarea name="home_directions" class="fc" rows="3" placeholder="e.g. From Shell garage, turn left, third house on right, green gate." required>{{ old('home_directions',$application->home_directions) }}</textarea></div>
    </div>
    
    <div style="background:#f0f4ff;border:1px solid #d4e0d4;border-radius:10px;padding:16px;margin-top:16px">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
          <div style="font-size:14px;font-weight:700;color:var(--p);margin-bottom:2px"><i class="bi bi-geo-alt-fill" style="margin-right:5px"></i>Capture GPS Location</div>
          <div style="font-size:12px;color:var(--muted)" id="gps-status">Please capture the client's current location if at their residence.</div>
        </div>
        <button type="button" class="btn btn-p btn-sm" onclick="captureGPS()"><i class="bi bi-crosshair"></i> Capture Coordinates</button>
      </div>
      <input type="hidden" name="gps_latitude" id="gps-lat" value="{{ old('gps_latitude',$application->gps_latitude) }}">
      <input type="hidden" name="gps_longitude" id="gps-lng" value="{{ old('gps_longitude',$application->gps_longitude) }}">
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
        <input type="text" name="employer_name" class="fc" value="{{ old('employer_name',$emp?->employer_name) }}" required>
        @error('employer_name')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Employer Type *</label>
        <select name="employer_type" class="fc" required onchange="toggleCategory(this.value)">
          <option value="">— Select —</option>
          @foreach(['government'=>'Government','private'=>'Private Sector','sme'=>'SMEs'] as $v=>$l)
          <option value="{{ $v }}" {{ old('employer_type',$emp?->employer_type)===$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <div id="category_wrapper" class="fg" style="display: {{ in_array($application->employment?->employer_type, ['government', 'sme', 'private']) ? 'block' : 'none' }}">
        <label class="fl">Work Sector / Category *</label>
        <select name="employer_category" id="employer_category" class="fc" {{ in_array($application->employment?->employer_type, ['government', 'sme', 'private']) ? 'required' : '' }}>
            <option value="">— Select —</option>
            @foreach(['Defence','NSS','Police','LCS','Pensioner','Civil servants','Teacher','Private sector','SMEs'] as $c)
            <option {{ old('employer_category',$emp?->employer_category)===$c?'selected':'' }}>{{ $c }}</option>
            @endforeach
        </select>
      </div>
      <div class="fg">
        <label class="fl">Position / Job Title *</label>
        <input type="text" name="job_title" class="fc" value="{{ old('job_title',$emp?->job_title) }}" required>
      </div>
      <div class="fg">
        <label class="fl">Department</label>
        <input type="text" name="department" class="fc" value="{{ old('department',$emp?->department) }}">
      </div>
      <div class="fg">
        <label class="fl">Employment / Staff ID *</label>
        <input type="text" name="employment_number" class="fc" value="{{ old('employment_number',$emp?->employment_number) }}" required>
      </div>
      <div class="fg">
        <label class="fl">HR Contact Number *</label>
        <input type="tel" name="contact_number" class="fc" value="{{ old('contact_number',$emp?->contact_number) }}" required>
      </div>
      <div class="fg">
        <label class="fl">Employment Expiry Date</label>
        <input type="date" name="employment_expiry_date" class="fc" value="{{ old('employment_expiry_date',$emp?->employment_expiry_date?->format('Y-m-d')) }}">
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
        <select name="bank_name" id="bank_name" class="fc" data-prev="{{ old('bank_name',$bank?->bank_name) }}" required onchange="loadBranches(this.value)">
          <option value="">— Select Bank —</option>
        </select>
        @error('bank_name')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Branch Name *</label>
        <select name="branch_name" id="branch_name" class="fc" data-prev="{{ old('branch_name',$bank?->branch_name) }}" required onchange="updateBranchCode(this.options[this.selectedIndex])">
            <option value="">— Select Branch —</option>
        </select>
      </div>
      <div class="fg">
        <label class="fl">Branch Code</label>
        <input type="text" name="branch_code" id="branch_code" class="fc" value="{{ old('branch_code',$bank?->branch_code) }}" placeholder="Auto-filled">
      </div>
      <div class="fg">
        <label class="fl">Account Holder Name *</label>
        <input type="text" name="account_holder_name" class="fc" value="{{ old('account_holder_name',$bank?->account_holder_name ?? $client->name) }}" required>
        <span class="ft">Must match client's full name</span>
      </div>
      <div class="fg">
        <label class="fl">Account Number *</label>
        <input type="text" name="account_number" class="fc" value="{{ old('account_number',$bank?->account_number) }}" required>
        @error('account_number')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Account Type *</label>
        <select name="account_type" class="fc" required><option value="">—</option><option value="savings" {{ old('account_type',$bank?->account_type)==='savings'?'selected':'' }}>Savings</option><option value="cheque" {{ old('account_type',$bank?->account_type)==='cheque'?'selected':'' }}>Cheque / Current</option></select>
      </div>
    </div>
  </div>
</div>

{{-- ═══════════ STEP 5: NEXT OF KIN ═══════════ --}}
@elseif($step == 5)
@php $nok = $application->nextOfKin->first(); @endphp
<div class="card">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-person-heart" style="color:var(--p)"></i> Next of Kin / Emergency Contact</span></div>
  <div class="card-body">
    <div class="alert a-i" style="margin-bottom:16px"><i class="bi bi-info-circle-fill"></i> Provide details of a family member or friend we can contact if needed.</div>
    <div class="g2" style="gap:14px">
      <div class="fg">
        <label class="fl">First Name *</label>
        <input type="text" name="nok_1_first_name" class="fc" value="{{ old('nok_1_first_name',$nok?->first_name) }}" required>
      </div>
      <div class="fg">
        <label class="fl">Surname *</label>
        <input type="text" name="nok_1_last_name" class="fc" value="{{ old('nok_1_last_name',$nok?->last_name) }}" required>
      </div>
      <div class="fg">
        <label class="fl">Relationship *</label>
        <select name="nok_1_relationship" class="fc" required>
          <option value="">— Select —</option>
          @foreach(['Spouse','Parent','Sibling','Child','Friend','Other'] as $r)
          <option {{ old('nok_1_relationship',$nok?->relationship)===$r?'selected':'' }}>{{ $r }}</option>
          @endforeach
        </select>
      </div>
      <div class="fg">
        <label class="fl">Contact Number *</label>
        <input type="tel" name="nok_1_phone" class="fc" value="{{ old('nok_1_phone',$nok?->contact_number) }}" required>
      </div>
    </div>
  </div>
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
        'rent'=>'Rent','groceries'=>'Groceries','transport'=>'Transport','utilities'=>'Utilities',
        'education'=>'Education','communication'=>'Airtime/Data','other_insurance'=>'Insurance',
        'medical'=>'Medical','other_loan_repayments'=>'Other Loans','family_support'=>'Family Support',
        'entertainment'=>'Entertainment','other_expenses'=>'Other'
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
        <label class="fl">Loan Purpose *</label>
        <select name="loan_purpose" class="fc" required>
          <option value="">— Select Purpose —</option>
          @foreach(['Home Improvement','Education','Medical / Health','Business Investment','Vehicle Purchase','Debt Consolidation','School Fees','Wedding / Family Event','Funeral Costs','Groceries / Food','Clothing','Travel','Other'] as $p)
          <option value="{{ $p }}" {{ old('loan_purpose',$application->loan_purpose)===$p?'selected':'' }}>{{ $p }}</option>
          @endforeach
        </select>
        @error('loan_purpose')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Expected Payday (1-31) *</label>
        <input type="number" name="salary_payday" class="fc" min="1" max="31" value="{{ old('salary_payday', $application->salary_payday ?? 25) }}" required>
        <div style="font-size:11.5px;color:var(--muted);margin-top:6px">Determines the monthly instalment due date.</div>
      </div>
      <div class="fg">
        <label class="fl">Payout Method *</label>
        <select name="payout_method" class="fc" required>
          <option value="bank_transfer" {{ old('payout_method',$application->payout_method)==='bank_transfer'?'selected':'' }}>Bank Transfer</option>
          <option value="mobile_money" {{ old('payout_method',$application->payout_method)==='mobile_money'?'selected':'' }}>Mobile Money</option>
          <option value="cash" {{ old('payout_method',$application->payout_method)==='cash'?'selected':'' }}>Cash</option>
        </select>
      </div>
      <div class="fg">
        <label class="fl">Collection Method *</label>
        <select name="collection_method" class="fc" required>
          <option value="">— Select Method —</option>
          <option value="salary_deduction" {{ old('collection_method',$application->collection_method)==='salary_deduction'?'selected':'' }}>Salary deduction</option>
          <option value="card_payment" {{ old('collection_method',$application->collection_method)==='card_payment'?'selected':'' }}>Card payment</option>
          <option value="debit_order" {{ old('collection_method',$application->collection_method)==='debit_order'?'selected':'' }}>Debit Order</option>
          <option value="mobile_money" {{ old('collection_method',$application->collection_method)==='mobile_money'?'selected':'' }}>Mobile Money</option>
          <option value="cash" {{ old('collection_method',$application->collection_method)==='cash'?'selected':'' }}>Cash</option>
        </select>
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
    <span class="card-title"><i class="bi bi-cloud-upload" style="color:var(--p)"></i> Document Uploads</span>
    <span class="badge bs">Max 10MB · PDF, JPG, PNG</span>
  </div>
  <div class="card-body">
    <div style="font-size:13px;color:var(--muted);margin-bottom:20px">Upload clear documents for the client. These will be marked for verification.</div>
    
    @php
    $docTypes = [
      ['national_id', 'National ID Card', 'bi-person-badge', 'Front and back scan'],
      ['payslip', 'Recent Payslip', 'bi-receipt', 'Latest 3 months if available'],
      ['bank_statement', 'Bank Statement', 'bi-bank', 'Last 3 months'],
      ['photo', 'User Portrait', 'bi-camera', 'Clear selfie or photo']
    ];
    $uploaded = $application->documents->keyBy('type');
    @endphp

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
      @foreach($docTypes as [$dtype, $dlabel, $icon, $note])
      @php $existing = $uploaded->get($dtype); @endphp
      <div style="background:#f8fafc;border-radius:15px;padding:20px;border:1.5px solid {{ $existing?'rgba(22,163,74,.3)':'var(--border)' }};position:relative">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <div style="width:42px;height:42px;border-radius:12px;background:{{ $existing?'rgba(22,163,74,.1)':'rgba(43,108,176,.1)' }};display:flex;align-items:center;justify-content:center;font-size:18px;color:{{ $existing?'var(--ok)':'var(--p)' }}">
            <i class="bi {{ $icon }}"></i>
          </div>
          @if($existing)
            <span class="badge {{ $existing->status==='verified'?'bok':'bw' }}">{{ ucfirst($existing->status) }}</span>
          @else
            <span class="badge be">Required</span>
          @endif
        </div>
        
        <div style="font-weight:700;font-size:14px;margin-bottom:4px">{{ $dlabel }}</div>
        <div style="font-size:11.5px;color:var(--muted);margin-bottom:15px">{{ $note }}</div>

        @if(!$existing || $existing->status === 'rejected')
          <div style="display:flex;gap:8px">
            <input type="file" id="file_{{ $dtype }}" class="fc" accept=".pdf,.jpg,.jpeg,.png" style="flex:1;padding:7px;font-size:12px">
            <input type="file" id="cam_{{ $dtype }}" accept="image/*" capture="environment" style="display:none" onchange="const df=new DataTransfer();df.items.add(this.files[0]);document.getElementById('file_{{ $dtype }}').files=df.files;uploadDoc('{{ $dtype }}', document.getElementById('btn_{{ $dtype }}'))">
            <button type="button" class="btn btn-o btn-sm" onclick="document.getElementById('cam_{{ $dtype }}').click()" title="Take Photo" style="padding:0 12px;height:38px"><i class="bi bi-camera" style="font-size:16px"></i></button>
            <button type="button" id="btn_{{ $dtype }}" class="btn btn-p btn-sm" onclick="uploadDoc('{{ $dtype }}', this)" style="height:38px;padding:0 15px">Upload</button>
          </div>
        @else
          <div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--navy);font-weight:600">
               <i class="bi bi-file-earmark-check-fill" style="color:var(--ok)"></i>
               Uploaded
            </div>
            <a href="{{ \Illuminate\Support\Facades\Storage::url($existing->path) }}" target="_blank" class="btn btn-o btn-xs">View</a>
          </div>
        @endif
      </div>
      @endforeach
    </div>
  </div>
</div>

{{-- ═══════════ STEP 9: REVIEW & SUBMIT ═══════════ --}}
@elseif($step == 9)
<div class="alert a-ok" style="margin-bottom:20px;border:1px solid rgba(22,163,74,.2)">
  <i class="bi bi-check-circle-fill"></i>
  <div><strong>Ready to submit!</strong> Please review all details below. Once confirmed, collect the client's signature.</div>
</div>

<div class="card" style="margin-bottom:18px">
  <div class="card-hdr" style="background:#f8fafc"><span class="card-title"><i class="bi bi-journal-text" style="color:var(--p)"></i> Application Summary</span></div>
  <div class="card-body" style="padding:24px">
    
    {{-- Personal Section --}}
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Personal Information</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:10px;margin-bottom:24px">
      @foreach([
        'Full Name'     => $application->applicant_name,
        'National ID'   => $application->national_id,
        'Phone Number'  => $application->cell_number,
        'Email Address' => $application->email ?? '—',
        'Gender'        => ucfirst($application->gender ?? '—'),
        'Marital Status'=> ucfirst($application->marital_status ?? '—')
      ] as $l => $v)
      <div style="background:#f8fafc;border-radius:10px;padding:12px 15px;border:1px solid #edf2f7">
        <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">{{ $l }}</div>
        <div style="font-weight:700;margin-top:3px;font-size:13.5px;color:var(--navy)">{{ $v }}</div>
      </div>
      @endforeach
    </div>

    {{-- Employment & Bank Section --}}
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Employment & Financials</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:10px;margin-bottom:24px">
      @foreach([
        'Employer'      => $application->employment->employer_name ?? '—',
        'Job Title'     => $application->employment->job_title ?? '—',
        'Bank Name'     => $application->bankDetails->bank_name ?? '—',
        'Account Type'  => $application->bankDetails->account_type ?? '—',
        'Monthly Earn'  => 'M '.number_format($application->affordability->monthly_earnings ?? 0, 2),
        'Disposable'    => 'M '.number_format($application->affordability->disposable_income ?? 0, 2)
      ] as $l => $v)
      <div style="background:#f8fafc;border-radius:10px;padding:12px 15px;border:1px solid #edf2f7">
        <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">{{ $l }}</div>
        <div style="font-weight:700;margin-top:3px;font-size:13.5px;color:var(--navy)">{{ $v }}</div>
      </div>
      @endforeach
    </div>

    {{-- Loan Details Section --}}
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Loan Selection</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:10px;margin-bottom:24px">
      @foreach([
        'Loan Product'  => $application->loanProduct->name ?? '—',
        'Amount'        => 'M '.number_format($application->requested_amount ?? 0, 2),
        'Term'          => ($application->requested_term ?? '—').' months',
        'Purpose'       => $application->loan_purpose ?? '—',
        'Repay Method'  => ucfirst(str_replace('_',' ',$application->collection_method ?? '—')),
        'Payout Method' => ucfirst(str_replace('_',' ',$application->payout_method ?? '—'))
      ] as $l => $v)
      <div style="background:rgba(43,108,176,0.03);border-radius:10px;padding:12px 15px;border:1px solid rgba(43,108,176,0.1)">
        <div style="font-size:10px;color:var(--p);font-weight:600;text-transform:uppercase">{{ $l }}</div>
        <div style="font-weight:700;margin-top:3px;font-size:13.5px;color:var(--navy)">{{ $v }}</div>
      </div>
      @endforeach
    </div>

    {{-- Documents Section --}}
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Documents Status</div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:24px">
      @foreach(['national_id'=>'National ID','payslip'=>'Payslip','bank_statement'=>'Bank Statement','photo'=>'Selfie Picture'] as $dtype=>$dlabel)
      @php $doc = $application->documents->where('type',$dtype)->first(); @endphp
      <div style="background:{{ $doc?'rgba(22,163,74,.08)':'rgba(239,68,68,.08)' }};border:1px solid {{ $doc?'rgba(22,163,74,.2)':'rgba(239,68,68,.2)' }};border-radius:8px;padding:8px 14px;font-size:12.5px;font-weight:600;color:{{ $doc?'#16a34a':'#dc2626' }}">
        <i class="bi bi-{{ $doc?'check-circle-fill':'x-circle-fill' }}"></i> {{ $dlabel }}
      </div>
      @endforeach
    </div>

    {{-- Signature Section --}}
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Client Digital Signature *</div>
    <div style="background:#fff;border:1.5px dashed var(--border);border-radius:15px;padding:20px;text-align:center">
        <canvas id="signature-pad" style="width:100%;max-width:500px;height:180px;border-radius:10px;background:#fcfdfd;touch-action:none;cursor:crosshair"></canvas>
        <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center;max-width:500px;margin:12px auto 0">
          <span style="font-size:12px;color:var(--muted)"><i class="bi bi-info-circle"></i> Use mouse or touch to sign</span>
          <button type="button" class="btn btn-o btn-sm" onclick="sigPad.clear()" style="padding:5px 15px;font-size:12px"><i class="bi bi-eraser"></i> Clear</button>
        </div>
    </div>
    <input type="hidden" name="signature_data" id="signature_data">

  </div>
</div>

<div class="card" style="border:1px solid #fbd38d;background:#fffaf0">
  <div class="card-body" style="padding:15px">
    <div style="display:flex;gap:12px;align-items:flex-start">
      <i class="bi bi-exclamation-triangle-fill" style="color:#dd6b20;font-size:18px"></i>
      <div style="font-size:13px;color:#744210;line-height:1.5">
        <strong>Officer Declaration:</strong> I confirm that I have interviewed the client ({{ $application->applicant_name }}), verified their original documents, and performed a preliminary affordability check as per the company's lending policy.
      </div>
    </div>
  </div>
</div>
@endif

    </div>

    <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:space-between;background:#fcfcfc">
      @if($step > 1)
      <a href="{{ route('officer.walk-in.step.show', [$application, $step-1]) }}" class="btn btn-o"><i class="bi bi-chevron-left"></i> Back</a>
      @else<div></div>@endif

      @if($step < 9)
        <button type="submit" class="btn btn-p">Save & Continue <i class="bi bi-chevron-right"></i></button>
      @else
        <button type="button" class="btn btn-ok" onclick="prepareSubmit()"><i class="bi bi-send-fill"></i> Submit for Review</button>
      @endif
    </div>
</form>
</div>

<!-- Final Confirmation Modal -->
<div class="mo" id="submitModal"><div class="mc" style="max-width:440px">
  <div class="mh">
    <div class="mt">Confirm Submission</div>
    <button class="cb" onclick="closeModal('submitModal')">&times;</button>
  </div>
  <div class="mb" style="text-align:center;padding:25px">
    <div style="width:60px;height:60px;background:rgba(22,163,74,.1);color:var(--ok);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:30px;margin:0 auto 15px">
      <i class="bi bi-send-check"></i>
    </div>
    <p style="font-size:14px;font-weight:600;color:var(--navy);margin-bottom:8px">Ready to Finalize?</p>
    <p style="font-size:12.5px;color:var(--muted)">This will submit the application for <strong>{{ $application->applicant_name }}</strong> to the administration for final approval.</p>
  </div>
  <div class="mf">
    <button type="button" class="btn btn-o" onclick="closeModal('submitModal')">Cancel</button>
    <form method="POST" action="{{ route('officer.walk-in.submit', $application) }}" id="finalSubmitForm">@csrf
      <input type="hidden" name="signature_data" id="modal_signature_data">
      <button type="submit" class="btn btn-ok"><i class="bi bi-send-fill"></i> Confirm & Submit</button>
    </form>
  </div>
</div></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
// Modal helpers
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.mo.open').forEach(m=>m.classList.remove('open'))})

function uploadDoc(type, btn) {
    let input = document.getElementById('file_' + type);
    if (!input || !input.files[0]) return alert('Please select a file first.');
    if (input.files[0].size > 10 * 1024 * 1024) return alert('File is too large (max 10MB).');
    
    let oldHtml = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i>...';
    btn.disabled = true;
    
    let formData = new FormData();
    formData.append('file', input.files[0]);
    formData.append('type', type);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route("officer.applications.documents.upload", $application) }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    }).then(async res => {
        if (res.ok) { 
            window.location.reload(); 
        } else {
            let data = await res.json().catch(()=>({}));
            alert(data.message || 'Upload failed. File type may be unsupported or too large.');
            btn.innerHTML = oldHtml; btn.disabled = false;
        }
    }).catch(e => { 
        alert('Network error'); 
        btn.innerHTML = oldHtml; btn.disabled = false; 
    });
}

function formatCard(input) {
  let v = input.value.replace(/\D/g,'').substring(0,16);
  input.value = v.match(/.{1,4}/g)?.join(' ')||v;
}

function formatExpiry(input, e) {
  if (e && e.inputType === 'deleteContentBackward') return;
  let v = input.value.replace(/\D/g, '').substring(0, 4);
  if (v.length >= 2) {
    v = v.substring(0, 2) + '/' + v.substring(2);
  }
  input.value = v;
}

let sigPad;
document.addEventListener("DOMContentLoaded", function() {
    const canvas = document.getElementById('signature-pad');
    if (canvas) {
        function resizeCanvas() {
            var ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }
        window.onresize = resizeCanvas;
        resizeCanvas();
        sigPad = new SignaturePad(canvas, { backgroundColor: '#f8fafc', penColor: '#0f172a' });
    }
});

function prepareSubmit() {
    if (sigPad && sigPad.isEmpty()) {
        alert('Please collect the client\'s digital signature before submitting.');
        return;
    }
    if (sigPad) {
        document.getElementById('modal_signature_data').value = sigPad.toDataURL('image/png');
    }
    openModal('submitModal');
}

function toggleCategory(val) {
    const wrap = document.getElementById('category_wrapper');
    const sel = document.getElementById('employer_category');
    if (wrap) {
        if (['government', 'sme', 'private'].includes(val)) {
            wrap.style.display = 'block';
            if (sel) {
                sel.setAttribute('required','required');
                if(val === 'private' && !sel.value) {
                    sel.value = 'Private sector';
                }
            }
        } else {
            wrap.style.display = 'none';
            if (sel) {
                sel.removeAttribute('required');
                sel.value = '';
            }
        }
    }
}

async function loadBanks() {
    const el = document.getElementById('bank_name');
    if (!el) return;
    try {
        const res = await fetch('/api/banks');
        const banks = await res.json();
        const prev = el.getAttribute('data-prev');
        banks.forEach(b => {
            const opt = new Option(b.name, b.id);
            if (b.id == prev || b.name == prev) opt.selected = true;
            el.add(opt);
        });
        if (el.value) loadBranches(el.value);
    } catch(e) {}
}

async function loadBranches(bankId) {
    const el = document.getElementById('branch_name');
    if (!el || !bankId) return;
    el.innerHTML = '<option value="">— Select Branch —</option>';
    try {
        const res = await fetch(`/api/banks/${bankId}/branches`);
        const branches = await res.json();
        const prev = el.getAttribute('data-prev');
        branches.forEach(b => {
            const opt = new Option(b.name, b.name);
            opt.setAttribute('data-code', b.code);
            if (b.name == prev) opt.selected = true;
            el.add(opt);
        });
        updateBranchCode(el.options[el.selectedIndex]);
    } catch(e) {}
}

function updateBranchCode(opt) {
    const codeEl = document.getElementById('branch_code');
    if (codeEl && opt && opt.getAttribute('data-code')) {
        codeEl.value = opt.getAttribute('data-code');
    }
}

document.addEventListener("DOMContentLoaded", function() {
    loadBanks();
});

@if($step == 6)
// ── AFFORDABILITY CALCULATOR ──
function calcAfford(){
  const earn = parseFloat(document.getElementById('fe_earn').value)||0;
  const tax  = parseFloat(document.getElementById('fe_tax').value)||0;
  const loans= parseFloat(document.getElementById('fe_loans').value)||0;
  const oD   = parseFloat(document.getElementById('fe_other_ded').value)||0;

  const expFields = ['rent','groceries','transport','utilities','education','communication', 'other_insurance','medical','other_loan_repayments','family_support','entertainment','other_expenses'];
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
    const limit = netPay * 0.70;
    const isOver = totalExp > limit;
    
    if (isOver) {
        statusEl.textContent = '✗ Fails (Expenses > 70%)';
        statusEl.style.color = 'var(--err)';
    } else {
        statusEl.textContent = disposable >= 0 ? '✓ Qualifies' : '✗ Fails (Negative)';
        statusEl.style.color = disposable >= 0 ? 'var(--ok)' : 'var(--err)';
    }
    document.getElementById('affordBadge').textContent = 'Disposable: M'+disposable.toFixed(2);
  }
}
calcAfford();

function captureGPS() {
    const status = document.getElementById('gps-status');
    const latIn = document.getElementById('gps-lat');
    const lngIn = document.getElementById('gps-lng');

    if (!navigator.geolocation) {
        status.textContent = 'Geolocation is not supported by your browser';
        return;
    }

    status.textContent = 'Locating...';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            latIn.value = lat;
            lngIn.value = lng;
            status.textContent = `Captured: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            status.style.color = 'var(--ok)';
        },
        () => {
            status.textContent = 'Unable to retrieve location. Please check permissions.';
            status.style.color = 'var(--err)';
        }
    );
}
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

  // Repayment Limit Check (30% of Net Salary)
  const netSal = {{ $application->affordability?->net_salary ?? 0 }};
  let limitMsg = document.getElementById('repaymentLimitMsg');
  if(!limitMsg){
      limitMsg = document.createElement('div');
      limitMsg.id = 'repaymentLimitMsg';
      limitMsg.style.padding = '12px';
      limitMsg.style.marginTop = '15px';
      limitMsg.style.borderRadius = '8px';
      limitMsg.style.fontSize = '12.5px';
      limitMsg.style.fontWeight = '600';
      limitMsg.style.textAlign = 'center';
      document.getElementById('calcPreview').appendChild(limitMsg);
  }
  if (netSal > 0) {
      const limit = netSal * 0.30;
      if (monthly > limit) {
          limitMsg.innerHTML = `<span style="color:var(--err)"><i class="bi bi-exclamation-triangle-fill"></i> Fails: Monthly repayment exceeds 30% of Net Salary (M${limit.toFixed(2)})</span>`;
          limitMsg.style.background = 'rgba(239,68,68,0.1)';
          limitMsg.style.border = '1px solid rgba(239,68,68,0.2)';
      } else {
          limitMsg.innerHTML = `<span style="color:var(--ok)"><i class="bi bi-check-circle-fill"></i> Qualifies: Repayment is within 30% limit</span>`;
          limitMsg.style.background = 'rgba(22,163,74,0.1)';
          limitMsg.style.border = '1px solid rgba(22,163,74,0.2)';
      }
  }
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
