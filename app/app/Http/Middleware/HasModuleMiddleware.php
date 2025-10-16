<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HasModuleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $module)
    {
        if (auth()->user()->role == 'building_manager') {
            if (config('app.type') == 'c36') {
                return $next($request);
            }
            $building = auth()->buildingManager()->building;
            if ($module == 'base') {
                $activeBaseModule = $building->modules()->where('slug', 'like', 'base-%')->first();
            } else {
                $activeBaseModule = $building->modules()->where('slug', $module)->first();
            }
            if (!$activeBaseModule && !$request->routeIs('v1.building_manager.modules.*')) {
                if ($request->expectsJson() && $module !== 'base') {
                    return response()->json([
                        'success' => false,
                        'message' => 'برای دسترسی به این بخش باید اشتراک پکیج ' . Module::where('slug', $module)->first()->title . ' را خریداری کنید.'
                    ], 403);
                }else {
                    return response()->json([
                        'success' => false,
                        'message' => 'برای دسترسی به این بخش باید اشتراک پکیج پایه را خریداری کنید.'
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
