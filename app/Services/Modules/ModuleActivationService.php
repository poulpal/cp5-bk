<?php

namespace App\Services\Modules;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleActivationService
{
    /**
     * @param int|null    $userId
     * @param int|null    $buildingId
     * @param string[]    $items      لیست اسلاگ‌های خریداری‌شده (ممکن است پکیج یا ماژول باشد)
     * @param string      $period     monthly|quarterly|yearly (یا هر رشته‌ای که به ماه قابل تبدیل باشد)
     * @param Carbon|null $startsAt   تاریخ شروع (پیش‌فرض: now)
     */
    public function activate(?int $userId, ?int $buildingId, array $items, string $period = 'monthly', ?Carbon $startsAt = null): void
    {
        $startsAt = $startsAt ?: Carbon::now();

        // 1) پکیج‌ها → ماژول‌ها + نرمال‌سازی اسلاگ
        $moduleSlugs = $this->expandToModules($items);
        $moduleSlugs = array_map([$this, 'canonicalize'], $moduleSlugs);
        $moduleSlugs = array_values(array_unique(array_filter($moduleSlugs)));

        if (empty($moduleSlugs)) return;

        // 2) محاسبه پایان اعتبار از روی دوره
        $months = $this->monthsFor($period);
        $newEndsAt = (clone $startsAt)->addMonths($months);

        // 3) یافتن جدول اشتراک ماژول‌ها با انعطاف نام‌گذاری
        $table = $this->firstExistingTable([
            'module_subscriptions',
            'building_module_subscriptions',
            'user_module_subscriptions',
        ]);
        if (!$table) {
            // اگر چنین جدولی ندارید، بی‌صدا خارج می‌شویم (برای جلوگیری از خطا)
            return;
        }

        $cols = $this->columnsFor($table); // ['module','start','end','user','building']

        foreach ($moduleSlugs as $slug) {
            // اگر رکورد فعالی هست، ends_at را max کن؛ وگرنه insert جدید
            $q = DB::table($table)->where($cols['module'], $slug);
            if ($cols['user'] && $userId)         $q->where($cols['user'], $userId);
            if ($cols['building'] && $buildingId) $q->where($cols['building'], $buildingId);

            $existing = $q->orderByDesc('id')->first();

            if ($existing) {
                $currentEnd = Carbon::parse(Arr::get((array)$existing, $cols['end'], $startsAt));
                $finalEnd   = $currentEnd->gt($newEndsAt) ? $currentEnd : $newEndsAt;

                DB::table($table)->where('id', $existing->id)->update([
                    $cols['start'] => Carbon::parse(Arr::get((array)$existing, $cols['start'], $startsAt)),
                    $cols['end']   => $finalEnd,
                    'updated_at'   => Carbon::now(),
                ]);
            } else {
                DB::table($table)->insert([
                    $cols['module']   => $slug,
                    $cols['start']    => $startsAt,
                    $cols['end']      => $newEndsAt,
                ] + ($cols['user'] && $userId ? [$cols['user'] => $userId] : [])
                  + ($cols['building'] && $buildingId ? [$cols['building'] => $buildingId] : [])
                  + ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            }
        }
    }

    /** اگر آیتم پکیج بود، طبق config(package_modules) به ماژول‌ها بسط می‌دهیم. */
    protected function expandToModules(array $items): array
    {
        $map = (array) config('package_modules', []);
        $out = [];
        foreach ($items as $slug) {
            $slug = $this->canonicalize((string) $slug);
            if (isset($map[$slug])) {
                $out = array_merge($out, (array) $map[$slug]);
            } else {
                $out[] = $slug; // خودِ ماژول
            }
        }
        return $out;
    }

    /** نرمال‌سازی اسلاگ طبق config(module_aliases) */
    protected function canonicalize(string $slug): string
    {
        $aliases = (array) config('module_aliases', []);
        return $aliases[$slug] ?? $slug;
    }

    /** تبدیل دوره به ماه */
    protected function monthsFor(string $period): int
    {
        return match ($period) {
            'monthly'   => 1,
            'quarterly' => 3,
            'yearly'    => 12,
            default     => (int) preg_replace('/\D+/', '', $period) ?: 1,
        };
    }

    /** اولین جدول موجود را پیدا می‌کند */
    protected function firstExistingTable(array $candidates): ?string
    {
        foreach ($candidates as $t) if (Schema::hasTable($t)) return $t;
        return null;
    }

    /** نگاشت نام ستون‌ها بین اسکیمای متفاوت */
    protected function columnsFor(string $table): array
    {
        $has = fn($c) => Schema::hasColumn($table, $c);

        return [
            'module'   => $has('module_slug') ? 'module_slug' : ($has('module') ? 'module' : 'slug'),
            'start'    => $has('starts_at')   ? 'starts_at'   : ($has('start_at') ? 'start_at' : 'created_at'),
            'end'      => $has('ends_at')     ? 'ends_at'     : ($has('expired_at') ? 'expired_at' : 'end_at'),
            'user'     => $has('user_id')     ? 'user_id'     : null,
            'building' => $has('building_id') ? 'building_id' : null,
        ];
    }
}
