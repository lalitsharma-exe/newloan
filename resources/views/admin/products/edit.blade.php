@extends('admin.layouts.app')
@section('title','New Product')
@section('page-title','New Loan Product')
@section('content')
<div style="max-width:700px">
<div class="card">
    <div class="card-header"><span class="card-title">Create Loan Product</span></div>
    <form method="POST" action="{{ route('admin.products.store') }}">
        @csrf
        <div class="card-body">
            <div class="grid grid-2" style="gap:16px">
                <div class="form-group" style="grid-column:span 2"><label class="form-label">Product Name *</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
                <div class="form-group"><label class="form-label">Interest Rate (%/month) *</label><input type="number" name="interest_rate" class="form-control" value="{{ old('interest_rate') }}" step="0.01" required></div>
                <div class="form-group"><label class="form-label">Min Amount (L) *</label><input type="number" name="min_amount" class="form-control" value="{{ old('min_amount',500) }}" required></div>
                <div class="form-group"><label class="form-label">Max Amount (L) *</label><input type="number" name="max_amount" class="form-control" value="{{ old('max_amount') }}" required></div>
                <div class="form-group"><label class="form-label">Min Term (months) *</label><input type="number" name="min_term_months" class="form-control" value="{{ old('min_term_months',1) }}" required></div>
                <div class="form-group"><label class="form-label">Max Term (months) *</label><input type="number" name="max_term_months" class="form-control" value="{{ old('max_term_months',60) }}" required></div>
                <div class="form-group"><label class="form-label">Processing Fee</label><input type="number" name="processing_fee" class="form-control" value="{{ old('processing_fee',0) }}" step="0.01"></div>
                <div class="form-group"><label class="form-label">Fee Type</label><select name="processing_fee_type" class="form-control"><option value="percentage">Percentage (%)</option><option value="fixed">Fixed Amount</option></select></div>
                <div class="form-group"><label class="form-label">Late Payment Fee (L)</label><input type="number" name="late_payment_fee" class="form-control" value="{{ old('late_payment_fee',0) }}" step="0.01"></div>
                <div class="form-group"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:28px"><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
                <div class="form-group" style="grid-column:span 2"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea></div>
                <div class="form-group" style="grid-column:span 2"><label class="form-label">Eligibility Criteria</label><textarea name="eligibility_criteria" class="form-control" rows="3">{{ old('eligibility_criteria') }}</textarea></div>
            </div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;gap:10px;justify-content:flex-end">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Product</button>
        </div>
    </form>
</div>
</div>
@endsection
