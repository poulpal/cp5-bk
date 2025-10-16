<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Facades\CommissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\BuildingResource;
use App\Http\Resources\Public\ReservableResource;
use App\Models\Building;
use App\Models\Commission;
use App\Models\Reservable;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;

class ReserveController extends Controller
{
    public function index(Building $building)
    {
        $reservables = $building->reservables()
            ->where('is_active', true)
            ->where('is_public', true)
            ->with('active_reservations')
            ->get();
        $building = $building;
        return response()->json([
            'success' => true,
            'data' => [
                'building' => BuildingResource::make($building),
                'reservables' => ReservableResource::collection($reservables),
            ]
        ]);
    }

    public function reserve(Reservable $reservable, Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'mobile' => 'required|numeric|digits:11',
            'date' => 'required|date',
            'start_time' => 'required|integer|between:0,23',
            'end_time' => 'required|integer|between:0,23',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('mobile', $request->mobile)->first();
        if (!$user) {
            $user = User::create([
                'mobile' => $request->mobile,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
            ]);
        }

        // check if already reserved
        $reserved = $reservable->reservations()
            ->where('start_time', '>=', Carbon::parse($request->date)->startOfDay()->addHours($request->start_time))
            ->where('end_time', '<=', Carbon::parse($request->date)->startOfDay()->addHours($request->end_time))
            ->first();

        if ($reserved) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'reserved' => ['این بازه زمانی قبلا رزرو شده است.']
                ]
            ], 422);
        }

        $reservation = $reservable->reservations()->create([
            'user_id' => $user->id,
            'start_time' => Carbon::parse($request->date)->startOfDay()->addHours($request->start_time),
            'end_time' => Carbon::parse($request->date)->startOfDay()->addHours($request->end_time),
            'cost' => $reservable->cost_per_hour * ($request->end_time - $request->start_time),
            'status' => 'pending',
        ]);

        $amount = $reservable->cost_per_hour * ($request->end_time - $request->start_time);

        if (config('app.type') === 'kaino') {
            $payment_invoice = (new Invoice)->amount($amount)->detail([
                'mobile' => $user->mobile,
                'account' => $reservable->building,
            ]);

            $payment = Payment::purchase(
                $payment_invoice,
                function ($driver, $transactionId) use ($reservation, $reservable, $request, $payment_invoice, $user, $amount) {
                    $invoice = $user->invoices()->create([
                        'user_id' => $user->id,
                        'payment_id' => $transactionId,
                        'payment_method' => get_class($driver),
                        'amount' => $amount,
                        'building_id' => $reservable->building->id,
                        'serviceable_id' => $reservation->id,
                        'serviceable_type' => Reservation::class,
                        'description' => __("رزرو ") . $reservable->title . '(' . $request->first_name . __(" ") . $request->last_name . ' - ' . $user->mobile .  ')',
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


        $commission_amount = CommissionHelper::calculateMaxCommission($reservable->building);
        $payment_invoice = (new Invoice)->amount($amount + $commission_amount)->detail([
            'mobile' => $user->mobile,
            'business' => "CHARGEPAL - " . $reservable->building->name
        ]);

        if ($reservable->building->terminal_id && $reservable->building->terminal_id != '') {
            switch ($reservable->building->terminal_id) {
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
            function ($driver, $transactionId) use ($reservation, $reservable, $request, $commission_amount, $payment_invoice, $user, $amount) {
                $invoice = $user->invoices()->create([
                    'user_id' => $user->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $amount,
                    'building_id' => $reservable->building->id,
                    'serviceable_id' => $reservation->id,
                    'serviceable_type' => Reservation::class,
                    'description' => __("رزرو ") . $reservable->title . '(' . $request->first_name . __(" ") . $request->last_name . ' - ' . $user->mobile .  ')',
                ]);

                if ($reservable->building->terminal_id && $reservable->building->terminal_id != '') {
                    $invoice->data = [
                        'terminal_id' => $reservable->building->terminal_id,
                    ];
                    $invoice->saveQuietly();
                }

                $commission = $user->invoices()->create([
                    'user_id' => $user->id,
                    'payment_id' => get_class($driver) == "App\Helpers\SEP" ? $payment_invoice->getUUid() : $transactionId,
                    'payment_method' => get_class($driver) == "App\Helpers\Sepehr" ? "Shetabit\Multipay\Drivers\Sepehr\Sepehr" : get_class($driver),
                    'amount' => $commission_amount,
                    'building_id' => $reservable->building->id,
                    'serviceable_type' => Commission::class,
                    'description' => __("کمیسیون رزرو ") . $reservable->title . '(' . $request->first_name . __(" ") . $request->last_name . ' - ' . $user->mobile .  ')',
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
