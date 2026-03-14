@extends('admin.layouts.app')
@section('title','Application #'.$application->application_number)
@section('page-title','Application Review')
@section('bc','<a href="'.route('admin.applications.index').'">Applications</a> / #'.$application->application_number)
@section('content')

@php
$isPending = in_array($application->status, ['submitted','under_review','info_requested','on_hold']);
$badgeMap  = ['submitted'=>['#6366f1','#ede9fe'],'under_review'=>['#0891b2','#e0f2fe'],'info_requested'=>['#d97706','#fef3c7'],'on_hold'=>['#d97706','#fef3c7'],'approved'=>['#059669','#d1fae5'],'declined'=>['#dc2626','#fee2e2'],'disbursed'=>['#2563eb','#dbeafe']];
[$tc,$bc] = $badgeMap[$application->status] ?? ['#64748b','#f1f5f9'];
@endphp

{{-- Flash --}}
@foreach(['success'=>'ok','info'=>'i','error'=>'e'] as $type=>$cls)
@if(session($type))
<div style="background:rgba({{ $cls==='ok'?'16,185,129':($cls==='e'?'239,68,68':'6,182,212') }},.08);border:1px solid rgba({{ $cls==='ok'?'16,185,129':($cls==='e'?'239,68,68':'6,182,212') }},.2);color:{{ $cls==='ok'?'#065f46':($cls==='e'?'#991b1b':'#0e7490') }};padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px;margin-bottom:18px">
  <i class="bi bi-{{ $cls==='ok'?'check-circle-fill':($cls==='e'?'x-circle-fill':'info-circle-fill') }}"></i> {{ session($type) }}
</div>
@endif
@endforeach

