<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        foreach (static::getRecordedEvents() as $event) {
            static::$event(function ($model) use ($event) {
                static::logActivity($model, $event);
            });
        }
    }

    protected static function getRecordedEvents(): array
    {
        return [
            'created',
            'updated',
            'deleted',
        ];
    }

    protected static function logActivity(Model $model, string $event): void
    {
        $user = Auth::user();
        $ip = Request::ip();
        $userAgent = Request::userAgent();

        ActivityLog::create([
            'user_id' => $user?->id,
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'event' => $event,
            'old_values' => $event === 'updated' ? $model->getOriginal() : null,
            'new_values' => $event === 'updated' ? $model->getChanges() : $model->getAttributes(),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'properties' => [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'geolocation' => app(ServicioGeolocalizacion::class)->getLocation($ip),
                'route' => Request::path(),
                'method' => Request::method(),
            ],
        ]);
    }

    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    public function getActivityDescriptionForEvent($eventName): string
    {
        return class_basename($this) . " was {$eventName}";
    }

    protected function shouldLogActivity(string $event): bool
    {
        // Override this method in your model to add custom conditions
        return true;
    }

    public function getLoggedAttributes(): array
    {
        // Override this method in your model to specify which attributes to log
        return $this->getFillable();
    }

    protected function getLoggedData(string $event): array
    {
        $attributes = $this->getLoggedAttributes();

        if ($event === 'updated') {
            $changed = array_intersect_key($this->getDirty(), array_flip($attributes));
            $original = array_intersect_key($this->getOriginal(), $changed);

            return [
                'old' => $original,
                'new' => $changed,
            ];
        }

        return [
            'attributes' => array_intersect_key($this->getAttributes(), array_flip($attributes)),
        ];
    }
}
