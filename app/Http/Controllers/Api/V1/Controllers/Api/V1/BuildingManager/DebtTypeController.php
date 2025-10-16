<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\DebtTypeResource;
use App\Models\DebtType;
use Illuminate\Http\Request;

class DebtTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $building = auth()->buildingManager()->building;
        $debtTypes = $building->debtTypes;

        return response()->json([
            'success' => true,
            'data' => [
                'debtTypes' => DebtTypeResource::collection($debtTypes)
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DebtType  $debtType
     * @return \Illuminate\Http\Response
     */
    public function show(DebtType $debtType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DebtType  $debtType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DebtType $debtType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DebtType  $debtType
     * @return \Illuminate\Http\Response
     */
    public function destroy(DebtType $debtType)
    {
        //
    }
}
