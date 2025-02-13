<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class LogThresholdExceeded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $threshold;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(array $data, int $threshold = null)
    {
        $this->data = $data;
        $this->threshold = $threshold ?? config('logging.monitoring.alert_threshold', 100);
        $this->timestamp = now();
    }

    /**
     * Get the error count
     */
    public function getErrorCount(): int
    {
        return $this->data['summary']['errors'] ?? 0;
    }

    /**
     * Get the critical count
     */
    public function getCriticalCount(): int
    {
        return $this->data['summary']['critical'] ?? 0;
    }

    /**
     * Get the warning count
     */
    public function getWarningCount(): int
    {
        return $this->data['summary']['warnings'] ?? 0;
    }

    /**
     * Get the total entries count
     */
    public function getTotalCount(): int
    {
        return $this->data['summary']['total_entries'] ?? 0;
    }

    /**
     * Get the error rate
     */
    public function getErrorRate(): float
    {
        $total = $this->getTotalCount();
        if ($total === 0) {
            return 0;
        }

        return round(($this->getErrorCount() / $total) * 100, 2);
    }

    /**
     * Get the period covered by the data
     */
    public function getPeriod(): string
    {
        return $this->data['period'] ?? 'unknown';
    }

    /**
     * Get error details
     */
    public function getErrorDetails(): array
    {
        return $this->data['error_details'] ?? [];
    }

    /**
     * Get the most frequent error types
     */
    public function getMostFrequentErrors(int $limit = 5): array
    {
        $errors = [];
        foreach ($this->getErrorDetails() as $type => $typeErrors) {
            foreach ($typeErrors as $error) {
                $key = $error['type'] . ': ' . $error['message'];
                if (!isset($errors[$key])) {
                    $errors[$key] = 0;
                }
                $errors[$key]++;
            }
        }

        arsort($errors);
        return array_slice($errors, 0, $limit, true);
    }

    /**
     * Get the urgency level based on error counts
     */
    public function getUrgencyLevel(): string
    {
        if ($this->getCriticalCount() > 0) {
            return 'critical';
        }

        if ($this->getErrorRate() > 50) {
            return 'high';
        }

        if ($this->getErrorRate() > 25) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get a summary of the threshold exceedance
     */
    public function getSummary(): array
    {
        return [
            'timestamp' => $this->timestamp->toIso8601String(),
            'period' => $this->getPeriod(),
            'threshold' => $this->threshold,
            'error_count' => $this->getErrorCount(),
            'critical_count' => $this->getCriticalCount(),
            'warning_count' => $this->getWarningCount(),
            'total_count' => $this->getTotalCount(),
            'error_rate' => $this->getErrorRate(),
            'urgency_level' => $this->getUrgencyLevel(),
            'most_frequent_errors' => $this->getMostFrequentErrors(),
        ];
    }

    /**
     * Get a human-readable description of the threshold exceedance
     */
    public function getDescription(): string
    {
        return sprintf(
            'Log error threshold exceeded: %d errors detected (threshold: %d). Error rate: %.2f%%',
            $this->getErrorCount(),
            $this->threshold,
            $this->getErrorRate()
        );
    }

    /**
     * Determine if this is a critical situation
     */
    public function isCritical(): bool
    {
        return $this->getUrgencyLevel() === 'critical';
    }

    /**
     * Get notification channels that should be used
     */
    public function getNotificationChannels(): array
    {
        $channels = config('logging.monitoring.notification_channels', []);

        // Asegurar que los canales críticos siempre se notifiquen
        if ($this->isCritical()) {
            $channels = array_unique(array_merge($channels, ['mail', 'slack']));
        }

        return $channels;
    }

    /**
     * Get tags for the event
     */
    public function getTags(): array
    {
        return [
            'log-threshold',
            'urgency:' . $this->getUrgencyLevel(),
            'period:' . $this->getPeriod(),
        ];
    }
}
