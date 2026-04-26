@extends('borrower.layouts.app')
@section('title','MyBill')
@section('content')

{{-- Flash messages --}}
@if(session('success'))<div class="alert a-ok mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert a-e mb-3"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>@endif

{{-- Credit Limit Hero --}}
<div style="background:linear-gradient(135deg,#0d1b3e 0%,#1e3370 50%,#2b4bad 100%);border-radius:20px;padding:32px 28px;margin-bottom:28px;color:#fff;position:relative;overflow:hidden">
  <div style="position:absolute;top:-40px;right:-40px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.04)"></div>
  <div style="position:absolute;bottom:-60px;right:60px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.03)"></div>
  <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
    <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;font-size:24px">
      <i class="bi bi-lightning-charge-fill" style="color:#f59e0b"></i>
    </div>
    <div>
      <div style="font-size:14px;opacity:.7;font-weight:500">MyBill Credit</div>
      <div style="font-size:28px;font-weight:800;letter-spacing:-0.5px">M {{ number_format($limit->available_amount, 2) }}</div>
    </div>
  </div>

  {{-- Limit bar --}}
  @php $pct = $limit->total_limit > 0 ? min(100, ($limit->used_amount / $limit->total_limit) * 100) : 0; @endphp
  <div style="background:rgba(255,255,255,.12);border-radius:8px;height:10px;overflow:hidden;margin-bottom:10px">
    <div style="background:linear-gradient(90deg,#8cc63f,#10b981);height:100%;width:{{ 100 - $pct }}%;border-radius:8px;transition:width .4s"></div>
  </div>
  <div style="display:flex;justify-content:space-between;font-size:12px;opacity:.6">
    <span>Used: M {{ number_format($limit->used_amount, 2) }}</span>
    <span>Limit: M {{ number_format($limit->total_limit, 2) }}</span>
  </div>
</div>

{{-- Quick Buy Cards --}}
<div style="margin-bottom:28px">
  <h3 style="font-size:17px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px">
    <i class="bi bi-grid-fill" style="color:var(--blue)"></i> Pay a Bill on Credit
  </h3>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
    @foreach([
      ['electricity', 'lightning-charge-fill', '#f59e0b', 'linear-gradient(135deg,#fef3c7,#fde68a)', '#92400e', 'Electricity', 'LEC prepaid tokens'],
      ['airtime', 'phone-fill', '#06b6d4', 'linear-gradient(135deg,#cffafe,#a5f3fc)', '#155e75', 'Airtime & Data', 'Vodacom, Econet recharge'],
      ['insurance', 'shield-fill', '#8b5cf6', 'linear-gradient(135deg,#ede9fe,#ddd6fe)', '#4c1d95', 'Insurance', 'Premium payments'],
      ['ticket', 'ticket-perforated-fill', '#ec4899', 'linear-gradient(135deg,#fce7f3,#fbcfe8)', '#831843', 'Event Tickets', 'Concerts & events'],
    ] as [$cat, $icon, $color, $bg, $textColor, $label, $desc])
    <a href="{{ route('borrower.mybill.purchase', $cat) }}" style="background:{{ $bg }};border-radius:16px;padding:20px;text-decoration:none;display:block;transition:all .3s;border:1px solid transparent;position:relative;overflow:hidden"
       onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 32px rgba(0,0,0,.1)'"
       onmouseout="this.style.transform='';this.style.boxShadow=''">
      <div style="width:44px;height:44px;border-radius:12px;background:{{ $color }};display:flex;align-items:center;justify-content:center;margin-bottom:14px;font-size:20px;color:#fff;box-shadow:0 4px 12px {{ $color }}40">
        <i class="bi bi-{{ $icon }}"></i>
      </div>
      <div style="font-size:15px;font-weight:700;color:{{ $textColor }};margin-bottom:4px">{{ $label }}</div>
      <div style="font-size:12px;color:{{ $textColor }};opacity:.7">{{ $desc }}</div>
      <i class="bi bi-arrow-right" style="position:absolute;bottom:18px;right:18px;font-size:18px;color:{{ $textColor }};opacity:.4"></i>
    </a>
    @endforeach
  </div>
