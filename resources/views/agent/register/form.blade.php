<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Become a MyLoan Agent</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:'Inter',sans-serif;background:linear-gradient(160deg,#042f2e,#115e59);min-height:100vh;padding:20px}
.container{max-width:640px;margin:0 auto}
.header{text-align:center;padding:30px 0 20px;color:#fff}
.header img{height:42px;margin-bottom:12px}
.header h1{font-size:24px;font-weight:800;margin-bottom:6px}
.header p{font-size:14px;color:rgba(255,255,255,.6)}
.form-card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.stepper{display:flex;border-bottom:1px solid #e2e8f0;background:#f8fafc}
.step-tab{flex:1;padding:14px 8px;text-align:center;font-size:11px;font-weight:600;color:#94a3b8;cursor:pointer;transition:all .2s;border-bottom:2px solid transparent}
.step-tab.active{color:#0f766e;border-bottom-color:#0f766e;background:#f0fdf4}
.step-tab.done{color:#10b981}
.step-tab .num{display:inline-flex;width:22px;height:22px;border-radius:50%;align-items:center;justify-content:center;font-size:10px;font-weight:800;margin-bottom:2px;background:#e2e8f0;color:#64748b}
.step-tab.active .num{background:#0f766e;color:#fff}
.step-tab.done .num{background:#10b981;color:#fff}
.step-panel{display:none;padding:28px}.step-panel.active{display:block}
.fg{margin-bottom:18px}.fl{display:block;font-size:12px;font-weight:600;margin-bottom:5px;color:#334155}
.fl .req{color:#ef4444}
.fc{width:100%;padding:10px 13px;border:1.5px solid #d1e7dd;border-radius:8px;font-size:14px;font-family:'Inter',sans-serif;outline:none;transition:all .2s;background:#fff}
.fc:focus{border-color:#0f766e;box-shadow:0 0 0 3px rgba(15,118,110,.1)}
select.fc{cursor:pointer}
.fc.err{border-color:#ef4444}
.iv{font-size:12px;color:#ef4444;margin-top:3px;display:none}
.type-cards{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px}
.type-card{border:2px solid #d1e7dd;border-radius:14px;padding:20px;text-align:center;cursor:pointer;transition:all .2s}
.type-card:hover{border-color:#0f766e;background:#f0fdf4}
.type-card.selected{border-color:#0f766e;background:#f0fdf4;box-shadow:0 0 0 3px rgba(15,118,110,.15)}
.type-card i{font-size:28px;color:#0f766e;margin-bottom:8px;display:block}
.type-card strong{font-size:14px;display:block;margin-bottom:4px}
.type-card span{font-size:11px;color:#64748b}
.upload-zone{border:2px dashed #d1e7dd;border-radius:12px;padding:24px;text-align:center;cursor:pointer;transition:all .2s}
.upload-zone:hover{border-color:#0f766e;background:#f0fdf4}
.upload-zone.has-file{border-color:#10b981;background:rgba(16,185,129,.06)}
.upload-zone i{font-size:28px;color:#94a3b8;margin-bottom:6px;display:block}
.upload-zone.has-file i{color:#10b981}
.upload-zone input[type="file"]{display:none}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.btn-row{display:flex;gap:10px;padding:20px 28px;border-top:1px solid #e2e8f0;justify-content:flex-end}
.btn{padding:10px 20px;border-radius:9px;border:none;font-size:13px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:all .2s}
.btn-next{background:#0f766e;color:#fff}.btn-next:hover{background:#0d5b55}
.btn-back{background:#f1f5f9;color:#334155}.btn-back:hover{background:#e2e8f0}
.btn-submit{background:linear-gradient(135deg,#134e4a,#0f766e);color:#fff;padding:13px 28px;font-size:14px}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(15,118,110,.3)}
.btn-submit:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}
.agreement-box{max-height:200px;overflow-y:auto;border:1px solid #d1e7dd;border-radius:10px;padding:14px;font-size:12px;color:#475569;line-height:1.7;margin-bottom:14px;background:#f8fafc}
.check-row{display:flex;align-items:center;gap:8px;margin-bottom:14px}
.check-row input{width:18px;height:18px;accent-color:#0f766e}
.check-row label{font-size:13px;color:#334155;cursor:pointer}
.cond-fields{display:none}.cond-fields.show{display:block}
@media(max-width:480px){.g2{grid-template-columns:1fr}.type-cards{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="container">
  <div class="header">
    <img src="{{ config('app.logo', 'https://ik.imagekit.io/ygydr1m84/png.webp') }}" alt="MyLoan" style="filter:brightness(0) invert(1)">
    <h1>Become a MyLoan Agent</h1>
    <p>Earn M50 per qualifying loan application. Register in 5 minutes.</p>
  </div>

  <div class="form-card">
    <div class="stepper">
      <div class="step-tab active" data-step="1"><div class="num">1</div><br>Details</div>
      <div class="step-tab" data-step="2"><div class="num">2</div><br>Documents</div>
      <div class="step-tab" data-step="3"><div class="num">3</div><br>Payout</div>
      <div class="step-tab" data-step="4"><div class="num">4</div><br>Review</div>
    </div>

    <form method="POST" action="{{ route('agent.register.submit') }}" enctype="multipart/form-data" id="regForm" novalidate>
      @csrf

      {{-- STEP 1 --}}
      <div class="step-panel active" data-panel="1">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px">Personal & Business Details</div>

        <div style="font-size:13px;font-weight:600;margin-bottom:10px">I am registering as a…</div>
        <div class="type-cards">
          <div class="type-card" data-type="shop" onclick="selectType('shop')">
            <i class="bi bi-shop"></i><strong>Shop Owner</strong><span>Spaza, tuck shop, dealer</span>
          </div>
          <div class="type-card" data-type="individual" onclick="selectType('individual')">
            <i class="bi bi-person-badge"></i><strong>Individual</strong><span>Community connector</span>
          </div>
        </div>
        <input type="hidden" name="agent_type" id="agentType" value="{{ old('agent_type', '') }}">

        <div class="g2">
          <div class="fg"><label class="fl">First Name <span class="req">*</span></label><input type="text" name="first_name" class="fc" value="{{ old('first_name') }}" required maxlength="60"></div>
          <div class="fg"><label class="fl">Last Name <span class="req">*</span></label><input type="text" name="last_name" class="fc" value="{{ old('last_name') }}" required maxlength="60"></div>
        </div>
        <div class="g2">
          <div class="fg"><label class="fl">National ID (13 digits) <span class="req">*</span></label><input type="text" name="national_id" class="fc" value="{{ old('national_id') }}" required maxlength="13" pattern="\d{13}"></div>
          <div class="fg"><label class="fl">Mobile Number <span class="req">*</span></label><input type="tel" name="mobile_number" class="fc" value="{{ old('mobile_number') }}" required placeholder="+266 5X XXX XXXX"></div>
        </div>

        <div id="shopFields" class="cond-fields">
          <div class="fg"><label class="fl">Shop / Business Name <span class="req">*</span></label><input type="text" name="shop_name" class="fc" value="{{ old('shop_name') }}" maxlength="100"></div>
          <div class="fg">
            <label class="fl">Type of Business <span class="req">*</span></label>
            <select name="business_type" class="fc">
              <option value="">Select…</option>
              <option {{ old('business_type')=='Spaza / General Dealer' ? 'selected' : '' }}>Spaza / General Dealer</option>
              <option {{ old('business_type')=='Tuck Shop' ? 'selected' : '' }}>Tuck Shop</option>
              <option {{ old('business_type')=='Mobile Money Agent' ? 'selected' : '' }}>Mobile Money Agent</option>
              <option {{ old('business_type')=='Pharmacy / Health Shop' ? 'selected' : '' }}>Pharmacy / Health Shop</option>
              <option {{ old('business_type')=='Other' ? 'selected' : '' }}>Other</option>
            </select>
          </div>
        </div>

        <div class="fg"><label class="fl">Location / Village <span class="req">*</span></label><input type="text" name="shop_location" class="fc" value="{{ old('shop_location') }}" required maxlength="120" placeholder="e.g. Ha Matala, Maseru"></div>
      </div>

      {{-- STEP 2 --}}
      <div class="step-panel" data-panel="2">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px">Document Upload</div>
        <div class="fg">
          <label class="fl">National ID Photo <span class="req">*</span></label>
          <div class="upload-zone" id="zone_id" onclick="document.getElementById('f_id').click()">
            <i class="bi bi-credit-card-2-front"></i>
            <div style="font-size:13px;font-weight:600" id="lbl_id">Tap to upload ID photo</div>
            <div style="font-size:11px;color:#94a3b8">JPG or PNG, max 5 MB</div>
            <input type="file" name="national_id_photo" id="f_id" accept="image/jpeg,image/png" required onchange="fileSelected(this,'zone_id','lbl_id')">
          </div>
        </div>
        <div class="fg">
          <label class="fl">Selfie Holding ID <span class="req">*</span></label>
          <div class="upload-zone" id="zone_selfie" onclick="document.getElementById('f_selfie').click()">
            <i class="bi bi-camera"></i>
            <div style="font-size:13px;font-weight:600" id="lbl_selfie">Tap to take selfie with ID</div>
            <div style="font-size:11px;color:#94a3b8">JPG or PNG, max 5 MB</div>
            <input type="file" name="selfie_holding_id" id="f_selfie" accept="image/jpeg,image/png" required onchange="fileSelected(this,'zone_selfie','lbl_selfie')">
          </div>
        </div>
        <div class="fg" id="licenceUpload" class="cond-fields">
          <label class="fl">Business Licence / Permit <span style="color:#94a3b8">(optional)</span></label>
          <div class="upload-zone" id="zone_lic" onclick="document.getElementById('f_lic').click()">
            <i class="bi bi-file-earmark-text"></i>
            <div style="font-size:13px;font-weight:600" id="lbl_lic">Tap to upload licence</div>
            <div style="font-size:11px;color:#94a3b8">JPG, PNG or PDF, max 5 MB</div>
            <input type="file" name="business_licence" id="f_lic" accept="image/jpeg,image/png,application/pdf" onchange="fileSelected(this,'zone_lic','lbl_lic')">
          </div>
        </div>
      </div>

      {{-- STEP 3 --}}
      <div class="step-panel" data-panel="3">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px">Payout Setup</div>
        <div class="fg">
          <label class="fl">Preferred Payout Method <span class="req">*</span></label>
          <select name="payout_method" id="payoutMethod" class="fc" required onchange="togglePayout()">
            <option value="">Select…</option>
            <option {{ old('payout_method')=='M-Pesa' ? 'selected' : '' }}>M-Pesa</option>
            <option {{ old('payout_method')=='EcoCash' ? 'selected' : '' }}>EcoCash</option>
            <option {{ old('payout_method')=='Bank transfer' ? 'selected' : '' }}>Bank transfer</option>
          </select>
        </div>
        <div id="mobilePayFields" class="cond-fields">
          <div class="fg"><label class="fl">Mobile Money Number <span class="req">*</span></label><input type="tel" name="payout_number_or_details" class="fc" value="{{ old('payout_number_or_details') }}" placeholder="+266 5X XXX XXXX"></div>
        </div>
        <div id="bankPayFields" class="cond-fields">
          <div class="fg">
            <label class="fl">Bank Name <span class="req">*</span></label>
            <select name="payout_bank_name" class="fc">
              <option value="">Select…</option>
              <option {{ old('payout_bank_name')=='Standard Lesotho Bank' ? 'selected' : '' }}>Standard Lesotho Bank</option>
              <option {{ old('payout_bank_name')=='FNB Lesotho' ? 'selected' : '' }}>FNB Lesotho</option>
              <option {{ old('payout_bank_name')=='Nedbank Lesotho' ? 'selected' : '' }}>Nedbank Lesotho</option>
              <option {{ old('payout_bank_name')=='PostBank Lesotho' ? 'selected' : '' }}>PostBank Lesotho</option>
            </select>
          </div>
          <div class="fg"><label class="fl">Account Number <span class="req">*</span></label><input type="text" name="payout_number_or_details" class="fc" maxlength="20"></div>
        </div>
        <div class="fg"><label class="fl">Account Holder Name <span class="req">*</span></label><input type="text" name="payout_account_name" class="fc" value="{{ old('payout_account_name') }}" required maxlength="80"></div>
      </div>

      {{-- STEP 4 --}}
      <div class="step-panel" data-panel="4">
        <div style="font-size:16px;font-weight:700;margin-bottom:16px">Review & Agreement</div>
        <div id="reviewSummary" style="background:#f8fafc;border-radius:12px;padding:16px;margin-bottom:18px;font-size:13px"></div>

        <div style="font-size:13px;font-weight:700;margin-bottom:8px">Agent Agreement</div>
        <div class="agreement-box">
          <p><strong>1. Appointment</strong> — MyLoan Financial Services appoints you as a community loan origination agent on a non-exclusive basis. This appointment is personal and may not be transferred.</p>
          <p><strong>2. Nature of Relationship</strong> — You are an independent contractor, not an employee of MyLoan. You bear no financial risk related to any loan originated through you.</p>
          <p><strong>3. Agent Obligations</strong> — You shall: accurately capture client information; submit only genuine applications; never charge clients any fees; maintain client confidentiality; and comply with all applicable laws.</p>
          <p><strong>4. What You Must Not Do</strong> — You must not: make credit decisions; promise loan approval; handle disbursement funds; collect loan repayments; or misrepresent your role.</p>
          <p><strong>5. Credit Decisions</strong> — All lending decisions are made solely by MyLoan. You acknowledge that submission of an application does not guarantee approval.</p>
          <p><strong>6. Confidentiality</strong> — Client personal data collected must be treated as strictly confidential and used only for the purpose of loan application submission.</p>
          <p><strong>7. Termination</strong> — Either party may terminate this agreement with 7 days written notice. MyLoan reserves the right to immediately terminate for breach of the Code of Conduct.</p>
          <p><strong>8. Governing Law</strong> — This agreement is governed by the laws of the Kingdom of Lesotho.</p>
        </div>

        <div class="check-row">
          <input type="checkbox" name="agreement" id="agreeCheck" value="1" required>
          <label for="agreeCheck">I have read and accept the MyLoan Agent Agreement.</label>
        </div>
      </div>

      <div class="btn-row">
        <button type="button" class="btn btn-back" id="btnBack" style="display:none" onclick="prevStep()"><i class="bi bi-arrow-left"></i> Back</button>
        <button type="button" class="btn btn-next" id="btnNext" onclick="nextStep()">Next <i class="bi bi-arrow-right"></i></button>
        <button type="submit" class="btn btn-submit" id="btnSubmit" style="display:none" disabled><i class="bi bi-send-fill"></i> Submit Application</button>
      </div>
    </form>
  </div>

  <div style="text-align:center;margin-top:20px">
    <a href="{{ route('agent.login') }}" style="color:rgba(255,255,255,.5);font-size:13px;text-decoration:none">Already an agent? <span style="color:#2dd4bf">Sign in →</span></a>
  </div>
</div>

<script>
let currentStep = 1;
const totalSteps = 4;

function showStep(n) {
  document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.step-tab').forEach(t => { t.classList.remove('active'); if(parseInt(t.dataset.step) < n) t.classList.add('done'); else t.classList.remove('done'); });
  document.querySelector('[data-panel="'+n+'"]').classList.add('active');
  document.querySelector('[data-step="'+n+'"]').classList.add('active');
  document.getElementById('btnBack').style.display = n > 1 ? '' : 'none';
  document.getElementById('btnNext').style.display = n < totalSteps ? '' : 'none';
  document.getElementById('btnSubmit').style.display = n === totalSteps ? '' : 'none';
  if(n === 4) buildReview();
}

function nextStep() {
  if (validateStep(currentStep)) {
    if(currentStep < totalSteps) { currentStep++; showStep(currentStep); }
  }
}
function prevStep() { if(currentStep > 1) { currentStep--; showStep(currentStep); } }

function validateStep(step) {
  // Clear previous error styles
  document.querySelectorAll('.fc').forEach(el => el.classList.remove('err'));
  
  if (step === 1) {
    const agentType = document.getElementById('agentType').value;
    if (!agentType) {
      alert('Please select either "Shop Owner" or "Individual" to proceed.');
      return false;
    }
    
    let valid = true;
    const reqFields = ['first_name', 'last_name', 'national_id', 'mobile_number', 'shop_location'];
    if (agentType === 'shop') {
      reqFields.push('shop_name', 'business_type');
    }
    
    reqFields.forEach(name => {
      const input = document.querySelector('[name="' + name + '"]');
      if (!input || !input.value.trim()) {
        if (input) input.classList.add('err');
        valid = false;
      }
    });

    if (!valid) {
      alert('Please fill in all required fields marked with *');
      return false;
    }

    // Verify national ID format (exactly 13 digits)
    const natIdInput = document.querySelector('[name="national_id"]');
    const natIdVal = natIdInput.value.trim();
    if (!/^\d{13}$/.test(natIdVal)) {
      natIdInput.classList.add('err');
      alert('Lesotho National ID must be exactly 13 numeric digits.');
      return false;
    }
  }

  if (step === 2) {
    const idFile = document.getElementById('f_id').files.length;
    const selfieFile = document.getElementById('f_selfie').files.length;
    
    if (!idFile) {
      document.getElementById('zone_id').classList.add('err');
      alert('Please upload a photo of your National ID.');
      return false;
    }
    if (!selfieFile) {
      document.getElementById('zone_selfie').classList.add('err');
      alert('Please upload a selfie holding your ID.');
      return false;
    }
  }

  if (step === 3) {
    const payoutMethod = document.getElementById('payoutMethod').value;
    if (!payoutMethod) {
      document.getElementById('payoutMethod').classList.add('err');
      alert('Please select a preferred payout method.');
      return false;
    }
    
    const accountName = document.querySelector('[name="payout_account_name"]');
    if (!accountName || !accountName.value.trim()) {
      if (accountName) accountName.classList.add('err');
      alert('Please enter the payout account holder name.');
      return false;
    }

    // Dynamic fields depending on selected payout method
    if (payoutMethod === 'M-Pesa' || payoutMethod === 'EcoCash') {
      // There are two inputs with name="payout_number_or_details" in the HTML. We must target the visible one
      const visibleNum = document.querySelector('#mobilePayFields [name="payout_number_or_details"]');
      if (!visibleNum || !visibleNum.value.trim()) {
        if (visibleNum) visibleNum.classList.add('err');
        alert('Please enter your mobile money number.');
        return false;
      }
    } else if (payoutMethod === 'Bank transfer') {
      const bankName = document.querySelector('[name="payout_bank_name"]');
      if (!bankName || !bankName.value.trim()) {
        if (bankName) bankName.classList.add('err');
        alert('Please select your bank.');
        return false;
      }
      const visibleNum = document.querySelector('#bankPayFields [name="payout_number_or_details"]');
      if (!visibleNum || !visibleNum.value.trim()) {
        if (visibleNum) visibleNum.classList.add('err');
        alert('Please enter your bank account number.');
        return false;
      }
    }
  }

  return true;
}

function selectType(type) {
  document.getElementById('agentType').value = type;
  document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
  document.querySelector('[data-type="'+type+'"]').classList.add('selected');
  document.getElementById('shopFields').classList.toggle('show', type === 'shop');
}

function togglePayout() {
  const m = document.getElementById('payoutMethod').value;
  document.getElementById('mobilePayFields').classList.toggle('show', m === 'M-Pesa' || m === 'EcoCash');
  document.getElementById('bankPayFields').classList.toggle('show', m === 'Bank transfer');
}

function fileSelected(input, zoneId, lblId) {
  const zone = document.getElementById(zoneId);
  const lbl = document.getElementById(lblId);
  if(input.files.length) {
    zone.classList.add('has-file');
    lbl.textContent = input.files[0].name;
  }
}

function buildReview() {
  const f = document.getElementById('regForm');
  const fd = new FormData(f);
  let html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">';
  const fields = [['First Name','first_name'],['Last Name','last_name'],['National ID','national_id'],['Mobile','mobile_number'],['Type','agent_type'],['Location','shop_location']];
  fields.forEach(([l,n]) => { html += '<div><span style="font-size:10px;color:#94a3b8;text-transform:uppercase;font-weight:600">'+l+'</span><br><strong>'+( fd.get(n) || '—')+'</strong></div>'; });
  html += '</div>';
  if(fd.get('agent_type')==='shop') { html += '<div style="margin-top:8px"><span style="font-size:10px;color:#94a3b8;text-transform:uppercase;font-weight:600">Shop</span><br><strong>'+(fd.get('shop_name')||'—')+'</strong></div>'; }
  html += '<hr style="margin:10px 0;border:none;border-top:1px solid #e2e8f0">';
  html += '<div><span style="font-size:10px;color:#94a3b8;text-transform:uppercase;font-weight:600">Payout</span><br><strong>'+(fd.get('payout_method')||'—')+'</strong> — '+(fd.get('payout_account_name')||'—')+'</div>';
  const docs = [];
  if(document.getElementById('f_id').files.length) docs.push('National ID ✔');
  if(document.getElementById('f_selfie').files.length) docs.push('Selfie ✔');
  if(document.getElementById('f_lic').files.length) docs.push('Licence ✔');
  html += '<div style="margin-top:8px"><span style="font-size:10px;color:#94a3b8;text-transform:uppercase;font-weight:600">Documents</span><br>' + docs.join(' · ') + '</div>';
  document.getElementById('reviewSummary').innerHTML = html;
}

// Enable submit only when checkbox is ticked
document.getElementById('agreeCheck').addEventListener('change', function(){ document.getElementById('btnSubmit').disabled = !this.checked; });

// Restore type selection on page load
@if(old('agent_type')) selectType('{{ old('agent_type') }}'); @endif
togglePayout();
</script>
</body>
</html>
