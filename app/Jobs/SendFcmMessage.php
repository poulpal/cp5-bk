<?php

namespace App\Jobs;

use App\Models\unit;
use App\Notifications\User\FcmNotification;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFcmMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $tries = 3;

    public function __construct(
        public $unit,
        public $pattern,
        public $resident_type,
    ) {
        //
    }

    public function handle()
    {
        $pattern = $this->pattern;
        $unit = $this->unit;
        if ($this->resident_type == 'all') {
            foreach ($unit->residents as $resident) {
                $resident->notify(new FcmNotification($unit, $pattern));
            }
        }
        if ($this->resident_type == 'owner' && $unit->owner) {
            $unit->owner->notify(new FcmNotification($unit, $pattern));
        }
        if ($this->resident_type == 'renter' && $unit->renter) {
            $unit->renter->notify(new FcmNotification($unit, $pattern));
        }
        if ($this->resident_type == 'resident') {
            $resident = $unit->renter ?? $unit->owner;
            if ($resident) {
                $resident->notify(new FcmNotification($unit, $pattern));
            }
        }
    }
}
