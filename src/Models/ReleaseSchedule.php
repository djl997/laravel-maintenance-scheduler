<?php

namespace Djl997\LaravelReleaseScheduler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ReleaseSchedule extends Model
{
    const STATUS_CONCEPT = 'concept';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    protected $table = 'release_schedule';
    protected $casts = [ 
        'changelog' => 'json',
        'release_at' => 'datetime'
    ];
    protected $fillable = [ 'status' ];


    /**
     * Static functions
     */
    public static function getCurrentVersion(): self
    {
        try {
            return Cache::rememberForever('release-scheduler-version', function() { 
                return self::where('status', ReleaseSchedule::STATUS_COMPLETED)->orderByDesc('release_at')->first();
            });
        } catch(\Illuminate\Database\QueryException $e) {
            $version = self::initialVersion();

            $release = (new ReleaseSchedule);
            $release->major = $version['major'];
            $release->minor = $version['minor'];
            $release->patch = $version['patch'];

            return $release;
        }
    }

    public static function getNextMinorVersion($absolute = true): array
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

    public static function getNextPatchVersion($absolute = true): array
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
        try {
            $activeRelease = self::active()->first();
        } catch(\Illuminate\Database\QueryException $e) {
            return null;
        }
        
        if(is_null($activeRelease)) {
            $firstScheduledRelease = self::scheduled()->soon()->orderBy('release_at')->first();

            if(is_null($firstScheduledRelease)) {
                return null;
            }

            if($firstScheduledRelease->release_at->addMinutes($firstScheduledRelease->duration_in_minutes / 2)->isPast()) {
                return __('release_scheduler::messages.release_at_past_due', [
                    'app' => env('APP_NAME'),
                    'minutes' => \Carbon\CarbonInterval::minutes($firstScheduledRelease->duration_in_minutes)->cascade()->forHumans()
                ]);
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

    public static function recalculateVersions(): void
    {
        $initialVersion = self::initialVersion();

        $major = $initialVersion['major'];

        ReleaseSchedule::all()->sortBy('release_at')->groupBy('major')->each(function($minorVersions) use (&$major, $initialVersion) {
            $minor = $major === $initialVersion['major'] ? $initialVersion['minor'] : 0;

            $minorVersions->sortBy('release_at')->groupBy('minor')->each(function($patchVersions) use ($major, &$minor, $initialVersion) {
                $patch = $minor === $initialVersion['minor'] ? $initialVersion['patch'] : 0;

                $patchVersions->sortBy('release_at')->each(function($patchVersion) use ($major, $minor, &$patch) {
                    $patchVersion->major = $major;
                    $patchVersion->minor = $minor;
                    $patchVersion->patch = $patch;
                    $patchVersion->save();

                    $patch = ++ $patch; // patch can be string
                });

                $minor++;
            });

            $major++;
        });

        Cache::forget('release-scheduler-version');
    }

    private static function initialVersion(): array
    {
        return [
            'major' => config('release-scheduler.major'),
            'minor' => config('release-scheduler.minor'),
            'patch' => config('release-scheduler.patch'),
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
        return $query->whereBetween('release_at', [
            now()->startOfDay(),
            now()->addDays(config('release-scheduler.soonInDays'))->endOfDay()
        ]);
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