{{-- TOP HEADER BAR --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
  <div style="display:flex;align-items:center;gap:16px">
    <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:22px;flex-shrink:0">
      {{ strtoupper(substr($application->applicant_name,0,1)) }}
    </div>
    <div>
      <div style="font-size:19px;font-weight:800;color:var(--dark)">{{ $application->applicant_name }}</div>
      <div style="font-size:13px;color:var(--muted);margin-top:2px">
        {{ $application->application_number }} &nbsp;·&nbsp; {{ $application->user->email ?? $application->email ?? '—' }}
        @if($application->loanProduct)
        &nbsp;·&nbsp; {{ $application->loanProduct->name }}
        @endif
      </div>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <span style="background:{{ $bc }};color:{{ $tc }};font-size:13px;font-weight:700;padding:6px 16px;border-radius:20px">
      {{ ucfirst(str_replace('_',' ',$application->status)) }}
    </span>
    @if($isPending)
      <button onclick="openModal('aModal')" class="btn btn-ok btn-sm"><i class="bi bi-check-lg"></i> Approve</button>
      <button onclick="openModal('dModal')" class="btn btn-e btn-sm"><i class="bi bi-x-lg"></i> Decline</button>
      <button onclick="openModal('hModal')" class="btn btn-w btn-sm"><i class="bi bi-pause-fill"></i> Hold</button>
      <button onclick="openModal('iModal')" class="btn btn-o btn-sm"><i class="bi bi-question-circle"></i> Request Info</button>
      @if($application->status !== 'under_review')
        <form method="POST" action="{{ route('admin.applications.mark-under-review',$application) }}" style="display:inline">@csrf<button class="btn btn-i btn-sm"><i class="bi bi-eye"></i> Mark Under Review</button></form>
      @endif
    @endif
    @if(in_array($application->status,['declined','on_hold']))
      <form method="POST" action="{{ route('admin.applications.reinstate',$application) }}" style="display:inline">@csrf<button class="btn btn-o btn-sm"><i class="bi bi-arrow-counterclockwise"></i> Reinstate</button></form>
    @endif
    @if($application->loan)
      <a href="{{ route('admin.loans.show',$application->loan) }}" class="btn btn-p btn-sm"><i class="bi bi-bank"></i> View Loan</a>
    @endif
  </div>
</div>

{{-- QUICK STATS ROW --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px">
  @foreach([
    ['Requested','L '.number_format($application->requested_amount??0,0),'currency-dollar','#4f46e5'],
    ['Term',$application->requested_term.' months','calendar3','#0891b2'],
    ['Risk Score',$application->risk_score??'—','graph-up','#f59e0b'],
    ['Submitted',$application->submitted_at?->format('d M Y')??'Draft','clock','#64748b'],
    ['Officer',$application->assignedOfficer?->name??'Unassigned','person-badge','#10b981'],
  ] as [$label,$val,$icon,$color])
  <div style="background:#fff;border:1px solid var(--border);border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:11px">
    <div style="width:36px;height:36px;border-radius:10px;background:{{ $color }}18;display:flex;align-items:center;justify-content:center;color:{{ $color }};font-size:16px;flex-shrink:0"><i class="bi bi-{{ $icon }}"></i></div>
    <div><div style="font-size:14px;font-weight:700;color:var(--dark)">{{ $val }}</div><div style="font-size:11px;color:var(--muted)">{{ $label }}</div></div>
  </div>
  @endforeach
</div>

{{-- MAIN LAYOUT --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:18px;align-items:start">

  {{-- LEFT: Tab content --}}
  <div>
    {{-- Tabs --}}
    <div class="tabs">
      <button class="tab active" data-tg="app" data-t="personal" onclick="switchTab('app','personal')"><i class="bi bi-person"></i> Personal</button>
      <button class="tab" data-tg="app" data-t="loan" onclick="switchTab('app','loan')"><i class="bi bi-bank"></i> Loan Details</button>
      <button class="tab" data-tg="app" data-t="afford" onclick="switchTab('app','afford')"><i class="bi bi-calculator"></i> Affordability</button>
      <button class="tab" data-tg="app" data-t="docs" onclick="switchTab('app','docs')"><i class="bi bi-files"></i> Documents <span style="background:var(--bg);color:var(--muted);font-size:10px;padding:1px 6px;border-radius:10px;margin-left:2px">{{ $application->documents->count() }}</span></button>
      <button class="tab" data-tg="app" data-t="notes" onclick="switchTab('app','notes')"><i class="bi bi-chat-text"></i> Notes <span style="background:var(--bg);color:var(--muted);font-size:10px;padding:1px 6px;border-radius:10px;margin-left:2px">{{ $application->notes->count() }}</span></button>
      <button class="tab" data-tg="app" data-t="schedule" onclick="switchTab('app','schedule')"><i class="bi bi-table"></i> Schedule</button>
    </div>

    {{-- Personal tab --}}
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

      @if($application->employment)
      <div class="card" style="margin-bottom:16px">
        <div class="card-hdr"><span class="card-title">Employment Details</span></div>
        <div class="card-body">
          <div class="info-grid">
            @foreach(['Employer'=>$application->employment->employer_name,'Employer Type'=>$application->employment->employer_type,'Department'=>$application->employment->department,'Job Title'=>$application->employment->job_title,'Contact #'=>$application->employment->contact_number,'Employee #'=>$application->employment->employment_number] as $l=>$v)
            <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
            @endforeach
          </div>
        </div>
      </div>
      @endif

      @if($application->bankDetails)
      <div class="card">
        <div class="card-hdr"><span class="card-title">Bank Details</span></div>
        <div class="card-body">
          <div class="info-grid">
            @foreach(['Bank'=>$application->bankDetails->bank_name,'Account Holder'=>$application->bankDetails->account_holder_name,'Account #'=>'••••'.substr($application->bankDetails->account_number??'',-4),'Account Type'=>ucfirst($application->bankDetails->account_type??'')] as $l=>$v)
            <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
            @endforeach
          </div>
        </div>
      </div>
      @endif
    </div>

    {{-- Loan Details tab --}}
    <div class="tpanel" data-pg="app" data-p="loan">

      {{-- ── AFFORDABILITY CHECK ─────────────────────────────────────── --}}
      @php
        $afford = app(\App\Services\Admin\ApplicationService::class)->checkAffordability($application);
      @endphp
      @if($afford['net_salary'] > 0)
        @if(!$afford['passes'])
        <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:12px;padding:14px 18px;margin-bottom:14px;display:flex;align-items:flex-start;gap:12px">
          <i class="bi bi-exclamation-triangle-fill" style="color:#d97706;font-size:18px;flex-shrink:0;margin-top:1px"></i>
          <div>
            <div style="font-weight:700;color:#92400e;font-size:13px">Affordability Warning</div>
            <div style="font-size:13px;color:#78350f;margin-top:3px">{{ $afford['warning'] }}</div>
            <div style="font-size:12px;color:#92400e;margin-top:4px">
              Net Salary: M{{ number_format($afford['net_salary'],2) }} &nbsp;·&nbsp;
              30% Limit: M{{ number_format($afford['max_allowed'],2) }} &nbsp;·&nbsp;
              Monthly Installment: M{{ number_format($afford['monthly'],2) }}
            </div>
          </div>
        </div>
        @else
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:12px 18px;margin-bottom:14px;display:flex;align-items:center;gap:10px">
          <i class="bi bi-check-circle-fill" style="color:#10b981;font-size:16px"></i>
          <div style="font-size:13px;color:#065f46">
            <strong>Affordability OK</strong> — Monthly M{{ number_format($afford['monthly'],2) }} is within the 30% limit of M{{ number_format($afford['max_allowed'],2) }} (Net salary: M{{ number_format($afford['net_salary'],2) }})
          </div>
        </div>
        @endif
      @endif

      <div class="card" style="margin-bottom:16px">
        <div class="card-hdr">
          <span class="card-title">Requested Loan Terms</span>
          @if($isPending)
          <button onclick="openModal('ovModal')" class="btn btn-sm btn-o"><i class="bi bi-pencil"></i> Override Terms</button>
          @endif
        </div>
        <div class="card-body">
          <div class="info-grid">
            @foreach(['Product'=>$application->loanProduct?->name,'Amount'=>'L '.number_format($application->requested_amount??0,2),'Term'=>($application->requested_term??0).' months','Purpose'=>$application->loan_purpose,'Payout'=>ucfirst(str_replace('_',' ',$application->payout_method??'')),'Collection'=>ucfirst(str_replace('_',' ',$application->collection_method??''))] as $l=>$v)
            <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
            @endforeach
          </div>
        </div>
      </div>

      @if($application->approved_amount)
      <div style="background:linear-gradient(135deg,rgba(16,185,129,.07),rgba(5,150,105,.04));border:1px solid rgba(16,185,129,.25);border-radius:14px;padding:20px;margin-bottom:16px">
        <div style="font-weight:700;color:#065f46;margin-bottom:14px;display:flex;align-items:center;gap:7px"><i class="bi bi-check-circle-fill" style="color:#10b981"></i> Approved Terms</div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;text-align:center">
          <div><div style="font-size:11px;color:#065f46;font-weight:600;text-transform:uppercase;letter-spacing:.06em">Amount</div><div style="font-size:22px;font-weight:800;color:#059669">L{{ number_format($application->approved_amount,0) }}</div></div>
          <div><div style="font-size:11px;color:#065f46;font-weight:600;text-transform:uppercase;letter-spacing:.06em">Term</div><div style="font-size:22px;font-weight:800;color:#059669">{{ $application->approved_term }}mo</div></div>
          <div><div style="font-size:11px;color:#065f46;font-weight:600;text-transform:uppercase;letter-spacing:.06em">Rate</div><div style="font-size:22px;font-weight:800;color:#059669">{{ $application->approved_interest_rate }}%</div></div>
          <div><div style="font-size:11px;color:#065f46;font-weight:600;text-transform:uppercase;letter-spacing:.06em">Disburse</div><div style="font-size:16px;font-weight:700;color:#059669;margin-top:4px">{{ $application->disbursement_date?->format('d M Y') }}</div></div>
        </div>
      </div>
      @endif

      {{-- Risk score setter --}}
      @if($isPending)
      <div class="card">
        <div class="card-hdr"><span class="card-title">Risk Assessment</span></div>
        <div class="card-body">
          <form method="POST" action="{{ route('admin.applications.set-risk-score',$application) }}" style="display:flex;gap:10px;align-items:flex-end">
            @csrf
            <div class="fg" style="margin-bottom:0;flex:1">
              <label class="fl">Risk Score (0–1000)</label>
              <input type="number" name="risk_score" class="fc" min="0" max="1000" value="{{ $application->risk_score }}" placeholder="e.g. 720">
            </div>
            <button type="submit" class="btn btn-p">Update Score</button>
          </form>
          @if($application->risk_score)
          <div style="margin-top:14px">
            @php
            $rs = $application->risk_score;
            $rp = min(100, $rs/10);
            $rc = $rs >= 700 ? '#10b981' : ($rs >= 500 ? '#f59e0b' : '#ef4444');
            $rl = $rs >= 700 ? 'Low Risk' : ($rs >= 500 ? 'Moderate Risk' : 'High Risk');
            @endphp
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:5px">
              <span style="color:var(--muted)">Score: <strong style="color:{{ $rc }}">{{ $rs }}</strong></span>
              <span style="color:{{ $rc }};font-weight:600">{{ $rl }}</span>
            </div>
            <div style="height:8px;background:var(--bg);border-radius:99px;overflow:hidden">
              <div style="height:100%;width:{{ $rp }}%;background:{{ $rc }};border-radius:99px;transition:width .5s"></div>
            </div>
          </div>
          @endif
        </div>
      </div>
      @endif
    </div>

    {{-- Affordability tab --}}
    <div class="tpanel" data-pg="app" data-p="afford">
      <div class="card">
        <div class="card-hdr"><span class="card-title">Affordability Assessment</span></div>
        <div class="card-body">
          @if($application->affordability)
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
            <div style="background:#f8fafc;border-radius:12px;padding:16px">
              <div style="font-weight:700;margin-bottom:12px;font-size:13px;color:var(--dark)">Income & Deductions</div>
              @foreach(['Monthly Earnings'=>$application->affordability->monthly_earnings,'Tax Deduction'=>$application->affordability->tax_deduction,'Existing Loans'=>$application->affordability->existing_loans_deduction,'Other Deductions'=>$application->affordability->other_deductions,'Net Salary'=>$application->affordability->net_salary] as $l=>$v)
              <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
                <span style="color:var(--muted)">{{ $l }}</span>
                <span style="font-weight:600;{{ $l==='Net Salary'?'color:var(--p)':'' }}">L {{ number_format($v??0,2) }}</span>
              </div>
              @endforeach
            </div>
            <div style="background:#f8fafc;border-radius:12px;padding:16px">
              <div style="font-weight:700;margin-bottom:12px;font-size:13px;color:var(--dark)">Monthly Expenses</div>
              @foreach(['Transport'=>$application->affordability->transport,'Groceries'=>$application->affordability->groceries,'Utilities'=>$application->affordability->utilities,'Rent/Mortgage'=>$application->affordability->rent,'Other Expenses'=>$application->affordability->other_expenses,'Disposable Income'=>$application->affordability->disposable_income] as $l=>$v)
              <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
                <span style="color:var(--muted)">{{ $l }}</span>
                <span style="font-weight:600;{{ $l==='Disposable Income'?'color:#10b981':'' }}">L {{ number_format($v??0,2) }}</span>
              </div>
              @endforeach
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div style="background:rgba(79,70,229,.06);border-radius:12px;padding:16px;text-align:center;border:1px solid rgba(79,70,229,.12)">
              <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em">Suggested Loan</div>
              <div style="font-size:26px;font-weight:800;color:var(--p);margin-top:6px">L {{ number_format($application->affordability->suggested_loan_amount??0,0) }}</div>
            </div>
            <div style="background:rgba(16,185,129,.06);border-radius:12px;padding:16px;text-align:center;border:1px solid rgba(16,185,129,.12)">
              <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em">Max Loan</div>
              <div style="font-size:26px;font-weight:800;color:#10b981;margin-top:6px">L {{ number_format($application->affordability->max_loan_amount??0,0) }}</div>
            </div>
          </div>
          @else
          <div style="text-align:center;padding:50px;color:var(--muted)"><i class="bi bi-calculator" style="font-size:44px;opacity:.25;display:block;margin-bottom:12px"></i><div style="font-weight:600">No affordability data</div></div>
          @endif
        </div>
      </div>
    </div>

    {{-- Documents tab --}}
    <div class="tpanel" data-pg="app" data-p="docs">
      <div class="card">
        <div class="card-hdr"><span class="card-title">Documents ({{ $application->documents->count() }})</span></div>
        <div class="card-body">
          @forelse($application->documents as $doc)
          <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;border:1px solid var(--border);border-radius:12px;margin-bottom:10px;transition:background .15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
            <div style="display:flex;align-items:center;gap:12px">
              <div style="width:42px;height:42px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#64748b">
                <i class="bi bi-file-earmark-{{ str_contains($doc->mime_type??'','pdf')?'pdf':'text' }}-fill"></i>
              </div>
              <div>
                <div style="font-weight:600;font-size:13px">{{ ucfirst(str_replace('_',' ',$doc->type)) }}</div>
                <div style="font-size:11.5px;color:var(--muted)">{{ $doc->original_name }} &nbsp;·&nbsp; {{ $doc->size ? round($doc->size/1024,1).'KB' : '—' }}</div>
              </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <span class="badge {{ $doc->status==='verified'?'bok':($doc->status==='rejected'?'be':'bw') }}">
                <i class="bi bi-{{ $doc->status==='verified'?'check-circle':'clock' }}" style="font-size:10px"></i> {{ ucfirst($doc->status) }}
              </span>
              @if($doc->status==='pending')
              <form method="POST" action="{{ route('admin.applications.documents.verify',[$application,$doc]) }}" style="display:inline">@csrf<button class="btn btn-xs btn-ok"><i class="bi bi-check"></i></button></form>
              <form method="POST" action="{{ route('admin.applications.documents.reject',[$application,$doc]) }}" style="display:inline">@csrf<button class="btn btn-xs btn-e"><i class="bi bi-x"></i></button></form>
              @endif
              <a href="{{ route('admin.applications.documents.download',[$application,$doc]) }}" class="btn btn-xs btn-o"><i class="bi bi-download"></i></a>
            </div>
          </div>
          @empty
          <div style="text-align:center;padding:48px;color:var(--muted)"><i class="bi bi-file-earmark-x" style="font-size:44px;opacity:.25;display:block;margin-bottom:12px"></i><div style="font-weight:600">No documents uploaded</div></div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Notes tab --}}
    <div class="tpanel" data-pg="app" data-p="notes">
      {{-- Add note form --}}
      <div class="card" style="margin-bottom:16px">
        <div class="card-hdr"><span class="card-title">Add Note</span></div>
        <div class="card-body">
          <form method="POST" action="{{ route('admin.applications.notes.store',$application) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
              <div class="fg" style="margin-bottom:0">
                <label class="fl">Type</label>
                <select name="type" class="fc">
                  @foreach(['general'=>'General','approval'=>'Approval','decision'=>'Decision','status'=>'Status Update','info_request'=>'Info Request','override'=>'Override'] as $v=>$l)
                  <option value="{{ $v }}">{{ $l }}</option>
                  @endforeach
                </select>
              </div>
              <div class="fg" style="margin-bottom:0;display:flex;align-items:flex-end">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600;padding-bottom:9px">
                  <input type="checkbox" name="is_internal" value="1" checked style="width:16px;height:16px;accent-color:var(--p)"> Internal (not visible to borrower)
                </label>
              </div>
            </div>
            <div class="fg" style="margin-bottom:12px">
              <label class="fl">Note *</label>
              <textarea name="content" class="fc" rows="3" placeholder="Add a note about this application…" required></textarea>
            </div>
            <button type="submit" class="btn btn-p btn-sm"><i class="bi bi-plus-lg"></i> Add Note</button>
          </form>
        </div>
      </div>

      {{-- Notes list --}}
      <div class="card">
        <div class="card-hdr"><span class="card-title">Notes History ({{ $application->notes->count() }})</span></div>
        <div class="card-body" style="padding-bottom:10px">
          @forelse($application->notes as $note)
          <div style="padding:14px 16px;border-left:3px solid {{ $note->is_internal?'#f59e0b':'#4f46e5' }};background:#f8fafc;border-radius:0 12px 12px 0;margin-bottom:10px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
              <span style="font-size:11px;font-weight:700;color:{{ $note->is_internal?'#d97706':'var(--p)' }};text-transform:uppercase;letter-spacing:.06em;background:{{ $note->is_internal?'rgba(245,158,11,.12)':'rgba(79,70,229,.08)' }};padding:2px 8px;border-radius:99px">
                {{ str_replace('_',' ',strtoupper($note->type)) }}
                @if($note->is_internal) · Internal @endif
              </span>
              <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:11.5px;color:var(--muted)">{{ $note->created_at->format('d M Y H:i') }} · {{ $note->createdBy?->name ?? '—' }}</span>
                <form method="POST" action="{{ route('admin.applications.notes.destroy',[$application,$note]) }}" style="display:inline">
                  @csrf @method('DELETE')
                  <button class="btn btn-xs" style="background:none;border:none;color:var(--muted);cursor:pointer;padding:2px 5px" title="Delete"><i class="bi bi-trash"></i></button>
                </form>
              </div>
            </div>
            <div style="font-size:13px;color:#334155;line-height:1.6">{{ $note->content }}</div>
          </div>
          @empty
          <div style="text-align:center;padding:40px;color:var(--muted)"><i class="bi bi-chat-left-dots" style="font-size:40px;opacity:.25;display:block;margin-bottom:10px"></i><div>No notes yet</div></div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Schedule tab --}}
    <div class="tpanel" data-pg="app" data-p="schedule">
      <div class="card">
        <div class="card-hdr"><span class="card-title">Repayment Schedule Preview</span></div>
        <div class="card-body">
          <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
            <div class="fg" style="margin-bottom:0">
              <label class="fl">Amount (L)</label>
              <input type="number" id="schAmt" class="fc" style="width:150px" value="{{ $application->approved_amount ?? $application->requested_amount }}" step="0.01">
            </div>
            <div class="fg" style="margin-bottom:0">
              <label class="fl">Rate (%/mo)</label>
              <input type="number" id="schRate" class="fc" style="width:130px" value="{{ $application->approved_interest_rate ?? $application->loanProduct?->interest_rate ?? 0 }}" step="0.01">
            </div>
            <div class="fg" style="margin-bottom:0">
              <label class="fl">Term (months)</label>
              <input type="number" id="schTerm" class="fc" style="width:130px" value="{{ $application->approved_term ?? $application->requested_term }}">
            </div>
            <div style="display:flex;align-items:flex-end">
              <button onclick="previewSchedule()" class="btn btn-p"><i class="bi bi-table"></i> Generate</button>
            </div>
          </div>
          <div id="scheduleResult" style="overflow-x:auto"></div>
        </div>
      </div>
    </div>

  </div>

  {{-- RIGHT: Sidebar --}}
  <div>

    {{-- Timeline --}}
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title">Timeline</span></div>
      <div style="padding:16px">
        @php
        $timeline = [
          ['Submitted',   $application->submitted_at,  'send',           '#4f46e5'],
          ['Reviewed',    $application->reviewed_at,   'eye',            '#0891b2'],
          ['Decided',     $application->decided_at,    'check-circle',   '#10b981'],
        ];
        @endphp
        @foreach($timeline as [$label,$date,$icon,$color])
        <div style="display:flex;gap:12px;padding:8px 0;{{ !$loop->last?'border-bottom:1px solid var(--border)':'' }}">
          <div style="width:30px;height:30px;border-radius:50%;background:{{ $date?$color.'18':'var(--bg)' }};display:flex;align-items:center;justify-content:center;color:{{ $date?$color:'var(--muted)' }};font-size:13px;flex-shrink:0">
            <i class="bi bi-{{ $icon }}"></i>
          </div>
          <div>
            <div style="font-size:12.5px;font-weight:600;color:{{ $date?'var(--dark)':'var(--muted)' }}">{{ $label }}</div>
            <div style="font-size:11.5px;color:var(--muted)">{{ $date?->format('d M Y H:i') ?? 'Pending' }}</div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Assign officer --}}
    @if($isPending)
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title">Assign Officer</span></div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.applications.assign-officer',$application) }}">
          @csrf
          <div class="fg" style="margin-bottom:10px">
            <select name="officer_id" class="fc">
              <option value="">— Select Officer —</option>
              @foreach($officers as $o)
              <option value="{{ $o->id }}" {{ $application->assigned_officer_id===$o->id?'selected':'' }}>{{ $o->name }}</option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="btn btn-p btn-sm" style="width:100%;justify-content:center"><i class="bi bi-person-check"></i> Assign</button>
        </form>
        @if($application->assignedOfficer)
        <form method="POST" action="{{ route('admin.applications.unassign-officer',$application) }}" style="margin-top:8px">
          @csrf
          <button class="btn btn-o btn-sm" style="width:100%;justify-content:center"><i class="bi bi-person-x"></i> Unassign</button>
        </form>
        @endif
      </div>
    </div>
    @endif

    {{-- Next of Kin --}}
    @if($application->nextOfKin->count())
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title">Next of Kin</span></div>
      <div style="padding:14px 18px">
        @foreach($application->nextOfKin as $i=>$k)
        <div style="{{ $i>0?'margin-top:12px;padding-top:12px;border-top:1px solid var(--border)':'' }}">
          <div style="font-weight:700;font-size:13px">{{ $k->first_name }} {{ $k->last_name }}</div>
          <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ $k->relationship }}</div>
          <div style="font-size:12px;color:var(--muted)">{{ $k->contact_number }}</div>
        </div>
        @endforeach
      </div>
    </div>
    @endif

    {{-- Admin notes --}}
    @if($application->admin_notes)
    <div class="card">
      <div class="card-hdr"><span class="card-title">Admin Notes</span></div>
      <div style="padding:14px 18px;font-size:13px;color:#334155;line-height:1.6">{{ $application->admin_notes }}</div>
    </div>
    @endif

  </div>
