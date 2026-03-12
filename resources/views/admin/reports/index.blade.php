@extends('admin.layouts.app')

@section('title','Reports')
@section('page-title','Reports')

@section('content')

<div class="g3 mb6">

@foreach([
  ['portfolio','Portfolio Report','bar-chart-fill','View complete loan portfolio breakdown','p'],
  ['disbursement','Disbursement Report','arrow-up-circle-fill','Loans disbursed by date/product','ok'],
  ['repayment','Repayment Report','cash-stack','Payment collections summary','i'],
  ['arrears','Arrears Report','exclamation-triangle-fill','Overdue loans by aging bucket','e'],
  ['collections','Collections Report','piggy-bank-fill','Daily/monthly collection totals','w'],
  ['product-performance','Product Performance','box-fill','Performance by loan product','s'],
] as [$route,$title,$icon,$desc,$color])

<a href="{{ route('admin.reports.'.$route) }}" style="text-decoration:none">

<div class="card" style="cursor:pointer;transition:all .2s"
onmouseover="this.style.transform='translateY(-3px)'"
onmouseout="this.style.transform=''">

<div class="card-body flex aic gap3">

<div class="si {{ $color }}" style="width:54px;height:54px;font-size:22px">
<i class="bi bi-{{ $icon }}"></i>
</div>

<div>
<div style="font-size:15px;font-weight:700">{{ $title }}</div>
<div class="muted">{{ $desc }}</div>
</div>

</div>
</div>

</a>

@endforeach

</div>


<div class="card">

<div class="card-hdr">
<span class="card-title">Quick Export</span>
</div>

<div class="card-body">

<form method="POST"
action="{{ route('admin.reports.export') }}"
class="flex gap3 aic">

@csrf

<div class="fg">
<label class="fl">Report Type</label>

<select name="report_type" class="fc">

@foreach([
'portfolio',
'disbursement',
'repayment',
'arrears',
'collections',
'product_performance',
'officer_performance'
] as $t)

<option value="{{ $t }}">
{{ ucfirst(str_replace('_',' ',$t)) }}
</option>

@endforeach

</select>
</div>


<div class="fg">
<label class="fl">Format</label>

<select name="format" class="fc">
<option value="csv">CSV</option>
<option value="pdf">PDF</option>
</select>

</div>


<div class="fg">
<label class="fl">From</label>

<input type="date"
name="date_from"
class="fc"
value="{{ now()->startOfMonth()->format('Y-m-d') }}">

</div>


<div class="fg">
<label class="fl">To</label>

<input type="date"
name="date_to"
class="fc"
value="{{ now()->format('Y-m-d') }}">

</div>


<div class="fg">
<label class="fl">&nbsp;</label>

<button class="btn btn-p">
<i class="bi bi-download"></i>
Export
</button>

</div>


</form>

</div>

</div>

@endsection