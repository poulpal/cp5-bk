<?php

namespace App\Console\Commands;

use App\Models\Building;
use Illuminate\Console\Command;

class TollLateFine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'toll:late-fine';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $building_id = $this->ask('Enter Building id in db');
        $building = Building::find($building_id);


        $units = $building->units()->whereHas('tolls', function ($query) {
            $query->whereNot('status', 'paid');
        })->get();
        foreach ($units as $unit) {
            $charge_debt = $unit->charge_debt;

            $debt = $unit->tolls()
                ->whereNot('status', 'paid')
                ->sum('amount');
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
                'description' => 'خسارت تاخیر در پرداخت عوارض',
                'is_verified' => true,
                'fine_exception' => true,
            ]);

            $charge_debt += -1 * $amount;
            $unit->charge_debt = round($charge_debt, 1);
            $unit->save();
        }
        return Command::SUCCESS;
    }
}
