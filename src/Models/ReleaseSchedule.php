<?php

namespace Djl997\LaravelReleaseScheduler\Models;

use Illuminate\Database\Eloquent\Model;

class ReleaseSchedule extends Model
{
    const STATUS_CONCEPT = 'concept';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    protected $table = 'release_schedule';
    protected $dates = [ 'release_at' ];
    protected $casts = [ 
        'changelog' => 'json'
    ];
    protected $fillable = [ 'status' ];


    /**
     * Static functions
     */
    public static function getCurrentVersion()
    {
        return self::where('status', ReleaseSchedule::STATUS_COMPLETED)->orderByDesc('release_at')->first();
    }

    public static function getNextMinorVersion($absolute = true)
    {
        if($absolute) {
            $latestRelease = ReleaseSchedule::orderByDesc('release_at')->orderByDesc('id')->notFailed()->first();
        } else {
            $latestRelease = self::getCurrentVersion();
        }

        if(is_null($latestRelease)) {
            return self::initialVersion();
        } 
        
        return [
            'major' => $latestRelease->major,
            'minor' => ++ $latestRelease->minor,
            'patch' => self::initialVersion()['patch'],
        ];
    }

    public static function getNextPatchVersion($absolute = true)
    {
        if($absolute) {
            $latestRelease = ReleaseSchedule::orderByDesc('release_at')->orderByDesc('id')->notFailed()->first();
        } else {
            $latestRelease = self::getCurrentVersion();
        }

        if(is_null($latestRelease)) {
            return self::initialVersion();
        } 
        
        return [
            'major' => $latestRelease->major,
            'minor' => $latestRelease->minor,
            'patch' => ++ $latestRelease->patch,
        ];
    }

    public static function getMaintenanceMessage(): string|null
    {
        $activeRelease = self::active()->first();
        
        if(is_null($activeRelease)) {
            $firstScheduledRelease = self::scheduled()->soon()->orderBy('release_at')->first();

            if(is_null($firstScheduledRelease)) {
                return null;
            }

            return __('release_scheduler::messages.release_at', [
                'day' => $firstScheduledRelease->release_at->translatedFormat(__('release_scheduler::messages.day_format')),
                'hour' => $firstScheduledRelease->release_at->format(__('release_scheduler::messages.time_format')),
                'app' => env('APP_NAME'),
                'minutes' => \Carbon\CarbonInterval::minutes($firstScheduledRelease->duration_in_minutes)->cascade()->forHumans()
            ]);
        }

        $diff = $activeRelease->duration_in_minutes - $activeRelease->release_at->diffInMinutes();

        if($diff < 0) {
            return __('release_scheduler::messages.release_taking_longer_than_scheduled', [
                'app' => env('APP_NAME'),
                'minutes' => \Carbon\CarbonInterval::minutes($activeRelease->duration_in_minutes / 2)->cascade()->forHumans()
            ]);
        }

        return __('release_scheduler::messages.releasing_now', [
            'app' => env('APP_NAME'),
            'minutes' => \Carbon\CarbonInterval::minutes($diff)->cascade()->forHumans()
        ]);
    }

    private static function initialVersion(): array
    {
        return [
            'major' => 1,
            'minor' => 0,
            'patch' => 0,
        ];
    }

    /**
     * Scopes
     */
    public static function scopeScheduled($query)
    {
        return $query->where('status', ReleaseSchedule::STATUS_SCHEDULED);
    }

    public static function scopeSoon($query)
    {
        // TODO: make days configurable
        return $query->where('release_at', '<=', now()->addDays(7)->startOfDay());
    }

    public static function scopeToday($query)
    {
        return $query->whereBetween('release_at', [now()->startOfDay(), now()->endOfDay()]);
    }

    public static function scopeActive($query)
    {
        return $query->where('status', ReleaseSchedule::STATUS_ACTIVE);
    }
    public function scopeCompleted($query)
    {
        $query->where('status', ReleaseSchedule::STATUS_COMPLETED);
    }
    public function scopeNotFailed($query)
    {
        $query->whereNotIn('status', [ReleaseSchedule::STATUS_CONCEPT, ReleaseSchedule::STATUS_FAILED]);
    }

    /**
     * Attributes
     */
    public function getVersionAttribute()
    {
        return implode('.', [$this->major, $this->minor, $this->patch]);
    }
}