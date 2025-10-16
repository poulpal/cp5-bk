<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index']);
    }

    public function index(Request $request)
    {
        $balances = auth()->buildingManager()->building->balances;

        return response()->json([
            'success' => true,
            'data' => [
                'balances' => $balances,
            ]
        ]);
    }
}
