@extends('admin.layouts.app')
@section('title','Reports')
@section('page-title','Reports')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Home</a> / Reports
@endsection
@section('content')

<div class="g3" style="gap:16px;margin-bottom:16px">
  @foreach([
    ['portfolio',       'Portfolio Report',        'pie-chart-fill',          'Total loans, portfolio value, growth','p'],
    ['disbursement',    'Disbursement Report',     'arrow-up-circle-fill',    'Loans issued today/week/month','ok'],
    ['collections',     'Collection Report',       'cash-stack',              'Payments collected, collection rate','ok'],
    ['outstanding',     'Outstanding Loans',       'wallet2',                 'All unpaid loans with balances','i'],
    ['arrears',         'Arrears Report',          'exclamation-triangle-fill','Overdue loans by aging bucket','w'],
    ['par',             'PAR Report',              'shield-exclamation',      'Portfolio at Risk: PAR 1/30/60/90','e'],
    ['default',         'Default Report',          'x-circle-fill',           'Defaulted & written-off loans','e'],
    ['officer-performance','Officer Performance',  'person-badge-fill',       'Collection rate & portfolio per officer','p'],
    ['income-statement','Income & Profit',         'graph-up-arrow',          'Interest, fees, admin fees, net profit','ok'],
    ['applications',    'Application Report',      'file-earmark-text-fill',  'Submitted/approved/declined/pending','i'],
    ['payment-failures','Payment Failure Report',  'credit-card-2-front',     'Failed debits, blocked cards','e'],
  ] as [$route,$title,$icon,$desc,$color])
  <a href="{{ route('admin.reports.'.$route) }}" style="text-decoration:none">
    <div class="card" style="transition:all .2s;cursor:pointer"
      onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 25px rgba(0,0,0,.09)'"
      onmouseout="this.style.transform='';this.style.boxShadow=''">
      <div style="padding:18px 20px;display:flex;align-items:center;gap:14px">
        <div class="si {{ $color }}" style="flex-shrink:0"><i class="bi bi-{{ $icon }}"></i></div>
        <div>
          <div style="font-size:13.5px;font-weight:700">{{ $title }}</div>
          <div class="muted" style="margin-top:3px">{{ $desc }}</div>
        </div>
        <i class="bi bi-chevron-right" style="margin-left:auto;color:var(--muted);font-size:12px"></i>
      </div>
    </div>
  </a>
  @endforeach
</div>

{{-- Quick Export --}}
<div class="card">
  <div class="card-hdr"><span class="card-title"><i class="bi bi-download"></i> Quick CSV Export</span></div>
  <form method="POST" action="{{ route('admin.reports.export') }}">
    @csrf
    <div class="card-body">
      <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
        <div class="fg" style="margin-bottom:0;min-width:180px">
          <label class="fl">Report</label>
          <select name="type" class="fc">
            @foreach(['portfolio','disbursement','collections','outstanding','arrears','par','default','income_statement','applications','payment_failures','officer_performance'] as $t)
            <option value="{{ $t }}">{{ ucfirst(str_replace('_',' ',$t)) }}</option>
            @endforeach
          </select>
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">From</label>
          <input type="date" name="date_from" class="fc" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="fg" style="margin-bottom:0">
          <label class="fl">To</label>
          <input type="date" name="date_to" class="fc" value="{{ now()->format('Y-m-d') }}">
        </div>
        <button type="submit" class="btn btn-p"><i class="bi bi-download"></i> Export CSV</button>
      </div>
    </div>
  </form>
</div>
@endsection
