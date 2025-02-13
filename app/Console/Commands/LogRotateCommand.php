<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;

class LogRotateCommand extends Command
{
    protected $signature = 'log:rotate
                          {--max-files=30 : Maximum number of log files to keep}
                          {--max-size=100 : Maximum size in MB for each log file}
                          {--compress : Compress rotated log files}
                          {--archive-dir=logs/archive : Directory for archived logs}';

    protected $description = 'Rotate and archive log files';

    protected $logPaths = [
        'laravel' => 'logs/laravel.log',
        'system' => 'logs/system.log',
        'monitoring' => 'logs/monitoring.log',
        'performance' => 'logs/performance.log',
        'security' => 'logs/security.log',
        'audit' => 'logs/audit.log',
    ];

    public function handle()
    {
        $this->info('Starting log rotation...');
        $startTime = microtime(true);

        try {
            foreach ($this->logPaths as $type => $path) {
                $fullPath = storage_path($path);
                
                if (File::exists($fullPath)) {
                    $this->rotateSingleLog($type, $fullPath);
                }
            }

            // Limpiar archivos antiguos
            $this->cleanOldFiles();

            $duration = round(microtime(true) - $startTime, 2);
            $this->info("Log rotation completed in {$duration} seconds");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error during log rotation: " . $e->getMessage());
            \Log::error("Log rotation failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    protected function rotateSingleLog(string $type, string $path): void
    {
        $maxSize = $this->option('max-size') * 1024 * 1024; // Convert MB to bytes
        
        if (!$this->shouldRotate($path, $maxSize)) {
            return;
        }

        $this->info("Rotating {$type} log...");

        $archiveDir = $this->getArchiveDirectory($type);
        $timestamp = now()->format('Y-m-d_His');
        $archivePath = "{$archiveDir}/{$type}_{$timestamp}.log";

        // Copiar el archivo actual al archivo
        File::copy($path, storage_path($archivePath));

        // Limpiar el archivo original
        File::put($path, '');

        // Comprimir si está habilitado
        if ($this->option('compress')) {
            $this->compressLog($archivePath);
        }

        $this->info("Log rotated successfully: {$archivePath}");
    }

    protected function shouldRotate(string $path, int $maxSize): bool
    {
        if (!File::exists($path)) {
            return false;
        }

        $size = File::size($path);
        return $size >= $maxSize;
    }

    protected function getArchiveDirectory(string $type): string
    {
        $baseDir = $this->option('archive-dir');
        $yearMonth = now()->format('Y/m');
        $dir = "{$baseDir}/{$type}/{$yearMonth}";

        if (!File::exists(storage_path($dir))) {
            File::makeDirectory(storage_path($dir), 0755, true);
        }

        return $dir;
    }

    protected function compressLog(string $path): void
    {
        $fullPath = storage_path($path);
        $zipPath = "{$fullPath}.zip";

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFile($fullPath, basename($fullPath));
            $zip->close();

            // Eliminar el archivo original después de comprimir
            File::delete($fullPath);
            
            $this->info("Log compressed: {$zipPath}");
        } else {
            $this->warn("Could not create zip file for: {$path}");
        }
    }

    protected function cleanOldFiles(): void
    {
        $maxFiles = $this->option('max-files');
        $baseDir = storage_path($this->option('archive-dir'));

        foreach ($this->logPaths as $type => $path) {
            $typeDir = "{$baseDir}/{$type}";
            if (!File::exists($typeDir)) {
                continue;
            }

            $files = collect(File::allFiles($typeDir))
                ->sortByDesc(function ($file) {
                    return $file->getMTime();
                });

            if ($files->count() > $maxFiles) {
                $filesToDelete = $files->slice($maxFiles);
                
                foreach ($filesToDelete as $file) {
                    File::delete($file->getRealPath());
                    $this->info("Deleted old log: {$file->getRealPath()}");
                }
            }
        }
    }

    protected function getRetentionDays(string $type): int
    {
        return config("logging.retention.{$type}", 30);
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