</div>

{{-- ===== MODALS ===== --}}

{{-- APPROVE --}}
<div class="mo" id="aModal">
  <div class="mb" style="max-width:560px">
    <div class="mh"><span class="mt"><i class="bi bi-check-circle-fill" style="color:#10b981"></i> Approve & Create Loan</span><button class="mc" onclick="closeModal('aModal')">&times;</button></div>
    <form method="POST" action="{{ route('admin.applications.approve',$application) }}">
      @csrf
      <div class="mbody">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="fg"><label class="fl">Approved Amount (L) *</label><input type="number" name="approved_amount" class="fc" value="{{ $application->requested_amount }}" step="0.01" min="1" required oninput="liveCalc()"></div>
          <div class="fg"><label class="fl">Term (months) *</label><input type="number" name="approved_term" class="fc" id="mTerm" value="{{ $application->requested_term }}" min="1" max="120" required oninput="liveCalc()"></div>
          <div class="fg"><label class="fl">Interest Rate (%/month) *</label><input type="number" name="interest_rate" class="fc" id="mRate" value="{{ $application->loanProduct?->interest_rate }}" step="0.01" min="0" required oninput="liveCalc()"></div>
          <div class="fg"><label class="fl">Disbursement Date *</label><input type="date" name="disbursement_date" class="fc" value="{{ now()->addDay()->format('Y-m-d') }}" required></div>
        </div>
        <div id="modalCalc" style="background:#f0fdf4;border:1px solid #d1fae5;border-radius:10px;padding:12px 14px;margin-bottom:6px;display:flex;gap:20px;flex-wrap:wrap">
          <div style="text-align:center"><div style="font-size:11px;color:#065f46;font-weight:600">MONTHLY</div><div style="font-size:18px;font-weight:800;color:#059669" id="mc-monthly">—</div></div>
          <div style="text-align:center"><div style="font-size:11px;color:#065f46;font-weight:600">TOTAL</div><div style="font-size:18px;font-weight:800;color:#059669" id="mc-total">—</div></div>
          <div style="text-align:center"><div style="font-size:11px;color:#065f46;font-weight:600">INTEREST</div><div style="font-size:18px;font-weight:800;color:#059669" id="mc-interest">—</div></div>
        </div>
        <div id="mc-afford" style="display:none;background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:8px 12px;font-size:12px;color:#92400e;margin-bottom:10px"></div>
        <div class="fg"><label class="fl">Approval Notes</label><textarea name="notes" class="fc" rows="2" placeholder="Optional internal notes…"></textarea></div>
      </div>
      <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('aModal')">Cancel</button><button type="submit" class="btn btn-ok"><i class="bi bi-check-lg"></i> Approve & Create Loan</button></div>
    </form>
  </div>
