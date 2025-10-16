<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Facades\CommissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\VoiceMessageResource;
use App\Mail\CustomMail;
use App\Models\BuildingUnit;
use App\Models\Commission;
use App\Models\FcmMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class FcmMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index', 'show']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'show']);
    }

    public function index()
    {
        $fcmMessages = auth()->buildingManager()->building->fcmMessages()->orderBy('status', 'desc')->orderBy('created_at', 'desc')->get();

        foreach ($fcmMessages as $fcmMessage) {
            if ($fcmMessage->batch_id && $fcmMessage->status == 'sending') {
                $batch = Bus::findBatch($fcmMessage->batch_id);
                $fcmMessage->status = __("در حال ارسال ") . (string)$batch->progress() . "%";
            }
        }
        return response()->json([
            'success' => true,
            'data' => [
                'fcmMessages' => VoiceMessageResource::collection($fcmMessages)
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

        $length = ceil((Str::length($request->pattern)) / 70);
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
                'message' => 'هیچ واحدی برای ارسال نوتیفیکیشن وجود ندارد.'
            ], 422);
        }

        $fcmMessage = FcmMessage::create([
            'building_id' => auth()->buildingManager()->building->id,
            'pattern' => $request->pattern,
            'units' => $units,
            'length' => $length,
            'count' => $count,
            'resident_type' => $request->resident_type,
        ]);

        Mail::to(['cc2com.com@gmail.com', 'arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(new CustomMail(
            __("درخواست ارسال نوتیفیکیشن") . " - " . str($fcmMessage->id),
            "نام ساختمان : " . $building->name . "<br>" .
                "متن پیام : <br> " . $request->pattern . "<br>"
        ));

        return response()->json([
            'success' => true,
            'message' => 'درخواست ارسال نوتیفیکیشن با موفقیت ثبت شد.',
        ], 200);
    }

    public function destroy(FcmMessage $fcmMessage)
    {
        if ($fcmMessage->status != 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'امکان حذف نوتیفیکیشن ارسال شده وجود ندارد.'
            ], 422);
        }
        if ($fcmMessage->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه حذف این نوتیفیکیشن را ندارید.'
            ], 403);
        }

        $building = $fcmMessage->building;
        $building->fcm_balance += $fcmMessage->count * $fcmMessage->length;
        $building->save();

        $fcmMessage->delete();

        return response()->json([
            'success' => true,
            'message' => 'نوتیفیکیشن با موفقیت حذف شد.'
        ], 200);
    }
}
