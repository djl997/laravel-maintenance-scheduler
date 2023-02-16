<?php

namespace Djl997\LaravelMaintenanceScheduler\Console\Commands;

use Djl997\LaravelMaintenanceScheduler\Models\MaintenanceSchedule;
use Illuminate\Console\Command;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class DeleteMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:delete {release : The ID of the release}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete one specific release.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $confirmed = $this->confirm('All versions will be recalculated. Are you sure to delete this release?');

        if($confirmed) {
            MaintenanceSchedule::findOrFail($this->argument('release'))->delete();

            $this->info("The release is deleted and all versions are recalculated.");
        }
        
        return Command::SUCCESS;
    }
}