</div>

{{-- How It Works --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:22px 24px;margin-bottom:28px">
  <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px">
    <i class="bi bi-info-circle-fill" style="color:var(--blue)"></i> How MyBill Works
  </h3>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px">
    @foreach([
      ['1', 'cart-fill', 'Choose a Bill', 'Select electricity, airtime, insurance, or tickets'],
      ['2', 'calculator-fill', 'See the Fee', 'Pay 10% upfront (30% fee) or nothing now (40% fee)'],
      ['3', 'send-fill', 'Bill Paid Instantly', 'We pay the provider — you get your token/voucher'],
      ['4', 'calendar-check-fill', 'Repay on Payday', 'Full amount deducted on your next salary day'],
    ] as [$num, $icon, $title, $text])
    <div style="text-align:center;padding:12px">
      <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--blue2));display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:800;margin:0 auto 10px">{{ $num }}</div>
      <div style="font-size:13px;font-weight:700;margin-bottom:4px">{{ $title }}</div>
      <div style="font-size:11.5px;color:var(--muted);line-height:1.5">{{ $text }}</div>
    </div>
    @endforeach
  </div>
</div>

{{-- Recent Transactions --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden">
  <div style="padding:16px 22px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border)">
    <span style="font-size:15px;font-weight:700"><i class="bi bi-clock-history" style="color:var(--blue)"></i> Recent Transactions</span>
    <a href="{{ route('borrower.mybill.history') }}" style="font-size:12px;color:var(--blue);text-decoration:none;font-weight:600">View All <i class="bi bi-arrow-right"></i></a>
  </div>

  @forelse($loans as $loan)
  <a href="{{ route('borrower.mybill.show', $loan) }}" style="display:flex;align-items:center;padding:14px 22px;border-bottom:1px solid #f1f5f9;text-decoration:none;color:inherit;transition:background .2s"
     onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
    <div style="width:40px;height:40px;border-radius:10px;background:{{ $loan->category_color }}15;display:flex;align-items:center;justify-content:center;margin-right:14px;flex-shrink:0">
      <i class="bi bi-{{ $loan->category_icon }}" style="color:{{ $loan->category_color }};font-size:18px"></i>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-weight:600;font-size:13.5px">{{ ucfirst($loan->bill_category) }}</div>
      <div style="font-size:11px;color:var(--muted)">{{ $loan->loan_number }} · {{ $loan->created_at->format('d M Y') }}</div>
    </div>
    <div style="text-align:right;flex-shrink:0">
      <div style="font-weight:700;font-size:14px">M {{ number_format($loan->bill_value, 2) }}</div>
      <span style="font-size:10px;padding:2px 8px;border-radius:6px;font-weight:600;background:{{ $loan->status === 'settled' ? '#ecfdf5' : ($loan->status === 'active' ? '#fffbeb' : '#fef2f2') }};color:{{ $loan->status === 'settled' ? '#059669' : ($loan->status === 'active' ? '#d97706' : '#dc2626') }}">
        {{ ucfirst($loan->status) }}
      </span>
    </div>
  </a>
  @empty
  <div style="text-align:center;padding:40px 20px;color:var(--muted)">
    <i class="bi bi-lightning-charge" style="font-size:36px;display:block;margin-bottom:10px;opacity:.3"></i>
    <div style="font-weight:600;margin-bottom:4px">No transactions yet</div>
    <div style="font-size:12px">Pay your first bill on credit to get started!</div>
  </div>
  @endforelse
</div>

<style>
.alert{padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px;margin-bottom:16px}
.a-ok{background:rgba(16,185,129,.08);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.a-e{background:rgba(239,68,68,.08);color:#991b1b;border:1px solid rgba(239,68,68,.2)}
.mb-3{margin-bottom:16px}
</style>
@endsection
