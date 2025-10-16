<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PlanResource;
use App\Models\Commission;
use App\Models\DiscountCode;
use App\Models\Factor;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;


class FactorController extends Controller
{
    public function index(Request $request)
    {
        $building = auth()->buildingManager()->building;
        $factors = $building->factors()->latest()->get();
        foreach ($factors as $factor) {
            $factor->url = route('v1.public.factors.view', $factor->token);
        }
        return response()->json([
            'success' => true,
            'data' => $factors
        ]);
    }

    public function view(Request $request, $token)
    {
        $factor = Factor::where('token', $token)->first();
        if (!$factor) {
            return response()->json([
                'success' => false,
                'message' => 'فاکتور مورد نظر یافت نشد.'
            ], 404);
        }
        return view('pdf.factor', compact('factor'));
    }

}
