<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Facades\CommissionHelper;
use App\Http\Controllers\Controller;
use App\Models\BuildingUnit;
use App\Models\Commission;
use App\Models\Toll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;

class PayChargeController extends Controller
{
    public function getCharge(Request $request)
    {
        $unit = BuildingUnit::where('token', $request->token)->firstOrFail();
        $unit->canPayCustomAmount = $unit->building->options->custom_payment;

        // $discount = $unit->invoices()->where('early_discount_until', '>=', Carbon::now())->sum('early_discount_amount');

        if ($unit->building->name_en == 'hshcomplex') {
            return response()->json([
                'success' => false,
                'message' => __("نقص مدارک"),
            ], 500);
        }

        $resident_debt = $unit->debt('resident');
        $owner_debt = $unit->debt('owner');

        return response()->json([
            'success' => true,
            'data' => [
                'unit_number' => $unit->unit_number,
                'charge_fee' => round($unit->charge_fee, 1),
                'charge_debt' => round($unit->charge_debt, 1),
                'resident_debt' => round($resident_debt, 1),
                'owner_debt' => round($owner_debt, 1),
                'discount' => 0,
                'canPayCustomAmount' => $unit->canPayCustomAmount,
                'separateResidentAndOwnerInvoices' => $unit->building->options->separate_resident_and_owner_invoices,
                'building' => [
                    'name' => $unit->building->name,
                    'address' => $unit->building->address,
                    'district' => $unit->building->district,
                    'image' => $unit->building->image ? asset($unit->building->image) : asset('images/building.png')
                ]
            ],
        ]);
    }

