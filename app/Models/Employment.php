<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Employment extends Model {
    protected $fillable = ['application_id','employer_name','employer_type','employment_expiry_date','department','job_title','contact_number','employment_number'];
    protected $casts = ['employment_expiry_date' => 'date'];
    public function application() { return $this->belongsTo(LoanApplication::class); }
}
