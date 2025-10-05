<?php

namespace App\Http\Controllers\BuildingManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('buildingManager.dashboard');
    }
}
