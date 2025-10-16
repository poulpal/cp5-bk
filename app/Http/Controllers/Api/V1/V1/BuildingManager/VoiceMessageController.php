<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\VoiceMessageResource;
use App\Jobs\SendVoiceMessage;
use App\Mail\CustomMail;
use App\Models\VoiceMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class VoiceMessageController extends Controller
{

    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index', 'show']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'show']);
    }

    public function index()
    {
        $voiceMessages = auth()->buildingManager()->building->voiceMessages;

        foreach ($voiceMessages as $voiceMessage) {
            if ($voiceMessage->batch_id && $voiceMessage->status == 'sending') {
                $batch = Bus::findBatch($voiceMessage->batch_id);
                $voiceMessage->status = __("در حال ارسال ") . (string)$batch->progress() . "%";
            }
        }
        return response()->json([
            'success' => true,
            'data' => [
                'voiceMessages' => VoiceMessageResource::collection($voiceMessages)
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pattern' => ['required', 'string', 'max:200'],
            // 'units' => ['required', 'array'],
            // 'units.*' => ['required', 'integer', 'exists:building_units,id'],
            // 'scheduled_at' => ['required', 'date_format:Y-m-d H:i:s'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $units = auth()->buildingManager()->building->units()->pluck('id')->toArray();

        if (count($units) == 0) {
            return response()->json([
                'success' => false,
                'message' => 'هیچ واحدی برای ارسال پیام صوتی وجود ندارد.'
            ], 422);
        }

        // if (Carbon::now()->isAfter(Carbon::today()->addHours(19)) || Carbon::now()->isBefore(Carbon::today()->addHours(9))) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'ارسال پیام صوتی فقط در بازه زمانی 9 صبح تا 7 بعد از ظهر مجاز است.'
        //     ], 422);
        // }

        // $items = [];

        // foreach ($request->units as $unit) {
        //     $items[] = new SendVoiceMessage($unit, $request->pattern);
        // }

        // $batch = Bus::batch($items)->then(function ($batch) {
        //     $voiceMessage = VoiceMessage::where('batch_id', $batch->id)->first();
        //     $voiceMessage->status = 'completed';
        //     $voiceMessage->save();
        // })->catch(function ($batch, $e) {
        //     // First exception thrown...
        //     $voiceMessage = VoiceMessage::where('batch_id', $batch->id)->first();
        //     $voiceMessage->status = 'failed';
        //     $voiceMessage->save();
        // })->finally(function ($batch) {
        //     // The batch has finished executing...
        // });

        Mail::to(['cc2com.com@gmail.com', 'arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(new CustomMail(__("درخواست ارسال پیام صوتی"), __("درخواست ارسال پیام صوتی از طرف مدیر ساختمان")));

        $voiceMessage = VoiceMessage::create([
            'building_id' => auth()->buildingManager()->building->id,
            'pattern' => $request->pattern,
            'units' => $units,
            // 'scheduled_at' => $request->scheduled_at,
            // 'batch_id' => $batch->id,
        ]);
    }

    public function destroy(VoiceMessage $voiceMessage)
    {
        if ($voiceMessage->status != 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'امکان حذف پیام صوتی ارسال شده وجود ندارد.'
            ], 422);
        }
        if ($voiceMessage->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه حذف این پیام صوتی را ندارید.'
            ], 403);
        }

        $voiceMessage->delete();

        return response()->json([
            'success' => true,
            'message' => 'پیام صوتی با موفقیت حذف شد.'
        ], 200);
    }
}
