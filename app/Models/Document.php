<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Document extends Model {
    protected $fillable = [
        "application_id", "user_id", "type",
        "filename", "original_name", "path", "size", "mime_type",
        "status", "verified_by", "verified_at", "notes",
    ];

    protected $casts = ["verified_at" => "datetime"];

    public function application() { return $this->belongsTo(LoanApplication::class, "application_id"); }
    public function user()        { return $this->belongsTo(User::class); }
    public function verifiedBy()  { return $this->belongsTo(User::class, "verified_by"); }

    // Convenience: get a display-friendly file name
    public function getDisplayNameAttribute(): string {
        return $this->original_name ?? $this->filename;
    }
}