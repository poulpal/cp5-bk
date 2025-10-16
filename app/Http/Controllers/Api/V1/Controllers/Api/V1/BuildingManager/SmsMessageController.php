<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Facades\CommissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\VoiceMessageResource;
use App\Mail\CustomMail;
use App\Models\BuildingUnit;
use App\Models\Commission;
use App\Models\SmsMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;

class SmsMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index', 'show', 'getBalance', 'getSmsPrice']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'show', 'getBalance', 'getSmsPrice']);
    }

    public function index()
    {
        $smsMessages = auth()->buildingManager()->building->smsMessages()->orderBy('status', 'desc')->orderBy('created_at', 'desc')->get();

        foreach ($smsMessages as $smsMessage) {
            if ($smsMessage->batch_id && $smsMessage->status == 'sending') {
                $batch = Bus::findBatch($smsMessage->batch_id);
                $smsMessage->status = __("در حال ارسال ") . (string)$batch->progress() . "%";
            }
        }
        return response()->json([
            'success' => true,
            'data' => [
                'smsMessages' => VoiceMessageResource::collection($smsMessages)
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $building = auth()->buildingManager()->building;
        $validator = Validator::make($request->all(), [
            'pattern' => ['required', 'string', 'max:210'],
            'units' => ['required', 'array'],
            'units.*' => [
                'required', 'integer',
                Rule::exists('building_units', 'id')->where(function ($query) use ($building) {
                    return $query->where('building_id', $building->id)->whereNull('deleted_at');
                })
            ],
            'resident_type' => ['required', 'string', Rule::in(['all', 'owner', 'renter', 'resident'])],
        ], [
            'units.*.exists' => 'واحد انتخاب شده معتبر نیست.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $length = ceil((Str::length($request->pattern) + 6) / 70);
        $units = $request->units;

        $count = 0;
        foreach ($units as $unit) {
            $buildingUnit = BuildingUnit::find($unit);
            if ($request->resident_type == 'all') {
                $count += count($buildingUnit->residents);
            }
            if ($request->resident_type == 'owner' && $buildingUnit->owner) {
                $count += 1;
            }
            if ($request->resident_type == 'renter' && $buildingUnit->renter) {
                $count += 1;
            }
            if ($request->resident_type == 'resident') {
                $resident = $buildingUnit->renter ?? $buildingUnit->owner;
                if ($resident) {
                    $count += 1;
                }
            }
        }

        if ($count == 0) {
            return response()->json([
                'success' => false,
                'message' => 'هیچ واحدی برای ارسال پیام متنی وجود ندارد.'
            ], 422);
        }


        if ($building->sms_balance < $count * $length) {
            return response()->json([
                'success' => false,
                'message' => 'موجودی پیامک شما کافی نیست.'
            ], 422);
        }

        $smsMessage = SmsMessage::create([
            'building_id' => auth()->buildingManager()->building->id,
            'pattern' => $request->pattern,
            'units' => $units,
            'length' => $length,
            'count' => $count,
            'resident_type' => $request->resident_type,
        ]);

        Mail::to(['cc2com.com@gmail.com', 'arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(new CustomMail(
            __("درخواست ارسال پیام متنی") . " - " . str($smsMessage->id),
            "نام ساختمان : " . $building->name . "<br>" .
                "متن پیام : <br> " . $request->pattern . "<br>"
        ));

        $building->sms_balance -= $count * $length;
        $building->save();

        return response()->json([
            'success' => true,
            'message' => 'درخواست ارسال پیام متنی با موفقیت ثبت شد.',
        ], 200);
    }

    public function destroy(SmsMessage $smsMessage)
    {
        if ($smsMessage->status != 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'امکان حذف پیام متنی ارسال شده وجود ندارد.'
            ], 422);
        }
        if ($smsMessage->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه حذف این پیام متنی را ندارید.'
            ], 403);
        }

        $building = $smsMessage->building;
        $building->sms_balance += $smsMessage->count * $smsMessage->length;
        $building->save();

        $smsMessage->delete();

        return response()->json([
            'success' => true,
            'message' => 'پیام متنی با موفقیت حذف شد.'
        ], 200);
    }

    public function addBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'count' => 'required|integer|min:3000',
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
                    'modules' => __("این امکان در این نسخه وجود ندارد"),
                ]
            ], 422);
        }

        $sms_price = CommissionHelper::getSmsPrice();
        $price = $sms_price * $request->count;

        $user = auth()->buildingManager();
        $building = $user->building;

        $payment_invoice = (new Invoice)->amount($price)->detail([
            'mobile' => $user->mobile,
            'business' => "CHARGEPAL - " . $building->name
        ]);

        $payment = Payment::config([]);

        $payment = $payment->purchase(
            $payment_invoice,
            function ($driver, $transactionId) use ($request, $price, $building, $payment_invoice) {
                $invoice = $building->invoices()->create([
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $price,
                    'building_id' => $building->id,
                    'serviceable_id' => 0,
                    'serviceable_type' => SmsMessage::class,
                    'description' => __("افزایش موجودی پیامک"),
                    'data' => [
                        'count' => $request->count
                    ]
                ]);

                $commission = $building->invoices()->create([
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => 0,
                    'building_id' => $building->id,
                    'serviceable_type' => Commission::class,
                    'description' => __("کمیسیون افزایش موجودی پیامک"),
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

    public function getBalance()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'balance' => auth()->buildingManager()->building->sms_balance
            ]
        ]);
    }

    public function getSmsPrice()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'price' => CommissionHelper::getSmsPrice()
            ]
        ]);
    }
}
