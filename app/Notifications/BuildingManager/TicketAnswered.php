<?php

namespace App\Notifications\BuildingManager;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;


class TicketAnswered extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $subject
    )
    {
    }

    public function via($notifiable)
    {
        return ['smsir'];
    }

    public function toSmsIr($notifiable)
    {
        return [
            'templateId' => 287081,
            'parameters' => [
                'FIRSTVARIABLE' => Str::limit($this->subject, 25),
            ]
        ];
    }
}
