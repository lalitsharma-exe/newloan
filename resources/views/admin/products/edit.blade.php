@extends('admin.layouts.app')
@section('title', 'Edit Product')
@section('page-title', 'Configure Loan Product')
@section('bc')
<a href="{{ route('admin.products.index') }}">Products</a> / Edit
@endsection

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    <form method="POST" action="{{ route('admin.products.update', $product) }}">
        @csrf @method('PUT')
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
            
            <!-- Main Configuration -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                
                <!-- General Info Card -->
                <div class="card">
                    <div class="card-hdr">
                        <span class="card-title"><i class="bi bi-info-circle" style="color: var(--info);"></i> General Information</span>
                    </div>
                    <div class="card-body">
                        <div class="fg">
                            <label class="fl">Product Name *</label>
                            <input type="text" name="name" class="fc" value="{{ old('name', $product->name) }}" required placeholder="e.g. Standard Personal Loan">
                            <span class="ft">The public name of the loan product visible to borrowers.</span>
                        </div>
                        
                        <div class="fg" style="margin-top: 16px;">
                            <label class="fl">Description</label>
                            <textarea name="description" class="fc" rows="3" placeholder="Briefly describe the product's target audience or benefits...">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Financial Configuration Card -->
                <div class="card">
                    <div class="card-hdr">
                        <span class="card-title"><i class="bi bi-cash-stack" style="color: var(--ok);"></i> Financial Configuration</span>
                    </div>
                    <div class="card-body">
                        <div class="g2" style="gap: 20px;">
                            <div class="fg">
                                <label class="fl">Interest Method *</label>
                                <select name="interest_method" class="fc" required>
                                    <option value="flat" {{ old('interest_method', $product->interest_method) === 'flat' ? 'selected' : '' }}>Flat Rate (Fixed)</option>
                                    <option value="reducing" {{ old('interest_method', $product->interest_method) === 'reducing' ? 'selected' : '' }}>Reducing Balance</option>
                                </select>
                            </div>
                            
                            <div class="fg">
                                <label class="fl">Interest Rate (% per month) *</label>
                                <div style="position: relative;">
                                    <input type="number" name="interest_rate" class="fc" value="{{ old('interest_rate', $product->interest_rate) }}" step="0.01" min="0" max="100" required>
                                    <span style="position: absolute; right: 12px; top: 10px; color: var(--muted); font-weight: 600;">%</span>
                                </div>
                            </div>

                            <div class="fg">
                                <label class="fl">Initiation Fee (% of Principal) *</label>
                                <div style="position: relative;">
                                    <input type="number" name="initiation_fee_rate" class="fc" value="{{ old('initiation_fee_rate', $product->initiation_fee_rate) }}" step="0.01" min="0" max="100" required>
                                    <span style="position: absolute; right: 12px; top: 10px; color: var(--muted); font-weight: 600;">%</span>
                                </div>
                            </div>

                            <div class="fg">
                                <label class="fl">Admin Fee (Monthly Fixed) *</label>
                                <div style="position: relative;">
                                    <input type="number" name="admin_fee_fixed" class="fc" value="{{ old('admin_fee_fixed', $product->admin_fee_fixed) }}" step="0.01" min="0" required>
                                    <span style="position: absolute; left: 12px; top: 10px; color: var(--muted); font-weight: 600;">M</span>
                                    <style>input[name=admin_fee_fixed] { padding-left: 30px; }</style>
                                </div>
                            </div>

                            <div class="fg">
                                <label class="fl">Late Penalty (Fixed M per 10 days)</label>
                                <div style="position: relative;">
                                    <input type="number" name="late_payment_fee" class="fc" value="{{ old('late_payment_fee', $product->late_payment_fee) }}" step="0.01" min="0">
                                    <span style="position: absolute; left: 12px; top: 10px; color: var(--muted); font-weight: 600;">M</span>
                                    <style>input[name=late_payment_fee] { padding-left: 30px; }</style>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms & Limits Card -->
                <div class="card">
                    <div class="card-hdr">
                        <span class="card-title"><i class="bi bi-calendar-range" style="color: var(--warn);"></i> Terms & Limits</span>
                    </div>
                    <div class="card-body">
                        <div class="g2" style="gap: 20px;">
                            <div class="fg">
                                <label class="fl">Min Amount (M) *</label>
                                <input type="number" name="min_amount" class="fc" value="{{ old('min_amount', $product->min_amount) }}" min="1" required>
                            </div>
                            <div class="fg">
                                <label class="fl">Max Amount (M) *</label>
                                <input type="number" name="max_amount" class="fc" value="{{ old('max_amount', $product->max_amount) }}" min="1" required>
                            </div>
                            <div class="fg">
                                <label class="fl">Min Term (Months) *</label>
                                <input type="number" name="min_term_months" class="fc" value="{{ old('min_term_months', $product->min_term_months) }}" min="1" required>
                            </div>
                            <div class="fg">
                                <label class="fl">Max Term (Months) *</label>
                                <input type="number" name="max_term_months" class="fc" value="{{ old('max_term_months', $product->max_term_months) }}" min="1" max="60" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Eligibility Card -->
                <div class="card">
                    <div class="card-hdr">
                        <span class="card-title"><i class="bi bi-shield-check" style="color: var(--navy);"></i> Eligibility & Requirements</span>
                    </div>
                    <div class="card-body">
                        <div class="fg">
                            <label class="fl">Eligibility Criteria</label>
                            <textarea name="eligibility_criteria" class="fc" rows="4" placeholder="e.g. Minimum salary M5,000, Proof of employment for 6 months...">{{ old('eligibility_criteria', $product->eligibility_criteria) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar: Status & Preview -->
            <div style="display: flex; flex-direction: column; gap: 24px; position: sticky; top: 24px;">
                
                <!-- Action Card -->
                <div class="card">
                    <div class="card-body" style="padding: 20px;">
                        <div style="margin-bottom: 20px;">
                            <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid var(--border);">
                                <span style="font-weight: 700; color: var(--navy); font-size: 14px;">Product Status</span>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 12px; color: var(--muted);">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--ok);">
                                </div>
                            </label>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <button type="submit" class="btn btn-p" style="width: 100%; justify-content: center; padding: 12px;">
                                <i class="bi bi-save"></i> Update Product
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-o" style="width: 100%; justify-content: center; padding: 12px;">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Intelligence Preview Card -->
                <div class="card" style="background: var(--navy); color: #fff; border: none;">
                    <div class="card-body" style="padding: 24px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
                            <i class="bi bi-cpu" style="font-size: 20px; color: var(--ok);"></i>
                            <span style="font-weight: 800; font-size: 15px; letter-spacing: 0.5px;">FEE INTELLIGENCE</span>
                        </div>
                        
                        <div style="font-size: 12px; color: rgba(255,255,255,0.6); margin-bottom: 20px;">
                            Real-time projection based on a <strong>M1,000</strong> example loan over the max term.
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 15px;" id="fee-preview-list">
                            <!-- JS Injected -->
                        </div>

                        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <div style="font-size: 11px; color: rgba(255,255,255,0.5); text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">Estimated Monthly Pay</div>
                            <div id="monthly-installment-preview" style="font-size: 32px; font-weight: 900; color: var(--ok);">M0.00</div>
                        </div>
                    </div>
                </div>

                @if($errors->any())
                <div class="alert a-e">
                    <div style="font-weight: 800; margin-bottom: 5px;"><i class="bi bi-x-circle-fill"></i> Validation Errors</div>
                    <ul style="margin: 0; padding-left: 15px; font-size: 12px;">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </form>
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
    const adminTotal = admin * term;
    const total      = p + interest + initiation + adminTotal;
    const monthly    = (total / term).toFixed(2);

    const format = (v) => 'M' + v.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

    document.getElementById('monthly-installment-preview').innerText = format(parseFloat(monthly));
    
    document.getElementById('fee-preview-list').innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; opacity: 0.8;">Total Interest</span>
            <span style="font-weight: 700;">${format(interest)}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; opacity: 0.8;">Initiation Fee</span>
            <span style="font-weight: 700;">${format(initiation)}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; opacity: 0.8;">Total Admin Fees</span>
            <span style="font-weight: 700;">${format(adminTotal)}</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 5px; color: var(--warn);">
            <span style="font-size: 13px; font-weight: 700;">Total Repayment</span>
            <span style="font-weight: 800;">${format(total)}</span>
        </div>
    `;
}

['interest_rate', 'initiation_fee_rate', 'admin_fee_fixed', 'max_term_months'].forEach(n => {
    document.querySelector(`[name=${n}]`)?.addEventListener('input', updatePreview);
});
updatePreview();
</script>
@endsection
