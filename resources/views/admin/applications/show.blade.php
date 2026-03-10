@extends('admin.layouts.app')
@section('title','Review Application')@section('page-title','Application #'.$application->application_number)
@section('bc','<a href="'.route('admin.applications.index').'">Applications</a> / Review')
@section('content')
<div style="display:flex;gap:18px;align-items:flex-start">
<div style="flex:2;min-width:0">
  <div class="card mb4">
    <div style="padding:18px 22px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div class="flex aic gap3"><div class="av av-lg">{{ strtoupper(substr($application->user->name??'U',0,1)) }}</div>
        <div><div style="font-size:17px;font-weight:800">{{$application->first_name}} {{$application->surname}}</div><div class="muted">{{$application->user->email}} · {{$application->application_number}}</div></div>
      </div>
      <div class="flex gap2" style="flex-wrap:wrap">
        <span class="badge b{{$application->status_badge}}" style="font-size:12.5px;padding:5px 13px">{{ ucfirst(str_replace('_',' ',$application->status)) }}</span>
        @if(in_array($application->status,['submitted','under_review','on_hold','info_requested']))
        <button onclick="openModal('aModal')" class="btn btn-sm btn-ok"><i class="bi bi-check-lg"></i> Approve</button>
        <button onclick="openModal('dModal')" class="btn btn-sm btn-e"><i class="bi bi-x-lg"></i> Decline</button>
        <button onclick="openModal('hModal')" class="btn btn-sm btn-w"><i class="bi bi-pause-fill"></i> Hold</button>
        <button onclick="openModal('iModal')" class="btn btn-sm btn-o"><i class="bi bi-info-circle"></i> Request Info</button>
        @endif
      </div>
    </div>
  </div>
  <div class="tabs">
    <button class="tab active" data-tg="app" data-t="personal" onclick="switchTab('app','personal')"><i class="bi bi-person"></i> Personal</button>
    <button class="tab" data-tg="app" data-t="loan" onclick="switchTab('app','loan')"><i class="bi bi-currency-dollar"></i> Loan</button>
    <button class="tab" data-tg="app" data-t="afford" onclick="switchTab('app','afford')"><i class="bi bi-calculator"></i> Affordability</button>
    <button class="tab" data-tg="app" data-t="docs" onclick="switchTab('app','docs')"><i class="bi bi-files"></i> Docs ({{$application->documents->count()}})</button>
    <button class="tab" data-tg="app" data-t="notes" onclick="switchTab('app','notes')"><i class="bi bi-chat-text"></i> Notes ({{$application->notes->count()}})</button>
  </div>

  <div class="tpanel active" data-pg="app" data-p="personal">
    <div class="card mb4"><div class="card-hdr"><span class="card-title">Personal Info</span></div><div class="card-body"><div class="info-grid">
      @foreach(['Title'=>$application->title,'First Name'=>$application->first_name,'Surname'=>$application->surname,'National ID'=>$application->national_id,'Date of Birth'=>$application->date_of_birth?->format('d M Y'),'Gender'=>ucfirst($application->gender??''),'Marital Status'=>ucfirst($application->marital_status??''),'Cell'=>$application->cell_number,'Email'=>$application->email] as $l=>$v)
      <div><div class="info-lbl">{{$l}}</div><div class="info-val">{{$v??'—'}}</div></div>@endforeach
    </div></div></div>
    @if($application->employment)
    <div class="card mb4"><div class="card-hdr"><span class="card-title">Employment</span></div><div class="card-body"><div class="info-grid">
      @foreach(['Employer'=>$application->employment->employer_name,'Type'=>$application->employment->employer_type,'Dept'=>$application->employment->department,'Title'=>$application->employment->job_title,'Contact'=>$application->employment->contact_number,'Emp#'=>$application->employment->employment_number] as $l=>$v)
      <div><div class="info-lbl">{{$l}}</div><div class="info-val">{{$v??'—'}}</div></div>@endforeach
    </div></div></div>@endif
    @if($application->bankDetails)
    <div class="card"><div class="card-hdr"><span class="card-title">Bank Details</span></div><div class="card-body"><div class="info-grid">
      @foreach(['Bank'=>$application->bankDetails->bank_name,'Account Holder'=>$application->bankDetails->account_holder_name,'Acc#'=>'••••'.substr($application->bankDetails->account_number??'',-4),'Type'=>$application->bankDetails->account_type] as $l=>$v)
      <div><div class="info-lbl">{{$l}}</div><div class="info-val">{{$v??'—'}}</div></div>@endforeach
    </div></div></div>@endif
  </div>

  <div class="tpanel" data-pg="app" data-p="loan">
    <div class="card mb4">
      <div class="card-hdr"><span class="card-title">Loan Details</span>
        @if(in_array($application->status,['submitted','under_review','on_hold']))<button onclick="openModal('ovModal')" class="btn btn-sm btn-o"><i class="bi bi-pencil"></i> Override</button>@endif
      </div>
      <div class="card-body"><div class="info-grid">
        @foreach(['Product'=>$application->loanProduct?->name,'Amount'=>'L '.number_format($application->requested_amount??0,2),'Term'=>($application->requested_term??0).' months','Purpose'=>$application->loan_purpose,'Payout'=>ucfirst($application->payout_method??'—'),'Collection'=>ucfirst($application->collection_method??'—')] as $l=>$v)
        <div><div class="info-lbl">{{$l}}</div><div class="info-val">{{$v??'—'}}</div></div>@endforeach
      </div>
      @if($application->approved_amount)
      <div style="margin-top:18px;padding:14px;background:#f0fdf4;border-radius:11px;border:1px solid #d1fae5">
        <div style="font-weight:700;color:#065f46;margin-bottom:10px"><i class="bi bi-check-circle-fill"></i> Approved Terms</div>
        <div class="info-grid">
          <div><div class="info-lbl">Approved Amount</div><div class="info-val" style="color:#059669">L {{ number_format($application->approved_amount,2) }}</div></div>
          <div><div class="info-lbl">Term</div><div class="info-val">{{ $application->approved_term }} months</div></div>
          <div><div class="info-lbl">Rate</div><div class="info-val">{{ $application->approved_interest_rate }}%/mo</div></div>
          <div><div class="info-lbl">Disbursement</div><div class="info-val">{{ $application->disbursement_date?->format('d M Y') }}</div></div>
        </div>
      </div>@endif
    </div></div>
  </div>

  <div class="tpanel" data-pg="app" data-p="afford">
    <div class="card">
      <div class="card-hdr"><span class="card-title">Affordability Assessment</span></div>
      <div class="card-body">
        @if($application->affordability)
        <div class="g2" style="gap:14px">
          <div style="background:#f8fafc;border-radius:11px;padding:15px">
            <div style="font-weight:700;margin-bottom:10px;font-size:13px">Income & Deductions</div>
            @foreach(['Monthly Earnings'=>$application->affordability->monthly_earnings,'Tax'=>$application->affordability->tax_deduction,'Existing Loans'=>$application->affordability->existing_loans_deduction,'Other'=>$application->affordability->other_deductions] as $l=>$v)
            <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:12.5px;border-bottom:1px solid #e2e8f0"><span style="color:#64748b">{{$l}}</span><span style="font-weight:600">L {{ number_format($v??0,2) }}</span></div>@endforeach
            <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px;font-weight:700;color:#4f46e5"><span>Net Salary</span><span>L {{ number_format($application->affordability->net_salary??0,2) }}</span></div>
          </div>
          <div style="background:#f8fafc;border-radius:11px;padding:15px">
            <div style="font-weight:700;margin-bottom:10px;font-size:13px">Living Expenses</div>
            @foreach(['Transport'=>$application->affordability->transport,'Groceries'=>$application->affordability->groceries,'Utilities'=>$application->affordability->utilities,'Rent'=>$application->affordability->rent,'Other'=>$application->affordability->other_expenses] as $l=>$v)
            <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:12.5px;border-bottom:1px solid #e2e8f0"><span style="color:#64748b">{{$l}}</span><span style="font-weight:600">L {{ number_format($v??0,2) }}</span></div>@endforeach
            <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px;font-weight:700;color:#10b981"><span>Disposable</span><span>L {{ number_format($application->affordability->disposable_income??0,2) }}</span></div>
          </div>
        </div>
        <div style="display:flex;gap:14px;margin-top:14px">
          <div style="flex:1;background:rgba(79,70,229,.06);border-radius:11px;padding:14px;text-align:center;border:1px solid rgba(79,70,229,.1)"><div class="muted">Suggested</div><div style="font-size:22px;font-weight:800;color:#4f46e5">L {{ number_format($application->affordability->suggested_loan_amount??0,0) }}</div></div>
          <div style="flex:1;background:rgba(16,185,129,.06);border-radius:11px;padding:14px;text-align:center;border:1px solid rgba(16,185,129,.1)"><div class="muted">Max Loan</div><div style="font-size:22px;font-weight:800;color:#10b981">L {{ number_format($application->affordability->max_loan_amount??0,0) }}</div></div>
        </div>
        @else<div class="empty"><i class="bi bi-calculator"></i><p>No affordability data</p></div>@endif
      </div>
    </div>
  </div>

  <div class="tpanel" data-pg="app" data-p="docs">
    <div class="card"><div class="card-hdr"><span class="card-title">Documents</span></div><div class="card-body">
      @forelse($application->documents as $d)
      <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;border:1px solid var(--border);border-radius:10px;margin-bottom:9px">
        <div class="flex aic gap2"><div style="width:38px;height:38px;background:#f1f5f9;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#64748b"><i class="bi bi-file-earmark-pdf-fill"></i></div><div><div style="font-weight:600;font-size:13px">{{ ucfirst(str_replace('_',' ',$d->type)) }}</div><div class="muted">{{$d->original_name}}</div></div></div>
        <span class="badge {{ $d->status==='verified'?'bok':($d->status==='rejected'?'be':'bw') }}">{{ ucfirst($d->status) }}</span>
      </div>
      @empty<div class="empty"><i class="bi bi-file-earmark-x"></i><p>No documents</p></div>@endforelse
    </div></div>
  </div>

  <div class="tpanel" data-pg="app" data-p="notes">
    <div class="card"><div class="card-hdr"><span class="card-title">Notes</span></div><div class="card-body">
      @forelse($application->notes as $n)
      <div style="padding:12px;border-left:3px solid {{ $n->is_internal?'#f59e0b':'#4f46e5' }};background:#f8fafc;border-radius:0 10px 10px 0;margin-bottom:10px">
        <div class="flex jb mb4" style="flex-wrap:wrap;gap:6px"><span style="font-size:11.5px;font-weight:700;color:{{ $n->is_internal?'#f59e0b':'#4f46e5' }}">{{ strtoupper(str_replace('_',' ',$n->type)) }}</span><span class="muted">{{ $n->created_at->format('d M Y H:i') }} · {{ $n->createdBy?->name }}</span></div>
        <div style="font-size:13px;color:#334155">{{$n->content}}</div>
      </div>
      @empty<div class="empty"><i class="bi bi-chat-left-dots"></i><p>No notes</p></div>@endforelse
    </div></div>
  </div>
