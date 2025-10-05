<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\ContactResource;
use App\Models\BuildingUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
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
                'message' => __("شما مجاز به دسترسی به این مخاطبان نیستید"),
            ], 403);
        }

        $contacts = $unit->building->contacts;

        return response()->json([
            'success' => true,
            'data' => [
                'contacts' => ContactResource::collection($contacts),
            ]
        ], 200);
    }
}
