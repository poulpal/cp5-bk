<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $units = auth()->user()->building_units;
        return view('user.dashboard')->with(
            [
                'units' => $units,
            ]
        );
    }
}
