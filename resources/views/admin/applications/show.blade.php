@extends('admin.layouts.app')
@section('title','Application #'.$application->application_number)
@section('page-title','Application Review')
@section('bc')
<a href="{{ route('admin.applications.index') }}">Applications</a> / #{{ $application->application_number }}
@endsection
@section('content')

@php
$isPending = in_array($application->status, ['submitted','under_review','info_requested','on_hold']);
$badgeMap  = ['submitted'=>['#6366f1','#ede9fe'],'under_review'=>['#0891b2','#e0f2fe'],'info_requested'=>['#d97706','#fef3c7'],'on_hold'=>['#d97706','#fef3c7'],'approved'=>['#059669','#d1fae5'],'declined'=>['#dc2626','#fee2e2'],'disbursed'=>['#2563eb','#dbeafe'],'draft'=>['#64748b','#f1f5f9']];
[$tc,$bc] = $badgeMap[$application->status] ?? ['#64748b','#f1f5f9'];
$a = $application->affordability;
$afford = app(\App\Services\Admin\ApplicationService::class)->checkAffordability($application);
@endphp

@foreach(['success'=>'ok','info'=>'i','error'=>'e'] as $type=>$cls)
@if(session($type))
<div class="alert a-{{ $cls }}" style="margin-bottom:18px"><i class="bi bi-{{ $cls==='ok'?'check-circle-fill':($cls==='e'?'x-circle-fill':'info-circle-fill') }}"></i> {{ session($type) }}</div>
@endif
@endforeach

{{-- TOP HEADER --}}
<div style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
  <div style="display:flex;align-items:center;gap:16px">
    <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:22px;flex-shrink:0">
      {{ strtoupper(substr($application->applicant_name,0,1)) }}
    </div>
    <div>
      <div style="font-size:19px;font-weight:800;color:var(--dark)">{{ $application->applicant_name }}</div>
      <div style="font-size:13px;color:var(--muted);margin-top:2px">
        {{ $application->application_number }} &nbsp;·&nbsp; {{ $application->cell_number ?? '—' }}
        @if($application->loanProduct) &nbsp;·&nbsp; {{ $application->loanProduct->name }} @endif
      </div>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <span style="background:{{ $bc }};color:{{ $tc }};font-size:13px;font-weight:700;padding:6px 16px;border-radius:20px">{{ ucfirst(str_replace('_',' ',$application->status)) }}</span>
    @if($application->status === 'draft')
      <form method="POST" action="{{ route('admin.applications.verify-payment',$application) }}" style="display:inline" onsubmit="return confirm('Have you manually verified that the borrower has paid the application fee? This will submit the application for review.')">
        @csrf
        <button class="btn btn-p btn-sm"><i class="bi bi-shield-check"></i> Verify Payment & Submit</button>
      </form>
    @endif
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
    <a href="{{ route('admin.applications.experian-template',$application) }}" class="btn btn-o btn-sm" title="Download Experian MFT CSV"><i class="bi bi-filetype-csv"></i> Experian MFT</a>
  </div>
</div>

