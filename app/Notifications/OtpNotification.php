<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpNotification extends Notification
{
    use Queueable;

    protected $otp;
    protected $hash;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($otp, $hash = null)
    {
        $this->otp = $otp;
        $this->hash = $hash;
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
            'smsir',
            // 'smsmelli'
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
        $last_line = 'chargepal.ir';
        if (config('app.type') == 'c36') {
            $last_line = 'c36.ir';
        }
        if (config('app.type') == 'kaino') {
            $last_line = 'a444.ir';
        }
        if ($this->hash) {
            $last_line .= "/n" . $this->hash;
        }
        return [
            'templateId' => 458353,
            'parameters' => [
                'PASSWORD' => $this->otp,
                'FIRSTVARIABLE' => $last_line
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
