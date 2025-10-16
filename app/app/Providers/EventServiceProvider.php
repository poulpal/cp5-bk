<?php

namespace App\Providers;

use App\Models\Building;
use App\Models\BuildingUnit;
use App\Models\DepositRequest;
use App\Models\Invoice;
use App\Models\Toll;
use App\Observers\TollObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Toll::observe(TollObserver::class);
        Building::observe(\App\Observers\Accounting\BuildingObserver::class);
        BuildingUnit::observe(\App\Observers\Accounting\BuildingUnitObserver::class);
        Invoice::observe(\App\Observers\Accounting\InvoiceObserver::class);
        DepositRequest::observe(\App\Observers\Accounting\DepositRequestObserver::class);

        // listen for UploadedImage
        // Event::listen('BinshopsBlog\Events\UploadedImage', function ($event) {
        //     dispatch(new \App\Jobs\Blog\ProcessUploadedImage($event->image_filename, $event->BinshopsBlogPost, $event->source));
        // });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
