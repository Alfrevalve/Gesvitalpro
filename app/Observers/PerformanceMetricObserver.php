<?php

namespace App\Observers;

use App\Models\PerformanceMetric;

class PerformanceMetricObserver
{
    /**
     * Handle the PerformanceMetric "created" event.
     */
    public function created(PerformanceMetric $performanceMetric): void
    {
        // Only log creation of significant metrics
        if ($this->isSignificantMetric($performanceMetric)) {
            \Log::info('Significant performance metric recorded', [
                'type' => $performanceMetric->type,
                'name' => $performanceMetric->name,
                'value' => $performanceMetric->value,
                'unit' => $performanceMetric->unit,
                'timestamp' => $performanceMetric->recorded_at
            ]);
        }
    }

    /**
     * Handle the PerformanceMetric "updated" event.
     */
    public function updated(PerformanceMetric $performanceMetric): void
    {
        // Only log significant changes
        if ($performanceMetric->isDirty(['value']) && $this->isSignificantMetric($performanceMetric)) {
            \Log::info('Significant performance metric updated', [
                'type' => $performanceMetric->type,
                'name' => $performanceMetric->name,
                'old_value' => $performanceMetric->getOriginal('value'),
                'new_value' => $performanceMetric->value,
                'timestamp' => $performanceMetric->updated_at
            ]);
        }
    }

    /**
     * Handle the PerformanceMetric "deleted" event.
     */
    public function deleted(PerformanceMetric $performanceMetric): void
    {
        // Only log deletion of significant metrics
        if ($this->isSignificantMetric($performanceMetric)) {
            \Log::info('Significant performance metric deleted', [
                'type' => $performanceMetric->type,
                'name' => $performanceMetric->name,
                'last_value' => $performanceMetric->value,
                'timestamp' => now()
            ]);
        }
    }

    /**
     * Determine if a metric is significant enough to log
     */
    private function isSignificantMetric(PerformanceMetric $metric): bool
    {
        // Define significant metric types
        $significantTypes = [
            'error',
            'critical',
            'security',
            'threshold_exceeded'
        ];

        return in_array($metric->type, $significantTypes);
    }
}
