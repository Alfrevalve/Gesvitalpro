<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class VerifySystemCommand extends Command
{
    protected $signature = 'system:verify';
    protected $description = 'Verifica la integridad y configuración del sistema';

    public function handle()
    {
        $this->info('🔍 Iniciando verificación del sistema...');

        $checks = [
            'Verificación de Base de Datos' => $this->checkDatabase(),
            'Verificación de Caché' => $this->checkCache(),
            'Verificación de Seguridad' => $this->checkSecurity(),
            'Verificación de Assets' => $this->checkAssets(),
            'Verificación de Permisos' => $this->checkPermissions(),
            'Verificación de Configuración' => $this->checkConfiguration(),
        ];

        $this->displayResults($checks);

        return Command::SUCCESS;
    }

    protected function checkDatabase()
    {
        $results = [];

        try {
            // Verificar conexión
            DB::connection()->getPdo();
            $results['Conexión a BD'] = true;

            // Verificar migraciones
            $results['Migraciones'] = Schema::hasTable('migrations');

            // Verificar índices críticos para cirugías
            $surgeryIndexes = [
                'surgery_date',
                'institucion_id',
                'medico_id',
                'line_id'
            ];
            $results['Índices de cirugías'] = $this->checkTableIndexes('surgeries', $surgeryIndexes);

            // Verificar índices críticos para equipamiento
            $equipmentIndexes = [
                'line_id',
                'serial_number'
            ];
            $results['Índices de equipamiento'] = $this->checkTableIndexes('equipment', $equipmentIndexes);

            // Verificar integridad referencial
            $results['Integridad referencial'] = $this->checkForeignKeys();

        } catch (\Exception $e) {
            $results['Error'] = "Error en BD: " . $e->getMessage();
            return ['status' => false, 'details' => $results];
        }

        return ['status' => !in_array(false, $results), 'details' => $results];
    }

    protected function checkCache()
    {
        $results = [];

        try {
            // Verificar Redis
            $results['Conexión Redis'] = Redis::ping() == 'PONG';

            // Verificar caché del sistema
            Cache::put('test_key', 'test_value', 10);
            $results['Escritura en caché'] = Cache::has('test_key');
            $results['Lectura de caché'] = Cache::get('test_key') === 'test_value';
            Cache::forget('test_key');

            // Verificar caché de rutas
            $results['Caché de rutas'] = File::exists(base_path('bootstrap/cache/routes-v7.php'));

            // Verificar caché de configuración
            $results['Caché de configuración'] = File::exists(base_path('bootstrap/cache/config.php'));

        } catch (\Exception $e) {
            $results['Error'] = "Error en caché: " . $e->getMessage();
            return ['status' => false, 'details' => $results];
        }

        return ['status' => !in_array(false, $results), 'details' => $results];
    }

    protected function checkSecurity()
    {
        $results = [];

        try {
            // Verificar archivo .env
            $results['Archivo .env'] = File::exists(base_path('.env'));

            // Verificar modo debug
            $results['APP_DEBUG'] = !config('app.debug');

            // Verificar HTTPS
            $results['HTTPS'] = config('app.env') === 'production' ?
                str_starts_with(config('app.url'), 'https://') : true;

            // Verificar permisos de archivos críticos
            $results['Permisos .env'] = File::exists(base_path('.env')) &&
                (File::chmod(base_path('.env')) === 0600);

            // Verificar configuración de sesión
            $results['Configuración de sesión'] = config('session.secure') &&
                config('session.http_only');

            // Verificar middleware de seguridad
            $results['Middleware de seguridad'] = $this->checkSecurityMiddleware();

        } catch (\Exception $e) {
            $results['Error'] = "Error en seguridad: " . $e->getMessage();
            return ['status' => false, 'details' => $results];
        }

        return ['status' => !in_array(false, $results), 'details' => $results];
    }

    protected function checkAssets()
    {
        $results = [];

        try {
            // Verificar archivos compilados
            $results['CSS compilado'] = File::exists(public_path('build/assets/app.css'));
            $results['JS compilado'] = File::exists(public_path('build/assets/app.js'));

            // Verificar assets de AdminLTE
            $results['AdminLTE CSS'] = File::exists(public_path('vendor/adminlte/dist/css/adminlte.min.css'));
            $results['AdminLTE JS'] = File::exists(public_path('vendor/adminlte/dist/js/adminlte.min.js'));

            // Verificar assets de Sneat
            $results['Sneat CSS'] = File::exists(public_path('assets/vendor/css/core.css'));
            $results['Sneat JS'] = File::exists(public_path('assets/vendor/js/menu.js'));

        } catch (\Exception $e) {
            $results['Error'] = "Error en assets: " . $e->getMessage();
            return ['status' => false, 'details' => $results];
        }

        return ['status' => !in_array(false, $results), 'details' => $results];
    }

    protected function checkPermissions()
    {
        $results = [];

        try {
            $paths = [
                storage_path() => 0775,
                public_path('storage') => 0775,
                base_path('bootstrap/cache') => 0775,
                storage_path('logs') => 0775,
                storage_path('framework/views') => 0775,
                storage_path('framework/cache') => 0775,
                storage_path('framework/sessions') => 0775,
            ];

            foreach ($paths as $path => $permission) {
                if (File::exists($path)) {
                    $currentPermission = substr(sprintf('%o', File::chmod($path)), -4);
                    $results[basename($path)] = intval($currentPermission) <= $permission;
                }
            }

        } catch (\Exception $e) {
            $results['Error'] = "Error en permisos: " . $e->getMessage();
            return ['status' => false, 'details' => $results];
        }

        return ['status' => !in_array(false, $results), 'details' => $results];
    }

    protected function checkConfiguration()
    {
        $results = [];

        try {
            // Verificar configuración de correo
            $results['Configuración de correo'] = !empty(config('mail.mailers.smtp.host'));

            // Verificar configuración de base de datos
            $results['Configuración de BD'] = !empty(config('database.connections.mysql.database'));

            // Verificar configuración de caché
            $results['Driver de caché'] = config('cache.default') === 'redis';

            // Verificar configuración de sesión
            $results['Driver de sesión'] = in_array(config('session.driver'), ['redis', 'database']);

            // Verificar configuración de queue
            $results['Driver de cola'] = in_array(config('queue.default'), ['redis', 'database']);

        } catch (\Exception $e) {
            $results['Error'] = "Error en configuración: " . $e->getMessage();
            return ['status' => false, 'details' => $results];
        }

        return ['status' => !in_array(false, $results), 'details' => $results];
    }

    protected function checkTableIndexes($table, $columns)
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        $indexes = DB::select("SHOW INDEX FROM {$table}");
        $indexedColumns = collect($indexes)->pluck('Column_name')->toArray();

        return count(array_intersect($columns, $indexedColumns)) === count($columns);
    }

    protected function checkForeignKeys()
    {
        $tables = ['surgeries', 'equipment', 'surgery_equipment'];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }

            $foreignKeys = DB::select("
                SELECT * FROM information_schema.KEY_COLUMN_USAGE
                WHERE REFERENCED_TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ", [config('database.connections.mysql.database'), $table]);

            if (empty($foreignKeys)) {
                return false;
            }
        }

        return true;
    }

    protected function checkSecurityMiddleware()
    {
        $kernel = app()->make(\App\Http\Kernel::class);
        $middleware = $kernel->getMiddleware();

        return isset($middleware[\App\Http\Middleware\SecurityMiddleware::class]);
    }

    protected function displayResults($checks)
    {
        $this->newLine();

        foreach ($checks as $checkName => $result) {
            $this->info("📋 {$checkName}:");

            foreach ($result['details'] as $item => $status) {
                $icon = is_bool($status) ? ($status ? '✅' : '❌') : '❗';
                $message = is_bool($status) ? $item : $status;
                $this->line("{$icon} {$message}");
            }

            $this->newLine();
        }

        $allPassed = collect($checks)->every(function ($check) {
            return $check['status'];
        });

        if ($allPassed) {
            $this->info('✨ Todas las verificaciones pasaron exitosamente!');
        } else {
            $this->warn('⚠️ Algunas verificaciones fallaron. Por favor revise los detalles anteriores.');
        }
    }
}
