<?php

namespace App\Services\Modules;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * خروجی فقط «ماژول‌های فعال» می‌دهد (نه پکیج‌ها):
 * - اشتراک‌های مستقیم ماژول را می‌خواند.
 * - اشتراک‌های پکیج را طبق config/package_modules.php به ماژول‌ها بسط می‌دهد.
 * - برای هر ماژول، بزرگ‌ترین ends_at نگه داشته می‌شود.
 */
class ActiveModulesResolver
{
    /**
     * @param array $context ['user_id'=>?, 'building_id'=>?] اختیاری
     * @return array [['slug'=>'reserve','ends_at'=>'2025-12-31 23:59:59'], ...]
     */
    public function resolve(array $context = []): array
    {
        $now = Carbon::now();

        // 1) اشتراک مستقیم ماژول
        $direct = $this->fetchActiveModuleSubs($context, $now);

        // 2) اشتراک‌های پکیج → بسط به ماژول‌ها طبق config
        $packages = $this->fetchActivePackageSubs($context, $now);
        $map = (array) config('package_modules', []);

        $expanded = [];
        foreach ($packages as $pkg) {
            $pkgSlug = $pkg['slug'];
            $pkgEnds = $pkg['ends_at'];
            $mods = $map[$pkgSlug] ?? [];
            foreach ($mods as $mSlug) {
                $expanded[] = ['slug' => $mSlug, 'ends_at' => $pkgEnds];
            }
        }

        // 3) ادغام با بزرگ‌ترین تاریخ پایان
        $all = array_merge($direct, $expanded);
        $bucket = [];
        foreach ($all as $row) {
            $slug = $row['slug'];
            $ends = $row['ends_at'];
            if (!isset($bucket[$slug])) {
                $bucket[$slug] = $ends;
            } else {
                $bucket[$slug] = Carbon::parse($ends)->gt(Carbon::parse($bucket[$slug])) ? $ends : $bucket[$slug];
            }
        }

        $out = [];
        foreach ($bucket as $slug => $ends) {
            $out[] = ['slug' => $slug, 'ends_at' => Carbon::parse($ends)->toDateTimeString()];
        }
        usort($out, fn($a,$b) => strcmp($a['slug'], $b['slug']));

        return $out;
    }

    protected function fetchActiveModuleSubs(array $context, Carbon $now): array
    {
        $candidates = [
            'module_subscriptions',
            'building_module_subscriptions',
            'user_module_subscriptions',
        ];
        $table = $this->firstExistingTable($candidates);
        if (!$table) return [];

        $endsCol = $this->columnOr($table, 'ends_at', 'expired_at');
        $slugCol = $this->columnOr($table, 'module_slug', 'module');

        $q = DB::table($table)->select([$slugCol.' as slug', $endsCol.' as ends_at'])
            ->where($endsCol, '>', $now);

        if (!empty($context['user_id']) && $this->hasColumn($table, 'user_id')) {
            $q->where('user_id', $context['user_id']);
        }
        if (!empty($context['building_id']) && $this->hasColumn($table, 'building_id')) {
            $q->where('building_id', $context['building_id']);
        }

        return $q->get()
            ->map(fn($r) => ['slug' => (string) $r->slug, 'ends_at' => (string) $r->ends_at])
            ->toArray();
    }

    protected function fetchActivePackageSubs(array $context, Carbon $now): array
    {
        $candidates = [
            'package_subscriptions',
            'building_package_subscriptions',
            'user_package_subscriptions',
        ];
        $table = $this->firstExistingTable($candidates);
        if (!$table) return [];

        $endsCol = $this->columnOr($table, 'ends_at', 'expired_at');
        $slugCol = $this->columnOr($table, 'package_slug', 'package');

        $q = DB::table($table)->select([$slugCol.' as slug', $endsCol.' as ends_at'])
            ->where($endsCol, '>', $now);

        if (!empty($context['user_id']) && $this->hasColumn($table, 'user_id')) {
            $q->where('user_id', $context['user_id']);
        }
        if (!empty($context['building_id']) && $this->hasColumn($table, 'building_id')) {
            $q->where('building_id', $context['building_id']);
        }

        return $q->get()
            ->map(fn($r) => ['slug' => (string) $r->slug, 'ends_at' => (string) $r->ends_at])
            ->toArray();
    }

    // ——— Utilities ———
    protected function firstExistingTable(array $candidates): ?string
    {
        foreach ($candidates as $t) if (Schema::hasTable($t)) return $t;
        return null;
    }
    protected function hasColumn(string $table, string $col): bool
    {
        try { return Schema::hasColumn($table, $col); } catch (\Throwable $e) { return false; }
    }
    protected function columnOr(string $table, string $preferred, string $alt): string
    {
        return $this->hasColumn($table, $preferred) ? $preferred : $alt;
    }
}
