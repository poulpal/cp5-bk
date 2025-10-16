<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VoiceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $unit;
    public $pattern;

    public function __construct($unit, $pattern)
    {
        $this->unit = $unit;
        $this->pattern = $pattern;
    }

    public function via($notifiable)
    {
        return ['avanak'];
    }

    public function toAvanak($notifiable)
    {
        $unit = $this->unit;
        $resident = $notifiable;
        $pattern = $this->pattern;

        $text = str_replace(
            ['{unit_number}', '{first_name}', '{last_name}', '{charge}', '{debt}'],
            [$unit->unit_number, $resident->first_name, $resident->last_name, $unit->charge_fee, $unit->charge_debt],
            $pattern
        );
        return [
            'text' => $text,
        ];
    }
}
