@extends('borrower.layouts.app')
@section('title','MyBill — ' . ucfirst($category))
@section('content')

@if(session('error'))<div class="alert a-e mb-3"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>@endif

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px">
  <div style="display:flex;align-items:center;gap:16px">
    <a href="{{ route('borrower.mybill.index') }}" style="width:40px;height:40px;border-radius:12px;background:#fff;border:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;color:#64748b;text-decoration:none;transition:all .2s;box-shadow:0 2px 8px rgba(0,0,0,0.02)">
      <i class="bi bi-arrow-left"></i>
    </a>
    <div>
      <h2 style="font-size:22px;font-weight:900;margin:0;color:#0f172a">
        Pay {{ ucfirst($category) }}
      </h2>
      <div style="font-size:12.5px;color:var(--muted)">Account Available: <strong style="color:#0f172a">M {{ number_format($limit->available_amount, 2) }}</strong></div>
    </div>
  </div>
  
  <div style="display:flex;align-items:center;gap:12px">
    @if($category === 'electricity')
      <img src="/assets/logos/lec_logo.png" style="height:32px" onerror="this.style.display='none'">
    @elseif($category === 'airtime')
      <img src="/assets/logos/vodacom.png" style="height:20px" onerror="this.style.display='none'">
      <img src="/assets/logos/econet.png" style="height:20px" onerror="this.style.display='none'">
    @elseif($category === 'insurance')
      <i class="bi bi-shield-fill-check" style="color:#8b5cf6;font-size:28px"></i>
    @elseif($category === 'ticket')
      <i class="bi bi-ticket-perforated-fill" style="color:#ec4899;font-size:28px"></i>
    @endif
  </div>
</div>