{{-- QUICK STATS --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px">
  @foreach([
    ['Requested','M '.number_format($application->requested_amount??0,0),'currency-dollar','#4f46e5'],
    ['Term',($application->requested_term??'—').' months','calendar3','#0891b2'],
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
<div>

  {{-- TABS — NEW ORDER: Personal > Affordability > Loan Details > Documents > Notes > Schedule --}}
  <div class="tabs">
    <button class="tab active" data-tg="app" data-t="personal"  onclick="switchTab('app','personal')"><i class="bi bi-person"></i> Personal</button>
    <button class="tab"        data-tg="app" data-t="afford"    onclick="switchTab('app','afford')"><i class="bi bi-calculator"></i> Affordability</button>
    <button class="tab"        data-tg="app" data-t="loan"      onclick="switchTab('app','loan')"><i class="bi bi-bank"></i> Loan Details</button>
    <button class="tab"        data-tg="app" data-t="docs"      onclick="switchTab('app','docs')"><i class="bi bi-files"></i> Documents <span style="background:var(--bg);color:var(--muted);font-size:10px;padding:1px 6px;border-radius:10px;margin-left:2px">{{ $application->documents->count() }}</span></button>

    <button class="tab"        data-tg="app" data-t="notes"     onclick="switchTab('app','notes')"><i class="bi bi-chat-text"></i> Internal Notes <span style="background:var(--bg);color:var(--muted);font-size:10px;padding:1px 6px;border-radius:10px;margin-left:2px">{{ $application->notes->count() }}</span></button>
    <button class="tab"        data-tg="app" data-t="scoring"   onclick="switchTab('app','scoring')"><i class="bi bi-shield-check"></i> Scoring Decision</button>
    <button class="tab"        data-tg="app" data-t="schedule"  onclick="switchTab('app','schedule')"><i class="bi bi-table"></i> Schedule</button>
  </div>

  {{-- ── PERSONAL TAB ──────────────────────────────────────────── --}}
  <div class="tpanel active" data-pg="app" data-p="personal">
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr">
          <span class="card-title">Personal Information</span>
          <button class="btn btn-sm btn-o" style="margin-left:auto" onclick="openModal('editPersonalModal')">
              <i class="bi bi-pencil"></i> Edit
          </button>
      </div>
      <div class="card-body">
        <div class="info-grid">
          @foreach(['Title'=>$application->title,'First Name'=>$application->first_name,'Surname'=>$application->surname,'Maiden Name'=>$application->maiden_name,'National ID'=>$application->national_id,'Date of Birth'=>$application->date_of_birth?->format('d M Y'),'Gender'=>ucfirst($application->gender??''),'Marital Status'=>ucfirst($application->marital_status??''),'Cell'=>$application->cell_number,'Email'=>$application->email??'—'] as $l=>$v)
          <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr">
          <span class="card-title">Address & Location</span>
          <button class="btn btn-sm btn-o" style="margin-left:auto" onclick="openModal('editAddressModal')">
              <i class="bi bi-pencil"></i> Edit
          </button>
      </div>
      <div class="card-body">
        <div class="info-grid">
          @foreach(['Residential Address'=>$application->residential_address, 'Village'=>$application->village, 'Town'=>$application->town, 'District'=>$application->district, 'Residence Type'=>ucfirst($application->residence_type??''), 'Duration'=>$application->address_duration, 'Nearest Landmark'=>$application->nearest_landmark, 'Home Directions'=>$application->home_directions, 'GPS Coordinates'=>($application->gps_latitude && $application->gps_longitude) ? "{$application->gps_latitude}, {$application->gps_longitude}" : null] as $l=>$v)
          <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr">
          <span class="card-title">Employment Details</span>
          <button class="btn btn-sm btn-o" style="margin-left:auto" onclick="openModal('editEmploymentModal')">
              <i class="bi bi-pencil"></i> {{ $application->employment ? 'Edit' : 'Add' }}
          </button>
      </div>
      <div class="card-body">
        @if($application->employment)
        <div class="info-grid">
          @foreach(['Employer'=>$application->employment->employer_name,'Type'=>$application->employment->employer_type,'Category'=>$application->employment->employer_category,'Job Title'=>$application->employment->job_title,'Department'=>$application->employment->department,'Employee #'=>$application->employment->employment_number,'HR Contact'=>$application->employment->contact_number] as $l=>$v)
          <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
          @endforeach
        </div>
        @else
        <div style="text-align:center;color:var(--muted);font-size:12px;padding:10px">No employment details found. <a href="javascript:void(0)" onclick="openModal('editEmploymentModal')">Add now</a></div>
        @endif
      </div>
    </div>

    <div class="card" style="margin-top:16px">
      <div class="card-hdr">
          <span class="card-title">Bank Details</span>
          <button class="btn btn-sm btn-o" style="margin-left:auto" onclick="openModal('editBankModal')">
              <i class="bi bi-pencil"></i> {{ $application->bankDetails ? 'Edit' : 'Add' }}
          </button>
      </div>
      <div class="card-body">
        @if($application->bankDetails)
        <div class="info-grid">
          @foreach(['Bank'=>$application->bankDetails->bank_name,'Account Holder'=>$application->bankDetails->account_holder_name,'Account #'=>$application->bankDetails->account_number,'Branch'=>$application->bankDetails->branch_name,'Branch Code'=>$application->bankDetails->branch_code,'Account Type'=>ucfirst($application->bankDetails->account_type??'')] as $l=>$v)
          <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
          @endforeach

          @if($application->user && $application->user->encrypted_card_number)
            <div>
                <div class="info-lbl">Debit Card Number</div>
                <div class="info-val"><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;color:#0f172a">
                  @php try { $cn = \Illuminate\Support\Facades\Crypt::decryptString($application->user->encrypted_card_number); echo '•••• •••• •••• '.substr($cn,-4); } catch(\Exception $e) { echo '•••• •••• •••• '.($application->user->card_last_four ?? '????'); } @endphp
                </code></div>
            </div>
            <div><div class="info-lbl">Card Name</div><div class="info-val">{{ $application->user->card_name ?: '—' }}</div></div>
            <div><div class="info-lbl">Card Expiry</div><div class="info-val">{{ $application->user->card_expiry ?: '—' }}</div></div>
            <div>
                <div class="info-lbl">Card CVV</div>
                <div class="info-val"><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;color:#0f172a">•••</code></div>
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
        @else
        <div style="text-align:center;color:var(--muted);font-size:12px;padding:10px">No bank details found. <a href="javascript:void(0)" onclick="openModal('editBankModal')">Add now</a></div>
        @endif
      </div>
    </div>

    @if($application->nextOfKin->count() > 0)
    <div class="card" style="margin-top:16px">
      <div class="card-hdr"><span class="card-title">Next of Kin / Emergency Contact</span></div>
      <div class="card-body">
        <div class="info-grid">
          @php $nok = $application->nextOfKin->first(); @endphp
          @foreach(['First Name'=>$nok->first_name,'Last Name'=>$nok->last_name,'Relationship'=>$nok->relationship,'Contact Number'=>$nok->contact_number] as $l=>$v)
          <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
          @endforeach
        </div>
      </div>
    </div>
    @endif

    @if($application->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($application->signature_path))
    <div class="card" style="margin-top:16px">
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

  {{-- ── SCORING TAB ─────────────────────────────────────────── --}}
  <div class="tpanel" data-pg="app" data-p="scoring">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
      
      {{-- Credit Score Card --}}
      <div class="card">
        <div class="card-hdr" style="background:#f8fafc">
          <span class="card-title"><i class="bi bi-bank" style="color:#4f46e5"></i> Credit Score (Ability to Pay)</span>
          <span style="margin-left:auto;font-weight:800;font-size:20px;color:#4f46e5">{{ $credit['total'] }} / 100</span>
        </div>
        <div class="card-body">
          <div style="display:flex;justify-content:center;margin-bottom:20px">
            <div style="background:#eef2ff;color:#4f46e5;padding:8px 24px;border-radius:20px;font-weight:800;font-size:14px;letter-spacing:1px">
               {{ $credit['label'] }}
            </div>
          </div>
          <div style="display:flex;flex-direction:column;gap:12px">
            @foreach([
              'Repayment History'   => ['pts' => $credit['breakdown']['repayment_history'], 'max' => 35],
              'Affordability'        => ['pts' => $credit['breakdown']['affordability'], 'max' => 20],
              'Loan-to-Income'       => ['pts' => $credit['breakdown']['loan_to_income'], 'max' => 10],
              'Employment Stability' => ['pts' => $credit['breakdown']['employment_stability'], 'max' => 15],
              'Existing Debt'        => ['pts' => $credit['breakdown']['debt_burden'], 'max' => 10],
              'Data Completeness'    => ['pts' => $credit['breakdown']['data_completeness'], 'max' => 5],
              'Age Factor'           => ['pts' => $credit['breakdown']['age_factor'], 'max' => 5],
            ] as $label => $val)
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span style="font-size:13px;color:var(--muted)">{{ $label }}</span>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="width:100px;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden">
                  <div style="width:{{ ($val['pts']/$val['max'])*100 }}%;height:100%;background:#4f46e5"></div>
                </div>
                <span style="font-size:13px;font-weight:700;min-width:25px;text-align:right">{{ $val['pts'] }}</span>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Fraud Score Card --}}
      <div class="card">
        <div class="card-hdr" style="background:#fef2f2">
          <span class="card-title"><i class="bi bi-shield-lock" style="color:#dc2626"></i> Fraud Score (Trust Factor)</span>
          <span style="margin-left:auto;font-weight:800;font-size:20px;color:#dc2626">{{ $fraud['total'] }} / 100</span>
        </div>
        <div class="card-body">
          <div style="display:flex;justify-content:center;margin-bottom:20px">
            <div style="background:#fef2f2;color:#dc2626;padding:8px 24px;border-radius:20px;font-weight:800;font-size:14px;letter-spacing:1px">
               {{ $fraud['label'] }}
            </div>
          </div>
          <div style="display:flex;flex-direction:column;gap:12px">
            @if(empty($fraud['breakdown']))
              <div style="text-align:center;padding:20px;color:#059669;font-weight:600">
                <i class="bi bi-check-circle-fill"></i> No fraud risks detected
              </div>
            @else
              @foreach($fraud['breakdown'] as $risk => $pts)
              <div style="display:flex;justify-content:space-between;align-items:center;padding:8px;background:#fff5f5;border-radius:8px">
                <span style="font-size:13px;font-weight:600;color:#991b1b">{{ ucfirst(str_replace('_',' ',$risk)) }}</span>
                <span style="color:#dc2626;font-weight:800">{{ is_numeric($pts) ? $pts : 'CRITICAL' }}</span>
              </div>
              @endforeach
            @endif
            <div style="margin-top:auto;font-size:11px;color:var(--muted);border-top:1px solid #f1f5f9;padding-top:10px">
              Calculated based on Identity, Device, Location and Behavior heuristics.
            </div>
          </div>
        </div>
      </div>

    </div>

    {{-- Final Decision Engine --}}
    <div class="card" style="border:2px solid {{ $decision['color'] ?? '#64748b' }}">
      <div class="card-body" style="display:flex;align-items:center;gap:30px;padding:30px">
        <div style="width:80px;height:80px;border-radius:50%;background:{{ $decision['color'] ?? '#64748b' }}15;display:flex;align-items:center;justify-content:center;color:{{ $decision['color'] ?? '#64748b' }};font-size:40px">
          <i class="bi bi-{{ $decision['status'] === 'DECLINE' ? 'x-circle' : ($decision['status'] === 'MANUAL REVIEW' ? 'eye' : 'check-circle') }}"></i>
        </div>
        <div>
          <div style="font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:2px">Decision Engine Output</div>
          <div style="font-size:32px;font-weight:900;color:{{ $decision['color'] ?? '#64748b' }};margin:4px 0">{{ $decision['status'] }}</div>
          @if(isset($decision['reason']))
            <div style="color:#ef4444;font-weight:600;font-size:14px"><i class="bi bi-info-circle"></i> {{ $decision['reason'] }}</div>
          @endif
        </div>
        <div style="margin-left:auto;text-align:right">
          <div style="font-size:13px;color:var(--muted);margin-bottom:8px">Recommended Action:</div>
          @if($decision['status'] === 'DECLINE')
            <button class="btn btn-e" onclick="openModal('dModal')">Decline Application</button>
          @elseif($decision['status'] === 'FULL APPROVAL')
            <button class="btn btn-ok" onclick="openModal('aModal')">Approve Full Amount</button>
          @else
            <button class="btn btn-o" onclick="openModal('aModal')">Review & Adjust Terms</button>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ── AFFORDABILITY TAB ─────────────────────────────────────── --}}
  <div class="tpanel" data-pg="app" data-p="afford">

    {{-- Pass/fail banner --}}
    @if($a)
      @if(!$afford['passes'])
      <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:12px;padding:14px 18px;display:flex;align-items:flex-start;gap:12px;margin-bottom:16px">
        <i class="bi bi-exclamation-triangle-fill" style="color:#d97706;font-size:20px;flex-shrink:0;margin-top:1px"></i>
        <div><div style="font-weight:700;color:#92400e;font-size:13.5px;margin-bottom:4px">Affordability Warning</div><div style="font-size:13px;color:#78350f;line-height:1.6">{{ $afford['warning'] }}</div></div>
      </div>
      @else
      <div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);border-radius:12px;padding:12px 18px;display:flex;align-items:center;gap:10px;margin-bottom:16px">
        <i class="bi bi-check-circle-fill" style="color:#10b981;font-size:18px;flex-shrink:0"></i>
        <div style="font-size:13px;color:#065f46;font-weight:600">Affordable — Monthly M{{ number_format($afford['monthly'],2) }} within disposable income of M{{ number_format($afford['disposable_income'],2) }}</div>
      </div>
      @endif
    @endif

    <div class="card">
      <div class="card-hdr" style="display:flex;justify-content:space-between;align-items:center;">
        <span class="card-title"><i class="bi bi-calculator" style="color:var(--p);margin-right:6px"></i>Affordability Assessment</span>
        <div style="display:flex;gap:10px;">
          <a href="https://cc.experian.co.ls/" target="_blank" style="background:#f59e0b;color:#fff;text-decoration:none;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:600;display:flex;align-items:center"><i class="bi bi-box-arrow-up-right" style="margin-right:5px"></i>Credit Check</a>
          <button onclick="document.getElementById('affordModal').style.display='flex'" style="background:var(--p);color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer"><i class="bi bi-pencil" style="margin-right:5px"></i>{{ $a ? 'Edit' : 'Add' }} Affordability</button>
        </div>
      </div>
      <div class="card-body">
        @if($a)

        {{-- 3-column summary --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:24px">
          <div style="background:#f0f4ff;border-radius:10px;padding:16px;text-align:center;border:1px solid var(--border)">
            <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Gross Salary</div>
            <div style="font-size:24px;font-weight:800;color:var(--p)">M{{ number_format($a->monthly_earnings??0,2) }}</div>
          </div>
          <div style="background:#fff7f0;border-radius:10px;padding:16px;text-align:center;border:1px solid #fde8d0">
            <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Total Deductions</div>
            @php $totalDed = ($a->tax_deduction??0)+($a->existing_loans_deduction??0)+($a->other_deductions??0); @endphp
            <div style="font-size:24px;font-weight:800;color:#ea580c">M{{ number_format($totalDed,2) }}</div>
          </div>
          <div style="background:#f0fdf4;border-radius:10px;padding:16px;text-align:center;border:1px solid #bbf7d0">
            <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Net Salary</div>
            @php $netSalMatch = ($a->monthly_earnings??0) - $totalDed; @endphp
            <div style="font-size:24px;font-weight:800;color:#16a34a">M{{ number_format($netSalMatch,2) }}</div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
          {{-- Income breakdown --}}
          <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:10px">Income Breakdown</div>
            <div style="background:#f8fafc;border-radius:10px;padding:14px">
              @foreach(['Gross / Basic Salary'=>[$a->monthly_earnings,false],'Tax Deductions'=>[$a->tax_deduction,true],'Existing Loan Deductions'=>[$a->existing_loans_deduction,true],'Other Deductions'=>[$a->other_deductions,true]] as $l=>[$v,$isDed])
              <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
                <span style="color:var(--muted)">{{ $l }}</span>
                <span style="font-weight:600;{{ $isDed?'color:#ef4444':'' }}">{{ $isDed?'– ':'' }}M{{ number_format($v??0,2) }}</span>
              </div>
              @endforeach
              <div style="display:flex;justify-content:space-between;padding:9px 0;font-size:13.5px;font-weight:800;color:#16a34a">
                <span>Net Salary</span><span>M{{ number_format($a->net_salary??0,2) }}</span>
              </div>
            </div>
          </div>

          {{-- Expenses breakdown --}}
          <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:10px">Living Expenses</div>
            <div style="background:#f8fafc;border-radius:10px;padding:14px">
              @foreach(['Rent'=>$a->rent,'Groceries'=>$a->groceries,'Transport'=>$a->transport,'Utilities'=>$a->utilities,'Education'=>$a->education??0,'Communication'=>$a->communication??0,'Medical'=>$a->medical??0,'Other Loans'=>$a->other_loan_repayments??0,'Other'=>$a->other_expenses] as $l=>$v)
              @if(($v??0) > 0)
              <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12.5px;border-bottom:1px solid var(--border)">
                <span style="color:var(--muted)">{{ $l }}</span>
                <span style="font-weight:600;color:#ef4444">– M{{ number_format($v,2) }}</span>
              </div>
              @endif
              @endforeach
              <div style="display:flex;justify-content:space-between;padding:9px 0;font-size:13px;font-weight:700;color:#dc2626">
                <span>Total Expenses</span><span>M{{ number_format($a->total_living_expenses??0,2) }}</span>
              </div>
            </div>
          </div>
        </div>

        {{-- Disposable income result --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:16px">
          <div style="background:{{ ($a->disposable_income??0)>0?'rgba(16,185,129,.08)':'rgba(239,68,68,.06)' }};border-radius:12px;padding:18px;text-align:center;border:1px solid {{ ($a->disposable_income??0)>0?'rgba(16,185,129,.2)':'rgba(239,68,68,.2)' }}">
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Disposable Income</div>
            <div style="font-size:28px;font-weight:800;color:{{ ($a->disposable_income??0)>0?'#10b981':'#ef4444' }}">M{{ number_format($a->disposable_income??0,2) }}</div>
            <div style="font-size:12px;color:var(--muted);margin-top:4px">{{ ($a->disposable_income??0)>0?'✓ Passes':'✗ Fails' }}</div>
          </div>
          @if($afford['monthly']>0)
          <div style="background:#f8fafc;border-radius:12px;padding:18px;text-align:center;border:1px solid var(--border)">
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Monthly Instalment vs Disposable</div>
            <div style="font-size:22px;font-weight:800;color:var(--p)">M{{ number_format($afford['monthly'],2) }}</div>
            <div style="font-size:12px;margin-top:4px"><span class="badge {{ $afford['passes']?'bok':'be' }}">{{ $afford['passes']?'Affordable':'Not Affordable' }}</span></div>
          </div>
          @endif
        </div>

        @else
        <div style="text-align:center;padding:50px;color:var(--muted)">
          <i class="bi bi-calculator" style="font-size:44px;opacity:.25;display:block;margin-bottom:12px"></i>
          <div style="font-weight:600;margin-bottom:10px">No affordability data</div>
          <div style="font-size:12px;margin-bottom:16px">Borrower has not completed the income section</div>
          <button onclick="document.getElementById('affordModal').style.display='flex'" class="btn btn-p btn-sm"><i class="bi bi-plus"></i> Complete Assessment</button>
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- ── LOAN DETAILS TAB ──────────────────────────────────────── --}}
  <div class="tpanel" data-pg="app" data-p="loan">

    {{-- Inline affordability warning --}}
    @if($a && !$afford['passes'])
    <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:12px;padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:10px">
      <i class="bi bi-exclamation-triangle-fill" style="color:#d97706"></i>
      <div style="font-size:13px;color:#92400e">{{ $afford['warning'] }}</div>
    </div>
    @endif

    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr">
        <span class="card-title">Requested Loan Terms</span>
        <div style="margin-left:auto;display:flex;gap:8px">
            @if($isPending)<button onclick="openModal('ovModal')" class="btn btn-sm btn-o"><i class="bi bi-pencil"></i> Override</button>@endif
            <button onclick="openModal('editRequestModal')" class="btn btn-sm btn-o"><i class="bi bi-pencil-square"></i> Edit Request</button>
        </div>
      </div>
      <div class="card-body">
        <div class="info-grid">
          @foreach(['Product'=>$application->loanProduct?->name,'Amount'=>'M '.number_format($application->requested_amount??0,2),'Term'=>($application->requested_term??'—').' months','Purpose'=>$application->loan_purpose??'—','Payout'=>ucfirst(str_replace('_',' ',$application->payout_method??'')),'Collection'=>ucfirst(str_replace('_',' ',$application->collection_method??''))] as $l=>$v)
          <div><div class="info-lbl">{{ $l }}</div><div class="info-val">{{ $v ?: '—' }}</div></div>
          @endforeach
        </div>
      </div>
    </div>

    @if($application->approved_amount)
    <div style="background:linear-gradient(135deg,rgba(16,185,129,.07),rgba(5,150,105,.04));border:1px solid rgba(16,185,129,.25);border-radius:14px;padding:20px;margin-bottom:16px">
      <div style="font-weight:700;color:#065f46;margin-bottom:14px;display:flex;align-items:center;gap:7px"><i class="bi bi-check-circle-fill" style="color:#10b981"></i> Approved Terms</div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;text-align:center">
        <div><div style="font-size:11px;color:#065f46;font-weight:600;text-transform:uppercase">Amount</div><div style="font-size:22px;font-weight:800;color:#059669">M{{ number_format($application->approved_amount,0) }}</div></div>
        <div><div style="font-size:11px;color:#065f46;font-weight:600;text-transform:uppercase">Term</div><div style="font-size:22px;font-weight:800;color:#059669">{{ $application->approved_term }}mo</div></div>
        <div><div style="font-size:11px;color:#065f46;font-weight:600;text-transform:uppercase">Rate</div><div style="font-size:22px;font-weight:800;color:#059669">{{ $application->approved_interest_rate }}%</div></div>
        <div><div style="font-size:11px;color:#065f46;font-weight:600;text-transform:uppercase">Disburse</div><div style="font-size:16px;font-weight:700;color:#059669;margin-top:4px">{{ $application->disbursement_date?->format('d M Y') }}</div></div>
      </div>
    </div>
    @endif

    @if($isPending)
    @php $riskResult = app(\App\Services\RiskScoringService::class)->calculate($application); @endphp
    <div class="card">
      <div class="card-hdr">
        <span class="card-title"><i class="bi bi-shield-check" style="color:#f59e0b;margin-right:5px"></i> Risk Assessment</span>
        <form method="POST" action="{{ route('admin.applications.auto-risk-score',$application) }}" style="display:inline">@csrf
          <button type="submit" class="btn btn-sm btn-p" style="gap:5px"><i class="bi bi-cpu"></i> Auto-Calculate</button>
        </form>
      </div>
      <div class="card-body">

        {{-- Score gauge --}}
        @php $rs=$application->risk_score ?? $riskResult['score']; $rp=min(100,$rs/10); $rc=$rs>=700?'#10b981':($rs>=500?'#f59e0b':'#ef4444'); $rl=$rs>=700?'Low Risk':($rs>=500?'Moderate Risk':'High Risk'); @endphp
        <div style="text-align:center;margin-bottom:18px">
          <div style="font-size:48px;font-weight:800;color:{{ $rc }};line-height:1">{{ $rs }}</div>
          <div style="font-size:12px;font-weight:700;color:{{ $rc }};margin-top:3px">{{ $rl }}</div>
          <div style="height:8px;background:var(--bg);border-radius:99px;overflow:hidden;margin-top:10px">
            <div style="height:100%;width:{{ $rp }}%;background:linear-gradient(90deg,#ef4444,#f59e0b 50%,#10b981);border-radius:99px;transition:width .5s"></div>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--muted);margin-top:4px">
            <span>0 – High Risk</span><span>500 – Moderate</span><span>1000 – Low Risk</span>
          </div>
        </div>

        {{-- Breakdown table --}}
        <div style="font-size:12px;font-weight:700;color:var(--dark);margin-bottom:10px;display:flex;align-items:center;gap:6px"><i class="bi bi-list-check" style="color:var(--p)"></i> Score Breakdown</div>
        <div style="display:flex;flex-direction:column;gap:8px">
          @foreach($riskResult['breakdown'] as $item)
          <div style="background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:10px 14px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px">
              <div style="display:flex;align-items:center;gap:7px">
                <div style="width:28px;height:28px;border-radius:7px;background:{{ $item['color'] }}15;display:flex;align-items:center;justify-content:center;color:{{ $item['color'] }};font-size:13px;flex-shrink:0"><i class="bi bi-{{ $item['icon'] }}"></i></div>
                <div>
                  <div style="font-size:12.5px;font-weight:700;color:var(--dark)">{{ $item['factor'] }}</div>
                  <div style="font-size:11px;color:var(--muted)">{{ $item['detail'] }}</div>
                </div>
              </div>
              <div style="text-align:right;flex-shrink:0">
                <span style="font-size:14px;font-weight:800;color:{{ $item['color'] }}">{{ $item['score'] }}</span>
                <span style="font-size:11px;color:var(--muted)">/ {{ $item['max'] }}</span>
              </div>
            </div>
            <div style="height:5px;background:#e5e7eb;border-radius:99px;overflow:hidden">
              <div style="height:100%;width:{{ $item['max'] > 0 ? round($item['score']/$item['max']*100) : 0 }}%;background:{{ $item['color'] }};border-radius:99px;transition:width .4s"></div>
            </div>
          </div>
          @endforeach
        </div>

        {{-- Manual override --}}
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border)">
          <div style="font-size:11px;font-weight:600;color:var(--muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em">Manual Override</div>
          <form method="POST" action="{{ route('admin.applications.set-risk-score',$application) }}" style="display:flex;gap:10px;align-items:flex-end">@csrf
            <div class="fg" style="margin-bottom:0;flex:1"><input type="number" name="risk_score" class="fc" min="0" max="1000" value="{{ $application->risk_score }}" placeholder="e.g. 720" style="font-size:13px"></div>
            <button type="submit" class="btn btn-o btn-sm">Set Manually</button>
          </form>
        </div>
      </div>
    </div>
    @endif
  </div>

  {{-- ── DOCUMENTS TAB ─────────────────────────────────────────── --}}
  <div class="tpanel" data-pg="app" data-p="docs">

    {{-- Upload new document (admin can add docs) --}}
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title"><i class="bi bi-upload" style="color:var(--p);margin-right:6px"></i>Upload Document</span></div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.applications.documents.upload', $application) }}" enctype="multipart/form-data">
          @csrf
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;align-items:end">
            <div class="fg" style="margin-bottom:0">
              <label class="fl">Document Type *</label>
              <select name="type" class="fc" required>
                <option value="">— Select Type —</option>
                @foreach(['national_id'=>'National ID','payslip'=>'Payslip','bank_statement'=>'Bank Statement','photo'=>'Selfie Picture','employment_letter'=>'Employment Letter','experian_report'=>'Experian Report','other'=>'Other'] as $v=>$l)
                <option value="{{ $v }}">{{ $l }}</option>
                @endforeach
              </select>
            </div>
            <div class="fg" style="margin-bottom:0">
              <label class="fl">File * <span style="font-weight:400;font-size:11px;color:var(--muted)">(PDF/JPG/PNG max 10MB)</span></label>
              <input type="file" name="file" class="fc" accept=".pdf,.jpg,.jpeg,.png" required style="padding:8px 10px">
            </div>
            <div>
              <button type="submit" class="btn btn-p" style="width:100%;justify-content:center"><i class="bi bi-upload"></i> Upload</button>
            </div>
          </div>
          <div class="fg" style="margin-top:12px;margin-bottom:0">
            <label class="fl">Notes (optional)</label>
            <input type="text" name="notes" class="fc" placeholder="e.g. Submitted by borrower in person">
          </div>
        </form>
      </div>
    </div>

    {{-- Documents list --}}
    <div class="card">
      <div class="card-hdr"><span class="card-title">Documents ({{ $application->documents->count() }})</span></div>
      <div class="card-body">
        @forelse($application->documents as $doc)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;border:1px solid var(--border);border-radius:12px;margin-bottom:10px" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
          <div style="display:flex;align-items:center;gap:12px">
            <div style="width:42px;height:42px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#64748b">
              <i class="bi bi-file-earmark-{{ str_contains($doc->mime_type??'','pdf')?'pdf':'text' }}-fill"></i>
            </div>
            <div>
              <div style="font-weight:600;font-size:13px">
                {{ ucfirst(str_replace('_',' ',$doc->type)) }}
                @if($doc->type === 'experian_report') <span style="background:#fef2f2;color:#dc2626;font-size:10px;padding:2px 6px;border-radius:4px;margin-left:6px;vertical-align:middle;border:1px solid #fee2e2"><i class="bi bi-eye-slash-fill"></i> INTERNAL ONLY</span> @endif
              </div>
              <div style="font-size:11.5px;color:var(--muted)">{{ $doc->original_name }} &nbsp;·&nbsp; {{ $doc->created_at->format('d M Y') }}</div>
              @if($doc->notes)<div style="font-size:11px;color:var(--muted);font-style:italic">{{ $doc->notes }}</div>@endif
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:8px">
            <span class="badge {{ $doc->status==='verified'?'bok':($doc->status==='rejected'?'be':'bw') }}">
              <i class="bi bi-{{ $doc->status==='verified'?'check-circle':'clock' }}" style="font-size:10px"></i> {{ ucfirst($doc->status) }}
            </span>
            @if($doc->status==='pending')
            <form method="POST" action="{{ route('admin.applications.documents.verify',[$application,$doc]) }}" style="display:inline">@csrf<button class="btn btn-xs btn-ok" title="Verify"><i class="bi bi-check"></i></button></form>
            <form method="POST" action="{{ route('admin.applications.documents.reject',[$application,$doc]) }}" style="display:inline">@csrf<button class="btn btn-xs btn-e" title="Reject"><i class="bi bi-x"></i></button></form>
            @endif
            <button type="button" onclick="previewDoc('{{ route('admin.applications.documents.view',[$application,$doc]) }}', '{{ addslashes($doc->original_name) }}')" class="btn btn-xs btn-o" title="Preview"><i class="bi bi-eye"></i></button>
            <a href="{{ route('admin.applications.documents.download',[$application,$doc]) }}" class="btn btn-xs btn-o" title="Download"><i class="bi bi-download"></i></a>
          </div>
        </div>
        @empty
        <div style="text-align:center;padding:48px;color:var(--muted)"><i class="bi bi-file-earmark-x" style="font-size:44px;opacity:.25;display:block;margin-bottom:12px"></i><div style="font-weight:600">No documents uploaded</div></div>
        @endforelse
      </div>
    </div>
  </div>



  {{-- ── NOTES TAB ─────────────────────────────────────────────── --}}
  <div class="tpanel" data-pg="app" data-p="notes">
    <div class="card" style="margin-bottom:16px">
      <div class="card-hdr"><span class="card-title">Add Note</span></div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.applications.notes.store',$application) }}">@csrf
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
            <div class="fg" style="margin-bottom:0"><label class="fl">Type</label><select name="type" class="fc">@foreach(['general'=>'General','approval'=>'Approval','decision'=>'Decision','status'=>'Status Update','info_request'=>'Info Request','override'=>'Override'] as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
            <div class="fg" style="margin-bottom:0;display:flex;align-items:flex-end"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600;padding-bottom:9px"><input type="checkbox" name="is_internal" value="1" checked style="width:16px;height:16px;accent-color:var(--p)"> Internal only</label></div>
          </div>
          <div class="fg" style="margin-bottom:12px"><label class="fl">Note *</label><textarea name="content" class="fc" rows="3" required placeholder="Add a note…"></textarea></div>
          <button type="submit" class="btn btn-p btn-sm"><i class="bi bi-plus-lg"></i> Add Note</button>
        </form>
      </div>
    </div>
    <div class="card">
      <div class="card-hdr"><span class="card-title">Notes History ({{ $application->notes->count() }})</span></div>
      <div class="card-body" style="padding-bottom:10px">
        @forelse($application->notes as $note)
        <div style="padding:14px 16px;border-left:3px solid {{ $note->is_internal?'#f59e0b':'#4f46e5' }};background:#f8fafc;border-radius:0 12px 12px 0;margin-bottom:10px">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px">
            <span style="font-size:11px;font-weight:700;color:{{ $note->is_internal?'#d97706':'var(--p)' }};text-transform:uppercase;background:{{ $note->is_internal?'rgba(245,158,11,.12)':'rgba(79,70,229,.08)' }};padding:2px 8px;border-radius:99px">{{ str_replace('_',' ',strtoupper($note->type)) }}@if($note->is_internal) · Internal @endif</span>
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-size:11.5px;color:var(--muted)">{{ $note->created_at->format('d M Y H:i') }} · {{ $note->createdBy?->name ?? '—' }}</span>
              <form method="POST" action="{{ route('admin.applications.notes.destroy',[$application,$note]) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-xs" style="background:none;border:none;color:var(--muted);cursor:pointer;padding:2px 5px"><i class="bi bi-trash"></i></button></form>
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

  {{-- ── SCHEDULE TAB ──────────────────────────────────────────── --}}
  <div class="tpanel" data-pg="app" data-p="schedule">
    <div class="card">
      <div class="card-hdr"><span class="card-title">Repayment Schedule Preview</span></div>
      <div class="card-body">
        <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
          <div class="fg" style="margin-bottom:0"><label class="fl">Amount (M)</label><input type="number" id="schAmt" class="fc" style="width:150px" value="{{ $application->approved_amount ?? $application->requested_amount }}" step="0.01"></div>
          <div class="fg" style="margin-bottom:0"><label class="fl">Rate (%/mo)</label><input type="number" id="schRate" class="fc" style="width:130px" value="{{ $application->approved_interest_rate ?? $application->loanProduct?->interest_rate ?? 0 }}" step="0.01"></div>
          <div class="fg" style="margin-bottom:0"><label class="fl">Term (months)</label><input type="number" id="schTerm" class="fc" style="width:130px" value="{{ $application->approved_term ?? $application->requested_term }}"></div>
          <div style="display:flex;align-items:end"><button onclick="previewSchedule()" class="btn btn-p"><i class="bi bi-table"></i> Generate</button></div>
        </div>
        <div id="scheduleResult" style="overflow-x:auto"></div>
      </div>
    </div>
  </div>

</div>

{{-- SIDEBAR --}}
<div>
  {{-- Timeline --}}
  <div class="card" style="margin-bottom:16px">
    <div class="card-hdr"><span class="card-title">Timeline</span></div>
    <div style="padding:16px">
      @foreach([['Submitted',$application->submitted_at,'send','#4f46e5'],['Reviewed',$application->reviewed_at,'eye','#0891b2'],['Decided',$application->decided_at,'check-circle','#10b981']] as [$label,$date,$icon,$color])
      <div style="display:flex;gap:12px;padding:8px 0;{{ !$loop->last?'border-bottom:1px solid var(--border)':'' }}">
        <div style="width:30px;height:30px;border-radius:50%;background:{{ $date?$color.'18':'var(--bg)' }};display:flex;align-items:center;justify-content:center;color:{{ $date?$color:'var(--muted)' }};font-size:13px;flex-shrink:0"><i class="bi bi-{{ $icon }}"></i></div>
        <div><div style="font-size:12.5px;font-weight:600;color:{{ $date?'var(--dark)':'var(--muted)' }}">{{ $label }}</div><div style="font-size:11.5px;color:var(--muted)">{{ $date?->format('d M Y H:i') ?? 'Pending' }}</div></div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Assign officer --}}
  @if($isPending)
  <div class="card" style="margin-bottom:16px">
    <div class="card-hdr"><span class="card-title">Assign Officer</span></div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.applications.assign-officer',$application) }}">@csrf
        <div class="fg" style="margin-bottom:10px"><select name="officer_id" class="fc"><option value="">— Select Officer —</option>@foreach($officers as $o)<option value="{{ $o->id }}" {{ $application->assigned_officer_id===$o->id?'selected':'' }}>{{ $o->name }}</option>@endforeach</select></div>
        <button type="submit" class="btn btn-p btn-sm" style="width:100%;justify-content:center"><i class="bi bi-person-check"></i> Assign</button>
      </form>
      @if($application->assignedOfficer)
      <form method="POST" action="{{ route('admin.applications.unassign-officer',$application) }}" style="margin-top:8px">@csrf<button class="btn btn-o btn-sm" style="width:100%;justify-content:center"><i class="bi bi-person-x"></i> Unassign</button></form>
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
        <div style="font-size:12px;color:var(--muted)">{{ $k->relationship }} · {{ $k->contact_number }}</div>
      </div>
      @endforeach
    </div>
  </div>
  @endif