    public function payCharge(Request $request)
    {
        $unit = BuildingUnit::where('token', $request->token)->firstOrFail();
        $unit->canPayCustomAmount = $unit->building->options->custom_payment;
        $discount = $unit->invoices()->where('early_discount_until', '>=', Carbon::now())->sum('early_discount_amount');
        $unit->charge_debt -= $discount;

        if ($unit->building->name_en == 'hshcomplex' ) {
            return response()->json([
                'success' => false,
                'message' => __("نقص مدارک"),
            ], 500);
        }

        if (!$unit->building->options->separate_resident_and_owner_invoices) {
            $request->merge(['resident_type' => 'resident']);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|decimal:0,1|min:1000',
            'mobile' => 'required|numeric|digits:11',
            'resident_type' => 'required|string|in:resident,owner',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($unit->building->id == 17 || $unit->building->name_en == 'afra') {
            return response()->json([
                'success' => false,
                'errors' => [
                    'amount' => __("قابلیت پرداخت آنلاین فعال نیست"),
                ]
            ], 422);
        }

        if ($unit->building->is_verified == false) {
            return response()->json([
                'success' => false,
                'message' => 'قابلیت پرداخت آنلاین فعال نیست.',
            ], 422);
        }

        if (!$unit->canPayCustomAmount && $request->amount != $unit->debt($request->resident_type)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'amount' => 'مبلغ وارد شده برای پرداخت باید برابر با بدهی واحد باشد.'
                ],
            ], 422);
        }

        $user = $unit->residents()->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'mobile' => 'شماره موبایل وارد شده در سیستم ثبت نشده است.',
                ],
            ], 422);
        }

        if (config('app.type') === 'kaino') {
            $payment_invoice = (new Invoice)->amount($request->amount)->detail([
                'mobile' => auth()->user()->mobile,
                'account' => $unit->building,
            ]);

            $payment = Payment::purchase(
                $payment_invoice,
                function ($driver, $transactionId) use ($request, $user, $unit, $payment_invoice) {
                    $invoice = $user->invoices()->create([
                        'user_id' => $user->id,
                        'payment_id' => $transactionId,
                        'payment_method' => get_class($driver),
                        'amount' => $request->amount,
                        'building_id' => $unit->building->id,
                        'serviceable_id' => $unit->id,
                        'serviceable_type' => BuildingUnit::class,
                        'description' => 'پرداخت بدهی - واحد ' . $unit->unit_number . __("با شماره موبایل ") . $request->mobile,
                        'resident_type' => $request->resident_type,
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

        $commission_amount = CommissionHelper::calculateMaxCommission($unit->building);
        $payment_invoice = (new Invoice)->amount($request->amount + $commission_amount)->detail([
            'mobile' => $user->mobile,
            'business' => "CHARGEPAL - " . $unit->building->name
        ]);

        if ($unit->building->terminal_id && $unit->building->terminal_id != '') {
            switch ($unit->building->terminal_id) {
                case '77041334':
                    $payment = Payment::config([
                        'username' => 'ERP77049230',
                        'password' => 'VcbiKh!rr2',
                        'merchantId' => '77049230',
                        'terminalCode' => '77041334',
                    ]);
                    break;
                default:
                    $payment = Payment::config([]);
                    break;
            }
        } else {
            $payment = Payment::config([]);
        }

        $payment = $payment->purchase(
            $payment_invoice,
            function ($driver, $transactionId) use ($unit, $request, $commission_amount, $payment_invoice, $user, $discount) {
                $invoice = $user->invoices()->create([
                    'user_id' => $user->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $request->amount,
                    'building_id' => $unit->building->id,
                    'serviceable_id' => $unit->id,
                    'serviceable_type' => BuildingUnit::class,
                    'description' => 'پرداخت بدهی - واحد ' . $unit->unit_number . __("با شماره موبایل ") . $request->mobile,
                    'resident_type' => $request->resident_type,
                ]);

                if ($discount > 0) {
                    $discount_invoice = $user->invoices()->create([
                        'user_id' => $user->id,
                        'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                        'payment_method' => 'cash',
                        'amount' => $discount,
                        'building_id' => $unit->building->id,
                        'serviceable_id' => $unit->id,
                        'serviceable_type' => BuildingUnit::class,
                        'description' => __("تخفیف خوشحسابی پرداخت شارژ"),
                        'resident_type' => $request->resident_type,
                    ]);
                }

                if ($unit->building->terminal_id && $unit->building->terminal_id != '') {
                    $invoice->data = [
                        'terminal_id' => $unit->building->terminal_id,
                    ];
                    $invoice->saveQuietly();
                }

                $commission = $user->invoices()->create([
                    'user_id' => $user->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $commission_amount,
                    'building_id' => $unit->building->id,
                    'serviceable_type' => Commission::class,
                    'description' => 'کمیسیون پرداخت بدهی - واحد ' . $unit->unit_number . __("با شماره موبایل ") . $request->mobile,
                    'resident_type' => $request->resident_type,
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

    public function getToll(Request $request)
    {
        $toll = Toll::where('token', $request->token)
            ->with('unit', 'unit.building')
            ->firstOrFail();

        if ($toll->status == 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'این لینک قبلا پرداخت شده است.',
            ], 422);
        }

        $unit = $toll->unit;

        return response()->json([
            'success' => true,
            'data' => [
                'unit_number' => $unit->unit_number,
                'canPayCustomAmount' => false,
                'toll' => [
                    'amount' => round($toll->amount, 1),
                    'description' => $toll->description,
                    'date' => $toll->created_at->format('Y-m-d H:i:s'),
                    'status' => $toll->status,
                ],
                'building' => [
                    'name' => $unit->building->name,
                    'address' => $unit->building->address,
                    'district' => $unit->building->district,
                    'image' => $unit->building->image ? asset($unit->building->image) : asset('images/building.png')
                ]
            ],
        ]);
    }

    public function payToll(Request $request)
    {
        $toll = Toll::where('token', $request->token)
            ->with('unit', 'unit.building')
            ->firstOrFail();

        if ($toll->status == 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'این لینک قبلا پرداخت شده است.',
            ], 422);
        }

        $unit = $toll->unit;

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric|digits:11',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($unit->building->id == 17 || $unit->building->name_en == 'afra') {
            return response()->json([
                'success' => false,
                'errors' => [
                    'amount' => __("قابلیت پرداخت آنلاین فعال نیست"),
                ]
            ], 422);
        }

        if ($unit->building->is_verified == false) {
            return response()->json([
                'success' => false,
                'message' => 'قابلیت پرداخت آنلاین فعال نیست.',
            ], 422);
        }

        $user = User::where('mobile', $request->mobile)->firstOrNew([
            'mobile' => $request->mobile,
        ]);

        if (config('app.type') === 'kaino') {
            $payment_invoice = (new Invoice)->amount($request->amount)->detail([
                'mobile' => auth()->user()->mobile,
                'account' => $unit->building,
            ]);

            $payment = Payment::purchase(
                $payment_invoice,
                function ($driver, $transactionId) use ($unit, $request, $payment_invoice, $user, $toll) {
                    $invoice = $user->invoices()->create([
                        'user_id' => $user->id,
                        'payment_id' => $transactionId,
                        'payment_method' => get_class($driver),
                        'amount' => $toll->amount,
                        'building_id' => $unit->building->id,
                        'serviceable_id' => $toll->id,
                        'serviceable_type' => Toll::class,
                        'description' => __("پرداخت ") . $toll->description . __(" با شماره موبایل ") . $request->mobile,
                        'resident_type' => $toll->resident_type,
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

        $commission_amount = CommissionHelper::calculateMaxCommission($unit->building);
        $payment_invoice = (new Invoice)->amount($toll->amount + $commission_amount)->detail([
            'mobile' => $user->mobile,
            'business' => "CHARGEPAL - " . $unit->building->name
        ]);

        if ($unit->building->terminal_id && $unit->building->terminal_id != '') {
            switch ($unit->building->terminal_id) {
                case '77041334':
                    $payment = Payment::config([
                        'username' => 'ERP77049230',
                        'password' => 'VcbiKh!rr2',
                        'merchantId' => '77049230',
                        'terminalCode' => '77041334',
                    ]);
                    break;
                default:
                    $payment = Payment::config([]);
                    break;
            }
        } else {
            $payment = Payment::config([]);
        }

        $payment = $payment->purchase(
            $payment_invoice,
            function ($driver, $transactionId) use ($unit, $request, $commission_amount, $payment_invoice, $user, $toll) {
                $invoice = $user->invoices()->create([
                    'user_id' => $user->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $toll->amount,
                    'building_id' => $unit->building->id,
                    'serviceable_id' => $toll->id,
                    'serviceable_type' => Toll::class,
                    'description' => __("پرداخت ") . $toll->description . __(" با شماره موبایل ") . $request->mobile,
                    'resident_type' => $toll->resident_type,
                ]);

                if ($unit->building->terminal_id && $unit->building->terminal_id != '') {
                    $invoice->data = [
                        'terminal_id' => $unit->building->terminal_id,
                    ];
                    $invoice->saveQuietly();
                }

                $commission = $user->invoices()->create([
                    'user_id' => $user->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $commission_amount,
                    'building_id' => $unit->building->id,
                    'serviceable_type' => Commission::class,
                    'description' => __("کمیسیون پرداخت ") . $toll->description . ' - واحد ' . $unit->unit_number . __("با شماره موبایل ") . $request->mobile,
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
}
