<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | تنظیمات CORS برای پروژه ChargePal
    | این تنظیمات مشخص می‌کند چه درخواست‌هایی از چه دامنه‌هایی مجاز هستند.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'v1/*'],

    'allowed_methods' => ['POST', 'GET', 'OPTIONS', 'PUT', 'DELETE', 'PATCH'],

    /*
     * دامنه‌های مجاز برای ارسال درخواست
     * 
     * ⚠️ توجه: در محیط production باید فقط دامنه‌های معتبر اضافه شوند
     * استفاده از '*' در production خطر امنیتی دارد
     */
    'allowed_origins' => env('APP_ENV') === 'production' 
        ? [
            'https://cp.chargepal.ir',
            'https://bk.chargepal.ir',
            'https://chargepal.ir',
            'https://digisign.chargepal.ir', // برای نسخه kaino
        ]
        : [
            'http://localhost:3000',
            'http://localhost:8000',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:8000',
            'https://cp.chargepal.ir',
            'https://bk.chargepal.ir',
        ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Range',
        'Authorization',
        'Content-Type',
        'X-Accept-Language',
        'X-Requested-With',
        'Accept',
        'Origin',
    ],

    'exposed_headers' => [
        'Content-Length',
        'X-Total-Count',
    ],

    'max_age' => 3600,

    'supports_credentials' => true,

];