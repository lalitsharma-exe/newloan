@extends('admin.layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('bc','Home')
@section('content')

{{-- Flash --}}
@if(session('success'))
<div style="background:rgba(16,185,129,.08);color:#065f46;border:1px solid rgba(16,185,129,.2);padding:12px 16px;border-radius:11px;font-size:13px;display:flex;align-items:center;gap:9px;margin-bottom:22px">
  <i class="bi bi-check-circle-fill"></i>{{ session('success') }}
</div>
@endif

{{-- Top greeting bar --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:26px">
  <div>
    <div style="font-size:22px;font-weight:800;color:var(--dark)">
      Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
      {{ explode(' ', auth('admin')->user()->name)[0] }} 👋
    </div>
    <div style="font-size:13px;color:var(--muted);margin-top:3px">{{ now()->format('l, d F Y') }} · Here's what's happening today</div>
  </div>
  <a href="{{ route('admin.applications.index') }}" class="btn btn-p">
    <i class="bi bi-plus-lg"></i> New Application
  </a>
</div>

{{-- 4 primary KPI cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px">
  @php
  $kpis = [
    ['Approved Today',      $stats['loans_approved_today'],              'check-circle-fill',    '#10b981','rgba(16,185,129,.1)',  route('admin.loans.index')],
    ['Payments Today',      'M '.number_format($stats['payments_received_today'],0), 'cash-stack', '#4f46e5','rgba(79,70,229,.1)',  route('admin.payments.index')],
    ['Pending Applications',$stats['applications_pending'],              'hourglass-split',      '#f59e0b','rgba(245,158,11,.1)',  route('admin.applications.index')],
    ['Overdue Loans',       $stats['overdue_loans'],                     'exclamation-triangle-fill','#ef4444','rgba(239,68,68,.1)',route('admin.loans.index',['status'=>'overdue'])],
  ];
  @endphp
  @foreach($kpis as [$label,$value,$icon,$color,$bg,$link])
  <a href="{{ $link }}" style="background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px 22px;display:flex;align-items:flex-start;justify-content:space-between;text-decoration:none;transition:all .2s;group" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,.08)';this.style.borderColor='{{ $color }}44'" onmouseout="this.style.boxShadow='none';this.style.borderColor='var(--border)'">
    <div>
      <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">{{ $label }}</div>
      <div style="font-size:28px;font-weight:800;color:var(--dark);line-height:1">{{ $value }}</div>
    </div>
    <div style="width:48px;height:48px;border-radius:14px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;font-size:21px;color:{{ $color }};flex-shrink:0">
      <i class="bi bi-{{ $icon }}"></i>
    </div>
  </a>
  @endforeach
</div>

{{-- 4 secondary KPI cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
  @php
  $kpis2 = [
    ['Expected This Month', 'M '.number_format($stats['expected_collections'],0),  'calendar-check',      '#06b6d4'],
    ['Active Portfolio',    'M '.number_format($stats['total_portfolio'],0),        'pie-chart-fill',      '#0ea5e9'],
    ['Disbursed This Month','M '.number_format($stats['total_disbursed_month'],0),  'arrow-up-circle-fill','#8b5cf6'],
    ['Total Borrowers',     number_format($stats['total_borrowers']),               'people-fill',         '#10b981'],
  ];
  @endphp
  @foreach($kpis2 as [$label,$value,$icon,$color])
  <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px">
    <div style="width:42px;height:42px;border-radius:12px;background:{{ $color }}18;display:flex;align-items:center;justify-content:center;font-size:18px;color:{{ $color }};flex-shrink:0">
      <i class="bi bi-{{ $icon }}"></i>
    </div>
    <div>
      <div style="font-size:18px;font-weight:800;color:var(--dark)">{{ $value }}</div>
      <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ $label }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- Chart + Overdue side by side --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:20px">

  {{-- Chart --}}
  <div class="card">
    <div class="card-hdr">
      <span class="card-title">Portfolio Overview — Last 6 Months</span>
      <div style="display:flex;gap:12px;align-items:center">
        <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted)"><span style="width:10px;height:10px;background:#4f46e5;border-radius:3px;display:inline-block"></span>Disbursed</div>
        <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted)"><span style="width:10px;height:10px;background:#10b981;border-radius:3px;display:inline-block"></span>Collected</div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-o">Reports</a>
      </div>
    </div>
    <div class="card-body">
      <canvas id="dashChart" height="120"></canvas>
    </div>
  </div>

  {{-- Overdue loans --}}
  <div class="card">
    <div class="card-hdr">
      <span class="card-title" style="display:flex;align-items:center;gap:7px">
        <span style="width:8px;height:8px;background:#ef4444;border-radius:50%;display:inline-block;animation:pulse 2s infinite"></span>
        Overdue Loans
      </span>
      <a href="{{ route('admin.loans.index',['status'=>'overdue']) }}" class="btn btn-sm btn-o">All</a>
    </div>
    @forelse($overdueLoans as $loan)
    <a href="{{ route('admin.loans.show',$loan) }}" style="padding:13px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:11px;text-decoration:none;transition:background .15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
      <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#dc2626);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0">
        {{ strtoupper(substr($loan->user->name ?? 'U',0,1)) }}
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600;color:var(--dark);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $loan->user->name ?? '—' }}</div>
        <div style="font-size:11.5px;color:var(--muted)">{{ $loan->loan_number }}</div>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <div style="font-size:13px;font-weight:700;color:#ef4444">M{{ number_format($loan->outstanding_balance,0) }}</div>
        <div style="font-size:11px;color:var(--muted)">{{ $loan->days_overdue }}d overdue</div>
      </div>
    </a>
    @empty
    <div style="text-align:center;padding:40px 20px;color:var(--muted)">
      <i class="bi bi-check-circle-fill" style="font-size:36px;color:#10b981;display:block;margin-bottom:10px"></i>
      <div style="font-weight:600;font-size:13px">All loans are current!</div>
    </div>
    @endforelse
  </div>

</div>

{{-- Recent Applications + Recent Payments --}}
<div style="display:grid;grid-template-columns:3fr 2fr;gap:18px">

  {{-- Recent Applications --}}
  <div class="card">
    <div class="card-hdr">
      <span class="card-title">Recent Applications</span>
      <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-o">View All</a>
    </div>
    <div style="overflow-x:auto">
      <table class="dt">
        <thead>
          <tr>
            <th>Applicant</th>
            <th>Product</th>
            <th>Amount</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentApplications as $app)
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0">
                  {{ strtoupper(substr($app->applicant_name,0,1)) }}
                </div>
                <div>
                  <div style="font-size:13px;font-weight:600">{{ $app->applicant_name }}</div>
                  <div style="font-size:11.5px;color:var(--muted)">{{ $app->application_number }}</div>
                </div>
              </div>
            </td>
            <td style="font-size:12.5px;color:var(--muted)">{{ $app->loanProduct->name ?? '—' }}</td>
            <td style="font-weight:700;font-size:13px">M{{ number_format($app->requested_amount ?? 0, 0) }}</td>
            <td>
              <span class="badge b{{ $app->status_badge }}">{{ ucfirst(str_replace('_',' ',$app->status)) }}</span>
            </td>
            <td>
              <a href="{{ route('admin.applications.show',$app) }}" class="btn btn-xs btn-o">View</a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5">
              <div style="text-align:center;padding:36px;color:var(--muted)">
                <i class="bi bi-inbox" style="font-size:36px;opacity:.3;display:block;margin-bottom:8px"></i>
                <div>No applications yet</div>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Recent Payments --}}
  <div class="card">
    <div class="card-hdr">
      <span class="card-title">Recent Payments</span>
      <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-o">View All</a>
    </div>
    @forelse($recentPayments as $pay)
    <div style="padding:13px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:11px">
      <div style="width:36px;height:36px;border-radius:10px;background:rgba(16,185,129,.1);display:flex;align-items:center;justify-content:center;font-size:16px;color:#10b981;flex-shrink:0">
        <i class="bi bi-cash"></i>
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-size:12px;font-weight:700;color:var(--p);font-family:monospace">{{ $pay->payment_reference }}</div>
        <div style="font-size:11.5px;color:var(--muted);margin-top:1px">{{ $pay->loan->user->name ?? '—' }}</div>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <div style="font-size:13px;font-weight:700">M{{ number_format($pay->amount,0) }}</div>
        <span class="badge b{{ $pay->status_badge }}" style="font-size:10.5px;padding:2px 7px">{{ ucfirst($pay->status) }}</span>
      </div>
    </div>
    @empty
    <div style="text-align:center;padding:40px 20px;color:var(--muted)">
      <i class="bi bi-credit-card" style="font-size:36px;opacity:.3;display:block;margin-bottom:8px"></i>
      <div style="font-size:13px">No payments yet</div>
    </div>
    @endforelse
  </div>

</div>

<style>
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: .4; }
}
</style>

@push('scripts')
<script>
(function(){
  const d = @json($monthlyChart);
  new Chart(document.getElementById('dashChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: d.map(x => x.month),
      datasets: [
        {
          label: 'Disbursed (L)',
          data: d.map(x => x.disbursed),
          backgroundColor: 'rgba(79,70,229,.8)',
          borderRadius: 6,
          borderSkipped: false,
        },
        {
          label: 'Collected (L)',
          data: d.map(x => x.collected),
          backgroundColor: 'rgba(16,185,129,.8)',
          borderRadius: 6,
          borderSkipped: false,
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ' L ' + Number(ctx.raw).toLocaleString()
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: '#f1f5f9' },
          ticks: {
            callback: v => 'L' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v),
            font: { size: 11 }
          }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 } }
        }
      }
    }
  });
})();
</script>
@endpush
@endsection