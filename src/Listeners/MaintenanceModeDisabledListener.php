<?php 

namespace Djl997\LaravelReleaseScheduler\Listeners;

use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;
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
            $releases = ReleaseSchedule::active()->get();

            foreach($releases as $release) {
                $release->status = ReleaseSchedule::STATUS_COMPLETED;
                $release->duration_in_minutes = $release->release_at->diffInMinutes();
                $release->save();
            }
        } catch(\Exception $e) {}
    }
}