@extends('admin.layouts.app')
@section('title','Loan Products')
@section('page-title','Loan Products')
@section('content')
<div class="card">
    <div class="card-header"><span class="card-title">Products ({{ $products->count() }})</span><a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Product</a></div>
    <div style="overflow-x:auto"><table class="data-table">
        <thead><tr><th>Name</th><th>Interest Rate</th><th>Min-Max Amount</th><th>Term</th><th>Processing Fee</th><th>Loans</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($products as $p)
        <tr>
            <td><div style="font-weight:700;font-size:14px">{{ $p->name }}</div></td>
            <td><span class="badge badge-primary">{{ $p->interest_rate }}%/mo</span></td>
            <td>L {{ number_format($p->min_amount,0) }} – L {{ number_format($p->max_amount,0) }}</td>
            <td>{{ $p->min_term_months }}–{{ $p->max_term_months }} months</td>
            <td>{{ $p->processing_fee }}{{ $p->processing_fee_type==='percentage'?'%':' LSL' }}</td>
            <td><span class="badge badge-secondary">{{ $p->loans_count }}</span></td>
            <td><span class="badge badge-{{ $p->is_active?'success':'danger' }}">{{ $p->is_active?'Active':'Inactive' }}</span></td>
            <td>
                <div style="display:flex;gap:5px">
                    <a href="{{ route('admin.products.edit',$p) }}" class="btn btn-xs btn-outline"><i class="bi bi-pencil"></i> Edit</a>
                    <form method="POST" action="{{ route('admin.products.destroy',$p) }}" onsubmit="return confirm('Delete product?')">@csrf@method('DELETE')<button class="btn btn-xs btn-danger"><i class="bi bi-trash"></i></button></form>
                </div>
            </td>
        </tr>
        @empty<tr><td colspan="8"><div class="empty-state"><i class="bi bi-box"></i><p>No products</p></div></td></tr>@endforelse
        </tbody>
    </table></div>
</div>
@endsection
