<?php

namespace App\Notifications\User;

use App\Models\BuildingUnit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        public BuildingUnit $buildingUnit
    )
    {
        //
    }

    public function via($notifiable)
    {
        return ['smsir'];
    }

    public function toSmsIr($notifiable)
    {
        $resident = $this->buildingUnit->renter ?? $this->buildingUnit->owner;
        return [
            'templateId' => 520688,
            'parameters' => [
                'NAME' => Str::limit($resident->full_name , 25),
                'UNIT' => Str::limit($this->buildingUnit->unit_number),
                'BUILDING' => Str::limit($this->buildingUnit->building->name),
            ]
        ];
    }
}
