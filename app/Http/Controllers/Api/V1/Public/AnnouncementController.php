<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\BuildingResource;
use App\Http\Resources\User\AnnouncementResource;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'building' => 'required|exists:buildings,name_en',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $building = Building::where('name_en', $request->building)->first();

        $announcements = $building->announcements()
        ->where('is_public', true)
        ->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'building' => BuildingResource::make($building),
                'announcements' => AnnouncementResource::collection($announcements),
            ]
        ]);
    }
}
