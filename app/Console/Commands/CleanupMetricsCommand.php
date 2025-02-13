<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemMetric;
use App\Models\PerformanceMetric;
use App\Models\SystemAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CleanupMetricsCommand extends Command
{
    protected $signature = 'metrics:cleanup 
                          {--days=30 : Number of days to keep metrics}
                          {--type=all : Type of metrics to clean (all, system, performance, alerts)}
                          {--force : Force cleanup without confirmation}
                          {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Clean up old metrics from the database';

    protected $stats = [
        'system_metrics' => 0,
        'performance_metrics' => 0,
        'alerts' => 0,
        'space_freed' => 0,
    ];

    public function handle()
    {
        $this->info('Starting metrics cleanup...');
        $startTime = microtime(true);

        try {
            DB::beginTransaction();

            $days = $this->option('days');
            $type = $this->option('type');
            $dryRun = $this->option('dry-run');

            if (!$this->option('force') && !$dryRun) {
                if (!$this->confirm("This will delete metrics older than {$days} days. Continue?")) {
                    return Command::SUCCESS;
                }
            }

            $cutoffDate = Carbon::now()->subDays($days);
            $this->info("Cleaning up metrics older than: " . $cutoffDate->format('Y-m-d H:i:s'));

            // Calcular espacio usado antes de la limpieza
            $spaceBeforeCleanup = $this->calculateDatabaseSize();

            if ($type === 'all' || $type === 'system') {
                $this->cleanupSystemMetrics($cutoffDate, $dryRun);
            }

            if ($type === 'all' || $type === 'performance') {
                $this->cleanupPerformanceMetrics($cutoffDate, $dryRun);
            }

            if ($type === 'all' || $type === 'alerts') {
                $this->cleanupAlerts($cutoffDate, $dryRun);
            }

            if (!$dryRun) {
                DB::commit();
                
                // Calcular espacio liberado
                $spaceAfterCleanup = $this->calculateDatabaseSize();
                $this->stats['space_freed'] = $spaceBeforeCleanup - $spaceAfterCleanup;

                $this->logCleanupResults();
            } else {
                DB::rollBack();
                $this->info("Dry run completed. No records were actually deleted.");
            }

            $this->displayResults($startTime);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("Error during cleanup: " . $e->getMessage());
            Log::error("Metrics cleanup failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    protected function cleanupSystemMetrics(Carbon $cutoffDate, bool $dryRun): void
    {
        $query = SystemMetric::where('created_at', '<', $cutoffDate);
        $count = $query->count();

        if ($count > 0) {
            $this->info("Found {$count} system metrics to clean up");
            
            if (!$dryRun) {
                $query->delete();
                $this->stats['system_metrics'] = $count;
            }
        }
    }

    protected function cleanupPerformanceMetrics(Carbon $cutoffDate, bool $dryRun): void
    {
        $query = PerformanceMetric::where('recorded_at', '<', $cutoffDate);
        $count = $query->count();

        if ($count > 0) {
            $this->info("Found {$count} performance metrics to clean up");
            
            if (!$dryRun) {
                $query->delete();
                $this->stats['performance_metrics'] = $count;
            }
        }
    }

    protected function cleanupAlerts(Carbon $cutoffDate, bool $dryRun): void
    {
        $query = SystemAlert::where('created_at', '<', $cutoffDate)
            ->where('resolved', true);
        $count = $query->count();

        if ($count > 0) {
            $this->info("Found {$count} resolved alerts to clean up");
            
            if (!$dryRun) {
                $query->delete();
                $this->stats['alerts'] = $count;
            }
        }
    }

    protected function calculateDatabaseSize(): float
    {
        try {
            $result = DB::select(DB::raw("
                SELECT SUM(data_length + index_length) as size
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                AND table_name IN ('system_metrics', 'performance_metrics', 'system_alerts')
            "));

            return (float) ($result[0]->size ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function logCleanupResults(): void
    {
        Log::info('Metrics cleanup completed', [
            'stats' => $this->stats,
            'cleaned_at' => now()->toIso8601String(),
        ]);
    }

    protected function displayResults(float $startTime): void
    {
        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info("Cleanup completed in {$duration} seconds");
        
        $this->table(
            ['Metric Type', 'Records Cleaned'],
            [
                ['System Metrics', $this->stats['system_metrics']],
                ['Performance Metrics', $this->stats['performance_metrics']],
                ['Alerts', $this->stats['alerts']],
            ]
        );

        if ($this->stats['space_freed'] > 0) {
            $this->info(sprintf(
                "Freed approximately %.2f MB of database space",
                $this->stats['space_freed'] / 1024 / 1024
            ));
        }
    }
}
