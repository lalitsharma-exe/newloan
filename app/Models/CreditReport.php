<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CreditReport extends Model {
    protected $table = 'credit_reports';

    protected $fillable = [
        'user_id','application_id','national_id','provider','check_type',
        'credit_score','report_data','raw_response','status','retrieved_at',
    ];

    protected $casts = [
        'report_data'  => 'array',
        'raw_response' => 'array',
        'retrieved_at' => 'datetime',
    ];

    public function user()        { return $this->belongsTo(User::class); }
    public function application() { return $this->belongsTo(LoanApplication::class); }
}