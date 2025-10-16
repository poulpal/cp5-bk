<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\StockResource;
use App\Models\Stock;
use App\Models\StockTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Morilog\Jalali\Jalalian;

class StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index', 'show']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'show']);
        $this->middleware('hasModule:stocks')->except(['index', 'show']);
    }

    public function index()
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

        $stocks = auth()->buildingManager()->building->stocks();

        if (request()->has('sort') && request()->sort) {
            $stocks = $stocks->orderBy(request()->sort, request()->order ?? 'desc');
        }

        if (request()->has('paginate') && request()->paginate) {
            $stocks = $stocks->paginate(request()->perPage ?? 20);
        } else {
            $stocks = $stocks->get();
        }

        if (request()->has('paginate') && request()->paginate) {
            return response()->paginate($stocks, StockResource::class);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'stocks' => StockResource::collection($stocks),
                ]
            ]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'invoice_number' => [
                'required',
                'string',
                Rule::unique('stocks', 'invoice_number')->where(function ($query) {
                    return $query->where('building_id', auth()->buildingManager()->building->id)->whereNull('deleted_at');
                }),
            ],
            'quantity' => 'nullable|numeric',
            'price' => 'nullable|numeric',
            'buyer' => 'nullable|string',
            'seller' => 'nullable|string',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $stock = Stock::create([
            'building_id' => auth()->buildingManager()->building->id,
            'title' => $request->title,
            'description' => $request->description,
            'invoice_number' => $request->invoice_number,
            'quantity' => $request->quantity == "" ? null : $request->quantity,
            'price' => $request->price == "" ? null : $request->price,
            'buyer' => $request->buyer ?? null,
            'seller' => $request->seller ?? null,
        ]);

        $stock->created_at = Carbon::parse($request->date)->startOfDay() ?? Carbon::now()->startOfDay();
        $stock->save();

        return response()->json([
            'success' => true,
            'message' => __("کالا با موفقیت اضافه شد"),
        ], 201);
    }

    public function show(Stock $stock)
    {
        if ($stock->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'message' => 'Stock not found'
            ], 404);
        }

        $transactions = $stock->transactions()->orderBy('created_at', 'asc')->get();

        $remaining_transaction = new StockTransaction();
        $remaining_transaction->description = __("موجودی اولیه");
        $remaining_transaction->quantity = $stock->quantity;
        $remaining_transaction->type = 'import';
        $remaining_transaction->created_at = $stock->created_at;
        $remaining_transaction->price = $stock->price;
        $remaining_transaction->balance = $stock->quantity;

        $transactions->prepend($remaining_transaction);


        $balance = 0;

        foreach ($transactions as $key => $transaction) {
            $balance += $transaction->quantity;
            $transaction->balance = $balance;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stock' => new StockResource($stock),
                'transactions' => $transactions,
            ]
        ]);
    }

    public function addTransaction(Request $request, Stock $stock)
    {
        if ($stock->building_id != auth()->buildingManager()->building_id) {
            return response()->json([
                'success' => false,
                'message' => 'Stock not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'quantity' => 'required|numeric|min:1',
            'type' => 'required|string|in:import,export',
            'date' => 'required|date|after_or_equal:' . $stock->created_at,
            'price' => 'nullable|numeric|min:1',
        ], [
            'date.after_or_equal' => __("تاریخ تراکنش باید بعد از تاریخ ایجاد کالا باشد") . "\n" . 'تاریخ ایجاد کالا: ' . Jalalian::forge($stock->created_at)->format('Y/m/d'),
        ]);

        if ($request->type == 'export' && $stock->available_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => __("موجودی کافی نیست"),
            ], 422);
        }

        $quantity = $request->type == 'import' ? $request->quantity : -$request->quantity;

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $stock->transactions()->create([
            'description' => $request->description,
            'quantity' => $quantity,
            'created_at' => $request->date ?? now(),
            'price' => $request->price ?? $stock->price ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => __("تراکنش با موفقیت اضافه شد"),
        ], 201);
    }

    // public function update(Request $request, Stock $stock)
    // {
    //     if ($stock->building_id != auth()->buildingManager()->building_id) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Stock not found'
    //         ], 404);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'title' => 'required|string',
    //         'description' => 'required|string',
    //         'invoice_number' => [
    //             'required',
    //             'string',
    //             Rule::unique('stocks', 'invoice_number')->ignore($stock->id)->where(function ($query) {
    //                 return $query->where('building_id', auth()->buildingManager()->building->id)->whereNull('deleted_at');
    //             }),
    //         ],
    //         'quantity' => 'nullable|numeric',
    //         'price' => 'nullable|numeric',
    //         'buyer' => 'nullable|string',
    //         'seller' => 'nullable|string',
    //         'date' => 'required|date',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $stock->update([
    //         'title' => $request->title,
    //         'description' => $request->description,
    //         'invoice_number' => $request->invoice_number,
    //         'quantity' => $request->quantity == "" ? null : $request->quantity,
    //         'price' => $request->price == "" ? null : $request->price,
    //         'buyer' => $request->buyer ?? null,
    //         'seller' => $request->seller ?? null,
    //     ]);

    //     $stock->created_at = $request->date ?? $stock->created_at;
    //     $stock->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => __("کالا با موفقیت ویرایش شد"),
    //     ]);
    // }

    // public function destroy(Stock $stock)
    // {
    //     if ($stock->building_id != auth()->buildingManager()->building_id) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Stock not found'
    //         ], 404);
    //     }

    //     $stock->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => __("کالا با موفقیت حذف شد"),
    //     ]);
    // }
}
