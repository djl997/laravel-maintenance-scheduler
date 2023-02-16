<?php 

namespace Djl997\LaravelMaintenanceScheduler\Listeners;

use Djl997\LaravelMaintenanceScheduler\Models\MaintenanceSchedule;
use Illuminate\Foundation\Events\MaintenanceModeDisabled;
 
class MaintenanceModeDisabledListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
 
    /**
     * Handle the event.
     *
     * @param  \App\Events\MaintenanceModeDisabled  $event
     * @return void
     */
    public function handle(MaintenanceModeDisabled $event)
    {
        try {
            $maintenances = MaintenanceSchedule::active()->get();

            foreach($maintenances as $maintenance) {
                $maintenance->status = MaintenanceSchedule::STATUS_COMPLETED;
                $maintenance->duration_in_minutes = $maintenance->maintenance_at->diffInMinutes();
                $maintenance->save();
            }
        } catch(\Exception $e) {}
    }
}