<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TempMiddleware
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
        // if (auth()->buildingManager() && auth()->buildingManager()->mobile == '09124218398'){
        //     return $next($request);
        // }

        if (config('app.env') == 'local'){
            return $next($request);
        }

        if (auth()->buildingManager()){
            if (auth()->buildingManager()->building->name_en == 'hshcomplex'){
                return response()->json(['message' => 'نقص مدارک'], 500);
            }
        }

        if (auth()->user()){
            if (auth()->user()->is_banned){
                return response()->json(['message' => 'امکان دسترسی برای این اکانت وجود ندارد. لطفا با پشتیبانی تماس بگیرید.'], 500);
            }
            // check if user has unit in hsh complex
            $user = auth()->user();
            foreach ($user->building_units as $unit){
                if ($unit->building->name_en == 'hshcomplex'){
                    return response()->json(['message' => 'نقص مدارک'], 500);
                }
            }
        }
        return $next($request);
    }
}
