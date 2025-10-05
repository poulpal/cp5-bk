<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\PendingDeposit;
use App\Models\Toll;

class TollObserver
{
    /**
     * Handle the Toll "created" event.
     *
     * @param  \App\Models\Toll  $toll
     * @return void
     */
    public function created(Toll $toll)
    {
        //
    }

    /**
     * Handle the Toll "updated" event.
     *
     * @param  \App\Models\Toll  $toll
     * @return void
     */
    public function updated(Toll $toll)
    {
        if ($toll->isDirty('status') && $toll->status == 'paid') {
            $debt = new Invoice();
            $debt->building_id = $toll->building_id;
            $debt->amount = -1 * $toll->amount;
            $debt->status = 'paid';
            $debt->payment_method = 'cash';
            $debt->description = $toll->description;
            $debt->serviceable_id = $toll->serviceable_id;
            $debt->serviceable_type = $toll->serviceable_type;
            $debt->is_verified = true;
            $debt->created_at = $toll->created_at;
            $debt->resident_type = $toll->resident_type;
            $debt->debt_type_id = $toll->building->debtTypes()->first()->id;
            $debt->save();

            $invoice = $toll->invoices()->where('status', 'paid')->first();
            if ($invoice) {
                $deposit = $invoice->replicate();
                $deposit->serviceable_id = $toll->serviceable_id;
                $deposit->serviceable_type = $toll->serviceable_type;
                $deposit->save();

                $pending_deposit = new PendingDeposit();
                $pending_deposit->invoice()->associate($deposit);
                $pending_deposit->building()->associate($toll->unit->building);
                $pending_deposit->save();
            }else{
                $deposit = new Invoice();
                $deposit->building_id = $toll->building_id;
                $deposit->amount = $toll->amount;
                $deposit->status = 'paid';
                $deposit->payment_method = 'cash';
                $deposit->description = $toll->description;
                $deposit->serviceable_id = $toll->serviceable_id;
                $deposit->serviceable_type = $toll->serviceable_type;
                $deposit->is_verified = true;
                $deposit->resident_type = $toll->resident_type;
                $deposit->save();
            }
        }
    }

    /**
     * Handle the Toll "deleted" event.
     *
     * @param  \App\Models\Toll  $toll
     * @return void
     */
    public function deleted(Toll $toll)
    {
        //
    }

    /**
     * Handle the Toll "restored" event.
     *
     * @param  \App\Models\Toll  $toll
     * @return void
     */
    public function restored(Toll $toll)
    {
        //
    }

    /**
     * Handle the Toll "force deleted" event.
     *
     * @param  \App\Models\Toll  $toll
     * @return void
     */
    public function forceDeleted(Toll $toll)
    {
        //
    }
}
