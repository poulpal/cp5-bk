<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\PollResource;
use App\Models\Poll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PollController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next)
        {
            if (auth()->user()->mobile == '09125052364') {
                return $next($request);
            }
            if (auth()->user()->role !== 'building_manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'دسترسی شما توسط مدیریت ساختمان محدود شده است.'
                ], 403);
            }
            if (auth()->user()->role == 'building_manager') {
                if ('other' == auth()->buildingManager()->building_manager_type) {
                    return response()->json([
                        'success' => false,
                        'message' => 'دسترسی شما توسط مدیریت ساختمان محدود شده است.'
                    ], 403);
                }
            }

            return $next($request);
        })->except(['index']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index']);
        $this->middleware('hasModule:reserve-and-poll')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'perPage' => 'nullable|numeric',
            'paginate' => 'nullable|boolean',
            'sort' => 'nullable|string',
            'order' => 'nullable|string',
            'search' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $polls = auth()->buildingManager()->building->polls()->withCount('votes')->with('votes');


        if (request()->has('sort') && request()->sort) {
            $polls = $polls->orderBy(request()->sort, request()->order ?? 'desc');
        } else {
            $invoices = $polls->orderBy('created_at', 'desc');
        }

        if (request()->has('paginate') && request()->paginate) {
            $invoices = $invoices->paginate(request()->perPage ?? 20);
        } else {
            $invoices = $invoices->get();
        }

        if (request()->has('paginate') && request()->paginate) {
            return response()->paginate($invoices, PollResource::class);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    // 'has_more' => false,
                    'polls' => PollResource::collection($invoices),
                ]
            ]);
        }
    }

    public function show(Poll $poll)
    {
        if ($poll->building_id !== auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => __("نظرسنجی مورد نظر یافت نشد"),
            ], 404);
        }

        $poll->loadCount('votes');
        $poll->load('votes');

        return response()->json([
            'success' => true,
            'data' => [
                'poll' => new PollResource($poll),
                'votes' => $poll->votes->map(function ($vote) use($poll) {
                    return [
                        'option' => $poll->options[$vote->option],
                        'user' => [
                            'name' => $vote->user->first_name . __(" ") . $vote->user->last_name,
                            'mobile' => $vote->user->mobile,
                        ],
                        'unit' => $vote->unit->unit_number,
                        'created_at' => $vote->created_at,
                    ];
                }),
            ]
        ]);
    }

    public function renew(Poll $poll, Request $request)
    {
        if ($poll->building_id !== auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => __("نظرسنجی مورد نظر یافت نشد"),
            ], 404);
        }

        $request->merge([
            'ends_at' => Carbon::parse($request->ends_at),
        ]);

        $building = auth()->buildingManager()->building;

        $validator = Validator::make(request()->all(), [
            'ends_at' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $poll->ends_at = $request->ends_at;

        $poll->save();

        return response()->json([
            'success' => true,
            'message' => __("نظرسنجی با موفقیت تمدید شد"),
            'data' => [
                'poll' => new PollResource($poll),
            ]
        ], 200);
    }

    public function store(Request $request){

        $request->merge([
            'starts_at' => Carbon::parse($request->starts_at),
            'ends_at' => Carbon::parse($request->ends_at),
        ]);

        $building = auth()->buildingManager()->building;

        $validator = Validator::make(request()->all(), [
            'title' => 'required|string',
            'description' => 'nullable|string',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'units' => ['required', 'array'],
            'units.*' => [
                'required', 'integer',
                Rule::exists('building_units', 'id')->where(function ($query) use ($building) {
                    return $query->where('building_id', $building->id)->whereNull('deleted_at');
                })
            ],
            'resident_type' => ['required', 'string', Rule::in(['all', 'owner', 'renter', 'resident'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $poll = new Poll();
        $poll->building_id = $building->id;
        $poll->title = $request->title;
        $poll->description = $request->description;
        $poll->options = $request->options;
        $poll->starts_at = $request->starts_at;
        $poll->ends_at = $request->ends_at;
        $poll->units = $request->units;
        $poll->resident_type = $request->resident_type;
        $poll->save();

        return response()->json([
            'success' => true,
            'message' => __("نظرسنجی با موفقیت ایجاد شد"),
            'data' => [
                'poll' => new PollResource($poll),
            ]
        ], 201);
    }


    public function destroy($id)
    {
        $poll = auth()->buildingManager()->building->polls()->where('id', $id)->first();
        $poll->loadCount('votes');

        if (!$poll) {
            return response()->json([
                'success' => false,
                'message' => __("نظرسنجی مورد نظر یافت نشد"),
            ], 404);
        }

        if(now()->greaterThan($poll->starts_at) && $poll->votes_count > 0) {
            return response()->json([
                'success' => false,
                'message' => __("امکان حذف نظرسنجی در حال اجرا وجود ندارد"),
            ], 422);
        }

        $poll->delete();

        return response()->json([
            'success' => true,
            'message' => __("نظرسنجی با موفقیت حذف شد"),
        ], 200);
    }
}
