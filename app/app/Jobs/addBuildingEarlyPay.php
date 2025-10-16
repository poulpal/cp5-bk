<?php

namespace App\Jobs;

use App\Models\Building;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Morilog\Jalali\Jalalian;

class addBuildingEarlyPay implements ShouldQueue
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
        return true;
        $buildings = Building::whereHas('options', function ($query) {
            $query
                ->where('early_payment', true)
                ->where('early_payment_days', '>', 0)
                ->where('early_payment_percent', '>', 0)
                ->where('early_payment_days', Jalalian::now()->getDay() - 1);
        })->get();

        foreach ($buildings as $building) {
            $activeBaseModule = $building->modules()->where('type', 'base')->first();
            if (!$activeBaseModule){
                continue;
            }
            $units = $building->units()->where('charge_debt', '<=', 0)->get();
            foreach ($units as $unit) {
                $charge_debt = $unit->charge_debt;
                $unit->invoices()->create([
                    'building_id' => $building->id,
                    'amount' => round($unit->charge_fee * $building->options->early_payment_percent / 100),
                    'status' => 'paid',
                    'payment_method' => 'cash',
                    'serviceable_type' => 'App\Models\BuildingUnit',
                    'serviceable_id' => $unit->id,
                    'description' => 'خوشحسابی پرداخت شارژ ماهیانه',
                    'is_verified' => true,
                ]);


                $charge_debt += round($unit->charge_fee * $building->options->early_payment_percent / 100);
                $unit->charge_debt = round($unit->charge_debt, 1);
                $unit->save();
            }
        }
    }
}
