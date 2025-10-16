<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\AnnouncementResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit' => 'required|exists:building_units,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $unit = $request->user()->building_units()->find($request->unit);
        if (!$unit) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'unit_id' => __("واحد مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        $announcements = $unit->building->announcements()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'announcements' => AnnouncementResource::collection($announcements),
            ]
        ]);
    }
}
