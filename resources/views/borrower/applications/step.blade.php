@extends('borrower.layouts.app')
@section('title','Application — Step '.$step)

@section('content')
@php
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

{{-- Progress --}}
<div style="margin-bottom:24px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
    <div style="font-family:'Playfair Display',serif;font-size:20px;font-weight:700;color:var(--navy)">{{ $stepTitle }}</div>
    <div style="font-size:12px;color:var(--muted);font-weight:500">Step {{ $step }} of {{ $totalSteps }}</div>
  </div>
  <div style="background:#e2e8f0;border-radius:99px;height:5px">
    <div style="background:linear-gradient(90deg,var(--blue),var(--blue2));height:100%;width:{{ round($step/$totalSteps*100) }}%;border-radius:99px;transition:width .3s"></div>
  </div>
  <div style="display:flex;gap:0;margin-top:8px;overflow-x:auto;scrollbar-width:none">
    @foreach($titles as $n=>$t)
    <div style="flex:1;text-align:center;font-size:9.5px;color:{{ $n<=$step?'var(--blue)':'var(--muted)' }};font-weight:{{ $n===$step?'700':'400' }};min-width:52px;padding:0 2px">{{ substr($t,0,5) }}.</div>
    @endforeach
  </div>
</div>

<div class="card">
<form id="stepForm" method="POST" action="{{ $step === 9 ? route('borrower.apply.submit', $application) : route('borrower.apply.step.save', [$application, $step]) }}" enctype="multipart/form-data">
    @csrf
    <div class="card-body">

      {{-- STEP 1: Personal Info --}}
      @if($step === 1)
      <div class="g2">
        <div class="fg"><label class="fl">Title</label><select name="title" class="fc"><option value="">—</option>@foreach(['Mr','Mrs','Ms','Dr','Prof'] as $t)<option {{ $application->title===$t?'selected':'' }}>{{ $t }}</option>@endforeach</select></div>
        <div class="fg"><label class="fl">First Name *</label><input type="text" name="first_name" class="fc" value="{{ old('first_name',$application->first_name) }}" required></div>
        <div class="fg"><label class="fl">Surname *</label><input type="text" name="surname" class="fc" value="{{ old('surname',$application->surname) }}" required></div>
        <div class="fg"><label class="fl">Maiden Name <span style="font-size:11px;color:var(--muted);font-weight:400">(if any)</span></label><input type="text" name="maiden_name" class="fc" value="{{ old('maiden_name',$application->maiden_name) }}" placeholder="Name before marriage"></div>

        <div class="fg"><label class="fl">National ID *</label><input type="text" name="national_id" class="fc" value="{{ old('national_id',$application->national_id) }}" required></div>
        <div class="fg"><label class="fl">Date of Birth *</label><input type="date" name="date_of_birth" class="fc" value="{{ old('date_of_birth',$application->date_of_birth?->format('Y-m-d')) }}" required></div>
        <div class="fg"><label class="fl">Gender *</label><select name="gender" class="fc" required><option value="">—</option><option value="male" {{ $application->gender==='male'?'selected':'' }}>Male</option><option value="female" {{ $application->gender==='female'?'selected':'' }}>Female</option></select></div>
        <div class="fg"><label class="fl">Marital Status *</label><select name="marital_status" class="fc" required><option value="">—</option>@foreach(['single'=>'Single','married'=>'Married','divorced'=>'Divorced','widowed'=>'Widowed'] as $v=>$l)<option value="{{ $v }}" {{ $application->marital_status===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="fg"><label class="fl">Cell Number *</label><input type="tel" name="cell_number" class="fc" value="{{ old('cell_number',$application->cell_number) }}" required></div>
        <div class="fg" style="grid-column:span 2">
          <label class="fl">Email <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:11px">(optional)</span></label>
          <input type="email" name="email" class="fc" value="{{ old('email',$application->email) }}">
        </div>
      </div>

      {{-- STEP 2: Address --}}
      @elseif($step === 2)
      <div class="alert a-i" style="margin-bottom:16px"><i class="bi bi-info-circle-fill"></i> Your address is used for verification and traceability. Please be accurate.</div>
      <div class="g2">
        <div class="fg" style="grid-column:span 2"><label class="fl">Residential Address *</label><input type="text" name="residential_address" class="fc" value="{{ old('residential_address',$application->residential_address) }}" placeholder="e.g. Ha Thamae, Block 5, House 23" required></div>
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
      <div style="background:#f0f4ff;border:1px solid #d4e0d4;border-radius:8px;padding:14px 16px;margin-top:4px">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
          <div><div style="font-size:13px;font-weight:600;color:var(--navy);margin-bottom:2px"><i class="bi bi-geo-alt-fill" style="color:var(--blue);margin-right:5px"></i>Capture GPS Location <span style="font-size:11px;color:var(--muted);font-weight:400">(optional)</span></div><div style="font-size:12px;color:var(--muted)" id="gps-status">Click to capture your current coordinates.</div></div>
          <button type="button" class="btn btn-p btn-sm" onclick="captureGPS()"><i class="bi bi-crosshair"></i> Capture</button>
        </div>
        <input type="hidden" name="gps_latitude" id="gps-lat" value="{{ old('gps_latitude',$application->gps_latitude) }}">
        <input type="hidden" name="gps_longitude" id="gps-lng" value="{{ old('gps_longitude',$application->gps_longitude) }}">
      </div>

      {{-- STEP 3: Employment --}}
      @elseif($step === 3)
      @php $emp = $application->employment; @endphp
      <div class="g2">
        <div class="fg"><label class="fl">Employer Name *</label><input type="text" name="employer_name" class="fc" value="{{ old('employer_name',$emp?->employer_name) }}" required></div>
        <div class="fg"><label class="fl">Employer Type *</label><select name="employer_type" id="employer_type" class="fc" required><option value="">—</option>@foreach(['government'=>'Government','private'=>'Private Sector','sme'=>'SMEs'] as $v=>$l)<option value="{{ $v }}" {{ $emp?->employer_type===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select></div>
        <div id="category_wrapper" class="fg" style="display: {{ in_array($application->employment?->employer_type, ['government', 'sme', 'private']) ? 'block' : 'none' }}">
          <label class="fl">Work Sector / Category *</label>
          <select name="employer_category" id="employer_category" class="fc" {{ in_array($application->employment?->employer_type, ['government', 'sme', 'private']) ? 'required' : '' }}>
            <option value="">— Select Category —</option>
            @foreach(['Defence','NSS','Police','LCS','Pensioner','Civil servants','Teacher','Private sector','SMEs'] as $cat)
              <option value="{{ $cat }}" {{ (old('employer_category', $emp?->employer_category) == $cat) ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg"><label class="fl">Job Title *</label><input type="text" name="job_title" class="fc" value="{{ old('job_title',$emp?->job_title) }}" required></div>
        <div class="fg"><label class="fl">Department</label><input type="text" name="department" class="fc" value="{{ old('department',$emp?->department) }}"></div>
        <div class="fg"><label class="fl">Employment Number *</label><input type="text" name="employment_number" class="fc" value="{{ old('employment_number',$emp?->employment_number) }}" required></div>
        <div class="fg"><label class="fl">HR Contact Number *</label><input type="tel" name="contact_number" class="fc" value="{{ old('contact_number',$emp?->contact_number) }}" required></div>
        <div class="fg"><label class="fl">Employment Expiry <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:11px">(if contract)</span></label><input type="date" name="employment_expiry_date" class="fc" value="{{ old('employment_expiry_date',$emp?->employment_expiry_date?->format('Y-m-d')) }}"></div>
      </div>

      {{-- STEP 4: Bank Details --}}
      @elseif($step === 4)
      @php $bank = $application->bankDetails; @endphp
      <div class="g2">
        <div class="fg"><label class="fl">Bank Name *</label>
          <select name="bank_name" id="bank_name" class="fc" data-prev="{{ old('bank_name',$bank?->bank_name) }}" required onchange="loadBranches(this.value)">
            <option value="">— Select Bank —</option>
          </select>
        </div>
        <div class="fg"><label class="fl">Branch Name *</label>
            <select name="branch_name" id="branch_name" class="fc" data-prev="{{ old('branch_name',$bank?->branch_name) }}" required onchange="updateBranchCode(this.options[this.selectedIndex])">
                <option value="">— Select Branch —</option>
            </select>
        </div>
        <div class="fg"><label class="fl">Branch Code</label>
            <input type="text" name="branch_code" id="branch_code" class="fc" value="{{ old('branch_code',$bank?->branch_code) }}" placeholder="Auto-filled" readonly>
        </div>
        <div class="fg"><label class="fl">Account Holder Name *</label><input type="text" name="account_holder_name" class="fc" value="{{ old('account_holder_name',$bank?->account_holder_name) }}" required></div>
        <div class="fg"><label class="fl">Account Number *</label><input type="text" name="account_number" class="fc" value="{{ old('account_number',$bank?->account_number) }}" required></div>
        <div class="fg"><label class="fl">Account Type *</label><select name="account_type" class="fc" required><option value="">—</option><option value="savings" {{ old('account_type',$bank?->account_type)==='savings'?'selected':'' }}>Savings</option><option value="cheque" {{ old('account_type',$bank?->account_type)==='cheque'?'selected':'' }}>Cheque / Current</option></select></div>
      </div>

      {{-- STEP 5: Next of Kin --}}
      @elseif($step === 5)
      @php $nok = $application->nextOfKin?->first(); @endphp
      <div class="alert a-i"><i class="bi bi-info-circle-fill"></i> Emergency contact — someone we can reach if we cannot contact you.</div>
      <div class="g2">
        <div class="fg"><label class="fl">First Name *</label><input type="text" name="nok_1_first_name" class="fc" value="{{ old('nok_1_first_name',$nok?->first_name) }}" required></div>
        <div class="fg"><label class="fl">Last Name *</label><input type="text" name="nok_1_last_name" class="fc" value="{{ old('nok_1_last_name',$nok?->last_name) }}" required></div>
        <div class="fg"><label class="fl">Relationship *</label><select name="nok_1_relationship" class="fc" required><option value="">—</option>@foreach(['Spouse','Parent','Sibling','Child','Friend','Other'] as $r)<option {{ old('nok_1_relationship',$nok?->relationship)===$r?'selected':'' }}>{{ $r }}</option>@endforeach</select></div>
        <div class="fg"><label class="fl">Phone Number *</label><input type="tel" name="nok_1_phone" class="fc" value="{{ old('nok_1_phone',$nok?->contact_number) }}" required></div>
      </div>

      {{-- STEP 6: Affordability --}}
      @elseif($step === 6)
      @php $a = $application->affordability; @endphp
      <div class="g2" style="margin-bottom:20px">
        {{-- Gross --}}
        <div class="fg">
          <label class="fl">Monthly Basic Salary (M) *</label>
          <input type="number" name="monthly_earnings" class="fc" step="0.01" min="0" value="{{ old('monthly_earnings',$a?->monthly_earnings??'') }}" required oninput="calcAff()">
          <div class="ft">Your basic monthly salary before any deductions</div>
        </div>
        {{-- Total Deductions --}}
        <div>
          <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:12px">Monthly Deductions (Salary Slip)</div>
          <div class="fg" style="margin-bottom:10px"><label class="fl" style="font-size:11px">PAYE / Income Tax (M)</label><input type="number" name="tax_deduction" class="fc" step="0.01" min="0" value="{{ old('tax_deduction',$a?->tax_deduction??0) }}" oninput="calcAff()" style="padding:8px 11px"></div>
          <div class="fg" style="margin-bottom:10px"><label class="fl" style="font-size:11px">Loans (M)</label><input type="number" name="existing_loans_deduction" class="fc" step="0.01" min="0" value="{{ old('existing_loans_deduction',$a?->existing_loans_deduction??0) }}" oninput="calcAff()" style="padding:8px 11px"></div>
          <div class="fg" style="margin-bottom:10px"><label class="fl" style="font-size:11px">Pension Contribution (M)</label><input type="number" name="pension_deduction" class="fc" step="0.01" min="0" value="{{ old('pension_deduction',$a?->pension_deduction??0) }}" oninput="calcAff()" style="padding:8px 11px"></div>
          <div class="fg" style="margin-bottom:10px"><label class="fl" style="font-size:11px">Insurance (M)</label><input type="number" name="insurance_deduction" class="fc" step="0.01" min="0" value="{{ old('insurance_deduction',$a?->insurance_deduction??0) }}" oninput="calcAff()" style="padding:8px 11px"></div>
          <div class="fg" style="margin-bottom:10px"><label class="fl" style="font-size:11px">Subscriptions (M)</label><input type="number" name="subscriptions_deduction" class="fc" step="0.01" min="0" value="{{ old('subscriptions_deduction',$a?->subscriptions_deduction??0) }}" oninput="calcAff()" style="padding:8px 11px"></div>
          <div class="fg" style="margin-bottom:0"><label class="fl" style="font-size:11px">Other Deductions (M)</label><input type="number" name="other_deductions" class="fc" step="0.01" min="0" value="{{ old('other_deductions',$a?->other_deductions??0) }}" oninput="calcAff()" style="padding:8px 11px"></div>
        </div>
      </div>

      {{-- 3 summary boxes: Gross | Total Deductions | Net --}}
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:24px">
        <div style="background:#f0f4ff;border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Monthly Basic Salary</div>
          <div style="font-size:22px;font-weight:800;color:var(--navy)" id="grossDisplay">M{{ number_format($a?->monthly_earnings??0,2) }}</div>
        </div>
        <div style="background:#fff7f0;border:1px solid #fde8d0;border-radius:10px;padding:14px;text-align:center">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Total Deductions</div>
          <div style="font-size:22px;font-weight:800;color:#ea580c" id="dedDisplay">M{{ number_format(($a?->tax_deduction??0)+($a?->existing_loans_deduction??0)+($a?->other_deductions??0),2) }}</div>
        </div>
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;text-align:center">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Net Salary</div>
          <div style="font-size:22px;font-weight:800;color:#16a34a" id="netSalDisplay">M{{ number_format($a?->net_salary??0,2) }}</div>
        </div>
      </div>

      {{-- Expenses --}}
      <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:14px">Monthly Living Expenses</div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px">
        @foreach([['rent','Rent'],['groceries','Groceries'],['transport','Transport'],['utilities','Utilities'],['education','Education'],['communication','Airtime/Data'],['medical','Medical'],['other_loan_repayments','Other Bank Loans'],['other_expenses','Other Expenses']] as [$fname,$flabel])
        <div class="fg" style="margin-bottom:4px"><label class="fl" style="font-size:10.5px">{{ $flabel }} (M)</label><input type="number" name="{{ $fname }}" class="fc" step="0.01" min="0" value="{{ old($fname,$a?->$fname??0) }}" oninput="calcAff()" style="padding:8px 10px;font-size:13px"></div>
        @endforeach
      </div>

      {{-- Disposable result --}}
      <div style="background:linear-gradient(135deg,var(--navy),var(--navy3));border-radius:12px;padding:18px;text-align:center">
        <div style="font-size:10px;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px">Disposable Income</div>
        <div style="font-size:30px;font-weight:900;color:#fff" id="dispSalDisplay">M{{ number_format($a?->disposable_income??0,2) }}</div>
        <div style="font-size:12px;color:rgba(255,255,255,.6);margin-top:4px" id="affordMsg"></div>
      </div>

      {{-- STEP 7: Loan Details --}}
      @elseif($step === 7)
      <script>window.netSalary = {{ $application->affordability?->net_salary ?? 0 }};</script>
      <div class="g2">
        <div class="fg" style="grid-column:span 2">
          <label class="fl">Loan Product *</label>
          <select name="loan_product_id" class="fc" required id="prodSelect" onchange="loadProductTerms(this.value)">
            <option value="">— Select a Product —</option>
            @foreach($products as $p)
            <option value="{{ $p->id }}" {{ $application->loan_product_id==$p->id?'selected':'' }}
              data-rate="{{ $p->interest_rate }}" data-init="{{ $p->initiation_fee_rate }}"
              data-admin="{{ $p->admin_fee_fixed }}" data-min="{{ $p->min_amount }}"
              data-max="{{ $p->max_amount }}" data-minterm="{{ $p->min_term_months }}" data-maxterm="{{ $p->max_term_months }}">
              {{ $p->name }} — {{ $p->interest_rate }}%/mo · M{{ number_format($p->min_amount,0) }}–M{{ number_format($p->max_amount,0) }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Loan Amount (M) *</label>
          <input type="number" name="requested_amount" class="fc" id="amtInput" step="100" min="100" value="{{ old('requested_amount',$application->requested_amount) }}" required oninput="calcPreview()">
          <div class="ft" id="amtHint"></div>
        </div>
        <div class="fg">
          <label class="fl">Term (months) *</label>
          <input type="number" name="requested_term" class="fc" id="termInput" min="1" max="24" value="{{ old('requested_term',$application->requested_term??3) }}" required oninput="calcPreview()">
          <div class="ft" id="termHint"></div>
        </div>
        <div class="fg">
          <label class="fl">Loan Purpose *</label>
          <select name="loan_purpose" class="fc" required>
            <option value="">— Select Purpose —</option>
            @foreach(['Home Improvement','Education','Medical / Health','Business Investment','Vehicle Purchase','Debt Consolidation','School Fees','Wedding / Family Event','Funeral Costs','Groceries / Food','Clothing','Travel','Other'] as $p)
            <option {{ old('loan_purpose',$application->loan_purpose)===$p?'selected':'' }}>{{ $p }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Payout Method *</label>
          <select name="payout_method" class="fc" required>
            <option value="bank_transfer" {{ old('payout_method',$application->payout_method)==='bank_transfer'?'selected':'' }}>Bank Transfer</option>
            <option value="mobile_money" {{ old('payout_method',$application->payout_method)==='mobile_money'?'selected':'' }}>Mobile Money</option>
          </select>
        </div>
        <div class="fg">
          <label class="fl">Collection Method *</label>
          <select name="collection_method" class="fc" required>
            <option value="">— Select Method —</option>
            <option value="salary_deduction" {{ old('collection_method',$application->collection_method)==='salary_deduction'?'selected':'' }}>Salary deduction</option>
            <option value="card_payment" {{ old('collection_method',$application->collection_method)==='card_payment'?'selected':'' }}>Card payment</option>
            <option value="debit_order" {{ old('collection_method',$application->collection_method)==='debit_order'?'selected':'' }}>Debit Order</option>
          </select>
        </div>
      </div>
      <div class="fg">
        <label class="fl">Expected Payday (1-31) *</label>
        <input type="number" name="salary_payday" class="fc" min="1" max="31" value="{{ old('salary_payday', $application->salary_payday ?? 25) }}" required>
        <div style="font-size:11.5px;color:var(--muted);margin-top:6px">Determines your monthly instalment due date.</div>
      </div>
      <div id="previewBox" style="background:#f0f4ff;border:1px solid var(--border);border-radius:12px;padding:16px;display:none;margin-top:4px">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:12px">Repayment Preview</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;text-align:center">
          <div><div style="font-size:18px;font-weight:800;color:var(--blue)" id="prev-monthly">—</div><div style="font-size:11px;color:var(--muted)">Monthly</div></div>
          <div><div style="font-size:18px;font-weight:800" id="prev-total">—</div><div style="font-size:11px;color:var(--muted)">Total Repay</div></div>
          <div><div style="font-size:18px;font-weight:800" id="prev-init">—</div><div style="font-size:11px;color:var(--muted)">Initiation</div></div>
        </div>
      </div>

      {{-- STEP 8: Documents --}}
      @elseif($step === 8)
      <div style="font-size:13px;color:var(--muted);margin-bottom:20px">Upload required documents. Accepted: PDF, JPG, PNG (max 10MB each).</div>
      @foreach([['national_id','National ID'],['payslip','Latest Payslip'],['bank_statement','3 Months Bank Statement'],['photo','Selfie Picture']] as [$dtype,$dlabel])
      @php $existing = $application->documents->where('type',$dtype)->first(); @endphp
      <div style="background:#f8fafc;border-radius:12px;padding:16px;margin-bottom:12px;border:1px solid {{ $existing?'#bbf7d0':'var(--border)' }}">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <div style="font-weight:700;font-size:13.5px">{{ $dlabel }}</div>
          @if($existing)<span class="badge {{ $existing->status==='verified'?'bok':'bw' }}">{{ ucfirst($existing->status) }}</span>
          @else<span class="badge be">Required</span>@endif
        </div>
        @if(!$existing || $existing->status==='rejected')
          @if($dtype === 'photo')
            <div id="live_cam_section_{{ $dtype }}">
              <div id="cam_container_{{ $dtype }}" style="display:none; text-align:center; margin-bottom: 10px;">
                <video id="video_{{ $dtype }}" autoplay playsinline style="width:100%; max-width: 300px; border-radius: 8px; border: 1px solid var(--border);"></video>
                <canvas id="canvas_{{ $dtype }}" style="display:none;"></canvas>
                <div style="margin-top:10px; display:flex; gap: 10px; justify-content: center;">
                  <button type="button" class="btn btn-p btn-sm" onclick="captureLivePhoto('{{ $dtype }}')"><i class="bi bi-camera"></i> Capture</button>
                  <button type="button" class="btn btn-o btn-sm" onclick="stopLiveCam('{{ $dtype }}')">Cancel</button>
                </div>
              </div>
              <div id="cam_preview_container_{{ $dtype }}" style="display:none; text-align:center; margin-bottom: 10px;">
                <img id="photo_preview_{{ $dtype }}" style="width:100%; max-width: 300px; border-radius: 8px; border: 1px solid var(--border);" />
                <div style="margin-top:10px; display:flex; gap: 10px; justify-content: center;">
                  <button type="button" class="btn btn-o btn-sm" onclick="retakeLivePhoto('{{ $dtype }}')"><i class="bi bi-arrow-counterclockwise"></i> Retake</button>
                  <button type="button" id="btn_{{ $dtype }}" class="btn btn-ok btn-sm" onclick="uploadLivePhoto('{{ $dtype }}', this)"><i class="bi bi-upload"></i> Upload</button>
                </div>
              </div>
              <div id="cam_start_btn_{{ $dtype }}">
                <button type="button" class="btn btn-o btn-sm w-100" onclick="startLiveCam('{{ $dtype }}')"><i class="bi bi-camera-video"></i> Open Camera for Live Selfie</button>
                <div style="font-size:11px;color:var(--muted);margin-top:6px;text-align:center;">For fraud prevention, only live photos are accepted.</div>
              </div>
            </div>
          @else
            <div style="display:flex;gap:6px;flex-direction:column">
              @if($dtype === 'bank_statement')
              <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 12px;font-size:12px;color:#1d4ed8;margin-bottom:6px">
                <i class="bi bi-info-circle-fill"></i> You can upload <strong>multiple pages</strong> of your bank statement. Upload them one by one — all pages will be saved.
              </div>
              @endif
              <div style="display:flex;gap:6px">
                <input type="file" id="file_{{ $dtype }}" class="fc" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" {{ $dtype === 'bank_statement' ? '' : 'required' }} style="flex:1;padding:6px;font-size:12px">
                <input type="file" id="cam_{{ $dtype }}" accept="image/*" capture="environment" style="display:none" onchange="const df=new DataTransfer();df.items.add(this.files[0]);document.getElementById('file_{{ $dtype }}').files=df.files;uploadDoc('{{ $dtype }}', document.getElementById('btn_{{ $dtype }}'))">
                <button type="button" class="btn btn-o btn-sm" onclick="document.getElementById('cam_{{ $dtype }}').click()" title="Take Photo" style="padding:4px 10px"><i class="bi bi-camera" style="font-size:16px"></i></button>
                <button type="button" id="btn_{{ $dtype }}" class="btn btn-p btn-sm" onclick="uploadDoc('{{ $dtype }}', this)">Upload</button>
              </div>
              @if($dtype === 'bank_statement')
              @php $bankPages = $application->documents->where('type','bank_statement'); @endphp
              @if($bankPages->count() > 0)
              <div style="margin-top:6px">
                <div style="font-size:11px;font-weight:600;color:var(--muted);margin-bottom:6px;text-transform:uppercase">Uploaded Pages ({{ $bankPages->count() }})</div>
                @foreach($bankPages as $pg)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin-bottom:4px;font-size:12px">
                  <span><i class="bi bi-file-earmark-check-fill" style="color:#059669;margin-right:6px"></i>{{ $pg->original_name }}</span>
                  <span class="badge {{ $pg->status==='verified'?'bok':($pg->status==='rejected'?'be':'bw') }}">{{ ucfirst($pg->status) }}</span>
                </div>
                @endforeach
              </div>
              @endif
              @endif
            </div>
          @endif

        @else
        <div style="font-size:12.5px;color:var(--muted)"><i class="bi bi-check-circle-fill" style="color:var(--ok)"></i> {{ $existing->original_name }}</div>
        @endif
      </div>
      @endforeach

      {{-- STEP 9: Review & Submit --}}
      @elseif($step === 9)
      @php $fee = (float) \App\Models\SystemSetting::get('application_fee', 0); @endphp
      <div class="alert a-ok"><i class="bi bi-check-circle-fill"></i> Review everything below and put your signature before submitting.</div>
      
      @if($fee > 0 && !$application->fee_paid)
      <div style="background:rgba(79,70,229,.05);border:1px solid rgba(79,70,229,.2);border-radius:12px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:12px">
        <div style="width:40px;height:40px;border-radius:50%;background:rgba(79,70,229,.1);display:flex;align-items:center;justify-content:center;color:var(--p);flex-shrink:0"><i class="bi bi-credit-card-fill"></i></div>
        <div>
          <div style="font-weight:700;color:var(--navy);font-size:13.5px">Application Fee: M{{ number_format($fee, 2) }}</div>
          <div style="font-size:12px;color:var(--muted);line-height:1.5">Please pay a non-refundable application fee of M{{ number_format($fee, 2) }} to submit your application. Kindly note that payment of this fee does not guarantee loan approval, as all applications are subject to review and verification.</div>
        </div>
      </div>
      @endif

      {{-- Personal --}}
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Personal Information</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px">
        @foreach(['Name'=>$application->applicant_name,'ID Number'=>$application->national_id,'Cell'=>$application->cell_number,'Email'=>$application->email??'—','District'=>$application->district??'—','Residence Type'=>ucfirst($application->residence_type??'—')] as $l=>$v)
        <div style="background:#f8fafc;border-radius:8px;padding:10px 12px"><div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">{{ $l }}</div><div style="font-weight:600;margin-top:2px;font-size:13px">{{ $v }}</div></div>
        @endforeach
      </div>

      {{-- Affordability --}}
      @if($application->affordability)
      @php $aff = $application->affordability; $totalDed = ($aff->tax_deduction??0)+($aff->existing_loans_deduction??0)+($aff->other_deductions??0); @endphp
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Affordability</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:20px">
        <div style="background:#f0f4ff;border-radius:8px;padding:12px;text-align:center"><div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Monthly Basic Salary</div><div style="font-size:18px;font-weight:800;color:var(--navy)">M{{ number_format($aff->monthly_earnings??0,0) }}</div></div>
        <div style="background:#fff7f0;border-radius:8px;padding:12px;text-align:center"><div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Total Deductions</div><div style="font-size:18px;font-weight:800;color:#ea580c">M{{ number_format($totalDed,0) }}</div></div>
        <div style="background:#f0fdf4;border-radius:8px;padding:12px;text-align:center"><div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Net Salary</div><div style="font-size:18px;font-weight:800;color:#16a34a">M{{ number_format($aff->net_salary??0,0) }}</div></div>
      </div>
      @endif

      {{-- Bank Details Review --}}
      @if($application->bankDetails)
      @php $bank = $application->bankDetails; @endphp
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Bank Account Details</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px">
        @foreach(['Bank'=>$bank->bank_name,'Branch'=>$bank->branch_name,'Account Number'=>$bank->account_number,'Account Type'=>ucfirst($bank->account_type)] as $l=>$v)
        <div style="background:#f8fafc;border-radius:8px;padding:10px 12px"><div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">{{ $l }}</div><div style="font-weight:600;margin-top:2px;font-size:13px">{{ $v }}</div></div>
        @endforeach
      </div>
      @endif

      {{-- Loan details --}}
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Loan Details</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px">
        @foreach(['Product'=>$application->loanProduct?->name??'—','Amount'=>'M '.number_format($application->requested_amount??0,2),'Term'=>($application->requested_term??'—').' months','Purpose'=>$application->loan_purpose??'—','Collection'=>ucfirst(str_replace('_',' ',$application->collection_method??'—')),'Payout'=>ucfirst(str_replace('_',' ',$application->payout_method??'—'))] as $l=>$v)
        <div style="background:#f8fafc;border-radius:8px;padding:10px 12px"><div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">{{ $l }}</div><div style="font-weight:600;margin-top:2px;font-size:13px">{{ $v }}</div></div>
        @endforeach
      </div>

      {{-- Documents --}}
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px">Documents</div>
      <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px">
        @foreach(['national_id'=>'National ID','payslip'=>'Payslip','bank_statement'=>'Bank Statement','photo'=>'Selfie Picture'] as $dtype=>$dlabel)
        @php $doc = $application->documents->where('type',$dtype)->first(); @endphp
        <div style="background:{{ $doc?'#f0fdf4':'#fef2f2' }};border:1px solid {{ $doc?'#bbf7d0':'#fecaca' }};border-radius:8px;padding:8px 14px;font-size:12.5px;font-weight:600;color:{{ $doc?'#16a34a':'#dc2626' }}">
          <i class="bi bi-{{ $doc?'check-circle-fill':'x-circle-fill' }}"></i> {{ $dlabel }}
        </div>
        @endforeach
      </div>

      <div style="background:#f8fafc;border:1px dashed var(--border);border-radius:10px;padding:16px;margin-bottom:16px">
        <div style="font-size:13px;font-weight:600;margin-bottom:10px;color:var(--navy)"><i class="bi bi-upload" style="margin-right:6px;color:var(--blue)"></i>Upload Additional Documents</div>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
            <select name="type" class="fc" required style="padding:9px 11px;font-size:13px" form="uploadForm_step9">
              <option value="">— Document Type —</option>
              @foreach(['national_id'=>'National ID','payslip'=>'Payslip','bank_statement'=>'Bank Statement','other'=>'Other'] as $v=>$l)
              <option value="{{ $v }}">{{ $l }}</option>
              @endforeach
            </select>
            <input type="file" id="file_step9" name="file" class="fc" accept=".pdf,.jpg,.jpeg,.png" required style="padding:7px 11px;font-size:13px" form="uploadForm_step9">
            <div style="display:flex;gap:6px">
              <input type="file" id="cam_step9" accept="image/*" capture="environment" style="display:none" onchange="if(this.files.length){const df=new DataTransfer();df.items.add(this.files[0]);document.getElementById('file_step9').files=df.files;document.getElementById('uploadForm_step9').submit();}">
              <button type="button" class="btn btn-o btn-sm" onclick="document.getElementById('cam_step9').click()" title="Take Photo" style="height:40px;padding:0 12px"><i class="bi bi-camera" style="font-size:16px"></i></button>
              <button type="submit" class="btn btn-p btn-sm" style="height:40px;flex:1" form="uploadForm_step9">Upload</button>
            </div>
          </div>
      </div>

      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);margin-bottom:10px;margin-top:24px">Digital Signature *</div>
      <div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:16px;text-align:center">
        <canvas id="signature-pad" style="width:100%;max-width:400px;height:150px;border:1px dashed #cbd5e1;border-radius:6px;background:#f8fafc;touch-action:none"></canvas>
        <div style="margin-top:8px;display:flex;justify-content:space-between;align-items:center;max-width:400px;margin:8px auto 0">
          <span style="font-size:11px;color:var(--muted)">Sign above using your mouse or finger</span>
          <button type="button" class="btn btn-o btn-sm" onclick="sigPad.clear()" style="padding:4px 10px;font-size:11px">Clear</button>
        </div>
      </div>
      <input type="hidden" name="signature_data" id="signature_data">

      <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:10px;padding:12px 14px;font-size:13px;color:#92400e">
        <i class="bi bi-info-circle-fill"></i> By submitting you confirm all information is accurate, provide your digital signature, and agree to Prosperity Loans's loan terms and conditions.
      </div>
      @endif

    </div>

    <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:space-between;gap:10px">
      @if($step > 1)
      <a href="{{ route('borrower.apply.step.show', [$application, $step-1]) }}" class="btn btn-o"><i class="bi bi-chevron-left"></i> Back</a>
      @else<div></div>@endif
      @if($step < 9)
      <button type="submit" class="btn btn-p">Save & Continue <i class="bi bi-chevron-right"></i></button>
      @else
      <button type="submit" class="btn btn-ok"><i class="bi bi-send-fill"></i> Submit Application</button>
      @endif
    </div>
  </form>
</div>

@if($step === 8)
<script>
function uploadDoc(type, btn) {
    let input = document.getElementById('file_' + type);
    if (!input.files[0]) return alert('Please select a file first.');
    if (input.files[0].size > 10 * 1024 * 1024) return alert('File is too large (max 10MB).');
    let oldHtml = btn.innerHTML;
    btn.innerHTML = 'Uploading...';
    btn.disabled = true;
    let formData = new FormData();
    formData.append('file', input.files[0]);
    formData.append('type', type);
    formData.append('_token', '{{ csrf_token() }}');
    fetch('{{ route("borrower.documents.upload.application", $application) }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    }).then(async res => {
        if (res.ok) { window.location.reload(); }
        else {
            let data = await res.json().catch(()=>({}));
            alert(data.message || 'Upload failed. File type may be unsupported or too large.');
            btn.innerHTML = oldHtml; btn.disabled = false;
        }
    }).catch(e => { alert('Network error'); btn.innerHTML = oldHtml; btn.disabled = false; });
}

let liveStream = null;
let capturedBlob = null;

async function startLiveCam(dtype) {
    try {
        liveStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false });
        const video = document.getElementById('video_' + dtype);
        video.srcObject = liveStream;
        document.getElementById('cam_container_' + dtype).style.display = 'block';
        document.getElementById('cam_start_btn_' + dtype).style.display = 'none';
        document.getElementById('cam_preview_container_' + dtype).style.display = 'none';
    } catch (err) {
        alert("Camera access denied or not available. Please allow camera permissions to take a live selfie.");
    }
}

function stopLiveCam(dtype) {
    if (liveStream) {
        liveStream.getTracks().forEach(track => track.stop());
        liveStream = null;
    }
    document.getElementById('cam_container_' + dtype).style.display = 'none';
    document.getElementById('cam_start_btn_' + dtype).style.display = 'block';
}

function captureLivePhoto(dtype) {
    const video = document.getElementById('video_' + dtype);
    const canvas = document.getElementById('canvas_' + dtype);
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Convert to blob
    canvas.toBlob(blob => {
        capturedBlob = blob;
        const preview = document.getElementById('photo_preview_' + dtype);
        preview.src = URL.createObjectURL(blob);
        
        // Stop stream
        if (liveStream) {
            liveStream.getTracks().forEach(track => track.stop());
            liveStream = null;
        }
        
        document.getElementById('cam_container_' + dtype).style.display = 'none';
        document.getElementById('cam_preview_container_' + dtype).style.display = 'block';
    }, 'image/jpeg', 0.8);
}

function retakeLivePhoto(dtype) {
    capturedBlob = null;
    document.getElementById('cam_preview_container_' + dtype).style.display = 'none';
    startLiveCam(dtype);
}

function uploadLivePhoto(dtype, btn) {
    if (!capturedBlob) return alert('Please capture a photo first.');
    let oldHtml = btn.innerHTML;
    btn.innerHTML = 'Uploading...';
    btn.disabled = true;
    
    let formData = new FormData();
    formData.append('file', capturedBlob, 'selfie.jpg');
    formData.append('type', dtype);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route("borrower.documents.upload.application", $application) }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    }).then(async res => {
        if (res.ok) { window.location.reload(); }
        else {
            let data = await res.json().catch(()=>({}));
            alert(data.message || 'Upload failed.');
            btn.innerHTML = oldHtml; btn.disabled = false;
        }
    }).catch(e => { alert('Network error'); btn.innerHTML = oldHtml; btn.disabled = false; });
}
</script>
@endif

@if($step === 9)
    <form id="uploadForm_step9" method="POST" action="{{ route('borrower.documents.upload.application',$application) }}" enctype="multipart/form-data">
        @csrf
    </form>
@endif

@push('scripts')
<script>
// Step 6: Affordability
function calcAff() {
  const gross = parseFloat(document.querySelector('[name=monthly_earnings]')?.value||0)||0;
  const ded = ['tax_deduction','existing_loans_deduction','pension_deduction','insurance_deduction','subscriptions_deduction','other_deductions'].reduce((s,n)=>s+(parseFloat(document.querySelector('[name='+n+']')?.value||0)||0),0);
  const expenses = ['rent','groceries','transport','utilities','education','communication','medical','other_loan_repayments','other_expenses'].reduce((s,n)=>s+(parseFloat(document.querySelector('[name='+n+']')?.value||0)||0),0);
  const net = gross - ded;
  const disp = net - expenses;
  if(document.getElementById('grossDisplay')) document.getElementById('grossDisplay').textContent = 'M'+gross.toFixed(2);
  if(document.getElementById('dedDisplay'))   document.getElementById('dedDisplay').textContent   = 'M'+ded.toFixed(2);
  if(document.getElementById('netSalDisplay'))document.getElementById('netSalDisplay').textContent= 'M'+net.toFixed(2);
  if(document.getElementById('dispSalDisplay'))document.getElementById('dispSalDisplay').textContent= 'M'+disp.toFixed(2);
  const msg = document.getElementById('affordMsg');
  if(msg) {
    const maxExp = net * 0.70;
    if (expenses > maxExp) {
      msg.innerHTML = '<span style="color:#ef4444;font-weight:bold">⚠ Total expenses cannot exceed 70% of your net salary.</span>';
    } else {
      msg.textContent = disp >= 0 ? '✓ Good — you have surplus income' : '⚠ Expenses exceed income';
    }
  }
}
calcAff();

// Step 7: Product preview
function loadProductTerms(id) {
  const sel = document.getElementById('prodSelect');
  if(!sel) return;
  const opt = sel.options[sel.selectedIndex];
  const min=opt.dataset.min, max=opt.dataset.max, mint=opt.dataset.minterm, maxt=opt.dataset.maxterm;
  if(document.getElementById('amtHint'))  document.getElementById('amtHint').textContent  = min&&max  ? `Min: M${Number(min).toLocaleString()} · Max: M${Number(max).toLocaleString()}` : '';
  if(document.getElementById('termHint')) document.getElementById('termHint').textContent = mint&&maxt ? `${mint} – ${maxt} months` : '';
  calcPreview();
}
function calcPreview() {
  const sel = document.getElementById('prodSelect');
  if(!sel||!sel.value) return;
  const opt = sel.options[sel.selectedIndex];
  const p=parseFloat(document.getElementById('amtInput')?.value||0)||0;
  const t=parseInt(document.getElementById('termInput')?.value||0)||0;
  const rate=parseFloat(opt.dataset.rate||15)/100;
  const initR=parseFloat(opt.dataset.init||40)/100;
  const admin=parseFloat(opt.dataset.admin||50);
  if(!p||!t){document.getElementById('previewBox').style.display='none';return;}
  const totalInt=p*rate*t; const initFee=p*initR; const total=p+totalInt+initFee+(admin*t); const monthly=total/t;
  document.getElementById('prev-monthly').textContent='M'+monthly.toFixed(2);
  document.getElementById('prev-total').textContent='M'+total.toFixed(2);
  document.getElementById('prev-init').textContent='M'+initFee.toFixed(2);
  
  if (window.netSalary) {
      const maxRepayment = window.netSalary * 0.30;
      let limitMsg = document.getElementById('repaymentLimitMsg');
      if (!limitMsg) {
          limitMsg = document.createElement('div');
          limitMsg.id = 'repaymentLimitMsg';
          limitMsg.style.marginTop = '10px';
          limitMsg.style.fontSize = '13px';
          limitMsg.style.fontWeight = '600';
          limitMsg.style.textAlign = 'center';
          document.getElementById('previewBox').appendChild(limitMsg);
      }
      if (monthly > maxRepayment) {
          limitMsg.innerHTML = '<span style="color:#ef4444">⚠ Monthly repayment (M' + monthly.toFixed(2) + ') exceeds 30% of your net salary (M' + maxRepayment.toFixed(2) + '). Please lower loan amount or increase term.</span>';
      } else {
          limitMsg.innerHTML = '<span style="color:#16a34a">✓ Good - Repayment is within 30% limit.</span>';
      }
  }

  document.getElementById('previewBox').style.display='';
}
if(document.getElementById('prodSelect')?.value) loadProductTerms(document.getElementById('prodSelect').value);

// Step 9: Card formatting
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

// Step 2: GPS
function captureGPS() {
  const s=document.getElementById('gps-status');
  if(!navigator.geolocation){s.textContent='GPS not supported.';return;}
  s.textContent='Capturing...';
  navigator.geolocation.getCurrentPosition(
    pos=>{
      document.getElementById('gps-lat').value=pos.coords.latitude.toFixed(6);
      document.getElementById('gps-lng').value=pos.coords.longitude.toFixed(6);
      s.innerHTML='<span style="color:#16a34a;font-weight:600">✓ Location captured ('+pos.coords.latitude.toFixed(4)+', '+pos.coords.longitude.toFixed(4)+')</span>';
    },
    ()=>{s.textContent='Could not capture. Please enter address manually.';}
  ,{enableHighAccuracy:true,timeout:10000});
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

document.addEventListener("DOMContentLoaded", loadBanks);
</script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const typeSelect = document.getElementById('employer_type');
    const catWrapper = document.getElementById('category_wrapper');
    const catSelect  = document.getElementById('employer_category');

    if (typeSelect && catWrapper) {
        typeSelect.addEventListener('change', function() {
            if (['government', 'sme', 'private'].includes(this.value)) {
                catWrapper.style.display = 'block';
                catSelect.setAttribute('required', 'required');
                if(this.value === 'private' && !catSelect.value) {
                    catSelect.value = 'Private sector';
                }
            } else {
                catWrapper.style.display = 'none';
                catSelect.removeAttribute('required');
                catSelect.value = '';
            }
        });
    }
});
</script>
<script>
// Step 10: Signature Pad
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
        
        document.getElementById('stepForm').addEventListener('submit', function(e) {
            if (sigPad.isEmpty()) {
                e.preventDefault();
                alert('Please provide your digital signature before submitting.');
            } else {
                document.getElementById('signature_data').value = sigPad.toDataURL('image/png');
            }
        });
    }
});
</script>
@endpush
@endsection