</div>

<div style="width:280px;flex-shrink:0">
  <div class="card mb4"><div class="card-hdr"><span class="card-title">Quick Info</span></div>
    <div style="padding:14px">
      @foreach(['Applied'=>$application->submitted_at?->format('d M Y')??'—','Reviewed'=>$application->reviewed_at?->format('d M Y')??'Not yet','Decided'=>$application->decided_at?->format('d M Y')??'Pending','Officer'=>$application->assignedOfficer?->name??'Unassigned'] as $l=>$v)
      <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:12.5px;border-bottom:1px solid #f1f5f9"><span class="muted">{{$l}}</span><span style="font-weight:600">{{$v}}</span></div>@endforeach
    </div>
  </div>
  @if($application->nextOfKin->count())
  <div class="card"><div class="card-hdr"><span class="card-title">Next of Kin</span></div><div style="padding:14px">
    @foreach($application->nextOfKin as $i=>$k)
    <div style="{{ $i>0?'margin-top:10px;padding-top:10px;border-top:1px solid #f1f5f9':'' }}">
      <div style="font-weight:700;font-size:13px">{{$k->first_name}} {{$k->last_name}}</div><div class="muted">{{$k->relationship}} · {{$k->contact_number}}</div>
    </div>@endforeach
  </div></div>@endif
