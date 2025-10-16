<?php

namespace App\Notifications\BuildingManager;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserReserved extends Notification implements ShouldQueue
{
    use Queueable;

    public $amount;
    public $name;
    public $mobile;
    public $tracenumber;
    public $title;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($amount, $name, $mobile, $tracenumber, $title)
    {
        $this->amount = $amount;
        $this->name = $name;
        $this->mobile = $mobile;
        $this->tracenumber = $tracenumber;
        $this->title = $title;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['smsir'];
    }

    public function toSmsIr($notifiable)
    {
        return [
            'templateId' => 103564,
            'parameters' => [
                'AMOUNT' => number_format($this->amount * 10),
                'REF' => $this->tracenumber,
                'MOBILE' => $this->mobile,
                'RESERVE' => $this->title,
            ]
        ];
    }

    // public function toSmsIr($notifiable)
    // {
    //     return [
    //         'templateId' => 478336,
    //         'parameters' => [
    //             'FIRSTVARIABLE' => number_format($this->amount * 10),
    //             'SECONDVARIABLE' => $this->tracenumber,
    //             'THIRDVARIABLE' => $this->mobile,
    //             'FOURTHVARIABLE' => 'پول پل',
    //         ]
    //     ];
    // }

    public function toSmsMelli($notifiable)
    {
        return [
            // 'text' => '',
            'text' => "مبلغ" . number_format($this->amount * 10) . "ریال با شماره ارجاع" . $this->tracenumber . "توسط کاربر شماره" . $this->mobile . "واریز شد. پول پل",
        ];
    }
}
