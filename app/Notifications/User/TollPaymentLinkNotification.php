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

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\BuildingUnit  $unit
     * @param  \App\Models\Toll  $toll
     * @param  string  $buildingName
     * @param  string  $amount - مبلغ فرمت شده با جداکننده (مثل: 5,000,000)
     * @param  string  $description
     * @return void
     */
    public function __construct($unit, $toll, $buildingName, $amount, $description)
    {
        $this->unit = $unit;
        $this->toll = $toll;
        $this->buildingName = $buildingName;
        $this->amount = $amount;
        $this->description = $description;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['smsmelli']; // ✅ استفاده از SmsMelliChannel (mysmsapi.ir)
    }

    /**
     * Get the SMS representation of the notification.
     * ارسال متن ساده از طریق mysmsapi.ir
     *
     * @param  mixed  $notifiable
     * @return array
     */
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
            'text' => $text, // ✅ فرمت صحیح برای SmsMelliChannel
        ];
    }
}