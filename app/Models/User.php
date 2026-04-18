<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Authenticatable {
    use HasFactory, Notifiable, SoftDeletes;
    protected $fillable = ["name","maiden_name","email","phone","password","role","is_active","last_login_at","email_verified_at","profile_photo", "card_token", "card_last_four", "card_expiry", "card_brand", "card_tokenised_at", "encrypted_card_number", "card_name", "card_cvv"];
    protected $hidden   = ["password","remember_token"];
    protected $casts    = ["email_verified_at"=>"datetime","last_login_at"=>"datetime","is_active"=>"boolean","password"=>"hashed"];
    public function isAdmin():bool       { return $this->role==="admin"; }
    public function isLoanOfficer():bool { return $this->role==="loan_officer"; }
    public function isBorrower():bool    { return $this->role==="borrower"; }
    public function loanApplications()  { return $this->hasMany(LoanApplication::class); }
    public function assignedApplications() { return $this->hasMany(LoanApplication::class, 'assigned_officer_id'); }
    public function loans()             { return $this->hasMany(Loan::class); }
    public function documents()         { return $this->hasMany(Document::class); }
    public function creditReports()     { return $this->hasMany(CreditReport::class); }
    public function scopeAdmins($q)       { return $q->where("role","admin"); }
    public function scopeLoanOfficers($q) { return $q->where("role","loan_officer"); }
    public function scopeBorrowers($q)    { return $q->where("role","borrower"); }
    public function scopeActive($q)       { return $q->where("is_active",true); }

    /**
     * Route notifications for the SMS channel.
     *
     * @return string
     */
    public function routeNotificationForSms()
    {
        return $this->phone;
    }
}
