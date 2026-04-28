@extends('borrower.layouts.app')
@section('title','MyBill')
@section('content')

{{-- Flash messages --}}
@if(session('success'))<div class="alert a-ok mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert a-e mb-3"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>@endif

{{-- Credit Limit Hero (Mature Version) --}}
<div style="background:#0f172a;border-radius:24px;padding:40px;margin-bottom:32px;color:#fff;position:relative;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,0.1)">
  <div style="position:absolute;top:0;right:0;width:300px;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.03));"></div>
  <div style="display:flex;justify-content:space-between;align-items:flex-end">
    <div>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
        <div style="width:8px;height:8px;border-radius:50%;background:#10b981;box-shadow:0 0 12px #10b981"></div>
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:1px">Available Credit</div>
      </div>
      <div style="font-size:42px;font-weight:800;letter-spacing:-1px">M {{ number_format($limit->available_amount, 2) }}</div>
    </div>
    <div style="text-align:right">
      <div style="font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:4px">Total Limit</div>
      <div style="font-size:18px;font-weight:700">M {{ number_format($limit->total_limit, 2) }}</div>
    </div>
  </div>

  {{-- Limit progress bar --}}
  @php $pct = $limit->total_limit > 0 ? min(100, ($limit->used_amount / $limit->total_limit) * 100) : 0; @endphp
  <div style="margin-top:30px">
    <div style="background:rgba(255,255,255,0.1);border-radius:100px;height:8px;overflow:hidden">
      <div style="background:linear-gradient(90deg,#10b981,#34d399);height:100%;width:{{ 100 - $pct }}%;border-radius:100px;transition:width 1s cubic-bezier(0.4, 0, 0.2, 1)"></div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:12px;margin-top:12px;color:rgba(255,255,255,0.5)">
      <span>Used: M {{ number_format($limit->used_amount, 2) }}</span>
      <span>Remaining: {{ 100 - round($pct) }}%</span>
    </div>
  </div>
</div>

{{-- Quick Buy Cards (Mature & Branded) --}}
<div style="margin-bottom:32px">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h3 style="font-size:18px;font-weight:800;color:#1e293b;margin:0">Pay a Bill on Credit</h3>
    <span style="font-size:12px;color:var(--muted);font-weight:500">Select a category to start</span>
  </div>
  
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px">
    
    {{-- Electricity --}}
    <a href="{{ route('borrower.mybill.purchase', 'electricity') }}" class="bill-card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px">
        <div style="width:48px;height:48px;border-radius:14px;background:#f8fafc;border:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center">
          <i class="bi bi-lightning-charge-fill" style="color:#f59e0b;font-size:20px"></i>
        </div>
        <img src="/assets/logos/lec_logo.png" style="height:24px;opacity:0.9" onerror="this.src='https://placehold.co/80x30?text=LEC'">
      </div>
      <div style="font-size:16px;font-weight:800;color:#1e293b;margin-bottom:4px">Electricity</div>
      <div style="font-size:12.5px;color:var(--muted);line-height:1.5">Purchase LEC prepaid tokens instantly on credit.</div>
      <div class="card-footer">
        <span>Get Started</span>
        <i class="bi bi-chevron-right"></i>
      </div>
    </a>

    {{-- Airtime --}}
    <a href="{{ route('borrower.mybill.purchase', 'airtime') }}" class="bill-card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px">
        <div style="width:48px;height:48px;border-radius:14px;background:#f8fafc;border:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center">
          <i class="bi bi-phone-fill" style="color:#0ea5e9;font-size:20px"></i>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          <img src="/assets/logos/vodacom.png" style="height:18px" onerror="this.src='https://placehold.co/60x20?text=Vodacom'">
          <img src="/assets/logos/econet.png" style="height:18px" onerror="this.src='https://placehold.co/60x20?text=Econet'">
        </div>
      </div>
      <div style="font-size:16px;font-weight:800;color:#1e293b;margin-bottom:4px">Airtime & Data</div>
      <div style="font-size:12.5px;color:var(--muted);line-height:1.5">Top up Vodacom or Econet bundles anytime.</div>
      <div class="card-footer">
        <span>Get Started</span>
        <i class="bi bi-chevron-right"></i>
      </div>
    </a>

    {{-- Insurance --}}
    <a href="{{ route('borrower.mybill.purchase', 'insurance') }}" class="bill-card">
      <div style="width:48px;height:48px;border-radius:14px;background:#f8fafc;border:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;margin-bottom:20px">
        <i class="bi bi-shield-fill-check" style="color:#8b5cf6;font-size:20px"></i>
      </div>
      <div style="font-size:16px;font-weight:800;color:#1e293b;margin-bottom:4px">Insurance</div>
      <div style="font-size:12.5px;color:var(--muted);line-height:1.5">Pay your insurance premiums and stay covered.</div>
      <div class="card-footer">
        <span>Get Started</span>
        <i class="bi bi-chevron-right"></i>
      </div>
    </a>

    {{-- Tickets --}}
    <a href="{{ route('borrower.mybill.purchase', 'ticket') }}" class="bill-card">
      <div style="width:48px;height:48px;border-radius:14px;background:#f8fafc;border:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;margin-bottom:20px">
        <i class="bi bi-ticket-perforated-fill" style="color:#ec4899;font-size:20px"></i>
      </div>
      <div style="font-size:16px;font-weight:800;color:#1e293b;margin-bottom:4px">Event Tickets</div>
      <div style="font-size:12.5px;color:var(--muted);line-height:1.5">Book concerts and event tickets on credit.</div>
      <div class="card-footer">
        <span>Get Started</span>
        <i class="bi bi-chevron-right"></i>
      </div>
    </a>

  </div>
