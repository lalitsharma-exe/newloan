@extends('admin.layouts.app')
@section('title','Repayment Charts')
@section('page-title','Repayment Charts & Trends')
@section('bc','<a href="'.route('admin.loans.index').'">Loans</a> / Repayment Charts')
@section('content')

@php
  $months    = $data['months'];
  $byMethod  = $data['by_method'];
  $labels    = array_column($months, 'label');
  $collected = array_column($months, 'collected');
  $disbursed = array_column($months, 'disbursed');
  $totalCollected = array_sum($collected);
  $totalDisbursed = array_sum($disbursed);
  $methodLabels = array_keys($byMethod);
  $methodTotals = array_values($byMethod);
  $methodColors = ['cash'=>'#4f46e5','bank_transfer'=>'#0891b2','mobile_money'=>'#059669','card'=>'#f59e0b','payroll'=>'#7c3aed','cheque'=>'#dc2626','other'=>'#94a3b8'];
@endphp

{{-- Summary stat cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
  @foreach([
    ['Total Collected (12mo)', 'M '.number_format($totalCollected,0), 'cash-stack', '#059669', 'rgba(5,150,105,.08)'],
    ['Total Disbursed (12mo)', 'M '.number_format($totalDisbursed,0), 'bank', '#4f46e5', 'rgba(79,70,229,.08)'],
    ['Collection Rate', $totalDisbursed>0 ? round(($totalCollected/$totalDisbursed)*100,1).'%' : '—', 'graph-up-arrow', '#0891b2', 'rgba(8,145,178,.08)'],
    ['This Month', 'M '.number_format(end($collected),0), 'calendar-check', '#f59e0b', 'rgba(245,158,11,.08)'],
  ] as [$lbl,$val,$icon,$color,$bg])
  <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px">
    <div style="width:46px;height:46px;border-radius:12px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;color:{{ $color }};font-size:20px;flex-shrink:0">
      <i class="bi bi-{{ $icon }}"></i>
    </div>
    <div>
      <div style="font-size:22px;font-weight:800;color:var(--dark);line-height:1">{{ $val }}</div>
      <div style="font-size:11.5px;color:var(--muted);margin-top:2px">{{ $lbl }}</div>
    </div>
  </div>
  @endforeach
</div>

{{-- Monthly collections vs disbursements chart --}}
<div class="card" style="margin-bottom:20px">
  <div class="card-hdr">
    <span class="card-title">Monthly Collections vs Disbursements (Last 12 Months)</span>
  </div>
  <div style="padding:20px">
    {{-- Legend --}}
    <div style="display:flex;gap:20px;margin-bottom:16px;font-size:13px">
      <span style="display:flex;align-items:center;gap:6px">
        <span style="width:12px;height:12px;border-radius:3px;background:#059669;display:inline-block"></span> Collections
      </span>
      <span style="display:flex;align-items:center;gap:6px">
        <span style="width:12px;height:12px;border-radius:3px;background:#c7d2fe;display:inline-block"></span> Disbursements
      </span>
    </div>
    <div style="position:relative;height:320px">
      <canvas id="monthlyChart"></canvas>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

  {{-- Collections trend line --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title">Collections Trend</span></div>
    <div style="padding:20px">
      <div style="position:relative;height:220px">
        <canvas id="trendChart"></canvas>
      </div>
    </div>
  </div>

  {{-- By method donut --}}
  <div class="card">
    <div class="card-hdr"><span class="card-title">Collections by Method</span></div>
    <div style="padding:20px">
      @if(!empty($byMethod))
      <div style="display:flex;gap:20px;align-items:center">
        <div style="position:relative;height:180px;width:180px;flex-shrink:0">
          <canvas id="methodChart"></canvas>
        </div>
        <div style="flex:1">
          @foreach($byMethod as $method => $total)
          @php $color = $methodColors[$method] ?? '#94a3b8'; @endphp
          <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:0.5px solid var(--border);font-size:13px">
            <span style="display:flex;align-items:center;gap:7px">
              <span style="width:10px;height:10px;border-radius:2px;background:{{ $color }};display:inline-block"></span>
              {{ ucwords(str_replace('_',' ',$method)) }}
            </span>
            <strong>M{{ number_format($total,0) }}</strong>
          </div>
          @endforeach
        </div>
      </div>
      @else
      <div style="text-align:center;padding:40px;color:var(--muted)">No payment data yet</div>
      @endif
    </div>
  </div>

</div>

{{-- Monthly breakdown table --}}
<div class="card">
  <div class="card-hdr"><span class="card-title">Monthly Breakdown</span></div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead>
        <tr>
          <th>Month</th>
          <th>Collected</th>
          <th>Disbursed</th>
          <th>Net</th>
          <th>Collection Bar</th>
        </tr>
      </thead>
      <tbody>
        @php $maxCollected = max(array_merge([1], $collected)); @endphp
        @foreach($months as $m)
        @php $net = $m['collected'] - $m['disbursed']; @endphp
        <tr>
          <td style="font-weight:600;font-size:13px">{{ $m['label'] }}</td>
          <td style="color:#059669;font-weight:600">M{{ number_format($m['collected'],0) }}</td>
          <td style="color:#4f46e5">M{{ number_format($m['disbursed'],0) }}</td>
          <td style="font-weight:600;color:{{ $net>=0?'#059669':'#ef4444' }}">
            {{ $net>=0?'+':'' }}M{{ number_format($net,0) }}
          </td>
          <td style="min-width:160px">
            @if($m['collected']>0)
            <div style="background:#e0e7ff;border-radius:4px;height:10px;position:relative">
              <div style="background:#059669;border-radius:4px;height:10px;width:{{ min(100,round($m['collected']/$maxCollected*100)) }}%"></div>
            </div>
            @else
            <span style="color:var(--muted);font-size:12px">—</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="background:#f8fafc;font-weight:700">
          <td style="padding:10px 12px">12-Month Total</td>
          <td style="color:#059669;padding:10px 12px">M{{ number_format($totalCollected,0) }}</td>
          <td style="color:#4f46e5;padding:10px 12px">M{{ number_format($totalDisbursed,0) }}</td>
          <td style="padding:10px 12px;color:{{ $totalCollected-$totalDisbursed>=0?'#059669':'#ef4444' }}">
            {{ ($totalCollected-$totalDisbursed)>=0?'+':'' }}M{{ number_format($totalCollected-$totalDisbursed,0) }}
          </td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
const labels    = @json($labels);
const collected = @json($collected);
const disbursed = @json($disbursed);

// Monthly grouped bar chart
new Chart(document.getElementById('monthlyChart'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      { label: 'Collections', data: collected, backgroundColor: '#059669', borderRadius: 4, borderSkipped: false },
      { label: 'Disbursements', data: disbursed, backgroundColor: '#c7d2fe', borderRadius: 4, borderSkipped: false },
    ]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 45 } },
      y: { grid: { color: '#f1f5f9' }, ticks: { callback: v => 'M'+Math.round(v).toLocaleString(), font: { size: 11 } } }
    }
  }
});

// Trend line
new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels,
    datasets: [{
      label: 'Collections', data: collected,
      borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.08)',
      borderWidth: 2.5, tension: 0.4, fill: true, pointRadius: 4, pointBackgroundColor: '#059669'
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
      y: { grid: { color: '#f1f5f9' }, ticks: { callback: v => 'M'+Math.round(v).toLocaleString(), font: { size: 10 } } }
    }
  }
});

// Method donut
@if(!empty($byMethod))
const methodLabels = @json($methodLabels);
const methodData   = @json($methodTotals);
const methodColors = @json(array_values(array_intersect_key($methodColors, $byMethod)));
new Chart(document.getElementById('methodChart'), {
  type: 'doughnut',
  data: {
    labels: methodLabels.map(l => l.replace('_',' ').replace(/\b\w/g, c => c.toUpperCase())),
    datasets: [{ data: methodData, backgroundColor: methodColors, borderWidth: 2, borderColor: '#fff' }]
  },
  options: {
    responsive: true, maintainAspectRatio: false, cutout: '65%',
    plugins: { legend: { display: false } }
  }
});
@endif
</script>
@endsection
