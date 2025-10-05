<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\BuildingManagerResource;
use App\Models\BuildingManager;
use App\Models\Details;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuildingManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index', 'show']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'show']);
    }

    public function index()
    {
        $building_manager = auth()->buildingManager();
        $building = $building_manager->building;
        $building_managers = $building->buildingManagers()->whereNot('building_manager_type', 'superadmin')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'building_managers' => BuildingManagerResource::collection($building_managers),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'mobile' => 'required|regex:/(09)[0-9]{9}/',
            'type' => 'required|in:main,other,hsh-1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->type == 'main') {
            return response()->json([
                'success' => false,
                'message' => __("برای تغییر مدیر اصلی لطفا با پشتیبانی تماس بگیرید"),
            ], 402);
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            $user = new User();
            if ($request->first_name) {
                $user->first_name = $request->first_name;
            }
            if ($request->last_name) {
                $user->last_name = $request->last_name;
            }
            $user->mobile = $request->mobile;
            $user->save();
        } else {

            if ($user->role == 'building_manager') {
                return response()->json([
                    'success' => false,
                    'message' => __("این شماره موبایل قبلا در سیستم ثبت شده است"),
                ]);
            }

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->save();
        }

        $user->role = 'building_manager';
        $user->building_manager_type = $request->type;
        $user->building_id = auth()->buildingManager()->building->id;
        $user->save();

        $user->details()->delete();

        $details = new Details();
        $details = auth()->buildingManager()->building->mainBuildingManagers()->first()->details->replicate();
        $details->user_id = $user->id;
        $details->save();

        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => __("مدیر مورد نظر با موفقیت ایجاد شد"),
            'data' => [
                'building_manager' => BuildingManagerResource::make($user),
            ]
        ]);
    }

    public function show(BuildingManager $buildingManager)
    {
        if ($buildingManager->building_id != auth()->buildingManager()->building_id) {
            return abort(404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'building_manager' => BuildingManagerResource::make($buildingManager),
            ]
        ]);
    }

    public function destroy(BuildingManager $buildingManager)
    {
        if ($buildingManager->building_id != auth()->buildingManager()->building_id) {
            return abort(404);
        }

        if ($buildingManager->id == auth()->buildingManager()->id) {
            return response()->json([
                'success' => false,
                'message' => __("شما نمیتوانید خودتان را حذف کنید"),
            ], 402);
        }

        $buildingManager->role = 'user';
        $buildingManager->building_id = null;
        $buildingManager->building_manager_type = null;
        $buildingManager->save();

        $buildingManager->details()->delete();

        // revoke tokens
        $buildingManager->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => __("مدیر مورد نظر با موفقیت حذف شد"),
        ]);
    }
}
