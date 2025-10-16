<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaidCharge extends Notification implements ShouldQueue
{
    use Queueable;

    public $amount;
    public $tracenumber;

    public function __construct($amount, $tracenumber)
    {
        $this->amount = $amount;
        $this->tracenumber = $tracenumber;
    }

    public function via($notifiable)
    {
        return ['smsir'];
    }

    public function toSmsIr($notifiable)
    {
        return [
            'templateId' => 548777,
            'parameters' => [
                'FIRSTVARIABLE' => number_format($this->amount * 10) . ' ریال',
                'SECONDVARIABLE' => $this->tracenumber,
            ]
        ];
    }

    public function toSmsMelli($notifiable)
    {
        return [
            // 'text' => 'مبلغ #FIRSTVARIABLE# با کد ارجاع #SECONDVARIABLE# دریافت شد. www.poulpal.com',
            'text' => "مبلغ " . number_format($this->amount * 10) . " ریال با کد ارجاع " . $this->tracenumber . " دریافت شد. \n www.poulpal.com",
        ];
    }
}
