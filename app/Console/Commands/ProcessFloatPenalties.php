<?php

namespace App\Console\Commands;

use App\Services\Admin\FloatService;
use Illuminate\Console\Command;

class ProcessFloatPenalties extends Command
{
    protected $signature = 'float:penalties';
    protected $description = 'Process monthly penalties for overdue floats (runs on 1st of month)';

    public function handle(FloatService $service)
    {
        $this->info('Starting float penalty processing...');
        $count = $service->processMonthlyPenalties();
        $this->info("Processed $count overdue floats.");
    }
}
