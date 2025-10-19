<?php
// ===== app/Http/Controllers/Api/V1/BuildingManager/ProformaController.php =====
// ساخت پیش‌فاکتور با قیمت‌گذاری واحدی (per-unit) بر اساس تعداد واحدهای مجتمع

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Services\Billing\UnitBasedPricing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProformaController extends Controller
{
    /**
     * POST /v1/building_manager/proforma
     * ورودی نمونه:
     * {
     *   "package_slug": "basic",
     *   "period": "monthly",   // monthly | quarterly | yearly
     *   "discount": 0,         // اختیاری - ریال
     *   "tax_percent": 0,      // اختیاری - درصد
     *   "building_id": 123     // اختیاری - اگر ندهید از مدیر لاگین‌شده استخراج می‌شود
     * }
     */
    public function store(Request $request, UnitBasedPricing $unitPricing)
    {
        // ===== اعتبارسنجی ورودی =====
        $request->validate([
            'package_slug' => ['required', 'string', Rule::in(array_keys(config('unit_pricing.per_unit_monthly', [])))],
            'period'       => ['nullable', 'string', Rule::in(['monthly','quarterly','yearly'])],
            'discount'     => ['nullable', 'integer', 'min:0'],
            'tax_percent'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'building_id'  => ['nullable', 'integer', 'min:1'],
        ]);

        $packageSlug = $request->string('package_slug')->toString();
        $period      = $request->input('period', 'monthly');
        $discount    = (int)$request->input('discount', 0);
        $taxPercent  = (float)$request->input('tax_percent', 0);
        $buildingId  = $request->input('building_id');

        // ===== محاسبهٔ خط پکیج بر اساس تعداد واحد =====
        $line = $unitPricing->compute($packageSlug, $period, $buildingId);

        // یک آرایهٔ آیتم‌ها درست می‌کنیم (در این نسخه فقط یک آیتم داریم)
        $items = [[
            'title'      => $line['line_title'],
            'qty'        => $line['qty'],
            'unit_price' => $line['unit_price'],
            'line_total' => $line['line_total'],
        ]];

        // ===== محاسبات مالی =====
        $subtotal = array_sum(array_column($items, 'line_total'));
        $taxBase  = max(0, $subtotal - $discount);
        $tax      = (int) floor($taxBase * ($taxPercent / 100));
        $total    = max(0, $subtotal - $discount + $tax);
        $currency = $line['currency'];

        // ===== خروجی JSON (در صورت نیاز، اینجا می‌توانید ذخیره در DB هم اضافه کنید) =====
        return response()->json([
            'success'  => true,
            'data'     => [
                'package_slug'        => $packageSlug,
                'period'              => $period,
                'unit_count'          => $line['unit_count'],
                'per_unit_monthly'    => $line['per_unit_monthly'],
                'pay_multiplier'      => $line['pay_multiplier'],
                'currency'            => $currency,

                'items'               => $items,
                'subtotal'            => $subtotal,
                'discount'            => $discount,
                'tax_percent'         => $taxPercent,
                'tax'                 => $tax,
                'total'               => $total,
            ],
        ]);
    }
}