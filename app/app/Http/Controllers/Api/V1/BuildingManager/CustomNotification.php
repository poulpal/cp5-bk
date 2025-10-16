<?php

namespace App\Notifications;

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

    public function toSmsMelli($notifiable)
    {
        return [
            'text' => "ساکن گرامی واحد $this->unit_number \n با شارژ کیف پول خود  و جمع آوری امتیاز روزانه شانس خود را برای یک ماه شارژ رایگان افزایش دهید \n poulpal.com, chargepal.ir"
        ];
    }

    public function toFcm($notifiable)
    {
        return [
            'title' => 'شارژ کیف پول',
            'body' => "ساکن گرامی واحد $this->unit_number \n با شارژ کیف پول خود  و جمع آوری امتیاز روزانه شانس خود را برای یک ماه شارژ رایگان افزایش دهید \n poulpal.com, chargepal.ir"
        ];
    }
}
