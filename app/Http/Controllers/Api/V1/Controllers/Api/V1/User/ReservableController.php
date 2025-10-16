<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\ReservableResource;
use App\Http\Resources\Public\ReservationResource;
use App\Models\BuildingUnit;
use App\Models\Reservable;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class ReservableController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit' => 'required|integer|exists:building_units,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $unit = BuildingUnit::findOrFail($request->unit);
        if ($unit->residents()->where('user_id', auth()->user()->id)->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => __("شما مجاز به مشاهده این رزروها نیستید"),
            ], 403);
        }

        $ownership = $unit->residents()->where('user_id', auth()->user()->id)->first()->pivot->ownership;
        $resident_type = $ownership;
        if ($ownership == 'owner' && $unit->residents()->count() == 1) {
            $resident_type = 'resident';
        }
        if ($ownership == 'renter') {
            $resident_type = 'resident';
        }

        $reservables = $unit->building->reservables()
            ->where('is_active', true)
            ->where('is_public', true)
            ->where(function ($query) use ($resident_type, $ownership) {
                $query->where('resident_type', 'all')
                    ->orWhere('resident_type', $ownership)
                    ->orWhere('resident_type', $resident_type);
            })
            ->get();

        $now = Jalalian::now();
        $startOfMonth = $now->subDays($now->getDay() - 1)->toCarbon()->startOfDay();
        $endOfMonth = $now->addMonths(1)->subDays($now->getDay())->toCarbon()->endOfDay();
        $userId = auth()->user()->id;

        foreach ($reservables as $reservable) {
            if ($reservable->monthly_hour_limit) {
                $reservations = $reservable->reservations()
                    ->where('unit_id', $unit->id)
                    ->where('start_time', '>=', $startOfMonth)
                    ->where('end_time', '<=', $endOfMonth)
                    ->get();
                $hoursUsed = 0;
                foreach ($reservations as $reservation) {
                    $hoursUsed += $reservation->end_time->diffInHours($reservation->start_time);
                }
                $reservable->remaining_hours_this_month = max(0, $reservable->monthly_hour_limit - $hoursUsed);
            } else {
                $reservable->remaining_hours_this_month = null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'reservables' => ReservableResource::collection($reservables),
            ]
        ]);
    }

    public function reserve(Request $request, Reservable $reservable)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'start_time' => 'required|integer|between:0,23',
            'end_time' => 'required|integer|between:0,23',
            'unit' => 'required|integer|exists:building_units,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->start_time >= $request->end_time) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'start_time' => ['زمان شروع باید کمتر از زمان پایان باشد.']
                ]
            ], 422);
        }

        if ($request->date > Jalalian::now()->addMonths(1)->subDays(Jalalian::now()->getDay())->toCarbon()->endOfDay()) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'date' => ['تاریخ رزرو باید در ماه جاری باشد.']
                ]
            ], 422);
        }

        $user = auth()->user();

        $unit = BuildingUnit::findOrFail($request->unit);
        if ($unit->residents()->where('user_id', auth()->user()->id)->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => __("شما مجاز به مشاهده این رزروها نیستید"),
            ], 403);
        }

        // Check monthly hour limit
        if ($reservable->monthly_hour_limit) {
            $date = Jalalian::forge($request->date);
            $startOfMonth = $date->subDays($date->getDay() - 1)->toCarbon()->startOfDay();
            $endOfMonth = $date->addMonths(1)->subDays($date->getDay())->toCarbon()->endOfDay();
            $reservations = $reservable->reservations()
                ->where('unit_id', $unit->id)
                ->where('start_time', '>=', $startOfMonth)
                ->where('end_time', '<=', $endOfMonth)
                ->get();
            $hoursUsed = 0;
            foreach ($reservations as $reservation) {
                $hoursUsed += $reservation->end_time->diffInHours($reservation->start_time);
            }
            $hoursRequested = $request->end_time - $request->start_time;
            if ($hoursUsed + $hoursRequested > $reservable->monthly_hour_limit) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'monthly_hour_limit' => [__('سقف ساعت مجاز برای این ماه پر شده است.')]
                    ]
                ], 422);
            }
        }

        // check if already reserved
        $reserved = $reservable->reservations()
            ->where('start_time', '>=', Jalalian::fromDateTime($request->date)->toCarbon()->startOfDay()->addHours($request->start_time))
            ->where('end_time', '<=', Jalalian::fromDateTime($request->date)->toCarbon()->startOfDay()->addHours($request->end_time))
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
            'unit_id' => $unit->id,
            'start_time' => Jalalian::fromDateTime($request->date)->toCarbon()->startOfDay()->addHours($request->start_time),
            'end_time' => Jalalian::fromDateTime($request->date)->toCarbon()->startOfDay()->addHours($request->end_time),
            'cost' => $reservable->cost_per_hour * ($request->end_time - $request->start_time),
            'status' => 'paid',
        ]);

        return response()->json([
            'success' => true,
            'message' => __("رزرو با موفقیت انجام شد"),
        ]);
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id != auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => __("شما مجاز به لغو این رزرو نیستید"),
            ], 403);
        }

        if ($reservation->status == 'canceled') {
            return response()->json([
                'success' => false,
                'message' => __("این رزرو قبلا لغو شده است"),
            ], 422);
        }

        if ($reservation->reservable->cancel_hour_limit) {
            $now = Carbon::now();
            $cancelLimit = $reservation->start_time->subHours($reservation->reservable->cancel_hour_limit);
            if ($now >= $cancelLimit) {
                return response()->json([
                    'success' => false,
                    'message' => __("امکان لغو این رزرو فقط تا :hour ساعت قبل از شروع آن وجود دارد", [
                        'hour' => $reservation->reservable->cancel_hour_limit
                    ])
                ], 422);
            }
        }

        $reservation->status = 'canceled';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => __("رزرو با موفقیت لغو شد"),
        ]);
    }

    /**
     * Get reservation history for a unit
     */
    public function unitReserveHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit' => 'required|integer|exists:building_units,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $unit = BuildingUnit::findOrFail($request->unit);
        if ($unit->residents()->where('user_id', auth()->user()->id)->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => __("شما مجاز به مشاهده این رزروها نیستید"),
            ], 403);
        }

        $userIds = $unit->residents()->pluck('user_id');
        $reservations = Reservation::where('unit_id', $unit->id)
            ->whereIn('reservable_id', $unit->building->reservables()->pluck('id'))
            ->where('status', '!=', 'pending')
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'reservations' => ReservationResource::collection($reservations),
            ]
        ]);
    }
}
