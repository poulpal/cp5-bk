<?php
// ===== app/Services/Billing/ProformaInvoiceService.php =====
// سرویس ساخت پیش‌فاکتور با قیمت‌گذاری «به‌ازای هر واحد» + محاسبه تخفیف/مالیات + ذخیره آیتم‌ها
// کاملاً خودکفا، با fallback به قیمت ثابت پکیج در صورت نبودن نرخ واحدی.

namespace App\Services\Billing;

use App\Models\ProformaInvoice;
use App\Models\ProformaInvoiceItem;
use App\Models\Package;
use App\Models\Building;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ProformaInvoiceService
{
    // ===== تنظیمات پیش‌فرض (در صورت نبودن config)
    private array $defaultPerUnitMonthly = [
        // ماهانه به‌ازای هر واحد (ریال)
        'basic'               => 99000,
        'accounting-advanced' => 69000,
        // در صورت نیاز بعدها اضافه کنید: 'standard' => 120000, 'pro' => 180000, ...
    ];

    private array $defaultPeriodMultipliers = [
        // ضرایب پرداخت: monthly=1, quarterly=2.5, yearly=10
        'monthly'   => 1.0,
        'quarterly' => 2.5,
        'yearly'    => 10.0,
    ];

    private string $defaultCurrency = 'IRR';

    /**
     * ساخت پیش‌فاکتور و ذخیره در دیتابیس.
     *
     * ورودی نمونه:
     * [
     *   'package_slug' => 'basic',             // اختیاری؛ اگر آیتم‌های دستی می‌دهید لازم نیست
     *   'period'       => 'monthly',           // 'monthly'|'quarterly'|'yearly' (پیش‌فرض monthly)
     *   'building_id'  => 123,                 // اختیاری؛ اگر ندهید از مدیرِ لاگین‌شده استخراج می‌شود
     *   'units'        => 40,                  // اختیاری؛ override تعداد واحد (برای تست/حالات خاص)
     *   'items'        => [                    // اختیاری؛ آیتم‌های دستی (اگر بدهید کنار پکیج افزوده می‌شوند)
     *       ['title'=>'X','qty'=>2,'unit_price'=>10000],
     *   ],
     *   'discount'     => 0,                   // ریال (پیش‌فرض 0)
     *   'tax_percent'  => 0,                   // درصد (پیش‌فرض 0)
     *   'currency'     => 'IRR',               // اختیاری (پیش‌فرض IRR)
     *   'meta'         => ['note'=>'...'],     // اختیاری؛ هر داده‌ی جانبی
     * ]
     */
    public function create(array $payload): ProformaInvoice
    {
        // ===== اعتبارسنجی ورودی با پیام‌های ساده =====
        $v = Validator::make($payload, [
            'package_slug' => ['nullable', 'string'],
            'period'       => ['nullable', 'string', Rule::in(['monthly','quarterly','yearly'])],
            'building_id'  => ['nullable', 'integer', 'min:1'],
            'units'        => ['nullable', 'integer', 'min:1', 'max:100000'],
            'items'        => ['nullable', 'array'],
            'items.*.title'      => ['required_with:items', 'string', 'max:255'],
            'items.*.qty'        => ['required_with:items', 'integer', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'integer', 'min:0'],
            'discount'     => ['nullable', 'integer', 'min:0'],
            'tax_percent'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'currency'     => ['nullable', 'string', 'size:3'],
            'meta'         => ['nullable', 'array'],
        ], [
            'period.in' => 'دوره باید یکی از مقادیر monthly, quarterly, yearly باشد.',
        ]);
        if ($v->fails()) {
            throw new InvalidArgumentException($v->errors()->first());
        }

        $period     = $payload['period']      ?? 'monthly';
        $discount   = (int)($payload['discount']    ?? 0);
        $taxPercent = (float)($payload['tax_percent'] ?? 0);
        $currency   = $payload['currency']    ?? ($this->cfg('currency', $this->defaultCurrency));
        $meta       = $payload['meta']        ?? [];

        // ===== ساخت لیست آیتم‌ها =====
        $items = [];

        // 1) اگر پکیج داده شد → خط پکیج را با منطق «به‌ازای هر واحد» بساز
        if (!empty($payload['package_slug'])) {
            $pkgLine = $this->buildPackageLine(
                packageSlug: $payload['package_slug'],
                period: $period,
                buildingId: $payload['building_id'] ?? null,
                unitsOverride: $payload['units'] ?? null
            );
            $items[] = $pkgLine;
        }

        // 2) اگر آیتم‌های دستی هم داده شده‌اند → اضافه کن
        if (!empty($payload['items']) && is_array($payload['items'])) {
            foreach ($payload['items'] as $it) {
                $qty  = (int)$it['qty'];
                $unit = (int)$it['unit_price'];
                $items[] = [
                    'title'      => (string)$it['title'],
                    'qty'        => $qty,
                    'unit_price' => $unit,
                    'line_total' => (int)($qty * $unit),
                ];
            }
        }

        if (empty($items)) {
            throw new InvalidArgumentException('هیچ پکیج یا آیتمی برای پیش‌فاکتور ارسال نشده است.');
        }

        // ===== محاسبات مالی =====
        $subtotal = array_sum(array_column($items, 'line_total'));
        $taxBase  = max(0, $subtotal - $discount);
        $tax      = (int) floor($taxBase * ($taxPercent / 100));
        $total    = max(0, $subtotal - $discount + $tax);

        // ===== ذخیره در دیتابیس (اتمیک) =====
        return DB::transaction(function () use ($payload, $items, $subtotal, $discount, $taxPercent, $tax, $total, $currency, $meta) {

            /** @var \App\Models\ProformaInvoice $pi */
            $pi = new ProformaInvoice();
            // ستون‌های متداول—اگر در مدل/جدول شما نام‌ها متفاوت است، همینجا هماهنگ کنید
            $pi->user_id     = Auth::id();
            $pi->building_id = $payload['building_id'] ?? $this->guessBuildingId();
            $pi->period      = $payload['period'] ?? 'monthly';
            $pi->currency    = $currency;

            $pi->subtotal    = (int)$subtotal;
            $pi->discount    = (int)$discount;
            $pi->tax_percent = (float)$taxPercent;
            $pi->tax         = (int)$tax;
            $pi->total       = (int)$total;

            // توصیه: ستون JSON شما «meta» است (طبق بررسی قبلی)
            $pi->meta        = $meta;
            $pi->status      = 'draft'; // اگر ستون وضعیت دارید

            $pi->save();

            // آیتم‌ها
            foreach ($items as $it) {
                $pi->items()->create([
                    'title'      => $it['title'],
                    'qty'        => (int)$it['qty'],
                    'unit_price' => (int)$it['unit_price'],
                    'line_total' => (int)$it['line_total'],
                ]);
            }

            // خروجی به‌همراه آیتم‌ها
            return $pi->load('items');
        });
    }

    /**
     * پیش‌نمایش محاسبات (بدون ذخیره در دیتابیس).
     */
    public function preview(array $payload): array
    {
        $tmp = $this->createArrayOnly($payload);
        return $tmp;
    }

    // ======== توابع داخلی ========

    /**
     * ساخت «فقط محاسبه» (بدون ذخیره).
     */
    private function createArrayOnly(array $payload): array
    {
        // اعتبارسنجی اولیه مختصر
        $period     = $payload['period']      ?? 'monthly';
        $discount   = (int)($payload['discount']    ?? 0);
        $taxPercent = (float)($payload['tax_percent'] ?? 0);
        $currency   = $payload['currency']    ?? ($this->cfg('currency', $this->defaultCurrency));

        $items = [];
        if (!empty($payload['package_slug'])) {
            $pkgLine = $this->buildPackageLine(
                packageSlug: $payload['package_slug'],
                period: $period,
                buildingId: $payload['building_id'] ?? null,
                unitsOverride: $payload['units'] ?? null
            );
            $items[] = $pkgLine;
        }
        if (!empty($payload['items']) && is_array($payload['items'])) {
            foreach ($payload['items'] as $it) {
                $qty  = (int)$it['qty'];
                $unit = (int)$it['unit_price'];
                $items[] = [
                    'title'      => (string)$it['title'],
                    'qty'        => $qty,
                    'unit_price' => $unit,
                    'line_total' => (int)($qty * $unit),
                ];
            }
        }
        if (empty($items)) {
            throw new InvalidArgumentException('هیچ پکیج یا آیتمی برای پیش‌نمایش ارسال نشده است.');
        }

        $subtotal = array_sum(array_column($items, 'line_total'));
        $taxBase  = max(0, $subtotal - $discount);
        $tax      = (int) floor($taxBase * ($taxPercent / 100));
        $total    = max(0, $subtotal - $discount + $tax);

        return [
            'items'       => $items,
            'subtotal'    => (int)$subtotal,
            'discount'    => (int)$discount,
            'tax_percent' => (float)$taxPercent,
            'tax'         => (int)$tax,
            'total'       => (int)$total,
            'currency'    => $currency,
        ];
    }

    /**
     * ساخت یک «خط پکیج» بر اساس تعداد واحدها، یا در صورت نبود نرخ واحدی → قیمت ثابت جدول packages.
     */
    private function buildPackageLine(string $packageSlug, string $period, ?int $buildingId = null, ?int $unitsOverride = null): array
    {
        $perUnitMap = $this->cfg('per_unit_monthly', $this->defaultPerUnitMonthly);
        $multiplier = (float)($this->cfg("period_multipliers.$period", $this->defaultPeriodMultipliers[$period] ?? 1.0));

        if (array_key_exists($packageSlug, $perUnitMap)) {
            // حالت «به‌ازای هر واحد»
            $unitCount = $this->resolveUnitCount($buildingId, $unitsOverride);
            $unitCount = max($unitCount, (int)$this->cfg('min_units', 1));

            $perUnitMonthly = (int)$perUnitMap[$packageSlug];
            $monthlyPackage = $perUnitMonthly * $unitCount;       // قیمت ماهانه پکیج برای کل ساختمان
            $payable        = (int) round($monthlyPackage * $multiplier);

            $title = $this->humanTitle($packageSlug, $period, $unitCount, $perUnitMonthly);

            return [
                'title'      => $title,
                'qty'        => 1,
                'unit_price' => $payable,
                'line_total' => $payable,
            ];
        }

        // حالت fallback: قیمت ثابت از جدول packages
        $pkg = Package::query()->where('slug', $packageSlug)->first();
        if (!$pkg) {
            throw new InvalidArgumentException("پکیج '{$packageSlug}' یافت نشد و نرخ واحدی هم برایش تنظیم نشده است.");
        }

        $unitPrice = ($period === 'yearly')
            ? (int)$pkg->price_yearly
            : (int)$pkg->price_monthly;

        // اگر سه‌ماهه باشد و قیمت ثابت دارید، می‌توانید منطق زیر را ترجیح دهید:
        if ($period === 'quarterly') {
            $unitPrice = (int) round(((int)$pkg->price_monthly) * 2.5);
        }

        $title = sprintf('اشتراک %s (%s)', $this->pretty($packageSlug), $this->prettyPeriod($period));

        return [
            'title'      => $title,
            'qty'        => 1,
            'unit_price' => $unitPrice,
            'line_total' => $unitPrice,
        ];
    }

    /**
     * شمارش تعداد واحدهای ساختمان: از ورودی → از ساختمان مدیر لاگین‌شده.
     */
    private function resolveUnitCount(?int $buildingId = null, ?int $unitsOverride = null): int
    {
        if (!is_null($unitsOverride)) {
            return max(0, (int)$unitsOverride);
        }

        /** @var Building|null $building */
        $building = null;

        if (!is_null($buildingId)) {
            $building = Building::query()->find($buildingId);
        }

        if (!$building) {
            $user = Auth::user();
            if ($user && method_exists($user, 'buildingManager') && $user->buildingManager) {
                // در پروژهٔ شما رابطه‌ی مدیر → ساختمان غالباً 'building' است
                $building = $user->buildingManager->building ?? null;
            }
        }

        if ($building && method_exists($building, 'units')) {
            // اگر لازم است، می‌توانید واحدهای غیر فعال را فیلتر کنید: ->where('is_active',1)
            return (int) $building->units()->count();
        }

        return 0;
    }

    // ===== کمکی‌های نمایش و کانفیگ =====

    private function humanTitle(string $packageSlug, string $period, int $unitCount, int $perUnitMonthly): string
    {
        // مثال: «اشتراک Basic (ماهانه) × 48 واحد — هر واحد 99,000 ریال»
        return sprintf(
            'اشتراک %s (%s) × %d واحد — هر واحد %s ریال',
            $this->pretty($packageSlug),
            $this->prettyPeriod($period),
            $unitCount,
            number_format($perUnitMonthly)
        );
    }

    private function pretty(string $slug): string
    {
        $t = str_replace('-', ' ', $slug);
        return mb_convert_case($t, MB_CASE_TITLE, "UTF-8"); // Title Case ساده
    }

    private function prettyPeriod(string $period): string
    {
        return match ($period) {
            'monthly'   => 'ماهانه',
            'quarterly' => 'سه‌ماهه',
            'yearly'    => 'سالانه',
            default     => $period,
        };
    }

    /**
     * خواندن کانفیگ با پیش‌فرض‌های داخلی این سرویس.
     * اگر config/unit_pricing.* موجود باشد استفاده می‌شود؛ وگرنه از پیش‌فرض‌های همین کلاس.
     */
    private function cfg(string $key, mixed $default = null): mixed
    {
        // ساخت نگاشت config → کلیدهای داخلی
        $map = [
            'per_unit_monthly'          => 'unit_pricing.per_unit_monthly',
            'period_multipliers'        => 'unit_pricing.period_multipliers',
            'currency'                  => 'unit_pricing.currency',
            'min_units'                 => 'unit_pricing.min_units',
        ];

        // اگر کلید دقیقِ period multipliers مانند 'period_multipliers.monthly' خواسته شد:
        if (str_starts_with($key, 'period_multipliers.')) {
            $cfg = Config::get($map['period_multipliers'], $this->defaultPeriodMultipliers);
            $sub = substr($key, strlen('period_multipliers.') );
            return $cfg[$sub] ?? $default;
        }

        // کلیدهای سطح اول
        if (isset($map[$key])) {
            $fallback = match ($key) {
                'per_unit_monthly'   => $this->defaultPerUnitMonthly,
                'period_multipliers' => $this->defaultPeriodMultipliers,
                'currency'           => $this->defaultCurrency,
                'min_units'          => 1,
                default              => $default,
            };
            return Config::get($map[$key], $fallback);
        }

        return $default;
    }
// پیش‌نمایش بدون ذخیره: JSON یا HTML (با پارامتر ?format=html)
    public function preview(Request $request, ProformaInvoiceService $service)
    {
        $calc = $service->preview($request->all());

        if ($request->query('format') === 'html') {
            $pi = [
                'id'          => null,
                'created_at'  => now(),
                'period'      => $request->input('period', 'monthly'),
                'currency'    => $calc['currency'],
                'subtotal'    => $calc['subtotal'],
                'discount'    => $calc['discount'],
                'tax_percent' => $calc['tax_percent'],
                'tax'         => $calc['tax'],
                'total'       => $calc['total'],
                'meta'        => $request->input('meta', []),
            ];
            $items = $calc['items'];
            $business = ['name' => optional(auth()->user()?->business)->name ?? '—'];
            return response()->view('proforma.invoice', compact('pi','items','business'));
        }

        return response()->json(['success'=>true, 'data'=>$calc]);
    }

    // HTML پیش‌فاکتور ذخیره‌شده
    public function showHtml(string $id)
    {
        /** @var ProformaInvoice $pi */
        $pi = ProformaInvoice::with('items')->findOrFail($id);

        $payload = [
            'id'          => $pi->id,
            'created_at'  => $pi->created_at,
            'period'      => $pi->period,
            'currency'    => $pi->currency ?? 'IRR',
            'subtotal'    => $pi->subtotal,
            'discount'    => $pi->discount,
            'tax_percent' => $pi->tax_percent,
            'tax'         => $pi->tax,
            'total'       => $pi->total,
            'meta'        => $pi->meta,
        ];
        $items = $pi->items->map(fn($it)=>[
            'title'=>$it->title, 'qty'=>$it->qty, 'unit_price'=>$it->unit_price, 'line_total'=>$it->line_total
        ])->values()->all();

        $business = ['name' => optional(auth()->user()?->business)->name ?? '—'];

        return response()->view('proforma.invoice', [
            'pi'       => $payload,
            'items'    => $items,
            'business' => $business,
        ]);
    }

    // PDF پیش‌فاکتور ذخیره‌شده (نیازمند barryvdh/laravel-dompdf)
    public function showPdf(string $id)
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->json([
                'success'=>false,
                'message'=>'لطفاً پکیج barryvdh/laravel-dompdf نصب شود.'
            ], 422);
        }

        /** @var ProformaInvoice $pi */
        $pi = ProformaInvoice::with('items')->findOrFail($id);

        $payload = [
            'id'          => $pi->id,
            'created_at'  => $pi->created_at,
            'period'      => $pi->period,
            'currency'    => $pi->currency ?? 'IRR',
            'subtotal'    => $pi->subtotal,
            'discount'    => $pi->discount,
            'tax_percent' => $pi->tax_percent,
            'tax'         => $pi->tax,
            'total'       => $pi->total,
            'meta'        => $pi->meta,
        ];
        $items = $pi->items->map(fn($it)=>[
            'title'=>$it->title, 'qty'=>$it->qty, 'unit_price'=>$it->unit_price, 'line_total'=>$it->line_total
        ])->values()->all();

        $business = ['name' => optional(auth()->user()?->business)->name ?? '—'];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('proforma.invoice', [
            'pi'       => $payload,
            'items'    => $items,
            'business' => $business,
        ])->setPaper('a4');

        $filename = 'proforma-'.$pi->id.'.pdf';
        // دانلود مستقیم؛ اگر نمایش در مرورگر می‌خواهی → stream()
        return $pdf->download($filename);
    }
}