</div>
</div>

{{-- MODALS (unchanged) --}}
<div class="mo" id="aModal"><div class="mb" style="max-width:560px">
  <div class="mh"><span class="mt"><i class="bi bi-check-circle-fill" style="color:#10b981"></i> Approve & Create Loan</span><button class="mc" onclick="closeModal('aModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.applications.approve',$application) }}">@csrf
    <div class="mbody">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="fg">
          <label class="fl">Approved Amount (M) *</label>
          <input type="number" name="approved_amount" class="fc" value="{{ $application->requested_amount }}" step="0.01" min="{{ $application->loanProduct?->min_amount ?? 1 }}" max="{{ $application->loanProduct?->max_amount ?? 999999 }}" required oninput="liveCalc()">
          <div style="font-size:10px;color:var(--muted);margin-top:2px">Product Limits: M{{ number_format($application->loanProduct?->min_amount??0,0) }} - M{{ number_format($application->loanProduct?->max_amount??0,0) }}</div>
        </div>
        <div class="fg">
          <label class="fl">Term (months) *</label>
          <input type="number" name="approved_term" class="fc" id="mTerm" value="{{ $application->requested_term }}" min="{{ $application->loanProduct?->min_term_months ?? 1 }}" max="{{ $application->loanProduct?->max_term_months ?? 120 }}" required oninput="liveCalc()">
          <div style="font-size:10px;color:var(--muted);margin-top:2px">Product Limits: {{ $application->loanProduct?->min_term_months ?? 1 }} - {{ $application->loanProduct?->max_term_months ?? 120 }} months</div>
        </div>
        <div class="fg"><label class="fl">Interest Rate (%/month) *</label><input type="number" name="interest_rate" class="fc" id="mRate" value="{{ $application->loanProduct?->interest_rate }}" step="0.01" min="0" required oninput="liveCalc()"></div>
        <div class="fg"><label class="fl">Disbursement Date *</label><input type="date" name="disbursement_date" class="fc" value="{{ now()->addDay()->format('Y-m-d') }}" required></div>
      </div>
      <div id="modalCalc" style="background:#f0fdf4;border:1px solid #d1fae5;border-radius:10px;padding:12px 14px;margin-bottom:6px;display:flex;gap:20px;flex-wrap:wrap">
        <div style="text-align:center"><div style="font-size:11px;color:#065f46;font-weight:600">MONTHLY</div><div style="font-size:18px;font-weight:800;color:#059669" id="mc-monthly">—</div></div>
        <div style="text-align:center"><div style="font-size:11px;color:#065f46;font-weight:600">TOTAL</div><div style="font-size:18px;font-weight:800;color:#059669" id="mc-total">—</div></div>
        <div style="text-align:center"><div style="font-size:11px;color:#065f46;font-weight:600">INTEREST</div><div style="font-size:18px;font-weight:800;color:#059669" id="mc-interest">—</div></div>
      </div>
      <div id="mc-afford" style="display:none;background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:8px 12px;font-size:12px;color:#92400e;margin-bottom:10px"></div>
      <div class="fg"><label class="fl">Approval Notes</label><textarea name="notes" class="fc" rows="2" placeholder="Optional notes…"></textarea></div>
    </div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('aModal')">Cancel</button><button type="submit" class="btn btn-ok"><i class="bi bi-check-lg"></i> Approve & Create Loan</button></div>
  </form>
