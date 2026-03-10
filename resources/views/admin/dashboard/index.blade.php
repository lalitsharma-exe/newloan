@extends('admin.layouts.app')
@section('title','Dashboard')@section('page-title','Dashboard')
@section('content')
<div class="g4 mb6">
  <div class="sc"><div class="si ok"><i class="bi bi-check-circle-fill"></i></div><div><div class="sv">{{ $stats['loans_approved_today'] }}</div><div class="sl">Loans Approved Today</div></div></div>
  <div class="sc"><div class="si p"><i class="bi bi-cash-stack"></i></div><div><div class="sv">L {{ number_format($stats['payments_received_today'],0) }}</div><div class="sl">Payments Today</div></div></div>
  <div class="sc"><div class="si w"><i class="bi bi-hourglass-split"></i></div><div><div class="sv">{{ $stats['applications_pending'] }}</div><div class="sl">Pending Applications</div></div></div>
  <div class="sc"><div class="si e"><i class="bi bi-exclamation-triangle-fill"></i></div><div><div class="sv">{{ $stats['overdue_loans'] }}</div><div class="sl">Overdue Loans</div></div></div>
</div>
<div class="g4 mb6">
  <div class="sc"><div class="si i"><i class="bi bi-calendar-check"></i></div><div><div class="sv">L {{ number_format($stats['expected_collections'],0) }}</div><div class="sl">Expected This Month</div></div></div>
  <div class="sc"><div class="si s"><i class="bi bi-pie-chart-fill"></i></div><div><div class="sv">L {{ number_format($stats['total_portfolio'],0) }}</div><div class="sl">Active Portfolio</div></div></div>
  <div class="sc"><div class="si p"><i class="bi bi-arrow-up-circle-fill"></i></div><div><div class="sv">L {{ number_format($stats['total_disbursed_month'],0) }}</div><div class="sl">Disbursed This Month</div></div></div>
  <div class="sc"><div class="si ok"><i class="bi bi-people-fill"></i></div><div><div class="sv">{{ $stats['total_borrowers'] }}</div><div class="sl">Total Borrowers</div></div></div>
</div>
<div style="display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:18px">
  <div class="card">
    <div class="card-hdr"><span class="card-title">Portfolio Overview (6 Months)</span><a href="{{ route('admin.reports.portfolio') }}" class="btn btn-sm btn-o">View Report</a></div>
    <div class="card-body"><canvas id="chart" height="110"></canvas></div>
  </div>
  <div class="card">
    <div class="card-hdr"><span class="card-title">Overdue Loans</span><a href="{{ route('admin.loans.index',['overdue'=>1]) }}" class="btn btn-sm btn-o">All</a></div>
    @forelse($overdueLoans as $l)
    <div style="padding:12px 18px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:10px">
      <div class="av av-sm">{{ strtoupper(substr($l->user->name??'U',0,1)) }}</div>
      <div style="flex:1;min-width:0"><div style="font-size:12.5px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $l->user->name }}</div><div class="muted">{{ $l->loan_number }}</div></div>
      <div style="text-align:right"><div style="font-size:12.5px;font-weight:700;color:#ef4444">L{{ number_format($l->outstanding_balance,0) }}</div><div class="muted">{{ $l->days_overdue }}d</div></div>
    </div>
    @empty<div class="empty"><i class="bi bi-check-circle-fill" style="color:#10b981"></i><p>No overdue loans!</p></div>@endforelse
  </div>
</div>
<div class="g2">
  <div class="card">
    <div class="card-hdr"><span class="card-title">Recent Applications</span><a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-o">All</a></div>
    <div style="overflow-x:auto"><table class="dt">
      <thead><tr><th>Applicant</th><th>Product</th><th>Amount</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @forelse($recentApplications as $a)
        <tr>
          <td><div class="flex aic gap2"><div class="av av-sm">{{ strtoupper(substr($a->user->name??'U',0,1)) }}</div><div><div style="font-size:12.5px;font-weight:600">{{ $a->user->name }}</div><div class="muted">{{ $a->application_number }}</div></div></div></td>
          <td style="font-size:12.5px">{{ $a->loanProduct->name??'—' }}</td>
          <td><strong>L{{ number_format($a->requested_amount??0,0) }}</strong></td>
          <td><span class="badge b{{ $a->status_badge }}">{{ ucfirst(str_replace('_',' ',$a->status)) }}</span></td>
          <td><a href="{{ route('admin.applications.show',$a) }}" class="btn btn-xs btn-o">View</a></td>
        </tr>
        @empty<tr><td colspan="5"><div class="empty"><i class="bi bi-inbox"></i><p>No applications</p></div></td></tr>@endforelse
      </tbody>
    </table></div>
  </div>
  <div class="card">
    <div class="card-hdr"><span class="card-title">Recent Payments</span><a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-o">All</a></div>
    <div style="overflow-x:auto"><table class="dt">
      <thead><tr><th>Reference</th><th>Borrower</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($recentPayments as $p)
        <tr>
          <td><span style="font-size:11.5px;font-weight:700;color:#4f46e5">{{ $p->payment_reference }}</span></td>
          <td>{{ $p->loan->user->name??'—' }}</td>
          <td><strong>L{{ number_format($p->amount,0) }}</strong></td>
          <td><span class="badge b{{ $p->status_badge }}">{{ ucfirst($p->status) }}</span></td>
        </tr>
        @empty<tr><td colspan="4"><div class="empty"><i class="bi bi-credit-card"></i><p>No payments</p></div></td></tr>@endforelse
      </tbody>
    </table></div>
  </div>
</div>
@endsection
@push('scripts')
<script>
const d=@json($monthlyChart);
new Chart(document.getElementById('chart').getContext('2d'),{
  type:'bar',data:{labels:d.map(x=>x.month),datasets:[{label:'Disbursed (L)',data:d.map(x=>x.disbursed),backgroundColor:'rgba(79,70,229,.75)',borderRadius:5},{label:'Collected (L)',data:d.map(x=>x.collected),backgroundColor:'rgba(16,185,129,.75)',borderRadius:5}]},
  options:{responsive:true,plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true,grid:{color:'#f1f5f9'}},x:{grid:{display:false}}}}
});
</script>
@endpush
