<?php

namespace App\Jobs;

use App\Models\BuildingUnit;
use App\Notifications\User\VoiceNotification;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendVoiceMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $tries = 3;

    public function __construct(
        public $unit,
        public $pattern,
    ) {
        //
    }

    public function handle()
    {
        $pattern = $this->pattern;
        $unit = $this->unit;
        $residents = $unit->residents;
        foreach ($residents as $resident) {
            $resident->notify(new VoiceNotification($unit, $pattern));
        }
    }
}
