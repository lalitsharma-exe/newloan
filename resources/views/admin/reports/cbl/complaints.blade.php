@extends('admin.layouts.app')

@section('title', 'CBL Complaint Registration')
@section('page-title', 'Regulatory Grievance Management')

@section('content')
<div class="d-wrap">
    
    @if(session('success'))
        <div class="alert bok mb-4"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div style="display:grid; grid-template-columns: 1fr 400px; gap:24px; align-items: start">
        
        {{-- MAIN FORM: 6 SECTIONS --}}
        <form action="{{ route('admin.reports.cbl.complaints.store') }}" method="POST" id="complaintForm">
            @csrf
            
            {{-- SECTION 1 & 2: INSTITUTION & REFERENCE --}}
            <div class="card" style="margin-bottom:20px">
                <div class="card-hdr" style="background:#f8fafc"><span class="card-title">Section 1 & 2: Institution & Reference</span></div>
                <div class="card-body" style="background:#fff">
                    <div class="g3">
                        <div class="fg"><label class="fl">Institution ID</label><input type="text" class="fc" value="Prosperity Loans Limited" readonly style="background:#f1f5f9"></div>
                        <div class="fg"><label class="fl">Financial Year</label><input type="text" class="fc" value="{{ date('Y') }}" readonly style="background:#f1f5f9"></div>
                        <div class="fg"><label class="fl">Complaint Ref #</label><input type="text" class="fc" value="Auto-generated" readonly style="background:#f1f5f9; color:var(--blue); font-weight:700"></div>
                    </div>
                    <div class="g2" style="margin-top:15px">
                        <div class="fg">
                            <label class="fl">Complaint Receipt Date <span style="color:red">*</span></label>
                            <input type="date" name="complaint_date" class="fc" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="fg">
                            <label class="fl">Reporting Period</label>
                            @php $q = ceil(date('n') / 3); @endphp
                            <input type="text" class="fc" value="Quarter {{ $q }}" readonly style="background:#f1f5f9">
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 3: CUSTOMER DETAILS --}}
            <div class="card" style="margin-bottom:20px">
                <div class="card-hdr" style="background:#f8fafc"><span class="card-title">Section 3: Customer Details</span></div>
                <div class="card-body">
                    <div class="fg" style="margin-bottom:20px">
                        <label class="fl">Search Account / Borrower</label>
                        <div style="display:flex; gap:10px">
                            <input type="text" id="user_search" class="fc" placeholder="Type name, phone or email to search existing database..." autocomplete="off">
                            <input type="hidden" name="user_id" id="selected_user_id" required>
                        </div>
                        <div id="search_results" style="background:#fff; border:1px solid var(--border); border-radius:8px; display:none; margin-top:5px; position:absolute; width:400px; z-index:100; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1)"></div>
                    </div>

                    <div class="g2">
                        <div class="fg"><label class="fl">First Name <span style="color:red">*</span></label><input type="text" name="customer_first_name" id="f_name" class="fc" required maxlength="100"></div>
                        <div class="fg"><label class="fl">Surname <span style="color:red">*</span></label><input type="text" name="customer_surname" id="s_name" class="fc" required maxlength="100"></div>
                    </div>
                    <div class="g2">
                        <div class="fg">
                            <label class="fl">Account / Loan Number <span style="color:red">*</span></label>
                            <div id="acc_num_wrap">
                                <select name="account_number" id="acc_num" class="fc" required onchange="handleAccountChange(this)">
                                    <option value="">Search borrower first...</option>
                                </select>
                            </div>
                            <div id="acc_num_other_wrap" style="display:none; margin-top:10px">
                                <input type="text" id="acc_num_other" class="fc" placeholder="Enter custom account or reference...">
                            </div>
                            <input type="hidden" name="loan_id" id="selected_loan_id">
                        </div>
                        <div class="fg">
                            <label class="fl">Customer Type <span style="color:red">*</span></label>
                            <select name="customer_type" class="fc" onchange="toggleOther(this, 'cust_type_other_wrap')" required>
                                <option value="Individual">Individual</option>
                                <option value="MSME">MSME</option>
                                <option value="Large business">Large business</option>
                                <option value="MFI's employee">MFI's employee</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                    </div>
                    <div id="cust_type_other_wrap" class="fg" style="display:none">
                        <label class="fl">Specify Customer Type <span style="color:red">*</span></label>
                        <input type="text" name="customer_type_other" class="fc">
                    </div>
                    <div class="g3">
                        <div class="fg"><label class="fl">Cell Number <span style="color:red">*</span></label><input type="text" name="customer_cell_number" id="phone" class="fc" required></div>
                        <div class="fg"><label class="fl">Email Address</label><input type="email" name="customer_email_address" id="email" class="fc"></div>
                        <div class="fg">
                            <label class="fl">Age Group <span style="color:red">*</span></label>
                            <select name="age_group" class="fc" required>
                                <option value="Under 18">Under 18</option>
                                <option value="18 – 25">18 – 25</option>
                                <option value="26 – 35">26 – 35</option>
                                <option value="36 – 45">36 – 45</option>
                                <option value="46 – 55">46 – 55</option>
                                <option value="56 – 65">56 – 65</option>
                                <option value="65 and above">65 and above</option>
                            </select>
                        </div>
                    </div>
                    <div class="fg">
                        <label class="fl">Sex <span style="color:red">*</span></label>
                        <select name="sex" class="fc" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- SECTION 4: RECEIPT DETAILS --}}
            <div class="card" style="margin-bottom:20px">
                <div class="card-hdr" style="background:#f8fafc"><span class="card-title">Section 4: Complaint Receipt Details</span></div>
                <div class="card-body">
                    <div class="g3">
                        <div class="fg">
                            <label class="fl">Mode of Receipt <span style="color:red">*</span></label>
                            <select name="mode_of_receipt" class="fc" required>
                                <option value="Branch or Office visit">Branch or Office visit</option>
                                <option value="Email">Email</option>
                                <option value="Telephone">Telephone</option>
                                <option value="Other digital platforms">Other digital platforms</option>
                            </select>
                        </div>
                        <div class="fg"><label class="fl">Branch / Place Received <span style="color:red">*</span></label><input type="text" name="received_at_place" class="fc" placeholder="e.g. Maseru Head Office" required maxlength="150"></div>
                        <div class="fg">
                            <label class="fl">District <span style="color:red">*</span></label>
                            <select name="district" class="fc" required>
                                <option value="Mokhotlong">Mokhotlong</option>
                                <option value="Butha-Buthe">Butha-Buthe</option>
                                <option value="Leribe">Leribe</option>
                                <option value="Berea">Berea</option>
                                <option value="Maseru">Maseru</option>
                                <option value="Mafeteng">Mafeteng</option>
                                <option value="Mohale's Hoek">Mohale's Hoek</option>
                                <option value="Quthing">Quthing</option>
                                <option value="Qacha's Nek">Qacha's Nek</option>
                                <option value="Thaba-Tseka">Thaba-Tseka</option>
                                <option value="N/A">N/A</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 5: CLASSIFICATION --}}
            <div class="card" style="margin-bottom:20px">
                <div class="card-hdr" style="background:#f8fafc"><span class="card-title">Section 5: Complaint Classification</span></div>
                <div class="card-body">
                    <div class="g2">
                        <div class="fg">
                            <label class="fl">Product Category <span style="color:red">*</span></label>
                            <select name="product_category" class="fc" onchange="toggleOther(this, 'prod_other_wrap')" required>
                                <option value="Unsecured personal loan">Unsecured personal loan</option>
                                <option value="Vehicle loan – personal">Vehicle loan – personal</option>
                                <option value="Home loan – personal">Home loan – personal</option>
                                <option value="Business loan">Business loan</option>
                                <option value="Others">Others</option>
                            </select>
                            <div id="prod_other_wrap" style="display:none; margin-top:10px"><input type="text" name="product_category_other" class="fc" placeholder="Specify product..."></div>
                        </div>
                        <div class="fg">
                            <label class="fl">Issue Category <span style="color:red">*</span></label>
                            <select name="issue_category" class="fc" onchange="toggleOther(this, 'issue_other_wrap')" required>
                                <option value="Transparency">Transparency</option>
                                <option value="Unfair treatment">Unfair treatment</option>
                                <option value="Data privacy and security">Data privacy and security</option>
                                <option value="Error">Error</option>
                                <option value="Fraud">Fraud</option>
                                <option value="Pricing">Pricing</option>
                                <option value="Over-indebtedness">Over-indebtedness</option>
                                <option value="Service quality and reliability">Service quality and reliability</option>
                                <option value="Others">Others</option>
                            </select>
                            <div id="issue_other_wrap" style="display:none; margin-top:10px"><input type="text" name="issue_category_other" class="fc" placeholder="Specify issue..."></div>
                        </div>
                    </div>
                    <div class="fg" style="margin-top:15px">
                        <label class="fl">Full Description <span style="color:red">*</span> (Min 50 chars)</label>
                        <textarea name="description" class="fc" rows="4" required minlength="50" placeholder="Provide a detailed explanation of the customer grievance..."></textarea>
                    </div>
                    <div class="fg">
                        <label class="fl">Complainant Name (If 3rd Party)</label>
                        <input type="text" name="complainant_name_third_party" class="fc" placeholder="Leave blank if customer submitted personally">
                    </div>
                </div>
            </div>

            <div style="margin-bottom:60px">
                <button type="submit" class="btn btn-p btn-lg" style="width:100%; justify-content:center; padding:16px; border-radius:12px; font-weight:800; font-size:16px">
                    <i class="bi bi-shield-check"></i> Validate & Register Complaint
                </button>
            </div>
        </form>

        {{-- SIDEBAR: RECENT LOGS --}}
        <div>
            <div class="card">
                <div class="card-hdr">
                    <span class="card-title">Recent Complaints</span>
                    <div style="font-size:11px; font-weight:700; color:var(--muted)">LIVE LOG</div>
                </div>
                <div class="card-body" style="padding:0">
                    @foreach($complaints as $c)
                    <div style="padding:15px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center">
                        <div>
                            <div style="font-family:monospace; font-size:12px; color:var(--blue); font-weight:700">{{ $c->reference_number }}</div>
                            <div style="font-size:13px; font-weight:700; margin-top:2px">{{ $c->customer_first_name }} {{ $c->customer_surname }}</div>
                            <div style="font-size:11px; color:var(--muted)">{{ $c->complaint_date->format('d M Y') }}</div>
                        </div>
                        <div style="text-align:right">
                            <span class="badge {{ $c->status === 'Pending' ? 'bw' : 'bok' }}" style="font-size:10px">{{ $c->status }}</span>
                            <button onclick="openManageModal({{ $c->id }}, '{{ $c->reference_number }}')" class="btn btn-sm btn-o" style="display:block; margin-top:5px; font-size:10px">Manage</button>
                        </div>
                    </div>
                    @endforeach
                    @if($complaints->isEmpty())
                        <div style="padding:40px; text-align:center; color:var(--muted); font-size:13px">No complaints found.</div>
                    @endif
                </div>
                @if($complaints->hasPages())
                    <div style="padding:10px">{{ $complaints->links() }}</div>
                @endif
            </div>

            {{-- TURNAROUND BENCHMARK CONFIG --}}
            <div class="card" style="margin-top:20px; background:linear-gradient(135deg, #fef2f2, #fff)">
                <div class="card-hdr" style="background:transparent"><span class="card-title">CBL Turnaround Monitor</span></div>
                <div class="card-body">
                    <div style="font-size:12px; color:#991b1b; margin-bottom:10px">Benchmark: <strong>15 Working Days</strong></div>
                    <div style="font-size:11px; color:var(--muted); line-height:1.5">Turnaround time is calculated based on Monday-Friday excluding official Lesotho public holidays.</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MANAGE / RESOLUTION MODAL --}}
