<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            // web
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // api پیش‌فرض لاراول (اگر استفاده داری)
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            // ⬇️ فقط mount: کل فایل routes/api/v1.php را زیر /v1 بارگذاری کن
            Route::prefix('v1')
                ->middleware('api')
                ->group(function () {
                    require base_path('routes/api/v1.php');

                    // اگر routes/proforma.php داری و داخل خودش prefix('v1') ندارد، اینجا لودش کن
                    $proforma = base_path('routes/proforma.php');
                    if (file_exists($proforma)) {
                        require $proforma;
                    }
                });
        });
    }
}
