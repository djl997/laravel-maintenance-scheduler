<?php 

namespace Djl997\LaravelReleaseScheduler\Listeners;

use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;
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
            
            $release = ReleaseSchedule::scheduled()->today()->where(function($query) use ($unscheduledMessage) {
                $query->where('description', '!=', $unscheduledMessage);
                $query->orWhereNull('description');
            })->orderBy('release_at')->first();
            
            if(!is_null($release)) {
                $release->status = ReleaseSchedule::STATUS_ACTIVE;
                $release->release_at = now();
            } else {
                $nextVersion = ReleaseSchedule::getNextPatchVersion(false);

                $release = (new ReleaseSchedule);
                $release->major = $nextVersion['major'];
                $release->minor = $nextVersion['minor'];
                $release->patch = $nextVersion['patch'];
                $release->release_at = now();
                $release->description = $unscheduledMessage;
                $release->status = ReleaseSchedule::STATUS_ACTIVE;

                // Recalculate scheduled patch releases 
                ReleaseSchedule::where([
                    'major' => $release->major,
                    'minor' => $release->minor,
                ])->where('release_at', '>', $release->release_at)->orderBy('release_at')->get()->each(function($release) use ($nextVersion) {
                    $release->patch = ++ $nextVersion['patch'];
                    $release->save();
                });
            }

            $release->save();
        } catch(\Exception $e) {}
    }
}