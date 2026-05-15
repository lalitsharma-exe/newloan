<?php

namespace App\Console\Commands;

use App\Services\Admin\FloatService;
use Illuminate\Console\Command;

class TransitionFloatToDue extends Command
{
    protected $signature = 'float:transition-due';
    protected $description = 'Transition disbursed floats to due status (runs on 20th of month)';

    public function handle(FloatService $service)
    {
        $this->info('Transitioning disbursed floats to due status...');
        $count = $service->transitionToDue();
        $this->info("Updated $count floats to DUE status.");
    }
}
