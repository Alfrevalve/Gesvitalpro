<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class SystemAuditCommand extends Command
{
    protected $signature = 'system:audit {--detailed : Mostrar información detallada}';
    protected $description = 'Realiza una auditoría completa del sistema';

    protected $issues = [];
    protected $warnings = [];
    protected $recommendations = [];

    public function handle()
    {
        $this->info('Iniciando auditoría del sistema...');
        $this->newLine();

        // Auditar base de datos
        $this->auditDatabase();

        // Auditar rutas y controladores
        $this->auditRoutes();

        // Auditar archivos y permisos
        $this->auditFiles();

        // Auditar configuración
        $this->auditConfiguration();

        // Auditar seguridad
        $this->auditSecurity();

        // Auditar rendimiento
        $this->auditPerformance();

        // Mostrar resultados
        $this->showResults();

        return 0;
    }

    protected function auditDatabase()
    {
        $this->info('Auditando base de datos...');

        // Verificar conexión
        try {
            DB::connection()->getPdo();
            $this->line('✓ Conexión a base de datos establecida');
        } catch (\Exception $e) {
            $this->addIssue('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }

        // Verificar migraciones
        if (!Schema::hasTable('migrations')) {
            $this->addIssue('Tabla de migraciones no encontrada');
        } else {
            $pendingMigrations = collect(File::files(database_path('migrations')))
                ->count() - DB::table('migrations')->count();

            if ($pendingMigrations > 0) {
                $this->addWarning("Hay {$pendingMigrations} migraciones pendientes");
            }
        }

        // Verificar índices
        $this->auditDatabaseIndexes();

        $this->newLine();
    }

    protected function auditDatabaseIndexes()
    {
        $tables = ['surgeries', 'equipment', 'medicos', 'instituciones'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $indexes = DB::select("SHOW INDEXES FROM {$table}");
                $indexCount = count($indexes);

                if ($indexCount < 2) {
                    $this->addWarning("La tabla {$table} tiene pocos índices ({$indexCount})");
                }

                // Verificar índices específicos según la tabla
                $this->checkTableSpecificIndexes($table, $indexes);
            }
        }
    }

    protected function checkTableSpecificIndexes($table, $indexes)
    {
        $indexNames = collect($indexes)->pluck('Key_name')->toArray();

        switch ($table) {
            case 'surgeries':
                if (!in_array('idx_surgeries_date_status', $indexNames)) {
                    $this->addRecommendation("Agregar índice por fecha y estado en tabla surgeries");
                }
                break;
            case 'equipment':
                if (!in_array('idx_equipment_status', $indexNames)) {
                    $this->addRecommendation("Agregar índice por estado en tabla equipment");
                }
                break;
        }
    }

    protected function auditRoutes()
    {
        $this->info('Auditando rutas y controladores...');

        $routes = Route::getRoutes();
        $routeCount = count($routes);
        $this->line("✓ {$routeCount} rutas registradas");

        // Verificar controladores
        foreach ($routes as $route) {
            if (isset($route->action['controller'])) {
                $controller = $route->action['controller'];
                if (!class_exists(explode('@', $controller)[0])) {
                    $this->addIssue("Controlador no encontrado: {$controller}");
                }
            }
        }

        // Verificar middleware
        $this->auditMiddleware();

        $this->newLine();
    }

    protected function auditMiddleware()
    {
        $middleware = app()->make(\Illuminate\Contracts\Http\Kernel::class)->getMiddleware();

        // Verificar middleware esenciales
        $essentialMiddleware = [
            'Illuminate\Foundation\Http\Middleware\ValidatePostSize',
            'Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance',
            'App\Http\Middleware\SimpleOptimizeResponse',
        ];

        foreach ($essentialMiddleware as $mid) {
            if (!in_array($mid, array_values($middleware))) {
                $this->addWarning("Middleware recomendado no encontrado: {$mid}");
            }
        }
    }

    protected function auditFiles()
    {
        $this->info('Auditando archivos y permisos...');

        // Verificar directorios críticos
        $directories = [
            storage_path(),
            public_path(),
            base_path('bootstrap/cache'),
        ];

        foreach ($directories as $directory) {
            if (!is_writable($directory)) {
                $this->addIssue("Directorio no escribible: {$directory}");
            }
        }

        // Verificar archivos de configuración
        $configFiles = [
            '.env',
            'composer.json',
            'package.json',
        ];

        foreach ($configFiles as $file) {
            if (!File::exists(base_path($file))) {
                $this->addIssue("Archivo de configuración no encontrado: {$file}");
            }
        }

        $this->newLine();
    }

    protected function auditConfiguration()
    {
        $this->info('Auditando configuración...');

        // Verificar configuración de caché
        if (config('cache.default') === 'file') {
            $this->addRecommendation('Considerar usar Redis o Memcached para caché');
        }

        // Verificar configuración de sesión
        if (config('session.driver') === 'file') {
            $this->addRecommendation('Considerar usar Redis o base de datos para sesiones');
        }

        // Verificar configuración de correo
        if (empty(config('mail.mailers.smtp.host'))) {
            $this->addWarning('Configuración de correo SMTP no establecida');
        }

        $this->newLine();
    }

    protected function auditSecurity()
    {
        $this->info('Auditando seguridad...');

        // Verificar configuración de APP_KEY
        if (empty(config('app.key'))) {
            $this->addIssue('APP_KEY no establecida');
        }

        // Verificar modo debug
        if (config('app.debug')) {
            $this->addWarning('Modo debug está activado');
        }

        // Verificar HTTPS
        if (!config('session.secure')) {
            $this->addWarning('Las cookies de sesión no están configuradas como seguras');
        }

        // Verificar políticas de seguridad
        $this->auditSecurityPolicies();

        $this->newLine();
    }

    protected function auditSecurityPolicies()
    {
        // Verificar middleware de seguridad
        $securityMiddleware = [
            'App\Http\Middleware\TrustProxies',
            'App\Http\Middleware\PreventRequestsDuringMaintenance',
            'App\Http\Middleware\VerifyCsrfToken',
        ];

        foreach ($securityMiddleware as $middleware) {
            if (!class_exists($middleware)) {
                $this->addWarning("Middleware de seguridad no encontrado: {$middleware}");
            }
        }
    }

    protected function auditPerformance()
    {
        $this->info('Auditando rendimiento...');

        // Verificar optimización de autoload
        if (!File::exists(base_path('bootstrap/cache/packages.php'))) {
            $this->addRecommendation('Ejecutar composer dump-autoload --optimize');
        }

        // Verificar caché de rutas
        if (!File::exists(base_path('bootstrap/cache/routes-v7.php'))) {
            $this->addRecommendation('Ejecutar php artisan route:cache');
        }

        // Verificar caché de configuración
        if (!File::exists(base_path('bootstrap/cache/config.php'))) {
            $this->addRecommendation('Ejecutar php artisan config:cache');
        }

        $this->newLine();
    }

    protected function addIssue($message)
    {
        $this->issues[] = $message;
        $this->error("✗ {$message}");
    }

    protected function addWarning($message)
    {
        $this->warnings[] = $message;
        $this->warn("! {$message}");
    }

    protected function addRecommendation($message)
    {
        $this->recommendations[] = $message;
        $this->line("○ {$message}");
    }

    protected function showResults()
    {
        $this->newLine();
        $this->info('Resumen de la Auditoría:');
        $this->newLine();

        $this->table(
            ['Categoría', 'Cantidad'],
            [
                ['Problemas', count($this->issues)],
                ['Advertencias', count($this->warnings)],
                ['Recomendaciones', count($this->recommendations)],
            ]
        );

        if ($this->option('detailed')) {
            $this->showDetailedResults();
        }

        $this->newLine();
        $this->info('Auditoría completada.');
    }

    protected function showDetailedResults()
    {
        if (!empty($this->issues)) {
            $this->error('Problemas Encontrados:');
            foreach ($this->issues as $issue) {
                $this->line(" - {$issue}");
            }
            $this->newLine();
        }

        if (!empty($this->warnings)) {
            $this->warn('Advertencias:');
            foreach ($this->warnings as $warning) {
                $this->line(" - {$warning}");
            }
            $this->newLine();
        }

        if (!empty($this->recommendations)) {
            $this->info('Recomendaciones:');
            foreach ($this->recommendations as $recommendation) {
                $this->line(" - {$recommendation}");
            }
            $this->newLine();
        }
    }
}