</div></div>

<div class="mo" id="dModal"><div class="mb">
  <div class="mh"><span class="mt"><i class="bi bi-x-circle-fill" style="color:#ef4444"></i> Decline Application</span><button class="mc" onclick="closeModal('dModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.declines.store') }}">@csrf
    <input type="hidden" name="application_id" value="{{ $application->id }}">
    <div class="mbody">
        <div style="background:rgba(239,68,68,.05); border:1px solid rgba(239,68,68,.1); border-radius:10px; padding:12px; margin-bottom:20px; font-size:12px; color:#991b1b">
            <i class="bi bi-info-circle-fill"></i> Select the most accurate category and reason for this decline. This data is used for portfolio risk reporting.
        </div>
        <div class="fg">
            <label class="fl">Decline Category *</label>
            <select name="category_id" id="decline_category" class="fc" required onchange="updateDeclineReasons(this.value)">
                <option value="">— Select Category —</option>
                @foreach($declineCategories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="fg">
            <label class="fl">Specific Reason *</label>
            <select name="reason" id="decline_reason" class="fc" required disabled>
                <option value="">— Select Category First —</option>
            </select>
        </div>
        <div class="g2">
            <div class="fg">
                <label class="fl">Decline Date *</label>
                <input type="date" name="declined_at" class="fc" value="{{ now()->format('Y-m-d') }}" required max="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="fg">
                <label class="fl">Loan Amount (LSL)</label>
                <input type="number" name="loan_amount" class="fc" value="{{ $application->requested_amount }}" readonly style="background:#f1f5f9">
            </div>
        </div>
        <div class="fg">
            <label class="fl">Officer Notes (Internal)</label>
            <textarea name="notes" class="fc" rows="3" placeholder="DTI ratio, specific credit flags, or document discrepancies..."></textarea>
        </div>
    </div>
    <div class="mf">
        <button type="button" class="btn btn-o" onclick="closeModal('dModal')">Cancel</button>
        <button type="submit" class="btn btn-e"><i class="bi bi-x-lg"></i> Confirm Decline</button>
    </div>
  </form>
</div></div>

<div class="mo" id="hModal"><div class="mb">
  <div class="mh"><span class="mt"><i class="bi bi-pause-fill" style="color:#f59e0b"></i> Place On Hold</span><button class="mc" onclick="closeModal('hModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.applications.hold',$application) }}">@csrf
    <div class="mbody"><div class="fg"><label class="fl">Reason *</label><textarea name="reason" class="fc" rows="3" required></textarea></div></div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('hModal')">Cancel</button><button type="submit" class="btn btn-w"><i class="bi bi-pause-fill"></i> Hold</button></div>
  </form>
</div></div>

<div class="mo" id="iModal"><div class="mb">
  <div class="mh"><span class="mt"><i class="bi bi-question-circle-fill" style="color:#4f46e5"></i> Request Information</span><button class="mc" onclick="closeModal('iModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.applications.request-info',$application) }}">@csrf
    <div class="mbody"><div class="fg"><label class="fl">Message to Borrower *</label><textarea name="message" class="fc" rows="4" required></textarea></div></div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('iModal')">Cancel</button><button type="submit" class="btn btn-p"><i class="bi bi-send"></i> Send</button></div>
  </form>
</div></div>

<div class="mo" id="ovModal"><div class="mb">
  <div class="mh"><span class="mt"><i class="bi bi-pencil-fill" style="color:#4f46e5"></i> Override Terms</span><button class="mc" onclick="closeModal('ovModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.applications.override-terms',$application) }}">@csrf
    <div class="mbody"><div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
      <div class="fg"><label class="fl">Amount (M)</label><input type="number" name="loan_amount" class="fc" value="{{ $application->requested_amount }}" step="0.01"></div>
      <div class="fg"><label class="fl">Rate (%/mo)</label><input type="number" name="interest_rate" class="fc" value="{{ $application->loanProduct?->interest_rate }}" step="0.01"></div>
      <div class="fg"><label class="fl">Term (months)</label><input type="number" name="term_months" class="fc" value="{{ $application->requested_term }}"></div>
    </div></div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('ovModal')">Cancel</button><button type="submit" class="btn btn-p">Update Terms</button></div>
  </form>
</div></div>

<div class="mo" id="affordModal"><div class="mb" style="max-width:600px">
  <div class="mh"><span class="mt"><i class="bi bi-calculator" style="color:#4f46e5"></i> {{ $a ? 'Edit' : 'Add' }} Affordability</span><button class="mc" onclick="closeModal('affordModal')">&times;</button></div>
  <form method="POST" action="{{ route('admin.applications.update-affordability',$application) }}">@csrf
    <div class="mbody">
      <div style="font-size:12px;color:var(--muted);margin-bottom:16px;text-align:center">Update the borrower's income and living expenses to recalculate affordability.</div>
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--p);margin-bottom:8px">Income & Deductions</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px">
        <div class="fg"><label class="fl">Gross / Basic Salary (M)</label><input type="number" name="monthly_earnings" id="aff-gross" class="fc" value="{{ $a->monthly_earnings ?? '' }}" step="0.01" required oninput="liveAfford()"></div>
        <div class="fg"><label class="fl">Tax Deduction (M)</label><input type="number" name="tax_deduction" id="aff-tax" class="fc" value="{{ $a->tax_deduction ?? '' }}" step="0.01" oninput="liveAfford()"></div>
        <div class="fg"><label class="fl">Existing Loans Ded. (M)</label><input type="number" name="existing_loans_deduction" id="aff-loans" class="fc" value="{{ $a->existing_loans_deduction ?? '' }}" step="0.01" oninput="liveAfford()"></div>
        <div class="fg"><label class="fl">Other Deductions (M)</label><input type="number" name="other_deductions" id="aff-other" class="fc" value="{{ $a->other_deductions ?? '' }}" step="0.01" oninput="liveAfford()"></div>
      </div>
      
      <div id="affordPreview" style="background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:20px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div style="text-align:center"><div style="font-size:10px;color:var(--muted);font-weight:600">PREVIEW TOTAL DED.</div><div style="font-size:18px;font-weight:800;color:#dc2626" id="pre-total-ded">—</div></div>
        <div style="text-align:center"><div style="font-size:10px;color:var(--muted);font-weight:600">PREVIEW NET SALARY</div><div style="font-size:18px;font-weight:800;color:#16a34a" id="pre-net-salary">—</div></div>
      </div>

      <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--p);margin-bottom:8px">Living Expenses</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
        <div class="fg"><label class="fl">Rent</label><input type="number" name="rent" class="fc aff-exp" value="{{ $a->rent ?? '' }}" step="0.01" oninput="liveAfford()"></div>
        <div class="fg"><label class="fl">Groceries</label><input type="number" name="groceries" class="fc aff-exp" value="{{ $a->groceries ?? '' }}" step="0.01" oninput="liveAfford()"></div>
        <div class="fg"><label class="fl">Transport</label><input type="number" name="transport" class="fc aff-exp" value="{{ $a->transport ?? '' }}" step="0.01" oninput="liveAfford()"></div>
        <div class="fg"><label class="fl">Utilities</label><input type="number" name="utilities" class="fc aff-exp" value="{{ $a->utilities ?? '' }}" step="0.01" oninput="liveAfford()"></div>
        <div class="fg"><label class="fl">Education</label><input type="number" name="education" class="fc aff-exp" value="{{ $a->education ?? '' }}" step="0.01" oninput="liveAfford()"></div>
        <div class="fg"><label class="fl">Communication</label><input type="number" name="communication" class="fc aff-exp" value="{{ $a->communication ?? '' }}" step="0.01" oninput="liveAfford()"></div>
        <div class="fg"><label class="fl">Medical</label><input type="number" name="medical" class="fc aff-exp" value="{{ $a->medical ?? '' }}" step="0.01" oninput="liveAfford()"></div>
        <div class="fg"><label class="fl">Other Loans</label><input type="number" name="other_loan_repayments" class="fc aff-exp" value="{{ $a->other_loan_repayments ?? '' }}" step="0.01" oninput="liveAfford()"></div>
        <div class="fg"><label class="fl">Other Exp.</label><input type="number" name="other_expenses" class="fc aff-exp" value="{{ $a->other_expenses ?? '' }}" step="0.01" oninput="liveAfford()"></div>
      </div>
    </div>
    <div class="mf"><button type="button" class="btn btn-o" onclick="closeModal('affordModal')">Cancel</button><button type="submit" class="btn btn-p">Save Affordability</button></div>
  </form>
