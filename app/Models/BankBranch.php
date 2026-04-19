<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BankBranch extends Model {
    protected $fillable = ['bank_id', 'name', 'code'];
    public function bank() { return $this->belongsTo(Bank::class); }
}
