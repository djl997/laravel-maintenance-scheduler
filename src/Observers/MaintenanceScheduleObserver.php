<?php
 
namespace Djl997\LaravelMaintenanceScheduler\Observers;
 
use Djl997\LaravelMaintenanceScheduler\Models\MaintenanceSchedule;
 
class MaintenanceScheduleObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\MaintenanceSchedule $maintenanceSchedule
     * @return void
     */
    public function created(MaintenanceSchedule $maintenanceSchedule)
    {
        MaintenanceSchedule::recalculateVersions();
    }
 
    /**
     * Handle the MaintenanceSchedule "updated" event.
     *
     * @param  \App\Models\MaintenanceSchedule  $maintenanceSchedule
     * @return void
     */
    public function updated(MaintenanceSchedule $maintenanceSchedule)
    {
        MaintenanceSchedule::recalculateVersions();
    }
 
    /**
     * Handle the MaintenanceSchedule "deleted" event.
     *
     * @param  \App\Models\MaintenanceSchedule  $maintenanceSchedule
     * @return void
     */
    public function deleted(MaintenanceSchedule $maintenanceSchedule)
    {
        MaintenanceSchedule::recalculateVersions();
    }
 
    /**
     * Handle the MaintenanceSchedule "restored" event.
     *
     * @param  \App\Models\MaintenanceSchedule  $maintenanceSchedule
     * @return void
     */
    public function restored(MaintenanceSchedule $maintenanceSchedule)
    {
        MaintenanceSchedule::recalculateVersions();
    }
 
    /**
     * Handle the MaintenanceSchedule "forceDeleted" event.
     *
     * @param  \App\Models\MaintenanceSchedule  $maintenanceSchedule
     * @return void
     */
    public function forceDeleted(MaintenanceSchedule $maintenanceSchedule)
    {
        MaintenanceSchedule::recalculateVersions();
    }
}