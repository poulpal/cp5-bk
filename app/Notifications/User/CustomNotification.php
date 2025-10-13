<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    use Queueable;

    public function __construct(
        public $parameters,
        public $token
    ) {
    }

    public function via($notifiable)
    {
        return ['smsir'];
    }

    public function toSmsIr($notifiable)
    {
        return [
            'templateId' => $this->token,
            'parameters' => $this->parameters
        ];
    }
}
