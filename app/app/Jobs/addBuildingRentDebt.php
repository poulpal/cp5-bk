<?php

namespace App\Jobs;

use App\Facades\SmsMelli;
use App\Mail\CustomMail;
use App\Models\Building;
use App\Models\BuildingUnit;
use App\Models\Invoice;
use App\Models\PendingDeposit;
use App\Notifications\BuildingManager\UserPaidCharge;
use App\Notifications\User\ChargeAddedNotfication;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Morilog\Jalali\Jalalian;

class addBuildingRentDebt
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
            $query->where('auto_add_monthly_rent', true)->where('rent_day', Jalalian::now()->getDay());
        })->cursor();
        foreach ($buildings as $building) {
            $activeBaseModule = $building->modules()->where('type', 'base')->first();
            if (!$activeBaseModule){
                continue;
            }
            $units = $building->units()->whereHas('residents', function ($query) {
                $query->where('ownership', 'renter');
            })->cursor();
            foreach ($units as $unit) {
                $rent_fee = $unit->rent_fee;
                if ($unit->rent_fee == 0) {
                    continue;
                }
                $charge_debt = $unit->charge_debt;
                $charge_debt += $rent_fee;
                $unit->charge_debt = round($unit->charge_debt, 1);
                $unit->save();
                $invoice = Invoice::create([
                    'building_id' => $building->id,
                    'amount' => -1 * $rent_fee,
                    'status' => 'paid',
                    'payment_method' => 'cash',
                    'description' => 'هزینه اجاره ' . Jalalian::now()->format('F Y'),
                    'serviceable_id' => $unit->id,
                    'serviceable_type' => BuildingUnit::class,
                    'is_verified' => 1,
                    'debt_type_id' => $building->debtTypes->where('name', 'اجاره')->first()->id ?? $building->debtTypes->first()->id,
                    'early_discount_until' => $building->options->early_payment ? Carbon::now()->addDays($building->options->early_payment_days)->endOfDay() : null,
                    'early_discount_amount' => $building->options->early_payment ? $building->options->early_payment_percent * $rent_fee / 100 : null,
                ]);
                $paidWithWallet = $this->payWithWallet($unit, $invoice);
                if (!$paidWithWallet && $unit->charge_debt > 0) {
                    $send_time = Carbon::now()->startOfDay()->addHours(8);
                    $resident = $unit->renter ?? $unit->owner;
                    $resident->notify(
                        (new ChargeAddedNotfication($rent_fee, $unit->charge_debt, $unit->token, 'اجاره ' . Jalalian::now()->format('F')))
                            ->delay($send_time)
                    );
                }
            }
        }
    }

    private function payWithWallet($unit, $invoice)
    {
        return false;
        DB::transaction(function () use ($unit, $invoice) {
            $resident = $unit->renter ?? $unit->owner;
            $amount = -1 * $invoice->amount;
            if ($resident->balance < $amount || $unit->charge_debt <= 0) {
                return false;
            }
            $invoice = Invoice::create([
                'user_id' => $resident->id,
                'payment_method' => 'wallet',
                'amount' => $amount,
                'building_id' => $unit->building->id,
                'serviceable_id' => $unit->id,
                'serviceable_type' => BuildingUnit::class,
                'description' => 'پرداخت آنلاین بدهی',
                'status' => 'paid'
            ]);

            $unit->charge_debt = round($unit->charge_debt - $amount, 1);
            $unit->save();

            $invoice->user->balance = round($invoice->user->balance - $amount, 1);
            $invoice->user->save();

            $unit->building->balance = round($unit->building->balance + $amount, 1);
            $unit->building->save();

            if ($unit->building->options->send_building_manager_payment_notification) {
                foreach ($unit->building->mainBuildingManagers as $manager) {
                    $manager->notify(new UserPaidCharge($invoice->amount, $invoice->user->full_name, $invoice->user->mobile, $invoice->id, $unit->unit_number));
                }
            }

            Mail::to(['sales@cc2com.com', 'arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com', 'Shaqayeq.shafiee1370@yahoo.com'])->send(
                new CustomMail(
                    'پرداخت شارژ از کیف پول - ساختمان : ' . $unit->building->name . " - " . $invoice->id ?? "",
                    "نام ساختمان : " . $unit->building->name . "<br>" .
                        "واحد : " . $unit->unit_number . " - " . $invoice->user->mobile . "<br>" .
                        "مبلغ : " . number_format($invoice->amount * 10) . " ریال" . "<br>" .
                        "شماره ارجاع : " . ($invoice->id ?? "")
                )
            );

            $pending_deposit = new PendingDeposit();
            $pending_deposit->invoice()->associate($invoice);
            $pending_deposit->building()->associate($unit->building);
            $pending_deposit->save();
        });
    }
}