</div>

{{-- How It Works (Mature Section) --}}
<div style="background:#fff;border:1px solid #f1f5f9;border-radius:24px;padding:32px;margin-bottom:32px;box-shadow:0 4px 20px rgba(0,0,0,0.02)">
  <div style="text-align:center;max-width:500px;margin:0 auto 32px">
    <h3 style="font-size:18px;font-weight:800;color:#1e293b;margin-bottom:8px">How MyBill Works</h3>
    <p style="font-size:13px;color:var(--muted);line-height:1.6;margin:0">Get the essentials you need today and pay back automatically on your next payday.</p>
  </div>
  
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:24px">
    @foreach([
      ['01', 'Choose a Bill', 'Select electricity, airtime, or insurance.'],
      ['02', 'Flexible Fees', 'Choose to pay 10% upfront or nothing now.'],
      ['03', 'Instant Payment', 'We pay the provider and you get your tokens.'],
      ['04', 'Auto Repayment', 'Full amount is deducted on your next salary day.'],
    ] as [$num, $title, $text])
    <div style="position:relative">
      <div style="font-size:40px;font-weight:900;color:#f1f5f9;position:absolute;top:-10px;left:-5px;z-index:0;line-height:1">{{ $num }}</div>
      <div style="position:relative;z-index:1">
        <div style="font-size:14px;font-weight:800;color:#1e293b;margin-bottom:6px">{{ $title }}</div>
        <div style="font-size:12px;color:var(--muted);line-height:1.5">{{ $text }}</div>
      </div>
    </div>
    @endforeach
  </div>
</div>

{{-- Recent Transactions (Mature List) --}}
<div style="background:#fff;border:1px solid #f1f5f9;border-radius:20px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.03)">
  <div style="padding:24px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f1f5f9">
    <span style="font-size:16px;font-weight:800;color:#1e293b">Recent Purchases</span>
    <a href="{{ route('borrower.mybill.history') }}" style="font-size:13px;color:#3b82f6;text-decoration:none;font-weight:600">View History</a>
  </div>

  <div style="padding:8px">
    @forelse($loans as $loan)
    <a href="{{ route('borrower.mybill.show', $loan) }}" class="txn-item">
      <div class="txn-icon">
        <i class="bi bi-{{ $loan->category_icon }}"></i>
      </div>
      <div style="flex:1">
        <div style="font-weight:700;font-size:14px;color:#1e293b">{{ ucfirst($loan->bill_category) }}</div>
        <div style="font-size:12px;color:var(--muted)">{{ $loan->created_at->format('d M Y') }} · {{ $loan->loan_number }}</div>
      </div>
      <div style="text-align:right">
        <div style="font-weight:800;font-size:15px;color:#0f172a">M {{ number_format($loan->bill_value, 2) }}</div>
        <div style="font-size:10px;font-weight:700;color:{{ $loan->status === 'settled' ? '#10b981' : '#f59e0b' }};text-transform:uppercase;letter-spacing:0.5px">{{ $loan->status }}</div>
      </div>
    </a>
    @empty
    <div style="padding:60px 20px;text-align:center;color:var(--muted)">
      <div style="font-size:14px;font-weight:600">No transactions found</div>
      <div style="font-size:12px">Your recent bill payments will appear here.</div>
    </div>
    @endforelse
  </div>
</div>

<style>
.bill-card {
  background: #fff;
  border: 1px solid #f1f5f9;
  border-radius: 20px;
  padding: 24px;
  text-decoration: none;
  display: block;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}
.bill-card:hover {
  transform: translateY(-4px);
  border-color: #e2e8f0;
  box-shadow: 0 12px 30px rgba(0,0,0,0.06);
}
.card-footer {
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f8fafc;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  font-weight: 700;
  color: #3b82f6;
  opacity: 0;
  transform: translateX(-10px);
  transition: all 0.3s;
}
.bill-card:hover .card-footer {
  opacity: 1;
  transform: translateX(0);
}
.txn-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border-radius: 14px;
  text-decoration: none;
  transition: background 0.2s;
}
.txn-item:hover {
  background: #f8fafc;
}
.txn-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-size: 18px;
}
.alert{padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px;margin-bottom:16px}
.a-ok{background:rgba(16,185,129,.08);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.a-e{background:rgba(239,68,68,.08);color:#991b1b;border:1px solid rgba(239,68,68,.2)}
</style>

<style>
.alert{padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px;margin-bottom:16px}
.a-ok{background:rgba(16,185,129,.08);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.a-e{background:rgba(239,68,68,.08);color:#991b1b;border:1px solid rgba(239,68,68,.2)}
.mb-3{margin-bottom:16px}
</style>
@endsection
