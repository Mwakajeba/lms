<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['sms'];
    }

    public function toSms($notifiable)
    {
        // Implement SMS sending logic here, e.g. via an external service
        // Example:
        // SmsService::send($notifiable->phone, $this->message);
        return [
            'to' => $notifiable->phone,
            'message' => $this->message,
        ];
    }
}
