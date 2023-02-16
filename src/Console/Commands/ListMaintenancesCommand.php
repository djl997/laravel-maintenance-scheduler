<?php

namespace Djl997\LaravelMaintenanceScheduler\Console\Commands;

use Djl997\LaravelMaintenanceScheduler\Models\MaintenanceSchedule;
use Illuminate\Console\Command;

class ListMaintenancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all versions in the past, present or future.';

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
        $versions = MaintenanceSchedule::orderByDesc('maintenance_at')->get();

        $statusColors = [
            'concept' => 'gray',
            'scheduled' => 'gray',
            'active' => 'yellow',
            'completed' => 'green',
            'failed' => 'red',
        ];

        $versionData = $versions->map(function($version) use ($statusColors) {
            $color = $statusColors[$version->status];
            
            return [
                $version->id,
                $version->version,
                $version->maintenance_at->format('Y-m-d H:i'),
                "<fg=$color>$version->status</>",
                $version->description,
            ];
        })->toArray();

        $this->table([
            'ID', 
            'Version', 
            'Date', 
            'Status',
            'Description', 
        ], $versionData);

        return Command::SUCCESS;
    }
}