</div>

{{-- DECLINE --}}
<div class="mo" id="dModal">
  <div class="mb">
    <div class="mh"><span class="mt"><i class="bi bi-x-circle-fill" style="color:#ef4444"></i> Decline Application</span><button class="mc" onclick="closeModal('dModal')">&times;</button></div>
    <form method="POST" action="{{ route('admin.applications.decline',$application) }}">
      @csrf
      <div class="mbody"><div class="fg"><label class="fl">Decline Reason *</label><textarea name="reason" class="fc" rows="4" placeholder="Explain why this application is being declined…" required></textarea></div></div>
      <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('dModal')">Cancel</button><button type="submit" class="btn btn-e"><i class="bi bi-x-lg"></i> Decline</button></div>
    </form>
  </div>
</div>

{{-- HOLD --}}
<div class="mo" id="hModal">
  <div class="mb">
    <div class="mh"><span class="mt"><i class="bi bi-pause-fill" style="color:#f59e0b"></i> Place On Hold</span><button class="mc" onclick="closeModal('hModal')">&times;</button></div>
    <form method="POST" action="{{ route('admin.applications.hold',$application) }}">
      @csrf
      <div class="mbody"><div class="fg"><label class="fl">Reason *</label><textarea name="reason" class="fc" rows="3" required></textarea></div></div>
      <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('hModal')">Cancel</button><button type="submit" class="btn btn-w"><i class="bi bi-pause-fill"></i> Hold</button></div>
    </form>
  </div>
