<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use App\Services\PerformanceMonitor;
use App\Services\SecurityMonitor;

class PerformanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PerformanceMonitor::class, function ($app) {
            return new PerformanceMonitor();
        });

        $this->app->singleton(SecurityMonitor::class, function ($app) {
            return new SecurityMonitor();
        });
    }

    public function boot(): void
    {
        if (!config('performance.monitoring.enabled')) {
            return;
        }

        // Monitor database queries
        if (config('performance.monitoring.log_slow_queries')) {
            DB::listen(function ($query) {
                $this->app->make(PerformanceMonitor::class)
                    ->monitorQuery($query->sql, $query->time, $query->bindings);
            });
        }

        // Monitor model events
        Event::listen(['eloquent.*'], function ($event, $models) {
            $this->app->make(PerformanceMonitor::class)
                ->monitorModelEvent($event, $models);
        });

        // Schedule periodic performance checks
        $this->schedulePerformanceChecks();

        // Initialize cache for critical data
        $this->initializeCriticalDataCache();
    }

    protected function schedulePerformanceChecks(): void
    {
        // These will be executed based on the Laravel task scheduler
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

            // Check performance metrics every 5 minutes
            $schedule->call(function () {
                $monitor = $this->app->make(PerformanceMonitor::class);
                $report = $monitor->generatePerformanceReport();

                if ($monitor->shouldShowPerformanceAlert()) {
                    // Log and notify about performance issues
                    $this->handlePerformanceAlert($report);
                }
            })->everyFiveMinutes();

            // Check security events every minute
            $schedule->call(function () {
                $this->app->make(SecurityMonitor::class)->checkSecurityEvents();
            })->everyMinute();
        });
    }

    protected function initializeCriticalDataCache(): void
    {
        if (!config('performance.monitoring.enabled')) {
            return;
        }

        $cacheDuration = config('performance.cache_duration');
        $store = cache();

        try {
            if (config('cache.default') === 'redis' && $store->supportsTags()) {
                $this->initializeTaggedCache($cacheDuration);
            } else {
                $this->initializeSimpleCache($cacheDuration);
            }
        } catch (\Exception $e) {
            logger()->error('Error initializing performance cache: ' . $e->getMessage());
        }
    }

    protected function initializeTaggedCache(array $cacheDuration): void
    {
        // Initialize cache for dashboard data with tags
        cache()->tags(['dashboard', 'critical'])->remember('critical_stats', $cacheDuration['dashboard'], function () {
            return $this->getDashboardStats();
        });

        // Initialize cache for equipment data with tags
        cache()->tags(['equipment', 'status'])->remember('equipment_summary', $cacheDuration['equipment'], function () {
            return $this->getEquipmentStats();
        });
    }

    protected function initializeSimpleCache(array $cacheDuration): void
    {
        // Initialize cache for dashboard data without tags
        cache()->remember('critical_stats', $cacheDuration['dashboard'], function () {
            return $this->getDashboardStats();
        });

        // Initialize cache for equipment data without tags
        cache()->remember('equipment_summary', $cacheDuration['equipment'], function () {
            return $this->getEquipmentStats();
        });
    }

    protected function getDashboardStats(): array
    {
        return [
            'pending_surgeries' => \App\Models\Surgery::pending()->count(),
            'equipment_status' => \App\Models\Equipment::getStatusSummary(),
            'today_visits' => \App\Models\Visita::today()->count(),
        ];
    }

    protected function getEquipmentStats(): array
    {
        return \App\Models\Equipment::with('line')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    protected function handlePerformanceAlert(array $report): void
    {
        $message = $this->app->make(PerformanceMonitor::class)->getPerformanceAlertMessage();

        // Log the alert
        logger()->channel('performance')->warning($message, $report);

        // Send notifications if configured
        if (config('performance.alerts.channels.slack')) {
            \Illuminate\Support\Facades\Notification::route('slack', config('logging.slack_webhook_url'))
                ->notify(new \App\Notifications\PerformanceAlert($message, $report));
        }

        if (config('performance.alerts.channels.email')) {
            // Send email to system administrators
            \Illuminate\Support\Facades\Notification::route('mail', config('performance.admin_email'))
                ->notify(new \App\Notifications\PerformanceAlert($message, $report));
        }
    }
}
