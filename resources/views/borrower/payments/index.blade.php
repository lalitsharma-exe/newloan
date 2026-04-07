@extends('borrower.layouts.app')
@section('title', 'Payments')
@section('content')
  <style>
    /* Remove bullets and padding from pagination */
    .pagination {
      display: flex;
      padding-left: 0;
      list-style: none;
      border-radius: 0.25rem;
      margin: 0;
    }

    .page-item {
      display: inline;
    }

    .page-link {
      position: relative;
      display: block;
      padding: 0.5rem 0.75rem;
      margin-left: -1px;
      line-height: 1.25;
      color: var(--p, #007bff);
      /* Uses your primary color variable */
      background-color: #fff;
      border: 1px solid #dee2e6;
      text-decoration: none;
    }

    .page-item.active .page-link {
      z-index: 1;
      color: #fff;
      background-color: var(--p, #007bff);
      border-color: var(--p, #007bff);
    }

    .page-item.disabled .page-link {
      color: #6c757d;
      pointer-events: none;
      background-color: #fff;
      border-color: #dee2e6;
    }
  </style>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
    <div style="font-size:20px;font-weight:800">Payment History</div>
    <a href="{{ route('borrower.payments.make') }}" class="btn btn-p btn-sm"><i class="bi bi-cash"></i> Make Payment</a>
  </div>
  <div class="card">
    <div style="overflow-x:auto">
      <table class="dt">
        <thead>
          <tr>
            <th>Reference</th>
            <th>Loan</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($payments as $p)
            <tr>
              <td style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p)">{{ $p->payment_reference }}
              </td>
              <td style="font-size:12.5px;color:var(--muted)">{{ $p->loan?->loan_number }}</td>
              <td style="font-weight:700">M{{ number_format($p->amount, 2) }}</td>
              <td>{{ ucfirst(str_replace('_', ' ', $p->method)) }}</td>
              <td><span
                  class="badge {{ $p->status === 'verified' ? 'bok' : ($p->status === 'rejected' ? 'be' : 'bw') }}">{{ ucfirst($p->status) }}</span>
              </td>
              <td style="font-size:12px;color:var(--muted)">{{ $p->created_at->format('d M Y') }}</td>
              <td><a href="{{ route('borrower.payments.receipt', $p) }}" class="btn btn-xs btn-o">Receipt</a></td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No payments yet</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($payments->hasPages())
      <div style="padding:14px 20px;border-top:1px solid var(--border)">
    {{ $payments->links('pagination::simple-bootstrap-4') }}</div>@endif
  </div>
@endsection