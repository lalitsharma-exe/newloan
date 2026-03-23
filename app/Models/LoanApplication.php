<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanApplication extends Model {
    use SoftDeletes;

   protected $fillable = [
    // Core
    'application_number', 'user_id', 'loan_product_id', 'status', 'step',
 
    // Personal
    'title', 'first_name', 'surname', 'national_id', 'date_of_birth',
    'gender', 'marital_status', 'cell_number', 'email',
 
    // Address
    'residential_address', 'village', 'town', 'district',
    'address_duration', 'residence_type',
    'nearest_landmark', 'home_directions',
    'gps_latitude', 'gps_longitude',
 
    // Loan request
    'requested_amount', 'requested_term', 'loan_purpose',
    'payout_method', 'collection_method', 'salary_payday',
 
    // Approved terms
    'approved_amount', 'approved_term', 'approved_interest_rate',
    'disbursement_date',
 
    // Status tracking
    'submitted_at', 'reviewed_at', 'decided_at',
    'decline_reason', 'admin_notes', 'risk_score',
 
    // Officer/Admin assignment
    'assigned_officer_id',
 
    // Card tokenization step
    'card_tokenised',   // ← boolean, marks step 9 complete
 
    // Overrides
    'override_amount', 'override_term', 'override_rate',
    
    // Signature
    'signature_path',
];

    protected $casts = [
    'date_of_birth'      => 'date',
    'submitted_at'       => 'datetime',
    'reviewed_at'        => 'datetime',
    'decided_at'         => 'datetime',
    'disbursement_date'  => 'date',
    'card_tokenised'     => 'boolean',   // ← add this
    'gps_latitude'       => 'decimal:6',
    'gps_longitude'      => 'decimal:6',
];

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeSubmitted($q)    { return $q->where('status', 'submitted'); }
    public function scopePending($q)      { return $q->whereIn('status', ['submitted','under_review','info_requested','on_hold']); }
    public function scopeUnderReview($q)  { return $q->where('status', 'under_review'); }
    public function scopeApproved($q)     { return $q->where('status', 'approved'); }
    public function scopeDeclined($q)     { return $q->where('status', 'declined'); }
    public function scopeDisbursed($q)    { return $q->where('status', 'disbursed'); }

    // ── Relationships ─────────────────────────────────────────────
    public function user()            { return $this->belongsTo(User::class); }
    public function loanProduct()     { return $this->belongsTo(LoanProduct::class); }
    public function assignedOfficer() { return $this->belongsTo(User::class, 'assigned_officer_id'); }
    public function documents()       { return $this->hasMany(Document::class, 'application_id'); }
    public function notes()           { return $this->hasMany(ApplicationNote::class, 'application_id')->latest(); }
    public function affordability()   { return $this->hasOne(AffordabilityAssessment::class, 'application_id'); }
    public function employment()      { return $this->hasOne(Employment::class, 'application_id'); }
    public function bankDetails()     { return $this->hasOne(BankDetail::class, 'application_id'); }
    public function nextOfKin()       { return $this->hasMany(NextOfKin::class, 'application_id'); }
    public function creditReport()    { return $this->hasOne(CreditReport::class, 'application_id'); }
    public function loan()            { return $this->hasOne(Loan::class, 'application_id'); }
    public function messages()        { return $this->hasMany(ApplicationMessage::class, 'application_id'); }

    // ── Accessors ─────────────────────────────────────────────────
    public function getStatusBadgeAttribute(): string {
        return match($this->status) {
            'approved','disbursed' => 'ok',
            'declined'             => 'e',
            'on_hold',
            'info_requested'       => 'w',
            'under_review'         => 'i',
            'submitted'            => 's',
            default                => 's',
        };
    }

    public function getApplicantNameAttribute(): string {
        return trim("{$this->first_name} {$this->surname}") ?: ($this->user?->name ?? '—');
    }
}