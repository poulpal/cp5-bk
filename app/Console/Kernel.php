<?php

namespace App\Console;

use App\Jobs\AccepetSmsMessages;
use App\Jobs\addBuildingChargeDebt;
use App\Jobs\addBuildingEarlyPay;
use App\Jobs\addBuildingLateFine;
use App\Jobs\addBuildingRentDebt;
use App\Jobs\RefreshUSDPrice;
use App\Jobs\UpdateCRMGoogleSheets;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->job(new addBuildingChargeDebt)->dailyAt("01:00");
        $schedule->job(new addBuildingRentDebt)->dailyAt("01:30");
        $schedule->job(new addBuildingLateFine)->dailyAt("00:00");
        $schedule->job(new addBuildingEarlyPay)->dailyAt("00:00");

        $schedule->job(new UpdateCRMGoogleSheets)->daily();
        $schedule->command('sitemap:generate')->daily();

        // $schedule->job(new RefreshUSDPrice)->dailyAt("00:00");

        // $schedule->command('backup:clean')->daily()->at('02:00');
        // $schedule->command('backup:run')->daily()->at('03:00');
        // $schedule->command('backup:run --only-db')->hourly();

        $schedule->job(new AccepetSmsMessages)->everyTenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
