@extends('admin.layouts.app')
@section('title', 'Compuscan (CCI) Data Submission')
@section('page-title', 'Compuscan (CCI)')

@section('content')

{{-- Info Alert --}}
<div class="alert a-i">
    <i class="bi bi-info-circle-fill" style="margin-top:2px;"></i>
    <div>
        <strong style="display:block;margin-bottom:2px">Data Submission Layout V3.00</strong>
        <span style="opacity:0.9">Generate fixed-length (719-character) ASCII files for Compuscan Data Submission. You can generate daily files (for new loans and closures) or monthly snapshot files.</span>
    </div>
</div>

<div class="card mb6">
    <div class="card-hdr">
        <span class="card-title">Generate CCI File</span>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.compuscan.generate') }}" method="POST">
            @csrf
            
            <div class="g2" style="max-width:600px; margin-bottom:18px;">
                <div class="fg">
                    <label class="fl">Submission Type</label>
                    <select name="submission_type" class="fc" required>
                        <option value="daily" selected>Daily File (Registrations & Closures)</option>
                        <option value="monthly">Monthly Snapshot (Full Book)</option>
                    </select>
                </div>
                
                <div class="fg">
                    <label class="fl">Target Date</label>
                    <input type="date" name="target_date" class="fc" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            
            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-p">
                    <i class="bi bi-cloud-download"></i> Generate & Download File
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-hdr">
        <span class="card-title">Upload to Experian</span>
    </div>
    <div class="card-body" style="text-align: center; padding: 40px 20px;">
        <div style="width: 64px; height: 64px; background: rgba(22,163,74,0.1); color: var(--ok); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px;">
            <i class="bi bi-cloud-upload-fill"></i>
        </div>
        <h5 style="margin-bottom: 8px; font-weight: 700;">Experian/Compuscan Portal</h5>
        <p class="muted" style="margin-bottom: 24px; max-width: 400px; margin-left: auto; margin-right: auto;">
            After downloading the generated file, upload it securely to the Experian data submission portal.
        </p>
        
        <a href="https://expcnntmv.experian.co.za/human.aspx?r=1720027234&arg12=ahtselfprovision&orgid=3898" target="_blank" class="btn btn-ok" style="padding: 10px 20px; font-size: 14px;">
            <i class="bi bi-box-arrow-up-right"></i> Open File Upload Portal
        </a>
    </div>
</div>

@endsection
