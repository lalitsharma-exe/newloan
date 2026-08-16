@extends('admin.layouts.app')
@section('title', 'Collection Sheet')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Home</a> / <a href="{{ route('admin.reports.index') }}">Reports</a> / Collection Sheet
@endsection
@section('content')
<div class="flex jb aic mb6">
    <div>
        <h1 style="font-size:20px;font-weight:800;color:var(--pd)">Collection Sheet</h1>
        <div class="muted" style="font-size:13px;margin-top:2px">Monthly debit list for bank collections.</div>
    </div>
    <a href="{{ route('admin.reports.collection-sheet.export', $filters) }}" class="btn btn-p">
        <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
    </a>
</div>

<!-- Premium Collection Sheet Dashboard -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:16px;margin-bottom:24px">
    <!-- Total Collection Card -->
    <div class="card" style="background:linear-gradient(135deg, #22894e, #1a6b3c);color:#fff;border:none;box-shadow:0 10px 20px rgba(26,107,60,0.15)">
        <div style="padding:18px">
            <div style="font-size:11px;opacity:0.8;font-weight:700;text-transform:uppercase;letter-spacing:0.05em">Total Collection</div>
            <div style="font-size:22px;font-weight:800;margin-top:8px">M{{ number_format($data['totalCollection'], 2) }}</div>
            <div style="font-size:11px;opacity:0.7;margin-top:6px"><i class="bi bi-calendar-check"></i> Month: {{ $data['month'] }}</div>
        </div>
    </div>
    
    <!-- Government Loan Card -->
    <div class="card" style="border-left:4px solid #22894e;box-shadow:0 4px 12px rgba(0,0,0,0.03);transition:transform 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
        <div style="padding:18px">
            <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.05em">Government</div>
            <div style="font-size:18px;font-weight:800;color:#22894e;margin-top:8px">M{{ number_format($data['govCollection'], 2) }}</div>
            <div style="font-size:11px;color:var(--muted);margin-top:6px"><i class="bi bi-bank"></i> Public Sector</div>
        </div>
    </div>

    <!-- Private Sector Card -->
    <div class="card" style="border-left:4px solid #2eaa62;box-shadow:0 4px 12px rgba(0,0,0,0.03);transition:transform 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
        <div style="padding:18px">
            <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.05em">Private</div>
            <div style="font-size:18px;font-weight:800;color:#2eaa62;margin-top:8px">M{{ number_format($data['privateCollection'], 2) }}</div>
            <div style="font-size:11px;color:var(--muted);margin-top:6px"><i class="bi bi-building"></i> Private Sector</div>
        </div>
    </div>

    <!-- Pensioner Card -->
    <div class="card" style="border-left:4px solid #f59e0b;box-shadow:0 4px 12px rgba(0,0,0,0.03);transition:transform 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
        <div style="padding:18px">
            <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.05em">Pensioner</div>
            <div style="font-size:18px;font-weight:800;color:#f59e0b;margin-top:8px">M{{ number_format($data['pensionerCollection'], 2) }}</div>
            <div style="font-size:11px;color:var(--muted);margin-top:6px"><i class="bi bi-person-heart"></i> Retired / Elders</div>
        </div>
    </div>

    <!-- Student Card -->
    <div class="card" style="border-left:4px solid #06b6d4;box-shadow:0 4px 12px rgba(0,0,0,0.03);transition:transform 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
        <div style="padding:18px">
            <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.05em">Students</div>
            <div style="font-size:18px;font-weight:800;color:#06b6d4;margin-top:8px">M{{ number_format($data['studentsCollection'], 2) }}</div>
            <div style="font-size:11px;color:var(--muted);margin-top:6px"><i class="bi bi-mortarboard-fill"></i> Higher Ed</div>
        </div>
    </div>

    <!-- SMEs Card -->
    <div class="card" style="border-left:4px solid #10b981;box-shadow:0 4px 12px rgba(0,0,0,0.03);transition:transform 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
        <div style="padding:18px">
            <div style="font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:0.05em">SMEs</div>
            <div style="font-size:18px;font-weight:800;color:#10b981;margin-top:8px">M{{ number_format($data['smesCollection'], 2) }}</div>
            <div style="font-size:11px;color:var(--muted);margin-top:6px"><i class="bi bi-shop"></i> Micro Enterprises</div>
        </div>
    </div>
</div>

<div class="card mb4">
    <div class="card-body">
        <form action="{{ route('admin.reports.collection-sheet') }}" method="GET" class="flex aic" style="gap:14px;flex-wrap:wrap">
            <div class="fg" style="margin-bottom:0;min-width:180px">
                <label class="fl">Collection Month</label>
                <input type="month" name="month" class="fc" value="{{ $data['month'] }}">
            </div>
            
            <div class="fg" style="margin-bottom:0;min-width:200px">
                <label class="fl">Employment Category</label>
                <select name="category" class="fc">
                    <option value="">— All Categories —</option>
                    @foreach(['Defence','NSS','Police','LCS','Pensioner','Civil servants','Teacher','Private sector','SMEs'] as $c)
                    <option value="{{ $c }}" {{ ($filters['category'] ?? '') == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="fg" style="margin-bottom:0;min-width:180px">
                <label class="fl">District</label>
                <select name="district" class="fc">
                    <option value="">— All Districts —</option>
                    @foreach(['Maseru','Leribe','Berea','Mafeteng','Mohale\'s Hoek','Quthing','Qacha\'s Nek','Mokhotlong','Thaba-Tseka','Butha-Buthe'] as $d)
                    <option value="{{ $d }}" {{ ($filters['district'] ?? '') == $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex gap2 aic" style="margin-top:auto">
                <button type="submit" class="btn btn-p">Filter</button>
                <a href="{{ route('admin.reports.collection-sheet') }}" class="btn btn-o">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0">
        <table class="dt">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>District</th>
                    <th>Bank</th>
                    <th>Account Number</th>
                    <th>Branch Code</th>
                    <th style="text-align:right">Amount (M)</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['installments'] as $i)
                @php $loan = $i->loan; @endphp
                <tr>
                    <td style="font-weight:700;color:var(--p)">{{ $loan->user?->name ?? 'Unknown' }}</td>
                    <td style="font-weight:600;color:var(--muted)">{{ $loan->application?->district ?? '—' }}</td>
                    <td>{{ $loan->application?->bankDetails?->bank_name ?? '—' }}</td>
                    <td><code>{{ $loan->application?->bankDetails?->account_number ?? '—' }}</code></td>
                    <td><code>{{ str_pad($loan->application?->bankDetails?->branch_code ?? '', 6, '0', STR_PAD_LEFT) }}</code></td>
                    <td style="text-align:right;font-weight:800;color:var(--p)">{{ number_format($i->total_amount, 2) }}</td>
                    <td class="muted">{{ $i->due_date->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No installments found for the selected criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