{{-- Purchase Form --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:24px">

  <form id="billForm" method="POST" action="{{ route('borrower.mybill.confirm') }}">
    @csrf
    <input type="hidden" name="bill_category" value="{{ $category }}">

    {{-- Category-specific fields --}}
    @if($category === 'electricity')
    <div style="margin-bottom:18px">
      <label style="font-size:13px;font-weight:600;color:var(--ink);display:block;margin-bottom:6px">Meter Number *</label>
      <div style="display:flex;gap:10px">
        <input type="text" name="meter_number" id="meterNumber" placeholder="Enter your electricity meter number"
               style="flex:1;padding:11px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px;font-family:inherit;outline:none;transition:border .2s"
               onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'" required>
        <button type="button" onclick="lookupMeter()" style="padding:11px 18px;border:none;border-radius:10px;background:var(--blue);color:#fff;font-weight:600;font-size:13px;cursor:pointer;white-space:nowrap;font-family:inherit">
          <i class="bi bi-search"></i> Verify
        </button>
      </div>
      <div id="meterResult" style="margin-top:8px;font-size:12px;display:none"></div>
    </div>
    @endif

    @if($category === 'airtime')
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px">
      <div>
        <label style="font-size:13px;font-weight:600;color:var(--ink);display:block;margin-bottom:6px">Recipient Phone *</label>
        <input type="text" name="phone_number" placeholder="e.g. 58123456"
               style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px;font-family:inherit;outline:none" required>
      </div>
      <div>
        <label style="font-size:13px;font-weight:600;color:var(--ink);display:block;margin-bottom:6px">Network *</label>
        <select name="airtime_type" style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px;font-family:inherit;outline:none;background:#fff" required>
          <option value="VCL">Vodacom Lesotho</option>
          <option value="ETL">Econet Lesotho</option>
        </select>
      </div>
    </div>
    @endif

    @if($category === 'insurance')
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px">
      <div>
        <label style="font-size:13px;font-weight:600;color:var(--ink);display:block;margin-bottom:6px">Insurance Provider *</label>
        <select name="insurance_partner_id" id="insurancePartner"
                style="width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px;font-family:inherit;outline:none;background:#fff" required>
          <option value="">Select provider...</option>
          @if(!empty($providerData['data']) && is_array($providerData['data']))
            @foreach($providerData['data'] as $provider)
              <option value="{{ $provider['id'] ?? '' }}">{{ $provider['name'] ?? 'Unknown' }}</option>
            @endforeach
          @endif
        </select>
      </div>
      <div>
        <label style="font-size:13px;font-weight:600;color:var(--ink);display:block;margin-bottom:6px">Policy Number *</label>
        <div style="display:flex;gap:10px">
          <input type="text" name="policy_number" id="policyNumber" placeholder="Enter policy number"
                 style="flex:1;padding:11px 14px;border:1px solid var(--border);border-radius:10px;font-size:14px;font-family:inherit;outline:none" required>
          <button type="button" onclick="lookupInsurance()" style="padding:11px 14px;border:none;border-radius:10px;background:var(--blue);color:#fff;font-weight:600;font-size:12px;cursor:pointer;font-family:inherit">
            <i class="bi bi-search"></i>
          </button>
        </div>
        <div id="insuranceResult" style="margin-top:8px;font-size:12px;display:none"></div>
      </div>
    </div>
    @endif

    @if($category === 'ticket')
    <div style="margin-bottom:18px">
      <label style="font-size:13px;font-weight:600;color:var(--ink);display:block;margin-bottom:6px">Select Event</label>
      <div style="font-size:12px;color:var(--muted);margin-bottom:10px">Browse available events and enter the ticket amount below</div>
      <input type="hidden" name="event_id" id="eventId">
      <input type="hidden" name="ticket_id" id="ticketId">
    </div>
    @endif

    {{-- Amount --}}
    <div style="margin-bottom:18px">
      <label style="font-size:13px;font-weight:600;color:var(--ink);display:block;margin-bottom:6px">Bill Amount (M) *</label>
      <input type="number" name="bill_value" id="billAmount" step="0.01" min="1" max="{{ $limit->available_amount }}"
             placeholder="Enter amount (max M{{ number_format($limit->available_amount, 0) }})"
             style="width:100%;padding:14px 16px;border:1px solid var(--border);border-radius:10px;font-size:16px;font-weight:600;font-family:inherit;outline:none;transition:border .2s"
             onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'"
             oninput="updateQuote()" required>
      <div style="font-size:11px;color:var(--muted);margin-top:4px">Maximum: M {{ number_format($limit->available_amount, 2) }}</div>
    </div>

    {{-- Quote Display --}}
    <div id="quoteSection" style="display:none;margin-bottom:22px">
      <div style="font-size:13px;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:6px">
        <i class="bi bi-calculator-fill" style="color:var(--blue)"></i> Fee Breakdown
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        {{-- Standard Tier --}}
        <label style="cursor:pointer" id="tierCard30">
          <input type="radio" name="tier" value="30" style="display:none" checked>
          <div id="tier30" style="border:2px solid var(--blue);border-radius:14px;padding:18px;background:rgba(43,75,173,.03);transition:all .3s">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
              <span style="font-size:12px;font-weight:700;color:var(--blue);background:rgba(43,75,173,.1);padding:3px 10px;border-radius:6px">STANDARD</span>
              <span style="font-size:12px;font-weight:700;color:#059669">30% fee</span>
            </div>
            <div style="font-size:12px;color:var(--muted);line-height:1.8">
              <div style="display:flex;justify-content:space-between"><span>Upfront (10%)</span><strong id="q30upfront" style="color:var(--ink)">—</strong></div>
              <div style="display:flex;justify-content:space-between"><span>Payday deduction</span><strong id="q30payday" style="color:var(--ink)">—</strong></div>
              <div style="display:flex;justify-content:space-between;border-top:1px solid var(--border);padding-top:6px;margin-top:6px"><span>Total fee</span><strong id="q30fee" style="color:#059669">—</strong></div>
            </div>
          </div>
        </label>

        {{-- No-Upfront Tier --}}
        <label style="cursor:pointer" id="tierCard40">
          <input type="radio" name="tier" value="40" style="display:none">
          <div id="tier40" style="border:2px solid var(--border);border-radius:14px;padding:18px;transition:all .3s">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
              <span style="font-size:12px;font-weight:700;color:#6b7280;background:#f3f4f6;padding:3px 10px;border-radius:6px">NO UPFRONT</span>
              <span style="font-size:12px;font-weight:700;color:#d97706">40% fee</span>
            </div>
            <div style="font-size:12px;color:var(--muted);line-height:1.8">
              <div style="display:flex;justify-content:space-between"><span>Upfront</span><strong style="color:var(--ink)">M 0.00</strong></div>
              <div style="display:flex;justify-content:space-between"><span>Payday deduction</span><strong id="q40payday" style="color:var(--ink)">—</strong></div>
              <div style="display:flex;justify-content:space-between;border-top:1px solid var(--border);padding-top:6px;margin-top:6px"><span>Total fee</span><strong id="q40fee" style="color:#d97706">—</strong></div>
            </div>
          </div>
        </label>
      </div>

      <div id="extraSaving" style="margin-top:10px;background:#ecfdf5;border:1px solid rgba(16,185,129,.2);border-radius:10px;padding:10px 14px;font-size:12px;color:#065f46;display:none">
        <i class="bi bi-piggy-bank-fill"></i> <strong>Save M <span id="savingAmount">0</span></strong> by paying 10% upfront!
      </div>
    </div>

    {{-- Submit --}}
    <button type="submit" id="submitBtn" style="width:100%;padding:14px;border:none;border-radius:12px;background:linear-gradient(135deg,var(--blue),var(--blue2));color:#fff;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:8px"
            onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(43,75,173,.3)'"
            onmouseout="this.style.transform='';this.style.boxShadow=''">
      <i class="bi bi-shield-check"></i> Review & Confirm
    </button>
  </form>
</div>

<style>
.alert{padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px}
.a-e{background:rgba(239,68,68,.08);color:#991b1b;border:1px solid rgba(239,68,68,.2)}
.mb-3{margin-bottom:16px}
input[name="tier"]:checked + div { border-color: var(--blue) !important; background: rgba(43,75,173,.03) !important; }
</style>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let debounceTimer;

function updateQuote() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    const val = parseFloat(document.getElementById('billAmount').value);
    if (!val || val < 1) { document.getElementById('quoteSection').style.display = 'none'; return; }

    fetch('{{ route("borrower.mybill.quote") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ bill_value: val })
    })
    .then(r => r.json())
    .then(q => {
      document.getElementById('q30upfront').textContent = 'M ' + q.standard.upfront.toFixed(2);
      document.getElementById('q30payday').textContent  = 'M ' + q.standard.payday_amount.toFixed(2);
      document.getElementById('q30fee').textContent     = 'M ' + q.standard.total_cost.toFixed(2);
      document.getElementById('q40payday').textContent  = 'M ' + q.no_upfront.payday_amount.toFixed(2);
      document.getElementById('q40fee').textContent     = 'M ' + q.no_upfront.total_cost.toFixed(2);
      document.getElementById('savingAmount').textContent = q.extra_if_no_upfront.toFixed(2);

      document.getElementById('quoteSection').style.display = 'block';
      document.getElementById('extraSaving').style.display  = q.extra_if_no_upfront > 0 ? 'block' : 'none';
    });
  }, 300);
}

