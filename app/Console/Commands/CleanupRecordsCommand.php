<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LinkCheckService;
use App\Models\BrokenLink;
use App\Models\LinkCheckHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupRecordsCommand extends Command
{
    protected $signature = 'records:cleanup 
                          {--days=30 : Number of days to keep records}
                          {--type=all : Type of records to clean (all, links, history)}
                          {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Clean up old records from the database';

    protected $linkCheckService;

    public function __construct(?LinkCheckService $linkCheckService = null)
    {
        parent::__construct();
        $this->linkCheckService = $linkCheckService;
    }

    public function handle()
    {
        $days = $this->option('days');
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');

        $this->info("Starting cleanup process...");
        $this->newLine();

        // Iniciar transacción para asegurar consistencia
        DB::beginTransaction();

        try {
            $stats = [
                'broken_links' => 0,
                'check_history' => 0,
                'system_logs' => 0,
                'notifications' => 0,
            ];

            if ($type === 'all' || $type === 'links') {
                $stats['broken_links'] = $this->cleanupBrokenLinks($days, $dryRun);
            }

            if ($type === 'all' || $type === 'history') {
                $stats['check_history'] = $this->cleanupCheckHistory($days, $dryRun);
                $stats['system_logs'] = $this->cleanupSystemLogs($days, $dryRun);
                $stats['notifications'] = $this->cleanupNotifications($days, $dryRun);
            }

            if (!$dryRun) {
                DB::commit();
                $this->logCleanupResults($stats);
            } else {
                DB::rollBack();
                $this->info("Dry run completed. No records were actually deleted.");
            }

            $this->displayResults($stats, $dryRun);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error during cleanup: " . $e->getMessage());
            Log::error("Cleanup failed: " . $e->getMessage(), [
                'exception' => $e,
                'options' => $this->options(),
            ]);

            return Command::FAILURE;
        }
    }

    protected function cleanupBrokenLinks($days, $dryRun)
    {
        $query = BrokenLink::where(function ($q) use ($days) {
            $q->where('is_fixed', true)
              ->where('fixed_at', '<', Carbon::now()->subDays($days))
              ->orWhere(function ($q) use ($days) {
                  $q->where('is_fixed', false)
                    ->where('created_at', '<', Carbon::now()->subDays($days * 2))
                    ->where('check_count', '>', 10);
              });
        });

        $count = $query->count();

        if (!$dryRun) {
            $query->delete();
        }

        $this->info("Found {$count} broken links to clean up");
        return $count;
    }

    protected function cleanupCheckHistory($days, $dryRun)
    {
        $query = LinkCheckHistory::where('checked_at', '<', Carbon::now()->subDays($days));
        $count = $query->count();

        if (!$dryRun) {
            $query->delete();
        }

        $this->info("Found {$count} check history records to clean up");
        return $count;
    }

    protected function cleanupSystemLogs($days, $dryRun)
    {
        $query = DB::table('system_logs')
            ->where('created_at', '<', Carbon::now()->subDays($days));
        
        $count = $query->count();

        if (!$dryRun) {
            $query->delete();
        }

        $this->info("Found {$count} system logs to clean up");
        return $count;
    }

    protected function cleanupNotifications($days, $dryRun)
    {
        $query = DB::table('notifications')
            ->where('created_at', '<', Carbon::now()->subDays($days));
        
        $count = $query->count();

        if (!$dryRun) {
            $query->delete();
        }

        $this->info("Found {$count} notifications to clean up");
        return $count;
    }

    protected function displayResults($stats, $dryRun)
    {
        $this->newLine();
        $this->info("Cleanup " . ($dryRun ? "would delete" : "deleted") . ":");
        $this->table(
            ['Type', 'Count'],
            collect($stats)->map(fn($count, $type) => [
                str_replace('_', ' ', ucfirst($type)),
                $count
            ])->toArray()
        );
    }

    protected function logCleanupResults($stats)
    {
        Log::info('Database cleanup completed', [
            'stats' => $stats,
            'total_cleaned' => array_sum($stats),
            'cleaned_at' => now()->toIso8601String(),
        ]);
    }

    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
