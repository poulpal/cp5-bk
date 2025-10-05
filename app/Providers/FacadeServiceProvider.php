<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class FacadeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('numberFormatter',function() {
            return new \App\Helpers\NumberFormatter;
        });

        App::bind('smsMelli',function() {
            return new \App\Helpers\SmsMelli;
        });

        App::bind('avanak',function() {
            return new \App\Helpers\Avanak;
        });

        App::bind('commissionHelper',function() {
            return new \App\Helpers\CommissionHelper;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
