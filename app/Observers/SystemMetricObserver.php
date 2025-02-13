<?php

namespace App\Observers;

use App\Models\SystemMetric;

class SystemMetricObserver
{
    /**
     * Handle the SystemMetric "created" event.
     */
    public function created(SystemMetric $systemMetric): void
    {
        // Log the creation of new system metrics
        \Log::info('New system metric recorded', [
            'metric' => $systemMetric->name,
            'value' => $systemMetric->value,
            'timestamp' => $systemMetric->created_at
        ]);
    }

    /**
     * Handle the SystemMetric "updated" event.
     */
    public function updated(SystemMetric $systemMetric): void
    {
        // Log significant changes in system metrics
        if ($systemMetric->isDirty('value')) {
            \Log::info('System metric updated', [
                'metric' => $systemMetric->name,
                'old_value' => $systemMetric->getOriginal('value'),
                'new_value' => $systemMetric->value,
                'timestamp' => $systemMetric->updated_at
            ]);
        }
    }

    /**
     * Handle the SystemMetric "deleted" event.
     */
    public function deleted(SystemMetric $systemMetric): void
    {
        // Log when a system metric is deleted
        \Log::info('System metric deleted', [
            'metric' => $systemMetric->name,
            'last_value' => $systemMetric->value,
            'timestamp' => now()
        ]);
    }
}
