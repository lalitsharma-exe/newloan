@extends('borrower.layouts.app')
@section('title','Upload Document')
@section('content')
<div style="max-width:520px">
<div style="font-size:20px;font-weight:800;margin-bottom:18px">Upload Document</div>
<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('borrower.documents.upload.post') }}" enctype="multipart/form-data">@csrf
      <div class="fg"><label class="fl">Document Type *</label>
        <select name="type" class="fc" required><option value="">— Select Type —</option><option value="national_id">National ID</option><option value="payslip">Payslip</option><option value="bank_statement">Bank Statement</option><option value="photo">Passport Photo</option><option value="other">Other</option></select>
      </div>
      @if($applications->count())
      <div class="fg"><label class="fl">Link to Application <span style="font-size:11px;color:var(--muted)">(optional)</span></label>
        <select name="application_id" class="fc"><option value="">— Not linked —</option>@foreach($applications as $a)<option value="{{ $a->id }}">{{ $a->application_number }} — {{ $a->loanProduct?->name }}</option>@endforeach</select>
      </div>
      @endif
      <div class="fg"><label class="fl">File * <span style="font-size:11px;color:var(--muted)">(PDF, JPG, PNG — max 5MB)</span></label>
        <input type="file" name="file" class="fc" accept=".pdf,.jpg,.jpeg,.png" required>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <a href="{{ route('borrower.documents.index') }}" class="btn btn-o">Cancel</a>
        <button type="submit" class="btn btn-p"><i class="bi bi-upload"></i> Upload</button>
      </div>
    </form>
  </div>
</div>
</div>
@endsection
