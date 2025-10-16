<?php

namespace App\Support;

/**
 * هلسپر گروه‌بندی ماژول‌ها:
 * - expand: گسترش اسلاگ‌ها به کل گروه
 * - normalize: فقط کانُن را برگرداند (برای قیمت‌گذاری)
 * - splitBillableShadow: جداکردن billable و shadow برای خرید/فعال‌سازی
 */
class ModuleAliases
{
    protected static array $groups;
    protected static bool $chargeOnlyCanonical;

    protected static function boot(): void
    {
        if (!isset(self::$groups)) {
            $cfg = config('module_aliases');
            $raw = $cfg['groups'] ?? [];
            self::$groups = [];
            foreach ($raw as $grp) {
                $grp = array_values(array_filter(array_unique($grp)));
                if (count($grp) < 2) continue;
                // کل اعضا → شناسه گروه (کانُن = idx0)
                foreach ($grp as $i => $slug) {
                    self::$groups[$slug] = ['canonical' => $grp[0], 'members' => $grp];
                }
            }
            self::$chargeOnlyCanonical = (bool)($cfg['billing']['charge_only_canonical'] ?? true);
        }
    }

    /**
     * گسترش یک اسلاگ به کل اعضای گروهش (اگر عضوی از گروه باشد)
     */
    public static function expand(string $slug): array
    {
        self::boot();
        if (isset(self::$groups[$slug])) {
            return self::$groups[$slug]['members'];
        }
        return [$slug];
    }

    /**
     * گرفتن کانُن یک اسلاگ (اگر عضو گروه باشد)
     */
    public static function canonical(string $slug): string
    {
        self::boot();
        return self::$groups[$slug]['canonical'] ?? $slug;
    }

    /**
     * ورودی: آرایهٔ اسلاگ‌های انتخاب‌شده
     * خروجی:
     *  - billable: فقط کانُن‌ها (برای قیمت‌گذاری)
     *  - shadow: بقیهٔ اعضای گروه‌ها که باید با قیمت 0 فعال شوند
     */
    public static function splitBillableShadow(array $slugs): array
    {
        self::boot();

        $billable = [];
        $shadow = [];

        // 1) گسترش همه انتخاب‌ها به گروه کامل
        $expanded = [];
        foreach ($slugs as $s) {
            foreach (self::expand($s) as $m) {
                $expanded[$m] = true;
            }
        }
        $expanded = array_keys($expanded);

        // 2) اگر chargeOnlyCanonical: فقط کانُن‌ها billable، بقیه shadow
        if (self::$chargeOnlyCanonical) {
            // گروه‌بندی بر اساس کانُن
            $byCanonical = [];
            foreach ($expanded as $m) {
                $c = self::canonical($m);
                $byCanonical[$c][] = $m;
            }
            foreach ($byCanonical as $c => $members) {
                $billable[] = $c;
                foreach ($members as $m) {
                    if ($m !== $c) $shadow[] = $m;
                }
            }
        } else {
            $billable = $expanded;
        }

        // یکتا سازی
        $billable = array_values(array_unique($billable));
        $shadow   = array_values(array_unique($shadow));

        return compact('billable','shadow');
    }
}
