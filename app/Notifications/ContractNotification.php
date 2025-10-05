<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'smsir'
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toSmsIr($notifiable)
    {
        return [
            'templateId' => 458353,
            'parameters' => [
                'PASSWORD' => $this->otp,
                'FIRSTVARIABLE' => 'قبول قرارداد شارژپل'
            ]
        ];
    }

    public function toSmsMelli($notifiable)
    {
        return [
            'text' => "پسورد ورود code: " . $this->otp . " \n chargepal.ir",
        ];
    }
}
