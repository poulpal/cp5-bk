<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\BuildingManager\ModulesController;

/**
 * روت‌های مستقل برای ماژول‌ها و پروفایل BM
 * اگر در فایل routes/api/v1.php همین مسیرها را نداری، این فایل را require کن.
 * مسیرها کامل با prefix هستند، تداخلی با بقیه ندارند.
 */

Route::prefix('v1/building_manager')->group(function () {
    Route::get('modules', [ModulesController::class, 'index'])->name('bm.modules.index');
    Route::get('profile', [ModulesController::class, 'profile'])->name('bm.profile.show');
});
