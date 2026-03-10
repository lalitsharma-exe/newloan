<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class NextOfKin extends Model {
    protected $fillable = ['application_id','relationship','first_name','last_name','contact_number'];
    public function application() { return $this->belongsTo(LoanApplication::class); }
}
