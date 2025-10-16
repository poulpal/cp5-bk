<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index', 'show']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'show']);
    }

    public function index()
    {
        $building_manager = auth()->buildingManager();
        $announcements = $building_manager->building->announcements()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'announcements' => AnnouncementResource::collection($announcements),
            ]
        ]);
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
            'title' => 'required|string|max:255',
            'text' => 'required|string',
            'is_public' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $building_manager = auth()->buildingManager();
        $announcement = $building_manager->building->announcements()->create([
            'title' => $request->title,
            'text' => $request->text,
            'is_public' => $request->is_public,
        ]);

        return response()->json([
            'success' => true,
            'message' => __("اطلاعیه با موفقیت ایجاد شد"),
            'data' => [
                'announcement' => new AnnouncementResource($announcement),
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Announcement  $announcement
     * @return \Illuminate\Http\Response
     */
    public function show(Announcement $announcement)
    {
        if ($announcement->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'announcement_id' => __("اطلاعیه مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'announcement' => new AnnouncementResource($announcement),
            ]
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        if ($announcement->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'announcement_id' => __("اطلاعیه مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'text' => 'required|string',
            'is_public' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $announcement->update([
            'title' => $request->title,
            'text' => $request->text,
            'is_public' => $request->is_public,
        ]);

        return response()->json([
            'success' => true,
            'message' => __("اطلاعیه با موفقیت ویرایش شد"),
            'data' => [
                'announcement' => new AnnouncementResource($announcement),
            ]
        ]);
    }

    public function updatePublicStatus(Request $request, Announcement $announcement)
    {
        if ($announcement->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'announcement_id' => __("اطلاعیه مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'is_public' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $announcement->update([
            'is_public' => $request->is_public,
        ]);

        return response()->json([
            'success' => true,
            'message' => __("وضعیت نمایش اطلاعیه با موفقیت تغییر یافت"),
            'data' => [
                'announcement' => new AnnouncementResource($announcement),
            ]
        ]);
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'announcement_id' => __("اطلاعیه مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => __("اطلاعیه با موفقیت حذف شد"),
        ]);
    }
}
