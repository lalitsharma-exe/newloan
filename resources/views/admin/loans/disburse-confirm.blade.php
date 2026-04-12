@extends('admin.layouts.app')
@section('title','Disburse Loan '.$loan->loan_number)
@section('page-title','Disbursement Confirmation')
@section('bc')
<a href="{{ route('admin.loans.index') }}">Loans</a> /
<a href="{{ route('admin.loans.show',$loan) }}">{{ $loan->loan_number }}</a> /
Disburse
@endsection

@section('content')
@php
  $app      = $loan->application;
  $bank     = $app?->bankDetails;
  $allPass  = collect($checks)->every(fn($c) => $c['pass'] || !$c['required']);
  $blocked  = collect($checks)->contains(fn($c) => !$c['pass'] && $c['required']);
@endphp

{{-- Blocked Banner --}}
@if($blocked)
<div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:12px;padding:14px 18px;display:flex;align-items:center;gap:10px;margin-bottom:22px">
  <i class="bi bi-slash-circle-fill" style="color:#ef4444;font-size:20px;flex-shrink:0"></i>
  <div>
    <div style="font-weight:700;color:#991b1b;font-size:14px">Disbursement Blocked</div>
    <div style="color:#b91c1c;font-size:13px;margin-top:2px">One or more required pre-disbursement checks have failed. Resolve them before proceeding.</div>
  </div>
