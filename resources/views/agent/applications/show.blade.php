@extends('agent.layouts.app')
@section('page-title', 'Application Details')
@section('content')

<div style="margin-bottom:16px">
  <a href="{{ route('agent.applications.index') }}" style="font-size:13px;color:#0f766e;text-decoration:none"><i class="bi bi-arrow-left"></i> Back to Applications</a>
</div>

<div class="card" style="margin-bottom:22px">
  <div class="card-hdr">
    <div class="card-title">{{ $application->application_number }}</div>
    @php
      $bc = match($application->status) {
        'approved','disbursed' => 'bok',
        'declined' => 'be',
        'under_review' => 'bp',
        'info_requested' => 'bw',
        default => 'bs',
      };
    @endphp
    <span class="badge {{ $bc }}">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
  </div>
  <div class="card-body">
    <div class="g3" style="gap:20px">
      <div>
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Client Name</div>
        <div style="font-size:14px;font-weight:500">{{ $application->applicant_name }}</div>
      </div>
      <div>
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">National ID</div>
        <div style="font-size:14px;font-weight:500">{{ $application->national_id }}</div>
      </div>
      <div>
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Phone</div>
        <div style="font-size:14px;font-weight:500">{{ $application->cell_number }}</div>
      </div>
      <div>
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Loan Amount</div>
        <div style="font-size:14px;font-weight:500">M{{ number_format($application->requested_amount, 2) }}</div>
      </div>
      <div>
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Term</div>
        <div style="font-size:14px;font-weight:500">{{ $application->requested_term }} months</div>
      </div>
      <div>
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Submitted</div>
        <div style="font-size:14px;font-weight:500">{{ $application->submitted_at?->format('d M Y H:i') ?? '-' }}</div>
      </div>
    </div>

    @if($application->employment)
    <hr style="margin:20px 0;border:none;border-top:1px solid #e2e8f0">
    <div style="font-size:13px;font-weight:700;margin-bottom:12px">Employment Details</div>
    <div class="g3" style="gap:20px">
      <div>
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Employer</div>
        <div style="font-size:14px;font-weight:500">{{ $application->employment->employer_name }}</div>
      </div>
      <div>
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Employee #</div>
        <div style="font-size:14px;font-weight:500">{{ $application->employment->employee_number }}</div>
      </div>
      <div>
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px">Job Title</div>
        <div style="font-size:14px;font-weight:500">{{ $application->employment->job_title }}</div>
      </div>
    </div>
    @endif
  </div>
</div>

{{-- 5-Step Automated Verification Pipeline Card --}}
@php
  $vStatus = $application->verification_status ?? 'pending';
  $vMeta = $application->verification_meta;
  $steps = $vMeta['steps'] ?? [];
