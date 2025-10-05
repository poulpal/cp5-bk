<?php

namespace App\Observers\Accounting;

use App\Models\BuildingUnit;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class BuildingUnitObserver
{
    /**
     * Handle the BuildingUnit "created" event.
     *
     * @param  \App\Models\BuildingUnit  $buildingUnit
     * @return void
     */
    public function created(BuildingUnit $buildingUnit)
    {
        try {
            $this->createDetails($buildingUnit);
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    /**
     * Handle the BuildingUnit "updated" event.
     *
     * @param  \App\Models\BuildingUnit  $buildingUnit
     * @return void
     */
    public function updated(BuildingUnit $buildingUnit)
    {
        // check if charge_debt is update
        // if ($buildingUnit->isDirty('charge_debt')) {
        $buildingUnit->resident_debt = $buildingUnit->debt('resident');
        $buildingUnit->owner_debt = $buildingUnit->debt('owner');
        $buildingUnit->charge_debt = $buildingUnit->debt();
        $buildingUnit->saveQuietly();
        // }
    }

    /**
     * Handle the BuildingUnit "deleted" event.
     *
     * @param  \App\Models\BuildingUnit  $buildingUnit
     * @return void
     */
    public function deleted(BuildingUnit $buildingUnit)
    {
        //
    }

    /**
     * Handle the BuildingUnit "restored" event.
     *
     * @param  \App\Models\BuildingUnit  $buildingUnit
     * @return void
     */
    public function restored(BuildingUnit $buildingUnit)
    {
        //
    }

    /**
     * Handle the BuildingUnit "force deleted" event.
     *
     * @param  \App\Models\BuildingUnit  $buildingUnit
     * @return void
     */
    public function forceDeleted(BuildingUnit $buildingUnit)
    {
        //
    }

    private function createDetails($buildingUnit)
    {
        // $details = [];
        // foreach ($buildingUnit->residents as $resident) {
        //     $max_code = $buildingUnit->building->accountingDetails()->max('code') ?? 100000;
        //     $code = $max_code + 1;
        //     $detail = $buildingUnit->building->accountingDetails()->firstOrCreate(
        //         [
        //             'accountable_id' => $resident->id,
        //             'accountable_type' => User::class,
        //         ],
        //         [
        //             'name' => $resident->full_name,
        //             'type' => 'person',
        //             'code' => $code,
        //             'accountable_id' => $resident->id,
        //             'accountable_type' => User::class,
        //         ]
        //     );
        //     $details[] = $detail;
        // }
        // return collect($details);
        $max_code = $buildingUnit->building->accountingDetails()->max('code') ?? 100000;
        $code = $max_code + 1;
        $detail = $buildingUnit->building->accountingDetails()->firstOrCreate(
            [
                'accountable_id' => $buildingUnit->id,
                'accountable_type' => BuildingUnit::class,
            ],
            [
                'name' => __('واحد ') . $buildingUnit->unit_number,
                'type' => 'unit',
                'code' => $code,
                'accountable_id' => $buildingUnit->id,
                'accountable_type' => BuildingUnit::class,
            ]
        );
    }
}
