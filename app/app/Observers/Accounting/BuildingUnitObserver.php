<?php

namespace App\Observers\Accounting;

use App\Models\BuildingUnit;
use App\Support\Phone;

/**
 * اعمال پیش‌فرض‌ها و نرمال‌سازی ورودی‌های واحد — بدون دست‌کاری کنترلرها
 * - فیلدها همه "غیراجباری"
 * - block / parking_count / storage_count پیش‌فرض 1
 * - floor فقط عددی
 * - landline_phone طبق الگوی ایران
 * - شماره‌های پارکینگ/انباری: CSV تمیز
 * - مبلغ‌ها: بدون تبدیل واحد (بک‌اند «ریال»)
 */
class BuildingUnitObserver
{
    public function saving(BuildingUnit $unit): void
    {
        $req = request();

        // پیش‌فرض‌های امن در لایه اپلیکیشن
        if (empty($unit->block))         $unit->block = (int) ($req->input('block') ?? 1);
        if (empty($unit->parking_count)) $unit->parking_count = (int) ($req->input('parking_count') ?? 1);
        if (empty($unit->storage_count)) $unit->storage_count = (int) ($req->input('storage_count') ?? 1);

        // طبقه: صرفاً عددی و غیراجباری
        if ($req->filled('floor') && is_numeric($req->input('floor'))) {
            $unit->floor = (int) $req->input('floor');
        }

        // تلفن ثابت ایران: غیراجباری، در صورت نامعتبر → null
        if ($req->filled('landline_phone')) {
            $unit->landline_phone = Phone::normalizeIranLandline($req->input('landline_phone'));
        }

        // شماره‌های پارکینگ/انباری: CSV تمیز
        if ($req->filled('parking_numbers')) {
            $unit->parking_numbers = self::normalizeCsv($req->input('parking_numbers'));
        }
        if ($req->filled('storage_numbers')) {
            $unit->storage_numbers = self::normalizeCsv($req->input('storage_numbers'));
        }

        // مرزبندی حداقلی
        if ($unit->block !== null && $unit->block < 1)                 $unit->block = 1;
        if ($unit->parking_count !== null && $unit->parking_count < 1) $unit->parking_count = 1;
        if ($unit->storage_count !== null && $unit->storage_count < 1) $unit->storage_count = 1;

        // توجه: هیچ تبدیل تومان↔️ریال انجام نمی‌دهیم.
    }

    public function updated(BuildingUnit $buildingUnit): void
    {
        // رفتار قبلی محاسبه بدهی‌ها حفظ می‌شود
        $buildingUnit->resident_debt = $buildingUnit->debt('resident');
        $buildingUnit->owner_debt    = $buildingUnit->debt('owner');
        $buildingUnit->charge_debt   = $buildingUnit->debt();
        $buildingUnit->saveQuietly();
    }

    public function created(BuildingUnit $buildingUnit): void {}
    public function deleted(BuildingUnit $buildingUnit): void {}

    private static function normalizeCsv(?string $csv): ?string
    {
        if ($csv === null) return null;
        $parts = preg_split('/[,\s]+/', trim($csv)) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), fn($p) => $p !== ''));
        return count($parts) ? implode(',', $parts) : null;
    }
}
