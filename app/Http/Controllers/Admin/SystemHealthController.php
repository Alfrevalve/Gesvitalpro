<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HealthCheckService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SystemHealthController extends Controller
{
    protected $healthCheckService;

    public function __construct(HealthCheckService $healthCheckService)
    {
        $this->middleware(['auth', 'role:admin']);
        $this->healthCheckService = $healthCheckService;
    }

    /**
     * Mostrar el panel de estado del sistema
     */
    public function index()
    {
        // Obtener o cachear el estado del sistema por 5 minutos
        $healthCheck = Cache::remember('system_health', 300, function () {
            return $this->healthCheckService->checkSystem();
        });

        // Obtener estadísticas adicionales
        $stats = $this->getSystemStats();

        return view('admin.system.health', compact('healthCheck', 'stats'));
    }

    /**
     * Realizar una verificación manual del sistema
     */
    public function check(Request $request)
    {
        // Forzar una nueva verificación
        Cache::forget('system_health');
        $healthCheck = $this->healthCheckService->checkSystem();

        if ($request->wantsJson()) {
            return response()->json($healthCheck);
        }

        return redirect()->route('admin.system.health')
            ->with('status', 'Verificación del sistema completada.');
    }

    /**
     * Ejecutar tareas de mantenimiento
     */
    public function maintenance(Request $request)
    {
        $task = $request->input('task');
        $output = '';

        switch ($task) {
            case 'clear-cache':
                Artisan::call('cache:clear');
                Artisan::call('view:clear');
                Artisan::call('config:clear');
                $output = 'Caché del sistema limpiada.';
                break;

            case 'optimize':
                Artisan::call('optimize');
                $output = 'Sistema optimizado.';
                break;

            case 'migrate':
                Artisan::call('migrate', ['--force' => true]);
                $output = 'Migraciones ejecutadas.';
                break;

            case 'storage-link':
                Artisan::call('storage:link');
                $output = 'Enlaces simbólicos creados.';
                break;

            default:
                return back()->with('error', 'Tarea de mantenimiento no válida.');
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => $output]);
        }

        return back()->with('status', $output);
    }

    /**
     * Obtener estadísticas del sistema
     */
    private function getSystemStats(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => $this->getDatabaseVersion(),
            'system_stats' => [
                'uptime' => $this->getSystemUptime(),
                'load_average' => sys_getloadavg(),
                'memory_usage' => $this->getMemoryUsage(),
                'disk_usage' => $this->getDiskUsage(),
            ],
            'application_stats' => [
                'users_count' => \App\Models\User::count(),
                'roles_count' => \Spatie\Permission\Models\Role::count(),
                'permissions_count' => \Spatie\Permission\Models\Permission::count(),
                'cache_size' => $this->getCacheSize(),
                'log_size' => $this->getLogSize(),
            ],
        ];
    }

    /**
     * Obtener la versión de la base de datos
     */
    private function getDatabaseVersion(): string
    {
        try {
            $version = \DB::select('SELECT version() as version')[0]->version;
            return $version;
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Obtener el tiempo de actividad del sistema
     */
    private function getSystemUptime(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'N/A en Windows';
        }

        try {
            $uptime = shell_exec('uptime -p');
            return trim($uptime) ?: 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Obtener el uso de memoria
     */
    private function getMemoryUsage(): array
    {
        $memoryTotal = PHP_OS_FAMILY === 'Windows' ? 0 : shell_exec("free -b | grep 'Mem:' | awk '{print $2}'");
        $memoryUsed = PHP_OS_FAMILY === 'Windows' ? 0 : shell_exec("free -b | grep 'Mem:' | awk '{print $3}'");

        return [
            'total' => $this->formatBytes((int)$memoryTotal),
            'used' => $this->formatBytes((int)$memoryUsed),
            'php_memory_limit' => ini_get('memory_limit'),
        ];
    }

    /**
     * Obtener el uso del disco
     */
    private function getDiskUsage(): array
    {
        $path = storage_path();
        return [
            'total' => $this->formatBytes(disk_total_space($path)),
            'free' => $this->formatBytes(disk_free_space($path)),
            'used' => $this->formatBytes(disk_total_space($path) - disk_free_space($path)),
        ];
    }

    /**
     * Obtener el tamaño de la caché
     */
    private function getCacheSize(): string
    {
        $cacheDir = storage_path('framework/cache');
        return $this->formatBytes($this->getDirSize($cacheDir));
    }

    /**
     * Obtener el tamaño de los logs
     */
    private function getLogSize(): string
    {
        $logDir = storage_path('logs');
        return $this->formatBytes($this->getDirSize($logDir));
    }

    /**
     * Obtener el tamaño de un directorio
     */
    private function getDirSize(string $dir): int
    {
        $size = 0;
        foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->getDirSize($each);
        }
        return $size;
    }

    /**
     * Formatear bytes a unidades legibles
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