</div>
@else
<div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);border-radius:12px;padding:14px 18px;display:flex;align-items:center;gap:10px;margin-bottom:22px">
  <i class="bi bi-check-circle-fill" style="color:#10b981;font-size:20px;flex-shrink:0"></i>
  <div style="font-weight:700;color:#065f46;font-size:14px">All required checks passed. Ready to disburse.</div>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start">

  {{-- LEFT COLUMN --}}
  <div>

    {{-- 1. Borrower Details --}}
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title"><i class="bi bi-person-fill" style="color:var(--p)"></i> 1. Borrower Details</span></div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
          @foreach([
            'Borrower Name'  => $loan->user->name ?? '—',
            'ID Number'      => $loan->user->national_id ?? $app?->national_id ?? '—',
            'Phone Number'   => $loan->user->phone ?? $app?->cell_number ?? '—',
          ] as $l => $v)
          <div style="background:#f8fafc;border-radius:10px;padding:12px 14px">
            <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">{{ $l }}</div>
            <div style="font-size:14px;font-weight:700;color:var(--dark)">{{ $v }}</div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- 2. Loan Details --}}
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title"><i class="bi bi-bank" style="color:var(--p)"></i> 2. Loan Details</span></div>
      <div class="card-body">
        @php
          $product    = $loan->loanProduct;
          $principal  = (float)$loan->principal_amount;
          $rate       = (float)$loan->interest_rate;
          $term       = (int)$loan->term_months;
          $initFee    = round($principal * (($product?->initiation_fee_rate ?? 40)/100), 2);
          $adminTotal = ($product?->admin_fee_fixed ?? 50) * $term;
          $totalInt   = round($principal * ($rate/100) * $term, 2);
          $totalRepay = $loan->total_amount;
          $monthly    = $loan->monthly_installment;
        @endphp
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px">
          @foreach([
            ['Loan ID',            $loan->loan_number,                    'p'],
            ['Principal',          'M '.number_format($principal,2),       'ok'],
            ['Loan Term',          $term.' months',                        'i'],
            ['Monthly Instalment', 'M '.number_format($monthly,2),         'w'],
            ['Initiation Fee',     'M '.number_format($initFee,2),         's'],
            ['Total Interest',     'M '.number_format($totalInt,2),        's'],
            ['Admin Fees',         'M '.number_format($adminTotal,2),      's'],
            ['Total Repayment',    'M '.number_format($totalRepay,2),      'p'],
          ] as [$l,$v,$c])
          <div class="sc" style="padding:14px">
            <div><div style="font-size:16px;font-weight:800">{{ $v }}</div><div style="font-size:11px;color:var(--muted);margin-top:2px">{{ $l }}</div></div>
          </div>
          @endforeach
        </div>
        <div style="display:flex;gap:12px">
          <div style="background:#f8fafc;border-radius:10px;padding:10px 14px;flex:1;font-size:12.5px">
            <span style="color:var(--muted)">Status:</span>
            <strong style="margin-left:6px">{{ ucfirst(str_replace('_',' ',$loan->status)) }}</strong>
          </div>
          <div style="background:#f8fafc;border-radius:10px;padding:10px 14px;flex:1;font-size:12.5px">
            <span style="color:var(--muted)">Product:</span>
            <strong style="margin-left:6px">{{ $product?->name ?? '—' }}</strong>
          </div>
          <div style="background:#f8fafc;border-radius:10px;padding:10px 14px;flex:1;font-size:12.5px">
            <span style="color:var(--muted)">Interest Rate:</span>
            <strong style="margin-left:6px">{{ $rate }}% / month (flat)</strong>
          </div>
        </div>
      </div>
    </div>

    {{-- 5. Pre-Disbursement Checks --}}
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title"><i class="bi bi-shield-check" style="color:var(--p)"></i> 5. Pre-Disbursement Checks</span></div>
      <div class="card-body" style="padding:0">
        @foreach($checks as $check)
        <div style="display:flex;align-items:center;gap:14px;padding:14px 22px;border-bottom:1px solid var(--border)">
          <div style="width:34px;height:34px;border-radius:50%;background:{{ $check['pass'] ? 'rgba(16,185,129,.1)' : 'rgba(239,68,68,.1)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="bi bi-{{ $check['pass'] ? 'check-lg' : 'x-lg' }}" style="color:{{ $check['pass'] ? '#10b981' : '#ef4444' }};font-size:16px"></i>
          </div>
          <div style="flex:1">
            <div style="font-size:13.5px;font-weight:700;color:var(--dark)">
              {{ $check['label'] }}
              @if(!$check['required'])<span style="font-size:10.5px;color:var(--muted);font-weight:500;margin-left:6px">(warning only)</span>@endif
            </div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ $check['detail'] }}</div>
          </div>
          @if(!$check['pass'] && $check['required'])
          <span style="background:rgba(239,68,68,.1);color:#dc2626;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px">BLOCKED</span>
          @elseif(!$check['pass'])
          <span style="background:rgba(245,158,11,.1);color:#d97706;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px">WARNING</span>
          @else
          <span style="background:rgba(16,185,129,.1);color:#059669;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px">PASSED</span>
          @endif
        </div>
        @endforeach
      </div>
    </div>

  </div>

  {{-- RIGHT COLUMN — Disbursement Form --}}
  <div>

    {{-- 3. Disbursement Method --}}
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title"><i class="bi bi-send-fill" style="color:var(--p)"></i> 3. Disbursement Method</span></div>
      <div class="card-body">
        {{-- Bank Transfer info from bank details --}}
        @if($bank)
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 14px;margin-bottom:14px">
          <div style="font-size:11px;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">Bank Details on File</div>
          <div style="font-size:12.5px;color:var(--dark);display:grid;gap:4px">
            <div><span style="color:var(--muted)">Bank:</span> <strong>{{ $bank->bank_name ?? '—' }}</strong></div>
            <div><span style="color:var(--muted)">Account Name:</span> <strong>{{ $bank->account_holder_name ?? '—' }}</strong></div>
            <div><span style="color:var(--muted)">Account Number:</span> <strong>{{ $bank->account_number ?? '—' }}</strong></div>
          </div>
        </div>
        @endif
        
        @if($loan->user && $loan->user->encrypted_card_number)
        <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:10px;padding:12px 14px;margin-bottom:14px">
          <div style="font-size:11px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">Card Details on File</div>
          <div style="font-size:12.5px;color:var(--dark);display:grid;gap:4px">
            <div><span style="color:var(--muted)">Card Name:</span> <strong>{{ $loan->user->card_name ?? '—' }}</strong></div>
            <div><span style="color:var(--muted)">Card Number:</span> <strong>
              @php try { $cn = \Illuminate\Support\Facades\Crypt::decryptString($loan->user->encrypted_card_number); echo '•••• •••• •••• '.substr($cn,-4); } catch(\Exception $e) { echo '•••• •••• •••• '.($loan->user->card_last_four ?? '????'); } @endphp
            </strong></div>
            <div><span style="color:var(--muted)">Expiry Date:</span> <strong>{{ $loan->user->card_expiry ?? '—' }}</strong></div>
            <div><span style="color:var(--muted)">CVV:</span> <strong>•••</strong></div>
          </div>
        </div>
        @elseif($loan->application?->card_tokenised)
        <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:10px;padding:12px 14px;margin-bottom:14px">
          <div style="font-size:11px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">Card Details on File</div>
          <div style="font-size:12.5px;color:var(--dark);display:grid;gap:4px">
            <div><span style="color:var(--muted)">Card Setup:</span> <strong style="color:#10b981"><i class="bi bi-shield-check"></i> Securely Tokenised</strong></div>
            @if($loan->user?->card_last_four)
              <div><span style="color:var(--muted)">Card:</span> <strong>•••• {{ $loan->user->card_last_four }}</strong></div>
            @endif
          </div>
        </div>
        @endif

        <div style="display:grid;gap:8px;margin-bottom:14px">

          {{-- Bank Transfer --}}
          <label style="cursor:pointer">
            <input type="radio" name="_method_preview" value="bank_transfer" {{ ($loan->payout_method ?? 'bank_transfer') !== 'cpay_wallet' ? 'checked' : '' }} style="display:none" class="method-radio" onchange="switchMethod('bank_transfer')">
            <div class="method-card" data-m="bank_transfer" style="border:2px solid {{ ($loan->payout_method ?? 'bank_transfer') !== 'cpay_wallet' ? '#4f46e5;background:#4f46e511' : 'var(--border)' }};border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:14px;transition:all .2s">
              <div style="width:42px;height:42px;background:rgba(79,70,229,.12);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-bank2" style="font-size:20px;color:#4f46e5"></i>
              </div>
              <div>
                <div style="font-weight:700;font-size:14px;color:var(--dark)">Bank Transfer / EFT</div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px">Deposit directly to borrower's bank account</div>
              </div>
            </div>
          </label>

          {{-- CPay Wallet --}}
          <label style="cursor:pointer">
            <input type="radio" name="_method_preview" value="cpay_wallet" {{ ($loan->payout_method ?? '') === 'cpay_wallet' ? 'checked' : '' }} style="display:none" class="method-radio" onchange="switchMethod('cpay_wallet')">
            <div class="method-card" data-m="cpay_wallet" style="border:2px solid {{ ($loan->payout_method ?? '') === 'cpay_wallet' ? '#7c3aed;background:#7c3aed11' : 'var(--border)' }};border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:14px;transition:all .2s">
              <div style="width:42px;height:42px;background:rgba(124,58,237,.12);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="24" height="24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="50" cy="50" r="48" fill="#7c3aed"/>
                  <text x="50%" y="56%" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="40" font-weight="bold" font-family="Arial">C</text>
                </svg>
              </div>
              <div>
                <div style="font-weight:700;font-size:14px;color:var(--dark)">CPay Wallet</div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px">Deposit to borrower's Chaperone C-Pay wallet (KYC verified)</div>
              </div>
            </div>
          </label>
          {{-- M-Pesa Disbursement --}}
          @if($mpesaConfigured)
          <label style="cursor:pointer">
            <input type="radio" name="_method_preview" value="mpesa_b2c" style="display:none" class="method-radio" onchange="switchMethod('mpesa_b2c')">
            <div class="method-card" data-m="mpesa_b2c" style="border:2px solid var(--border);border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:14px;transition:all .2s">
              <div style="width:42px;height:42px;background:rgba(16,185,129,.12);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-phone-fill" style="font-size:20px;color:#10b981"></i>
              </div>
              <div>
                <div style="font-weight:700;font-size:14px;color:var(--dark)">M-Pesa (B2C)</div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px">Automated disbursement to borrower's phone via M-Pesa</div>
              </div>
            </div>
          </label>
          @endif

          {{-- Cash --}}
          <label style="cursor:pointer">
            <input type="radio" name="_method_preview" value="cash" style="display:none" class="method-radio" onchange="switchMethod('cash')">
            <div class="method-card" data-m="cash" style="border:2px solid var(--border);border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:14px;transition:all .2s">
              <div style="width:42px;height:42px;background:rgba(16,185,129,.12);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-cash-stack" style="font-size:20px;color:#10b981"></i>
              </div>
              <div>
                <div style="font-weight:700;font-size:14px;color:var(--dark)">Cash</div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px">Record manual cash disbursement (no API call)</div>
              </div>
            </div>
          </label>
        </div>

        {{-- Phone field for M-Pesa --}}
        <div id="mpesaFields" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 14px;margin-bottom:10px">
          <div class="fg" style="margin-bottom:0">
            <label class="fl">M-Pesa Phone Number</label>
            <input type="tel" id="mpesaPhone" class="fc" placeholder="e.g. 0712345678" value="{{ $loan->user->phone ?? '' }}">
            <div class="ft">The borrower's M-Pesa registered number</div>
          </div>
        </div>

        {{-- CPay Wallet — phone field --}}
        <div id="cpayWalletFields" style="display:none;background:#f5f3ff;border:1px solid #ede9fe;border-radius:10px;padding:12px 14px;margin-bottom:10px">
          <div class="fg" style="margin-bottom:0">
            <label class="fl">Borrower CPay Wallet Phone Number</label>
            <input type="tel" id="cpayWalletPhone" class="fc" placeholder="e.g. 58145851" value="{{ $loan->user->phone ?? '' }}">
            <div class="ft">8-digit local number registered with CPay wallet</div>
          </div>
        </div>
      </div>
    </div>

    {{-- 4. Amount & Reference --}}
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title"><i class="bi bi-currency-exchange" style="color:var(--p)"></i> 4. Amount & Reference</span></div>
      <div class="card-body">
        <div style="background:linear-gradient(135deg,var(--p),var(--pl));border-radius:12px;padding:18px;text-align:center;margin-bottom:14px">
          <div style="color:rgba(255,255,255,.7);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.08em">Amount to Disburse</div>
          <div style="color:#fff;font-size:32px;font-weight:900;margin-top:4px">M{{ number_format($loan->principal_amount, 2) }}</div>
        </div>
        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;display:flex;justify-content:space-between;align-items:center">
          <div>
            <div style="font-size:11px;color:var(--muted);font-weight:600">Payment Reference</div>
            <div style="font-size:15px;font-weight:800;color:var(--p);font-family:monospace;margin-top:2px" id="refDisplay">{{ $reference }}</div>
          </div>
          <i class="bi bi-clipboard" style="font-size:18px;color:var(--muted);cursor:pointer" onclick="navigator.clipboard.writeText('{{ $reference }}')"></i>
        </div>
      </div>
    </div>

    {{-- 6. Confirmation Form --}}
    <div class="card" style="border:2px solid {{ $blocked ? '#ef4444' : '#10b981' }}">
      <div class="card-hdr" style="background:{{ $blocked ? 'rgba(239,68,68,.05)' : 'rgba(16,185,129,.05)' }}">
        <span class="card-title"><i class="bi bi-{{ $blocked ? 'slash-circle' : 'check2-circle' }}" style="color:{{ $blocked ? '#ef4444' : '#10b981' }}"></i> 6. Confirm Disbursement</span>
      </div>
      <div class="card-body">
        @if($blocked)
          <div class="alert a-e" style="font-size:13px">
            <i class="bi bi-lock-fill"></i> Disbursement is blocked. Resolve the failed checks above before proceeding.
          </div>
          <a href="{{ route('admin.loans.show', $loan) }}" class="btn btn-o" style="width:100%;justify-content:center">← Back to Loan</a>
        @else
          <div class="alert" style="background:rgba(245,158,11,.08);color:#92400e;border:1px solid rgba(245,158,11,.2);font-size:13px;margin-bottom:18px">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Are you sure you want to disburse this loan?</strong><br>
            This action will activate the loan, create the repayment schedule, and cannot be undone.
          </div>

          <form method="POST" action="{{ route('admin.loans.disburse', $loan) }}" id="disburseForm">
            @csrf
            <input type="hidden" name="disbursement_reference" id="finalRef" value="{{ $reference }}">
            <input type="hidden" name="disbursement_method" id="finalMethod" value="{{ $loan->payout_method ?? 'bank_transfer' }}">
            <input type="hidden" name="disbursement_phone" id="finalPhone" value="">
            <input type="hidden" name="disbursement_provider" id="finalProvider" value="">

            <div class="fg">
              <label class="fl">Disbursement Date *</label>
              <input type="date" name="disbursement_date" class="fc" value="{{ today()->format('Y-m-d') }}" required>
            </div>

            <div class="fg">
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 14px;border:1.5px solid var(--border);border-radius:10px;background:#f8fafc">
                <input type="checkbox" name="confirm" value="1" id="confirmCheck" required style="width:18px;height:18px;cursor:pointer;accent-color:var(--p)">
                <span style="font-size:13px;font-weight:600;color:var(--dark)">I confirm this disbursement is correct and authorised</span>
              </label>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
              <a href="{{ route('admin.loans.show', $loan) }}" class="btn btn-o" style="justify-content:center">
                <i class="bi bi-x-lg"></i> Cancel
              </a>
              <button type="submit" class="btn btn-ok" id="disburseBtn" style="justify-content:center" {{ $blocked ? 'disabled' : '' }}>
                <i class="bi bi-send-fill"></i> Confirm Disbursement
              </button>
            </div>
          </form>
        @endif
      </div>
    </div>

  </div>
