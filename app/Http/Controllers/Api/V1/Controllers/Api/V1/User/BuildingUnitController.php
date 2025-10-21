<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Facades\CommissionHelper;
use App\Helpers\Inopay;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\BuildingUnitResource;
use App\Http\Resources\User\InvoiceResource;
use App\Mail\CustomMail;
use App\Models\BuildingUnit;
use App\Models\Commission;
use App\Models\PendingDeposit;
use App\Models\Toll;
use App\Notifications\BuildingManager\UserPaidCharge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;

class BuildingUnitController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $buildingUnits = $user->building_units;
        return response()->json([
            'success' => true,
            'data' => [
                'units' => BuildingUnitResource::collection($buildingUnits),
            ]
        ]);
    }

    public function show(BuildingUnit $unit)
    {
        $user = auth()->user();
        if ($user->building_units->contains($unit)) {
            $unit->pivot = $user->building_units()->where('building_unit_id', $unit->id)->first()->pivot;
            $unit->canPayCustomAmount = $unit->building->options->custom_payment;
            $unit->canPayManual = $unit->building->options->manual_payment;
            $unit->separateResidentAndOwnerInvoices = $unit->building->options->separate_resident_and_owner_invoices;

            $ownership = $unit->residents()->where('user_id', $user->id)->first()->pivot->ownership;

            $resident_type = $ownership;
            if ($ownership == 'owner' && $unit->residents()->count() == 1) {
                $resident_type = 'resident';
            }
            if ($ownership == 'renter') {
                $resident_type = 'resident';
            }
            $unit->resident_type = $resident_type;

            $unit->resident_debt = $unit->debt('resident');
            $unit->owner_debt = $unit->debt('owner');

            $discount = $unit->userDiscount($user);
            $unit->discount = $discount;

            $unit->charge_debt = $unit->userDebt($user);
            return response()->json([
                'success' => true,
                'data' => [
                    'unit' => new BuildingUnitResource($unit),
                    'options' => [
                        'canPayCustomAmount' => $unit->building->options->custom_payment,
                        'canPayManual' => $unit->building->options->manual_payment,
                        'currency' => $unit->building->options->currency,
                        'showStocks' => $unit->building->options->show_stocks_to_units,
                        'showCosts' => $unit->building->options->show_costs_to_units,
                        'showBalances' => $unit->building->options->show_balances_to_units,
                    ]
                ]
            ]);
        } else {
            return abort(404);
        }
    }

    public function pay(BuildingUnit $unit, Request $request)
    {
        if (!$unit->building->options->separate_resident_and_owner_invoices) {
            $request->merge(['resident_type' => 'resident']);
        }

        $user = auth()->user();
        if (!$user->building_units->contains($unit)) {
            return abort(404);
        }
        $validator = Validator::make($request->all(), [
            'amount' => 'required|decimal:0,1|min:1000',
            'resident_type' => 'required|string|in:resident,owner',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $discount = $unit->userDiscount($user);
        $unit->charge_debt = $unit->userDebt($user) - $discount;

        if ($unit->building->id == 17 || $unit->building->name_en == 'afra' || $unit->building->id == 200) {
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

        $unit->canPayCustomAmount = $unit->building->options->custom_payment;
        if (!$unit->canPayCustomAmount && $request->amount != $unit->debt($request->resident_type)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'amount' => 'مبلغ وارد شده برای پرداخت باید برابر با بدهی شارژ واحد باشد.'
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
                function ($driver, $transactionId) use ($unit, $request, $payment_invoice, $discount) {
                    $invoice = auth()->user()->invoices()->create([
                        'user_id' => auth()->user()->id,
                        'payment_id' => $transactionId,
                        'payment_method' => get_class($driver),
                        'amount' => $request->amount,
                        'building_id' => $unit->building->id,
                        'serviceable_id' => $unit->id,
                        'serviceable_type' => BuildingUnit::class,
                        'description' => __("پرداخت آنلاین بدهی"),
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
            'mobile' => auth()->user()->mobile,
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
            function ($driver, $transactionId) use ($unit, $request, $commission_amount, $payment_invoice, $discount) {
                $invoice = auth()->user()->invoices()->create([
                    'user_id' => auth()->user()->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $request->amount,
                    'building_id' => $unit->building->id,
                    'serviceable_id' => $unit->id,
                    'serviceable_type' => BuildingUnit::class,
                    'description' => __("پرداخت آنلاین بدهی"),
                    'resident_type' => $request->resident_type,
                ]);

                if ($discount > 0) {
                    $discount_invoice = auth()->user()->invoices()->create([
                        'user_id' => auth()->user()->id,
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

                $commission = auth()->user()->invoices()->create([
                    'user_id' => auth()->user()->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $commission_amount,
                    'building_id' => $unit->building->id,
                    'serviceable_type' => Commission::class,
                    'description' => 'کمیسیون پرداخت بدهی - واحد ' . $unit->unit_number,
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


    public function payWithWallet(BuildingUnit $unit, Request $request)
    {
        $user = auth()->user();
        if (!$user->building_units->contains($unit)) {
            return abort(404);
        }
        $validator = Validator::make($request->all(), [
            'amount' => 'required|decimal:0,1|min:1',
            'resident_type' => 'required|string|in:resident,owner',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $discount = $unit->userDiscount($user);
        $unit->charge_debt = $unit->userDebt($user) - $discount;

        $canPayCustomAmount = $unit->building->options->custom_payment;
        if (!$canPayCustomAmount && $request->amount != $unit->debt($request->resident_type)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'amount' => 'مبلغ وارد شده برای پرداخت باید برابر با بدهی شارژ واحد باشد.'
                ],
            ], 422);
        }

        if ($request->amount > $unit->charge_debt) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'amount' => 'مبلغ وارد شده برای پرداخت نمی تواند از بدهی شارژ واحد بیشتر باشد.'
                ],
            ], 422);
        }

        if (config('app.type') === 'kaino') {
            $inopay = new Inopay();
            $balance = $inopay->getBalance($user);
        } else {
            $balance = $user->balance;
        }

        if ($request->amount > $balance) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'amount' => 'مبلغ وارد شده برای پرداخت نمی تواند از موجودی کیف پول شما بیشتر باشد.'
                ],
            ], 422);
        }

        DB::transaction(function () use ($unit, $request, $discount) {

            $invoice = auth()->user()->invoices()->create([
                'user_id' => auth()->user()->id,
                'payment_method' => 'wallet',
                'amount' => $request->amount,
                'building_id' => $unit->building->id,
                'serviceable_id' => $unit->id,
                'serviceable_type' => BuildingUnit::class,
                'description' => __("پرداخت آنلاین بدهی"),
                'status' => 'paid',
                'resident_type' => $request->resident_type,
            ]);

            if (config('app.type') === 'kaino') {
                $inopay = new Inopay();
                $data = $inopay->transfer($request->amount, auth()->user(), $unit->building, 'پرداخت بدهی - واحد ' . $unit->unit_number);
                $invoice->data = $data;
                $invoice->saveQuietly();
            }

            if ($discount > 0) {
                $discount_invoice = auth()->user()->invoices()->create([
                    'user_id' => auth()->user()->id,
                    'payment_id' => $invoice->id,
                    'payment_method' => 'cash',
                    'amount' => $discount,
                    'building_id' => $unit->building->id,
                    'serviceable_id' => $unit->id,
                    'serviceable_type' => BuildingUnit::class,
                    'description' => __("تخفیف خوشحسابی پرداخت شارژ"),
                    'status' => 'paid',
                    'resident_type' => $request->resident_type,
                ]);
            }

            $unit->invoices()->where('early_discount_until', '>=', $invoice->created_at)->update([
                'early_discount_amount' => 0,
            ]);

            $unit->charge_debt = round($unit->charge_debt - $request->amount - $discount, 1);
            $unit->save();

            $invoice->user->balance = round($invoice->user->balance - $request->amount, 1);
            $invoice->user->save();

            $unit->building->balance = round($unit->building->balance + $request->amount, 1);
            $unit->building->save();

            if ($unit->building->options->send_building_manager_payment_notification) {
                foreach ($unit->building->mainBuildingManagers as $manager) {
                    $manager->notify(new UserPaidCharge($invoice->amount, $invoice->user->full_name, $invoice->user->mobile, $invoice->id, $unit->unit_number));
                }
            }

            Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com', 'Shaqayeq.shafiee1370@yahoo.com'])->send(
                new CustomMail(
                    'پرداخت شارژ از کیف پول - ساختمان : ' . $unit->building->name . " - " . $invoice->id ?? "",
                    "نام ساختمان : " . $unit->building->name . "<br>" .
                        "واحد : " . $unit->unit_number . " - " . $invoice->user->mobile . "<br>" .
                        "مبلغ : " . number_format($invoice->amount * 10) . __(" ریال") . "<br>" .
                        "شماره ارجاع : " . ($invoice->id ?? "")
                )
            );

            $pending_deposit = new PendingDeposit();
            $pending_deposit->invoice()->associate($invoice);
            $pending_deposit->building()->associate($unit->building);
            $pending_deposit->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'پرداخت بدهی با موفقیت انجام شد.',
        ]);
    }

    public function addInvoice(BuildingUnit $unit, Request $request)
    {
        $canAddInvoice = $unit->building->options->manual_payment;

        if (!$canAddInvoice) {
            return response()->json([
                'success' => false,
                'message' => 'قابلیت افزودن فاکتور فعال نیست.',
            ], 422);
        }

        $user = auth()->user();
        if (!$user->building_units->contains($unit)) {
            return abort(404);
        }
        $validator = Validator::make($request->all(), [
            'amount' => 'required|decimal:0,1|min:1000',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg|max:2048'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $invoice = auth()->user()->invoices()->create([
            'user_id' => auth()->user()->id,
            'building_id' => $unit->building->id,
            'amount' => $request->amount,
            'status' => 'paid',
            'serviceable_id' => $unit->id,
            'serviceable_type' => BuildingUnit::class,
            'description' => $request->description,
            'payment_method' => 'cash',
            'is_verified' => false,
        ]);

        if ($request->hasFile('attachment')) {
            $url = $request->file('attachment')->store('public/invoices');
            $url = Storage::url($url);
            $invoice->attachments()->create([
                'file' => $url,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __("پرداخت دستی با موفقیت ثبت شد"),
            'data' => [
                'invoice' => new InvoiceResource($invoice),
            ]
        ]);
    }

    public function payToll(BuildingUnit $unit, Toll $toll, Request $request)
    {
        $user = auth()->user();
        if (!$user->building_units->contains($unit)) {
            return abort(404);
        }
        if ($unit->building->id == 17 || $unit->building->name_en == 'afra' || $unit->building->id == 200) {
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

        if (config('app.type') === 'kaino') {
            $payment_invoice = (new Invoice)->amount($toll->amount)->detail([
                'mobile' => auth()->user()->mobile,
                'account' => $unit->building,
            ]);

            $payment = Payment::purchase(
                $payment_invoice,
                function ($driver, $transactionId) use ($unit, $request, $payment_invoice, $toll) {
                    $invoice = auth()->user()->invoices()->create([
                        'user_id' => auth()->user()->id,
                        'payment_id' => $transactionId,
                        'payment_method' => get_class($driver),
                        'amount' => $toll->amount,
                        'building_id' => $unit->building->id,
                        'serviceable_id' => $toll->id,
                        'serviceable_type' => Toll::class,
                        'description' => 'پرداخت آنلاین - ' . $toll->description,
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
            'mobile' => auth()->user()->mobile,
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
            function ($driver, $transactionId) use ($unit, $request, $commission_amount, $payment_invoice, $toll) {
                $invoice = auth()->user()->invoices()->create([
                    'user_id' => auth()->user()->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $toll->amount,
                    'building_id' => $unit->building->id,
                    'serviceable_id' => $toll->id,
                    'serviceable_type' => Toll::class,
                    'description' => 'پرداخت آنلاین - ' . $toll->description,
                ]);

                if ($unit->building->terminal_id && $unit->building->terminal_id != '') {
                    $invoice->data = [
                        'terminal_id' => $unit->building->terminal_id,
                    ];
                    $invoice->saveQuietly();
                }

                $commission = auth()->user()->invoices()->create([
                    'user_id' => auth()->user()->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $commission_amount,
                    'serviceable_id' => $toll->id,
                    'serviceable_type' => Commission::class,
                    'description' => 'پرداخت آنلاین - ' . $toll->description . ' - کمیسیون',
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
