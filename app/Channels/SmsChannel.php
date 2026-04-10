<?php

namespace App\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function __construct(protected SmsService $sms)
    {
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // Get the phone number from the notifiable (User model)
        $to = $notifiable->routeNotificationFor('sms', $notification);

        if (!$to) {
            return;
        }

        // Get the SMS message content
        if (!method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);

        if (empty($message)) {
            return;
        }

        // Send via our service
        $this->sms->send($to, $message);
    }
}
