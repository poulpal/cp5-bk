<?php

namespace App\Jobs;

use App\Facades\CommissionHelper;
use App\Facades\SmsMelli;
use App\Mail\CustomMail;
use App\Models\Building;
use App\Models\BuildingUnit;
use App\Models\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

class AccepetSmsMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $buildings_name_en = ['hamidtower', 'hshcomplex'];
        $buildings = Building::whereIn('name_en', $buildings_name_en)->get();
        foreach ($buildings as $building) {
            $smsMessages = SmsMessage::where('building_id', $building->id)->where('status', 'pending')->get();
            foreach ($smsMessages as $smsMessage) {
                $this->accept($smsMessage);
            }
        }

    }

    private function accept(SmsMessage $smsMessage)
    {
        $credit = SmsMelli::getCredit();
        $smsPrice = CommissionHelper::getSmsPrice();

        if ($credit < ($smsPrice * 10 * $smsMessage->count * $smsMessage->length)) {
            Mail::to(['cc2com.com@gmail.com'])->send(new CustomMail('اعتبار پیامک', 'اعتبار پیامک کم است'));
            return false;
        }

        $smsMessage->update([
            'status' => 'sending'
        ]);

        $items = [];

        foreach ($smsMessage->units as $unit_id) {
            $unit = BuildingUnit::find($unit_id);
            $items[] = new SendSmsMessage($unit, $smsMessage->pattern, $smsMessage->resident_type);
        }

        $batch = Bus::batch($items)
        ->allowFailures()
        ->then(function ($batch) {
            $smsMessage = SmsMessage::where('batch_id', $batch->id)->first();
            $smsMessage->status = 'completed';
            $smsMessage->save();
        })->catch(function ($batch, $e) {
            $smsMessage = SmsMessage::where('batch_id', $batch->id)->first();
            $smsMessage->status = 'failed';
            $smsMessage->save();
            Mail::to('cc2com.com@gmail.com')->send(new CustomMail('خطا در ارسال پیام متنی', $e->getMessage() . "\n" . $batch->id));
        })->finally(function ($batch) {
            // The batch has finished executing...
        })->dispatch();

        $smsMessage->batch_id = $batch->id;
        $smsMessage->save();
    }
}
