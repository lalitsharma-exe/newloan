@extends('agent.layouts.app')
@section('page-title', 'New Client Application')

@push('styles')
<style>
.step-container {
  display: flex;
  justify-content: space-between;
  margin-bottom: 24px;
  background: #fff;
  padding: 16px;
  border-radius: 12px;
  border: 1px solid var(--border);
  overflow-x: auto;
  scrollbar-width: none;
}
.step-tab {
  flex: 1;
  text-align: center;
  position: relative;
  min-width: 80px;
}
.step-tab .num {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--bg);
  color: var(--muted);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  border: 2px solid var(--border);
  transition: all 0.3s;
}
.step-tab.active .num {
  background: var(--p);
  color: #fff;
  border-color: var(--p);
}
.step-tab.done .num {
  background: var(--ok);
  color: #fff;
  border-color: var(--ok);
}
.step-tab .lbl {
  font-size: 10px;
  color: var(--muted);
  font-weight: 600;
  text-transform: uppercase;
  margin-top: 4px;
  display: block;
}
.step-tab.active .lbl {
  color: var(--p);
}
.step-panel {
  display: none;
}
.step-panel.active {
  display: block;
}
.summary-box {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 18px;
}
.summary-box .title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--muted);
  margin-bottom: 8px;
  border-bottom: 1px solid var(--border);
  padding-bottom: 4px;
}
.req {
  color: var(--err);
}
.gps-btn {
  background: var(--bg);
  border: 1.5px solid var(--border);
  padding: 9px 16px;
  border-radius: 9px;
  cursor: pointer;
  font-size: 13.5px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}
.gps-btn:hover {
  background: var(--border);
  color: var(--p);
}
</style>
@endpush

@section('content')
<div style="margin-bottom:16px">
  <a href="{{ route('agent.dashboard') }}" style="font-size:13px;color:var(--p);text-decoration:none"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

{{-- Stepper Progress --}}
<div class="step-container">
  @php
    $steps = [
      1 => 'Personal',
      2 => 'Address',
      3 => 'Employment',
      4 => 'Bank',
      5 => 'Next of Kin',
      6 => 'Affordability',
      7 => 'Loan Request',
      8 => 'Documents',
      9 => 'Signature'
    ];
  @endphp
  @foreach($steps as $num => $lbl)
    <div class="step-tab @if($num === 1) active @endif" data-step="{{ $num }}">
      <div class="num">{{ $num }}</div>
      <span class="lbl">{{ $lbl }}</span>
    </div>
  @endforeach
</div>

