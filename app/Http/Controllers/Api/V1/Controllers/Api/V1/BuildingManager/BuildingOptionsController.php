<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\BuildingOptionsResource;
use App\Http\Resources\BuildingManager\BuildingResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuildingOptionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['show']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['show']);
    }

    public function show(Request $request)
    {
        $options = auth()->buildingManager()->building->options;
        return response()->json([
            'data' => [
                'options' => BuildingOptionsResource::make($options),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'charge_day' => 'required|numeric|min:1|max:29',
            'rent_day' => 'required|numeric|min:1|max:29',
            'custom_payment' => 'required|boolean',
            'late_fine' => 'required|boolean',
            'late_fine_percent' => 'requiredif:late_fine,1|decimal:0,1|numeric|between:0,99.9',
            'late_fine_days' => 'requiredif:late_fine,1|numeric|min:1|max:29',
            // 'early_payment' => 'required|boolean',
            // 'early_payment_percent' => 'requiredif:late_fine,1|decimal:0,1|numeric|between:0,99.9',
            // 'early_payment_days' => 'requiredif:late_fine,1|numeric|min:1|max:29',
            // 'manual_payment' => 'required|boolean',
            'auto_add_monthly_charge' => 'required|boolean',
            'auto_add_monthly_rent' => 'required|boolean',
            // 'early_payment' => 'required|boolean',
            // 'early_payment_percent' => 'required|numeric',
            // 'early_payment_days' => 'required|numeric',
            // 'send_building_manager_payment_notification' => 'required|boolean',
            // 'currency' => 'required|in:rial,toman',
            'show_costs_to_units' => 'required|boolean',
            'show_stocks_to_units' => 'required|boolean',
            'show_balances_to_units' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $options = auth()->buildingManager()->building->options;
        $options->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'اطلاعات با موفقیت بروزرسانی شد.',
            'data' => [
                'options' => BuildingOptionsResource::make($options),
            ],
        ]);

    }

}
