<?php

namespace Djl997\LaravelMaintenanceScheduler\Console\Commands;

use Djl997\LaravelMaintenanceScheduler\Models\MaintenanceSchedule;
use Illuminate\Console\Command;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class CreateMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and schedule a new maintenance time including the changelog.';

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
        $maintenance = (new MaintenanceSchedule);
        $maintenance->status = MaintenanceSchedule::STATUS_CONCEPT;
        
        $versionType = $this->choice(
            'Are you making a Minor Release or a Patch?',
            ['Minor release', 'Patch'],
            0
        );

        if($versionType == 'Patch') {
            $nextVersion = MaintenanceSchedule::getNextPatchVersion();
        } else {
            $nextVersion = MaintenanceSchedule::getNextMinorVersion();
        }

        $maintenance->major = $nextVersion['major'];
        $maintenance->minor = $nextVersion['minor'];
        $maintenance->patch = $nextVersion['patch'];
        
        $maintenance->save();

        $this->info('Version '. $maintenance->version .' is created!');

        $minutes = (int) now()->format('i') + 2;
        $minutes = 15 + $minutes - $minutes % 15;
        $date = now()->startOfHour()->addMinutes($minutes)->format('Y-m-d H:i');

        $maintenance->description = $this->ask('What is this release about? Please provide a short description');
        $maintenance->maintenance_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $this->ask('Maintenance date (Y-m-d H:i)', $date));
        $maintenance->duration_in_minutes = (int) $this->ask('Duration in minutes', 15);

        try {
            $maintenance->save();
        } catch(\Illuminate\Database\QueryException $e) {
            $maintenance->delete();

            $this->error('You have already scheduled a release for this time and date. Aborting.');

            return Command::FAILURE;
        }

        if ($this->confirm('Do you want to create the changelog interactively?', false)) {
            $this->info("<bg=white>Please divide multiple items with vertical pipe without spaces. E.g. 'Functionality to create an user|Functionality to change password'</>");
            $this->info("<bg=white>Please skip if something does not apply.</>");

            $changelog = [];

            $added = $this->ask('Added feature(s)');
            $changed = $this->ask('Changed feature(s)');
            $depricated = $this->ask('Depricated feature(s)');
            $removed = $this->ask('Removed feature(s)');
            $bugfixes = $this->ask('Bugfix(es)');
            $security = $this->ask('Solved security vulnerabilities');

            $changelog = collect([
                'added' => Str::of($added)->explode('|'),
                'changed' => Str::of($changed)->explode('|'),
                'depricated' => Str::of($depricated)->explode('|'),
                'removed' => Str::of($removed)->explode('|'),
                'bugfixes' => Str::of($bugfixes)->explode('|'),
                'security' => Str::of($security)->explode('|'),
            ])->filter(function($item) {
                return !empty($item->join(''));
            })->toArray();
            
            $maintenance->changelog = $changelog;
            $maintenance->save();
        }

        $this->table([
            'Version', 
            'Maintenance start',
            'Maintenance finished (expected)',
            'Description', 
            'Changelog', 
        ], [[
            $maintenance->version,
            $maintenance->maintenance_at->format('Y-m-d H:i'),
            $maintenance->maintenance_at->addMinutes($maintenance->duration_in_minutes)->format('Y-m-d H:i'),
            $maintenance->description,
            collect($maintenance->changelog)->map(function($item, $key) { 
                return ucfirst($key). ': '. implode("\n".ucfirst($key).": ", $item); 
            })->join("\n")
        ]]);

        if (!$this->confirm('Do you wish to schedule this version release?', true)) {
            $this->info('Version '. $maintenance->version .' is saved as concept.');
            
            return Command::SUCCESS;
        }
        
        $maintenance->status = MaintenanceSchedule::STATUS_SCHEDULED;
        $maintenance->save();

        $this->info("Version $maintenance->version is scheduled! ID: $maintenance->id.");

        return Command::SUCCESS;
    }
}