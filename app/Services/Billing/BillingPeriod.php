<?php

namespace App\Services\Billing;

/**
 * نگاشت دوره‌ها به تعداد ماه و تعداد ماه پرداختی (قیمت‌گذاری انگیزشی)
 * - monthly:  months=1,  payMonths=1
 * - quarterly: months=3,  payMonths=2.5  (نیم‌ماه رایگان)
 * - yearly:    months=12, payMonths=10   (۲ ماه رایگان)
 */
final class BillingPeriod
{
    public const MONTHLY   = 'monthly';
    public const QUARTERLY = 'quarterly';
    public const YEARLY    = 'yearly';

    private const MONTHS = [
        self::MONTHLY   => 1,
        self::QUARTERLY => 3,
        self::YEARLY    => 12,
    ];

    private const PAY_MONTHS = [
        self::MONTHLY   => 1.0,
        self::QUARTERLY => 2.5,
        self::YEARLY    => 10.0,
    ];

    public static function normalize(?string $period): string
    {
        $p = strtolower($period ?? self::MONTHLY);
        return in_array($p, [self::MONTHLY, self::QUARTERLY, self::YEARLY], true) ? $p : self::MONTHLY;
    }

    public static function months(string $period): int
    {
        return self::MONTHS[self::normalize($period)];
    }

    public static function payMonths(string $period): float
    {
        return self::PAY_MONTHS[self::normalize($period)];
    }

    /**
     * مبلغ دوره از قیمت ماهانهٔ پایه (ریال)
     * price = monthlyBase * payMonths(period)
     */
    public static function amountFromMonthly(int $monthlyBase, string $period): int
    {
        $p = self::normalize($period);
        $pay = self::PAY_MONTHS[$p];
        return (int) round($monthlyBase * $pay);
    }

    /**
     * مدت اعتبار سرویس (برای تمدید) برحسب ماه
     */
    public static function validityMonths(string $period): int
    {
        return self::months($period);
    }
}
