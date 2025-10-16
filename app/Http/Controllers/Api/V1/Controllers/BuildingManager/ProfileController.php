<?php

namespace App\Http\Controllers\BuildingManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        return view('buildingManager.profile', [
            'building_manager' => auth()->buildingManager(),
        ]);
    }
}
