@extends('admin.layouts.app')
@section('title', 'Create Campaign')
@section('page-title', 'New SMS Campaign')
@section('bc')
<a href="{{ route('admin.dashboard') }}">Dashboard</a> / <a href="{{ route('admin.bulk-sms.index') }}">Bulk SMS</a> / New
@endsection

@section('content')
@push('styles')
<style>
.compose-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}
.audience-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    cursor: pointer;
    transition: all .2s;
    margin-bottom: 10px;
}
.audience-card:hover { border-color: var(--pl); background: rgba(43,75,173,.02); }
.audience-card.selected { border-color: var(--pl); background: rgba(43,75,173,.05); }
.audience-card input[type=radio] { display: none; }
.audience-dot {
    width: 18px; height: 18px;
    border: 2px solid var(--border);
    border-radius: 50%;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: all .2s;
}
.audience-card.selected .audience-dot { border-color: var(--pl); }
.audience-card.selected .audience-dot::after {
    content: '';
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--pl);
}
.char-count { font-size: 11px; color: var(--muted); text-align: right; margin-top: 4px; }
.char-count.warn { color: var(--warn); }
.char-count.err  { color: var(--err); }

.preview-phone {
    background: #1a1a2e;
    border-radius: 20px;
    padding: 20px 16px;
    color: #fff;
}
.preview-bubble {
    background: #2a2a4e;
    border-radius: 0 12px 12px 12px;
    padding: 14px 16px;
    font-size: 13px;
    line-height: 1.6;
    color: rgba(255,255,255,.85);
    margin-top: 12px;
    word-break: break-word;
}

