@extends('admin.layouts.app')
@section('title', 'Decline Report')
@section('page-title', 'Decline Trend Analysis')
@section('bc', 'Decline Tracker / Report')

@section('content')
<div class="tabs">
    <a href="{{ route('admin.declines.index') }}" class="tab">Decline Log</a>
    <a href="{{ route('admin.declines.report') }}" class="tab active">Summary Report</a>
</div>

<div class="filter-bar">
    <form method="GET" action="{{ route('admin.declines.report') }}" class="flex aic gap3" style="width:100%">
        <div class="fg">
            <label class="fl">Date Range</label>
            <div class="flex aic gap2">
                <input type="date" name="from" class="fc" value="{{ $from }}">
                <span class="muted">to</span>
                <input type="date" name="to" class="fc" value="{{ $to }}">
            </div>
        </div>
        <div style="margin-bottom:0; align-self:flex-end">
            <button type="submit" class="btn btn-p"><i class="bi bi-graph-up"></i> Generate Report</button>
            <button type="button" onclick="exportReport()" class="btn btn-o"><i class="bi bi-download"></i> Export CSV</button>
        </div>
    </form>
</div>

<div class="g4 mb6">
    <div class="sc">
        <div class="si e"><i class="bi bi-x-circle-fill"></i></div>
        <div>
            <div class="sv">{{ number_format($totalDeclines) }}</div>
            <div class="sl">Total Declines</div>
        </div>
    </div>
    <div class="sc">
        <div class="si p"><i class="bi bi-currency-dollar"></i></div>
        <div>
            <div class="sv">M{{ number_format($totalValue, 0) }}</div>
            <div class="sl">Total Value Declined</div>
        </div>
    </div>
    <div class="sc">
        <div class="si w"><i class="bi bi-flag-fill"></i></div>
        <div>
            <div class="sv" style="font-size:18px">{{ $topCategory ? $topCategory->category->name : 'N/A' }}</div>
            <div class="sl">Top Decline Category</div>
        </div>
    </div>
    <div class="sc">
        <div class="si i"><i class="bi bi-layers-fill"></i></div>
        <div>
            <div class="sv">{{ $categoriesUsed }} / 8</div>
            <div class="sl">Categories Triggered</div>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 400px; gap:24px; align-items:start">
    <div class="card">
        <div class="card-hdr">
            <span class="card-title">Category Breakdown</span>
        </div>
        <div style="overflow-x:auto">
            <table class="dt">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="tr">Count</th>
                        <th class="tr">Percentage</th>
                        <th class="tr">Total Value (LSL)</th>
                        <th style="width:200px">Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $colors = [
                            1 => '#D85A30', 2 => '#E24B4A', 3 => '#EF9F27', 4 => '#378ADD',
                            5 => '#7F77DD', 6 => '#1D9E75', 7 => '#639922', 8 => '#D4537E'
                        ];
                    @endphp
                    @forelse($breakdown as $item)
                    <tr>
                        <td style="font-weight:700; color:var(--p)">{{ $item->category->name }}</td>
                        <td class="tr" style="font-weight:600">{{ $item->count }}</td>
                        <td class="tr">{{ $item->percentage }}%</td>
                        <td class="tr" style="font-weight:700">M{{ number_format($item->total_value, 2) }}</td>
                        <td>
                            <div style="width:100%; background:#f1f5f9; height:8px; border-radius:4px; overflow:hidden">
                                <div style="width:{{ $item->percentage }}%; height:100%; background:{{ $colors[$item->category_id] ?? 'var(--p)' }}"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="tc muted">No data for this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-hdr">
            <span class="card-title">Visual Distribution</span>
        </div>
        <div class="card-body">
            <canvas id="declineChart" height="300"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('declineChart').getContext('2d');
    const data = {
        labels: {!! json_encode($breakdown->map(fn($i) => $i->category->name)) !!},
        datasets: [{
            data: {!! json_encode($breakdown->map(fn($i) => $i->count)) !!},
            backgroundColor: {!! json_encode($breakdown->map(fn($i) => $colors[$i->category_id] ?? '#cbd5e1')) !!},
            borderWidth: 0
        }]
    };
    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            },
            cutout: '70%'
        }
    });
});

function exportReport() {
    // Logic to export breakdown to CSV
    let csv = "Category,Count,Percentage,Total Value (LSL)\n";
    @foreach($breakdown as $item)
    csv += "{{ $item->category->name }},{{ $item->count }},{{ $item->percentage }}%,{{ $item->total_value }}\n";
    @endforeach
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'decline_report_{{ $from }}_to_{{ $to }}.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>
@endsection
