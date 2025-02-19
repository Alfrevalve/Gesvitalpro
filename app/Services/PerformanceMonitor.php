<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class PerformanceMonitor
{
    protected $metrics = [];

    public function recordMetric($name, $value)
    {
        $this->metrics[$name] = $value;

        // Log metric for monitoring
        Log::info("Performance metric recorded: {$name} = {$value}");

        // Cache metric for quick access
        Cache::put("metrics.{$name}", $value, now()->addHours(24));
    }

    public function getMetric($name)
    {
        return $this->metrics[$name] ?? null;
    }

    public function monitorQuery(string $sql, float $time, array $bindings = []): void
    {
        $this->recordMetric('database_query_time', $time);
        $this->recordMetric('database_query_count',
            ($this->getMetric('database_query_count') ?? 0) + 1
        );

        // Log slow queries
        if ($time > 1.0) { // Queries taking more than 1 second
            Log::warning('Slow database query detected', [
                'sql' => $sql,
                'bindings' => $bindings,
                'time' => $time
            ]);
        }
    }

    public function recordCacheMetrics($hit)
    {
        $hitCount = $this->getMetric('cache_hits') ?? 0;
        $missCount = $this->getMetric('cache_misses') ?? 0;

        if ($hit) {
            $this->recordMetric('cache_hits', $hitCount + 1);
        } else {
            $this->recordMetric('cache_misses', $missCount + 1);
        }

        $this->recordMetric('cache_hit_ratio',
            $hitCount / ($hitCount + $missCount)
        );
    }

    public function recordMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $this->recordMetric('memory_usage', $memoryUsage);

        if ($memoryUsage > 100 * 1024 * 1024) { // 100MB threshold
            Log::warning("High memory usage detected: {$memoryUsage} bytes");
        }
    }

    public function recordAuthenticationMetrics(array $data): void
    {
        $action = $data['action'] ?? 'unknown';
        $status = $data['status'] ?? 'unknown';
        $duration = $data['duration'] ?? 0.0;

        // Record action-specific counts
        $key = "auth_{$action}_{$status}_count";
        $this->recordMetric($key, ($this->getMetric($key) ?? 0) + 1);

        // Record overall success/failure counts
        if ($status === 'success') {
            $this->recordMetric('auth_success_count',
                ($this->getMetric('auth_success_count') ?? 0) + 1
            );
        } elseif ($status === 'failed' || $status === 'error') {
            $this->recordMetric('auth_failure_count',
                ($this->getMetric('auth_failure_count') ?? 0) + 1
            );
        }

        // Update average authentication time for login attempts
        if ($action === 'login_attempt' && isset($data['duration'])) {
            $totalCount = ($this->getMetric('auth_attempt_count') ?? 0) + 1;
            $this->recordMetric('auth_attempt_count', $totalCount);

            $currentAverage = $this->getMetric('auth_average_time') ?? 0;
            $newAverage = (($currentAverage * ($totalCount - 1)) + $duration) / $totalCount;
            $this->recordMetric('auth_average_time', $newAverage);

            // Log slow authentication attempts
            if ($duration > 1.0) {
                Log::warning("Slow authentication detected", [
                    'duration' => $duration,
                    'action' => $action,
                    'status' => $status,
                    'ip' => $data['ip'] ?? 'unknown'
                ]);
            }
        }

        // Create activity log entry
        \App\Models\ActivityLog::create([
            'action' => $action,
            'loggable_type' => \App\Models\User::class,
            'loggable_id' => $data['user_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'changes' => array_filter($data, function ($key) {
                return !in_array($key, ['password']); // Exclude sensitive data
            }, ARRAY_FILTER_USE_KEY),
            'original' => [],
            'ip_address' => $data['ip'] ?? request()->ip(),
            'user_agent' => request()->userAgent(),
            'event' => $status,
            'properties' => [
                'duration' => $duration,
                'email' => $data['email'] ?? null,
                'status' => $status
            ]
        ]);

        // Log authentication events
        Log::info("Authentication event recorded", array_merge(
            ['action' => $action, 'status' => $status],
            array_filter($data, function ($key) {
                return !in_array($key, ['password']); // Exclude sensitive data
            }, ARRAY_FILTER_USE_KEY)
        ));
    }

    public function monitorModelEvent(string $event, array $models): void
    {
        $eventType = str_replace('eloquent.', '', $event);
        $modelName = get_class($models[0] ?? null);

        // Record event count
        $key = "model_event_{$eventType}";
        $this->recordMetric($key, ($this->getMetric($key) ?? 0) + 1);

        // Log significant model events
        Log::info("Model event: {$eventType}", [
            'model' => $modelName,
            'count' => count($models)
        ]);
    }

    public function generatePerformanceReport(): array
    {
        return [
            'database' => [
                'query_time' => $this->getMetric('database_query_time'),
                'query_count' => $this->getMetric('database_query_count')
            ],
            'cache' => [
                'hits' => $this->getMetric('cache_hits'),
                'misses' => $this->getMetric('cache_misses'),
                'hit_ratio' => $this->getMetric('cache_hit_ratio')
            ],
            'memory' => [
                'usage' => $this->getMetric('memory_usage')
            ],
            'authentication' => [
                'success_count' => $this->getMetric('auth_success_count'),
                'failure_count' => $this->getMetric('auth_failure_count'),
                'average_time' => $this->getMetric('auth_average_time')
            ]
        ];
    }

    public function shouldShowPerformanceAlert(): bool
    {
        $thresholds = [
            'database_query_time' => 1.0, // Alert if average query time > 1 second
            'memory_usage' => 100 * 1024 * 1024, // Alert if memory usage > 100MB
            'cache_hit_ratio' => 0.5 // Alert if cache hit ratio < 50%
        ];

        foreach ($thresholds as $metric => $threshold) {
            $value = $this->getMetric($metric);
            if ($value === null) continue;

            if ($metric === 'cache_hit_ratio' && $value < $threshold) {
                return true;
            } elseif ($value > $threshold) {
                return true;
            }
        }

        return false;
    }

    public function getPerformanceAlertMessage(): string
    {
        $alerts = [];

        if (($queryTime = $this->getMetric('database_query_time')) > 1.0) {
            $alerts[] = "High average query time: {$queryTime}s";
        }

        if (($memoryUsage = $this->getMetric('memory_usage')) > 100 * 1024 * 1024) {
            $alerts[] = "High memory usage: " . number_format($memoryUsage / 1024 / 1024, 2) . "MB";
        }

        if (($hitRatio = $this->getMetric('cache_hit_ratio')) < 0.5) {
            $alerts[] = "Low cache hit ratio: " . number_format($hitRatio * 100, 1) . "%";
        }

        return empty($alerts)
            ? "No performance issues detected"
            : "Performance Alert: " . implode(", ", $alerts);
    }
}
