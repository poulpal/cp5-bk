<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PlanResource;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = \App\Models\Plan::where('order', '>', 0)->orderBy('order')->get();
        return response()->json([
            'success' => true,
            'data' => [
                'plans' => PlanResource::collection($plans),
                'durations' => $plans->unique('durations.months')->pluck('durations')->flatten()->unique('months')->pluck('months'),
            ]
        ]);
    }
}
