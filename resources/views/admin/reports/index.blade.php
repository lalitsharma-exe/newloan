@extends('admin.layouts.app')
@section('title','Reports')
@section('page-title','Reports')
@section('content')
<div class="grid grid-3 mb-6">
@foreach([
  ['portfolio','Portfolio Report','bar-chart-fill','View complete loan portfolio breakdown','primary'],
  ['disbursement','Disbursement Report','arrow-up-circle-fill','Loans disbursed by date/product','success'],
  ['repayment','Repayment Report','cash-stack','Payment collections summary','info'],
  ['arrears','Arrears Report','exclamation-triangle-fill','Overdue loans by aging bucket','danger'],
  ['collections','Collections Report','piggy-bank-fill','Daily/monthly collection totals','warning'],
  ['product-performance','Product Performance','box-fill','Performance by loan product','secondary'],
] as [$route,$title,$icon,$desc,$color])
<a href="{{ route('admin.reports.'.$route) }}" style="text-decoration:none">
  <div class="card" style="transition:transform .2s,box-shadow .2s;cursor:pointer" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 25px rgba(0,0,0,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
    <div class="card-body" style="display:flex;align-items:center;gap:16px">
      <div class="stat-icon {{ $color }}" style="width:54px;height:54px;font-size:24px"><i class="bi bi-{{ $icon }}"></i></div>
      <div><div style="font-size:15px;font-weight:700;color:#0f172a">{{ $title }}</div><div class="text-muted" style="margin-top:3px">{{ $desc }}</div></div>
    </div>
  </div>
</a>
@endforeach
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Quick Export</span></div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.reports.export') }}" style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end">
      @csrf
      <div class="form-group" style="margin-bottom:0"><label class="form-label">Report Type</label>
        <select name="report_type" class="form-control">
          @foreach(['portfolio','disbursement','repayment','arrears','collections','product_performance','officer_performance'] as $t)
          <option value="{{ $t }}">{{ ucfirst(str_replace('_',' ',$t)) }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group" style="margin-bottom:0"><label class="form-label">Format</label>
        <select name="format" class="form-control"><option value="csv">CSV</option><option value="pdf">PDF</option></select>
      </div>
      <div class="form-group" style="margin-bottom:0"><label class="form-label">From</label><input type="date" name="date_from" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}"></div>
      <div class="form-group" style="margin-bottom:0"><label class="form-label">To</label><input type="date" name="date_to" class="form-control" value="{{ now()->format('Y-m-d') }}"></div>
      <button type="submit" class="btn btn-primary"><i class="bi bi-download"></i> Export</button>
    </form>
  </div>
</div>
@endsection
