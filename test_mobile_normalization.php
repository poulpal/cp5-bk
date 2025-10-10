<?php

/**
 * فایل تست برای توابع Normalize Mobile
 * 
 * نحوه استفاده:
 * php test_mobile_normalization.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Facades\NumberFormatter;

echo "🧪 تست توابع Normalize Mobile Number\n";
echo str_repeat("=", 60) . "\n\n";

// تست‌های مختلف
$testCases = [
    ['input' => '09121234567', 'expected' => '9121234567', 'description' => 'شماره با صفر اول'],
    ['input' => '9121234567', 'expected' => '9121234567', 'description' => 'شماره بدون صفر'],
    ['input' => '۰۹۱۲۱۲۳۴۵۶۷', 'expected' => '9121234567', 'description' => 'اعداد فارسی'],
    ['input' => '٠٩١٢١٢٣٤٥٦٧', 'expected' => '9121234567', 'description' => 'اعداد عربی'],
    ['input' => '+989121234567', 'expected' => '9121234567', 'description' => 'با کد کشور +98'],
    ['input' => '00989121234567', 'expected' => '9121234567', 'description' => 'با کد کشور 0098'],
    ['input' => '0912-123-4567', 'expected' => '9121234567', 'description' => 'با خط تیره'],
    ['input' => '0912 123 4567', 'expected' => '9121234567', 'description' => 'با فاصله'],
    ['input' => '(0912) 123-4567', 'expected' => '9121234567', 'description' => 'با پرانتز'],
];

$passed = 0;
$failed = 0;

foreach ($testCases as $index => $test) {
    $result = NumberFormatter::normalizeMobile($test['input']);
    $status = $result === $test['expected'] ? '✅ PASS' : '❌ FAIL';
    
    echo sprintf(
        "Test #%d: %s\n",
        $index + 1,
        $status
    );
    echo sprintf(
        "  توضیح: %s\n",
        $test['description']
    );
    echo sprintf(
        "  ورودی: %s\n",
        $test['input']
    );
    echo sprintf(
        "  انتظار: %s\n",
        $test['expected']
    );
    echo sprintf(
        "  نتیجه: %s\n",
        $result
    );
    
    if ($result === $test['expected']) {
        $passed++;
    } else {
        $failed++;
    }
    
    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo sprintf("📊 نتیجه: %d موفق | %d ناموفق\n", $passed, $failed);

if ($failed === 0) {
    echo "✅ همه تست‌ها موفقیت‌آمیز بودند!\n";
} else {
    echo "⚠️  برخی تست‌ها ناموفق بودند. لطفاً کد را بررسی کنید.\n";
    exit(1);
}

// تست formatMobileForDisplay
echo "\n" . str_repeat("=", 60) . "\n";
echo "🧪 تست formatMobileForDisplay\n";
echo str_repeat("=", 60) . "\n\n";

$displayTests = [
    ['input' => '9121234567', 'expected' => '09121234567'],
    ['input' => '09121234567', 'expected' => '09121234567'],
    ['input' => '۹۱۲۱۲۳۴۵۶۷', 'expected' => '09121234567'],
];

foreach ($displayTests as $index => $test) {
    $result = NumberFormatter::formatMobileForDisplay($test['input']);
    $status = $result === $test['expected'] ? '✅ PASS' : '❌ FAIL';
    
    echo sprintf(
        "Test #%d: %s | ورودی: %s → نتیجه: %s (انتظار: %s)\n",
        $index + 1,
        $status,
        $test['input'],
        $result,
        $test['expected']
    );
}

// تست isValidIranianMobile
echo "\n" . str_repeat("=", 60) . "\n";
echo "🧪 تست isValidIranianMobile\n";
echo str_repeat("=", 60) . "\n\n";

$validationTests = [
    ['input' => '9121234567', 'expected' => true, 'description' => 'همراه اول'],
    ['input' => '9351234567', 'expected' => true, 'description' => 'ایرانسل'],
    ['input' => '9191234567', 'expected' => true, 'description' => 'رایتل'],
    ['input' => '9901234567', 'expected' => true, 'description' => 'اپراتور مجازی'],
    ['input' => '8121234567', 'expected' => false, 'description' => 'شروع با 8 (نامعتبر)'],
    ['input' => '912123456', 'expected' => false, 'description' => 'کمتر از 10 رقم'],
    ['input' => '91212345678', 'expected' => false, 'description' => 'بیشتر از 10 رقم'],
];

foreach ($validationTests as $index => $test) {
    $result = NumberFormatter::isValidIranianMobile($test['input']);
    $status = $result === $test['expected'] ? '✅ PASS' : '❌ FAIL';
    
    echo sprintf(
        "Test #%d: %s | %s → %s (انتظار: %s)\n",
        $index + 1,
        $status,
        $test['description'],
        $result ? 'معتبر' : 'نامعتبر',
        $test['expected'] ? 'معتبر' : 'نامعتبر'
    );
}

echo "\n✅ تست‌ها تمام شدند!\n";