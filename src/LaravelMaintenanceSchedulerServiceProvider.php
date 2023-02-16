<?php

namespace Djl997\LaravelMaintenanceScheduler;

use Djl997\LaravelMaintenanceScheduler\Console\Commands\{
    InstallCommand,
    CreateMaintenanceCommand,
    ListMaintenancesCommand,
    DeleteMaintenanceCommand,
    RecalculateVersionsCommand,
};
use Djl997\LaravelMaintenanceScheduler\Providers\EventServiceProvider;
use Illuminate\Support\ServiceProvider;

class LaravelMaintenanceSchedulerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (! class_exists('MaintenanceScheduleTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_maintenance_schedule_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_maintenance_schedule_table.php'),
            ], 'maintenance-migrations');
        }

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'maintenance_scheduler');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                CreateMaintenanceCommand::class,
                ListMaintenancesCommand::class,
                DeleteMaintenanceCommand::class,
                RecalculateVersionsCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('maintenance-scheduler.php'),
            ], 'maintenance-config');
        }

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(EventServiceProvider::class);

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'maintenance-scheduler');
    }
}