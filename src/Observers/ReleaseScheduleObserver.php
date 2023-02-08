<?php
 
namespace Djl997\LaravelReleaseScheduler\Observers;
 
use Djl997\LaravelReleaseScheduler\Models\ReleaseSchedule;
 
class ReleaseScheduleObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\ReleaseSchedule $releaseSchedule
     * @return void
     */
    public function created(ReleaseSchedule $releaseSchedule)
    {
        ReleaseSchedule::recalculateVersions();
    }
 
    /**
     * Handle the ReleaseSchedule "updated" event.
     *
     * @param  \App\Models\ReleaseSchedule  $releaseSchedule
     * @return void
     */
    public function updated(ReleaseSchedule $releaseSchedule)
    {
        ReleaseSchedule::recalculateVersions();
    }
 
    /**
     * Handle the ReleaseSchedule "deleted" event.
     *
     * @param  \App\Models\ReleaseSchedule  $releaseSchedule
     * @return void
     */
    public function deleted(ReleaseSchedule $releaseSchedule)
    {
        ReleaseSchedule::recalculateVersions();
    }
 
    /**
     * Handle the ReleaseSchedule "restored" event.
     *
     * @param  \App\Models\ReleaseSchedule  $releaseSchedule
     * @return void
     */
    public function restored(ReleaseSchedule $releaseSchedule)
    {
        ReleaseSchedule::recalculateVersions();
    }
 
    /**
     * Handle the ReleaseSchedule "forceDeleted" event.
     *
     * @param  \App\Models\ReleaseSchedule  $releaseSchedule
     * @return void
     */
    public function forceDeleted(ReleaseSchedule $releaseSchedule)
    {
        ReleaseSchedule::recalculateVersions();
    }
}