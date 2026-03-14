@extends('admin.layouts.app')
@section('title','Loan Products')
@section('page-title','Loan Products')
@section('content')

@if(session('success'))<div class="alert a-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
@if(session('error'))<div class="alert a-e"><i class="bi bi-x-circle-fill"></i> {{ session('error') }}</div>@endif

{{-- Stat cards --}}
<div class="g4 mb6">
  @php
    $active   = $products->where('is_active',true)->count();
    $inactive = $products->where('is_active',false)->count();
    $totalLoans = $products->sum('loans_count');
  @endphp
  <div class="sc"><div class="si p"><i class="bi bi-box-fill"></i></div><div><div class="sv">{{ $products->count() }}</div><div class="sl">Total Products</div></div></div>
  <div class="sc"><div class="si ok"><i class="bi bi-check-circle-fill"></i></div><div><div class="sv">{{ $active }}</div><div class="sl">Active</div></div></div>
  <div class="sc"><div class="si s"><i class="bi bi-pause-circle-fill"></i></div><div><div class="sv">{{ $inactive }}</div><div class="sl">Inactive</div></div></div>
  <div class="sc"><div class="si i"><i class="bi bi-currency-dollar"></i></div><div><div class="sv">{{ $totalLoans }}</div><div class="sl">Total Loans</div></div></div>
</div>

<div class="card">
  <div class="card-hdr">
    <span class="card-title">Loan Products ({{ $products->count() }})</span>
    <a href="{{ route('admin.products.create') }}" class="btn btn-p btn-sm"><i class="bi bi-plus-lg"></i> Add Product</a>
  </div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Product Name</th>
          <th>Interest</th>
          <th>Initiation Fee</th>
          <th>Admin Fee</th>
          <th>Amount Range</th>
          <th>Term</th>
          <th>Penalty</th>
          <th>Loans</th>
          <th>Status</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
      @forelse($products as $p)
      <tr>
        <td>
          <div style="font-weight:700;font-size:13.5px">{{ $p->name }}</div>
          @if($p->description)
          <div class="muted" style="margin-top:2px">{{ Str::limit($p->description,60) }}</div>
          @endif
        </td>
        <td><span class="badge bp">{{ $p->interest_rate }}%/mo flat</span></td>
        <td style="font-size:13px">{{ $p->initiation_fee_rate ?? 40 }}%</td>
        <td style="font-size:13px">M{{ number_format($p->admin_fee_fixed ?? 50, 2) }}/mo</td>
        <td style="font-size:13px">M{{ number_format($p->min_amount,0) }} – M{{ number_format($p->max_amount,0) }}</td>
        <td style="font-size:13px">{{ $p->min_term_months }}–{{ $p->max_term_months }} mo</td>
        <td style="font-size:13px">M{{ number_format($p->late_payment_fee ?? 20, 2) }}/10d</td>
        <td><span class="badge bs">{{ $p->loans_count }}</span></td>
        <td>
          <span class="badge {{ $p->is_active ? 'bok' : 'bs' }}">
            {{ $p->is_active ? 'Active' : 'Inactive' }}
          </span>
        </td>
        <td>
          <div style="display:flex;gap:5px;justify-content:flex-end">
            <a href="{{ route('admin.products.show', $p) }}" class="btn btn-xs btn-o"><i class="bi bi-eye"></i></a>
            <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-xs btn-o"><i class="bi bi-pencil"></i> Edit</a>
            <form method="POST" action="{{ route('admin.products.toggle', $p) }}" style="margin:0">
              @csrf
              <button class="btn btn-xs {{ $p->is_active ? 'btn-w' : 'btn-ok' }}" title="{{ $p->is_active ? 'Deactivate' : 'Activate' }}">
                <i class="bi bi-{{ $p->is_active ? 'pause' : 'play' }}-circle"></i>
              </button>
            </form>
            @if($p->loans_count == 0)
            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" onsubmit="return confirm('Delete {{ $p->name }}?')" style="margin:0">
              @csrf @method('DELETE')
              <button class="btn btn-xs btn-e"><i class="bi bi-trash"></i></button>
            </form>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="10"><div class="empty"><i class="bi bi-box"></i><p>No products yet. <a href="{{ route('admin.products.create') }}">Add one</a></p></div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