</div></div>

<div class="mo" id="docPreviewModal"><div class="mb" style="max-width:800px;width:95%">
  <div class="mh"><span class="mt" id="docPreviewTitle"><i class="bi bi-file-earmark-text" style="color:var(--p)"></i> Document Preview</span><button class="mc" onclick="closeModal('docPreviewModal')">&times;</button></div>
  <div class="mbody" style="padding:0;height:70vh;background:#f8fafc">
    <iframe id="docPreviewFrame" src="" style="width:100%;height:100%;border:none;display:block"></iframe>
  </div>
</div></div>

<script>
const INITIATION_RATE = {{ $application->loanProduct?->initiation_fee_rate ?? 40 }} / 100;
const ADMIN_PER_MONTH = {{ $application->loanProduct?->admin_fee_fixed ?? 50 }};

function flatCalc(amount, ratePercent, term) {
  if (!amount || !term) return null;
  const rate=ratePercent/100, interest=amount*rate*term, initiation=amount*INITIATION_RATE, admin=ADMIN_PER_MONTH*term, total=amount+interest+initiation+admin, monthly=total/term;
  return {rate,interest,initiation,admin,total,monthly,principalPerMonth:amount/term,interestPerMonth:amount*rate,initiationPerMonth:initiation/term};
}

function liveCalc() {
  const amount=parseFloat(document.querySelector('[name=approved_amount]')?.value||0);
  const term=parseInt(document.getElementById('mTerm')?.value||0);
  const rate=parseFloat(document.getElementById('mRate')?.value||0);
  const c=flatCalc(amount,rate,term); if(!c) return;
  document.getElementById('mc-monthly').textContent='M '+c.monthly.toFixed(2);
  document.getElementById('mc-total').textContent='M '+c.total.toFixed(2);
  document.getElementById('mc-interest').textContent='M '+c.interest.toFixed(2);
  const netSal={{ (float)($application->affordability?->net_salary ?? 0) }};
  const warn=document.getElementById('mc-afford');
  if(warn&&netSal>0){const limit=netSal*0.3;if(c.monthly>limit){warn.style.display='block';warn.textContent='⚠ Monthly M'+c.monthly.toFixed(2)+' exceeds 30% limit M'+limit.toFixed(2);}else warn.style.display='none';}
}

