<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model {
    protected $fillable = [
        'user_id','user_name','action','module','subject_type',
        'subject_id','subject_label','old_values','new_values',
        'ip_address','description',
    ];
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
    public function user() { return $this->belongsTo(User::class); }

    public static function record(string $action, string $description, $subject = null, array $old = [], array $new = []): self {
        $user = auth('admin')->user();
        return self::create([
            'user_id'       => $user?->id,
            'user_name'     => $user?->name,
            'action'        => $action,
            'module'        => explode('.', $action)[0] ?? null,
            'subject_type'  => $subject ? get_class($subject) : null,
            'subject_id'    => $subject?->id,
            'subject_label' => $subject?->loan_number ?? $subject?->application_number ?? $subject?->payment_reference ?? $subject?->name ?? null,
            'old_values'    => $old ?: null,
            'new_values'    => $new ?: null,
            'ip_address'    => request()->ip(),
            'description'   => $description,
        ]);
    }
}