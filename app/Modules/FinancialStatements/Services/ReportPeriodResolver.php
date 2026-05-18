<?php

namespace App\Modules\FinancialStatements\Services;

use App\Models\FinancialPeriod;
use InvalidArgumentException;

class ReportPeriodResolver
{
    /**
     * Resolves generic parameters into resolved financial period database IDs.
     */
    public function resolve(string $type, int $year, ?int $quarter = null, ?int $month = null): array
    {
        return match ($type) {
            'monthly'   => FinancialPeriod::forYearMonth($year, $month)->pluck('id')->toArray(),
            'quarterly' => FinancialPeriod::forQuarter($year, $quarter)->pluck('id')->toArray(),
            'annual'    => FinancialPeriod::forYear($year)->pluck('id')->toArray(),
            default     => throw new InvalidArgumentException('Unknown period type: ' . $type),
        };
    }
}
