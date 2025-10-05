<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\Notification as MessagingNotification;

class FCMChannel
{
    public function __construct()
    {
        //
    }

    public function send($notifiable, Notification $notification)
    {
        $tokens = $notifiable->fcm_tokens->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $data = method_exists($notification, 'toFCM') ? $notification->toFCM($notifiable) : $notification->toArray($notifiable);

        if (empty($data)) {
            return;
        }

        $title = $data['title'];
        $body = $data['body'];
        $image = $data['image'] ?? null;
        $extra_data = $data['data'] ?? [];

        $messaging = app('firebase.messaging');

        if (config('app.env') == 'production') {
            foreach ($tokens as $key => $token) {
                try {
                    $message = CloudMessage::withTarget(MessageTarget::TOKEN, $token)
                        ->withNotification(MessagingNotification::create($title, $body, $image))
                        ->withData($extra_data);

                    $messaging->send($message);
                } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
                    $notifiable->fcm_tokens()->where('token', $token)->delete();
                } catch (\Throwable $e) {
                    Log::error($e->getMessage());
                    continue;
                }
            }
        } else {
            $message = CloudMessage::withTarget(MessageTarget::TOKEN, 'fg3wolQ_QIunvW7okxLTgG:APA91bETqgFmoHt8fthnFSy-usaxrr600V5BkBjZEZDs2NX_bUAQPjK6LN34UoEMW2HqSiyQ3UHz_M2rM3YQEKJCzbYp59xBDQZVD5R_KlqW8lfsHikIG3yaiCRY57V6TaTa24v0kztm')
                ->withNotification(MessagingNotification::create($title, $body, $image))
                ->withData($extra_data);
            $messaging->send($message);
        }
    }
}
