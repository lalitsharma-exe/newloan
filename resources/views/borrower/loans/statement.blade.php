@extends('borrower.layouts.app')
@section('title','Loan Statement — '.$loan->loan_number)

@section('content')

@push('styles')
<style>
/* ── Screen styles ──────────────────────────────────────── */
.stmt-wrap { max-width: 100%; }

.stmt-actions {
  display: flex; gap: 10px; justify-content: flex-end;
  margin-bottom: 20px;
}

/* Statement document */
#statementDoc {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(15,69,39,.07);
}

/* ── Header band ── */
.stmt-header {
  background: linear-gradient(135deg, #0f4527 0%, #22894e 60%, #1a6b3c 100%);
  padding: 28px 36px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
}
.stmt-logo img {
  height: 38px;
  width: auto;
  object-fit: contain;
  filter: brightness(0) invert(1);
  display: block;
}
.stmt-logo-text {
  font-size: 22px;
  font-weight: 800;
  color: #fff;
  letter-spacing: -.04em;
}
.stmt-logo-sub { font-size: 11px; color: rgba(255,255,255,.45); margin-top: 2px; letter-spacing: .08em; text-transform: uppercase; }

.stmt-header-right { text-align: right; }
.stmt-header-right .title { font-size: 20px; font-weight: 700; color: #fff; letter-spacing: -.02em; }
.stmt-header-right .loan-num {
  display: inline-block;
  margin-top: 6px;
  background: rgba(255,255,255,.1);
  border: 1px solid rgba(255,255,255,.15);
  border-radius: 6px;
  padding: 5px 14px;
  font-size: 13px;
  font-weight: 700;
  color: #e0c06a;
  letter-spacing: .05em;
  font-family: monospace;
}
.stmt-header-right .date { font-size: 12px; color: rgba(255,255,255,.5); margin-top: 6px; }

/* ── Status bar ── */
.stmt-status-bar {
  display: flex;
  align-items: center;
  gap: 0;
  border-bottom: 1px solid var(--border);
}
.stmt-stat {
  flex: 1;
  padding: 14px 20px;
  border-right: 1px solid var(--border);
  text-align: center;
}
.stmt-stat:last-child { border-right: none; }
.stmt-stat-val { font-size: 18px; font-weight: 800; color: #0f4527; line-height: 1; margin-bottom: 3px; }
.stmt-stat-lbl { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); }
.stmt-stat.green .stmt-stat-val { color: #059669; }
.stmt-stat.red   .stmt-stat-val { color: #dc2626; }
.stmt-stat.amber .stmt-stat-val { color: #d97706; }

/* ── Body ── */
.stmt-body { padding: 28px 36px; }

/* ── Info grid ── */
.stmt-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 20px;
  margin-bottom: 28px;
  padding-bottom: 24px;
  border-bottom: 1px solid var(--border);
}
.stmt-info-block-title {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .1em;
  color: var(--muted);
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.stmt-info-block-title i { color: #0f4527; font-size: 12px; }
.stmt-info-row {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
  font-size: 12.5px;
  border-bottom: 1px solid #f1f5f9;
}
.stmt-info-row .lbl { color: var(--muted); }
.stmt-info-row .val { font-weight: 600; color: #1c2433; }

/* ── Section headings ── */
.stmt-sec {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .1em;
  color: #0f4527;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.stmt-sec::after { content: ''; flex: 1; height: 1px; background: var(--border); }

/* ── Tables ── */
.stmt-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 28px;
  font-size: 12.5px;
}
.stmt-table th {
  padding: 9px 11px;
  text-align: left;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .07em;
  color: var(--muted);
  background: #f5f7fd;
  border-bottom: 2px solid var(--border);
  white-space: nowrap;
}
.stmt-table th.r,
.stmt-table td.r { text-align: right; }
.stmt-table td {
  padding: 9px 11px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
  color: #1c2433;
}
.stmt-table tbody tr:last-child td { border-bottom: none; }
.stmt-table tbody tr:hover td { background: #fafbff; }
.stmt-table tfoot td {
  padding: 11px;
  font-weight: 700;
  background: #f5f7fd;
  border-top: 2px solid var(--border);
}

/* ── Footer ── */
  .stmt-footer {
  margin-top: 8px;
  padding: 16px 36px;
  background: #f5f7fd;
  border-top: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 11px;
  color: var(--muted);
  border-radius: 0 0 14px 14px;
}

/* Authentication QR */
.stmt-auth-qr {
  text-align: right;
  margin-top: 20px;
  padding: 0 36px 20px;
}
.stmt-auth-qr img {
  width: 85px;
  height: 85px;
  border: 1px solid #f1f5f9;
  border-radius: 8px;
  display: inline-block;
}
.stmt-auth-qr .lbl {
  font-size: 10px;
  color: #94a3b8;
  margin-top: 4px;
  text-transform: uppercase;
  letter-spacing: .05em;
}

/* ── Print ──────────────────────────────────────────────── */
@media print {
  /* Hide everything except the statement doc */
  .stmt-actions,
  .topbar,
  aside,
  nav,
  .main > div > div:first-child,
  [class*="breadcrumb"],
  .btn,
  .topnav {
    display: none !important;
  }

  body, html {
    margin: 0 !important;
    padding: 0 !important;
    background: #fff !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
  }

  .stmt-wrap {
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  #statementDoc {
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    overflow: visible !important;
  }

  /* Keep navy header colors when printing */
  .stmt-header {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Ensure table fits on A4 */
  .stmt-table { font-size: 10px !important; }
  .stmt-table th, .stmt-table td { padding: 5px 6px !important; }
  .stmt-body { padding: 18px 20px !important; }
  .stmt-header { padding: 20px 20px !important; }

  /* Page breaks */
  .stmt-sec { page-break-after: avoid; }
  .stmt-table { page-break-inside: auto; }
  .stmt-table tr { page-break-inside: avoid; }
}

@page {
  size: A4 landscape;
  margin: 10mm;
}
</style>
@endpush

<div class="stmt-wrap">

  {{-- Action bar --}}
  <div class="stmt-actions no-print">
    <a href="{{ route('borrower.loans.show', $loan) }}" class="btn btn-o btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    <button onclick="window.print()" class="btn btn-o"><i class="bi bi-printer"></i> Print / Save PDF</button>
  </div>

  {{-- ═══════════ DOCUMENT ═══════════ --}}
  <div id="statementDoc">

    {{-- Header --}}
    <div class="stmt-header">
      <div class="stmt-logo">
        <img src="{{ config('app.logo') }}" alt="Prosperity Loans">
        <div style="margin-top:10px;font-size:11px;color:rgba(255,255,255,.4);line-height:1.6">
          L&amp;M Complex, Ha Thamae, Maseru<br>
          (+266) 58 478 799 · info@prosperityloans.co.ls
        </div>
      </div>
      <div class="stmt-header-right">
        <div class="title">Loan Statement</div>
        <div class="loan-num">{{ $loan->loan_number }}</div>
        <div class="date">Generated: {{ now()->format('d M Y, H:i') }}</div>
        <div style="margin-top:8px">
          @php
            $statusColors = ['active'=>'#22c55e','overdue'=>'#ef4444','closed'=>'#94a3b8','defaulted'=>'#dc2626','written_off'=>'#7c3aed'];
            $sc = $statusColors[$loan->status] ?? '#94a3b8';
          @endphp
          <span style="background:rgba(255,255,255,.1);border:1px solid {{ $sc }};color:{{ $sc }};font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;text-transform:uppercase;letter-spacing:.08em">
            {{ ucfirst(str_replace('_',' ',$loan->status)) }}
          </span>
        </div>
      </div>
    </div>

    {{-- Summary stat bar --}}
    @php
      $totalPaid = $loan->payments->where('status','verified')->sum('amount');
      $overdueCnt = $loan->installments->where('status','overdue')->count();
    @endphp
    <div class="stmt-status-bar">
      <div class="stmt-stat">
        <div class="stmt-stat-val">M{{ number_format($loan->principal_amount, 0) }}</div>
        <div class="stmt-stat-lbl">Principal</div>
      </div>
      <div class="stmt-stat green">
        <div class="stmt-stat-val">M{{ number_format($totalPaid, 0) }}</div>
        <div class="stmt-stat-lbl">Total Paid</div>
      </div>
      <div class="stmt-stat red">
        <div class="stmt-stat-val">M{{ number_format($loan->outstanding_balance, 0) }}</div>
        <div class="stmt-stat-lbl">Outstanding</div>
      </div>
      <div class="stmt-stat {{ $overdueCnt > 0 ? 'amber' : '' }}">
        <div class="stmt-stat-val">{{ $overdueCnt }}</div>
        <div class="stmt-stat-lbl">Overdue Instalments</div>
      </div>
      <div class="stmt-stat">
        <div class="stmt-stat-val">{{ $loan->term_months }}mo</div>
        <div class="stmt-stat-lbl">Term</div>
      </div>
    </div>

    {{-- Body --}}
    <div class="stmt-body">

      {{-- 3-column info grid --}}
      <div class="stmt-info-grid">

        {{-- Borrower --}}
        <div>
          <div class="stmt-info-block-title"><i class="bi bi-person-fill"></i> Borrower</div>
          <div class="stmt-info-row"><span class="lbl">Full Name</span><span class="val">{{ $loan->user?->name ?? 'Deleted User' }}</span></div>
          <div class="stmt-info-row"><span class="lbl">Phone</span><span class="val">{{ $loan->user?->phone ?? '—' }}</span></div>
          <div class="stmt-info-row"><span class="lbl">Email</span><span class="val" style="font-size:11.5px">{{ $loan->user?->email ?? '—' }}</span></div>
          <div class="stmt-info-row"><span class="lbl">ID Number</span><span class="val">{{ $loan->user?->national_id ?? '—' }}</span></div>
          @if($loan->application?->residential_address)
          <div class="stmt-info-row"><span class="lbl">Address</span><span class="val" style="font-size:11.5px;text-align:right;max-width:140px">{{ $loan->application->residential_address }}{{ $loan->application->district ? ', '.$loan->application->district : '' }}</span></div>
          @endif
        </div>

        {{-- Loan details --}}
        <div>
          <div class="stmt-info-block-title"><i class="bi bi-bank2"></i> Loan Details</div>
          <div class="stmt-info-row"><span class="lbl">Product</span><span class="val">{{ $loan->loanProduct?->name ?? '—' }}</span></div>
          <div class="stmt-info-row"><span class="lbl">Interest Rate</span><span class="val">{{ $loan->interest_rate }}%/mo (flat)</span></div>
          <div class="stmt-info-row"><span class="lbl">Monthly Payment</span><span class="val">M{{ number_format($loan->monthly_installment, 2) }}</span></div>
          <div class="stmt-info-row"><span class="lbl">Disbursed</span><span class="val">{{ $loan->disbursement_date?->format('d M Y') ?? '—' }}</span></div>
          <div class="stmt-info-row"><span class="lbl">Final Payment</span><span class="val">{{ $loan->installments->last()?->due_date?->format('d M Y') ?? '—' }}</span></div>
        </div>

        {{-- Fee summary --}}
        <div>
          <div class="stmt-info-block-title"><i class="bi bi-receipt"></i> Fee Summary</div>
          <div class="stmt-info-row"><span class="lbl">Principal</span><span class="val">M{{ number_format($loan->principal_amount, 2) }}</span></div>
          <div class="stmt-info-row"><span class="lbl">Admin Fee Total</span><span class="val">M{{ number_format($loan->installments->sum('admin_fee_amount'), 2) }}</span></div>
          <div class="stmt-info-row"><span class="lbl">Initiation Fee</span><span class="val">M{{ number_format($loan->installments->sum('initiation_fee_amount'), 2) }}</span></div>
          <div class="stmt-info-row"><span class="lbl">Total Interest</span><span class="val">M{{ number_format($loan->installments->sum('interest_amount'), 2) }}</span></div>
          <div class="stmt-info-row" style="border-bottom:none;padding-top:6px">
            <span class="lbl" style="font-weight:700;color:#1c2433">Total Cost</span>
            <span class="val" style="color:#0f4527;font-size:14px">M{{ number_format($loan->total_repayable ?? $loan->installments->sum('total_amount'), 2) }}</span>
          </div>
        </div>

      </div>

      {{-- Payment history --}}
      <div class="stmt-sec"><i class="bi bi-check-circle-fill" style="color:#059669"></i> Payment History</div>
      <table class="stmt-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Reference</th>
            <th>Method</th>
            <th class="r">Amount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($loan->payments->where('status','verified') as $p)
          <tr>
            <td style="color:var(--muted)">{{ $p->created_at->format('d M Y') }}</td>
            <td style="font-family:monospace;font-size:11.5px;color:#0f4527;font-weight:600">{{ $p->payment_reference }}</td>
            <td style="color:var(--muted)">{{ ucfirst(str_replace('_',' ',$p->method)) }}</td>
            <td class="r" style="font-weight:700;color:#059669">M{{ number_format($p->amount, 2) }}</td>
            <td><span style="background:#d1fae5;color:#065f46;font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:20px">Verified</span></td>
          </tr>
          @empty
          <tr><td colspan="5" style="padding:24px;text-align:center;color:var(--muted)"><i class="bi bi-clock" style="margin-right:6px"></i>No payments recorded yet</td></tr>
          @endforelse
        </tbody>
        @if($loan->payments->where('status','verified')->count())
        <tfoot>
          <tr>
            <td colspan="3" style="font-weight:700;color:#1c2433">Total Paid</td>
            <td class="r" style="font-size:15px;color:#059669">M{{ number_format($totalPaid, 2) }}</td>
            <td></td>
          </tr>
        </tfoot>
        @endif
      </table>

      {{-- Repayment schedule --}}
      <div class="stmt-sec" style="margin-top:4px"><i class="bi bi-table" style="color:#0f4527"></i> Repayment Schedule</div>
      <table class="stmt-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Due Date</th>
            <th class="r">Principal</th>
            <th class="r">Interest</th>
            <th class="r">Initiation</th>
            <th class="r">Admin Fee</th>
            <th class="r">Total</th>
            <th class="r">Paid</th>
            <th class="r">Penalty</th>
            <th class="r">Outstanding</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($loan->installments as $inst)
          @php
            $sc = ['paid'=>['#059669','#d1fae5'],'partial'=>['#d97706','#fef3c7'],'overdue'=>['#dc2626','#fee2e2'],'pending'=>['#475569','#f1f5f9'],'waived'=>['#7c3aed','#ede9fe']];
            [$tc,$bc] = $sc[$inst->status] ?? ['#475569','#f1f5f9'];
          @endphp
          <tr>
            <td style="font-weight:700;color:var(--muted)">{{ $inst->installment_number }}</td>
            <td style="white-space:nowrap;color:var(--muted)">{{ $inst->due_date->format('d M Y') }}</td>
            <td class="r" style="color:#0f4527">M{{ number_format($inst->principal_amount, 2) }}</td>
            <td class="r" style="color:#d97706">M{{ number_format($inst->interest_amount, 2) }}</td>
            <td class="r" style="color:#7c3aed">M{{ number_format($inst->initiation_fee_amount ?? 0, 2) }}</td>
            <td class="r" style="color:#475569">M{{ number_format($inst->admin_fee_amount ?? 0, 2) }}</td>
            <td class="r" style="font-weight:700">M{{ number_format($inst->total_amount, 2) }}</td>
            <td class="r" style="color:#059669">M{{ number_format($inst->paid_amount, 2) }}</td>
            <td class="r" style="color:#ef4444">{{ $inst->late_fee > 0 ? 'M'.number_format($inst->late_fee, 2) : '—' }}</td>
            <td class="r" style="font-weight:600">M{{ number_format($inst->outstanding_amount, 2) }}</td>
            <td><span style="background:{{ $bc }};color:{{ $tc }};font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap">{{ ucfirst($inst->status) }}</span></td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="2" style="font-weight:700;color:#1c2433">Totals</td>
            <td class="r" style="color:#0f4527">M{{ number_format($loan->installments->sum('principal_amount'), 2) }}</td>
            <td class="r" style="color:#d97706">M{{ number_format($loan->installments->sum('interest_amount'), 2) }}</td>
            <td class="r" style="color:#7c3aed">M{{ number_format($loan->installments->sum('initiation_fee_amount'), 2) }}</td>
            <td class="r" style="color:#475569">M{{ number_format($loan->installments->sum('admin_fee_amount'), 2) }}</td>
            <td class="r" style="font-weight:800;color:#0f4527">M{{ number_format($loan->installments->sum('total_amount'), 2) }}</td>
            <td class="r" style="color:#059669">M{{ number_format($loan->installments->sum('paid_amount'), 2) }}</td>
            <td class="r" style="color:#ef4444">M{{ number_format($loan->installments->sum('late_fee'), 2) }}</td>
            <td class="r" style="font-weight:800;color:#dc2626">M{{ number_format($loan->installments->sum('outstanding_amount'), 2) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>

    </div>

    {{-- Authentication: replaced stamp with system QR code --}}
    @php $qr = \App\Models\SystemSetting::get('system_qr'); @endphp
    @if($qr)
    <div class="stmt-auth-qr">
      <img src="{{ asset('storage/' . $qr) }}" alt="Verified">
      <div class="lbl">Document Verified</div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="stmt-footer">
      <span>Prosperity Loans Limited · CBL Licensed · L&amp;M Complex, Ha Thamae, Maseru</span>
      <span style="font-family:monospace">{{ $loan->loan_number }} · {{ now()->format('d M Y') }}</span>
    </div>

  </div>
</div>

@endsection
