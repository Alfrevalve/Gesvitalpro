<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DatabaseMaintenanceCommand extends Command
{
    protected $signature = 'db:maintain
                          {--analyze : Analyze tables}
                          {--optimize : Optimize tables}
                          {--repair : Repair tables}
                          {--check : Check tables}
                          {--all : Perform all maintenance tasks}
                          {--tables= : Specific tables to maintain (comma-separated)}';

    protected $description = 'Perform database maintenance tasks';

    protected $maintenanceLock = 'database-maintenance';

    public function handle()
    {
        if (!$this->checkMaintenanceMode()) {
            return Command::FAILURE;
        }

        $this->info('Starting database maintenance...');
        $startTime = microtime(true);

        try {
            // Obtener tablas a mantener
            $tables = $this->getTables();
            
            if (empty($tables)) {
                $this->error('No tables found to maintain.');
                return Command::FAILURE;
            }

            $this->info(sprintf('Found %d tables to maintain.', count($tables)));

            $stats = [
                'analyzed' => 0,
                'optimized' => 0,
                'repaired' => 0,
                'checked' => 0,
                'errors' => 0,
            ];

            foreach ($tables as $table) {
                $this->line(sprintf('Processing table: %s', $table));

                if ($this->shouldPerformTask('analyze')) {
                    $this->analyzeTable($table, $stats);
                }

                if ($this->shouldPerformTask('optimize')) {
                    $this->optimizeTable($table, $stats);
                }

                if ($this->shouldPerformTask('repair')) {
                    $this->repairTable($table, $stats);
                }

                if ($this->shouldPerformTask('check')) {
                    $this->checkTable($table, $stats);
                }
            }

            $duration = round(microtime(true) - $startTime, 2);
            $this->displayResults($stats, $duration);

            Log::info('Database maintenance completed', [
                'stats' => $stats,
                'duration' => $duration,
                'tables_processed' => count($tables),
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Maintenance failed: ' . $e->getMessage());
            Log::error('Database maintenance failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        } finally {
            $this->releaseMaintenance();
        }
    }

    protected function getTables(): array
    {
        if ($specifiedTables = $this->option('tables')) {
            return explode(',', $specifiedTables);
        }

        return Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableNames();
    }

    protected function shouldPerformTask(string $task): bool
    {
        return $this->option('all') || $this->option($task);
    }

    protected function analyzeTable(string $table, array &$stats): void
    {
        try {
            DB::statement("ANALYZE TABLE {$table}");
            $stats['analyzed']++;
            $this->info("✓ Table {$table} analyzed");
        } catch (\Exception $e) {
            $stats['errors']++;
            $this->error("Failed to analyze table {$table}: " . $e->getMessage());
            Log::error("Table analysis failed", [
                'table' => $table,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function optimizeTable(string $table, array &$stats): void
    {
        try {
            DB::statement("OPTIMIZE TABLE {$table}");
            $stats['optimized']++;
            $this->info("✓ Table {$table} optimized");
        } catch (\Exception $e) {
            $stats['errors']++;
            $this->error("Failed to optimize table {$table}: " . $e->getMessage());
            Log::error("Table optimization failed", [
                'table' => $table,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function repairTable(string $table, array &$stats): void
    {
        try {
            DB::statement("REPAIR TABLE {$table}");
            $stats['repaired']++;
            $this->info("✓ Table {$table} repaired");
        } catch (\Exception $e) {
            $stats['errors']++;
            $this->error("Failed to repair table {$table}: " . $e->getMessage());
            Log::error("Table repair failed", [
                'table' => $table,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function checkTable(string $table, array &$stats): void
    {
        try {
            $result = DB::select("CHECK TABLE {$table}");
            $status = $result[0]->Msg_text ?? '';

            if (stripos($status, 'ok') !== false) {
                $stats['checked']++;
                $this->info("✓ Table {$table} checked (OK)");
            } else {
                $stats['errors']++;
                $this->warn("! Table {$table} check result: {$status}");
            }
        } catch (\Exception $e) {
            $stats['errors']++;
            $this->error("Failed to check table {$table}: " . $e->getMessage());
            Log::error("Table check failed", [
                'table' => $table,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function displayResults(array $stats, float $duration): void
    {
        $this->newLine();
        $this->info('Maintenance completed in ' . $duration . ' seconds');
        $this->table(
            ['Task', 'Count'],
            [
                ['Tables Analyzed', $stats['analyzed']],
                ['Tables Optimized', $stats['optimized']],
                ['Tables Repaired', $stats['repaired']],
                ['Tables Checked', $stats['checked']],
                ['Errors Encountered', $stats['errors']],
            ]
        );
    }

    protected function checkMaintenanceMode(): bool
    {
        if (app()->isDownForMaintenance()) {
            $this->error('Application is in maintenance mode. Skipping database maintenance.');
            return false;
        }

        if (!cache()->add($this->maintenanceLock, true, 60)) {
            $this->error('Another maintenance process is already running.');
            return false;
        }

        return true;
    }

    protected function releaseMaintenance(): void
    {
        cache()->forget($this->maintenanceLock);
    }
}
