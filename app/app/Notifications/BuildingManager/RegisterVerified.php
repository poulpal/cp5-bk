<?php

namespace App\Notifications\BuildingManager;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class RegisterVerified extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        return ['smsir'];
    }

    public function toSmsIr($notifiable)
    {
        return [
            'templateId' => 125349,
            'parameters' => [
                'FIRSTVARIABLE' => Str::limit($notifiable->full_name, 25),
                'SECONDVARIABLE' => 'تایید شد.',
                'THIRDVARIABLE' => 'chargepal.ir'
            ]
        ];
    }
}
