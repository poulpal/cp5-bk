<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChargeAddedNotfication extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $charge,
        public $debt,
        public $token,
        public $subject,
    ) {
    }

    public function via($notifiable)
    {
        return ['smsir'];
    }

    public function toSmsIr($notifiable)
    {
        return [
            'templateId' => 714537,
            'parameters' => [
                'CHARGE' => number_format($this->charge * 10) . ' ریال',
                'DEBT' => number_format($this->debt * 10) . ' ریال',
                'SUBJECT' => $this->subject,
                'RADIF' => 'پرداخت :',
                'NUMBER' => 'chargepal.ir/b',
                'PROFILE' => $this->token,
            ]
        ];
        // return [
        //     'templateId' => 511561,
        //     'parameters' => [
        //         'CHARGE' => number_format($this->charge * 10) . ' ریال',
        //         'DEBT' => number_format($this->debt * 10) . ' ریال',
        //         // 'FIRSTVARIABLE' => 'لینک پرداخت : ' . "\n",
        //         'TOKEN' => $this->token,
        //     ]
        // ];
    }
    // public function via($notifiable)
    // {
    //     return ['smsmelli'];
    // }

    // public function toSmsMelli($notifiable)
    // {
    //     return [
    //         'text' => 'ساکن گرامی شارژ ماهیانه واحد شما به مبلغ ' . number_format($this->debt) . ' تومان به حساب شما افزوده شد.' . "\n" .
    //             "لینک پرداخت \n" .
    //             'poulpal.com/b' . $this->token,
    //     ];
    // }
}