function liveAfford() {
  const gross = parseFloat(document.getElementById('aff-gross').value) || 0;
  const tax = parseFloat(document.getElementById('aff-tax').value) || 0;
  const loans = parseFloat(document.getElementById('aff-loans').value) || 0;
  const other = parseFloat(document.getElementById('aff-other').value) || 0;
  
  const totalDed = tax + loans + other;
  const net = gross - totalDed;
  
  document.getElementById('pre-total-ded').textContent = 'M ' + totalDed.toFixed(2);
  document.getElementById('pre-net-salary').textContent = 'M ' + net.toFixed(2);
}

function previewSchedule() {
  const amount=parseFloat(document.getElementById('schAmt').value||0);
  const rateP=parseFloat(document.getElementById('schRate').value||0);
  const term=parseInt(document.getElementById('schTerm').value||0);
  const c=flatCalc(amount,rateP,term); if(!c) return;
  let rows='',rem=amount;
  for(let i=1;i<=term;i++){const isLast=i===term;const prin=isLast?parseFloat(rem.toFixed(2)):parseFloat(c.principalPerMonth.toFixed(2));const init=parseFloat(c.initiationPerMonth.toFixed(2));const tot=parseFloat((prin+c.interestPerMonth+ADMIN_PER_MONTH+init).toFixed(2));rem=Math.max(0,rem-prin);rows+=`<tr style="font-size:12.5px"><td style="padding:8px 10px;border-bottom:1px solid var(--border)">${i}</td><td style="padding:8px 10px;border-bottom:1px solid var(--border);color:var(--p)">M ${prin.toFixed(2)}</td><td style="padding:8px 10px;border-bottom:1px solid var(--border);color:#f59e0b">M ${c.interestPerMonth.toFixed(2)}</td><td style="padding:8px 10px;border-bottom:1px solid var(--border);color:#8b5cf6">M ${init.toFixed(2)}</td><td style="padding:8px 10px;border-bottom:1px solid var(--border);color:#64748b">M ${ADMIN_PER_MONTH.toFixed(2)}</td><td style="padding:8px 10px;border-bottom:1px solid var(--border);font-weight:700">M ${tot.toFixed(2)}</td><td style="padding:8px 10px;border-bottom:1px solid var(--border)">M ${rem.toFixed(2)}</td></tr>`;}
  document.getElementById('scheduleResult').innerHTML=`<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px;text-align:center;font-size:13px"><div style="background:rgba(79,70,229,.06);border-radius:10px;padding:12px;border:1px solid rgba(79,70,229,.12)"><div style="font-size:11px;color:var(--muted)">Monthly</div><div style="font-size:19px;font-weight:800;color:var(--p)">M ${c.monthly.toFixed(2)}</div></div><div style="background:rgba(16,185,129,.06);border-radius:10px;padding:12px;border:1px solid rgba(16,185,129,.12)"><div style="font-size:11px;color:var(--muted)">Total Repay</div><div style="font-size:19px;font-weight:800;color:#10b981">M ${c.total.toFixed(2)}</div></div><div style="background:rgba(245,158,11,.06);border-radius:10px;padding:12px;border:1px solid rgba(245,158,11,.12)"><div style="font-size:11px;color:var(--muted)">Interest+Fees</div><div style="font-size:19px;font-weight:800;color:#f59e0b">M ${(c.interest+c.initiation+c.admin).toFixed(2)}</div></div><div style="background:rgba(100,116,139,.06);border-radius:10px;padding:12px;border:1px solid rgba(100,116,139,.12)"><div style="font-size:11px;color:var(--muted)">Cash to Client</div><div style="font-size:19px;font-weight:800;color:#475569">M ${amount.toFixed(2)}</div></div></div><table style="width:100%;border-collapse:collapse"><thead><tr style="background:#f8fafc;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)"><th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">#</th><th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Principal</th><th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Interest</th><th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Initiation</th><th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Admin</th><th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Total</th><th style="padding:9px 10px;border-bottom:1px solid var(--border);text-align:left">Balance</th></tr></thead><tbody>${rows}</tbody></table>`;
}

function previewDoc(url, title) {
  document.getElementById('docPreviewTitle').innerHTML = '<i class="bi bi-file-earmark-text" style="color:var(--p)"></i> ' + title;
  document.getElementById('docPreviewFrame').src = url;
  openModal('docPreviewModal');
}

let declineTaxonomy = {!! $declineCategories->toJson() !!};
function loadDeclineTaxonomy() {
    // Already loaded via Blade
}

function updateDeclineReasons(catId) {
    const reasonSelect = document.getElementById('decline_reason');
    reasonSelect.innerHTML = '<option value="">— Select Reason —</option>';
    
    if (!catId) {
        reasonSelect.disabled = true;
        return;
    }

    const category = declineTaxonomy.find(c => c.id == catId);
    if (category && category.reasons) {
        category.reasons.forEach(reason => {
            const opt = document.createElement('option');
            opt.value = reason;
            opt.textContent = reason;
            reasonSelect.appendChild(opt);
        });
        reasonSelect.disabled = false;
    }
}
</script>

