<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RepaymentSchedulePreview extends Model {
    protected $fillable = ['application_id','month','amount','principal','interest'];
    public function application() { return $this->belongsTo(LoanApplication::class); }
}
