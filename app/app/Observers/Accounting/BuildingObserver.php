<?php

namespace App\Observers\Accounting;

use App\Jobs\Accounting\AddBuildingAccountingAccounts;
use App\Jobs\UpdateCRMGoogleSheets;
use App\Models\Building;
use App\Models\Module;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BuildingObserver
{
    /**
     * Handle the Building "created" event.
     *
     * @param  \App\Models\Building  $building
     * @return void
     */
    public function created(Building $building)
    {
        // فعال‌سازی افزودنی‌های رایگان برای ساختمان‌های جدید
        try {
            $freeModules = Module::where('type', 'extra')->where('price', 0)->get();
            
            $modulesData = [];
            foreach ($freeModules as $module) {
                $modulesData[$module->slug] = [
                    'starts_at' => now(),
                    'ends_at' => now()->addYears(100)->endOfDay(), // نامحدود
                    'price' => 0,
                ];
            }
            
            // دوره آزمایشی 1 روزه برای پکیج اصلی
            if (config('app.type') == 'main') {
                // می‌توانید اینجا یکی از پکیج‌های اصلی را به صورت trial فعال کنید
                $trialBaseModule = Module::where('slug', 'base-10')->first();
                if ($trialBaseModule) {
                    $modulesData[$trialBaseModule->slug] = [
                        'starts_at' => now(),
                        'ends_at' => now()->addDays(1)->endOfDay(),
                        'price' => 0,
                    ];
                }
            } elseif (config('app.type') == 'kaino') {
                // برای kaino پکیج نامحدود را فعال کن
                $baseInfModule = Module::where('slug', 'base-inf')->first();
                if ($baseInfModule) {
                    $modulesData[$baseInfModule->slug] = [
                        'starts_at' => now(),
                        'ends_at' => now()->addYears(500)->endOfDay(),
                        'price' => 0,
                    ];
                }
            }
            
            $building->modules()->attach($modulesData);
        } catch (\Throwable $th) {
            Log::error($th);
        }

        try {
            dispatch(new UpdateCRMGoogleSheets());
            dispatch_sync(new AddBuildingAccountingAccounts($building->id));

            $max_code = $building->accountingDetails()->max('code') ?? 100000;
            $code = $max_code + 1;

            $building->accountingDetails()->create([
                'name' => 'صندوق شارژپل',
                'code' => $code,
                'type' => 'cash',
                'is_locked' => 1,
            ]);

            $code = $max_code + 1;
            $building->accountingDetails()->create([
                'name' => 'صندوق ساختمان',
                'code' => $code,
                'type' => 'cash',
                'is_locked' => 0,
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    /**
     * Handle the Building "updated" event.
     *
     * @param  \App\Models\Building  $building
     * @return void
     */
    public function updated(Building $building)
    {
        foreach ($building->buildingManagers() as $manager) {
            Cache::forget('building_manager_' . $manager->id);
        }
    }

    /**
     * Handle the Building "deleted" event.
     *
     * @param  \App\Models\Building  $building
     * @return void
     */
    public function deleted(Building $building)
    {
        foreach ($building->buildingManagers() as $manager) {
            Cache::forget('building_manager_' . $manager->id);
        }
    }

    /**
     * Handle the Building "restored" event.
     *
     * @param  \App\Models\Building  $building
     * @return void
     */
    public function restored(Building $building)
    {
        //
    }

    /**
     * Handle the Building "force deleted" event.
     *
     * @param  \App\Models\Building  $building
     * @return void
     */
    public function forceDeleted(Building $building)
    {
        //
    }
}
