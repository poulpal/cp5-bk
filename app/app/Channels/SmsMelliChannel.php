<?php

namespace App\Channels;

use App\Facades\SmsMelli;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Cryptommer\Smsir\Smsir;
use Illuminate\Support\Facades\Session;

class SmsMelliChannel
{
    public function __construct()
    {
        //
    }

    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notifiable, 'routeNotificationForSms')) {
            $mobile = $notifiable->routeNotificationForSms($notifiable);
        } else {
            $mobile = $notifiable->getKey();
        }

        $data = method_exists($notification, 'toSmsMelli') ? $notification->toSmsMelli($notifiable) : $notification->toArray($notifiable);

        if (empty($data)) {
            return;
        }

        $txt = $data['text'];
        if (config('app.env') == 'production') {

            SmsMelli::send($mobile, $txt);
        } else {
            // log the sms
            \Log::info('SMS', [
                'mobile' => $mobile,
                'text' => $txt,
            ]);
        }
    }
}
