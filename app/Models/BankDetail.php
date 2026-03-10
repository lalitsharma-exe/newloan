<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model {
    protected $table = 'bank_details';

    protected $fillable = [
        'application_id','bank_name','account_holder_name','account_number','account_type',
    ];

    public function application() { return $this->belongsTo(LoanApplication::class); }
}