<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LogCleanCommand extends Command
{
    protected $signature = 'log:clean {--days=7 : Number of days to keep logs}';
    protected $description = 'Clean old log files and compress them';

    public function handle()
    {
        $this->info('Starting log cleanup process...');

        $days = $this->option('days');
        $logPath = storage_path('logs');
        $files = glob($logPath . '/*.log');
        $now = Carbon::now();
        $compressedCount = 0;
        $deletedCount = 0;
        $totalSize = 0;

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(filemtime($file));
            $fileSize = filesize($file);
            $totalSize += $fileSize;

            if ($lastModified->diffInDays($now) > $days) {
                // Comprimir archivos antiguos
                if ($fileSize > 0) {
                    $compressed = $this->compressLog($file);
                    if ($compressed) {
                        $compressedCount++;
                        unlink($file);
                        $deletedCount++;
                    }
                } else {
                    // Eliminar archivos vacíos
                    unlink($file);
                    $deletedCount++;
                }
            }
        }

        // Limpiar registros antiguos de la base de datos
        $this->cleanDatabaseLogs($days);

        $this->info("Log cleanup completed:");
        $this->line("- Files compressed: {$compressedCount}");
        $this->line("- Files deleted: {$deletedCount}");
        $this->line("- Total size processed: " . $this->formatBytes($totalSize));

        return Command::SUCCESS;
    }

    protected function compressLog(string $file): bool
    {
        try {
            $content = file_get_contents($file);
            $compressed = gzencode($content, 9);
            $compressedFile = $file . '.gz';
            file_put_contents($compressedFile, $compressed);

            // Verificar que la compresión fue exitosa
            if (file_exists($compressedFile) && filesize($compressedFile) > 0) {
                // Mover a carpeta de archivos
                $archivePath = storage_path('logs/archive');
                if (!is_dir($archivePath)) {
                    mkdir($archivePath, 0755, true);
                }

                $fileName = basename($compressedFile);
                $yearMonth = date('Y-m', filemtime($file));
                $archiveDir = $archivePath . '/' . $yearMonth;

                if (!is_dir($archiveDir)) {
                    mkdir($archiveDir, 0755, true);
                }

                rename($compressedFile, $archiveDir . '/' . $fileName);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->error("Error compressing log file {$file}: " . $e->getMessage());
            return false;
        }
    }

    protected function cleanDatabaseLogs(int $days): void
    {
        try {
            $deleted = \DB::table('system_logs')
                ->where('created_at', '<', Carbon::now()->subDays($days))
                ->delete();

            $this->info("Deleted {$deleted} old database log entries");
        } catch (\Exception $e) {
            $this->error("Error cleaning database logs: " . $e->getMessage());
        }
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
