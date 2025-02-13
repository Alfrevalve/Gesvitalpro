<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlowRequestDetected
{
    use Dispatchable, SerializesModels;

    public $duration;
    public $route;
    public $queries;
    public $timestamp;

    public function __construct(float $duration, ?string $route, array $queries)
    {
        $this->duration = $duration;
        $this->route = $route;
        $this->queries = $queries;
        $this->timestamp = now();
    }
}

class HighMemoryUsageDetected
{
    use Dispatchable, SerializesModels;

    public $memoryUsage;
    public $route;
    public $timestamp;

    public function __construct(float $memoryUsage, ?string $route)
    {
        $this->memoryUsage = $memoryUsage;
        $this->route = $route;
        $this->timestamp = now();
    }
}

class SlowQueriesDetected
{
    use Dispatchable, SerializesModels;

    public $queries;
    public $route;
    public $timestamp;

    public function __construct(array $queries, ?string $route)
    {
        $this->queries = $queries;
        $this->route = $route;
        $this->timestamp = now();
    }

    public function getSlowQueriesCount(): int
    {
        return count($this->queries);
    }

    public function getAverageQueryTime(): float
    {
        if (empty($this->queries)) {
            return 0;
        }

        $totalTime = array_sum(array_column($this->queries, 'time'));
        return round($totalTime / count($this->queries), 2);
    }

    public function getSlowestQuery(): ?array
    {
        if (empty($this->queries)) {
            return null;
        }

        return array_reduce($this->queries, function ($carry, $query) {
            if (!$carry || $query['time'] > $carry['time']) {
                return $query;
            }
            return $carry;
        });
    }
}

class LowCacheHitRateDetected
{
    use Dispatchable, SerializesModels;

    public $hitRate;
    public $hits;
    public $misses;
    public $timestamp;

    public function __construct(float $hitRate, int $hits, int $misses)
    {
        $this->hitRate = $hitRate;
        $this->hits = $hits;
        $this->misses = $misses;
        $this->timestamp = now();
    }

    public function getTotalRequests(): int
    {
        return $this->hits + $this->misses;
    }

    public function getMissRate(): float
    {
        return 100 - $this->hitRate;
    }
}

class HighSystemLoadDetected
{
    use Dispatchable, SerializesModels;

    public $cpuLoad;
    public $memoryUsage;
    public $diskUsage;
    public $timestamp;

    public function __construct(float $cpuLoad, float $memoryUsage, float $diskUsage)
    {
        $this->cpuLoad = $cpuLoad;
        $this->memoryUsage = $memoryUsage;
        $this->diskUsage = $diskUsage;
        $this->timestamp = now();
    }

    public function isCritical(): bool
    {
        return $this->cpuLoad >= 90 || 
               $this->memoryUsage >= 90 || 
               $this->diskUsage >= 90;
    }

    public function getOverloadedResources(): array
    {
        $overloaded = [];
        
        if ($this->cpuLoad >= 90) {
            $overloaded[] = 'CPU';
        }
        if ($this->memoryUsage >= 90) {
            $overloaded[] = 'Memory';
        }
        if ($this->diskUsage >= 90) {
            $overloaded[] = 'Disk';
        }

        return $overloaded;
    }
}

class DatabasePerformanceIssueDetected
{
    use Dispatchable, SerializesModels;

    public $type;
    public $details;
    public $timestamp;

    public function __construct(string $type, array $details)
    {
        $this->type = $type;
        $this->details = $details;
        $this->timestamp = now();
    }

    public function isCritical(): bool
    {
        return in_array($this->type, [
            'deadlock',
            'connection_failure',
            'replication_lag',
        ]);
    }
}

class QueuePerformanceIssueDetected
{
    use Dispatchable, SerializesModels;

    public $queueName;
    public $jobsCount;
    public $failedCount;
    public $averageWaitTime;
    public $timestamp;

    public function __construct(
        string $queueName,
        int $jobsCount,
        int $failedCount,
        float $averageWaitTime
    ) {
        $this->queueName = $queueName;
        $this->jobsCount = $jobsCount;
        $this->failedCount = $failedCount;
        $this->averageWaitTime = $averageWaitTime;
        $this->timestamp = now();
    }

    public function getFailureRate(): float
    {
        if ($this->jobsCount === 0) {
            return 0;
        }

        return round(($this->failedCount / $this->jobsCount) * 100, 2);
    }

    public function isCritical(): bool
    {
        return $this->getFailureRate() > 50 || 
               $this->averageWaitTime > 300 || // 5 minutes
               $this->jobsCount > 1000;
    }
}

class ApiPerformanceIssueDetected
{
    use Dispatchable, SerializesModels;

    public $endpoint;
    public $method;
    public $responseTime;
    public $errorRate;
    public $timestamp;

    public function __construct(
        string $endpoint,
        string $method,
        float $responseTime,
        float $errorRate
    ) {
        $this->endpoint = $endpoint;
        $this->method = $method;
        $this->responseTime = $responseTime;
        $this->errorRate = $errorRate;
        $this->timestamp = now();
    }

    public function isCritical(): bool
    {
        return $this->errorRate > 10 || $this->responseTime > 5000;
    }
}
