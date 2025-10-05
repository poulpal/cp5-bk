<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Cryptommer\Smsir\Smsir;
use Illuminate\Support\Facades\Session;

class SmsIrChannel
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

        $data = method_exists($notification, 'toSmsIr') ? $notification->toSmsIr($notifiable) : $notification->toArray($notifiable);

        if (empty($data)) {
            return;
        }

        if ($notifiable->role == 'user'){
            foreach ($notifiable->building_units as $unit) {
                if ($unit->building && ($unit->building->name_en == 'hshcomplex' || $unit->building->name_en == 'atishahr')) {
                    return;
                }
            }
        }
        if ($notifiable->role == 'building_manager'){
            if ($notifiable->building && ($notifiable->building->name_en == 'hshcomplex' || $notifiable->building->name_en == 'atishahr')) {
                return;
            }
        }


        $templateId = $data['templateId'];
        $parameters = $data['parameters'];


        $send = smsir::Send();
        $send_parameters = [];
        foreach ($parameters as $key => $value) {
            $parameter = new \Cryptommer\Smsir\Objects\Parameters($key, $value);
            array_push($send_parameters, $parameter);
        }
        // if app in production mode
        if (config('app.env') == 'production') {
            return $send->Verify($mobile, $templateId, $send_parameters);
        } else {
            // log the sms
            \Log::info('SMS', [
                'mobile' => $mobile,
                'templateId' => $templateId,
                'parameters' => $parameters,
            ]);
            // Session::flash('sms', $parameters['PASSWORD']);
        }
    }
}
