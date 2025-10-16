<?php

namespace App\Jobs;

use App\Models\Building;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Morilog\Jalali\Jalalian;

class addBuildingLateFine
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
        $buildings = Building::whereHas('options', function ($query) {
            $query
                ->where('late_fine', true)
                ->where('late_fine_days', '>', 0)
                ->where('late_fine_percent', '>', 0)
                ->where('late_fine_days', Jalalian::now()->getDay() - 1);
        })->get();

        foreach ($buildings as $building) {
            $activeBaseModule = $building->modules()->where('type', 'base')->first();
            if (!$activeBaseModule){
                continue;
            }
            $units = $building->units;
            foreach ($units as $unit) {
                $charge_debt = $unit->charge_debt;
                $debt = $unit->invoices()
                    ->where('status', 'paid')
                    ->where('is_verified', true)
                    // ->where('resident_type', 'resident')
                    ->where('fine_exception', false);

                $debt = -1 * $debt->sum('amount');
                $amount = round(-1 * $debt * $building->options->late_fine_percent / 100);
                if ($amount >= 0) {
                    continue;
                }
                $unit->invoices()->create([
                    'building_id' => $building->id,
                    'amount' => $amount,
                    'status' => 'paid',
                    'payment_method' => 'cash',
                    'serviceable_type' => 'App\Models\BuildingUnit',
                    'serviceable_id' => $unit->id,
                    'description' => 'خسارت تاخیر در پرداخت شارژ',
                    'is_verified' => true,
                    'fine_exception' => true,
                    'debt_type_id' => $building->debtTypes()->where('name', 'جریمه دیرکرد')->first()->id ?? $building->debtTypes()->first()->id,
                ]);

                $charge_debt += -1 * $amount;
                $unit->charge_debt = round($charge_debt, 1);
                $unit->save();
            }
        }

        $building = Building::where('name_en', 'atishahr')->first();
        $units = $building->units;
        foreach ($units as $unit) {
            $charge_debt = $unit->charge_debt;
            $debt = $unit->invoices()
                ->where('status', 'paid')
                ->where('is_verified', true)
                ->where('resident_type', 'resident')
                ->where('fine_exception', false)
                ->where('amount', '<', 0)
                ->where('created_at', '<', Carbon::now()->subDays(10)->endOfDay());
            $debt = -1 * $debt->sum('amount');

            $deposit = $unit->invoices()
                ->where('status', 'paid')
                ->where('is_verified', true)
                ->where('resident_type', 'resident')
                ->where('fine_exception', false)
                ->where('amount', '>', 0)
                ->sum('amount');

            $debt -= $deposit;


            $amount = round($debt * $building->options->late_fine_percent / 100, 1);
            if ($amount <= 0) {
                continue;
            }

            $unit->late_fine = $unit->late_fine + $amount;
            $unit->save();
        }
    }
}