<form method="POST" action="{{ route('agent.applications.store') }}" enctype="multipart/form-data" id="appForm" novalidate>
  @csrf

  {{-- STEP 1: Personal Info --}}
  <div class="card step-panel active" data-panel="1">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-person-fill" style="color:var(--p)"></i> Personal Information</div></div>
    <div class="card-body">
      <div class="g2">
        <div class="fg">
          <label class="fl">Title <span class="req">*</span></label>
          <select name="title" class="fc" required>
            <option value="">— Select Title —</option>
            @foreach(['Mr','Mrs','Ms','Dr','Prof'] as $t)
              <option value="{{ $t }}" {{ old('title') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">First Name <span class="req">*</span></label>
          <input type="text" name="first_name" class="fc" value="{{ old('first_name') }}" required maxlength="60">
        </div>
        <div class="fg">
          <label class="fl">Surname <span class="req">*</span></label>
          <input type="text" name="surname" class="fc" value="{{ old('surname') }}" required maxlength="60">
        </div>
        <div class="fg">
          <label class="fl">Maiden Name <span style="font-size:11px;color:var(--muted);font-weight:400">(if any)</span></label>
          <input type="text" name="maiden_name" class="fc" value="{{ old('maiden_name') }}" placeholder="Name before marriage">
        </div>
        <div class="fg">
          <label class="fl">National ID <span class="req">*</span></label>
          <input type="text" name="national_id" class="fc" value="{{ old('national_id') }}" required maxlength="13" placeholder="13 digits" pattern="\d{13}">
        </div>
        <div class="fg">
          <label class="fl">Date of Birth <span class="req">*</span></label>
          <input type="date" name="date_of_birth" class="fc" value="{{ old('date_of_birth') }}" required>
        </div>
        <div class="fg">
          <label class="fl">Gender <span class="req">*</span></label>
          <select name="gender" class="fc" required>
            <option value="">— Select Gender —</option>
            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
          </select>
        </div>
        <div class="fg">
          <label class="fl">Marital Status <span class="req">*</span></label>
          <select name="marital_status" class="fc" required>
            <option value="">— Select Status —</option>
            @foreach(['single'=>'Single','married'=>'Married','divorced'=>'Divorced','widowed'=>'Widowed'] as $v=>$l)
              <option value="{{ $v }}" {{ old('marital_status') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Cell Number <span class="req">*</span></label>
          <input type="tel" name="cell_number" class="fc" value="{{ old('cell_number') }}" required placeholder="+266 5X XXX XXXX">
        </div>
        <div class="fg">
          <label class="fl">Email <span style="color:var(--muted);font-weight:400;text-transform:none;font-size:11px">(optional)</span></label>
          <input type="email" name="email" class="fc" value="{{ old('email') }}">
        </div>
      </div>
    </div>
  </div>

  {{-- STEP 2: Address Details --}}
  <div class="card step-panel" data-panel="2">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-geo-alt-fill" style="color:var(--p)"></i> Residential Address Details</div></div>
    <div class="card-body">
      <div class="g2">
        <div class="fg" style="grid-column:span 2">
          <label class="fl">Residential Address <span class="req">*</span></label>
          <input type="text" name="residential_address" class="fc" value="{{ old('residential_address') }}" placeholder="e.g. Ha Thamae, Block 5, House 23" required>
        </div>
        <div class="fg">
          <label class="fl">Village / Area <span class="req">*</span></label>
          <input type="text" name="village" class="fc" value="{{ old('village') }}" required>
        </div>
        <div class="fg">
          <label class="fl">Town / City <span class="req">*</span></label>
          <input type="text" name="town" class="fc" value="{{ old('town') }}" required>
        </div>
        <div class="fg">
          <label class="fl">District <span class="req">*</span></label>
          <select name="district" class="fc" required>
            <option value="">— Select District —</option>
            @foreach(['Maseru','Berea','Leribe','Butha-Buthe','Mafeteng',"Mohale's Hoek","Qacha's Nek",'Quthing','Thaba-Tseka','Mokhotlong'] as $d)
              <option value="{{ $d }}" {{ old('district') === $d ? 'selected' : '' }}>{{ $d }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Duration at Address <span class="req">*</span></label>
          <select name="address_duration" class="fc" required>
            <option value="">— Duration —</option>
            @foreach(['less_than_6_months'=>'Less than 6 months','6_to_12_months'=>'6–12 months','1_to_3_years'=>'1–3 years','3_to_5_years'=>'3–5 years','more_than_5_years'=>'More than 5 years'] as $v=>$l)
              <option value="{{ $v }}" {{ old('address_duration') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Residence Type <span class="req">*</span></label>
          <select name="residence_type" class="fc" required>
            <option value="">— Type —</option>
            <option value="own" {{ old('residence_type') === 'own' ? 'selected' : '' }}>Own</option>
            <option value="rent" {{ old('residence_type') === 'rent' ? 'selected' : '' }}>Rent</option>
            <option value="family" {{ old('residence_type') === 'family' ? 'selected' : '' }}>Family</option>
            <option value="employer" {{ old('residence_type') === 'employer' ? 'selected' : '' }}>Employer Provided</option>
          </select>
        </div>
        <div class="fg" style="grid-column:span 2">
          <label class="fl">Nearest Landmark <span class="req">*</span></label>
          <input type="text" name="nearest_landmark" class="fc" value="{{ old('nearest_landmark') }}" placeholder="e.g. Near Maseru West Primary School" required>
        </div>
        <div class="fg" style="grid-column:span 2">
          <label class="fl">Directions to Home <span class="req">*</span></label>
          <textarea name="home_directions" class="fc" rows="3" placeholder="e.g. From Shell garage, turn left, third house on right, green gate." required>{{ old('home_directions') }}</textarea>
        </div>
      </div>
      
      <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px;margin-top:10px">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
          <div>
            <div style="font-size:13.5px;font-weight:700;color:var(--p);margin-bottom:2px"><i class="bi bi-geo-alt-fill"></i> Capture Client GPS Coordinates</div>
            <div style="font-size:11.5px;color:var(--muted)" id="gps-status">Capture coordinates from client's physical location.</div>
          </div>
          <button type="button" class="gps-btn" onclick="captureGPS()"><i class="bi bi-crosshair"></i> Capture Coordinates</button>
        </div>
        <input type="hidden" name="gps_latitude" id="gps-lat" value="{{ old('gps_latitude') }}">
        <input type="hidden" name="gps_longitude" id="gps-lng" value="{{ old('gps_longitude') }}">
      </div>
    </div>
  </div>

  {{-- STEP 3: Employment Details --}}
  <div class="card step-panel" data-panel="3">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-briefcase-fill" style="color:var(--p)"></i> Employment Details</div></div>
    <div class="card-body">
      <div class="g2">
        <div class="fg">
          <label class="fl">Employer Name <span class="req">*</span></label>
          <input type="text" name="employer_name" class="fc" value="{{ old('employer_name') }}" required>
        </div>
        <div class="fg">
          <label class="fl">Employer Type <span class="req">*</span></label>
          <select name="employer_type" id="employer_type" class="fc" required>
            <option value="">— Select Type —</option>
            @foreach(['government'=>'Government','private'=>'Private Sector','sme'=>'SMEs'] as $v=>$l)
              <option value="{{ $v }}" {{ old('employer_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div id="category_wrapper" class="fg" style="display:none">
          <label class="fl">Work Sector / Category <span class="req">*</span></label>
          <select name="employer_category" id="employer_category" class="fc">
            <option value="">— Select Category —</option>
            @foreach(['Defence','NSS','Police','LCS','Pensioner','Civil servants','Teacher','Private sector','SMEs'] as $cat)
              <option value="{{ $cat }}" {{ old('employer_category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Job Title <span class="req">*</span></label>
          <input type="text" name="job_title" class="fc" value="{{ old('job_title') }}" required>
        </div>
        <div class="fg">
          <label class="fl">Department</label>
          <input type="text" name="department" class="fc" value="{{ old('department') }}">
        </div>
        <div class="fg">
          <label class="fl">Employment / Payroll Number <span class="req">*</span></label>
          <input type="text" name="employment_number" class="fc" value="{{ old('employment_number') }}" required>
        </div>
        <div class="fg">
          <label class="fl">HR Contact Number <span class="req">*</span></label>
          <input type="tel" name="contact_number" class="fc" value="{{ old('contact_number') }}" required>
        </div>
        <div class="fg">
          <label class="fl">Employment Expiry Date <span style="font-size:11px;color:var(--muted)">(if contract)</span></label>
          <input type="date" name="employment_expiry_date" class="fc" value="{{ old('employment_expiry_date') }}">
        </div>
      </div>
    </div>
  </div>

  {{-- STEP 4: Bank Details --}}
  <div class="card step-panel" data-panel="4">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-bank" style="color:var(--p)"></i> Client Bank Account Details</div></div>
    <div class="card-body">
      <div class="g2">
        <div class="fg">
          <label class="fl">Bank Name <span class="req">*</span></label>
          <select name="bank_name" id="bank_name" class="fc" required onchange="loadBranches(this.value)">
            <option value="">— Select Bank —</option>
          </select>
        </div>
        <div class="fg">
          <label class="fl">Branch Name <span class="req">*</span></label>
          <select name="branch_name" id="branch_name" class="fc" required onchange="updateBranchCode(this.options[this.selectedIndex])">
            <option value="">— Select Branch —</option>
          </select>
        </div>
        <div class="fg">
          <label class="fl">Branch Code</label>
          <input type="text" name="branch_code" id="branch_code" class="fc" value="{{ old('branch_code') }}" placeholder="Auto-filled" readonly>
        </div>
        <div class="fg">
          <label class="fl">Account Holder Name <span class="req">*</span></label>
          <input type="text" name="account_holder_name" class="fc" value="{{ old('account_holder_name') }}" required>
        </div>
        <div class="fg">
          <label class="fl">Account Number <span class="req">*</span></label>
          <input type="text" name="account_number" class="fc" value="{{ old('account_number') }}" required>
        </div>
        <div class="fg">
          <label class="fl">Account Type <span class="req">*</span></label>
          <select name="account_type" class="fc" required>
            <option value="">— Select Type —</option>
            <option value="savings" {{ old('account_type') === 'savings' ? 'selected' : '' }}>Savings</option>
            <option value="cheque" {{ old('account_type') === 'cheque' ? 'selected' : '' }}>Cheque / Current</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  {{-- STEP 5: Next of Kin --}}
  <div class="card step-panel" data-panel="5">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-people-fill" style="color:var(--p)"></i> Next of Kin (Emergency Contact)</div></div>
    <div class="card-body">
      <div class="g2">
        <div class="fg">
          <label class="fl">First Name <span class="req">*</span></label>
          <input type="text" name="nok_1_first_name" class="fc" value="{{ old('nok_1_first_name') }}" required>
        </div>
        <div class="fg">
          <label class="fl">Last Name <span class="req">*</span></label>
          <input type="text" name="nok_1_last_name" class="fc" value="{{ old('nok_1_last_name') }}" required>
        </div>
        <div class="fg">
          <label class="fl">Relationship <span class="req">*</span></label>
          <select name="nok_1_relationship" class="fc" required>
            <option value="">— Select Relationship —</option>
            @foreach(['Spouse','Parent','Sibling','Child','Friend','Other'] as $r)
              <option value="{{ $r }}" {{ old('nok_1_relationship') === $r ? 'selected' : '' }}>{{ $r }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Phone Number <span class="req">*</span></label>
          <input type="tel" name="nok_1_phone" class="fc" value="{{ old('nok_1_phone') }}" required>
        </div>
      </div>
    </div>
  </div>

  {{-- STEP 6: Affordability Assessment --}}
  <div class="card step-panel" data-panel="6">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-calculator" style="color:var(--p)"></i> Affordability Assessment</div></div>
    <div class="card-body">
      <div class="g2">
        <div class="fg">
          <label class="fl">Monthly Basic Salary (M) <span class="req">*</span></label>
          <input type="number" name="monthly_earnings" id="monthly_earnings" class="fc" step="0.01" min="0" value="{{ old('monthly_earnings') }}" required oninput="calcAff()">
          <div class="ft">Basic salary before deductions</div>
        </div>
        <div>
          <div style="font-size:11.5px;font-weight:700;text-transform:uppercase;color:var(--muted);margin-bottom:10px">Monthly Pay Slip Deductions</div>
          <div class="fg" style="margin-bottom:8px">
            <label class="fl" style="font-size:11px">PAYE / Income Tax (M)</label>
            <input type="number" name="tax_deduction" class="fc" step="0.01" min="0" value="{{ old('tax_deduction',0) }}" oninput="calcAff()" style="padding:6px 10px">
          </div>
          <div class="fg" style="margin-bottom:8px">
            <label class="fl" style="font-size:11px">Existing Loan Deductions (M)</label>
            <input type="number" name="existing_loans_deduction" class="fc" step="0.01" min="0" value="{{ old('existing_loans_deduction',0) }}" oninput="calcAff()" style="padding:6px 10px">
          </div>
          <div class="fg" style="margin-bottom:8px">
            <label class="fl" style="font-size:11px">Pension Contribution (M)</label>
            <input type="number" name="pension_deduction" class="fc" step="0.01" min="0" value="{{ old('pension_deduction',0) }}" oninput="calcAff()" style="padding:6px 10px">
          </div>
          <div class="fg" style="margin-bottom:8px">
            <label class="fl" style="font-size:11px">Insurance Deductions (M)</label>
            <input type="number" name="insurance_deduction" class="fc" step="0.01" min="0" value="{{ old('insurance_deduction',0) }}" oninput="calcAff()" style="padding:6px 10px">
          </div>
          <div class="fg" style="margin-bottom:8px">
            <label class="fl" style="font-size:11px">Subscriptions / Other Slip Deductions (M)</label>
            <input type="number" name="subscriptions_deduction" class="fc" step="0.01" min="0" value="{{ old('subscriptions_deduction',0) }}" oninput="calcAff()" style="padding:6px 10px">
          </div>
          <div class="fg" style="margin-bottom:0">
            <label class="fl" style="font-size:11px">Other Salary Slip Deductions (M)</label>
            <input type="number" name="other_deductions" class="fc" step="0.01" min="0" value="{{ old('other_deductions',0) }}" oninput="calcAff()" style="padding:6px 10px">
          </div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin:20px 0">
        <div style="background:#f0f4ff;border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Basic Salary</div>
          <div style="font-size:18px;font-weight:800;color:var(--dark)" id="grossDisplay">M0.00</div>
        </div>
        <div style="background:#fff7f0;border:1px solid #fde8d0;border-radius:10px;padding:12px;text-align:center">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Total Deductions</div>
          <div style="font-size:18px;font-weight:800;color:#ea580c" id="dedDisplay">M0.00</div>
        </div>
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;text-align:center">
          <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase">Net Salary</div>
          <div style="font-size:18px;font-weight:800;color:#16a34a" id="netSalDisplay">M0.00</div>
        </div>
      </div>

      <div style="font-size:11.5px;font-weight:700;text-transform:uppercase;color:var(--muted);margin-bottom:12px">Monthly Living Expenses</div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
        @foreach([['rent','Rent'],['groceries','Groceries'],['transport','Transport'],['utilities','Utilities'],['education','Education'],['communication','Airtime/Data'],['medical','Medical'],['other_loan_repayments','Other Bank Loans'],['other_expenses','Other Expenses']] as [$fname,$flabel])
          <div class="fg" style="margin-bottom:4px">
            <label class="fl" style="font-size:11px">{{ $flabel }} (M)</label>
            <input type="number" name="{{ $fname }}" class="fc" step="0.01" min="0" value="{{ old($fname,0) }}" oninput="calcAff()" style="padding:6px 10px;font-size:13px">
          </div>
        @endforeach
      </div>

      <div style="background:linear-gradient(135deg,#0f4e4a,#0d5b55);border-radius:12px;padding:18px;text-align:center;color:#fff">
        <div style="font-size:10px;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px">Disposable Income</div>
        <div style="font-size:26px;font-weight:800;color:#fff" id="dispSalDisplay">M0.00</div>
        <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:4px" id="affordMsg"></div>
      </div>
    </div>
  </div>

  {{-- STEP 7: Loan Request Details --}}
  <div class="card step-panel" data-panel="7">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-cash-coin" style="color:var(--p)"></i> Loan Product & Details</div></div>
    <div class="card-body">
      <div class="g2">
        <div class="fg" style="grid-column:span 2">
          <label class="fl">Loan Product <span class="req">*</span></label>
          <select name="loan_product_id" class="fc" required id="prodSelect" onchange="loadProductTerms(this.value)">
            <option value="">— Select a Product —</option>
            @foreach($products as $p)
              <option value="{{ $p->id }}" {{ old('loan_product_id') == $p->id ? 'selected' : '' }}
                data-rate="{{ $p->interest_rate }}" data-init="{{ $p->initiation_fee_rate }}"
                data-admin="{{ $p->admin_fee_fixed }}" data-min="{{ $p->min_amount }}"
                data-max="{{ $p->max_amount }}" data-minterm="{{ $p->min_term_months }}" data-maxterm="{{ $p->max_term_months }}">
                {{ $p->name }} — {{ $p->interest_rate }}%/mo · M{{ number_format($p->min_amount,0) }}–M{{ number_format($p->max_amount,0) }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Loan Amount (M) <span class="req">*</span></label>
          <input type="number" name="requested_amount" class="fc" id="amtInput" step="100" min="100" value="{{ old('requested_amount') }}" required oninput="calcPreview()">
          <div class="ft" id="amtHint"></div>
        </div>
        <div class="fg">
          <label class="fl">Term (months) <span class="req">*</span></label>
          <input type="number" name="requested_term" class="fc" id="termInput" min="1" max="24" value="{{ old('requested_term',3) }}" required oninput="calcPreview()">
          <div class="ft" id="termHint"></div>
        </div>
        <div class="fg">
          <label class="fl">Loan Purpose <span class="req">*</span></label>
          <select name="loan_purpose" class="fc" required>
            <option value="">— Select Purpose —</option>
            @foreach(['Home Improvement','Education','Medical / Health','Business Investment','Vehicle Purchase','Debt Consolidation','School Fees','Wedding / Family Event','Funeral Costs','Groceries / Food','Clothing','Travel','Other'] as $p)
              <option value="{{ $p }}" {{ old('loan_purpose') === $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg">
          <label class="fl">Payout Method <span class="req">*</span></label>
          <select name="payout_method" class="fc" required>
            <option value="bank_transfer" {{ old('payout_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
            <option value="mobile_money" {{ old('payout_method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
          </select>
        </div>
        <div class="fg">
          <label class="fl">Collection Method <span class="req">*</span></label>
          <select name="collection_method" class="fc" required>
            <option value="">— Select Method —</option>
            <option value="salary_deduction" {{ old('collection_method') === 'salary_deduction' ? 'selected' : '' }}>Salary deduction</option>
            <option value="card_payment" {{ old('collection_method') === 'card_payment' ? 'selected' : '' }}>Card payment</option>
            <option value="debit_order" {{ old('collection_method') === 'debit_order' ? 'selected' : '' }}>Debit Order</option>
          </select>
        </div>
        <div class="fg">
          <label class="fl">Expected Payday (1-31) <span class="req">*</span></label>
          <input type="number" name="salary_payday" class="fc" min="1" max="31" value="{{ old('salary_payday', 25) }}" required>
          <div class="ft">Determines monthly instalment due date.</div>
        </div>
      </div>

      <div id="previewBox" style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:16px;display:none;margin-top:10px">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--muted);margin-bottom:10px">Repayment Schedule Preview</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;text-align:center;margin-bottom:10px">
          <div><div style="font-size:18px;font-weight:800;color:var(--p)" id="prev-monthly">—</div><div style="font-size:11px;color:var(--muted)">Monthly Instalment</div></div>
          <div><div style="font-size:18px;font-weight:800" id="prev-total">—</div><div style="font-size:11px;color:var(--muted)">Total Repayable</div></div>
          <div><div style="font-size:18px;font-weight:800" id="prev-init">—</div><div style="font-size:11px;color:var(--muted)">Initiation Fee</div></div>
        </div>
        <div id="repaymentLimitMsg" style="margin-top:10px;font-size:12.5px;font-weight:600;text-align:center"></div>
      </div>
    </div>
  </div>

  {{-- STEP 8: Document Uploads --}}
  <div class="card step-panel" data-panel="8">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-camera-fill" style="color:var(--p)"></i> Required Document Uploads</div></div>
    <div class="card-body">
      <div style="font-size:12.5px;color:var(--muted);margin-bottom:18px">Please upload high-resolution images or files for verification (max 10MB each, PDF/JPG/PNG).</div>
      <div class="g2">
        <div class="fg">
          <label class="fl">National ID Photo <span class="req">*</span></label>
          <input type="file" name="national_id_photo" class="fc" accept=".pdf,.jpg,.jpeg,.png" required>
          <div class="ft">Copy of front and back side of ID.</div>
        </div>
        <div class="fg">
          <label class="fl">Latest Payslip <span class="req">*</span></label>
          <input type="file" name="payslip_photo" class="fc" accept=".pdf,.jpg,.jpeg,.png" required>
          <div class="ft">Not older than 3 months.</div>
        </div>
        <div class="fg">
          <label class="fl">3 Months Bank Statement <span class="req">*</span></label>
          <input type="file" name="bank_statement_photo" class="fc" accept=".pdf,.jpg,.jpeg,.png" required>
          <div class="ft">Recent stamped statements.</div>
        </div>
        <div class="fg">
          <label class="fl">Selfie Holding ID Card <span class="req">*</span></label>
          <input type="file" name="selfie_photo" class="fc" accept=".pdf,.jpg,.jpeg,.png" required>
          <div class="ft">Face must be clearly visible alongside ID.</div>
        </div>
      </div>
    </div>
  </div>

  {{-- STEP 9: Review & Signature Pad --}}
  <div class="card step-panel" data-panel="9">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-file-earmark-check-fill" style="color:var(--p)"></i> Review & Sign Application</div></div>
    <div class="card-body">
      <div class="alert a-ok" style="margin-bottom:20px"><i class="bi bi-info-circle-fill"></i> Please review the application details with the client and have them sign in the signature pad below.</div>

      <div class="g2" style="gap:14px">
        <div class="summary-box">
          <div class="title">Personal Information</div>
          <div id="rev-personal" style="font-size:12.5px;line-height:1.6"></div>
        </div>
        <div class="summary-box">
          <div class="title">Residence Details</div>
          <div id="rev-address" style="font-size:12.5px;line-height:1.6"></div>
        </div>
        <div class="summary-box">
          <div class="title">Employment & Income</div>
          <div id="rev-employment" style="font-size:12.5px;line-height:1.6"></div>
        </div>
        <div class="summary-box">
          <div class="title">Requested Loan Terms</div>
          <div id="rev-loan" style="font-size:12.5px;line-height:1.6"></div>
        </div>
      </div>

      <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--muted);margin-bottom:8px;margin-top:16px">Client Digital Signature <span class="req">*</span></div>
      <div style="background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:12px;text-align:center">
        <canvas id="signature-pad" style="width:100%;max-width:460px;height:160px;border:1px dashed #cbd5e1;border-radius:8px;background:#f8fafc;touch-action:none"></canvas>
        <div style="margin-top:8px;display:flex;justify-content:space-between;align-items:center;max-width:460px;margin:8px auto 0">
          <span style="font-size:11px;color:var(--muted)">Ask the client to sign inside the box using a finger or mouse</span>
          <button type="button" class="btn btn-o btn-sm" onclick="sigPad.clear()" style="padding:4px 10px;font-size:11px">Clear Pad</button>
        </div>
      </div>
      <input type="hidden" name="signature_data" id="signature_data">

      <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:10px;padding:12px 16px;font-size:13px;color:#92400e;margin-top:20px">
        <i class="bi bi-shield-lock-fill"></i> By submitting, the agent confirms they have physically verified the client's original ID document and payslip. The client consents to credit check checks and accepts all repayment conditions.
      </div>
    </div>
  </div>

  {{-- Navigation Footer --}}
  <div style="margin-top:20px;display:flex;justify-content:space-between;gap:12px">
    <button type="button" class="btn btn-o" id="btnBack" style="display:none" onclick="prevStep()"><i class="bi bi-chevron-left"></i> Back</button>
    <div class="spacer"></div>
    <button type="button" class="btn btn-p" id="btnNext" onclick="nextStep()">Continue <i class="bi bi-chevron-right"></i></button>
    <button type="submit" class="btn btn-ok" id="btnSubmit" style="display:none"><i class="bi bi-send-fill"></i> Submit Client Application</button>
  </div>
</form>

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 9;
let sigPad;

function showStep(n) {
  document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.step-tab').forEach(t => {
    t.classList.remove('active');
    if(parseInt(t.dataset.step) < n) t.classList.add('done');
    else t.classList.remove('done');
  });
  
  document.querySelector('[data-panel="'+n+'"]').classList.add('active');
  document.querySelector('[data-step="'+n+'"]').classList.add('active');
  
  document.getElementById('btnBack').style.display = n > 1 ? '' : 'none';
  document.getElementById('btnNext').style.display = n < totalSteps ? '' : 'none';
  document.getElementById('btnSubmit').style.display = n === totalSteps ? '' : 'none';
  
  if(n === 9) buildReview();
}

function nextStep() {
  if (validateStep(currentStep)) {
    if(currentStep < totalSteps) { currentStep++; showStep(currentStep); }
  }
}
function prevStep() {
  if(currentStep > 1) { currentStep--; showStep(currentStep); }
}

function validateStep(step) {
  document.querySelectorAll('.fc').forEach(el => el.classList.remove('err'));
  let valid = true;
  let missing = [];

  if (step === 1) {
    const req = ['title', 'first_name', 'surname', 'national_id', 'date_of_birth', 'gender', 'marital_status', 'cell_number'];
    req.forEach(name => {
      const input = document.querySelector('[name="' + name + '"]');
      if (!input || !input.value.trim()) {
        if(input) input.classList.add('err');
        valid = false;
      }
    });
    if (!valid) {
      alert('Please fill in all required personal information fields.');
      return false;
    }
    const nid = document.querySelector('[name="national_id"]');
    if (!/^\d{13}$/.test(nid.value.trim())) {
      nid.classList.add('err');
      alert('Lesotho National ID must be exactly 13 numeric digits.');
      return false;
    }
  }

  if (step === 2) {
    const req = ['residential_address', 'village', 'town', 'district', 'address_duration', 'residence_type', 'nearest_landmark', 'home_directions'];
    req.forEach(name => {
      const input = document.querySelector('[name="' + name + '"]');
      if (!input || !input.value.trim()) {
        if(input) input.classList.add('err');
        valid = false;
      }
    });
    if (!valid) {
      alert('Please fill in all residential address details.');
      return false;
    }
  }

  if (step === 3) {
    const req = ['employer_name', 'employer_type', 'job_title', 'employment_number', 'contact_number'];
    req.forEach(name => {
      const input = document.querySelector('[name="' + name + '"]');
      if (!input || !input.value.trim()) {
        if(input) input.classList.add('err');
        valid = false;
      }
    });
    const type = document.getElementById('employer_type').value;
    if (['government', 'sme', 'private'].includes(type)) {
      const cat = document.getElementById('employer_category');
      if (!cat.value.trim()) {
        cat.classList.add('err');
        valid = false;
      }
    }
    if (!valid) {
      alert('Please fill in all employment details.');
      return false;
    }
  }

  if (step === 4) {
    const req = ['bank_name', 'branch_name', 'account_holder_name', 'account_number', 'account_type'];
    req.forEach(name => {
      const input = document.querySelector('[name="' + name + '"]');
      if (!input || !input.value.trim()) {
        if(input) input.classList.add('err');
        valid = false;
      }
    });
    if (!valid) {
      alert('Please fill in all client bank details.');
      return false;
    }
  }

  if (step === 5) {
    const req = ['nok_1_first_name', 'nok_1_last_name', 'nok_1_relationship', 'nok_1_phone'];
    req.forEach(name => {
      const input = document.querySelector('[name="' + name + '"]');
      if (!input || !input.value.trim()) {
        if(input) input.classList.add('err');
        valid = false;
      }
    });
    if (!valid) {
      alert('Please fill in all next of kin details.');
      return false;
    }
  }

  if (step === 6) {
    const earn = document.getElementById('monthly_earnings');
    if (!earn.value || parseFloat(earn.value) <= 0) {
      earn.classList.add('err');
      alert('Please enter a valid monthly earnings basic salary.');
      return false;
    }
    const gross = parseFloat(earn.value);
    const ded = ['tax_deduction','existing_loans_deduction','pension_deduction','insurance_deduction','subscriptions_deduction','other_deductions'].reduce((s,n)=>s+(parseFloat(document.querySelector('[name='+n+']')?.value||0)||0),0);
    const expenses = ['rent','groceries','transport','utilities','education','communication','medical','other_loan_repayments','other_expenses'].reduce((s,n)=>s+(parseFloat(document.querySelector('[name='+n+']')?.value||0)||0),0);
    const net = gross - ded;
    if (expenses > (net * 0.70)) {
      alert('Monthly living expenses cannot exceed 70% of net salary.');
      return false;
    }
  }

  if (step === 7) {
    const req = ['loan_product_id', 'requested_amount', 'requested_term', 'loan_purpose', 'payout_method', 'collection_method', 'salary_payday'];
    req.forEach(name => {
      const input = document.querySelector('[name="' + name + '"]');
      if (!input || !input.value.trim()) {
        if(input) input.classList.add('err');
        valid = false;
      }
    });
    if (!valid) {
      alert('Please complete all requested loan product parameters.');
      return false;
    }
    // Final check for loan scheduling calculations
    const p = parseFloat(document.getElementById('amtInput').value) || 0;
    const t = parseInt(document.getElementById('termInput').value) || 0;
    const gross = parseFloat(document.getElementById('monthly_earnings').value) || 0;
    const ded = ['tax_deduction','existing_loans_deduction','pension_deduction','insurance_deduction','subscriptions_deduction','other_deductions'].reduce((s,n)=>s+(parseFloat(document.querySelector('[name='+n+']')?.value||0)||0),0);
    const net = gross - ded;

    const sel = document.getElementById('prodSelect');
    const opt = sel.options[sel.selectedIndex];
    const rate = parseFloat(opt.dataset.rate)/100;
    const initR = parseFloat(opt.dataset.init)/100;
    const admin = parseFloat(opt.dataset.admin);

    const totalInt = p * rate * t;
    const initFee = p * initR;
    const total = p + totalInt + initFee + (admin * t);
    const monthly = total / t;
    const maxRepayment = net * 0.30;

    if (monthly > maxRepayment) {
      alert('Instalment exceeds the 30% Net Salary Cap. Please decrease requested amount or increase requested term.');
      return false;
    }
    if (total > (2 * p)) {
      alert('Total repayment exceeds the 2× capital cap. Please select another product or term.');
      return false;
    }
  }

  if (step === 8) {
    const req = ['national_id_photo', 'payslip_photo', 'bank_statement_photo', 'selfie_photo'];
    req.forEach(name => {
      const input = document.querySelector('[name="' + name + '"]');
      if (!input || !input.files || !input.files.length) {
        if(input) input.classList.add('err');
        valid = false;
      }
    });
    if (!valid) {
      alert('Please upload all 4 required client verification documents.');
      return false;
    }
  }

  return true;
}

function buildReview() {
  const f = document.getElementById('appForm');
  const fd = new FormData(f);
  
  // Personal Info
  let p = `<strong>Client Name:</strong> ${fd.get('title')} ${fd.get('first_name')} ${fd.get('surname')}<br>`;
  p += `<strong>National ID:</strong> ${fd.get('national_id')}<br>`;
  p += `<strong>DOB:</strong> ${fd.get('date_of_birth')}<br>`;
  p += `<strong>Gender:</strong> ${fd.get('gender')} · <strong>Marital:</strong> ${fd.get('marital_status')}<br>`;
  p += `<strong>Mobile:</strong> ${fd.get('cell_number')} · <strong>Email:</strong> ${fd.get('email') || '—'}`;
  document.getElementById('rev-personal').innerHTML = p;

  // Address
  let a = `<strong>Address:</strong> ${fd.get('residential_address')}<br>`;
  a += `<strong>Village/Area:</strong> ${fd.get('village')} · <strong>Town:</strong> ${fd.get('town')}<br>`;
  a += `<strong>District:</strong> ${fd.get('district')}<br>`;
  a += `<strong>Type:</strong> ${fd.get('residence_type')} · <strong>Duration:</strong> ${fd.get('address_duration').replace(/_/g,' ')}`;
  document.getElementById('rev-address').innerHTML = a;

  // Employment & Income
  let e = `<strong>Employer:</strong> ${fd.get('employer_name')} (${fd.get('employer_type')})<br>`;
  e += `<strong>Job Title:</strong> ${fd.get('job_title')}<br>`;
  e += `<strong>Employment Number:</strong> ${fd.get('employment_number')}<br>`;
  e += `<strong>Basic Salary:</strong> M${Number(fd.get('monthly_earnings')).toFixed(2)}`;
  document.getElementById('rev-employment').innerHTML = e;

  // Requested Loan
  const sel = document.getElementById('prodSelect');
  const prodName = sel.options[sel.selectedIndex].text.split('—')[0].trim();
  let l = `<strong>Product:</strong> ${prodName}<br>`;
  l += `<strong>Amount Requested:</strong> M${Number(fd.get('requested_amount')).toFixed(2)}<br>`;
  l += `<strong>Term Requested:</strong> ${fd.get('requested_term')} Months<br>`;
  l += `<strong>Payout Method:</strong> ${fd.get('payout_method').replace(/_/g,' ')}<br>`;
  l += `<strong>Expected Payday:</strong> Day ${fd.get('salary_payday')}`;
  document.getElementById('rev-loan').innerHTML = l;
}

// Recalculate Affordability
function calcAff() {
  const grossInput = document.getElementById('monthly_earnings');
  if (!grossInput) return;
  const gross = parseFloat(grossInput.value) || 0;
  
  const ded = ['tax_deduction','existing_loans_deduction','pension_deduction','insurance_deduction','subscriptions_deduction','other_deductions'].reduce((s,n)=>s+(parseFloat(document.querySelector('[name='+n+']')?.value||0)||0),0);
  const expenses = ['rent','groceries','transport','utilities','education','communication','medical','other_loan_repayments','other_expenses'].reduce((s,n)=>s+(parseFloat(document.querySelector('[name='+n+']')?.value||0)||0),0);
  const net = gross - ded;
  const disp = net - expenses;
  
  document.getElementById('grossDisplay').textContent = 'M' + gross.toFixed(2);
  document.getElementById('dedDisplay').textContent = 'M' + ded.toFixed(2);
  document.getElementById('netSalDisplay').textContent = 'M' + net.toFixed(2);
  document.getElementById('dispSalDisplay').textContent = 'M' + disp.toFixed(2);
  
  const msg = document.getElementById('affordMsg');
  if (msg) {
    const maxExp = net * 0.70;
    if (expenses > maxExp) {
      msg.innerHTML = '<span style="color:#ef4444;font-weight:bold">⚠ Monthly living expenses exceed 70% of client\'s net salary.</span>';
    } else {
      msg.innerHTML = disp >= 0 ? '<span style="color:#2dd4bf;font-weight:bold">✓ Surplus income confirmed</span>' : '<span style="color:#ef4444;font-weight:bold">⚠ Deficit income registered</span>';
    }
  }
}

// Calculate Schedule Preview
function loadProductTerms(id) {
  const sel = document.getElementById('prodSelect');
  if(!sel || !sel.value) return;
  const opt = sel.options[sel.selectedIndex];
  const min = opt.dataset.min;
  const max = opt.dataset.max;
  const mint = opt.dataset.minterm;
  const maxt = opt.dataset.maxterm;
  
  document.getElementById('amtHint').textContent = `Min: M${Number(min).toLocaleString()} · Max: M${Number(max).toLocaleString()}`;
  document.getElementById('termHint').textContent = `Term limit: ${mint}–${maxt} months`;
  calcPreview();
}

function calcPreview() {
  const sel = document.getElementById('prodSelect');
  if(!sel || !sel.value) return;
  const opt = sel.options[sel.selectedIndex];
  const p = parseFloat(document.getElementById('amtInput').value) || 0;
  const t = parseInt(document.getElementById('termInput').value) || 0;
  
  const rate = parseFloat(opt.dataset.rate)/100;
  const initR = parseFloat(opt.dataset.init)/100;
  const admin = parseFloat(opt.dataset.admin);
  
  const box = document.getElementById('previewBox');
  if (!p || !t) { box.style.display = 'none'; return; }
  
  const totalInt = p * rate * t;
  const initFee = p * initR;
  const total = p + totalInt + initFee + (admin * t);
  const monthly = total / t;
  
  document.getElementById('prev-monthly').textContent = 'M' + monthly.toFixed(2);
  document.getElementById('prev-total').textContent = 'M' + total.toFixed(2);
  document.getElementById('prev-init').textContent = 'M' + initFee.toFixed(2);
  
  const gross = parseFloat(document.getElementById('monthly_earnings').value) || 0;
  const ded = ['tax_deduction','existing_loans_deduction','pension_deduction','insurance_deduction','subscriptions_deduction','other_deductions'].reduce((s,n)=>s+(parseFloat(document.querySelector('[name='+n+']')?.value||0)||0),0);
  const net = gross - ded;
  
  const limitMsg = document.getElementById('repaymentLimitMsg');
  const maxRepayment = net * 0.30;
  
  if (monthly > maxRepayment) {
    limitMsg.innerHTML = '<span style="color:var(--err)">🛑 Repayment of M' + monthly.toFixed(2) + ' exceeds the 30% net salary cap (Limit: M' + maxRepayment.toFixed(2) + ')</span>';
  } else if (total > (2 * p)) {
    limitMsg.innerHTML = '<span style="color:var(--err)">🛑 Repayment of M' + total.toFixed(2) + ' violates 2× capital cap (Limit: M' + (2*p).toFixed(2) + ')</span>';
  } else {
    limitMsg.innerHTML = '<span style="color:var(--ok)">✓ Repayment schedule sits safely within the caps.</span>';
  }
  
  box.style.display = '';
}

// GPS capture
function captureGPS() {
  const s = document.getElementById('gps-status');
  if(!navigator.geolocation) { s.textContent = 'GPS not supported on this browser.'; return; }
  s.textContent = 'Acquiring high-accuracy GPS satellite locks...';
  navigator.geolocation.getCurrentPosition(
    pos => {
      document.getElementById('gps-lat').value = pos.coords.latitude.toFixed(6);
      document.getElementById('gps-lng').value = pos.coords.longitude.toFixed(6);
      s.innerHTML = '<span style="color:var(--ok);font-weight:700">✓ Physical coordinates locked (' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4) + ')</span>';
    },
    () => { s.textContent = 'Could not acquire lock. Please ensure GPS is active.'; },
    { enableHighAccuracy: true, timeout: 12000 }
  );
}

// Banks & Branches Dynamic Loader
async function loadBanks() {
  const el = document.getElementById('bank_name');
  if (!el) return;
  try {
    const res = await fetch('/api/banks');
    const banks = await res.json();
    banks.forEach(b => {
      el.add(new Option(b.name, b.id));
    });
  } catch(e) {}
}

async function loadBranches(bankId) {
  const el = document.getElementById('branch_name');
  if (!el || !bankId) return;
  el.innerHTML = '<option value="">— Select Branch —</option>';
  try {
    const res = await fetch(`/api/banks/${bankId}/branches`);
    const branches = await res.json();
    branches.forEach(b => {
      const opt = new Option(b.name, b.name);
      opt.setAttribute('data-code', b.code);
      el.add(opt);
    });
  } catch(e) {}
}

function updateBranchCode(opt) {
  const codeEl = document.getElementById('branch_code');
  if (codeEl && opt && opt.getAttribute('data-code')) {
    codeEl.value = opt.getAttribute('data-code');
  }
}

document.addEventListener("DOMContentLoaded", () => {
  loadBanks();
  
  // Employer type sector conditional display
  const typeSelect = document.getElementById('employer_type');
  const catWrapper = document.getElementById('category_wrapper');
  const catSelect  = document.getElementById('employer_category');
  if (typeSelect && catWrapper) {
    typeSelect.addEventListener('change', function() {
      if (['government', 'sme', 'private'].includes(this.value)) {
        catWrapper.style.display = 'block';
        catSelect.setAttribute('required', 'required');
      } else {
        catWrapper.style.display = 'none';
        catSelect.removeAttribute('required');
        catSelect.value = '';
      }
    });
  }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
// Signature pad init
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
        
        document.getElementById('appForm').addEventListener('submit', function(e) {
            if (sigPad.isEmpty()) {
                e.preventDefault();
                alert('Please ask the client to provide their signature.');
            } else {
                document.getElementById('signature_data').value = sigPad.toDataURL('image/png');
            }
        });
    }
});
</script>
@endpush
@endsection
