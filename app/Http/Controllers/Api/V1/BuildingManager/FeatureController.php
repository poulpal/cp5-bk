<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeatureController extends Controller
{
    /**
     * لیست قابلیت‌ها (الان فقط rent برمی‌گردانیم برای سادگی)
     */
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'code' => 'rent',
                    'name' => 'مدیریت اجاره',
                    'active' => $this->hasRent($request),
                ],
            ],
        ]);
    }

    /**
     * خرید/فعال‌سازی افزونه (نسخهٔ فوری: فعال‌سازی مستقیم بدون درگاه)
     * فرانت‌اند ما به این مسیر می‌زند: POST /building_manager/features/{code}/purchase
     */
    public function purchase(Request $request, string $code)
    {
        if ($code !== 'rent') {
            return response()->json([
                'success' => false,
                'message' => 'Feature not found.',
            ], 404);
        }

        $user = $request->user(); // گارد شما می‌تواند 'api' یا 'building_manager' باشد
        $buildingId = $user->building_id ?? ($user->building->id ?? null);
        if (!$buildingId) {
            return response()->json([
                'success' => false,
                'message' => 'Building not resolved for current user.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // اگر جدول اشتراک‌ها دارید، رکورد active ایجاد/به‌روز می‌کنیم
            if (Schema::hasTable('building_feature_subscriptions')) {
                DB::table('building_feature_subscriptions')->updateOrInsert(
                    [
                        'building_id'   => $buildingId,
                        'feature_code'  => 'rent',
                        // برای یکتا بودن، status را در کلید نگذارید تا همیشه همین رکورد آپدیت شود
                    ],
                    [
                        'status'     => 'active',
                        'starts_at'  => now(),
                        'expires_at' => now()->addYear(), // اگر نمی‌خواهی منقضی شود: null بگذار
                        'meta'       => null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            // تضمین برای فرانت‌اند: اگر BuildingOptions دارید، فلگ has_rent را هم روشن کن
            if (Schema::hasTable('building_options')) {
                // اگر سطر وجود ندارد، ایجاد کن؛ اگر وجود دارد، به‌روزرسانی کن
                $exists = DB::table('building_options')->where('building_id', $buildingId)->exists();
                if ($exists) {
                    DB::table('building_options')
                        ->where('building_id', $buildingId)
                        ->update(['has_rent' => 1, 'updated_at' => now()]);
                } else {
                    DB::table('building_options')
                        ->insert(['building_id' => $buildingId, 'has_rent' => 1, 'created_at' => now(), 'updated_at' => now()]);
                }
            }

            DB::commit();

            // پاسخ سازگار با فرانت‌اند ما (redirectUrl خالی یعنی پرداخت لازم نیست)
            return response()->json([
                'success' => true,
                'message' => 'افزونهٔ اجاره با موفقیت فعال شد',
                'data'    => ['redirectUrl' => null],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'خطا در فعال‌سازی افزونه',
            ], 500);
        }
    }

    private function hasRent(Request $request): bool
    {
        $user = $request->user();
        $buildingId = $user->building_id ?? ($user->building->id ?? null);
        if (!$buildingId) return false;

        // اگر جدول اشتراک‌ها وجود داشته باشد، بر اساس اشتراک active تشخیص بده
        if (Schema::hasTable('building_feature_subscriptions')) {
            $exists = DB::table('building_feature_subscriptions')
                ->where('building_id', $buildingId)
                ->where('feature_code', 'rent')
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->exists();
            if ($exists) return true;
        }

        // در غیر این صورت به building_options تکیه کن
        if (Schema::hasTable('building_options')) {
            return (bool) DB::table('building_options')
                ->where('building_id', $buildingId)
                ->value('has_rent');
        }

        return false;
    }
}
