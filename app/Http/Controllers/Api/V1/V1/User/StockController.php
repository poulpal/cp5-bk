<?php

namespace App\Http\Controllers\Api\V1\User;

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

    public function index()
    {

        $validator = Validator::make(request()->all(), [
            'perPage' => 'nullable|numeric',
            'paginate' => 'nullable|boolean',
            'sort' => 'nullable|string',
            'order' => 'nullable|string',
            'unit' => 'nullable|exists:building_units,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $unit = request()->unit ? auth()->user()->building_units()->find(request()->unit) : null;
        if (!$unit) {
            return response()->json([
                'success' => false,
                'errors' => 'unit not found'
            ], 404);
        }
        $building = $unit->building;

        if ($building->options->show_stocks_to_units == false) {
            return abort(200);
        }

        $stocks = $unit->building->stocks();

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
}
