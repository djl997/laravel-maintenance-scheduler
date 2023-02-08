<?php

namespace Djl997\LaravelReleaseScheduler\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Djl997\LaravelReleaseScheduler\Listeners\{
    MaintenanceModeEnabledListener,
    MaintenanceModeDisabledListener
};
use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;
use Djl997\LaravelReleaseScheduler\Observers\ReleaseScheduleObserver;
use Illuminate\Foundation\Events\{
    MaintenanceModeEnabled,
    MaintenanceModeDisabled
};

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MaintenanceModeEnabled::class => [
            MaintenanceModeEnabledListener::class,
        ],
        MaintenanceModeDisabled::class => [
            MaintenanceModeDisabledListener::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        ReleaseSchedule::observe(ReleaseScheduleObserver::class);
    }
}