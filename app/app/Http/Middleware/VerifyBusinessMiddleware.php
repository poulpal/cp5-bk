<?php

namespace App\Http\Middleware;

use App\Models\BuildingManager;
use Closure;
use Illuminate\Http\Request;

class VerifyBusinessMiddleware
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
            $building = BuildingManager::find(auth()->id())->building;
            if($building->is_verified == 0){
                if($request->expectsJson()){
                    return response()->json([
                        'success' => false,
                        'message' => 'اطلاعات شما تایید نشده است. لطفا با پشتیبانی تماس بگیرید.'
                    ], 403);
                }else{
                    return redirect()->route('building_manager.dashboard')->with('error', 'اطلاعات شما تایید نشده است.');
                }
            }
        }

        return $next($request);
    }
}