</div>

{{-- REQUEST INFO --}}
<div class="mo" id="iModal">
  <div class="mb">
    <div class="mh"><span class="mt"><i class="bi bi-question-circle-fill" style="color:#4f46e5"></i> Request Information</span><button class="mc" onclick="closeModal('iModal')">&times;</button></div>
    <form method="POST" action="{{ route('admin.applications.request-info',$application) }}">
      @csrf
      <div class="mbody"><div class="fg"><label class="fl">Message to Borrower *</label><textarea name="message" class="fc" rows="4" placeholder="Describe what additional information is needed…" required></textarea></div></div>
      <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('iModal')">Cancel</button><button type="submit" class="btn btn-p"><i class="bi bi-send"></i> Send Request</button></div>
    </form>
  </div>
</div>

{{-- OVERRIDE TERMS --}}
<div class="mo" id="ovModal">
  <div class="mb">
    <div class="mh"><span class="mt"><i class="bi bi-pencil-fill" style="color:#4f46e5"></i> Override Loan Terms</span><button class="mc" onclick="closeModal('ovModal')">&times;</button></div>
    <form method="POST" action="{{ route('admin.applications.override-terms',$application) }}">
      @csrf
      <div class="mbody">
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
          <div class="fg"><label class="fl">Amount (L)</label><input type="number" name="loan_amount" class="fc" value="{{ $application->requested_amount }}" step="0.01"></div>
          <div class="fg"><label class="fl">Rate (%/mo)</label><input type="number" name="interest_rate" class="fc" value="{{ $application->loanProduct?->interest_rate }}" step="0.01"></div>
          <div class="fg"><label class="fl">Term (months)</label><input type="number" name="term_months" class="fc" value="{{ $application->requested_term }}"></div>
        </div>
      </div>
      <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('ovModal')">Cancel</button><button type="submit" class="btn btn-p">Update Terms</button></div>
    </form>
  </div>
