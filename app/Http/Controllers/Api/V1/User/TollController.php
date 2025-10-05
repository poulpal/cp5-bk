<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\TollResource;
use App\Models\Building;
use App\Models\BuildingUnit;
use App\Models\Toll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TollController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit' => ['required', 'exists:building_units,id']
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $tolls = Toll::query();

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

        if ($request->has('unit')) {
            $tolls = $tolls->where('serviceable_type', 'App\Models\BuildingUnit')
                ->where('serviceable_id', $request->unit)
                ->where(function ($query) use ($resident_type, $ownership) {
                    $query->where('resident_type', 'all')
                        ->orWhere('resident_type', $ownership)
                        ->orWhere('resident_type', $resident_type);
                });
        }

        $tolls = $tolls->orderBy('id', 'desc');
        $tolls = $tolls->get();
        return response()->json([
            'success' => true,
            'data' => [
                'tolls' => TollResource::collection($tolls),
            ]
        ]);
    }
}
