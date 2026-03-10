<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanApplication extends Model {
    use SoftDeletes;

    protected $fillable = [
        "application_number","user_id","loan_product_id","assigned_officer_id",
        "status","step","title","first_name","surname","national_id","date_of_birth",
        "gender","marital_status","cell_number","email",
        "requested_amount","requested_term","loan_purpose","payout_method","collection_method",
        "approved_amount","approved_term","approved_interest_rate","disbursement_date",
        "risk_score","decline_reason","admin_notes",
        "submitted_at","reviewed_at","decided_at",
    ];

    protected $casts = [
        "date_of_birth"  => "date",
        "submitted_at"   => "datetime",
        "reviewed_at"    => "datetime",
        "decided_at"     => "datetime",
        "disbursement_date" => "date",
    ];

    const STATUS_DRAFT          = "draft";
    const STATUS_SUBMITTED      = "submitted";
    const STATUS_UNDER_REVIEW   = "under_review";
    const STATUS_INFO_REQUESTED = "info_requested";
    const STATUS_ON_HOLD        = "on_hold";
    const STATUS_APPROVED       = "approved";
    const STATUS_DECLINED       = "declined";
    const STATUS_DISBURSED      = "disbursed";

    // ── Relationships ─────────────────────────────────────────────

    public function user()           { return $this->belongsTo(User::class); }
    public function loanProduct()    { return $this->belongsTo(LoanProduct::class); }
    public function assignedOfficer(){ return $this->belongsTo(User::class, "assigned_officer_id"); }

    // FK is `application_id`, not the guessed `loan_application_id`
    public function documents()  { return $this->hasMany(Document::class, "application_id"); }
    public function notes()      { return $this->hasMany(ApplicationNote::class, "application_id")->latest(); }
    public function affordability(){ return $this->hasOne(AffordabilityAssessment::class, "application_id"); }
    public function employment() { return $this->hasOne(Employment::class, "application_id"); }
    public function bankDetails(){ return $this->hasOne(BankDetail::class, "application_id"); }
    public function nextOfKin()  { return $this->hasMany(NextOfKin::class, "application_id"); }
    public function creditReport(){ return $this->hasOne(CreditReport::class, "application_id"); }
    public function loan()       { return $this->hasOne(Loan::class, "application_id"); }

    // ── Accessors ─────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string {
        return match($this->status) {
            "approved"                          => "success",
            "disbursed"                         => "primary",
            "declined"                          => "danger",
            "on_hold","info_requested"          => "warning",
            "under_review"                      => "info",
            "submitted"                         => "secondary",
            default                             => "secondary",
        };
    }

    public function getApplicantNameAttribute(): string {
        return trim("{$this->first_name} {$this->surname}") ?: $this->user?->name ?? '—';
    }
}   