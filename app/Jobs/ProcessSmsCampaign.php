<?php

namespace App\Jobs;

use App\Models\{SmsCampaign, SmsCampaignMessage};
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSmsCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600; // 1 hour max
    public int $tries   = 1;

    public function __construct(
        public SmsCampaign $campaign
    ) {}

    public function handle(SmsService $sms): void
    {
        $campaign = $this->campaign;

        $campaign->update(['status' => 'processing']);
        Log::info("BulkSMS: Processing campaign #{$campaign->id} '{$campaign->name}' — {$campaign->total_recipients} recipients");

        $pending = $campaign->messages()->where('status', 'pending')->cursor();

        foreach ($pending as $msg) {
            try {
                $body = $this->personaliseMessage($campaign->message, $msg);
                $sent = $sms->send($msg->phone, $body);

                if ($sent) {
                    $msg->update(['status' => 'sent', 'sent_at' => now()]);
                    $campaign->increment('sent_count');
                } else {
                    $msg->update(['status' => 'failed', 'error' => 'SMS service returned false']);
                    $campaign->increment('failed_count');
                }
            } catch (\Throwable $e) {
                $msg->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 500)]);
                $campaign->increment('failed_count');
                Log::error("BulkSMS: Failed to send to {$msg->phone}", ['error' => $e->getMessage()]);
            }

            // Small delay to avoid rate-limiting
            usleep(200_000); // 200ms
        }

        $campaign->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        Log::info("BulkSMS: Campaign #{$campaign->id} completed. Sent: {$campaign->sent_count}, Failed: {$campaign->failed_count}");
    }

    private function personaliseMessage(string $template, SmsCampaignMessage $msg): string
    {
        return str_replace(
            ['{name}', '{phone}'],
            [$msg->recipient_name ?? 'Customer', $msg->phone],
            $template
        );
    }

    public function failed(\Throwable $exception): void
    {
        $this->campaign->update(['status' => 'failed']);
        Log::error("BulkSMS: Campaign #{$this->campaign->id} FAILED entirely", ['error' => $exception->getMessage()]);
    }
}