@media (max-width: 768px) {
    .compose-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

<form action="{{ route('admin.bulk-sms.store') }}" method="POST" id="campaignForm">
    @csrf

    <div class="compose-grid">
        <div>
            {{-- Campaign Name --}}
            <div class="card" style="margin-bottom:18px;">
                <div class="card-hdr"><span class="card-title">Campaign Details</span></div>
                <div class="card-body">
                    <div class="fg">
                        <label class="fl">Campaign Name *</label>
                        <input type="text" name="name" class="fc" value="{{ old('name') }}" placeholder="e.g. April Payment Reminder" required>
                        @error('name') <span class="iv">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Audience --}}
            <div class="card" style="margin-bottom:18px;">
                <div class="card-hdr"><span class="card-title">Select Audience</span></div>
                <div class="card-body">
                    <label class="audience-card {{ old('audience', 'all') === 'all' ? 'selected' : '' }}" onclick="selectAudience(this, 'all')">
                        <input type="radio" name="audience" value="all" {{ old('audience', 'all') === 'all' ? 'checked' : '' }}>
                        <div class="audience-dot"></div>
                        <div style="flex:1;">
                            <div style="font-weight:600; font-size:13px;">All Borrowers</div>
                            <div class="muted" style="font-size:11.5px;">Every registered borrower in the system</div>
                        </div>
                        <div style="font-weight:700; color:var(--pl);">{{ $audienceCounts['all'] }}</div>
                    </label>

                    <label class="audience-card {{ old('audience') === 'active_loans' ? 'selected' : '' }}" onclick="selectAudience(this, 'active_loans')">
                        <input type="radio" name="audience" value="active_loans" {{ old('audience') === 'active_loans' ? 'checked' : '' }}>
                        <div class="audience-dot"></div>
                        <div style="flex:1;">
                            <div style="font-weight:600; font-size:13px;">Active Loans</div>
                            <div class="muted" style="font-size:11.5px;">Borrowers with currently active loans</div>
                        </div>
                        <div style="font-weight:700; color:var(--ok);">{{ $audienceCounts['active_loans'] }}</div>
                    </label>

                    <label class="audience-card {{ old('audience') === 'overdue' ? 'selected' : '' }}" onclick="selectAudience(this, 'overdue')">
                        <input type="radio" name="audience" value="overdue" {{ old('audience') === 'overdue' ? 'checked' : '' }}>
                        <div class="audience-dot"></div>
                        <div style="flex:1;">
                            <div style="font-weight:600; font-size:13px;">Overdue Borrowers</div>
                            <div class="muted" style="font-size:11.5px;">Borrowers with overdue loan payments</div>
                        </div>
                        <div style="font-weight:700; color:var(--warn);">{{ $audienceCounts['overdue'] }}</div>
                    </label>

                    <label class="audience-card {{ old('audience') === 'defaulted' ? 'selected' : '' }}" onclick="selectAudience(this, 'defaulted')">
                        <input type="radio" name="audience" value="defaulted" {{ old('audience') === 'defaulted' ? 'checked' : '' }}>
                        <div class="audience-dot"></div>
                        <div style="flex:1;">
                            <div style="font-weight:600; font-size:13px;">Defaulted Borrowers</div>
                            <div class="muted" style="font-size:11.5px;">Borrowers who have defaulted on loans</div>
                        </div>
                        <div style="font-weight:700; color:var(--err);">{{ $audienceCounts['defaulted'] }}</div>
                    </label>

                    <label class="audience-card {{ old('audience') === 'custom' ? 'selected' : '' }}" onclick="selectAudience(this, 'custom')">
                        <input type="radio" name="audience" value="custom" {{ old('audience') === 'custom' ? 'checked' : '' }}>
                        <div class="audience-dot"></div>
                        <div style="flex:1;">
                            <div style="font-weight:600; font-size:13px;">Custom Numbers</div>
                            <div class="muted" style="font-size:11.5px;">Manually enter phone numbers</div>
                        </div>
                        <div><i class="bi bi-pencil-square" style="color:var(--muted);"></i></div>
                    </label>

                    <div id="customPhonesWrap" style="display:{{ old('audience') === 'custom' ? 'block' : 'none' }}; margin-top:12px;">
                        <div class="fg">
                            <label class="fl">Phone Numbers (one per line)</label>
                            <textarea name="phones" class="fc" rows="5" placeholder="+26653797734&#10;+26658123456&#10;+26650000000">{{ old('phones') }}</textarea>
                        </div>
                    </div>

                    @error('audience') <span class="iv">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Message --}}
            <div class="card" style="margin-bottom:18px;">
                <div class="card-hdr">
                    <span class="card-title">Compose Message</span>
                    <span class="muted" style="font-size:11px;">Use {name} for personalisation</span>
                </div>
                <div class="card-body">
                    <div class="fg">
                        <textarea name="message" id="smsMessage" class="fc" rows="5" maxlength="480"
                            placeholder="Hi {name}, your loan payment is due soon. Please make your payment to avoid penalties. — MyLoan" required
                            oninput="updatePreview()">{{ old('message') }}</textarea>
                        <div class="char-count" id="charCount">0 / 480 characters</div>
                        @error('message') <span class="iv">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn btn-p" style="flex:1;" onclick="return confirm('Queue this campaign? Messages will start sending immediately in the background.')">
                    <i class="bi bi-send-fill"></i> Queue Campaign
                </button>
                <a href="{{ route('admin.bulk-sms.index') }}" class="btn btn-o">Cancel</a>
            </div>
        </div>

        {{-- Preview Sidebar --}}
        <div>
            <div class="card">
                <div class="card-hdr"><span class="card-title">Preview</span></div>
                <div class="card-body">
                    <div class="preview-phone">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                            <div style="width:28px; height:28px; border-radius:50%; background:var(--pl); display:flex; align-items:center; justify-content:center;">
                                <i class="bi bi-chat-fill" style="font-size:12px; color:#fff;"></i>
                            </div>
                            <div>
                                <div style="font-size:12px; font-weight:600;">MyLoan</div>
                                <div style="font-size:10px; color:rgba(255,255,255,.4);">SMS</div>
                            </div>
                        </div>
                        <div class="preview-bubble" id="previewBubble">
                            <span style="color:rgba(255,255,255,.3); font-style:italic;">Your message preview will appear here...</span>
                        </div>
                        <div style="text-align:right; margin-top:6px; font-size:10px; color:rgba(255,255,255,.3);" id="previewTime">—</div>
                    </div>

                    <div style="margin-top:16px; padding:12px; background:var(--bg); border-radius:8px; font-size:12px; color:var(--muted);">
                        <div style="font-weight:600; margin-bottom:6px;"><i class="bi bi-lightbulb"></i> Tips</div>
                        <ul style="margin:0; padding-left:16px; line-height:1.7;">
                            <li>Use <strong>{name}</strong> to insert the recipient's name</li>
                            <li>Keep under 160 chars for 1 SMS segment</li>
                            <li>Messages are sent in background queue</li>
                            <li>200ms delay between each message</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function selectAudience(el, val) {
    document.querySelectorAll('.audience-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type=radio]').checked = true;
    document.getElementById('customPhonesWrap').style.display = val === 'custom' ? 'block' : 'none';
}

function updatePreview() {
    const msg = document.getElementById('smsMessage').value;
    const preview = document.getElementById('previewBubble');
    const counter = document.getElementById('charCount');
    const len = msg.length;

    if (msg.trim()) {
        preview.innerHTML = msg.replace(/{name}/g, '<strong>John Doe</strong>').replace(/\n/g, '<br>');
        document.getElementById('previewTime').textContent = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
    } else {
        preview.innerHTML = '<span style="color:rgba(255,255,255,.3); font-style:italic;">Your message preview will appear here...</span>';
        document.getElementById('previewTime').textContent = '—';
    }

    counter.textContent = len + ' / 480 characters';
    counter.className = 'char-count' + (len > 480 ? ' err' : len > 320 ? ' warn' : '');
}

// Init preview
document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endpush
@endsection
