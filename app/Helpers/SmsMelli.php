<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TollPaymentLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $unit;
    public $toll;
    public $buildingName;
    public $amount;
    public $description;

    public function __construct($unit, $toll, $buildingName, $amount, $description)
    {
        $this->unit = $unit;
        $this->toll = $toll;
        $this->buildingName = $buildingName;
        $this->amount = $amount;
        $this->description = $description;
    }

    public function via($notifiable)
    {
        return ['smsmelli']; // ✅ mysmsapi.ir
    }

    public function toSmsMelli($notifiable)
    {
        // ساخت لینک پرداخت
        $landingUrl = config('app.landing_url', 'https://chargepal.ir');
        $paymentLink = $landingUrl . '/p/' . $this->toll->token;

        // ساخت متن پیامک
        $text = "سلام";
        if ($notifiable->first_name) {
            $text .= " " . $notifiable->first_name;
        }
        $text .= "\n";
        $text .= "واحد {$this->unit->unit_number} - {$this->buildingName}\n";
        $text .= "مبلغ: {$this->amount} ریال\n";
        $text .= "{$this->description}\n";
        $text .= "لینک پرداخت:\n{$paymentLink}";

        return [
            'text' => $text, // ✅ فرمت صحیح
        ];
    }
}