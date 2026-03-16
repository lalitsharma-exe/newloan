@extends('admin.layouts.app')
@section('title','Loans')
@section('page-title','Loan Management')
@section('content')

{{-- Stat cards --}}
<div class="g4 mb6">
  <div class="sc"><div class="si ok"><i class="bi bi-check-circle-fill"></i></div><div><div class="sv">{{ $stats['total_active'] }}</div><div class="sl">Active</div></div></div>
  <div class="sc"><div class="si e"><i class="bi bi-exclamation-circle-fill"></i></div><div><div class="sv">{{ $stats['total_overdue'] }}</div><div class="sl">Overdue</div></div></div>
  <div class="sc"><div class="si p"><i class="bi bi-graph-up"></i></div><div><div class="sv">M{{ number_format($stats['total_portfolio'],0) }}</div><div class="sl">Portfolio</div></div></div>
  <div class="sc"><div class="si i"><i class="bi bi-trophy-fill"></i></div><div><div class="sv">{{ $stats['paid_off'] }}</div><div class="sl">Paid Off</div></div></div>
</div>

{{-- Quick action bar --}}
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px">
  <a href="{{ route('admin.loans.bulk-repayment') }}" class="btn btn-o"><i class="bi bi-collection"></i> Bulk Repayment</a>
  <a href="{{ route('admin.loans.import') }}" class="btn btn-o"><i class="bi bi-file-earmark-arrow-up"></i> Import Loans</a>
  <a href="{{ route('admin.loans.collection-sheet') }}" class="btn btn-o"><i class="bi bi-clipboard2-check"></i> Collection Sheet</a>
  <a href="{{ route('admin.loans.repayment-chart') }}" class="btn btn-o"><i class="bi bi-bar-chart-line"></i> Repayment Charts</a>
  <a href="{{ route('admin.loans.export', request()->query()) }}" class="btn btn-o"><i class="bi bi-download"></i> Export CSV</a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.loans.index') }}" class="filter-bar">
  <div class="fg" style="flex:2;min-width:180px">
    <label class="fl">Search</label>
    <input type="text" name="search" class="fc" placeholder="Loan#, borrower name…" value="{{ $filters['search'] ?? '' }}">
  </div>
  <div class="fg">
    <label class="fl">Status</label>
    <select name="status" class="fc">
      <option value="">All Statuses</option>
      @foreach(['active','overdue','paid_off','closed','defaulted'] as $s)
      <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
      @endforeach
    </select>
  </div>
  <div class="fg">
    <label class="fl">Product</label>
    <select name="product" class="fc">
      <option value="">All Products</option>
      @foreach($products as $p)
      <option value="{{ $p->id }}" {{ ($filters['product'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="fg">
    <label class="fl">From</label>
    <input type="date" name="date_from" class="fc" value="{{ $filters['date_from'] ?? '' }}">
  </div>
  <div class="fg">
    <label class="fl">To</label>
    <input type="date" name="date_to" class="fc" value="{{ $filters['date_to'] ?? '' }}">
  </div>
  <div class="flex gap2 aic" style="align-self:flex-end">
    <button type="submit" class="btn btn-p"><i class="bi bi-search"></i> Filter</button>
    <a href="{{ route('admin.loans.index') }}" class="btn btn-o">Clear</a>
  </div>
</form>

{{-- Table --}}
<div class="card">
  <div class="card-hdr">
    <span class="card-title">All Loans ({{ $loans->total() }})</span>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Loan #</th>
          <th>Borrower</th>
          <th>Product</th>
          <th>Principal</th>
          <th>Outstanding</th>
          <th>Monthly</th>
          <th>Next Due</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($loans as $l)
        @php
          $nextInst = $l->installments->whereIn('status', ['pending','overdue','partial'])->sortBy('due_date')->first();
          $nextDue  = $nextInst?->due_date?->format('d M Y') ?? '—';
          $isDue    = $nextInst && $nextInst->due_date->isPast();
        @endphp
        <tr>
          <td>
            <span style="font-weight:700;color:#4f46e5;font-size:12px">{{ $l->loan_number }}</span>
          </td>
          <td>
            <div class="flex aic gap2">
              <div class="av av-sm">{{ strtoupper(substr($l->user->name ?? 'U', 0, 1)) }}</div>
              <div>
                <div style="font-size:12.5px;font-weight:600">{{ $l->user->name ?? '—' }}</div>
                <div class="muted">{{ $l->user->phone ?? '' }}</div>
              </div>
            </div>
          </td>
          <td style="font-size:12.5px">{{ $l->loanProduct->name ?? '—' }}</td>
          <td><strong>M{{ number_format($l->principal_amount, 0) }}</strong></td>
          <td>
            <span style="font-weight:700;color:{{ $l->outstanding_balance > 0 ? '#ef4444' : '#10b981' }}">
              M{{ number_format($l->outstanding_balance, 0) }}
            </span>
          </td>
          <td style="font-size:12.5px">M{{ number_format($l->monthly_installment, 2) }}</td>
          <td>
            <span style="{{ $isDue ? 'color:#ef4444;font-weight:600' : '' }};font-size:12.5px">
              {{ $nextDue }}
            </span>
          </td>
          <td>
            <span class="badge {{ $l->status === 'active' ? 'bok' : ($l->status === 'overdue' ? 'be' : ($l->status === 'paid_off' ? 'bp' : 'bs')) }}">
              {{ ucfirst(str_replace('_', ' ', $l->status)) }}
            </span>
          </td>
          <td>
            <a href="{{ route('admin.loans.show', $l) }}" class="btn btn-sm btn-p">
              <i class="bi bi-eye"></i> View
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9">
            <div class="empty"><i class="bi bi-wallet2"></i><p>No loans found</p></div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($loans->hasPages())
  <div style="padding:14px 18px;border-top:1px solid #f1f5f9">
    {{ $loans->withQueryString()->links() }}
  </div>
  @endif
</div>

@endsection