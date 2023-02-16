<?php 

namespace Djl997\LaravelMaintenanceScheduler\Listeners;

use Djl997\LaravelMaintenanceScheduler\Models\MaintenanceSchedule;
use Illuminate\Foundation\Events\MaintenanceModeEnabled;
 
class MaintenanceModeEnabledListener
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
     * @param  \App\Events\MaintenanceModeEnabled  $event
     * @return void
     */
    public function handle(MaintenanceModeEnabled $event)
    {
        try {
            $unscheduledMessage = __('Unscheduled maintenance');
            
            $maintenance = MaintenanceSchedule::scheduled()->today()->where(function($query) use ($unscheduledMessage) {
                $query->where('description', '!=', $unscheduledMessage);
                $query->orWhereNull('description');
            })->orderBy('maintenance_at')->first();
            
            if(!is_null($maintenance)) {
                $maintenance->status = MaintenanceSchedule::STATUS_ACTIVE;
                $maintenance->maintenance_at = now();
            } else {
                $nextVersion = MaintenanceSchedule::getNextPatchVersion(false);

                $maintenance = (new MaintenanceSchedule);
                $maintenance->major = $nextVersion['major'];
                $maintenance->minor = $nextVersion['minor'];
                $maintenance->patch = $nextVersion['patch'];
                $maintenance->maintenance_at = now();
                $maintenance->description = $unscheduledMessage;
                $maintenance->status = MaintenanceSchedule::STATUS_ACTIVE;

                // Recalculate scheduled patch versions 
                MaintenanceSchedule::where([
                    'major' => $maintenance->major,
                    'minor' => $maintenance->minor,
                ])->where('maintenance_at', '>', $maintenance->maintenance_at)->orderBy('maintenance_at')->get()->each(function($maintenance) use ($nextVersion) {
                    $maintenance->patch = ++ $nextVersion['patch'];
                    $maintenance->save();
                });
            }

            $maintenance->save();
        } catch(\Exception $e) {}
    }
}