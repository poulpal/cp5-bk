<?php
// ===== app/Services/Billing/UnitBasedPricing.php =====

namespace App\Services\Billing;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UnitBasedPricing
{
    /**
     * محاسبه‌ی قیمت نهایی پکیج بر اساس تعداد واحدهای ساختمان.
     *
     * @param string      $packageSlug   مثلا: 'basic' | 'accounting-advanced'
     * @param string      $period        'monthly' | 'quarterly' | 'yearly'
     * @param null|int    $buildingId    اگر null باشد، از مدیر جاری ساختمان گرفته می‌شود
     * @param null|int    $unitsOverride اگر دستی بدهی، از دیتابیس نمی‌خواند
     * @return array{
     *   unit_count:int,
     *   per_unit_monthly:int,
     *   pay_multiplier:float,
     *   line_title:string,
     *   unit_price:int,
     *   qty:int,
     *   line_total:int,
     *   currency:string
     * }
     */
    public function compute(string $packageSlug, string $period = 'monthly', ?int $buildingId = null, ?int $unitsOverride = null): array
    {
        if (!Config::get('unit_pricing.enabled', false)) {
            throw new InvalidArgumentException('Unit-based pricing is disabled by config.');
        }

        $perUnitMap = Config::get('unit_pricing.per_unit_monthly', []);
        if (!array_key_exists($packageSlug, $perUnitMap)) {
            throw new InvalidArgumentException("Package slug '{$packageSlug}' is not configured for unit-based pricing.");
        }

        $perUnitMonthly = (int) $perUnitMap[$packageSlug];
        $multiplier     = (float) (Config::get("unit_pricing.period_multipliers.{$period}", 1.0));
        $currency       = Config::get('unit_pricing.currency', 'IRR');

        $unitCount = $this->resolveUnitCount($buildingId, $unitsOverride);
        $unitCount = max($unitCount, (int) Config::get('unit_pricing.min_units', 1));

        // قیمت ماهانه‌ی پکیج = نرخ ماهانه هر واحد × تعداد واحد
        $monthlyPackagePrice = $perUnitMonthly * $unitCount;

        // قیمت قابل پرداخت بر اساس دوره
        $payable = (int) round($monthlyPackagePrice * $multiplier);

        // عنوان شفاف برای خط فاکتور
        $prettyPkg = $this->pretty($packageSlug);
        $prettyPer = $this->prettyPeriod($period);
        $lineTitle = "اشتراک {$prettyPkg} ({$prettyPer}) × {$unitCount} واحد";

        return [
            'unit_count'       => $unitCount,
            'per_unit_monthly' => $perUnitMonthly,
            'pay_multiplier'   => $multiplier,
            'line_title'       => $lineTitle,
            'unit_price'       => $payable, // ما یک خط با qty=1 می‌سازیم
            'qty'              => 1,
            'line_total'       => $payable,
            'currency'         => $currency,
        ];
    }

    /**
     * شمارش تعداد واحدهای ساختمان.
     * اگر $unitsOverride داده شده باشد، همان استفاده می‌شود.
     * اگر buildingId داده نشود، از مدیرساختمانِ لاگین‌شده ساختمان را می‌گیرد.
     */
    public function resolveUnitCount(?int $buildingId = null, ?int $unitsOverride = null): int
    {
        if (!is_null($unitsOverride)) {
            return max(0, (int) $unitsOverride);
        }

        // کش ساده برای جلوگیری از پرس‌وجوهای تکراری در یک درخواست
        $cacheKey = "unit_pricing:unit_count:" . ($buildingId ?? ('manager:' . (Auth::id() ?? 'guest')));
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($buildingId) {
            // 1) اگر buildingId داری، از همان مدل Building بخوان
            if (!is_null($buildingId)) {
                $building = \App\Models\Building::query()->find($buildingId);
                if ($building && method_exists($building, Config::get('unit_pricing.relations.building_to_units', 'units'))) {
                    $rel = Config::get('unit_pricing.relations.building_to_units', 'units');
                    return (int) $building->{$rel}()->count();
                }
            }

            // 2) در غیر این صورت، از مدیرساختمانِ جاری به ساختمان برس
            $user = Auth::user();
            if ($user && method_exists($user, 'buildingManager') && $user->buildingManager) {
                $building = $user->buildingManager->{Config::get('unit_pricing.relations.manager_to_building', 'building')} ?? null;
                if ($building && method_exists($building, Config::get('unit_pricing.relations.building_to_units', 'units'))) {
                    $rel = Config::get('unit_pricing.relations.building_to_units', 'units');
                    return (int) $building->{$rel}()->count();
                }
            }

            return 0;
        });
    }

    private function pretty(string $slug): string
    {
        return str_replace('-', ' ', Str::title($slug));
    }

    private function prettyPeriod(string $period): string
    {
        return match ($period) {
            'monthly'   => 'ماهانه',
            'quarterly' => 'سه‌ماهه',
            'yearly'    => 'سالانه',
            default     => $period,
        };
    }
}
