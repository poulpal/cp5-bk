<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\ReservableResource;
use App\Models\Reservable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReserveController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index', 'show']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'show']);
        $this->middleware('hasModule:reserve-and-poll')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'perPage' => 'nullable|numeric',
            'paginate' => 'nullable|boolean',
            'sort' => 'nullable|string',
            'order' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reservables = auth()->buildingManager()->building->reservables()->withCount('active_reservations');


        if (request()->has('sort') && request()->sort) {
            $reservables = $reservables->orderBy(request()->sort, request()->order ?? 'desc');
        } else {
            $reservables = $reservables->orderBy('created_at', 'desc');
        }

        if (request()->has('paginate') && request()->paginate) {
            $reservables = $reservables->paginate(request()->perPage ?? 20);
        } else {
            $reservables = $reservables->get();
        }

        if (request()->has('paginate') && request()->paginate) {
            return response()->paginate($reservables, ReservableResource::class);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'reservables' => ReservableResource::collection($reservables),
                ]
            ]);
        }
    }

    public function store(Request $request){

        $validator = Validator::make(request()->all(), [
            'title' => 'required|string',
            'description' => 'nullable|string',
            'ranges' => 'required|array',
            'ranges.*.start' => 'required|integer|between:0,23',
            'ranges.*.end' => 'required|integer|between:0,23|gt:ranges.*.start',
            'ranges.*.key' => 'required|string|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'ranges.*.enabled' => 'nullable',
            'cost_per_hour' => 'required|integer',
            'monthly_hour_limit' => 'nullable|integer|min:0',
            'cancel_hour_limit' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $available_hours = [];

        foreach ($request->ranges as $range) {
            $available_hours[$range['key']] = [
                'start' => $range['start'],
                'end' => $range['end'],
                'enabled' => $range['enabled'] == true ? true : false,
            ];
        }

        $reservable = new Reservable();
        $reservable->building_id = auth()->buildingManager()->building->id;
        $reservable->title = $request->title;
        $reservable->description = $request->description;
        $reservable->cost_per_hour = $request->cost_per_hour;
        $reservable->available_hours = $available_hours;
        $reservable->monthly_hour_limit = $request->monthly_hour_limit;
        $reservable->cancel_hour_limit = $request->cancel_hour_limit;
        $reservable->save();

        return response()->json([
            'success' => true,
            'message' => __("رزرو با موفقیت ایجاد شد"),
            'data' => [
                'reservable' => new ReservableResource($reservable)
            ]
        ], 201);
    }

    public function show(Reservable $reservable)
    {
        if ($reservable->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => __("شما اجازه دسترسی به این رزرو را ندارید")
            ], 403);
        }

        $reservable->load('reservations')->load('reservations.user');

        return response()->json([
            'success' => true,
            'data' => [
                'reservable' => new ReservableResource($reservable)
            ]
        ]);
    }

    public function update(Request $request, Reservable $reservable)
    {
        if ($reservable->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => __("شما اجازه دسترسی به این رزرو را ندارید")
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'cost_per_hour' => 'sometimes|required|integer',
            'available_hours' => 'sometimes|required|array',
            'is_active' => 'sometimes|boolean',
            'is_public' => 'sometimes|boolean',
            'monthly_hour_limit' => 'nullable|integer|min:0',
            'cancel_hour_limit' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        if (isset($data['available_hours'])) {
            $reservable->available_hours = $data['available_hours'];
            unset($data['available_hours']);
        }
        $reservable->update($data);

        return response()->json([
            'success' => true,
            'message' => __('رزرو با موفقیت ویرایش شد'),
            'data' => [
                'reservable' => new ReservableResource($reservable->fresh())
            ]
        ]);
    }

    public function destroy(Reservable $reservable)
    {
        if ($reservable->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => __("شما اجازه دسترسی به این رزرو را ندارید")
            ], 403);
        }
        // check if have active reservations
        if ($reservable->active_reservations()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => __('این رزرو دارای رزروهای فعال است و نمی‌توان آن را حذف کرد')
            ], 422);
        }

        $reservable->delete();
        return response()->json([
            'success' => true,
            'message' => __('رزرو با موفقیت حذف شد')
        ]);
    }

}
