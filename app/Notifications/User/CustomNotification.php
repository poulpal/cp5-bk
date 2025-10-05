<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $unit_number;

    /**
     * Create a new notification instance.
     *
     * @param string $unit_number شماره واحد
     * @return void
     */
    public function __construct($unit_number)
    {
        $this->unit_number = $unit_number;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['smsmelli', 'fcm'];
    }

    /**
     * پیام SMS برای کاربر
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toSmsMelli($notifiable)
    {
        return [
            'text' => "ساکن گرامی واحد {$this->unit_number}\n" .
                     "با شارژ کیف پول خود و جمع‌آوری امتیاز روزانه، " .
                     "شانس خود را برای یک ماه شارژ رایگان افزایش دهید.\n" .
                     "cp.chargepal.ir"
        ];
    }

    /**
     * پیام FCM (Firebase Cloud Messaging) برای نوتیفیکیشن موبایل
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toFcm($notifiable)
    {
        return [
            'title' => 'شارژ کیف پول',
            'body' => "ساکن گرامی واحد {$this->unit_number}\n" .
                     "با شارژ کیف پول خود و جمع‌آوری امتیاز روزانه، " .
                     "شانس خود را برای یک ماه شارژ رایگان افزایش دهید.\n" .
                     "cp.chargepal.ir",
            'data' => [
                'url' => 'https://cp.chargepal.ir/wallet',
                'type' => 'wallet_promotion',
                'unit_number' => $this->unit_number,
            ]
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'unit_number' => $this->unit_number,
            'message' => 'شارژ کیف پول',
            'url' => 'https://cp.chargepal.ir/wallet',
        ];
    }
}