{{-- ── FLOATING CHAT WIDGET ─────────────────────────────────── --}}
<div id="chatWidget" style="position:fixed;bottom:24px;right:24px;z-index:9999;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
  {{-- The Chat Popup Window --}}
  <div id="chatPopup" style="display:none;width:350px;height:500px;background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,.15);flex-direction:column;overflow:hidden;margin-bottom:16px;border:1px solid var(--border);">
    <div style="background:var(--p);padding:16px 20px;color:#fff;font-weight:700;display:flex;justify-content:space-between;align-items:center;">
      <div style="display:flex;align-items:center;gap:10px">
        <i class="bi bi-headset" style="font-size:18px"></i> Conversation with Borrower
      </div>
      <button onclick="toggleChat()" style="background:none;border:none;color:#fff;cursor:pointer;"><i class="bi bi-x-lg"></i></button>
    </div>
    
    <div id="chatMessages" style="flex:1;background:#f8fafc;padding:20px;overflow-y:auto;display:flex;flex-direction:column;gap:16px">
      {{-- Messages will be loaded here via AJAX --}}
    </div>

    <div style="padding:16px;background:#fff;border-top:1px solid var(--border)">
      <form id="chatForm" onsubmit="sendChatMessage(event)">
        <div style="display:flex;gap:10px">
          <input type="text" id="chatInput" placeholder="Type a message..." required style="flex:1;border-radius:99px;padding:10px 16px;border:1px solid #cbd5e1;outline:none;font-size:14px;">
          <button type="submit" id="chatSendBtn" style="background:var(--p);color:#fff;border:none;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .1s;"><i class="bi bi-send-fill" style="margin-left:-2px"></i></button>
        </div>
      </form>
    </div>
  </div>

  {{-- The Floating Button --}}
  <button id="chatFloatingBtn" onclick="toggleChat()" style="background:var(--p);color:#fff;border:none;width:60px;height:60px;border-radius:50%;box-shadow:0 6px 20px rgba(79,70,229,.4);display:flex;align-items:center;justify-content:center;font-size:24px;cursor:pointer;cursor:pointer;transition:transform .2s;float:right;position:relative;">
    <i class="bi bi-chat-dots-fill"></i>
  </button>
</div>

<script>
let chatOpen = false;
const appId = {{ $application->id }};
const msgUrl = "{{ route('admin.applications.messages.get', $application) }}";
const sendUrl = "{{ route('admin.applications.messages.send', $application) }}";
const csrf = "{{ csrf_token() }}";

function toggleChat() {
  chatOpen = !chatOpen;
  document.getElementById('chatPopup').style.display = chatOpen ? 'flex' : 'none';
  if(chatOpen) {
    loadMessages();
    setTimeout(() => document.getElementById('chatInput').focus(), 100);
  }
}

function renderMessage(msg) {
  const isMine = (msg.sender_type === 'admin' || msg.sender_type === 'officer');
  const align = isMine ? 'flex-end' : 'flex-start';
  const flexDirection = isMine ? 'row-reverse' : 'row';
  const bg = isMine ? 'var(--p)' : '#fff';
  const textCol = isMine ? '#fff' : '#334155';
  const radius = isMine ? '16px 16px 0 16px' : '16px 16px 16px 0';
  const avatarBg = isMine ? 'rgba(79,70,229,.2)' : '#e2e8f0';
  const avatarCol = isMine ? 'var(--p)' : '#475569';
  const avatarText = msg.sender_initial;
  const shadow = isMine ? '0 4px 12px rgba(79,70,229,.2)' : '0 2px 8px rgba(0,0,0,.05)';

  return `
    <div style="display:flex;gap:12px;align-self:${align};max-width:85%;flex-direction:${flexDirection}">
      <div style="width:32px;height:32px;border-radius:50%;background:${avatarBg};color:${avatarCol};display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;font-size:12px">${avatarText}</div>
      <div style="display:flex;flex-direction:column;align-items:${align}">
        <div style="background:${bg};color:${textCol};padding:12px 16px;border-radius:${radius};font-size:13.5px;line-height:1.5;box-shadow:${shadow};box-sizing:border-box;">${msg.content}</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">${msg.sender_name} &nbsp;·&nbsp; ${msg.created_at}</div>
      </div>
    </div>
  `;
}

function loadMessages() {
  fetch(msgUrl)
    .then(r => r.json())
    .then(data => {
      const container = document.getElementById('chatMessages');
      if (data.length === 0) {
         container.innerHTML = '<div style="text-align:center;padding:40px;color:var(--muted);margin:auto"><i class="bi bi-chat-heart" style="font-size:40px;opacity:.25;display:block;margin-bottom:10px"></i><div>No messages yet.<br>Start the conversation.</div></div>';
      } else {
         container.innerHTML = data.map(renderMessage).join('');
         container.scrollTop = container.scrollHeight;
      }
    });
}

function sendChatMessage(e) {
  e.preventDefault();
  const input = document.getElementById('chatInput');
  const btn = document.getElementById('chatSendBtn');
  const content = input.value.trim();
  if(!content) return;

  btn.style.transform = 'scale(0.9)';
  btn.style.opacity = '0.7';

  fetch(sendUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    body: JSON.stringify({ content: content })
  }).then(r => r.json()).then(res => {
    btn.style.transform = 'none';
    btn.style.opacity = '1';
    if(res.success) {
      input.value = '';
      loadMessages();
    }
  });
}
</script>
{{-- Edit Personal Modal --}}
<div class="mo" id="editPersonalModal">
    <div class="mb" style="max-width:700px">
        <form action="{{ route('admin.applications.update-personal', $application) }}" method="POST">
            @csrf
            <div class="mh">
                <div class="mt">Edit Personal Information</div>
                <button type="button" class="mc" onclick="closeModal('editPersonalModal')">&times;</button>
            </div>
            <div class="mbody">
                <div class="g2">
                    <div class="fg"><label class="fl">Title</label><input type="text" name="title" class="fc" value="{{ $application->title }}"></div>
                    <div class="fg"><label class="fl">First Name *</label><input type="text" name="first_name" class="fc" value="{{ $application->first_name }}" required></div>
                    <div class="fg"><label class="fl">Surname *</label><input type="text" name="surname" class="fc" value="{{ $application->surname }}" required></div>
                    <div class="fg"><label class="fl">Maiden Name</label><input type="text" name="maiden_name" class="fc" value="{{ $application->maiden_name }}"></div>
                    <div class="fg"><label class="fl">National ID *</label><input type="text" name="national_id" class="fc" value="{{ $application->national_id }}" required></div>
                    <div class="fg"><label class="fl">Date of Birth</label><input type="date" name="date_of_birth" class="fc" value="{{ $application->date_of_birth?->format('Y-m-d') }}"></div>
                    <div class="fg"><label class="fl">Gender</label><select name="gender" class="fc"><option value="male" {{ $application->gender=='male'?'selected':'' }}>Male</option><option value="female" {{ $application->gender=='female'?'selected':'' }}>Female</option></select></div>
                    <div class="fg"><label class="fl">Marital Status</label><select name="marital_status" class="fc"><option value="single" {{ $application->marital_status=='single'?'selected':'' }}>Single</option><option value="married" {{ $application->marital_status=='married'?'selected':'' }}>Married</option><option value="divorced" {{ $application->marital_status=='divorced'?'selected':'' }}>Divorced</option><option value="widowed" {{ $application->marital_status=='widowed'?'selected':'' }}>Widowed</option></select></div>
                    <div class="fg"><label class="fl">Cell Number *</label><input type="text" name="cell_number" class="fc" value="{{ $application->cell_number }}" required></div>
                    <div class="fg"><label class="fl">Email</label><input type="email" name="email" class="fc" value="{{ $application->email }}"></div>
                </div>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-o" onclick="closeModal('editPersonalModal')">Cancel</button>
                <button type="submit" class="btn btn-p">Update Personal Info</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Address Modal --}}
