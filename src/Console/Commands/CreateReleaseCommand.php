<?php

namespace Djl997\LaravelReleaseScheduler\Console\Commands;

use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;
use Illuminate\Console\Command;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class CreateReleaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'releases:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and schedule a new release including the changelog.';

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
        $release = (new ReleaseSchedule);
        $release->status = ReleaseSchedule::STATUS_CONCEPT;
        
        $versionType = $this->choice(
            'Are you making a Minor Release or a Patch?',
            ['Minor release', 'Patch'],
            0
        );

        if($versionType == 'Patch') {
            $nextVersion = ReleaseSchedule::getNextPatchVersion();
        } else {
            $nextVersion = ReleaseSchedule::getNextMinorVersion();
        }

        $release->major = $nextVersion['major'];
        $release->minor = $nextVersion['minor'];
        $release->patch = $nextVersion['patch'];

        $release->save();

        $this->info('Version '. $release->version .' is created!');

        $release->description = $this->ask('What is this release about? Please provide a short description');
        $release->release_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $this->ask('Release date (Y-m-d H:i)', now()->format('Y-m-d H:i')));
        $release->duration_in_minutes = (int) $this->choice('Duration in minutes', [
            5 => '5 minutes', 
            15 => '15 minutes', 
            30 => '30 minutes', 
            60 => '1 hour', 
            120 => '2 hours', 
            180 => '3 hours', 
            240 => '4 hours', 
            480 => '1 day',
        ], 15);

        $release->save();

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
            
            $release->changelog = $changelog;
            $release->save();
        }

        $this->table([
            'Version', 
            'Maintenance start',
            'Maintenance finished (expected)',
            'Description', 
            'Changelog', 
        ], [[
            $release->version,
            $release->release_at->format('Y-m-d H:i'),
            $release->release_at->addMinutes($release->duration_in_minutes)->format('Y-m-d H:i'),
            $release->description,
            collect($release->changelog)->map(function($item, $key) { 
                return ucfirst($key). ': '. implode("\n".ucfirst($key).": ", $item); 
            })->join("\n")
        ]]);

        if (!$this->confirm('Do you wish to schedule this version release?', true)) {
            $this->info('Version '. $release->version .' is saved as concept.');
            
            return Command::SUCCESS;
        }
        
        $release->status = ReleaseSchedule::STATUS_SCHEDULED;
        $release->save();

        $this->info("Version $release->version is scheduled! ID: $release->id.");

        return Command::SUCCESS;
    }
}