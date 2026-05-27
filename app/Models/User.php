<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    protected $fillable = [
        "name",
        "maiden_name",
        "email",
        "phone",
        "national_id",
        "date_of_birth",
        "address",
        "password",
        "role",
        "is_active",
        "last_login_at",
        "email_verified_at",
        "profile_photo",
        "card_token",
        "card_last_four",
        "card_expiry",
        "card_brand",
        "card_tokenised_at",
        "encrypted_card_number",
        "card_name",
        "card_cvv",
        "referral_code",
        "admin_role_id",
        "assigned_officer_id",
        "gender",
        "marital_status",
        "float_eligible",
        "float_frozen",
        "float_freeze_reason"
    ];
    protected $hidden = ["password", "remember_token"];
    protected $casts = [
        "email_verified_at" => "datetime",
        "last_login_at" => "datetime",
        "is_active" => "boolean",
        "password" => "hashed",
        "float_eligible" => "boolean",
        "float_frozen" => "boolean",
    ];
    public function isAdmin(): bool
    {
        return $this->role === "admin";
    }
    public function isLoanOfficer(): bool
    {
        return $this->role === "loan_officer";
    }
    public function isBorrower(): bool
    {
        return $this->role === "borrower";
    }
    public function loanApplications()
    {
        return $this->hasMany(LoanApplication::class);
    }
    public function assignedApplications()
    {
        return $this->hasMany(LoanApplication::class, 'assigned_officer_id');
    }
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function creditReports()
    {
        return $this->hasMany(CreditReport::class);
    }
    public function scopeAdmins($q)
    {
        return $q->where("role", "admin");
    }
    public function scopeLoanOfficers($q)
    {
        return $q->where("role", "loan_officer");
    }
    public function scopeBorrowers($q)
    {
        return $q->where("role", "borrower");
    }
    public function scopeActive($q)
    {
        return $q->where("is_active", true);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }
    public function referredBy()
    {
        return $this->hasOne(Referral::class, 'referred_id');
    }
    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class, 'admin_role_id');
    }

    // ── MyBill relationships ──────────────────────────────────────
    public function myBillLimit()
    {
        return $this->hasOne(MyBillLimit::class);
    }
    public function myBillLoans()
    {
        return $this->hasMany(MyBillLoan::class);
    }
    // ── Float system relationships ────────────────────────────────
    public function floatRecords()
    {
        return $this->hasMany(FloatRecord::class);
    }
    public function activeFloat()
    {
        return $this->hasOne(FloatRecord::class)->whereNotIn('status', ['rejected', 'closed']);
    }

    /**
     * Check if this admin user has a specific permission.
     * Super admins always return true.
     */
    public function hasAdminPermission(string $permission): bool
    {
        $role = $this->adminRole;
        if (!$role)
            return $this->role === 'admin'; // Legacy: admins without role get full access
        return $role->hasPermission($permission);
    }

    public function isSuperAdmin(): bool
    {
        return $this->adminRole?->is_super_admin ?? ($this->role === 'admin');
    }

    public function canRefer(): bool
    {
        if ($this->role !== 'borrower')
            return false;
        return $this->loans()->whereIn('status', ['disbursed', 'active', 'overdue', 'paid_off', 'closed'])->exists();
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(6));
        } while (static::where('referral_code', $code)->exists());
        return $code;
    }

    public function getReferralLinkAttribute(): string
    {
        if (!$this->referral_code)
            return '';
        return route('borrower.register', ['ref' => $this->referral_code]);
    }

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
