<?php

namespace App\Support;

/**
 * نرمال‌سازی/اعتبارسنجی شماره‌های ایران (موبایل و تلفن ثابت)
 * - موبایل: 11 رقم با صفر اول (0912...)
 * - تلفن ثابت: 10 یا 11 رقم با صفر اول (021..., 026...)
 */
class Phone
{
    public static function normalizeIranMobile(?string $input): ?string
    {
        if ($input === null) return null;
        $digits = preg_replace('/\D+/', '', $input);
        if ($digits === '') return null;

        if (str_starts_with($digits, '0098')) $digits = substr($digits, 4);
        if (str_starts_with($digits, '98'))   $digits = substr($digits, 2);
        if (str_starts_with($digits, '9'))    $digits = '0' . $digits;

        return preg_match('/^0\d{10}$/', $digits) ? $digits : null;
    }

    public static function toInternationalIranMobile(?string $mobile): ?string
    {
        $n = self::normalizeIranMobile($mobile);
        return $n ? '+98' . substr($n, 1) : null;
    }

    public static function isValidIranMobile(?string $input): bool
    {
        return self::normalizeIranMobile($input) !== null;
    }

    public static function normalizeIranLandline(?string $input): ?string
    {
        if ($input === null) return null;
        $digits = preg_replace('/\D+/', '', $input);
        if ($digits === '') return null;

        if (str_starts_with($digits, '0098')) $digits = substr($digits, 4);
        if (str_starts_with($digits, '98'))   $digits = substr($digits, 2);
        if (!str_starts_with($digits, '0'))   $digits = '0' . $digits;

        // 10 یا 11 رقم (مثلاً 026xxxxxxx یا 021xxxxxxxx)
        return preg_match('/^0\d{9,10}$/', $digits) ? $digits : null;
    }

    public static function isValidIranLandline(?string $input): bool
    {
        return self::normalizeIranLandline($input) !== null;
    }
}
