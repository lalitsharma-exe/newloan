<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CblReportArchive extends Model
{
    use HasFactory;

    protected $table = 'cbl_report_archive';

    protected $fillable = [
        'report_period', 'payload', 'file_path_excel', 'file_path_pdf', 
        'generated_by', 'generated_at', 'data_hash'
    ];

    protected $casts = [
        'payload' => 'array',
        'generated_at' => 'datetime',
    ];

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
