<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WelcomeBorrowerSms extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected string $plainPassword)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [SmsChannel::class];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        $name = $notifiable->name;
        $phone = $notifiable->phone;
        $password = $this->plainPassword;

        return "Hi {$name}, welcome to MyLoan \n" .
               "Login: https://myloan.co.ls\n" .
               "Username: {$phone}\n" .
               "Password: {$password}\n" .
               "Change password after login.";
    }
}
