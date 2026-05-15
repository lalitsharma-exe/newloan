<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id', 'financial_year', 'reporting_period', 'reference_number',
        'complaint_date', 'user_id', 'loan_id', 'customer_first_name', 'customer_surname',
        'account_number', 'customer_type', 'customer_type_other', 'customer_cell_number',
        'customer_email_address', 'age_group', 'sex', 'mode_of_receipt', 'received_at_place',
        'district', 'product_category', 'product_category_other', 'issue_category',
        'issue_category_other', 'description', 'status', 'status_description',
        'resolved_date', 'working_days_to_resolve', 'amount_reimbursed', 'complainant_name_third_party'
    ];

    protected $casts = [
        'complaint_date' => 'date',
        'resolved_date' => 'date',
        'amount_reimbursed' => 'decimal:2'
    ];

    /**
     * Generate sequential reference number MLL-001, MLL-002...
     */
    public static function generateReference()
    {
        $last = self::orderBy('id', 'desc')->first();
        $nextNum = $last ? ((int) str_replace('MLL-', '', $last->reference_number) + 1) : 1;
        return 'MLL-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate working days (Mon-Fri) excluding Lesotho Public Holidays
     */
    public static function calculateWorkingDays($startDate, $endDate)
    {
        if (!$startDate || !$endDate) return null;
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Lesotho Public Holidays 2026 (Example list - in production this would come from a settings table)
        $holidays = [
            '2026-01-01', // New Years
            '2026-03-11', // Moshoeshoe Day
            '2026-04-03', // Good Friday
            '2026-04-06', // Easter Monday
            '2026-05-01', // Workers Day
            '2026-05-14', // Ascension Day
            '2026-05-25', // Africa Day
            '2026-07-17', // Kings Birthday
            '2026-10-04', // Independence Day
            '2026-12-25', // Christmas
            '2026-12-26', // Boxing Day
        ];

        $days = 0;
        $curr = $start->copy();
        
        while ($curr->lte($end)) {
            if ($curr->isWeekday() && !in_array($curr->format('Y-m-d'), $holidays)) {
                $days++;
            }
            $curr->addDay();
        }
        
        return $days;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
