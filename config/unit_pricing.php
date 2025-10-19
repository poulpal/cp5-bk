<?php
// ===== config/unit_pricing.php =====
// قیمت‌گذاری بر اساس تعداد واحدهای هر مجتمع

return [

    // فعال/غیرفعال کردن مکانیزم قیمت‌گذاری واحدی
    'enabled' => env('UNIT_PRICING_ENABLED', true),

    // ارز پیش‌فرض
    'currency' => 'IRR',

    // نرخ ماهانه «به ازای هر واحد» برای هر پکیج (ریال)
    // مثال درخواستی شما:
    'per_unit_monthly' => [
        'basic'                 => 99000,  // 99,000 ریال برای هر واحد
        'accounting-advanced'   => 69000,  // 69,000 ریال برای هر واحد
        // اگر لازم شد، پکیج‌های دیگر را هم اضافه کن
        // 'standard' => 120000,
        // 'pro'      => 180000,
    ],

    // ضرایب پرداخت برای دوره‌ها:
    // monthly = 1 ماه پرداخت، quarterly = 2.5 ماه پرداخت (یک تخفیف زمانی)، yearly = 10 ماه پرداخت (۲ ماه رایگان)
    'period_multipliers' => [
        'monthly'   => 1.0,
        'quarterly' => 2.5,
        'yearly'    => 10.0,
    ],

    // حداقل تعداد واحد (اگر به هر دلیل شمارش صفر شد)
    'min_units' => 1,

    // نام فیلد/رابط برای شمارش واحدها (اگر مدل‌هایت سفارشی‌اند، اینجا را می‌توانی عوض کنی)
    'relations' => [
        // از ساختمان مدیر جاری برو به رابطه‌ی واحدها
        'manager_to_building' => 'building',   // auth()->user()->buildingManager->building
        'building_to_units'   => 'units',      // $building->units()
    ],
];