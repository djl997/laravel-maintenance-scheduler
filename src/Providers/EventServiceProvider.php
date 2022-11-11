<?php

namespace Djl997\LaravelReleaseScheduler\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Djl997\LaravelReleaseScheduler\Listeners\MaintenanceModeEnabledListener;
use Djl997\LaravelReleaseScheduler\Listeners\MaintenanceModeDisabledListener;
use Illuminate\Foundation\Events\MaintenanceModeEnabled;
use Illuminate\Foundation\Events\MaintenanceModeDisabled;

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
    }
}