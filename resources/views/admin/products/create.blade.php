@extends('admin.layouts.app')
@section('title','New Loan Product')
@section('page-title','New Loan Product')
@section('bc')
<a href="{{ route('admin.products.index') }}">Products</a> / Create
@endsection
@section('content')
<div style="max-width:760px">
<div class="card">
  <div class="card-hdr"><span class="card-title">Create Loan Product</span></div>
  <form method="POST" action="{{ route('admin.products.store') }}">
    @csrf
    <div class="card-body">

      @if($errors->any())
      <div class="alert a-e" style="margin-bottom:16px">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <ul style="margin:0;padding-left:16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
      @endif

      <div class="g2" style="gap:16px">

        <div class="fg" style="grid-column:span 2">
          <label class="fl">Product Name *</label>
          <input type="text" name="name" class="fc" value="{{ old('name') }}" required>
        </div>

        <div class="fg">
          <label class="fl">Interest Method *</label>
          <select name="interest_method" class="fc" required>
            <option value="flat"     {{ old('interest_method','flat')==='flat'    ?'selected':'' }}>Flat (on original principal)</option>
            <option value="reducing" {{ old('interest_method')==='reducing'       ?'selected':'' }}>Reducing Balance</option>
          </select>
        </div>

        <div class="fg">
          <label class="fl">Interest Rate (% per month) *</label>
          <input type="number" name="interest_rate" class="fc" value="{{ old('interest_rate',15) }}" step="0.01" min="0" max="100" required>
          <span class="ft">Default: 15% per month flat</span>
        </div>

        <div class="fg">
          <label class="fl">Initiation Fee (% of principal) *</label>
          <input type="number" name="initiation_fee_rate" class="fc" value="{{ old('initiation_fee_rate',40) }}" step="0.01" min="0" max="100" required>
          <span class="ft">Default: 40%</span>
        </div>

        <div class="fg">
          <label class="fl">Admin Fee (M per month, fixed) *</label>
          <input type="number" name="admin_fee_fixed" class="fc" value="{{ old('admin_fee_fixed',50) }}" step="0.01" min="0" required>
          <span class="ft">Default: M50/month</span>
        </div>

        <div class="fg">
          <label class="fl">Late Penalty (M per 10 days overdue)</label>
          <input type="number" name="late_payment_fee" class="fc" value="{{ old('late_payment_fee',20) }}" step="0.01" min="0">
          <span class="ft">Default: M20 per 10 days</span>
        </div>

        <div class="fg">
          <label class="fl">Min Amount (M) *</label>
          <input type="number" name="min_amount" class="fc" value="{{ old('min_amount',500) }}" min="1" required>
        </div>

        <div class="fg">
          <label class="fl">Max Amount (M) *</label>
          <input type="number" name="max_amount" class="fc" value="{{ old('max_amount') }}" min="1" required>
        </div>

        <div class="fg">
          <label class="fl">Min Term (months) *</label>
          <input type="number" name="min_term_months" class="fc" value="{{ old('min_term_months',1) }}" min="1" max="24" required>
        </div>

        <div class="fg">
          <label class="fl">Max Term (months, max 24) *</label>
          <input type="number" name="max_term_months" class="fc" value="{{ old('max_term_months',6) }}" min="1" max="24" required>
        </div>

        <div class="fg" style="display:flex;align-items:center;gap:10px;margin-top:8px">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active',true) ? 'checked' : '' }}>
            <span class="fl" style="margin:0">Active</span>
          </label>
        </div>

        <div class="fg" style="grid-column:span 2">
          <label class="fl">Description</label>
          <textarea name="description" class="fc" rows="2">{{ old('description') }}</textarea>
        </div>

        <div class="fg" style="grid-column:span 2">
          <label class="fl">Eligibility Criteria</label>
          <textarea name="eligibility_criteria" class="fc" rows="2">{{ old('eligibility_criteria') }}</textarea>
        </div>

      </div>

      {{-- Live fee preview --}}
      <div id="fee-preview" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;margin-top:16px">
        <div style="font-weight:700;color:#065f46;margin-bottom:8px;font-size:13px">
          <i class="bi bi-calculator"></i> Live Fee Preview (M1,000 example)
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;font-size:13px" id="fee-grid"></div>
      </div>

    </div>
    <div style="padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
      <a href="{{ route('admin.products.index') }}" class="btn btn-o">Cancel</a>
      <button type="submit" class="btn btn-p"><i class="bi bi-check-lg"></i> Save Product</button>
    </div>
  </form>
</div>
</div>

<script>
function updatePreview() {
  const rate    = parseFloat(document.querySelector('[name=interest_rate]').value)    || 0;
  const initPct = parseFloat(document.querySelector('[name=initiation_fee_rate]').value) || 0;
  const admin   = parseFloat(document.querySelector('[name=admin_fee_fixed]').value)  || 0;
  const term    = parseInt(document.querySelector('[name=max_term_months]').value)    || 1;
  const p       = 1000;
  const interest   = p * (rate / 100) * term;
  const initiation = p * (initPct / 100);
  const total      = p + interest + initiation + (admin * term);
  const monthly    = (total / term).toFixed(2);
  document.getElementById('fee-grid').innerHTML = `
    <div style="background:#fff;padding:10px;border-radius:8px;text-align:center">
      <div style="font-size:11px;color:#6b7280">Monthly Installment</div>
      <div style="font-weight:800;color:#059669;font-size:18px">M${monthly}</div>
    </div>
    <div style="background:#fff;padding:10px;border-radius:8px;text-align:center">
      <div style="font-size:11px;color:#6b7280">Total Interest</div>
      <div style="font-weight:700;font-size:15px">M${interest.toFixed(2)}</div>
    </div>
    <div style="background:#fff;padding:10px;border-radius:8px;text-align:center">
      <div style="font-size:11px;color:#6b7280">Initiation Fee</div>
      <div style="font-weight:700;font-size:15px">M${initiation.toFixed(2)}</div>
    </div>
    <div style="background:#fff;padding:10px;border-radius:8px;text-align:center">
      <div style="font-size:11px;color:#6b7280">Total Repayment</div>
      <div style="font-weight:800;color:#1e3a5f;font-size:15px">M${total.toFixed(2)}</div>
    </div>
  `;
}
['interest_rate','initiation_fee_rate','admin_fee_fixed','max_term_months'].forEach(n => {
  document.querySelector(`[name=${n}]`)?.addEventListener('input', updatePreview);
});
updatePreview();
</script>
@endsection
