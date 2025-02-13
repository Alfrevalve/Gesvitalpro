<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class OptimizeSystem extends Command
{
    protected $signature = 'system:optimize {--force} {--memory=256}';
    protected $description = 'Optimiza el sistema de manera segura';

    public function handle()
    {
        try {
            // Establecer límite de memoria
            $memoryLimit = $this->option('memory');
            ini_set('memory_limit', $memoryLimit . 'M');

            $this->info('Iniciando optimización del sistema...');
            $this->info('Límite de memoria establecido a: ' . $memoryLimit . 'M');

            // Limpiar caché por partes
            $this->cleanCache();

            // Optimizar la aplicación
            $this->optimizeApp();

            // Limpiar archivos temporales
            $this->cleanTempFiles();

            $this->info('¡Optimización completada con éxito!');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error durante la optimización: ' . $e->getMessage());
            Log::error('Error en optimización del sistema: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }

    protected function cleanCache()
    {
        $this->info('Limpiando caché...');

        // Limpiar caché por partes para evitar problemas de memoria
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        
        // Limpiar caché de aplicación por chunks
        $this->cleanCacheInChunks();

        $this->info('Caché limpiada correctamente.');
    }

    protected function cleanCacheInChunks()
    {
        $cachePath = storage_path('framework/cache/data');
        if (!is_dir($cachePath)) {
            return;
        }

        $files = glob($cachePath . '/*');
        $chunkSize = 100;
        $chunks = array_chunk($files, $chunkSize);

        foreach ($chunks as $index => $chunk) {
            $this->info(sprintf('Procesando chunk %d de %d', $index + 1, count($chunks)));
            
            foreach ($chunk as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }

            // Liberar memoria
            gc_collect_cycles();
        }
    }

    protected function optimizeApp()
    {
        $this->info('Optimizando aplicación...');

        // Optimizar carga de clases
        if (!$this->laravel->environment('local')) {
            $this->call('optimize');
        }

        // Optimizar autoloader
        if ($this->option('force')) {
            $this->info('Optimizando autoloader...');
            shell_exec('composer dump-autoload -o');
        }

        $this->info('Aplicación optimizada.');
    }

    protected function cleanTempFiles()
    {
        $this->info('Limpiando archivos temporales...');

        // Limpiar logs antiguos
        $this->cleanOldLogs();

        // Limpiar archivos temporales
        $this->cleanTempDirectory();

        $this->info('Archivos temporales limpiados.');
    }

    protected function cleanOldLogs()
    {
        $logsPath = storage_path('logs');
        if (!is_dir($logsPath)) {
            return;
        }

        $files = glob($logsPath . '/*.log');
        foreach ($files as $file) {
            // Mantener el archivo de log actual
            if (basename($file) === 'laravel-' . date('Y-m-d') . '.log') {
                continue;
            }

            // Eliminar logs más antiguos de 7 días
            if (filemtime($file) < strtotime('-7 days')) {
                @unlink($file);
            }
        }
    }

    protected function cleanTempDirectory()
    {
        $tempPath = storage_path('app/temp');
        if (!is_dir($tempPath)) {
            return;
        }

        $files = glob($tempPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