</div>
</div>

{{-- APPROVE --}}
<div class="mo" id="aModal"><div class="mb">
  <div class="mh"><span class="mt"><i class="bi bi-check-circle-fill" style="color:#10b981"></i> Approve Loan</span><button class="mc" onclick="closeModal('aModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.applications.approve',$application) }}">@csrf
    <div class="mbody"><div class="g2" style="gap:14px">
      <div class="fg"><label class="fl">Approved Amount (L)*</label><input type="number" name="approved_amount" class="fc" value="{{ $application->requested_amount }}" step="0.01" required></div>
      <div class="fg"><label class="fl">Term (months)*</label><input type="number" name="approved_term" class="fc" value="{{ $application->requested_term }}" min="1" max="60" required></div>
      <div class="fg"><label class="fl">Interest Rate (%/mo)*</label><input type="number" name="interest_rate" class="fc" value="{{ $application->loanProduct?->interest_rate }}" step="0.01" required></div>
      <div class="fg"><label class="fl">Disbursement Date*</label><input type="date" name="disbursement_date" class="fc" value="{{ now()->addDay()->format('Y-m-d') }}" required></div>
    </div><div class="fg"><label class="fl">Notes</label><textarea name="notes" class="fc" rows="3"></textarea></div></div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('aModal')">Cancel</button><button type="submit" class="btn btn-ok"><i class="bi bi-check-lg"></i> Approve</button></div>
  </form>
