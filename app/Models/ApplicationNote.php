<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ApplicationNote extends Model {
    protected $fillable = ["application_id", "created_by", "type", "content", "is_internal"];

    protected $casts = ["is_internal" => "boolean"];

    public function application() { return $this->belongsTo(LoanApplication::class); }

    // column is `created_by`, not `user_id`
    public function createdBy()   { return $this->belongsTo(User::class, "created_by"); }
}