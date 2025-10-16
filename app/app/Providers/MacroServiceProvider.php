<?php

namespace App\Providers;

use App\Vendor;
use App\Admin;
use App\Models\BuildingManager;
use App\Models\User;
use Illuminate\Auth\RequestGuard;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        SessionGuard::macro('buildingManager', function () {
            if (auth('building_manager')->check()) return BuildingManager::find(auth('building_manager')->id());
            return null;
        });

        SessionGuard::macro('role', function () {
            $gurads = config('auth.guards');
            foreach ($gurads as $guard => $value) {
                if ($guard == 'sanctum' || $guard == 'web') continue;
                if (auth($guard)->check()) return $guard;
            }
            return null;
        });

        RequestGuard::macro('buildingManager', function () {
            if (auth()->check() && auth()->user()->role !== 'building_manager') return null;
            if (auth()->check()) return
                Cache::remember('building_manager_' . auth()->id(), 60 * 1, function () {
                    return BuildingManager::find(auth()->id())->load('building');
                });
            return null;
        });

        Response::macro('paginate', function ($data, $resource = null) {
            if ($resource) {
                $collection = $resource::collection($data);
            } else {
                $collection = $data;
            }
            return response()->json([
                'success' => true,
                'paginate' => true,
                'data' => $collection,
                'current_page' => $data->toArray()['current_page'],
                'first_page_url' => $data->toArray()['first_page_url'],
                'from' => $data->toArray()['from'],
                'to' => $data->toArray()['to'],
                'total' => $data->toArray()['total'],
                'last_page' => $data->toArray()['last_page'],
                'last_page_url' => $data->toArray()['last_page_url'],
                'links' => $data->toArray()['links'],
                'next_page_url' => $data->toArray()['next_page_url'],
                'path' => $data->toArray()['path'],
                'per_page' => $data->toArray()['per_page'],
                'prev_page_url' => $data->toArray()['prev_page_url'],

            ], 200);
        });
    }
}
