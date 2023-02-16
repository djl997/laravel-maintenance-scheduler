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
    protected $description = 'List all maintenance in the past, present or future.';

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
        $releases = MaintenanceSchedule::orderByDesc('maintenance_at')->get();

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
                $release->maintenance_at->format('Y-m-d H:i'),
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