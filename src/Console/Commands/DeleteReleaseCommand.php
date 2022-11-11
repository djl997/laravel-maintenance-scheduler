<?php

namespace Djl997\LaravelReleaseScheduler\Console\Commands;

use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;
use Illuminate\Console\Command;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class DeleteReleaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'releases:delete {release : The ID of the release}';

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
        $release = ReleaseSchedule::find($this->argument('release'))->delete();
                
        $this->info("The release is deleted!");

        return Command::SUCCESS;
    }
}