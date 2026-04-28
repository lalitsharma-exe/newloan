@extends('borrower.layouts.app')
@section('title','Loan '.$loan->loan_number)
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px">
  <div>
    <div style="font-family:monospace;font-size:12px;font-weight:700;color:var(--p)">{{ $loan->loan_number }}</div>
    <div style="font-size:20px;font-weight:800;margin-top:2px">{{ $loan->loanProduct?->name }}</div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a href="{{ route('borrower.loans.agreement',$loan) }}" class="btn btn-o btn-sm"><i class="bi bi-file-pdf"></i> Agreement</a>
    <a href="{{ route('borrower.loans.statement',$loan) }}" class="btn btn-o btn-sm"><i class="bi bi-file-earmark-text"></i> Statement</a>
    @if(in_array($loan->status,['active','overdue']))
      <a href="{{ route('borrower.loans.settlement',$loan) }}" class="btn btn-o btn-sm"><i class="bi bi-file-earmark-pdf"></i> Settlement</a>
      <a href="{{ route('borrower.payments.make') }}" class="btn btn-p btn-sm"><i class="bi bi-cash"></i> Make Payment</a>
    @endif
    @if(in_array($loan->status,['paid_off','closed']))
      <a href="{{ route('borrower.loans.settlement-letter',$loan) }}" class="btn btn-o btn-sm"><i class="bi bi-patch-check"></i> Settlement Letter</a>
    @endif
  </div>
</div>

@if($loan->application && empty($loan->application->signature_path))
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
  <div style="display:flex;gap:12px;align-items:center;">
    <i class="bi bi-exclamation-triangle-fill" style="color:#ef4444;font-size:24px"></i>
    <div>
      <div style="font-weight:700;color:#991b1b;font-size:15px">Missing Signature</div>
      <div style="color:#991b1b;font-size:13px;opacity:0.9">Your loan agreement requires a digital signature to be complete.</div>
    </div>
  </div>
  <button type="button" onclick="document.getElementById('sigModal').style.display='flex'" class="btn btn-e">Sign Agreement</button>
</div>
@endif

<div class="stat-grid" style="margin-bottom:20px">
  @foreach(['Principal'=>'M '.number_format($loan->principal_amount,2),'Outstanding'=>'M '.number_format($loan->outstanding_balance,2),'Monthly'=>'M '.number_format($loan->monthly_installment,2),'Maturity'=>$loan->maturity_date?->format('d M Y')??'—'] as $l=>$v)
  <div class="stat"><div class="stat-val" style="font-size:18px">{{ $v }}</div><div class="stat-lbl">{{ $l }}</div></div>
  @endforeach
</div>

<div class="card">
  <div class="card-hdr"><span class="card-title">Repayment Schedule</span></div>
  <div style="overflow-x:auto">
    <table class="dt">
      <thead><tr><th>#</th><th>Due Date</th><th>Amount</th><th>Paid</th><th>Outstanding</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($loan->installments as $i)
        <tr style="{{ $i->status==='overdue'?'background:#fef2f2':'' }}">
          <td>{{ $i->installment_number }}</td>
          <td>{{ $i->due_date->format('d M Y') }}</td>
          <td style="font-weight:700">M{{ number_format($i->total_amount,2) }}</td>
          <td style="color:var(--ok)">M{{ number_format($i->paid_amount,2) }}</td>
          <td style="color:{{ $i->outstanding_amount>0?'var(--err)':'var(--ok)' }}">M{{ number_format($i->outstanding_amount,2) }}</td>
          <td><span class="badge {{ $i->status==='paid'?'bok':($i->status==='overdue'?'be':($i->status==='partial'?'bw':'bs')) }}">{{ ucfirst($i->status) }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@if($loan->application && empty($loan->application->signature_path))
<div id="sigModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:20px">
  <div style="background:#fff;border-radius:12px;width:100%;max-width:440px;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
      <h3 style="margin:0;font-size:16px">Sign Loan Agreement</h3>
      <button type="button" onclick="document.getElementById('sigModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer">&times;</button>
    </div>
    <form id="sigForm" method="POST" action="{{ route('borrower.applications.signature', $loan->application) }}">
      @csrf
      <div style="padding:20px">
        <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:8px;text-align:center">
          <canvas id="signature-pad" style="width:100%;height:150px;touch-action:none;border-radius:4px;background:#fff"></canvas>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
            <span style="font-size:11px;color:#64748b">Sign above</span>
            <button type="button" class="btn btn-o btn-sm" onclick="sigPad.clear()" style="padding:4px 8px;font-size:11px">Clear</button>
          </div>
        </div>
        <input type="hidden" name="signature_data" id="signature_data">
      </div>
      <div style="padding:16px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px">
        <button type="button" onclick="document.getElementById('sigModal').style.display='none'" class="btn btn-o">Cancel</button>
        <button type="submit" class="btn btn-p"><i class="bi bi-check-lg"></i> Submit Signature</button>
      </div>
    </form>
  </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
let sigPad;
document.addEventListener("DOMContentLoaded", function() {
    const canvas = document.getElementById('signature-pad');
    if (canvas) {
        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }
        
        // Wait till modal is visible to resize canvas to avoid zero-width bug
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutationRecord) {
                if (document.getElementById('sigModal').style.display === 'flex') {
                    resizeCanvas();
                }
            });    
        });
        observer.observe(document.getElementById('sigModal'), { attributes : true, attributeFilter : ['style'] });
        
        window.addEventListener("resize", resizeCanvas);
        sigPad = new SignaturePad(canvas, { backgroundColor: '#ffffff', penColor: '#0f172a' });

        document.getElementById('sigForm').addEventListener('submit', function(e) {
            if (sigPad.isEmpty()) {
                e.preventDefault();
                alert('Please provide your digital signature before submitting.');
            } else {
                document.getElementById('signature_data').value = sigPad.toDataURL('image/png');
            }
        });
    }
});
</script>
@endpush
@endif
@endsection
