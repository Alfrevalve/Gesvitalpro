<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Process;
use Exception;

class WindowsService
{
    /**
     * Optimizar el sistema para Windows
     */
    public function optimize(): bool
    {
        try {
            // Configurar PHP
            $this->configurePHP();

            // Limpiar archivos temporales
            $this->cleanTempFiles();

            // Optimizar servicios de Windows
            if (config('windows.optimization.optimize_windows_services')) {
                $this->optimizeServices();
            }

            // Configurar permisos
            $this->configurePermissions();

            // Configurar tarea programada
            $this->configureScheduledTask();

            return true;
        } catch (Exception $e) {
            Log::error('Error optimizando Windows: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Configurar PHP para Windows
     */
    protected function configurePHP(): void
    {
        $phpIniFile = config('windows.php.ini_file');
        $phpIniContent = file_get_contents($phpIniFile);

        // Actualizar configuraciones
        $replacements = [
            '/^memory_limit = .*$/m' => 'memory_limit = ' . config('windows.php.memory_limit'),
            '/^max_execution_time = .*$/m' => 'max_execution_time = ' . config('windows.php.max_execution_time'),
            '/^upload_max_filesize = .*$/m' => 'upload_max_filesize = ' . config('windows.php.upload_max_filesize'),
            '/^post_max_size = .*$/m' => 'post_max_size = ' . config('windows.php.post_max_size'),
        ];

        $phpIniContent = preg_replace(array_keys($replacements), array_values($replacements), $phpIniContent);
        file_put_contents($phpIniFile, $phpIniContent);
    }

    /**
     * Limpiar archivos temporales
     */
    protected function cleanTempFiles(): void
    {
        // Limpiar directorios de Laravel
        foreach (config('windows.storage.paths') as $path) {
            if (File::isDirectory($path)) {
                File::cleanDirectory($path);
            }
        }

        // Limpiar temp de Windows si está configurado
        if (config('windows.optimization.clear_windows_temp')) {
            $tempDir = config('windows.environment.temp_dir');
            $command = config('windows.commands.clear_cache') . ' "' . $tempDir . '\\*.*"';
            $this->executeCommand($command);
        }
    }

    /**
     * Optimizar servicios de Windows
     */
    protected function optimizeServices(): void
    {
        foreach (config('windows.services') as $service) {
            if ($this->shouldRestartService($service['name'])) {
                $this->executeCommand($service['restart']);
            }
        }
    }

    /**
     * Verificar si un servicio necesita reinicio
     */
    protected function shouldRestartService(string $serviceName): bool
    {
        $process = new Process(['sc', 'query', $serviceName]);
        $process->run();

        return $process->isSuccessful() && 
               str_contains($process->getOutput(), 'RUNNING') &&
               config('windows.optimization.restart_services_after_optimization');
    }

    /**
     * Configurar permisos de archivos
     */
    protected function configurePermissions(): void
    {
        $paths = array_merge(
            [storage_path(), base_path('bootstrap/cache')],
            config('windows.security.windows_defender_exclusions', [])
        );

        foreach ($paths as $path) {
            if (File::exists($path)) {
                // Establecer permisos
                chmod($path, config('windows.storage.permissions.directories'));

                if (is_dir($path)) {
                    $files = File::allFiles($path);
                    foreach ($files as $file) {
                        chmod($file, config('windows.storage.permissions.files'));
                    }
                }

                // Agregar exclusión a Windows Defender si está configurado
                if (in_array($path, config('windows.security.windows_defender_exclusions', []))) {
                    $this->addWindowsDefenderExclusion($path);
                }
            }
        }
    }

    /**
     * Agregar exclusión a Windows Defender
     */
    protected function addWindowsDefenderExclusion(string $path): void
    {
        $command = "powershell -Command \"Add-MpPreference -ExclusionPath '{$path}'\"";
        $this->executeCommand($command);
    }

    /**
     * Configurar tarea programada
     */
    protected function configureScheduledTask(): void
    {
        $taskConfig = config('windows.scheduled_tasks.artisan_schedule');
        
        // Eliminar tarea existente
        $this->executeCommand('schtasks /delete /tn "' . $taskConfig['name'] . '" /f');

        // Crear nueva tarea
        $command = 'schtasks /create /tn "' . $taskConfig['name'] . '" ' .
                  '/tr "' . base_path($taskConfig['command']) . '" ' .
                  '/sc ' . $taskConfig['frequency'] . ' ' .
                  '/ru "' . $taskConfig['user'] . '" ' .
                  '/rp "' . $taskConfig['password'] . '"';

        $this->executeCommand($command);
    }

    /**
     * Ejecutar comando de Windows
     */
    protected function executeCommand(string $command): Process
    {
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::warning('Comando falló: ' . $command);
            Log::warning('Error: ' . $process->getErrorOutput());
        }

        return $process;
    }

    /**
     * Verificar requisitos del sistema
     */
    public function checkSystemRequirements(): array
    {
        $requirements = [
            'php_version' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'php_extensions' => $this->checkPhpExtensions(),
            'writable_paths' => $this->checkWritablePaths(),
            'services_running' => $this->checkServices(),
            'memory_limit' => $this->checkMemoryLimit(),
        ];

        return array_merge($requirements, [
            'all_passed' => !in_array(false, $requirements, true)
        ]);
    }

    /**
     * Verificar extensiones de PHP
     */
    protected function checkPhpExtensions(): bool
    {
        $required = ['pdo_mysql', 'openssl', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json'];
        
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verificar permisos de escritura
     */
    protected function checkWritablePaths(): bool
    {
        $paths = array_values(config('windows.storage.paths'));
        
        foreach ($paths as $path) {
            if (!is_writable($path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verificar servicios
     */
    protected function checkServices(): bool
    {
        foreach (config('windows.services') as $service) {
            $process = new Process(['sc', 'query', $service['name']]);
            $process->run();

            if (!$process->isSuccessful() || !str_contains($process->getOutput(), 'RUNNING')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verificar límite de memoria
     */
    protected function checkMemoryLimit(): bool
    {
        $limit = ini_get('memory_limit');
        $required = config('windows.php.memory_limit');

        return $this->convertToBytes($limit) >= $this->convertToBytes($required);
    }

    /**
     * Convertir string de memoria a bytes
     */
    protected function convertToBytes(string $value): int
    {
        $unit = strtolower(substr($value, -1));
        $value = (int) $value;
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}