<div id="manageModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; width:100%; max-width:600px; border-radius:16px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
        <div style="padding:20px; background:var(--navy); color:#fff; display:flex; justify-content:space-between; align-items:center">
            <h3 style="font-size:18px; font-weight:700">Manage Complaint: <span id="m_ref"></span></h3>
            <button onclick="closeModal()" style="background:transparent; border:none; color:#fff; font-size:24px; cursor:pointer">&times;</button>
        </div>
        <form id="updateForm" method="POST" style="padding:25px">
            @csrf
            <div class="g2">
                <div class="fg">
                    <label class="fl">Status <span style="color:red">*</span></label>
                    <select name="status" class="fc" required>
                        <option value="Pending">Pending</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="fg">
                    <label class="fl">Resolution Date</label>
                    <input type="date" name="resolved_date" class="fc">
                </div>
            </div>
            <div class="fg">
                <label class="fl">Status Description / Action Taken <span style="color:red">*</span></label>
                <textarea name="status_description" class="fc" rows="4" required placeholder="Describe what has been done to address this grievance..."></textarea>
            </div>
            <div class="g2">
                <div class="fg">
                    <label class="fl">Amount Reimbursed (LSL)</label>
                    <input type="number" step="0.01" name="amount_reimbursed" class="fc">
                </div>
                <div class="fg" style="display:flex; align-items:flex-end">
                    <button type="submit" class="btn btn-p" style="width:100%; justify-content:center; height:45px">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .g3 { display:grid; grid-template-columns: repeat(3, 1fr); gap:15px; }
    .g2 { display:grid; grid-template-columns: repeat(2, 1fr); gap:15px; }
