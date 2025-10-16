<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\PollResource;
use App\Models\BuildingUnit;
use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PollController extends Controller
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

        $unit = BuildingUnit::findOrfail($request->unit);
        if ($unit->residents()->where('user_id', auth()->user()->id)->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => __("شما مجاز به دسترسی به این نظرسنجی نیستید"),
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

        $polls = $unit->building->polls()
            ->withCount('votes')
            ->whereJsonContains('units', Str::lower($unit->id))
            ->where(function ($query) use ($resident_type, $ownership) {
                $query->where('resident_type', 'all')
                    ->orWhere('resident_type', $ownership)
                    ->orWhere('resident_type', $resident_type);
            })
            ->where('ends_at', '>=', now())
            ->where('starts_at', '<=', now())->orderBy('starts_at', 'desc');

        $polls = $polls->get();

        foreach ($polls as $poll) {
            $poll->has_voted = $poll->votes()->where('building_unit_id', $unit->id)->where('user_id', auth()->user()->id)->count() > 0;
            $poll->vote = $poll->has_voted ? $poll->votes()->where('building_unit_id', $unit->id)->where('user_id', auth()->user()->id)->first()->option : null;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'polls' => PollResource::collection($polls),
            ]
        ], 200);
    }

    public function vote(Request $request, Poll $poll)
    {
        $validator = Validator::make($request->all(), [
            'option' => 'required|integer',
            'unit' => 'required|integer|exists:building_units,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $unit = BuildingUnit::findOrfail($request->unit);
        if ($unit->residents()->where('user_id', auth()->user()->id)->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => __("شما مجاز به دسترسی به این نظرسنجی نیستید"),
            ], 403);
        }

        if ($poll->starts_at > now()) {
            return response()->json([
                'success' => false,
                'message' => __("نظرسنجی هنوز شروع نشده است"),
            ], 403);
        }
        if ($poll->ends_at < now()) {
            return response()->json([
                'success' => false,
                'message' => __("نظرسنجی به پایان رسیده است"),
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

        if ($poll->resident_type != 'all' && $poll->resident_type != $ownership && $poll->resident_type != $resident_type) {
            return response()->json([
                'success' => false,
                'message' => __("شما مجاز به دسترسی به این نظرسنجی نیستید"),
            ], 403);
        }

        $has_voted = $poll->votes()->where('building_unit_id', $unit->id)->where('user_id', auth()->user()->id)->first();

        if ($has_voted) {
            $has_voted->update([
                'option' => $request->option,
            ]);
        } else {
            $vote = $poll->votes()->create([
                'building_unit_id' => $unit->id,
                'user_id' => auth()->user()->id,
                'option' => $request->option,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __("رای شما با موفقیت ثبت شد"),
        ], 200);
    }
}
