@extends('borrower.layouts.app')
@section('title','Application — Step '.$step)

@section('content')
@php
$titles = [1=>'Personal Information',2=>'Address Details',3=>'Employment Details',4=>'Bank Details',5=>'Next of Kin',6=>'Affordability Calculator',7=>'Loan Details',8=>'Upload Documents',9=>'Review & Submit'];
$stepTitle = $titles[$step] ?? 'Step '.$step;
@endphp

{{-- Progress --}}
<div style="margin-bottom:24px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
    <div style="font-size:16px;font-weight:800">{{ $stepTitle }}</div>
    <div style="font-size:12px;color:var(--muted)">Step {{ $step }} of 9</div>
  </div>
  <div style="background:#e2e8f0;border-radius:99px;height:6px">
    <div style="background:linear-gradient(90deg,var(--p),var(--s));height:100%;width:{{ round($step/9*100) }}%;border-radius:99px;transition:width .3s"></div>
  </div>
  <div style="display:flex;gap:0;margin-top:8px;overflow-x:auto">
    @foreach($titles as $n=>$t)
    <div style="flex:1;text-align:center;font-size:10px;color:{{ $n<=$step?'var(--p)':'var(--muted)' }};font-weight:{{ $n===$step?'700':'400' }};min-width:60px">{{ substr($t,0,6) }}.</div>
    @endforeach
  </div>
</div>

