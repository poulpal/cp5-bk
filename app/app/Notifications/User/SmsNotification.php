<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class SmsNotification extends Notification implements ShouldQueue
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
        return ['smsmelli'];
    }

    public function toSmsMelli($notifiable)
    {
        $unit = $this->unit;
        $resident = $notifiable;
        $pattern = $this->pattern;
        $link = 'chargepal.ir/b' . $unit->token;

        $text = str_replace(
            ['{unit_number}', '{user_first_name}', '{user_last_name}', '{charge_amount_in_rial}', '{smsmessage_amount_in_rial}', '{unit_payment_link}'],
            [
                Str::limit($unit->unit_number, 20),
                Str::limit($resident->first_name, 20),
                Str::limit($resident->last_name, 20),
                number_format($unit->charge_fee * 10) . ' ریال',
                $unit->charge_debt > 0 ? number_format($unit->charge_debt * 10) . ' ریال' : '0 ریال',
                $link
            ],
            $pattern
        );
        return [
            'text' => $text,
        ];
    }
}