</div>

<script>
// ── FLAT interest calculator (MyLoan rules) ──────────────────────────────
// interest = principal × rate × term  (flat, same every month)
// initiation = principal × 40%
// admin = M50 × term
// total = principal + interest + initiation + admin
// monthly = total ÷ term
const INITIATION_RATE = {{ $application->loanProduct?->initiation_fee_rate ?? 40 }} / 100;
const ADMIN_PER_MONTH = {{ $application->loanProduct?->admin_fee_fixed ?? 50 }};

function flatCalc(amount, ratePercent, term) {
  if (!amount || !term) return null;
  const rate       = ratePercent / 100;
  const interest   = amount * rate * term;
  const initiation = amount * INITIATION_RATE;
  const admin      = ADMIN_PER_MONTH * term;
  const total      = amount + interest + initiation + admin;
  const monthly    = total / term;
  return { rate, interest, initiation, admin, total, monthly,
           principalPerMonth: amount/term,
           interestPerMonth:  amount*rate,
           initiationPerMonth: initiation/term };
}

// Live calculator in approve modal
function liveCalc() {
  const amount = parseFloat(document.querySelector('[name=approved_amount]')?.value || 0);
  const term   = parseInt(document.getElementById('mTerm')?.value || 0);
  const rate   = parseFloat(document.getElementById('mRate')?.value || 0);
  const c = flatCalc(amount, rate, term);
  if (!c) return;
  document.getElementById('mc-monthly').textContent   = 'M ' + c.monthly.toFixed(2);
  document.getElementById('mc-total').textContent     = 'M ' + c.total.toFixed(2);
  document.getElementById('mc-interest').textContent  = 'M ' + c.interest.toFixed(2);
  // Show affordability warning inline
  const netSal = {{ (float)($application->affordabilityAssessment?->net_salary ?? 0) }};
  const warn = document.getElementById('mc-afford');
  if (warn && netSal > 0) {
    const limit = netSal * 0.3;
    if (c.monthly > limit) {
      warn.style.display = 'block';
      warn.textContent   = '⚠ Monthly M'+c.monthly.toFixed(2)+' exceeds 30% limit M'+limit.toFixed(2);
    } else {
      warn.style.display = 'none';
    }
  }
}