<div class="card">
  <form method="POST" action="{{ route('borrower.apply.step.save', [$application, $step]) }}">
    @csrf
    <div class="card-body">

      {{-- STEP 1: Personal Info --}}
      @if($step === 1)
      <div class="g2">
        <div class="fg"><label class="fl">Title</label><select name="title" class="fc"><option value="">—</option>@foreach(['Mr','Mrs','Ms','Dr','Prof'] as $t)<option {{ $application->title===$t?'selected':'' }}>{{ $t }}</option>@endforeach</select></div>
        <div class="fg"><label class="fl">First Name *</label><input type="text" name="first_name" class="fc" value="{{ old('first_name',$application->first_name) }}" required></div>
        <div class="fg"><label class="fl">Surname *</label><input type="text" name="surname" class="fc" value="{{ old('surname',$application->surname) }}" required></div>
        <div class="fg"><label class="fl">National ID *</label><input type="text" name="national_id" class="fc" value="{{ old('national_id',$application->national_id) }}" required></div>
        <div class="fg"><label class="fl">Date of Birth</label><input type="date" name="date_of_birth" class="fc" value="{{ old('date_of_birth',$application->date_of_birth?->format('Y-m-d')) }}"></div>
        <div class="fg"><label class="fl">Gender</label><select name="gender" class="fc"><option value="">—</option><option value="male" {{ $application->gender==='male'?'selected':'' }}>Male</option><option value="female" {{ $application->gender==='female'?'selected':'' }}>Female</option></select></div>
        <div class="fg"><label class="fl">Marital Status</label><select name="marital_status" class="fc"><option value="">—</option>@foreach(['single'=>'Single','married'=>'Married','divorced'=>'Divorced','widowed'=>'Widowed'] as $v=>$l)<option value="{{ $v }}" {{ $application->marital_status===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="fg"><label class="fl">Cell Number *</label><input type="tel" name="cell_number" class="fc" value="{{ old('cell_number',$application->cell_number) }}" required></div>
        <div class="fg"><label class="fl">Email</label><input type="email" name="email" class="fc" value="{{ old('email',$application->email) }}"></div>
      </div>

      {{-- STEP 2: Address --}}
      @elseif($step === 2)
      <div style="margin-bottom:18px;padding:12px 16px;background:rgba(13,27,62,.05);border-left:3px solid var(--navy,#0d1b3e);border-radius:0 6px 6px 0;font-size:13px;color:#4b5a72">
        <i class="bi bi-info-circle-fill" style="margin-right:6px;color:#2b4bad"></i>
        Your address is required for verification and traceability. Please be as accurate as possible.
      </div>
      <div class="g2">
        <div class="fg" style="grid-column:span 2">
          <label class="fl">Residential Address *</label>
          <input type="text" name="residential_address" class="fc" value="{{ old('residential_address',$application->residential_address) }}" placeholder="e.g. Ha Thamae, Block 5, House No. 23" required>
        </div>
        <div class="fg">
          <label class="fl">Village / Area *</label>
          <input type="text" name="village" class="fc" value="{{ old('village',$application->village) }}" placeholder="e.g. Ha Thamae" required>
        </div>
        <div class="fg">
          <label class="fl">Town / City *</label>
          <input type="text" name="town" class="fc" value="{{ old('town',$application->town) }}" placeholder="e.g. Maseru" required>
        </div>
        <div class="fg">
          <label class="fl">District *</label>
          <select name="district" class="fc" required>
            <option value="">— Select District —</option>
            @foreach(['Maseru','Berea','Leribe','Butha-Buthe','Mafeteng','Mohale\'s Hoek','Qacha\'s Nek','Quthing','Thaba-Tseka','Mokhotlong'] as $d)
            <option {{ old('district',$application->district)===$d?'selected':'' }}>{{ $d }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Duration at this Address *</label>
          <select name="address_duration" class="fc" required>
            <option value="">— Select —</option>
            <option value="less_than_6_months" {{ old('address_duration',$application->address_duration)==='less_than_6_months'?'selected':'' }}>Less than 6 months</option>
            <option value="6_to_12_months" {{ old('address_duration',$application->address_duration)==='6_to_12_months'?'selected':'' }}>6 – 12 months</option>
            <option value="1_to_3_years" {{ old('address_duration',$application->address_duration)==='1_to_3_years'?'selected':'' }}>1 – 3 years</option>
            <option value="3_to_5_years" {{ old('address_duration',$application->address_duration)==='3_to_5_years'?'selected':'' }}>3 – 5 years</option>
            <option value="more_than_5_years" {{ old('address_duration',$application->address_duration)==='more_than_5_years'?'selected':'' }}>More than 5 years</option>
          </select>
        </div>
        <div class="fg">
          <label class="fl">Residence Type *</label>
          <select name="residence_type" class="fc" required>
            <option value="">— Select —</option>
            <option value="own" {{ old('residence_type',$application->residence_type)==='own'?'selected':'' }}>Own</option>
            <option value="rent" {{ old('residence_type',$application->residence_type)==='rent'?'selected':'' }}>Rent</option>
            <option value="family" {{ old('residence_type',$application->residence_type)==='family'?'selected':'' }}>Family</option>
            <option value="employer" {{ old('residence_type',$application->residence_type)==='employer'?'selected':'' }}>Employer Provided</option>
          </select>
        </div>
        <div class="fg" style="grid-column:span 2">
          <label class="fl">Nearest Landmark <span style="color:#4b5a72;font-weight:400;text-transform:none;font-size:12px">(name of school, church, shop, etc.)</span></label>
          <input type="text" name="nearest_landmark" class="fc" value="{{ old('nearest_landmark',$application->nearest_landmark) }}" placeholder="e.g. Next to Maseru West Primary School">
        </div>
        <div class="fg" style="grid-column:span 2">
          <label class="fl">Directions to Your Home <span style="color:#4b5a72;font-weight:400;text-transform:none;font-size:12px">(describe how to find you)</span></label>
          <textarea name="home_directions" class="fc" rows="3" placeholder="e.g. From the main road, turn left at the Shell garage, third house on the right, green gate.">{{ old('home_directions',$application->home_directions) }}</textarea>
        </div>
      </div>
      {{-- GPS Location Capture --}}
      <div style="background:#f4f7ff;border:1px solid #dde3ef;border-radius:8px;padding:16px 18px;margin-top:4px">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
          <div>
            <div style="font-size:13px;font-weight:600;color:#0d1b3e;margin-bottom:3px"><i class="bi bi-geo-alt-fill" style="color:#2b4bad;margin-right:5px"></i> Capture My GPS Location</div>
            <div style="font-size:12px;color:#4b5a72" id="gps-status">Click the button to capture your current location.</div>
          </div>
          <button type="button" class="btn btn-sm" style="background:#2b4bad;color:#fff;flex-shrink:0" onclick="captureGPS()">
            <i class="bi bi-crosshair"></i> Capture Location
          </button>
        </div>
        <input type="hidden" name="gps_latitude" id="gps-lat" value="{{ old('gps_latitude',$application->gps_latitude) }}">
        <input type="hidden" name="gps_longitude" id="gps-lng" value="{{ old('gps_longitude',$application->gps_longitude) }}">
      </div>

      {{-- STEP 3: Employment --}}
      @elseif($step === 3)
      @php $emp = $application->employment; @endphp
      <div class="g2">
        <div class="fg"><label class="fl">Employer Name *</label><input type="text" name="employer_name" class="fc" value="{{ old('employer_name',$emp?->employer_name) }}" required></div>
        <div class="fg"><label class="fl">Employer Type</label><select name="employer_type" class="fc"><option value="">—</option>@foreach(['government'=>'Government','private'=>'Private Sector','ngo'=>'NGO / Non-profit','self_employed'=>'Self Employed'] as $v=>$l)<option value="{{ $v }}" {{ $emp?->employer_type===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="fg"><label class="fl">Job Title *</label><input type="text" name="job_title" class="fc" value="{{ old('job_title',$emp?->job_title) }}" required></div>
        <div class="fg"><label class="fl">Department</label><input type="text" name="department" class="fc" value="{{ old('department',$emp?->department) }}"></div>
        <div class="fg"><label class="fl">Employment Number</label><input type="text" name="employment_number" class="fc" value="{{ old('employment_number',$emp?->employment_number) }}"></div>
        <div class="fg"><label class="fl">HR Contact Number</label><input type="tel" name="contact_number" class="fc" value="{{ old('contact_number',$emp?->contact_number) }}"></div>
        <div class="fg"><label class="fl">Employment Expiry Date <span style="color:var(--muted);font-size:11px">(if contract)</span></label><input type="date" name="employment_expiry_date" class="fc" value="{{ old('employment_expiry_date',$emp?->employment_expiry_date?->format('Y-m-d')) }}"></div>
      </div>

      {{-- STEP 4: Bank Details --}}
      @elseif($step === 4)
      @php $bank = $application->bankDetails; @endphp
      <div class="g2">
        <div class="fg"><label class="fl">Bank Name *</label><select name="bank_name" class="fc" required><option value="">— Select Bank —</option>@foreach(['Lesotho PostBank','Standard Lesotho Bank','Nedbank Lesotho','First National Bank Lesotho'] as $b)<option {{ $bank?->bank_name===$b?'selected':'' }}>{{ $b }}</option>@endforeach<option {{ !in_array($bank?->bank_name,['Lesotho PostBank','Standard Lesotho Bank','Nedbank Lesotho','First National Bank Lesotho'])?'selected':'' }}>Other</option></select></div>
        <div class="fg"><label class="fl">Account Holder Name *</label><input type="text" name="account_holder_name" class="fc" value="{{ old('account_holder_name',$bank?->account_holder_name) }}" required></div>
        <div class="fg"><label class="fl">Account Number *</label><input type="text" name="account_number" class="fc" value="{{ old('account_number',$bank?->account_number) }}" required></div>
        <div class="fg"><label class="fl">Account Type *</label><select name="account_type" class="fc" required><option value="">—</option><option value="savings" {{ $bank?->account_type==='savings'?'selected':'' }}>Savings</option><option value="cheque" {{ $bank?->account_type==='cheque'?'selected':'' }}>Cheque / Current</option></select></div>
      </div>

      {{-- STEP 5: Next of Kin --}}
      @elseif($step === 5)
      @php $nok = $application->nextOfKin->first(); @endphp
      <div style="font-size:12px;color:var(--muted);margin-bottom:16px">Please provide details of someone we can contact in case of emergency.</div>
      <div class="g2">
        <div class="fg"><label class="fl">First Name *</label><input type="text" name="nok_1_first_name" class="fc" value="{{ old('nok_1_first_name',$nok?->first_name) }}" required></div>
        <div class="fg"><label class="fl">Last Name *</label><input type="text" name="nok_1_last_name" class="fc" value="{{ old('nok_1_last_name',$nok?->last_name) }}" required></div>
        <div class="fg"><label class="fl">Relationship *</label><select name="nok_1_relationship" class="fc" required><option value="">—</option>@foreach(['Spouse','Parent','Sibling','Child','Friend','Other'] as $r)<option {{ $nok?->relationship===$r?'selected':'' }}>{{ $r }}</option>@endforeach</select></div>
        <div class="fg"><label class="fl">Phone Number *</label><input type="tel" name="nok_1_phone" class="fc" value="{{ old('nok_1_phone',$nok?->contact_number) }}" required></div>
      </div>

      {{-- STEP 6: Affordability --}}
      @elseif($step === 6)
      @php $a = $application->affordability; @endphp
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <div>
          <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:12px">Monthly Income</div>
          <div class="fg"><label class="fl">Gross Salary (M) *</label><input type="number" name="monthly_earnings" class="fc" step="0.01" min="0" value="{{ old('monthly_earnings',$a?->monthly_earnings??'') }}" required oninput="calcDisposable()"></div>
          <div class="fg"><label class="fl">Tax Deductions (M)</label><input type="number" name="tax_deduction" class="fc" step="0.01" min="0" value="{{ old('tax_deduction',$a?->tax_deduction??0) }}" oninput="calcDisposable()"></div>
          <div class="fg"><label class="fl">Existing Loan Deductions (M)</label><input type="number" name="existing_loans_deduction" class="fc" step="0.01" min="0" value="{{ old('existing_loans_deduction',$a?->existing_loans_deduction??0) }}" oninput="calcDisposable()"></div>
          <div class="fg"><label class="fl">Other Deductions (M)</label><input type="number" name="other_deductions" class="fc" step="0.01" min="0" value="{{ old('other_deductions',$a?->other_deductions??0) }}" oninput="calcDisposable()"></div>
          <div style="background:#f0fdf4;border-radius:10px;padding:14px;text-align:center;border:1px solid #bbf7d0">
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em">Net Salary</div>
            <div style="font-size:24px;font-weight:800;color:var(--p)" id="netSalDisplay">M{{ number_format($a?->net_salary??0,2) }}</div>
          </div>
        </div>
        <div>
          <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:12px">Monthly Expenses</div>
          @foreach([['rent','Rent / Housing'],['groceries','Groceries'],['transport','Transport'],['utilities','Utilities'],['education','Education'],['communication','Communication'],['medical','Medical'],['other_loan_repayments','Other Loan Repayments'],['other_expenses','Other Expenses']] as [$fname,$flabel])
          <div class="fg" style="margin-bottom:10px"><label class="fl" style="font-size:11.5px">{{ $flabel }} (M)</label><input type="number" name="{{ $fname }}" class="fc" step="0.01" min="0" value="{{ old($fname,$a?->$fname??0) }}" oninput="calcDisposable()" style="padding:7px 12px"></div>
          @endforeach
        </div>
      </div>
      <div style="background:linear-gradient(135deg,var(--p),var(--pl));border-radius:12px;padding:18px;text-align:center;margin-top:10px">
        <div style="font-size:11px;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px">Disposable Income</div>
        <div style="font-size:28px;font-weight:900;color:#fff" id="dispSalDisplay">M{{ number_format($a?->disposable_income??0,2) }}</div>
        <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:4px" id="affordMsg"></div>
      </div>

      {{-- STEP 7: Loan Details --}}
      @elseif($step === 7)
      <div class="g2">
        <div class="fg" style="grid-column:span 2">
          <label class="fl">Loan Product *</label>
          <select name="loan_product_id" class="fc" required id="prodSelect" onchange="loadProductTerms(this.value)">
            <option value="">— Select a Loan Product —</option>
            @foreach($products as $p)
            <option value="{{ $p->id }}" {{ $application->loan_product_id==$p->id?'selected':'' }} data-rate="{{ $p->interest_rate }}" data-init="{{ $p->initiation_fee_rate }}" data-admin="{{ $p->admin_fee_fixed }}" data-min="{{ $p->min_amount }}" data-max="{{ $p->max_amount }}" data-minterm="{{ $p->min_term_months }}" data-maxterm="{{ $p->max_term_months }}">
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
          <label class="fl">Loan Purpose</label>
          <select name="loan_purpose" class="fc"><option value="">—</option>@foreach(['Home Improvement','Education','Medical','Business','Vehicle','Debt Consolidation','Other'] as $p)<option {{ $application->loan_purpose===$p?'selected':'' }}>{{ $p }}</option>@endforeach</select>
        </div>
        <div class="fg">
          <label class="fl">Payout Method</label>
          <select name="payout_method" class="fc"><option value="bank_transfer" {{ $application->payout_method==='bank_transfer'?'selected':'' }}>Bank Transfer</option><option value="mobile_money" {{ $application->payout_method==='mobile_money'?'selected':'' }}>Mobile Money</option></select>
        </div>
        <div class="fg">
          <label class="fl">Collection Method</label>
          <select name="collection_method" class="fc"><option value="salary_deduction" {{ $application->collection_method==='salary_deduction'?'selected':'' }}>Salary Deduction</option><option value="debit_order" {{ $application->collection_method==='debit_order'?'selected':'' }}>Debit Order</option><option value="mobile_money" {{ $application->collection_method==='mobile_money'?'selected':'' }}>Mobile Money</option></select>
        </div>
      </div>
      {{-- Preview box --}}
      <div id="previewBox" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px;display:none">
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:12px">Repayment Preview</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;text-align:center">
          <div><div style="font-size:18px;font-weight:800;color:var(--p)" id="prev-monthly">—</div><div style="font-size:11px;color:var(--muted)">Monthly</div></div>
          <div><div style="font-size:18px;font-weight:800" id="prev-total">—</div><div style="font-size:11px;color:var(--muted)">Total Repay</div></div>
          <div><div style="font-size:18px;font-weight:800" id="prev-init">—</div><div style="font-size:11px;color:var(--muted)">Initiation Fee</div></div>
        </div>
      </div>

      {{-- STEP 8: Documents --}}
      @elseif($step === 8)
      <div style="font-size:13px;color:var(--muted);margin-bottom:20px">Please upload the required documents. Accepted formats: PDF, JPG, PNG (max 5MB each).</div>
      @foreach([['national_id','National ID / Passport'],['payslip','Latest Payslip'],['bank_statement','3 Months Bank Statement']] as [$dtype,$dlabel])
      @php $existing = $application->documents->where('type',$dtype)->first(); @endphp
      <div style="background:#f8fafc;border-radius:12px;padding:16px;margin-bottom:12px;border:1px solid {{ $existing?'#bbf7d0':'var(--border)' }}">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <div style="font-weight:700;font-size:13.5px">{{ $dlabel }}</div>
          @if($existing)
          <span class="badge {{ $existing->status==='verified'?'bok':'bw' }}">{{ ucfirst($existing->status) }}</span>
          @else
          <span class="badge be">Required</span>
          @endif
        </div>
        @if(!$existing || $existing->status==='rejected')
        <form method="POST" action="{{ route('borrower.documents.upload.application',$application) }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="type" value="{{ $dtype }}">
          <div style="display:flex;gap:8px">
            <input type="file" name="file" class="fc" accept=".pdf,.jpg,.jpeg,.png" required style="flex:1">
            <button type="submit" class="btn btn-p btn-sm">Upload</button>
          </div>
        </form>
        @else
        <div style="font-size:12.5px;color:var(--muted)"><i class="bi bi-check-circle-fill" style="color:var(--ok)"></i> {{ $existing->original_name }}</div>
        @endif
      </div>
      @endforeach

      {{-- STEP 9: Review --}}
      @elseif($step === 9)
      <div class="alert a-ok"><i class="bi bi-check-circle-fill"></i> Please review your application before submitting.</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px">
        @foreach(['Name'=>$application->applicant_name,'ID Number'=>$application->national_id,'Cell'=>$application->cell_number,'Employer'=>$application->employment?->employer_name??'—','Product'=>$application->loanProduct?->name??'—','Amount'=>'M '.number_format($application->requested_amount??0,2),'Term'=>($application->requested_term??'—').' months','Purpose'=>$application->loan_purpose??'—'] as $l=>$v)
        <div style="background:#f8fafc;border-radius:8px;padding:10px 12px"><div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em">{{ $l }}</div><div style="font-weight:600;margin-top:2px">{{ $v }}</div></div>
        @endforeach
      </div>
      <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:10px;padding:12px 14px;margin-top:16px;font-size:13px;color:#92400e">
        <i class="bi bi-info-circle-fill"></i> By submitting you confirm all information is accurate and you agree to the loan terms.
      </div>
      @endif

    </div>

    <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:space-between;gap:10px">
      @if($step > 1)
      <a href="{{ route('borrower.apply.step.show', [$application, $step-1]) }}" class="btn btn-o"><i class="bi bi-chevron-left"></i> Back</a>
      @else
      <div></div>
      @endif
      @if($step < 9)
      <button type="submit" class="btn btn-p">Save & Continue <i class="bi bi-chevron-right"></i></button>
      @else
      <button type="submit" formaction="{{ route('borrower.apply.submit', $application) }}" class="btn btn-ok"><i class="bi bi-send-fill"></i> Submit Application</button>
      @endif
    </div>
  </form>
</div>

@push('scripts')
<script>
// Step 6: Affordability calculator
function calcDisposable() {
  const gross=parseFloat(document.querySelector('[name=monthly_earnings]')?.value||0)||0;
  const deductions=['tax_deduction','existing_loans_deduction','other_deductions'].reduce((s,n)=>s+(parseFloat(document.querySelector('[name='+n+']')?.value||0)||0),0);
  const expenses=['rent','groceries','transport','utilities','education','communication','medical','other_loan_repayments','other_expenses'].reduce((s,n)=>s+(parseFloat(document.querySelector('[name='+n+']')?.value||0)||0),0);
  const net=gross-deductions; const disp=net-expenses;
  if(document.getElementById('netSalDisplay')) document.getElementById('netSalDisplay').textContent='M'+net.toFixed(2);
  if(document.getElementById('dispSalDisplay')) document.getElementById('dispSalDisplay').textContent='M'+disp.toFixed(2);
  const msg=document.getElementById('affordMsg');
  if(msg) msg.textContent = disp>=0 ? '✓ Good — you have surplus income' : '⚠ Expenses exceed income';
}
calcDisposable();

// Step 7: Product & preview
function loadProductTerms(id) {
  const sel=document.getElementById('prodSelect');
  const opt=sel.options[sel.selectedIndex];
  const min=opt.dataset.min,max=opt.dataset.max,mint=opt.dataset.minterm,maxt=opt.dataset.maxterm;
  if(document.getElementById('amtHint')) document.getElementById('amtHint').textContent=min&&max?'Min: M'+Number(min).toLocaleString()+' · Max: M'+Number(max).toLocaleString():'';
  if(document.getElementById('termHint')) document.getElementById('termHint').textContent=mint&&maxt?mint+' – '+maxt+' months':'';
  calcPreview();
}
function calcPreview() {
  const sel=document.getElementById('prodSelect');
  if(!sel||!sel.value) return;
  const opt=sel.options[sel.selectedIndex];
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
  document.getElementById('previewBox').style.display='';
}
if(document.getElementById('prodSelect')?.value) loadProductTerms(document.getElementById('prodSelect').value);

// Step 2: GPS capture
function captureGPS() {
  const status = document.getElementById('gps-status');
  if (!navigator.geolocation) { status.textContent = 'GPS not supported on this device.'; return; }
  status.textContent = 'Capturing your location...';
  navigator.geolocation.getCurrentPosition(
    pos => {
      document.getElementById('gps-lat').value = pos.coords.latitude.toFixed(6);
      document.getElementById('gps-lng').value = pos.coords.longitude.toFixed(6);
      status.innerHTML = '<span style="color:#1a5c2e;font-weight:600">✓ Location captured: ' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4) + '</span>';
    },
    err => {
      status.textContent = 'Could not capture location. Please enter address manually.';
    },
    { enableHighAccuracy: true, timeout: 10000 }
  );
}
</script>
@endpush
@endsection