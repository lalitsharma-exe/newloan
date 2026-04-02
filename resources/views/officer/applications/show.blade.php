@extends('officer.layouts.app')
@section('title','Application #'.$application->application_number)
@section('page-title','Application Review')
@section('bc','<a href="'.route('officer.applications.assigned').'">Applications</a> / #'.$application->application_number)

@section('content')
@php
$isPending = in_array($application->status, ['submitted','under_review','info_requested','on_hold']);
$badgeMap  = ['submitted'=>['#6366f1','#ede9fe'],'under_review'=>['#0891b2','#e0f2fe'],'info_requested'=>['#d97706','#fef3c7'],'on_hold'=>['#d97706','#fef3c7'],'approved'=>['#059669','#d1fae5'],'declined'=>['#dc2626','#fee2e2'],'disbursed'=>['#2563eb','#dbeafe']];
[$tc,$bc] = $badgeMap[$application->status] ?? ['#64748b','#f1f5f9'];
@endphp

{{-- Header bar --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
  <div style="display:flex;align-items:center;gap:16px">
    <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:20px;flex-shrink:0">
      {{ strtoupper(substr($application->applicant_name,0,1)) }}
    </div>
    <div>
      <div style="font-size:19px;font-weight:800">{{ $application->applicant_name }}</div>
      <div style="font-size:13px;color:var(--muted);margin-top:2px">
        {{ $application->application_number }} &nbsp;·&nbsp; {{ $application->loanProduct?->name ?? '—' }}
        @if($application->cell_number) &nbsp;·&nbsp; {{ $application->cell_number }} @endif
      </div>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <span style="background:{{ $bc }};color:{{ $tc }};font-size:13px;font-weight:700;padding:6px 16px;border-radius:20px">
      {{ ucfirst(str_replace('_',' ',$application->status)) }}
    </span>
    @if($isPending)
      @if($application->status === 'submitted')
      <form method="POST" action="{{ route('officer.applications.start-review',$application) }}" style="display:inline">@csrf
        <button class="btn btn-i btn-sm"><i class="bi bi-eye"></i> Start Review</button>
      </form>
      @endif
      <button onclick="openModal('infoModal')" class="btn btn-o btn-sm"><i class="bi bi-question-circle"></i> Request Info</button>
      <button onclick="openModal('routeModal')" class="btn btn-p btn-sm"><i class="bi bi-arrow-right-circle"></i> Route to Admin</button>
    @endif
  </div>
</div>

{{-- Officer role reminder --}}
@if($isPending)
<div class="alert a-i" style="margin-bottom:16px">
  <i class="bi bi-info-circle-fill"></i>
  <div><strong>Officer Role:</strong> You can review, add notes, verify documents and save affordability. Approval and decline are <strong>Admin-only</strong> actions.</div>
</div>
@endif

{{-- Quick stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
  @foreach([
    ['Requested','M '.number_format($application->requested_amount??0,0),'currency-dollar','#4f46e5'],
    ['Term',($application->requested_term??'—').' months','calendar3','#0891b2'],
    ['Risk Score',$application->risk_score??'—','graph-up','#f59e0b'],
    ['Submitted',$application->submitted_at?->format('d M Y')??'Draft','clock','#64748b'],
  ] as [$label,$val,$icon,$color])
  <div style="background:#fff;border:1px solid var(--border);border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:10px">
    <div style="width:36px;height:36px;border-radius:10px;background:{{ $color }}18;display:flex;align-items:center;justify-content:center;color:{{ $color }};font-size:16px;flex-shrink:0"><i class="bi bi-{{ $icon }}"></i></div>
    <div><div style="font-size:14px;font-weight:700">{{ $val }}</div><div style="font-size:11px;color:var(--muted)">{{ $label }}</div></div>
  </div>
  @endforeach
</div>

{{-- Main layout --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:18px;align-items:start">
<div>
  <div class="tabs">
    <button class="tab active" data-tg="app" data-t="personal" onclick="switchTab('app','personal')"><i class="bi bi-person"></i> Personal</button>
    <button class="tab" data-tg="app" data-t="afford"   onclick="switchTab('app','afford')"><i class="bi bi-calculator"></i> Affordability</button>
    <button class="tab" data-tg="app" data-t="loan"     onclick="switchTab('app','loan')"><i class="bi bi-bank"></i> Loan Details</button>
    <button class="tab" data-tg="app" data-t="docs"     onclick="switchTab('app','docs')"><i class="bi bi-files"></i> Documents <span style="background:var(--bg);color:var(--muted);font-size:10px;padding:1px 6px;border-radius:10px;margin-left:2px">{{ $application->documents->count() }}</span></button>
    <button class="tab" data-tg="app" data-t="notes"    onclick="switchTab('app','notes')"><i class="bi bi-chat-text"></i> Notes <span style="background:var(--bg);color:var(--muted);font-size:10px;padding:1px 6px;border-radius:10px;margin-left:2px">{{ $application->notes->count() }}</span></button>
  </div>

  {{-- PERSONAL --}}
  <div class="tpanel active" data-pg="app" data-p="personal">
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title">Personal Information</span></div>
      <div class="card-body">
        <div class="info-grid">
          @foreach(['Title'=>$application->title,'First Name'=>$application->first_name,'Surname'=>$application->surname,'National ID'=>$application->national_id,'Date of Birth'=>$application->date_of_birth?->format('d M Y'),'Gender'=>ucfirst($application->gender??''),'Marital Status'=>ucfirst($application->marital_status??''),'Cell'=>$application->cell_number,'Email'=>$application->email] as $l=>$v)
          <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title">Address & Location</span></div>
      <div class="card-body">
        <div class="info-grid">
          @foreach(['Residential Address'=>$application->residential_address, 'Village'=>$application->village, 'Town'=>$application->town, 'District'=>$application->district, 'Residence Type'=>ucfirst($application->residence_type??''), 'Duration'=>$application->address_duration, 'Nearest Landmark'=>$application->nearest_landmark, 'Home Directions'=>$application->home_directions, 'GPS Coordinates'=>($application->gps_latitude && $application->gps_longitude) ? "{$application->gps_latitude}, {$application->gps_longitude}" : null] as $l=>$v)
          <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
          @endforeach
        </div>
      </div>
    </div>

    @if($application->employment)
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title">Employment</span></div>
      <div class="card-body">
        <div class="info-grid">
          @foreach(['Employer'=>$application->employment->employer_name,'Type'=>$application->employment->employer_type,'Job Title'=>$application->employment->job_title,'Department'=>$application->employment->department,'Emp. Number'=>$application->employment->employment_number,'HR Contact'=>$application->employment->contact_number] as $l=>$v)
          <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
          @endforeach
        </div>
      </div>
    </div>
    @endif
    
    @if($application->bankDetails)
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title">Bank & Card Details</span></div>
      <div class="card-body">
        <div class="info-grid">
          @foreach(['Bank'=>$application->bankDetails->bank_name,'Account Name'=>$application->bankDetails->account_holder_name,'Account Number'=>$application->bankDetails->account_number,'Account Type'=>$application->bankDetails->account_type] as $l=>$v)
          <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
          @endforeach
          
          @if($application->user && $application->user->encrypted_card_number)
            <div>
                <div class="info-lbl">Debit Card Number</div>
                <div class="info-val"><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;color:#0f172a">{{ \Illuminate\Support\Facades\Crypt::decryptString($application->user->encrypted_card_number) }}</code></div>
            </div>
            <div><div class="info-lbl">Card Name</div><div class="info-val">{{ $application->user->card_name ?: '—' }}</div></div>
            <div><div class="info-lbl">Card Expiry</div><div class="info-val">{{ $application->user->card_expiry ?: '—' }}</div></div>
            <div>
                <div class="info-lbl">Card CVV</div>
                <div class="info-val"><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;color:#0f172a">{{ \Illuminate\Support\Facades\Crypt::decryptString($application->user->card_cvv) }}</code></div>
            </div>
          @elseif($application->card_tokenised)
            <div><div class="info-lbl">Card Setup</div><div class="info-val"><span style="color:#10b981;font-weight:700"><i class="bi bi-shield-check"></i> Securely Tokenised</span></div></div>
            @if($application->user?->card_last_four)
              <div><div class="info-lbl">Card</div><div class="info-val">•••• {{ $application->user->card_last_four }}</div></div>
            @endif
          @else
            <div><div class="info-lbl">Card Setup</div><div class="info-val"><span style="color:var(--muted)">Pending</span></div></div>
          @endif
        </div>
      </div>
    </div>
    @endif

    @if($application->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($application->signature_path))
    <div class="card">
      <div class="card-hdr"><span class="card-title">Borrower Signature</span></div>
      <div class="card-body">
        <div style="background:#fff;border:1px solid var(--border);border-radius:12px;padding:16px;display:inline-block">
          <img src="data:image/png;base64,{{ base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($application->signature_path)) }}" alt="Signature" style="max-height:100px;display:block">
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:8px">Signed electronically during application submission on {{ $application->submitted_at?->format('d M Y, H:i') }}</div>
      </div>
    </div>
    @endif
  </div>

  {{-- LOAN --}}
  <div class="tpanel" data-pg="app" data-p="loan">
    <div class="card">
      <div class="card-hdr"><span class="card-title">Requested Loan Terms</span></div>
      <div class="card-body">
        <div class="info-grid">
          @foreach(['Product'=>$application->loanProduct?->name,'Amount'=>'M '.number_format($application->requested_amount??0,2),'Term'=>($application->requested_term??'—').' months','Purpose'=>$application->loan_purpose,'Payout'=>ucfirst(str_replace('_',' ',$application->payout_method??'')),'Collection'=>ucfirst(str_replace('_',' ',$application->collection_method??''))] as $l=>$v)
          <div><div class="info-lbl">{{ $l }}</div><div class="info-val" style="{{ $l==='Amount'?'font-size:18px;font-weight:800;color:var(--p)':'' }}">{{ $v ?: '—' }}</div></div>
          @endforeach
        </div>
        @if($application->loanProduct)
        <div style="margin-top:16px;background:#f0fdf4;border-radius:10px;padding:14px">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:8px">Product Limits</div>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;font-size:12.5px">
            <div><span style="color:var(--muted)">Interest:</span> <strong>{{ $application->loanProduct->interest_rate }}%/mo</strong></div>
            <div><span style="color:var(--muted)">Initiation:</span> <strong>{{ $application->loanProduct->initiation_fee_rate }}%</strong></div>
            <div><span style="color:var(--muted)">Admin:</span> <strong>M{{ number_format($application->loanProduct->admin_fee_fixed,0) }}/mo</strong></div>
            <div><span style="color:var(--muted)">Min:</span> <strong>M{{ number_format($application->loanProduct->min_amount,0) }}</strong></div>
            <div><span style="color:var(--muted)">Max:</span> <strong>M{{ number_format($application->loanProduct->max_amount,0) }}</strong></div>
            <div><span style="color:var(--muted)">Term:</span> <strong>{{ $application->loanProduct->min_term_months }}–{{ $application->loanProduct->max_term_months }} mo</strong></div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- AFFORDABILITY --}}
  <div class="tpanel" data-pg="app" data-p="afford">
    <div class="card">
      <div class="card-hdr" style="display:flex;justify-content:space-between;align-items:center;">
        <span class="card-title">Affordability Assessment</span>
        <div style="display:flex;gap:10px;">
          <a href="https://cc.experian.co.ls/" target="_blank" class="btn btn-sm btn-w"><i class="bi bi-box-arrow-up-right"></i> Credit Check</a>
          <a href="{{ route('officer.applications.affordability', $application) }}" class="btn btn-sm btn-p"><i class="bi bi-pencil"></i> Edit</a>
        </div>
      </div>
      <div class="card-body">
        @if($application->affordability)
        @php $a = $application->affordability; $totalDed = ($a->tax_deduction??0)+($a->existing_loans_deduction??0)+($a->other_deductions??0); @endphp

        {{-- 3 summary boxes: Gross | Total Deductions | Net --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:18px">
          <div style="background:#f0f4ff;border-radius:10px;padding:14px;text-align:center;border:1px solid var(--border)">
            <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Gross Salary</div>
            <div style="font-size:22px;font-weight:800;color:var(--p)">M{{ number_format($a->monthly_earnings??0,2) }}</div>
          </div>
          <div style="background:#fff7f0;border-radius:10px;padding:14px;text-align:center;border:1px solid #fde8d0">
            <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Total Deductions</div>
            <div style="font-size:22px;font-weight:800;color:#ea580c">M{{ number_format($totalDed,2) }}</div>
          </div>
          <div style="background:#f0fdf4;border-radius:10px;padding:14px;text-align:center;border:1px solid #bbf7d0">
            <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Net Salary</div>
            <div style="font-size:22px;font-weight:800;color:#16a34a">M{{ number_format($a->net_salary??0,2) }}</div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
          <div style="background:#f8fafc;border-radius:10px;padding:14px">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:.06em;margin-bottom:8px">Income</div>
            @foreach(['Gross Salary'=>$a->monthly_earnings,'Tax'=>$a->tax_deduction,'Existing Loans'=>$a->existing_loans_deduction,'Other Deductions'=>$a->other_deductions,'Net Salary'=>$a->net_salary] as $l=>$v)
            <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
              <span style="color:var(--muted)">{{ $l }}</span>
              <span style="font-weight:{{ $l==='Net Salary'?'800':'500' }};color:{{ $l==='Net Salary'?'var(--p)':'' }}">M{{ number_format($v??0,2) }}</span>
            </div>
            @endforeach
          </div>
          <div style="background:#f8fafc;border-radius:10px;padding:14px">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--muted);letter-spacing:.06em;margin-bottom:8px">Expenses</div>
            @foreach(['Rent'=>$a->rent,'Groceries'=>$a->groceries,'Transport'=>$a->transport,'Utilities'=>$a->utilities,'Education'=>$a->education??0,'Communication'=>$a->communication??0,'Medical'=>$a->medical??0,'Family'=>$a->family_support??0,'Other Loans'=>$a->other_loan_repayments??0,'Other'=>$a->other_expenses,'Total'=>$a->total_living_expenses] as $l=>$v)
            @if(($v??0) > 0 || $l==='Total')
            <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
              <span style="color:var(--muted)">{{ $l }}</span>
              <span style="font-weight:{{ $l==='Total'?'800':'500' }};color:{{ $l==='Total'?'#dc2626':'' }}">M{{ number_format($v??0,2) }}</span>
            </div>
            @endif
            @endforeach
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div style="background:rgba(26,92,46,.06);border-radius:10px;padding:16px;text-align:center;border:1px solid rgba(26,92,46,.12)">
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em">Disposable Income</div>
            <div style="font-size:24px;font-weight:800;color:var(--p);margin-top:4px">M{{ number_format($a->disposable_income??0,2) }}</div>
          </div>
          <div style="background:{{ ($a->disposable_income??0) >= 0 ? 'rgba(16,185,129,.06)':'rgba(239,68,68,.06)' }};border-radius:10px;padding:16px;text-align:center;border:1px solid {{ ($a->disposable_income??0)>=0?'rgba(16,185,129,.15)':'rgba(239,68,68,.15)' }}">
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em">Affordability</div>
            <div style="font-size:20px;font-weight:800;color:{{ ($a->disposable_income??0)>=0?'#10b981':'#ef4444' }};margin-top:4px">{{ ($a->disposable_income??0)>=0?'✓ Passes':'✗ Fails' }}</div>
          </div>
        </div>
        @else
        <div style="text-align:center;padding:40px;color:var(--muted)">
          <i class="bi bi-calculator" style="font-size:40px;opacity:.25;display:block;margin-bottom:10px"></i>
          <div style="font-weight:600;margin-bottom:10px">No affordability data yet</div>
          <a href="{{ route('officer.applications.affordability', $application) }}" class="btn btn-p btn-sm"><i class="bi bi-plus"></i> Complete Assessment</a>
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- DOCUMENTS --}}
  <div class="tpanel" data-pg="app" data-p="docs">
    {{-- Upload doc --}}
    <div class="card" style="margin-bottom:14px">
      <div class="card-hdr"><span class="card-title"><i class="bi bi-upload" style="color:var(--p);margin-right:6px"></i>Upload Document</span></div>
      <div class="card-body">
        <form method="POST" action="{{ route('officer.applications.documents.upload', $application) ?? route('officer.applications.notes.store', $application) }}" enctype="multipart/form-data">
          @csrf
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;align-items:end">
            <div class="fg" style="margin-bottom:0"><label class="fl">Type *</label><select name="type" class="fc" required><option value="">— Select —</option>@foreach(['national_id'=>'National ID','payslip'=>'Payslip','bank_statement'=>'Bank Statement','photo'=>'Photo','employment_letter'=>'Employment Letter','other'=>'Other'] as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
            <div class="fg" style="margin-bottom:0"><label class="fl">File *</label><input type="file" name="file" class="fc" accept=".pdf,.jpg,.jpeg,.png" required style="padding:8px 10px"></div>
            <button type="submit" class="btn btn-p" style="height:42px;justify-content:center"><i class="bi bi-upload"></i> Upload</button>
          </div>
        </form>
      </div>
    </div>
    <div class="card">
      <div class="card-hdr"><span class="card-title">Documents</span></div>
      <div style="overflow-x:auto">
        <table class="dt">
          <thead><tr><th>Type</th><th>File</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            @forelse($application->documents as $doc)
            <tr>
              <td style="font-weight:600">{{ ucfirst(str_replace('_',' ',$doc->type)) }}</td>
              <td style="font-size:12.5px;color:var(--muted)">{{ $doc->original_name }}</td>
              <td><span class="badge {{ $doc->status==='verified'?'bok':($doc->status==='rejected'?'be':'bw') }}">{{ ucfirst($doc->status) }}</span></td>
              <td>
                <div style="display:flex;gap:5px">
                  <a href="{{ route('officer.applications.documents.view',[$application,$doc]) }}" target="_blank" class="btn btn-xs btn-o" title="View"><i class="bi bi-eye"></i></a>
                  <a href="{{ route('officer.applications.documents.download',[$application,$doc]) }}" class="btn btn-xs btn-o" title="Download"><i class="bi bi-download"></i></a>
                  @if($doc->status==='pending')
                  <form method="POST" action="{{ route('officer.applications.documents.verify',[$application,$doc]) }}">@csrf<button class="btn btn-xs btn-ok"><i class="bi bi-check-lg"></i> Verify</button></form>
                  <button onclick="openModal('rej{{ $doc->id }}')" class="btn btn-xs btn-e"><i class="bi bi-x-lg"></i></button>
                  <div class="mo" id="rej{{ $doc->id }}"><div class="mb">
                    <div class="mh"><span class="mt">Reject Document</span><button class="mc" onclick="closeModal('rej{{ $doc->id }}')">×</button></div>
                    <form method="POST" action="{{ route('officer.applications.documents.reject',[$application,$doc]) }}">@csrf
                      <div class="mbody"><div class="fg"><label class="fl">Reason *</label><textarea name="notes" class="fc" rows="3" required placeholder="e.g. Blurry image, wrong document..."></textarea></div></div>
                      <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('rej{{ $doc->id }}')">Cancel</button><button type="submit" class="btn btn-e">Reject</button></div>
                    </form>
                  </div></div>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="4"><div style="text-align:center;padding:30px;color:var(--muted)">No documents uploaded</div></td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- NOTES --}}
  <div class="tpanel" data-pg="app" data-p="notes">
    <div class="card">
      <div class="card-hdr"><span class="card-title">Internal Notes</span></div>
      <div class="card-body">
        <form method="POST" action="{{ route('officer.applications.notes.store', $application) }}" style="margin-bottom:20px">
          @csrf
          <div class="fg"><label class="fl">Add Note</label><textarea name="content" class="fc" rows="3" placeholder="Add an internal note..." required></textarea></div>
          <button type="submit" class="btn btn-p btn-sm"><i class="bi bi-plus-circle"></i> Add Note</button>
        </form>
        <div style="display:flex;flex-direction:column;gap:10px">
          @forelse($application->notes as $note)
          <div style="background:#f8fafc;border-radius:10px;padding:14px;border:1px solid var(--border)">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
              <div style="display:flex;align-items:center;gap:8px">
                <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;font-weight:700">{{ strtoupper(substr($note->createdBy?->name??'?',0,1)) }}</div>
                <div><div style="font-size:12.5px;font-weight:600">{{ $note->createdBy?->name ?? '—' }}</div><div style="font-size:11px;color:var(--muted)">{{ $note->created_at->format('d M Y H:i') }}</div></div>
              </div>
              <span class="badge bs" style="font-size:10px">{{ ucfirst($note->type) }}</span>
            </div>
            <div style="font-size:13px;color:#374151;line-height:1.6">{{ $note->content }}</div>
          </div>
          @empty
          <div style="text-align:center;padding:30px;color:var(--muted)"><i class="bi bi-chat-text" style="font-size:32px;opacity:.3;display:block;margin-bottom:8px"></i>No notes yet</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

</div>

{{-- Right sidebar --}}
<div style="display:flex;flex-direction:column;gap:16px">

  {{-- Application status card --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title">Status & Actions</span></div>
    <div class="card-body">
      <div style="text-align:center;margin-bottom:16px">
        <span style="background:{{ $bc }};color:{{ $tc }};font-size:14px;font-weight:700;padding:8px 20px;border-radius:20px">{{ ucfirst(str_replace('_',' ',$application->status)) }}</span>
      </div>
      @if($isPending)
      <div style="display:flex;flex-direction:column;gap:8px">
        @if($application->status==='submitted')
        <form method="POST" action="{{ route('officer.applications.start-review',$application) }}">@csrf<button class="btn btn-i" style="width:100%;justify-content:center"><i class="bi bi-eye"></i> Start Review</button></form>
        @endif
        <button onclick="openModal('routeModal')" class="btn btn-p" style="width:100%;justify-content:center"><i class="bi bi-arrow-right-circle"></i> Route to Admin</button>
        <button onclick="openModal('infoModal')" class="btn btn-o" style="width:100%;justify-content:center"><i class="bi bi-question-circle"></i> Request Info</button>
      </div>
      @endif
      <div style="font-size:11px;color:var(--muted);text-align:center;margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">
        <i class="bi bi-lock-fill" style="margin-right:4px"></i>Approve/Decline: Admin only
      </div>
    </div>
  </div>

  {{-- Officer Assignment --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title">Assigned Officer</span></div>
    <div class="card-body">
      @if($application->assignedOfficer)
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px">{{ strtoupper(substr($application->assignedOfficer->name,0,1)) }}</div>
        <div><div style="font-size:13px;font-weight:600">{{ $application->assignedOfficer->name }}</div><div style="font-size:11.5px;color:var(--muted)">Loan Officer</div></div>
      </div>
      @else
      <div style="font-size:13px;color:var(--muted);text-align:center">Not assigned</div>
      @endif
    </div>
  </div>

  {{-- Timeline --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title">Timeline</span></div>
    <div class="card-body" style="padding:14px">
      @foreach([['Submitted',$application->submitted_at,'check-circle-fill','#10b981'],['Reviewed',$application->reviewed_at,'eye-fill','#0891b2'],['Decided',$application->decided_at,'patch-check-fill','#4f46e5']] as [$l,$d,$ic,$col])
      <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:12px">
        <i class="bi bi-{{ $ic }}" style="color:{{ $d?$col:'#e2e8f0' }};font-size:16px;flex-shrink:0;margin-top:1px"></i>
        <div><div style="font-size:12.5px;font-weight:600;color:{{ $d?'var(--dark)':'var(--muted)' }}">{{ $l }}</div><div style="font-size:11.5px;color:var(--muted)">{{ $d?->format('d M Y H:i') ?? 'Pending' }}</div></div>
      </div>
      @endforeach
    </div>
  </div>

</div>
</div>

{{-- Request Info Modal --}}
<div class="mo" id="infoModal"><div class="mb">
  <div class="mh"><span class="mt">Request Additional Information</span><button class="mc" onclick="closeModal('infoModal')">×</button></div>
  <form method="POST" action="{{ route('officer.applications.request-info',$application) }}">@csrf
    <div class="mbody"><div class="fg"><label class="fl">Message to Borrower *</label><textarea name="message" class="fc" rows="4" required placeholder="Describe what additional information or documents you need..."></textarea></div></div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('infoModal')">Cancel</button><button type="submit" class="btn btn-i"><i class="bi bi-send"></i> Send Request</button></div>
  </form>
</div></div>

{{-- Route to Admin Modal --}}
<div class="mo" id="routeModal"><div class="mb">
  <div class="mh"><span class="mt">Route to Admin for Decision</span><button class="mc" onclick="closeModal('routeModal')">×</button></div>
  <form method="POST" action="{{ route('officer.applications.route-to-admin',$application) }}">@csrf
    <div class="mbody">
      <div class="alert a-ok" style="margin-bottom:16px"><i class="bi bi-check-circle-fill"></i> This will notify admin that the application is ready for approval/decline.</div>
      <div class="fg"><label class="fl">Additional Notes (optional)</label><textarea name="notes" class="fc" rows="3" placeholder="Any specific notes for the admin..."></textarea></div>
    </div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('routeModal')">Cancel</button><button type="submit" class="btn btn-p"><i class="bi bi-arrow-right-circle"></i> Route to Admin</button></div>
  </form>
</div></div>

@endsection
