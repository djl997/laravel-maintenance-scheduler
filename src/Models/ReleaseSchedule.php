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

    public static function getMaintenanceMessage($dueInDays = 7)
    {
        $firstScheduledRelease = self::scheduled()->orderBy('release_at')->first();

        return __(':Day om :hour is er onderhoud gepland voor :name. De update zal naar verwachting :minutes duren.', [
            'day' => $firstScheduledRelease->release_at->translatedFormat('l j F Y'),
            'hour' => $firstScheduledRelease->release_at->format('H:i'),
            'name' => env('APP_NAME'),
            'minutes' => \Carbon\CarbonInterval::minutes($firstScheduledRelease->duration_in_minutes)->cascade()->forHumans()
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