<?php

namespace Djl997\LaravelReleaseScheduler;

use Djl997\LaravelReleaseScheduler\Console\Commands\{
    CreateReleaseCommand,
    ListReleasesCommand,
    DeleteReleaseCommand,
};
use Djl997\LaravelReleaseScheduler\Providers\EventServiceProvider;
use Illuminate\Support\ServiceProvider;

class LaravelReleaseSchedulerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (! class_exists('ReleaseScheduleTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_release_schedule_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_release_schedule_table.php'),
            ], 'lrs-migrations');
        }

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'release_scheduler');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateReleaseCommand::class,
                ListReleasesCommand::class,
                DeleteReleaseCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('release-scheduler.php'),
            ], 'config');
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

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'release-scheduler');
    }
}