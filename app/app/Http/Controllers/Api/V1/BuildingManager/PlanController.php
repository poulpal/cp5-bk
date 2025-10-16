<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PlanResource;
use App\Models\Commission;
use App\Models\DiscountCode;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;


class PlanController extends Controller
{
    public function currentPlan()
    {
        $building = auth()->buildingManager()->building;
        $plan = $building->plan;
        $plan_expires_at = $building->plan_expires_at;
        $plan_expires_at = $plan_expires_at ? Carbon::parse($plan_expires_at)->diffInDays() : null;
        return response()->json([
            'success' => true,
            'data' => [
                'plan' => PlanResource::make($plan),
                'plan_expires_at' => Carbon::parse($building->plan_expires_at)->isPast() ? -1 * $plan_expires_at : $plan_expires_at,
            ]
        ]);
    }

    public function buyPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'required|exists:plans,slug',
            'duration' => 'required|numeric',
            'discount_code' => 'nullable|exists:discount_codes,code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (config('app.type') === 'kaino') {
            return response()->json([
                'success' => false,
                'errors' => [
                    'modules' => __("امکان خرید پکیج در این نسخه وجود ندارد"),
                ]
            ], 422);
        }

        if($request->plan == 'free' || $request->plan == 'vip'){
            return response()->json([
                'success' => false,
                'errors' => [
                    'plan' => __("امکان خرید این اشتراک وجود ندارد"),
                ]
            ], 422);
        }

        // TODO: check if 3 and 6 month plans accept discount codes

        $building = auth()->buildingManager()->building;
        $plan = Plan::where('slug', $request->plan)->firstOrFail();
        $duration = collect($plan->durations)->where('months', $request->duration)->first();
        if (!$duration) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'duration' => __("مدت زمان اشتراک انتخاب شده معتبر نیست"),
                ]
            ], 422);
        }
        if($building->current_plan && $building->current_plan->order >= $plan->order){
            return response()->json([
                'success' => false,
                'errors' => [
                    'plan' => __("امکان خرید این اشتراک وجود ندارد"),
                ]
            ], 422);
        }

        $discount_code = DiscountCode::where('code', $request->discount_code)->first();
        if($request->discount_code){
            if($duration->months != 12){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                    ]
                ], 422);
            }
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
            if ($discount_code->expires_at && $discount_code->expires_at->isPast()) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                    ]
                ], 422);
            }
        }

        $price = $duration->price;
        if ($discount_code) {
            $discount = $discount_code->type == 'fixed' ? $discount_code->value : round($price * $discount_code->value / 100);
            $price -= $discount;
        }

        $user = auth()->buildingManager();
        $building = $user->building;

        $payment_invoice = (new Invoice)->amount($price)->detail([
            'mobile' => $user->mobile,
            'business' => "CHARGEPAL - " . $building->name
        ]);

        $payment = Payment::config([]);

        $payment = $payment->purchase(
            $payment_invoice,
            function ($driver, $transactionId) use ($request, $price, $building, $payment_invoice, $discount_code, $plan, $duration) {
                $invoice = $building->invoices()->create([
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $price,
                    'discount_code_id' => $discount_code ? $discount_code->id : null,
                    'building_id' => $building->id,
                    'serviceable_id' => $plan->id,
                    'serviceable_type' => Plan::class,
                    'description' => __("خرید اشتراک ") . $plan->title . __(" ") . $duration->months . __(" ماهه"),
                    'data' => [
                        'plan' => $plan->slug,
                        'duration' => $duration->months,
                    ]
                ]);

                $commission = $building->invoices()->create([
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => 0,
                    'building_id' => $building->id,
                    'serviceable_type' => Commission::class,
                    'description' => __("خرید اشتراک ") . $plan->title . __(" ") . $duration->months . __(" ماهه"),
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
            'plan' => 'required|exists:plans,slug',
            'duration' => 'required|numeric',
            'discount_code' => 'nullable|exists:discount_codes,code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $plan = Plan::where('slug', $request->plan)->firstOrFail();
        $duration = collect($plan->durations)->where('months', $request->duration)->first();
        if (!$duration) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'duration' => __("مدت زمان اشتراک انتخاب شده معتبر نیست"),
                ]
            ], 422);
        }

        $discount_code = DiscountCode::where('code', $request->discount_code)->first();
        if($request->discount_code){
            if($duration->months != 12){
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                    ]
                ], 422);
            }
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
            if ($discount_code->expires_at && $discount_code->expires_at->isPast()) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'discount_code' => __("کد تخفیف وارد شده معتبر نیست"),
                    ]
                ], 422);
            }
        }

        $price = $duration->price;
        if ($discount_code) {
            $discount = $discount_code->type == 'fixed' ? $discount_code->value : round($price * $discount_code->value / 100);
            $price -= $discount;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'original_price' => $duration->price,
                'price' => $price,
            ]
        ]);
    }
}
