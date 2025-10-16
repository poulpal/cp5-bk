<?php

namespace App\Http\Middleware;

use App\Models\BuildingManager;
use Closure;
use Illuminate\Http\Request;

class HasPlanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->user()->role == 'building_manager'){
            if (config('app.type') == 'c36') {
                return $next($request);
            }
            $building = auth()->buildingManager()->building;
            $activeBaseModule = $building->modules()->where('type', 'base')->first();
            if(!$activeBaseModule && !$request->routeIs('v1.building_manager.modules.*')){
                if($request->expectsJson()){
                    return response()->json([
                        'success' => false,
                        'message' => 'برای دسترسی به امکانات پنل از بخش پکیج ها، پکیج پایه را تهیه نمایید.',
                        'action' => 'buy_base_module'
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
