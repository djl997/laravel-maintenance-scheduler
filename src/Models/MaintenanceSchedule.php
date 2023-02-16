<?php

namespace Djl997\LaravelMaintenanceScheduler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MaintenanceSchedule extends Model
{
    const STATUS_CONCEPT = 'concept';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $table = 'maintenance_schedule';
    protected $casts = [ 
        'changelog' => 'json',
        'maintenance_at' => 'datetime'
    ];
    protected $fillable = [ 'status' ];


    /**
     * Static functions
     */
    public static function getCurrentVersion(): self
    {
        try {
            return Cache::rememberForever('maintenance-scheduler-version', function() { 
                return self::where('status', MaintenanceSchedule::STATUS_COMPLETED)->orderByDesc('maintenance_at')->first();
            });
        } catch(\Illuminate\Database\QueryException $e) {
            $version = self::initialVersion();

            $maintenance = (new MaintenanceSchedule);
            $maintenance->major = $version['major'];
            $maintenance->minor = $version['minor'];
            $maintenance->patch = $version['patch'];

            return $maintenance;
        }
    }

    public static function getNextMinorVersion($absolute = true): array
    {
        if($absolute) {
            $latestVersion = MaintenanceSchedule::orderByDesc('maintenance_at')->orderByDesc('id')->notFailed()->first();
        } else {
            $latestVersion = self::getCurrentVersion();
        }

        if(is_null($latestVersion)) {
            return self::initialVersion();
        } 
        
        return [
            'major' => $latestVersion->major,
            'minor' => ++ $latestVersion->minor,
            'patch' => self::initialVersion()['patch'],
        ];
    }

    public static function getNextPatchVersion($absolute = true): array
    {
        if($absolute) {
            $latestVersion = MaintenanceSchedule::orderByDesc('maintenance_at')->orderByDesc('id')->notFailed()->first();
        } else {
            $latestVersion = self::getCurrentVersion();
        }

        if(is_null($latestVersion)) {
            return self::initialVersion();
        } 
        
        return [
            'major' => $latestVersion->major,
            'minor' => $latestVersion->minor,
            'patch' => ++ $latestVersion->patch,
        ];
    }

    public static function getMaintenanceMessage(): string|null
    {
        try {
            $activeMaintenance = self::active()->first();
        } catch(\Illuminate\Database\QueryException $e) {
            return null;
        }
        
        if(is_null($activeMaintenance)) {
            $firstScheduledMaintenance = self::scheduled()->soon()->orderBy('maintenance_at')->first();

            if(is_null($firstScheduledMaintenance)) {
                return null;
            }

            if($firstScheduledMaintenance->maintenance_at->addMinutes($firstScheduledMaintenance->duration_in_minutes / 2)->isPast()) {
                return __('maintenance_scheduler::messages.maintenance_at_past_due', [
                    'app' => env('APP_NAME'),
                    'minutes' => \Carbon\CarbonInterval::minutes($firstScheduledMaintenance->duration_in_minutes)->cascade()->forHumans()
                ]);
            }

            return __('maintenance_scheduler::messages.maintenance_at', [
                'day' => $firstScheduledMaintenance->maintenance_at->translatedFormat(__('maintenance_scheduler::messages.day_format')),
                'hour' => $firstScheduledMaintenance->maintenance_at->format(__('maintenance_scheduler::messages.time_format')),
                'app' => env('APP_NAME'),
                'minutes' => \Carbon\CarbonInterval::minutes($firstScheduledMaintenance->duration_in_minutes)->cascade()->forHumans()
            ]);
        }

        $diff = $activeMaintenance->duration_in_minutes - $activeMaintenance->maintenance_at->diffInMinutes();

        if($diff < 0) {
            return __('maintenance_scheduler::messages.maintenance_taking_longer_than_scheduled', [
                'app' => env('APP_NAME'),
                'minutes' => \Carbon\CarbonInterval::minutes($activeMaintenance->duration_in_minutes / 2)->cascade()->forHumans()
            ]);
        }

        return __('maintenance_scheduler::messages.releasing_now', [
            'app' => env('APP_NAME'),
            'minutes' => \Carbon\CarbonInterval::minutes($diff)->cascade()->forHumans()
        ]);
    }

    public static function recalculateVersions(): void
    {
        $initialVersion = self::initialVersion();

        $major = $initialVersion['major'];

        MaintenanceSchedule::all()->sortBy('maintenance_at')->groupBy('major')->each(function($minorVersions) use (&$major, $initialVersion) {
            $minor = $major === $initialVersion['major'] ? $initialVersion['minor'] : 0;

            $minorVersions->sortBy('maintenance_at')->groupBy('minor')->each(function($patchVersions) use ($major, &$minor, $initialVersion) {
                $patch = $minor === $initialVersion['minor'] ? $initialVersion['patch'] : 0;

                $patchVersions->sortBy('maintenance_at')->each(function($patchVersion) use ($major, $minor, &$patch) {
                    $patchVersion->major = $major;
                    $patchVersion->minor = $minor;
                    $patchVersion->patch = $patch;
                    $patchVersion->saveQuietly();

                    $patch = ++ $patch; // patch can be string
                });

                $minor++;
            });

            $major++;
        });

        Cache::forget('maintenance-scheduler-version');
    }

    private static function initialVersion(): array
    {
        return [
            'major' => config('maintenance-scheduler.major'),
            'minor' => config('maintenance-scheduler.minor'),
            'patch' => config('maintenance-scheduler.patch'),
        ];
    }

    /**
     * Scopes
     */
    public static function scopeScheduled($query)
    {
        return $query->where('status', MaintenanceSchedule::STATUS_SCHEDULED);
    }

    public static function scopeSoon($query)
    {
        return $query->whereBetween('maintenance_at', [
            now()->startOfDay(),
            now()->addDays(config('maintenance-scheduler.soonInDays'))->endOfDay()
        ]);
    }

    public static function scopeToday($query)
    {
        return $query->whereBetween('maintenance_at', [now()->startOfDay(), now()->endOfDay()]);
    }

    public static function scopeActive($query)
    {
        return $query->where('status', MaintenanceSchedule::STATUS_ACTIVE);
    }
    public function scopeCompleted($query)
    {
        $query->where('status', MaintenanceSchedule::STATUS_COMPLETED);
    }
    public function scopeNotFailed($query)
    {
        $query->whereNotIn('status', [MaintenanceSchedule::STATUS_CONCEPT, MaintenanceSchedule::STATUS_CANCELLED]);
    }

    /**
     * Attributes
     */
    public function getVersionAttribute()
    {
        return implode('.', [$this->major, $this->minor, $this->patch]);
    }
}