<?php

namespace Djl997\LaravelReleaseScheduler\Console\Commands;

use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;
use Illuminate\Console\Command;

class ListReleasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'releases:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all releases in the past, present or future.';

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
        $releases = ReleaseSchedule::orderByDesc('release_at')->get();

        $statusColors = [
            'concept' => 'gray',
            'scheduled' => 'gray',
            'active' => 'yellow',
            'completed' => 'green',
            'failed' => 'red',
        ];

        $releaseData = $releases->map(function($release) use ($statusColors) {
            $color = $statusColors[$release->status];
            
            return [
                $release->id,
                $release->version,
                $release->release_at->format('Y-m-d H:i'),
                "<fg=$color>$release->status</>",
                $release->description,
            ];
        })->toArray();

        $this->table([
            'ID', 
            'Version', 
            'Date', 
            'Status',
            'Description', 
        ], $releaseData);

        return Command::SUCCESS;
    }
}