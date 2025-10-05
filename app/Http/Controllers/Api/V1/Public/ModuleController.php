<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\ModuleResource;
use App\Models\Module;

class ModuleController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $groups = Module::where('order', '>', 0)
            ->get()
            ->groupBy('type');

        $arr = [];

        foreach ($groups as $key => $group) {
            $arr[] = [
                'type' => ($key == 'base') ? __("اصلی") : (($key == 'extra') ? __("اضافی") : (($key == 'accounting') ? __("حسابداری") : __("نامشخص"))),
                'modules' => ModuleResource::collection($group),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'modules' => $arr,
            ]
        ]);
    }
}
