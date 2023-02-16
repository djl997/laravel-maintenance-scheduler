<?php

namespace Djl997\LaravelMaintenanceScheduler\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Djl997\LaravelMaintenanceScheduler\Listeners\{
    MaintenanceModeEnabledListener,
    MaintenanceModeDisabledListener
};
use Djl997\LaravelMaintenanceScheduler\Models\MaintenanceSchedule;
use Djl997\LaravelMaintenanceScheduler\Observers\MaintenanceScheduleObserver;
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

        MaintenanceSchedule::observe(MaintenanceScheduleObserver::class);
    }
}