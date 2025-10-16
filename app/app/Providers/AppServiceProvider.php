<?php

namespace App\Providers;

use App\Mail\CustomMail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
        Notification::extend('smsir', function ($app) {
            return new \App\Channels\SmsIrChannel();
        });
        Notification::extend('smsmelli', function ($app) {
            return new \App\Channels\SmsMelliChannel();
        });
        Notification::extend('avanak', function ($app) {
            return new \App\Channels\AvanakChannel();
        });
        Notification::extend('fcm', function ($app) {
            return new \App\Channels\FCMChannel();
        });
        if ($this->app->environment('production') || $this->app->environment('test')) {
            \URL::forceScheme('https');
        }
        Queue::failing(function (JobFailed $event) {
            Mail::to('cc2com.com@gmail.com')->send(new CustomMail('Job Failed - Poulpal Charge', $event->exception->getMessage()));
        });
        Collection::macro('paginate', function ($perPage, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
            return new LengthAwarePaginator(
                $this->forPage($page, $perPage), // $items
                $this->count(),                  // $total
                $perPage,
                $page,
                [                                // $options
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
    }
}
