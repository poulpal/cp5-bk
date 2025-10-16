<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\TollResource;
use App\Models\Building;
use App\Models\BuildingManager;
use App\Models\BuildingUnit;
use App\Models\Commission;
use App\Models\Toll;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class TollController extends Controller
{

    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'cashPay']);
    }

    public function index(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'unit' => 'nullable|numeric|exists:building_units,id',
            'filter' => 'nullable|in:all,unverified,verified',
            'type' => 'nullable|in:toll,deposit,cost,income',
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

        $tolls = auth()->buildingManager()->building->tolls()->with('service');
        $tolls = $tolls->whereNot('serviceable_type', Commission::class);

        if (request()->has('unit')) {
            $tolls = $tolls->where('serviceable_id', request()->unit)->where('serviceable_type', 'App\Models\BuildingUnit');
        }

        if (request()->has('filter')) {
            if (request()->filter == 'unverified') {
                $tolls = $tolls->where('is_verified', 0);
            } elseif (request()->filter == 'verified') {
                $tolls = $tolls->where('is_verified', 1);
            }
        }

        if (request()->has('sort') && request()->sort) {
            if ($request->sort == 'unit_number') {
                $tolls = $tolls->join('building_units', 'building_units.id', '=', 'tolls.serviceable_id')
                    ->orderBy('building_units.unit_number', $request->order ?? 'desc');
            } else {
                $tolls = $tolls->orderBy(request()->sort, request()->order ?? 'desc');
            }
        } else {
            $tolls = $tolls->orderBy('created_at', 'desc');
        }

        if (request()->has('search') && request()->search && request()->search != '') {
            $tolls = $tolls->whereHas('unit', function ($query) {
                $query->where('unit_number', 'like', '%' . request()->search . '%');
            });
        }

        $tolls_query = $tolls;

        if (request()->has('paginate') && request()->paginate) {
            $tolls = $tolls->paginate(request()->perPage ?? 20);
        } else {
            $tolls = $tolls->get();
        }

        // $balance = 0;

        // if (request()->has('paginate') && request()->paginate && $tolls->count() > 0) {
        //     $first_id = $tolls->sortBy('id')->first()->id;
        //     $balance = round($tolls_query->where('id', '<', $first_id)->sum('amount'), 1);
        // }

        // foreach ($tolls->reverse() as $toll) {
        //     $balance += $toll->amount;
        //     $toll->balance = round($balance, 1);
        // }

        if (request()->has('paginate') && request()->paginate) {
            return response()->paginate($tolls, TollResource::class);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_more' => false,
                    'tolls' => TollResource::collection($tolls),
                ]
            ]);
        }
    }

    public function cashPay(Toll $toll)
    {
        if ($toll->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه دسترسی به این تراکنش را ندارید.'
            ], 403);
        }

        if ($toll->status == 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'این تراکنش قبلا پرداخت شده است.'
            ], 422);
        }

        $toll->update([
            'status' => 'paid',
            'payment_method' => 'cash',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تراکنش با موفقیت تایید شد.'
        ]);
    }

    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'decimal:0,1', function ($attribute, $value, $fail) use ($request) {
                if ($value < 0) {
                    $fail(__("مبلغ وارد شده باید بزرگتر از صفر باشد"));
                }
            }],
            'description' => 'required',
            'date' => 'required|date',
            'resident_type' => 'required|in:owner,resident',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $unit = auth()->buildingManager()->building->units()->where('id', $id)->first();
        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => "Not found"
            ], 404);
        }
        $unit->tolls()->create([
            'building_id' => $unit->building->id,
            'amount' => $request->amount,
            'payment_method' => 'cash',
            'serviceable_type' => 'App\Models\BuildingUnit',
            'serviceable_id' => $unit->id,
            'description' => $request->description,
            'resident_type' => $request->resident_type,
            'created_at' => Carbon::parse($request->date),
        ]);

        return response()->json([
            'success' => true,
            'message' => __("فاکتور با موفقیت اضافه شد"),
        ], 201);
    }

    public function addMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tolls' => 'required|array',
            'tolls.*.unit_number' => 'required',
            // 'tolls.*.amount' => 'required|decimal:0,1|min:1',
            'tolls.*.amount' => function ($attribute, $value, $fail) {
                [$group, $position, $name] = explode('.', $attribute);
                $position += 1;
                if (!Validator::make(['item' => $value], ['item' => 'required'])->passes()) {
                    $fail("لطفا مبلغ مورد $position را وارد کنید");
                }

                if (!Validator::make(['item' => $value], ['item' => 'decimal:0,1'])->passes()) {
                    $fail("لطفا مبلغ مورد $position را به درستی وارد کنید");
                }

                if ($value < 1) {
                    $fail("لطفا مبلغ مورد $position را به درستی وارد کنید");
                }
            },
            'tolls.*.description' => 'required',
            'tolls.*.date' => 'nullable|date',
            'resident_type' => 'required|in:owner,resident',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $date_format = 'Y/m/d';

        DB::connection('mysql')->beginTransaction();

        try {
            foreach ($request->tolls as $toll) {
                $unit = auth()->buildingManager()->building->units()->where('unit_number', $toll['unit_number'])->first();
                if (!$unit) {
                    throw new \Exception(__("واحد ") . $toll['unit_number'] . __(" در ساختمان شما موجود نیست"));
                    return response()->json([
                        'success' => false,
                        'message' => "Not found"
                    ], 404);
                }
                Toll::create([
                    'building_id' => $unit->building->id,
                    'amount' => $toll['amount'],
                    'payment_method' => 'cash',
                    'serviceable_type' => 'App\Models\BuildingUnit',
                    'serviceable_id' => $unit->id,
                    'resident_type' => $request->resident_type,
                    'description' => $toll['description'],
                    'created_at' => isset($toll['date']) ? Jalalian::fromFormat($date_format, $toll['date'])->toCarbon() : Carbon::now(),
                    'updated_at' => isset($toll['date']) ? Jalalian::fromFormat($date_format, $toll['date'])->toCarbon() : Carbon::now(),
                ]);
            }
            DB::connection('mysql')->commit();
        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }


        return response()->json([
            'success' => true,
            'message' => 'فاکتور های مورد نظر با موفقیت افزوده شد.',
        ], 200);
    }

    public function destroy(Toll $toll)
    {
        if ($toll->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه دسترسی به این تراکنش را ندارید.'
            ], 403);
        }

        if ($toll->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'تراکنش قابل حذف نیست.'
            ], 422);
        }

        $toll->delete();

        return response()->json([
            'success' => true,
            'message' => 'تراکنش با موفقیت حذف شد.'
        ]);
    }

    public function multipleDestroy(Request $request)
    {

        $validator = Validator::make(request()->all(), [
            'ids' => 'array|required',
            'ids.*' => 'numeric|exists:tolls,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->ids as $id) {
            $toll = Toll::find($id);
            if ($toll->building_id != auth()->buildingManager()->building_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toll not found'
                ], 404);
            }

            if ($toll->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'امکان حذف فاکتور های پرداخت شده وجود ندارد.'
                ], 422);
            }

            DB::connection('mysql')->beginTransaction();

            try {
                $toll->delete();
                DB::connection('mysql')->commit();
            } catch (\Exception $e) {
                DB::connection('mysql')->rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'خطایی در حذف فاکتور رخ داده است.'
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'فاکتور با موفقیت حذف شد.'
        ]);
    }
}