<div class="mo" id="editAddressModal">
    <div class="mb" style="max-width:700px">
        <form action="{{ route('admin.applications.update-address', $application) }}" method="POST">
            @csrf
            <div class="mh">
                <div class="mt">Edit Address Details</div>
                <button type="button" class="mc" onclick="closeModal('editAddressModal')">&times;</button>
            </div>
            <div class="mbody">
                <div class="fg"><label class="fl">Residential Address *</label><input type="text" name="residential_address" class="fc" value="{{ $application->residential_address }}" required></div>
                <div class="g2">
                    <div class="fg"><label class="fl">Village *</label><input type="text" name="village" class="fc" value="{{ $application->village }}" required></div>
                    <div class="fg"><label class="fl">Town *</label><input type="text" name="town" class="fc" value="{{ $application->town }}" required></div>
                    <div class="fg"><label class="fl">District *</label>
                        <select name="district" class="fc" required>
                            <option value="">— Select —</option>
                            @foreach(['Maseru','Berea','Leribe','Butha-Buthe','Mokhotlong','Thaba-Tseka','Qacha\'s Nek','Quthing','Mohale\'s Hoek','Mafeteng'] as $d)
                            <option {{ $application->district===$d?'selected':'' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg"><label class="fl">Residence Type *</label>
                        <select name="residence_type" class="fc" required>
                            <option value="">— Select —</option>
                            <option value="own" {{ $application->residence_type==='own'?'selected':'' }}>Own</option>
                            <option value="rent" {{ $application->residence_type==='rent'?'selected':'' }}>Rent</option>
                            <option value="family" {{ $application->residence_type==='family'?'selected':'' }}>Family</option>
                            <option value="employer" {{ $application->residence_type==='employer'?'selected':'' }}>Employer Provided</option>
                        </select>
                    </div>
                    <div class="fg"><label class="fl">Duration at Address</label><input type="text" name="address_duration" class="fc" value="{{ $application->address_duration }}"></div>
                </div>
                <div class="fg"><label class="fl">Nearest Landmark *</label><input type="text" name="nearest_landmark" class="fc" value="{{ $application->nearest_landmark }}" required></div>
                <div class="fg"><label class="fl">Home Directions *</label><textarea name="home_directions" class="fc" rows="2" required>{{ $application->home_directions }}</textarea></div>
                <div class="g2">
                    <div class="fg"><label class="fl">GPS Latitude</label><input type="text" name="gps_latitude" class="fc" value="{{ $application->gps_latitude }}"></div>
                    <div class="fg"><label class="fl">GPS Longitude</label><input type="text" name="gps_longitude" class="fc" value="{{ $application->gps_longitude }}"></div>
                </div>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-o" onclick="closeModal('editAddressModal')">Cancel</button>
                <button type="submit" class="btn btn-p">Update Address</button>
            </div>
        </form>
    </div>
</div>
{{-- Edit Employment Modal --}}
<div class="mo" id="editEmploymentModal">
    <div class="mb" style="max-width:700px">
        <form action="{{ route('admin.applications.update-employment', $application) }}" method="POST">
            @csrf
            <div class="mh">
                <div class="mt">Edit Employment Details</div>
                <button type="button" class="mc" onclick="closeModal('editEmploymentModal')">&times;</button>
            </div>
            <div class="mbody">
                <div class="g2">
                    <div class="fg">
                        <label class="fl">Employer Name *</label>
                        <input type="text" name="employer_name" class="fc" value="{{ $application->employment->employer_name ?? '' }}" required>
                    </div>
                    <div class="fg">
                        <label class="fl">Employer Type *</label>
                        <select name="employer_type" class="fc" required onchange="toggleCategoryEdit(this.value)">
                            <option value="">— Select —</option>
                            @foreach(['government'=>'Government','private'=>'Private Sector','sme'=>'SMEs'] as $v=>$l)
                            <option value="{{ $v }}" {{ ($application->employment->employer_type ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="fg" id="edit_cat_wrapper" style="display: {{ in_array($application->employment?->employer_type, ['government', 'sme', 'private']) ? 'block' : 'none' }}">
                    <label class="fl">Work Sector / Category *</label>
                    <select name="employer_category" id="edit_employer_category" class="fc" {{ in_array($application->employment?->employer_type, ['government', 'sme', 'private']) ? 'required' : '' }}>
                        <option value="">— Select Category —</option>
                        @foreach(['Defence','NSS','Police','LCS','Pensioner','Civil servants','Teacher','Private sector','SMEs'] as $c)
                        <option {{ ($application->employment->employer_category ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="g2">
                    <div class="fg">
                        <label class="fl">Job Title *</label>
                        <input type="text" name="job_title" class="fc" value="{{ $application->employment->job_title ?? '' }}" required>
                    </div>
                    <div class="fg">
                        <label class="fl">Department</label>
                        <input type="text" name="department" class="fc" value="{{ $application->employment->department ?? '' }}">
                    </div>
                    <div class="fg">
                        <label class="fl">Employee Number *</label>
                        <input type="text" name="employment_number" class="fc" value="{{ $application->employment->employment_number ?? '' }}" required>
                    </div>
                    <div class="fg">
                        <label class="fl">HR/Employer Contact *</label>
                        <input type="text" name="contact_number" class="fc" value="{{ $application->employment->contact_number ?? '' }}" required>
                    </div>
                    <div class="fg">
                        <label class="fl">Employment Expiry</label>
                        <input type="date" name="employment_expiry_date" class="fc" value="{{ $application->employment?->employment_expiry_date?->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-o" onclick="closeModal('editEmploymentModal')">Cancel</button>
                <button type="submit" class="btn btn-p">Update Employment</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Bank Modal --}}
<div class="mo" id="editBankModal">
    <div class="mb">
        <form action="{{ route('admin.applications.update-bank-details', $application) }}" method="POST">
            @csrf
            <div class="mh">
                <div class="mt">Edit Bank Details</div>
                <button type="button" class="mc" onclick="closeModal('editBankModal')">&times;</button>
            </div>
            <div class="mbody">
                <div class="fg">
                    <label class="fl">Bank Name *</label>
                    <select name="bank_name" id="edit_bank_name" class="fc" data-prev="{{ $application->bankDetails->bank_name ?? '' }}" required onchange="loadBranchesEdit(this.value)">
                        <option value="">— Select Bank —</option>
                    </select>
                </div>
                <div class="fg">
                    <label class="fl">Account Holder Name *</label>
                    <input type="text" name="account_holder_name" class="fc" value="{{ $application->bankDetails->account_holder_name ?? '' }}" required>
                </div>
                <div class="fg">
                    <label class="fl">Account Number *</label>
                    <input type="text" name="account_number" class="fc" value="{{ $application->bankDetails->account_number ?? '' }}" required>
                </div>
                <div class="g2">
                    <div class="fg">
                        <label class="fl">Branch Name *</label>
                        <select name="branch_name" id="edit_branch_name" class="fc" data-prev="{{ $application->bankDetails->branch_name ?? '' }}" required onchange="updateBranchCodeEdit(this.options[this.selectedIndex])">
                            <option value="">— Select Branch —</option>
                        </select>
                    </div>
                    <div class="fg">
                        <label class="fl">Branch Code</label>
                        <input type="text" name="branch_code" id="edit_branch_code" class="fc" value="{{ $application->bankDetails->branch_code ?? '' }}" placeholder="Auto-filled">
                    </div>
                </div>
                <div class="fg">
                    <label class="fl">Account Type *</label>
                    <select name="account_type" class="fc" required>
                        <option value="savings" {{ ($application->bankDetails->account_type ?? '') === 'savings' ? 'selected' : '' }}>Savings</option>
                        <option value="cheque" {{ ($application->bankDetails->account_type ?? '') === 'cheque' ? 'selected' : '' }}>Cheque / Current</option>
                    </select>
                </div>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-o" onclick="closeModal('editBankModal')">Cancel</button>
                <button type="submit" class="btn btn-p">Update Bank Details</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Request Modal --}}
<div class="mo" id="editRequestModal">
    <div class="mb" style="max-width:600px">
        <form action="{{ route('admin.applications.update-loan-request', $application) }}" method="POST">
            @csrf
            <div class="mh">
                <div class="mt">Edit Loan Request</div>
                <button type="button" class="mc" onclick="closeModal('editRequestModal')">&times;</button>
            </div>
            <div class="mbody">
                <div class="g2">
                    <div class="fg"><label class="fl">Requested Amount (M) *</label><input type="number" name="requested_amount" class="fc" value="{{ $application->requested_amount }}" required></div>
                    <div class="fg"><label class="fl">Requested Term (Months) *</label><input type="number" name="requested_term" class="fc" value="{{ $application->requested_term }}" required></div>
                </div>
                <div class="fg"><label class="fl">Loan Purpose</label><input type="text" name="loan_purpose" class="fc" value="{{ $application->loan_purpose }}"></div>
                <div class="g2">
                    <div class="fg">
                        <label class="fl">Payout Method *</label>
                        <select name="payout_method" class="fc" required>
                            @foreach(['bank_transfer'=>'Bank Transfer','mobile_money'=>'Mobile Money','cash'=>'Cash'] as $v=>$l)
                            <option value="{{ $v }}" {{ $application->payout_method === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg">
                        <label class="fl">Collection Method *</label>
                        <select name="collection_method" class="fc" required>
                            @foreach(['payroll'=>'Payroll Deduction','debit_order'=>'Debit Order','cash'=>'Cash / Direct'] as $v=>$l)
                            <option value="{{ $v }}" {{ $application->collection_method === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fg">
                        <label class="fl">Salary Payday *</label>
                        <input type="number" name="salary_payday" class="fc" value="{{ $application->salary_payday ?? 25 }}" min="1" max="31" required>
                        <small style="color:var(--muted)">Day of month (1-31)</small>
                    </div>
                </div>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-o" onclick="closeModal('editRequestModal')">Cancel</button>
                <button type="submit" class="btn btn-p">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function showEditEmploymentModal() { openModal('editEmploymentModal'); }
function showEditBankModal() { openModal('editBankModal'); }
function toggleCategoryEdit(type) {
    const wrapper = document.getElementById('edit_cat_wrapper');
    const select = document.getElementById('edit_employer_category');
    if(wrapper && select) {
        if(['government', 'sme', 'private'].includes(type)) {
            wrapper.style.display = 'block';
            select.setAttribute('required', 'required');
            if(type === 'private') {
                select.value = 'Private sector';
            }
        } else {
            wrapper.style.display = 'none';
            select.removeAttribute('required');
            select.value = '';
        }
    }
}

async function loadBanksEdit() {
    const el = document.getElementById('edit_bank_name');
    if (!el) return;
    try {
        const res = await fetch('/api/banks');
        const banks = await res.json();
        const prev = el.getAttribute('data-prev');
        banks.forEach(b => {
            const opt = new Option(b.name, b.id);
            if (b.id == prev || b.name == prev) opt.selected = true;
            el.add(opt);
        });
        if (el.value) loadBranchesEdit(el.value);
    } catch(e) {}
}

async function loadBranchesEdit(bankId) {
    const el = document.getElementById('edit_branch_name');
    if (!el || !bankId) return;
    el.innerHTML = '<option value="">— Select Branch —</option>';
    try {
        const res = await fetch(`/api/banks/${bankId}/branches`);
        const branches = await res.json();
        const prev = el.getAttribute('data-prev');
        branches.forEach(b => {
            const opt = new Option(b.name, b.name);
            opt.setAttribute('data-code', b.code);
            if (b.name == prev) opt.selected = true;
            el.add(opt);
        });
        updateBranchCodeEdit(el.options[el.selectedIndex]);
    } catch(e) {}
}

function updateBranchCodeEdit(opt) {
    const codeEl = document.getElementById('edit_branch_code');
    if (codeEl && opt && opt.getAttribute('data-code')) {
        codeEl.value = opt.getAttribute('data-code');
    }
}

document.addEventListener("DOMContentLoaded", function() {
    loadBanksEdit();
});
</script>
@endsection
