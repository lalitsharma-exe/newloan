@extends('admin.layouts.app')
@section('title',$product->name)
@section('page-title',$product->name)
@section('bc','<a href="'.route('admin.products.index').'">Products</a> / '.$product->name)
@section('content')

<div style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start">

  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card">
      <div class="card-hdr">
        <span class="card-title">{{ $product->name }}</span>
        <span class="badge {{ $product->is_active?'bok':'bs' }}">{{ $product->is_active?'Active':'Inactive' }}</span>
      </div>
      <div style="padding:16px 18px">
        @foreach([
          'Interest Rate'    => $product->interest_rate.'% / month (flat)',
          'Initiation Fee'   => $product->initiation_fee_rate.'% of principal',
          'Admin Fee'        => 'M'.number_format($product->admin_fee_fixed,2).' / month',
          'Late Penalty'     => 'M'.number_format($product->late_payment_fee,2).' per 10 days',
          'Loan Range'       => 'M'.number_format($product->min_amount,0).' – M'.number_format($product->max_amount,0),
          'Term Range'       => $product->min_term_months.'–'.$product->max_term_months.' months',
        ] as $label => $val)
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px">
          <span style="color:var(--muted)">{{ $label }}</span>
          <span style="font-weight:600">{{ $val }}</span>
        </div>
        @endforeach
        @if($product->description)
        <div style="margin-top:12px;font-size:13px;color:#374151;line-height:1.6">{{ $product->description }}</div>
        @endif
      </div>
      <div style="padding:14px 18px;border-top:1px solid var(--border);display:flex;gap:8px">
        <a href="{{ route('admin.products.edit',$product) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
        <form method="POST" action="{{ route('admin.products.toggle',$product) }}">
          @csrf
          <button type="submit" class="btn btn-sm {{ $product->is_active?'btn-w':'btn-ok' }}">
            <i class="bi bi-{{ $product->is_active?'pause':'play' }}-circle"></i> {{ $product->is_active?'Deactivate':'Activate' }}
          </button>
        </form>
      </div>
    </div>

    {{-- Example calculation for M1,000 / 3 months --}}
    <div class="card">
      <div class="card-hdr"><span class="card-title"><i class="bi bi-calculator"></i> Example Calculation</span></div>
      <div style="padding:14px 18px;font-size:13px">
        @php
          $ex_p    = 1000;
          $ex_t    = 3;
          $ex_int  = $ex_p * ($product->interest_rate/100) * $ex_t;
          $ex_init = $ex_p * ($product->initiation_fee_rate/100);
          $ex_adm  = $product->admin_fee_fixed * $ex_t;
          $ex_tot  = $ex_p + $ex_int + $ex_init + $ex_adm;
          $ex_mo   = $ex_tot / $ex_t;
        @endphp
        <div style="background:#f8fafc;border-radius:8px;padding:12px;margin-bottom:10px;font-size:12px;color:var(--muted);text-align:center">M1,000 principal · 3 months</div>
        @foreach([
          'Interest'       => 'M'.number_format($ex_int,2),
          'Initiation Fee' => 'M'.number_format($ex_init,2),
          'Admin Fees'     => 'M'.number_format($ex_adm,2),
          'Total Repay'    => 'M'.number_format($ex_tot,2),
          'Monthly'        => 'M'.number_format($ex_mo,2),
        ] as $l => $v)
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border){{ $l==='Monthly'?';font-weight:700;color:#059669':'' }}">
          <span style="color:var(--muted)">{{ $l }}</span><span style="font-weight:600">{{ $v }}</span>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-hdr"><span class="card-title">Recent Loans ({{ $product->loans_count }})</span></div>
    <div style="overflow-x:auto">
      <table class="dt">
        <thead><tr><th>Loan #</th><th>Borrower</th><th>Principal</th><th>Outstanding</th><th>Status</th><th>Disbursed</th></tr></thead>
        <tbody>
          @forelse($product->loans as $l)
          <tr>
            <td><a href="{{ route('admin.loans.show',$l) }}" style="color:#4f46e5;font-weight:700;font-size:12px">{{ $l->loan_number }}</a></td>
            <td style="font-size:13px">{{ $l->user->name ?? '—' }}</td>
            <td>M{{ number_format($l->principal_amount,0) }}</td>
            <td style="color:{{ $l->outstanding_balance>0?'#ef4444':'#10b981' }}">M{{ number_format($l->outstanding_balance,0) }}</td>
            <td><span class="badge {{ $l->status==='active'?'bok':($l->status==='overdue'?'be':'bs') }}">{{ ucfirst(str_replace('_',' ',$l->status)) }}</span></td>
            <td style="font-size:12px;color:var(--muted)">{{ $l->disbursement_date?->format('d M Y') ?? '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="6"><div class="empty"><i class="bi bi-wallet2"></i><p>No loans yet</p></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div style="padding:14px 18px;border-top:1px solid var(--border)">
      <a href="{{ route('admin.loans.index',['product'=>$product->id]) }}" class="btn btn-outline btn-sm">View All Loans for this Product</a>
    </div>
  </div>

</div>
@endsection