</div>

@push('scripts')
<script>
function switchMethod(val) {
    document.querySelectorAll('.method-card').forEach(c => {
        c.style.borderColor = 'var(--border)';
        c.style.background  = '';
    });
    const colors = { bank_transfer: '#4f46e5', cpay_wallet: '#7c3aed', cash: '#10b981' };
    const card = document.querySelector(`.method-card[data-m="${val}"]`);
    if (card && colors[val]) { card.style.borderColor = colors[val]; card.style.background = colors[val]+'18'; }
    // Show/hide extra fields
    const cpayF = document.getElementById('cpayWalletFields');
    if (cpayF) cpayF.style.display = val === 'cpay_wallet' ? '' : 'none';

    const mpesaF = document.getElementById('mpesaFields');
    if (mpesaF) mpesaF.style.display = val === 'mpesa_b2c' ? '' : 'none';

    document.getElementById('finalMethod').value = val;
    // Update provider hint
    const prov = document.getElementById('finalProvider');
    if (prov) {
        if (val === 'bank_transfer') prov.value = 'EFT';
        else if (val === 'cpay_wallet') prov.value = 'CPAY';
        else if (val === 'mpesa_b2c') prov.value = 'M-PESA';
        else prov.value = 'CASH';
    }
}

document.getElementById('disburseForm')?.addEventListener('submit', function(e) {
    const method = document.getElementById('finalMethod').value;
    const phoneEl = document.getElementById('cpayWalletPhone');
    const mpesaPhoneEl = document.getElementById('mpesaPhone');
    
    if (method === 'cpay_wallet' && phoneEl) {
        document.getElementById('finalPhone').value = phoneEl.value;
    } else if (method === 'mpesa_b2c' && mpesaPhoneEl) {
        document.getElementById('finalPhone').value = mpesaPhoneEl.value;
    } else {
        document.getElementById('finalPhone').value = '{{ $loan->user->phone ?? '' }}';
    }
});

// Init — default to bank_transfer unless loan.payout_method says cpay_wallet
const initMethod = '{{ ($loan->payout_method ?? 'bank_transfer') }}';
switchMethod(['bank_transfer','cpay_wallet','cash'].includes(initMethod) ? initMethod : 'bank_transfer');
</script>
@endpush
@endsection
