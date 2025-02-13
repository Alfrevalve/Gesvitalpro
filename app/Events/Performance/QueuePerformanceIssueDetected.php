<?php

namespace App\Events\Performance;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
