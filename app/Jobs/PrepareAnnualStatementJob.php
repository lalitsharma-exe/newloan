<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class PrepareAnnualStatementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $year;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $year = null)
    {
        $this->year = $year ?: Carbon::now()->subYear()->year;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $service = new \App\Modules\FinancialStatements\Services\AnnualConsolidationService();
        $service->consolidate($this->year);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::send(
                $admin->id,
                'annual_consolidated',
                "Annual Statements for {$this->year} Consolidated",
                "Twelve months of operational data have been consolidated into the statutory draft accounts.",
                "/admin/financial/reports?tier=annual",
                "safe"
            );
        }
    }
}
