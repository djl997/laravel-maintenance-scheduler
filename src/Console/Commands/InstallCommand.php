<?php

namespace Djl997\LaravelMaintenanceScheduler\Console\Commands;

use Djl997\LaravelMaintenanceScheduler\Models\MaintenanceSchedule;
use Exception;
use Illuminate\Console\Command;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:install {--reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        try { 
            DB::table('maintenance_schedule')->exists();
        } catch(\Illuminate\Database\QueryException $e) {
            $this->error('Table is not found.');
            $this->info('Did you run the migrations?');

            return Command::FAILURE;
        }

        if($this->option('reset')) {
            if (!$this->confirm('Do you want to delete all existing versions?')) {
                $this->error('Command aborted by user.');

                return Command::SUCCESS;
            } 

            MaintenanceSchedule::truncate();
        }

        if(MaintenanceSchedule::count() > 0) {
            $this->error('Initial version already exists. Aborting.');
            $this->info('Please run the `maintenance:install --reset` command to truncate table.');

            return Command::FAILURE;
        }

        // Create initial version 
        $maintenance = (new MaintenanceSchedule);
        $maintenance->major = MaintenanceSchedule::getNextMinorVersion()['major'];
        $maintenance->minor = MaintenanceSchedule::getNextMinorVersion()['minor'];
        $maintenance->patch = MaintenanceSchedule::getNextMinorVersion()['patch'];
        $maintenance->maintenance_at = now();
        $maintenance->duration_in_minutes = 0;
        $maintenance->description = 'Initial version';
        $maintenance->status = MaintenanceSchedule::STATUS_COMPLETED;
        $maintenance->save();

        $this->info('Version '. $maintenance->version .' is created!');

        return Command::SUCCESS;
    }
}