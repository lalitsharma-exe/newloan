@extends('agent.layouts.app')
@section('page-title', 'New Client Application')
@section('content')

<div style="margin-bottom:16px">
  <a href="{{ route('agent.dashboard') }}" style="font-size:13px;color:#0f766e;text-decoration:none"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

<form method="POST" action="{{ route('agent.applications.store') }}" enctype="multipart/form-data" id="appForm">
  @csrf

  {{-- Step 1: Client Personal Details --}}
  <div class="card" style="margin-bottom:22px">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-person-fill" style="color:#0f766e"></i> Client Personal Details</div></div>
    <div class="card-body">
      <div class="g2">
        <div class="fg">
          <label class="fl">First Name <span style="color:#ef4444">*</span></label>
          <input type="text" name="first_name" class="fc @error('first_name') err @enderror" value="{{ old('first_name') }}" required maxlength="60" aria-required="true">
          @error('first_name')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Surname <span style="color:#ef4444">*</span></label>
          <input type="text" name="surname" class="fc @error('surname') err @enderror" value="{{ old('surname') }}" required maxlength="60" aria-required="true">
          @error('surname')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">National ID Number <span style="color:#ef4444">*</span></label>
          <input type="text" name="national_id" class="fc @error('national_id') err @enderror" value="{{ old('national_id') }}" required maxlength="13" pattern="\d{13}" placeholder="13 digits" aria-required="true">
          @error('national_id')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Date of Birth <span style="color:#ef4444">*</span></label>
          <input type="date" name="date_of_birth" class="fc @error('date_of_birth') err @enderror" value="{{ old('date_of_birth') }}" required aria-required="true">
          @error('date_of_birth')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Mobile Number <span style="color:#ef4444">*</span></label>
          <input type="tel" name="cell_number" class="fc @error('cell_number') err @enderror" value="{{ old('cell_number') }}" required placeholder="+266 5X XXX XXXX" aria-required="true">
          @error('cell_number')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Email (optional)</label>
          <input type="email" name="email" class="fc" value="{{ old('email') }}">
        </div>
      </div>
      <div class="fg">
        <label class="fl">Residential Address <span style="color:#ef4444">*</span></label>
        <input type="text" name="residential_address" class="fc @error('residential_address') err @enderror" value="{{ old('residential_address') }}" required maxlength="255" aria-required="true">
        @error('residential_address')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="fg">
        <label class="fl">Village / Town</label>
        <input type="text" name="village" class="fc" value="{{ old('village') }}" maxlength="120">
      </div>
    </div>
  </div>

  {{-- Step 2: Employment Details --}}
  <div class="card" style="margin-bottom:22px">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-briefcase-fill" style="color:#0f766e"></i> Employment Details</div></div>
    <div class="card-body">
      <div class="g2">
        <div class="fg">
          <label class="fl">Employer Name <span style="color:#ef4444">*</span></label>
          <input type="text" name="employer_name" class="fc @error('employer_name') err @enderror" value="{{ old('employer_name') }}" required aria-required="true">
          @error('employer_name')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Employee Number <span style="color:#ef4444">*</span></label>
          <input type="text" name="employee_number" class="fc @error('employee_number') err @enderror" value="{{ old('employee_number') }}" required aria-required="true">
          @error('employee_number')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Job Title <span style="color:#ef4444">*</span></label>
          <input type="text" name="job_title" class="fc @error('job_title') err @enderror" value="{{ old('job_title') }}" required aria-required="true">
          @error('job_title')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Monthly Net Salary (M) <span style="color:#ef4444">*</span></label>
          <input type="number" name="monthly_net_salary" id="salary" class="fc @error('monthly_net_salary') err @enderror" value="{{ old('monthly_net_salary') }}" required min="0" step="0.01" aria-required="true">
          @error('monthly_net_salary')<span class="iv">{{ $message }}</span>@enderror
        </div>
      </div>
    </div>
  </div>

  {{-- Step 3: Loan Request --}}
  <div class="card" style="margin-bottom:22px">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-cash-coin" style="color:#0f766e"></i> Loan Request</div></div>
    <div class="card-body">
      <div class="fg">
        <label class="fl">Loan Product <span style="color:#ef4444">*</span></label>
        <select name="loan_product_id" id="productSelect" class="fc @error('loan_product_id') err @enderror" required aria-required="true">
          <option value="">Select product…</option>
          @foreach($products as $p)
          <option value="{{ $p->id }}" data-rate="{{ $p->interest_rate }}" data-init="{{ $p->initiation_fee_rate }}" data-admin="{{ $p->admin_fee_fixed }}" {{ old('loan_product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
          @endforeach
        </select>
        @error('loan_product_id')<span class="iv">{{ $message }}</span>@enderror
      </div>
      <div class="g2">
        <div class="fg">
          <label class="fl">Loan Amount (M100 – M5,000) <span style="color:#ef4444">*</span></label>
          <input type="number" name="requested_amount" id="amount" class="fc @error('requested_amount') err @enderror" value="{{ old('requested_amount') }}" required min="100" max="5000" step="50" aria-required="true">
          @error('requested_amount')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Period <span style="color:#ef4444">*</span></label>
          <select name="requested_term" id="term" class="fc @error('requested_term') err @enderror" required aria-required="true">
            <option value="">Select…</option>
            <option value="1" {{ old('requested_term') == '1' ? 'selected' : '' }}>1 month</option>
            <option value="2" {{ old('requested_term') == '2' ? 'selected' : '' }}>2 months</option>
            <option value="3" {{ old('requested_term') == '3' ? 'selected' : '' }}>3 months</option>
          </select>
          @error('requested_term')<span class="iv">{{ $message }}</span>@enderror
        </div>
      </div>

      {{-- Live Schedule Calculator --}}
      <div id="scheduleBox" style="display:none;margin-top:14px;border:1px solid #d1e7dd;border-radius:12px;padding:16px;background:#f0fdf4">
        <div style="font-size:13px;font-weight:700;margin-bottom:10px"><i class="bi bi-calculator"></i> Loan Breakdown</div>
        <div class="g3" style="gap:12px;margin-bottom:10px">
          <div><div style="font-size:11px;color:#64748b">Interest</div><div style="font-weight:700" id="calcInterest">-</div></div>
          <div><div style="font-size:11px;color:#64748b">Initiation Fee</div><div style="font-weight:700" id="calcInit">-</div></div>
          <div><div style="font-size:11px;color:#64748b">Admin Fee</div><div style="font-weight:700" id="calcAdmin">-</div></div>
        </div>
        <div class="g2" style="gap:12px">
          <div style="background:#fff;border:1px solid #d1e7dd;border-radius:8px;padding:10px;text-align:center">
            <div style="font-size:11px;color:#64748b">Total Repayable</div>
            <div style="font-size:20px;font-weight:800;color:#0f766e" id="calcTotal">-</div>
          </div>
          <div style="background:#fff;border:1px solid #d1e7dd;border-radius:8px;padding:10px;text-align:center">
            <div style="font-size:11px;color:#64748b">Monthly Instalment</div>
            <div style="font-size:20px;font-weight:800;color:#0f766e" id="calcInstalment">-</div>
          </div>
        </div>
      </div>

      {{-- Affordability Warning --}}
      <div id="affordWarn" style="display:none;margin-top:14px;background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.2);border-radius:12px;padding:16px;color:#991b1b">
        <div style="font-size:13px;font-weight:700;margin-bottom:6px"><i class="bi bi-exclamation-triangle-fill"></i> Affordability Limit Exceeded</div>
        <div style="font-size:12px" id="affordMsg"></div>
      </div>

      {{-- 2x Cap Warning --}}
      <div id="capWarn" style="display:none;margin-top:14px;background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.2);border-radius:12px;padding:16px;color:#92400e">
        <div style="font-size:13px;font-weight:700;margin-bottom:6px"><i class="bi bi-shield-exclamation"></i> 2× Capital Cap Exceeded</div>
        <div style="font-size:12px" id="capMsg"></div>
      </div>
    </div>
  </div>

  {{-- Step 4: Document Uploads --}}
  <div class="card" style="margin-bottom:22px">
    <div class="card-hdr"><div class="card-title"><i class="bi bi-camera-fill" style="color:#0f766e"></i> Document Uploads</div></div>
    <div class="card-body">
      <div class="g3">
        <div class="fg">
          <label class="fl">National ID Photo <span style="color:#ef4444">*</span></label>
          <input type="file" name="national_id_photo" class="fc @error('national_id_photo') err @enderror" accept="image/jpeg,image/png" required aria-required="true">
          <div class="ft">JPG or PNG, max 5 MB</div>
          @error('national_id_photo')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Payslip (not older than 3 months) <span style="color:#ef4444">*</span></label>
          <input type="file" name="payslip_photo" class="fc @error('payslip_photo') err @enderror" accept="image/jpeg,image/png" required aria-required="true">
          <div class="ft">JPG or PNG, max 5 MB</div>
          @error('payslip_photo')<span class="iv">{{ $message }}</span>@enderror
        </div>
        <div class="fg">
          <label class="fl">Selfie Holding ID <span style="color:#ef4444">*</span></label>
          <input type="file" name="selfie_photo" class="fc @error('selfie_photo') err @enderror" accept="image/jpeg,image/png" required aria-required="true">
          <div class="ft">JPG or PNG, max 5 MB</div>
          @error('selfie_photo')<span class="iv">{{ $message }}</span>@enderror
        </div>
      </div>
    </div>
  </div>

  {{-- Submit --}}
  <button type="submit" class="btn btn-p" id="submitBtn" style="font-size:15px;padding:14px 32px;border-radius:12px">
    <i class="bi bi-send-fill"></i> Submit Application
  </button>
</form>

@push('scripts')
<script>
(function(){
  const amount = document.getElementById('amount');
  const term   = document.getElementById('term');
  const salary = document.getElementById('salary');
  const prodSel= document.getElementById('productSelect');
  const box    = document.getElementById('scheduleBox');
  const affordWarn = document.getElementById('affordWarn');
  const capWarn    = document.getElementById('capWarn');
  const submitBtn  = document.getElementById('submitBtn');

  function calc(){
    const a = parseFloat(amount.value) || 0;
    const t = parseInt(term.value) || 0;
    const s = parseFloat(salary.value) || 0;
    const opt = prodSel.options[prodSel.selectedIndex];
    if(!a || !t || !opt || !opt.value){ box.style.display='none'; affordWarn.style.display='none'; capWarn.style.display='none'; submitBtn.disabled=false; return; }

    const rate = parseFloat(opt.dataset.rate)/100;
    const initRate = parseFloat(opt.dataset.init)/100;
    const adminFee = parseFloat(opt.dataset.admin);

    const interest = Math.round(a * rate * t * 100)/100;
    const initFee  = Math.round(a * initRate * 100)/100;
    const totalAdmin = adminFee * t;
    const totalRepay = a + interest + initFee + totalAdmin;
    const instalment = Math.round(totalRepay / t * 100)/100;
    const maxInst    = Math.round(s * 0.30 * 100)/100;
    const capLimit   = 2 * a;

    document.getElementById('calcInterest').textContent = 'M' + interest.toFixed(2);
    document.getElementById('calcInit').textContent = 'M' + initFee.toFixed(2);
    document.getElementById('calcAdmin').textContent = 'M' + totalAdmin.toFixed(2);
    document.getElementById('calcTotal').textContent = 'M' + totalRepay.toFixed(2);
    document.getElementById('calcInstalment').textContent = 'M' + instalment.toFixed(2);
    box.style.display = 'block';

    // 2x cap check
    if(totalRepay > capLimit){
      capWarn.style.display = 'block';
      document.getElementById('capMsg').textContent = 'Total repayable M' + totalRepay.toFixed(2) + ' exceeds 2× capital limit of M' + capLimit.toFixed(2) + '. This combination is not available.';
      submitBtn.disabled = true;
    } else {
      capWarn.style.display = 'none';
    }

    // 30% affordability
    if(s > 0 && instalment > maxInst){
      affordWarn.style.display = 'block';
      document.getElementById('affordMsg').textContent = 'Monthly instalment M' + instalment.toFixed(2) + ' exceeds 30% of client net salary (limit: M' + maxInst.toFixed(2) + ').';
      submitBtn.disabled = true;
    } else {
      affordWarn.style.display = 'none';
    }

    if(totalRepay <= capLimit && (s <= 0 || instalment <= maxInst)){
      submitBtn.disabled = false;
    }
  }

  [amount, term, salary, prodSel].forEach(el => el.addEventListener('change', calc));
  [amount, salary].forEach(el => el.addEventListener('input', calc));
  calc();
})();
</script>
@endpush

@endsection