// Schedule preview tab — uses flat interest
function previewSchedule() {
  const amount = parseFloat(document.getElementById('schAmt').value || 0);
  const rateP  = parseFloat(document.getElementById('schRate').value || 0);
  const term   = parseInt(document.getElementById('schTerm').value || 0);
  const c = flatCalc(amount, rateP, term);
  if (!c) return;

  let rows = '';
  let remainPrincipal = amount;

  for (let i = 1; i <= term; i++) {
    const isLast = i === term;
    const prin   = isLast ? parseFloat(remainPrincipal.toFixed(2)) : parseFloat(c.principalPerMonth.toFixed(2));
    const init   = isLast ? parseFloat((c.initiation - c.initiationPerMonth*(term-1)).toFixed(2)) : parseFloat(c.initiationPerMonth.toFixed(2));
    const total  = parseFloat((prin + c.interestPerMonth + ADMIN_PER_MONTH + init).toFixed(2));
    remainPrincipal = Math.max(0, remainPrincipal - prin);

    rows += `<tr style="font-size:12.5px">
      <td style="padding:8px 10px;border-bottom:1px solid var(--border)">${i}</td>
      <td style="padding:8px 10px;border-bottom:1px solid var(--border);color:var(--p)">M ${prin.toFixed(2)}</td>
      <td style="padding:8px 10px;border-bottom:1px solid var(--border);color:#f59e0b">M ${c.interestPerMonth.toFixed(2)}</td>
      <td style="padding:8px 10px;border-bottom:1px solid var(--border);color:#8b5cf6">M ${init.toFixed(2)}</td>
      <td style="padding:8px 10px;border-bottom:1px solid var(--border);color:#64748b">M ${ADMIN_PER_MONTH.toFixed(2)}</td>
      <td style="padding:8px 10px;border-bottom:1px solid var(--border);font-weight:700">M ${total.toFixed(2)}</td>
      <td style="padding:8px 10px;border-bottom:1px solid var(--border)">M ${remainPrincipal.toFixed(2)}</td>
    </tr>`;
  }

  document.getElementById('scheduleResult').innerHTML = `
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px;text-align:center;font-size:13px">
      <div style="background:rgba(79,70,229,.06);border-radius:10px;padding:12px;border:1px solid rgba(79,70,229,.12)">
        <div style="font-size:11px;color:var(--muted)">Monthly Payment</div>
        <div style="font-size:19px;font-weight:800;color:var(--p)">M ${c.monthly.toFixed(2)}</div>
      </div>
      <div style="background:rgba(16,185,129,.06);border-radius:10px;padding:12px;border:1px solid rgba(16,185,129,.12)">
        <div style="font-size:11px;color:var(--muted)">Total Repayment</div>
        <div style="font-size:19px;font-weight:800;color:#10b981">M ${c.total.toFixed(2)}</div>
      </div>
      <div style="background:rgba(245,158,11,.06);border-radius:10px;padding:12px;border:1px solid rgba(245,158,11,.12)">
        <div style="font-size:11px;color:var(--muted)">Interest + Fees</div>
        <div style="font-size:19px;font-weight:800;color:#f59e0b">M ${(c.interest+c.initiation+c.admin).toFixed(2)}</div>
      </div>
      <div style="background:rgba(100,116,139,.06);border-radius:10px;padding:12px;border:1px solid rgba(100,116,139,.12)">
        <div style="font-size:11px;color:var(--muted)">Cash to Client</div>
        <div style="font-size:19px;font-weight:800;color:#475569">M ${amount.toFixed(2)}</div>
      </div>
    </div>
    <table style="width:100%;border-collapse:collapse">
      <thead><tr style="background:#f8fafc;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)">
        <th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">#</th>
        <th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Principal</th>
        <th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Interest</th>
        <th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Initiation</th>
        <th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Admin</th>
        <th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Total</th>
        <th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Balance</th>
      </tr></thead>
      <tbody>${rows}</tbody>
    </table>`;
}
</script>
@endsection
