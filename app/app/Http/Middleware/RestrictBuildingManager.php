<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictBuildingManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $type)
    {
        if (auth()->user()->role !== 'building_manager') {
            return response()->json([
                'success' => false,
                'message' => 'دسترسی شما توسط مدیریت ساختمان محدود شده است.'
            ], 403);
        }
        if (auth()->user()->role == 'building_manager') {
            if ($type == auth()->buildingManager()->building_manager_type) {
                return response()->json([
                    'success' => false,
                    'message' => 'دسترسی شما توسط مدیریت ساختمان محدود شده است.'
                ], 403);
            }
        }

        return $next($request);
    }
}