// Tier card selection visual
document.querySelectorAll('input[name="tier"]').forEach(radio => {
  radio.addEventListener('change', () => {
    document.getElementById('tier30').style.borderColor = radio.value === '30' ? 'var(--blue)' : 'var(--border)';
    document.getElementById('tier30').style.background  = radio.value === '30' ? 'rgba(43,75,173,.03)' : '';
    document.getElementById('tier40').style.borderColor = radio.value === '40' ? 'var(--blue)' : 'var(--border)';
    document.getElementById('tier40').style.background  = radio.value === '40' ? 'rgba(43,75,173,.03)' : '';
  });
});

@if($category === 'electricity')
function lookupMeter() {
  const meter = document.getElementById('meterNumber').value;
  if (!meter) return;
  const el = document.getElementById('meterResult');
  el.style.display = 'block';
  el.innerHTML = '<span style="color:var(--blue)"><i class="bi bi-hourglass-split"></i> Verifying...</span>';

  fetch('{{ route("borrower.mybill.lookup-meter") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify({ meter_number: meter })
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) {
      const name = d.data?.return?.additionalData?.customerName || d.data?.return?.description || 'Valid meter';
      el.innerHTML = '<span style="color:#059669"><i class="bi bi-check-circle-fill"></i> ' + name + '</span>';
    } else {
      el.innerHTML = '<span style="color:#dc2626"><i class="bi bi-x-circle-fill"></i> ' + (d.error || 'Meter not found') + '</span>';
    }
  })
  .catch(() => { el.innerHTML = '<span style="color:#dc2626"><i class="bi bi-x-circle-fill"></i> Verification failed</span>'; });
}
@endif

@if($category === 'insurance')
function lookupInsurance() {
  const policy = document.getElementById('policyNumber').value;
  const partner = document.getElementById('insurancePartner').value;
  if (!policy || !partner) return alert('Please select a provider and enter policy number');
  const el = document.getElementById('insuranceResult');
  el.style.display = 'block';
  el.innerHTML = '<span style="color:var(--blue)"><i class="bi bi-hourglass-split"></i> Looking up...</span>';

  fetch('{{ route("borrower.mybill.lookup-insurance") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify({ policy_number: policy, partner_id: parseInt(partner) })
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) {
      const name = d.data?.memberName || 'Found';
      const amount = d.data?.policyAmount ? 'M' + parseFloat(d.data.policyAmount).toFixed(2) : '';
      el.innerHTML = '<span style="color:#059669"><i class="bi bi-check-circle-fill"></i> ' + name + (amount ? ' — Due: ' + amount : '') + '</span>';
      if (d.data?.policyAmount) document.getElementById('billAmount').value = d.data.policyAmount;
      updateQuote();
    } else {
      el.innerHTML = '<span style="color:#dc2626"><i class="bi bi-x-circle-fill"></i> ' + (d.error || 'Policy not found') + '</span>';
    }
  })
  .catch(() => { el.innerHTML = '<span style="color:#dc2626"><i class="bi bi-x-circle-fill"></i> Lookup failed</span>'; });
}
@endif
</script>
@endsection