@endphp
<div class="card" style="margin-bottom:22px;border-color:{{ $vStatus === 'passed' ? 'rgba(16,185,129,.3)' : 'rgba(245,158,11,.3)' }}">
  <div class="card-hdr" style="background:{{ $vStatus === 'passed' ? 'rgba(16,185,129,.02)' : 'rgba(245,158,11,.02)' }}">
    <div class="card-title" style="color:{{ $vStatus === 'passed' ? '#0f766e' : '#b45309' }}">
      <i class="bi bi-shield-check"></i> 5-Step Automated Verification Pipeline
    </div>
    <span class="badge {{ $vStatus === 'passed' ? 'bok' : 'bw' }}">
      {{ $vStatus === 'passed' ? 'Passed' : 'Flagged for Review' }}
    </span>
  </div>
  <div class="card-body">
    <div style="display:flex;flex-direction:column;gap:14px">
      {{-- Step 1 --}}
      <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9">
        <div>
          <div style="font-weight:600;font-size:13.5px">1. Metadata & Duplicates Rate Limit</div>
          <div style="font-size:11.5px;color:var(--muted)">{{ $steps['metadata_check']['details'] ?? $steps['metadata_check']['reason'] ?? 'Checks rate-limits & coordinate proximity' }}</div>
        </div>
        <span class="badge {{ ($steps['metadata_check']['status'] ?? 'pending') === 'passed' ? 'bok' : 'bw' }}">
          {{ ucfirst($steps['metadata_check']['status'] ?? 'pending') }}
        </span>
      </div>

      {{-- Step 2 --}}
      <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9">
        <div>
          <div style="font-weight:600;font-size:13.5px">2. SHA-256 Document Fingerprint Check</div>
          <div style="font-size:11.5px;color:var(--muted)">{{ $steps['duplicate_check']['details'] ?? $steps['duplicate_check']['reason'] ?? 'Detects duplicate file signatures across system' }}</div>
        </div>
        <span class="badge {{ ($steps['duplicate_check']['status'] ?? 'pending') === 'passed' ? 'bok' : 'bw' }}">
          {{ ucfirst($steps['duplicate_check']['status'] ?? 'pending') }}
        </span>
      </div>

      {{-- Step 3 --}}
      <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9">
        <div>
          <div style="font-weight:600;font-size:13.5px">3. Vision API / OCR Text Extraction Match</div>
          <div style="font-size:11.5px;color:var(--muted)">{{ $steps['ocr_check']['details'] ?? $steps['ocr_check']['reason'] ?? 'Matches extracted text to form fields' }}</div>
        </div>
        <span class="badge {{ ($steps['ocr_check']['status'] ?? 'pending') === 'passed' ? 'bok' : 'bw' }}">
          {{ ucfirst($steps['ocr_check']['status'] ?? 'pending') }}
        </span>
      </div>

      {{-- Step 4 --}}
      <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:10px;border-bottom:1px solid #f1f5f9">
        <div>
          <div style="font-weight:600;font-size:13.5px">4. Employer Registry Payroll Verification</div>
          <div style="font-size:11.5px;color:var(--muted)">{{ $steps['employer_check']['details'] ?? $steps['employer_check']['reason'] ?? 'Validates employer name & employee number' }}</div>
        </div>
        <span class="badge {{ ($steps['employer_check']['status'] ?? 'pending') === 'passed' ? 'bok' : 'bw' }}">
          {{ ucfirst($steps['employer_check']['status'] ?? 'pending') }}
        </span>
      </div>

      {{-- Step 5 --}}
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div>
          <div style="font-weight:600;font-size:13.5px">5. AWS Rekognition Facial Match Check</div>
          <div style="font-size:11.5px;color:var(--muted)">{{ $steps['facial_match']['details'] ?? $steps['facial_match']['reason'] ?? 'Compares selfie photo with extracted national ID photo' }}</div>
        </div>
        <span class="badge {{ ($steps['facial_match']['status'] ?? 'pending') === 'passed' ? 'bok' : 'bw' }}">
          {{ ucfirst($steps['facial_match']['status'] ?? 'pending') }}
        </span>
      </div>
    </div>
  </div>
</div>

{{-- Documents --}}
@if($application->documents->count())
<div class="card">
  <div class="card-hdr"><div class="card-title">Uploaded Documents</div></div>
  <div class="card-body">
    <div class="g3" style="gap:14px">
      @foreach($application->documents as $doc)
      <div style="border:1px solid #d1e7dd;border-radius:10px;padding:12px;text-align:center">
        <i class="bi bi-file-earmark-image" style="font-size:28px;color:#0f766e"></i>
        <div style="font-size:12px;font-weight:600;margin-top:6px">{{ $doc->type }}</div>
        <span class="badge {{ $doc->status === 'verified' ? 'bok' : ($doc->status === 'rejected' ? 'be' : 'bs') }}" style="margin-top:4px">{{ ucfirst($doc->status) }}</span>
      </div>
      @endforeach
    </div>
  </div>
</div>
@endif

@endsection