</div></div>

{{-- DECLINE --}}
<div class="mo" id="dModal"><div class="mb">
  <div class="mh"><span class="mt"><i class="bi bi-x-circle-fill" style="color:#ef4444"></i> Decline Application</span><button class="mc" onclick="closeModal('dModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.applications.decline',$application) }}">@csrf
    <div class="mbody"><div class="fg"><label class="fl">Reason*</label><textarea name="reason" class="fc" rows="4" required></textarea></div></div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('dModal')">Cancel</button><button type="submit" class="btn btn-e"><i class="bi bi-x-lg"></i> Decline</button></div>
  </form>
</div></div>

{{-- HOLD --}}
<div class="mo" id="hModal"><div class="mb">
  <div class="mh"><span class="mt"><i class="bi bi-pause-fill" style="color:#f59e0b"></i> Hold Application</span><button class="mc" onclick="closeModal('hModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.applications.hold',$application) }}">@csrf
    <div class="mbody"><div class="fg"><label class="fl">Reason*</label><textarea name="reason" class="fc" rows="3" required></textarea></div></div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('hModal')">Cancel</button><button type="submit" class="btn btn-w">Hold</button></div>
  </form>
</div></div>

{{-- REQUEST INFO --}}
<div class="mo" id="iModal"><div class="mb">
  <div class="mh"><span class="mt">Request Information</span><button class="mc" onclick="closeModal('iModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.applications.request-info',$application) }}">@csrf
    <div class="mbody"><div class="fg"><label class="fl">Message to Borrower*</label><textarea name="message" class="fc" rows="4" required></textarea></div></div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('iModal')">Cancel</button><button type="submit" class="btn btn-p"><i class="bi bi-send"></i> Send</button></div>
  </form>
</div></div>

{{-- OVERRIDE --}}
<div class="mo" id="ovModal"><div class="mb">
  <div class="mh"><span class="mt"><i class="bi bi-pencil-fill"></i> Override Terms</span><button class="mc" onclick="closeModal('ovModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.applications.override',$application) }}">@csrf
    <div class="mbody"><div class="g2" style="gap:14px">
      <div class="fg"><label class="fl">Amount (L)</label><input type="number" name="loan_amount" class="fc" value="{{ $application->requested_amount }}" step="0.01"></div>
      <div class="fg"><label class="fl">Rate (%/mo)</label><input type="number" name="interest_rate" class="fc" value="{{ $application->loanProduct?->interest_rate }}" step="0.01"></div>
      <div class="fg"><label class="fl">Term (months)</label><input type="number" name="term_months" class="fc" value="{{ $application->requested_term }}"></div>
    </div></div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('ovModal')">Cancel</button><button type="submit" class="btn btn-p">Update</button></div>
  </form>
</div></div>
@endsection
