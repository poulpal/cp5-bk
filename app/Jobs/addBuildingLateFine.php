<?php

namespace App\Jobs;

use App\Models\Building;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class addBuildingLateFine implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int|null */
    protected $buildingId;

    /**
     * اگر خواستی برای یک ساختمان خاص اجرا کنی، id را بده؛
     * در غیراینصورت (null) روی همهٔ ساختمان‌ها اجرا می‌شود.
     */
    public function __construct(?int $buildingId = null)
    {
        $this->buildingId = $buildingId;
    }

    public function handle(): void
    {
        $buildings = Building::query()
            ->when($this->buildingId, fn($q) => $q->where('id', $this->buildingId))
            ->with(['options', 'units'])
            ->get();

        foreach ($buildings as $building) {
            // گزینهٔ کلی ساختمان: اگر خاموش یا درصد/روز نامعتبر، رد شو
            $opts = $building->options;
            if (!$opts || !$opts->late_fine || ($opts->late_fine_percent ?? 0) <= 0 || ($opts->late_fine_days ?? 0) <= 0) {
                continue;
            }

            $percent = (float) $opts->late_fine_percent;
            $delayDays = (int) $opts->late_fine_days;

            foreach ($building->units as $unit) {
                // اگر واحد ماندهٔ بدهی ندارد، بی‌خیال
                if (max(0, (float)($unit->charge_debt ?? 0)) <= 0) {
                    continue;
                }

                $base = 0.0;
                $consideredInvoices = collect();

                if ((bool)($unit->late_fine_only_last_cycle ?? false) === true) {
                    // ——— حالت «فقط ماه اخیر»
                    $lastCharge = Invoice::query()
                        ->where('building_id', $building->id)
                        ->where('building_unit_id', $unit->id)
                        ->when($this->hasColumn('invoices', 'type'), fn($q) => $q->where('type', 'charge'))
                        ->orderByDesc('created_at')
                        ->first();

                    if (!$lastCharge) {
                        continue;
                    }

                    $periodStart = Carbon::parse($lastCharge->created_at)->startOfMonth();
                    $periodEnd   = Carbon::parse($lastCharge->created_at)->endOfMonth();

                    $consideredInvoices = Invoice::query()
                        ->where('building_id', $building->id)
                        ->where('building_unit_id', $unit->id)
                        ->when($this->hasColumn('invoices', 'type'), fn($q) => $q->where('type', 'charge'))
                        ->whereBetween('created_at', [$periodStart, $periodEnd])
                        ->where(function ($q) {
                            $q->whereNull('paid_at')
                              ->orWhere('remaining_amount', '>', 0)
                              ->orWhereRaw('(amount - COALESCE(paid_amount,0)) > 0');
                        })
                        ->when($this->hasColumn('invoices', 'fine_exception'), fn($q) => $q->where(function($qq){
                            $qq->whereNull('fine_exception')->orWhere('fine_exception', false);
                        }))
                        ->get();

                    $base = $this->sumRemaining($consideredInvoices);
                } else {
                    // ——— حالت «کل بدهی»
                    $consideredInvoices = Invoice::query()
                        ->where('building_id', $building->id)
                        ->where('building_unit_id', $unit->id)
                        ->where(function ($q) {
                            $q->whereNull('paid_at')
                              ->orWhere('remaining_amount', '>', 0)
                              ->orWhereRaw('(amount - COALESCE(paid_amount,0)) > 0');
                        })
                        ->when($this->hasColumn('invoices', 'fine_exception'), fn($q) => $q->where(function($qq){
                            $qq->whereNull('fine_exception')->orWhere('fine_exception', false);
                        }))
                        ->get();

                    $base = $this->sumRemaining($consideredInvoices);
                }

                // اگر آستانهٔ تأخیر رعایت نشده، رد شو
                if (!$this->isPastDelayThreshold($consideredInvoices, $delayDays)) {
                    continue;
                }

                // کسر سپرده (در صورت وجود)
                $deposit = (float)($unit->available_deposit ?? $unit->deposit ?? 0.0);
                $base = max(0.0, $base - $deposit);

                if ($base <= 0) {
                    continue;
                }

                // محاسبهٔ جریمه (درصد ساده از مانده)
                $amount = round($base * $percent / 100, 1);
                if ($amount <= 0) {
                    continue;
                }

                // انباشتن در late_fine واحد (طبق الگوی موجود پروژه)
                $unit->late_fine = (float)($unit->late_fine ?? 0) + $amount;
                $unit->save();
            }
        }
    }

    /**
     * مجموع ماندهٔ هر صورتحساب را حساب می‌کند.
     * اولویت با remaining_amount، وگرنه amount - paid_amount.
     */
    private function sumRemaining($invoices): float
    {
        $sum = 0.0;
        foreach ($invoices as $inv) {
            $remaining = null;

            if ($this->hasColumn('invoices', 'remaining_amount')) {
                $remaining = $inv->remaining_amount;
            }
            if ($remaining === null && ($this->hasColumn('invoices', 'amount') || $this->hasColumn('invoices', 'paid_amount'))) {
                $remaining = (float)($inv->amount ?? 0) - (float)($inv->paid_amount ?? 0);
            }

            $sum += max(0.0, (float)($remaining ?? 0.0));
        }
        return $sum;
    }

    /**
     * بررسی می‌کند که حداقل یکی از صورتحساب‌های لحاظ‌شده
     * از آستانهٔ روزهای تأخیر عبور کرده باشد.
     */
    private function isPastDelayThreshold($invoices, int $delayDays): bool
    {
        $now = now();
        foreach ($invoices as $inv) {
            $baseDate = null;
            if ($this->hasColumn('invoices', 'due_date') && $inv->due_date) {
                $baseDate = Carbon::parse($inv->due_date);
            } else {
                $baseDate = Carbon::parse($inv->created_at);
            }
            if ($baseDate->copy()->addDays($delayDays)->lte($now)) {
                return true;
            }
        }
        return false;
    }

    /**
     * چک وجود ستون در جدول (برای سازگاری با اسکیمای مختلف)
     */
    private function hasColumn(string $table, string $column): bool
    {
        static $cache = [];
        if (!array_key_exists($table, $cache)) {
            $cache[$table] = collect(\Schema::getColumnListing($table))->flip();
        }
        return $cache[$table]->has($column);
    }
}