</style>

@push('scripts')
<script>
    function toggleOther(sel, targetId) {
        document.getElementById(targetId).style.display = (sel.value === 'Others') ? 'block' : 'none';
    }

    function handleAccountChange(sel) {
        const otherWrap = document.getElementById('acc_num_other_wrap');
        const otherInput = document.getElementById('acc_num_other');
        const loanIdInput = document.getElementById('selected_loan_id');
        
        if (sel.value === 'Other') {
            otherWrap.style.display = 'block';
            otherInput.required = true;
            loanIdInput.value = '';
        } else {
            otherWrap.style.display = 'none';
            otherInput.required = false;
            // Get data-loan-id from selected option
            const selectedOpt = sel.options[sel.selectedIndex];
            loanIdInput.value = selectedOpt.getAttribute('data-loan-id') || '';
        }
    }

    // Intercept form submission to handle 'Other' account number
    document.getElementById('complaintForm').addEventListener('submit', function(e) {
        const accSelect = document.getElementById('acc_num');
        const accOther = document.getElementById('acc_num_other');
        
        if (accSelect.value === 'Other') {
            // Temporarily change select value or use a hidden field if needed. 
            // Here we'll just ensure the custom value is what gets sent.
            // Actually, simpler to just have a hidden input that gets populated.
            // But let's just make the 'Other' input have the name if it's visible.
        }
    });

    // Modified handleAccountChange to handle the hidden submission logic
    function handleAccountChange(sel) {
        const otherWrap = document.getElementById('acc_num_other_wrap');
        const otherInput = document.getElementById('acc_num_other');
        const loanIdInput = document.getElementById('selected_loan_id');
        
        if (sel.value === 'Other') {
            otherWrap.style.display = 'block';
            otherInput.required = true;
            otherInput.name = 'account_number'; // Give it the name
            sel.name = 'account_number_placeholder'; // Take name away from select
            loanIdInput.value = '';
        } else {
            otherWrap.style.display = 'none';
            otherInput.required = false;
            otherInput.name = 'account_number_other_val'; // Take name away
            sel.name = 'account_number'; // Give name to select
            const selectedOpt = sel.options[sel.selectedIndex];
            loanIdInput.value = selectedOpt.getAttribute('data-loan-id') || '';
        }
    }

    function openManageModal(id, ref) {
        document.getElementById('m_ref').innerText = ref;
        document.getElementById('updateForm').action = `/admin/reports/complaints/${id}/update`;
        document.getElementById('manageModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('manageModal').style.display = 'none';
    }

    // User Search Logic
    const searchInput = document.getElementById('user_search');
    const resultsDiv = document.getElementById('search_results');
    const selectedUserId = document.getElementById('selected_user_id');
    const accSelect = document.getElementById('acc_num');

    searchInput.addEventListener('input', function() {
        const query = this.value;
        if (query.length < 3) { resultsDiv.style.display = 'none'; return; }
        fetch(`/admin/reports/cbl/search-users?q=${query}`)
            .then(res => res.json())
            .then(data => {
                resultsDiv.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(user => {
                        const div = document.createElement('div');
                        div.style.padding = '10px 15px'; div.style.cursor = 'pointer'; div.style.borderBottom = '1px solid var(--border)';
                        div.innerHTML = `<div style="font-weight:700">${user.name}</div><div class="muted" style="font-size:11px">${user.phone}</div>`;
                        div.onclick = () => {
                            searchInput.value = user.name;
                            selectedUserId.value = user.id;
                            
                            // Auto-fill Section 3 fields
                            document.getElementById('f_name').value = user.name.split(' ')[0] || '';
                            document.getElementById('s_name').value = user.name.split(' ').slice(1).join(' ') || '';
                            document.getElementById('phone').value = user.phone;
                            
                            // Populate Account Dropdown
                            accSelect.innerHTML = '<option value="">Select Account / Loan...</option>';
                            if (user.loans && user.loans.length > 0) {
                                user.loans.forEach(loan => {
                                    const opt = document.createElement('option');
                                    opt.value = loan.loan_number;
                                    opt.innerText = `Loan: ${loan.loan_number}`;
                                    opt.setAttribute('data-loan-id', loan.id);
                                    accSelect.appendChild(opt);
                                });
                            }
                            const otherOpt = document.createElement('option');
                            otherOpt.value = 'Other';
                            otherOpt.innerText = 'Other (Custom Reference)';
                            accSelect.appendChild(otherOpt);
                            
                            resultsDiv.style.display = 'none';
                        };
                        div.onmouseover = () => div.style.background = 'var(--bg)';
                        div.onmouseout = () => div.style.background = '#fff';
                        resultsDiv.appendChild(div);
                    });
                    resultsDiv.style.display = 'block';
                } else { resultsDiv.style.display = 'none'; }
            });
    });
</script>
@endpush
@endsection
