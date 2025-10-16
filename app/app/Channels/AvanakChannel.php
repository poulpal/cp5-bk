<?php

namespace App\Channels;

use App\Facades\Avanak;
use App\Facades\SmsMelli;
use Illuminate\Notifications\Notification;

class AvanakChannel
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

        $data = method_exists($notification, 'toAvanak') ? $notification->toAvanak($notifiable) : $notification->toArray($notifiable);

        if (empty($data)) {
            return;
        }

        $txt = $data['text'];
        if (config('app.env') == 'production') {
            Avanak::QuickSendWithTTS($mobile, $txt);
        } else {
            \Log::info('Avanak', [
                'mobile' => $mobile,
                'text' => $txt,
            ]);
        }
    }
}
