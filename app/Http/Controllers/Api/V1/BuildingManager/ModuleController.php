<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\ModuleResource;
use App\Models\Commission;
use App\Models\DiscountCode;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:100,15')->only(['checkDiscountCode', 'buy']);
    }

    public function index()
    {
        $building = auth()->buildingManager()->building;
        $activeModules = $building->modules;

        // Append to active modules
        $groups = Module::where('order', '>', 0)
            ->orderBy('order')
            ->get()
            ->groupBy('type');

        $arr = [];

        foreach ($groups as $key => $group) {
            $arr[] = [
                'type' => ($key == 'base') ? __("اصلی") : (($key == 'extra') ? __("افزودنی ها") : (($key == 'accounting') ? __("حسابداری") : __("نامشخص"))),
                'modules' => ModuleResource::collection($group),
            ];
        }

        $discount_code = null;

        return response()->json([
            'success' => true,
            'data' => [
                'activeModules' => ModuleResource::collection($activeModules),
                'modules' => $arr,
                'discount_code' => $discount_code,
            ]
        ]);
    }

    public function buy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'modules' => 'required|array',
            'modules.*' => 'required|exists:modules,slug',
            'discount_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $building = auth()->buildingManager()->building;
        $activeModules = $building->modules;

        $modules = Module::whereIn('slug', $request->modules)->get();
        
        // محاسبه قیمت اولیه
        $price = $modules->sum('price');
       
        // بررسی تداخل و extra_days
        foreach ($modules as $module) {
            if ($activeModules->contains($module)) {
                if (Carbon::parse($activeModules->where('slug', $module->slug)->first()->pivot->ends_at)->diffInDays(now()) >= 14) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'modules' => __("امکان تمدید پکیج ") . $module->title . __(" وجود ندارد"),
                        ]
                    ], 422);
                }
                $module->extra_days = Carbon::parse($activeModules->where('slug', $module->slug)->first()->pivot->ends_at)->diffInDays(now());
            }
        }

        // فقط یک پکیج اصلی
        $selected_base_modules = $modules->where('type', 'base');
        if ($selected_base_modules->count() > 1) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'modules' => 'تنها می‌توانید یک پکیج اصلی انتخاب کنید',
                ]
            ], 422);
        }
        
        $selected_base_module = $selected_base_modules->first();
        if ($selected_base_module) {
            $selected_base_module_limit = $selected_base_module->features->limit ?? null;
            if ($building->units()->count() > $selected_base_module_limit) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'modules' => __("تعداد واحدهای شما بیشتر از حد مجاز پکیج ") . $selected_base_module->title . __(" است"),
                    ]
                ], 422);
            }
        }

        // بررسی کد تخفیف
        $discount_code = DiscountCode::where('code', $request->discount_code)->first();
        if ($request->discount_code) {
            if (!$discount_code || !$discount_code->is_active) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                    ]
                ], 422);
            }
            if ($discount_code->max_usage && $discount_code->usage >= $discount_code->max_usage) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                    ]
                ], 422);
            }
            if ($discount_code->expires_at && Carbon::parse($discount_code->expires_at)->isPast()) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                    ]
                ], 422);
            }
            if (Str::startsWith($discount_code->code, 'JqtzauKlrE-trial-day-')) {
                $trial_days = (int)Str::after($discount_code->code, 'JqtzauKlrE-trial-day-');
                if ($building->created_at->diffInDays(now()) + 1 != $trial_days) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                        ]
                    ], 422);
                }
            }
        }

        // اعمال تخفیف
        if ($discount_code) {
            $discount = $discount_code->type == 'fixed' ? $discount_code->value : round($price * $discount_code->value / 100);
            $price -= $discount;
        }

        // محاسبه VAT روی قیمت نهایی (بعد از تخفیف)
        $vat = round($price * 0.1);
        $price += $vat;

        // اگر پکیج رایگان بود (قیمت 0 یا با تخفیف 100%)
        if ($price <= 0) {
            // فعال‌سازی ماژول‌های انتخابی
            foreach ($modules as $module) {
                if ($activeModules->contains($module)) {
                    $module->extra_days = Carbon::parse($activeModules->where('slug', $module->slug)->first()->pivot->ends_at)->diffInDays(now());
                    $activeModules->where('slug', $module->slug)->first()->pivot->update(['ends_at' => now()]);
                }
                if ($module->type == 'base' && $activeModules->where('type', 'base')->count() > 0) {
                    foreach ($activeModules->where('type', 'base') as $activeModule) {
                        $activeModule->pivot->update(['ends_at' => now()]);
                    }
                }
                $building->modules()->attach($module->slug, [
                    'starts_at' => now(),
                    'ends_at' => now()->addYears(1)->addDays($module->extra_days ?? 0),
                    'price' => $module->price
                ]);
            }
            
            // اگر پکیج اصلی خریداری شد، افزودنی‌های رایگان را هم فعال کن
            if ($selected_base_module) {
                $freeAddons = Module::whereIn('slug', ['accounting-basic', 'accounting-general', 'stocks', 'reserve-and-poll', 'fine-and-reward'])
                    ->get();
                foreach ($freeAddons as $addon) {
                    if (!$activeModules->contains($addon)) {
                        $building->modules()->attach($addon->slug, [
                            'starts_at' => now(),
                            'ends_at' => now()->addYears(1),
                            'price' => 0
                        ]);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'پکیج‌های انتخابی با موفقیت فعال شدند',
                'data' => [
                    'price' => $price,
                ]
            ]);
        }

        // ادامه پروسه پرداخت
        $user = auth()->buildingManager();
        $building = $user->building;

        $payment_invoice = (new Invoice)->amount($price)->detail([
            'mobile' => $user->mobile,
            'business' => "CHARGEPAL - " . $building->name
        ]);

        $payment = Payment::config([]);

        $payment = $payment->purchase(
            $payment_invoice,
            function ($driver, $transactionId) use ($request, $price, $building, $payment_invoice, $discount_code, $modules) {
                $invoice = $building->invoices()->create([
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $price,
                    'discount_code_id' => $discount_code ? $discount_code->id : null,
                    'building_id' => $building->id,
                    'serviceable_id' => null,
                    'serviceable_type' => Module::class,
                    'description' => __("خرید ماژول ") . $modules->pluck('title')->implode(', '),
                    'data' => [
                        'modules' => $modules->pluck('slug')->toArray(),
                    ]
                ]);

                $commission = $building->invoices()->create([
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => 0,
                    'building_id' => $building->id,
                    'serviceable_type' => Commission::class,
                    'description' => __("خرید ماژول ") . $modules->pluck('title')->implode(', '),
                ]);
            }
        )->pay()->toJson();

        return response()->json([
            'success' => true,
            'data' => [
                'driver' => config('payment.default'),
                'redirect' => json_decode($payment),
                'redirectUrl' => route('paymentRedirect', [
                    'method' => json_decode($payment)->method,
                    'action' => json_decode($payment)->action,
                    'inputs' => json_encode(json_decode($payment)->inputs),
                ]),
                'callback' => route('v1.callback.' . config('payment.default')),
            ]
        ]);
    }

    public function checkDiscountCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'modules' => 'nullable|array',
            'modules.*' => 'nullable|exists:modules,slug',
            'discount_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->buildingManager();
        $building = $user->building;
        
        // محاسبه قیمت اولیه
        $original_price = 0;
        if ($request->has('modules') && is_array($request->modules) && count($request->modules) > 0) {
            $modules = Module::whereIn('slug', $request->modules)->get();
            $original_price = $modules->sum('price');
        }
        
        $price = $original_price;
        $discount_amount = 0;

        $discount_code = DiscountCode::where('code', $request->discount_code)->first();
        if ($request->discount_code) {
            if (!$discount_code || !$discount_code->is_active) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                    ]
                ], 422);
            }
            if ($discount_code->max_usage && $discount_code->usage >= $discount_code->max_usage) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                    ]
                ], 422);
            }
            if ($discount_code->expires_at && Carbon::parse($discount_code->expires_at)->isPast()) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                    ]
                ], 422);
            }
            if (Str::startsWith($discount_code->code, 'JqtzauKlrE-trial-day-')) {
                $trial_days = (int)Str::after($discount_code->code, 'JqtzauKlrE-trial-day-');
                if ($building->created_at->diffInDays(now()) + 1 != $trial_days) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                        ]
                    ], 422);
                }
            }
            
            // محاسبه تخفیف
            if ($original_price > 0) {
                $discount_amount = $discount_code->type == 'fixed' 
                    ? $discount_code->value 
                    : round($price * $discount_code->value / 100);
                $price -= $discount_amount;
            }
        }

        // محاسبه VAT روی قیمت بعد از تخفیف
        $vat = $price > 0 ? round($price * 0.1) : 0;
        $final_price = $price + $vat;

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $discount_code->type ?? null,
                'value' => $discount_code->value ?? null,
                'original_price' => $original_price,
                'discount_amount' => $discount_amount,
                'price_after_discount' => $price,
                'vat' => $vat,
                'final_price' => $final_price,
            ]
        ]);
    }
}
