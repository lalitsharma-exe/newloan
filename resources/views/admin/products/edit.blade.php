@extends('admin.layouts.app')
@section('title','Edit Product')
@section('page-title','Edit Loan Product')
@section('content')
<div style="max-width:760px">
<div class="card">
    <div class="card-header"><span class="card-title">Edit: {{ $product->name }}</span></div>
    <form method="POST" action="{{ route('admin.products.update', $product) }}">
        @csrf @method('PUT')
        <div class="card-body">
            @if($errors->any())
            <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px">
                <strong>Please fix the errors below:</strong>
                <ul style="margin:6px 0 0 18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif
            <div class="grid grid-2" style="gap:16px">
                <div class="form-group" style="grid-column:span 2">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name',$product->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Interest Method *</label>
                    <select name="interest_method" class="form-control" required>
                        <option value="flat"     {{ old('interest_method',$product->interest_method??'flat')==='flat'    ?'selected':'' }}>Flat (on original principal)</option>
                        <option value="reducing" {{ old('interest_method',$product->interest_method)==='reducing'        ?'selected':'' }}>Reducing Balance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Interest Rate (% per month) *</label>
                    <input type="number" name="interest_rate" class="form-control" value="{{ old('interest_rate',$product->interest_rate) }}" step="0.01" min="0" max="100" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Initiation Fee (% of principal) *</label>
                    <input type="number" name="initiation_fee_rate" class="form-control" value="{{ old('initiation_fee_rate',$product->initiation_fee_rate??40) }}" step="0.01" min="0" max="100" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Admin Fee (M per month, fixed) *</label>
                    <input type="number" name="admin_fee_fixed" class="form-control" value="{{ old('admin_fee_fixed',$product->admin_fee_fixed??50) }}" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Late Penalty (M per 10 days overdue)</label>
                    <input type="number" name="late_payment_fee" class="form-control" value="{{ old('late_payment_fee',$product->late_payment_fee??20) }}" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Min Amount (M) *</label>
                    <input type="number" name="min_amount" class="form-control" value="{{ old('min_amount',$product->min_amount) }}" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Amount (M) *</label>
                    <input type="number" name="max_amount" class="form-control" value="{{ old('max_amount',$product->max_amount) }}" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Min Term (months) *</label>
                    <input type="number" name="min_term_months" class="form-control" value="{{ old('min_term_months',$product->min_term_months) }}" min="1" max="24" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Term (months, max 24) *</label>
                    <input type="number" name="max_term_months" class="form-control" value="{{ old('max_term_months',$product->max_term_months) }}" min="1" max="24" required>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:10px;margin-top:24px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active',$product->is_active) ? 'checked':'' }}>
                        <span class="form-label" style="margin:0">Active</span>
                    </label>
                </div>
                <div class="form-group" style="grid-column:span 2">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description',$product->description) }}</textarea>
                </div>
                <div class="form-group" style="grid-column:span 2">
                    <label class="form-label">Eligibility Criteria</label>
                    <textarea name="eligibility_criteria" class="form-control" rows="2">{{ old('eligibility_criteria',$product->eligibility_criteria) }}</textarea>
                </div>
            </div>
            <div id="fee-preview" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;margin-top:16px">
                <div style="font-weight:700;color:#065f46;margin-bottom:8px;font-size:13px"><i class="bi bi-calculator"></i> Live Fee Preview (M1,000 example)</div>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;font-size:13px" id="fee-grid"></div>
            </div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;gap:10px;justify-content:flex-end">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Product</button>
        </div>
    </form>
</div>
</div>
<script>
function updatePreview(){
    const rate=parseFloat(document.querySelector('[name=interest_rate]').value)||0;
    const initPct=parseFloat(document.querySelector('[name=initiation_fee_rate]').value)||0;
    const admin=parseFloat(document.querySelector('[name=admin_fee_fixed]').value)||0;
    const term=parseInt(document.querySelector('[name=max_term_months]').value)||1;
    const p=1000;
    const interest=p*(rate/100)*term;
    const initiation=p*(initPct/100);
    const total=p+interest+initiation+(admin*term);
    const monthly=(total/term).toFixed(2);
    document.getElementById('fee-grid').innerHTML=`
        <div style="background:#fff;padding:10px;border-radius:8px;text-align:center"><div style="font-size:11px;color:#6b7280">Monthly Installment</div><div style="font-weight:800;color:#059669;font-size:18px">M${monthly}</div></div>
        <div style="background:#fff;padding:10px;border-radius:8px;text-align:center"><div style="font-size:11px;color:#6b7280">Total Interest</div><div style="font-weight:700;font-size:15px">M${interest.toFixed(2)}</div></div>
        <div style="background:#fff;padding:10px;border-radius:8px;text-align:center"><div style="font-size:11px;color:#6b7280">Initiation Fee</div><div style="font-weight:700;font-size:15px">M${initiation.toFixed(2)}</div></div>
        <div style="background:#fff;padding:10px;border-radius:8px;text-align:center"><div style="font-size:11px;color:#6b7280">Total Repayment</div><div style="font-weight:800;color:#1e3a5f;font-size:15px">M${total.toFixed(2)}</div></div>
    `;
}
['interest_rate','initiation_fee_rate','admin_fee_fixed','max_term_months'].forEach(n=>{
    document.querySelector(`[name=${n}]`)?.addEventListener('input',updatePreview);
});
updatePreview();
</script>
@endsection
