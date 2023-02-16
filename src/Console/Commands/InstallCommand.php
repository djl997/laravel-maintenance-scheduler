<?php

namespace Djl997\LaravelReleaseScheduler\Console\Commands;

use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;
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
    protected $signature = 'releases:install {--reset}';

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
            DB::table('release_schedule')->exists();
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

            ReleaseSchedule::truncate();
        }

        if(ReleaseSchedule::count() > 0) {
            $this->error('Initial version already exists. Aborting.');
            $this->info('Please run the `releases:install --reset` command to truncate table.');

            return Command::FAILURE;
        }

        // Create initial release 
        $release = (new ReleaseSchedule);
        $release->major = ReleaseSchedule::getNextMinorVersion()['major'];
        $release->minor = ReleaseSchedule::getNextMinorVersion()['minor'];
        $release->patch = ReleaseSchedule::getNextMinorVersion()['patch'];
        $release->release_at = now();
        $release->duration_in_minutes = 0;
        $release->description = 'Initial version';
        $release->status = ReleaseSchedule::STATUS_COMPLETED;
        $release->save();

        $this->info('Version '. $release->version .' is created!');

        return Command::SUCCESS;
    }
}