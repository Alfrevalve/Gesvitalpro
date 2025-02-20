<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QueryOptimizationService;
use App\Services\PerformanceMonitor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptimizeQueriesCommand extends Command
{
    protected $signature = 'queries:optimize
                          {action=analyze : Action to perform (analyze|report|suggest|monitor)}
                          {--detailed : Generate detailed analysis}
                          {--threshold=1 : Slow query threshold in seconds}';

    protected $description = 'Analiza y optimiza las consultas SQL del sistema';

    protected $queryOptimizer;
    protected $performanceMonitor;

    public function __construct(
        QueryOptimizationService $queryOptimizer,
        PerformanceMonitor $performanceMonitor
    ) {
        parent::__construct();
        $this->queryOptimizer = $queryOptimizer;
        $this->performanceMonitor = $performanceMonitor;
    }

    public function handle()
    {
        $action = $this->argument('action');
        $this->queryOptimizer->setSlowQueryThreshold(
            (float) $this->option('threshold')
        );

        switch ($action) {
            case 'analyze':
                $this->analyzeQueries();
                break;
            case 'report':
                $this->generateReport();
                break;
            case 'suggest':
                $this->suggestOptimizations();
                break;
            case 'monitor':
                $this->monitorPerformance();
                break;
            default:
                $this->error("Acción no válida: $action");
                return 1;
        }
    }

    protected function analyzeQueries()
    {
        $this->info('Analizando consultas del sistema...');

        // Analizar tablas principales
        $tables = ['surgeries', 'equipment', 'medicos', 'instituciones'];
        $results = [];

        foreach ($tables as $table) {
            $this->info("\nAnalizando tabla: $table");

            // Obtener estadísticas de la tabla
            $count = DB::table($table)->count();
            $this->line("- Registros totales: $count");

            // Analizar índices
            $indexes = $this->getTableIndexes($table);
            $this->line("- Índices encontrados: " . count($indexes));

            // Analizar consultas comunes
            $this->analyzeCommonQueries($table);
        }

        if ($this->option('detailed')) {
            $this->showDetailedAnalysis();
        }
    }

    protected function generateReport()
    {
        $this->info('Generando reporte de rendimiento de consultas...');

        $stats = $this->queryOptimizer->getQueryStats();

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Consultas Lentas', $stats['slow_queries_count']],
                ['Tiempo Promedio', number_format($stats['average_query_time'], 4) . ' seg'],
                ['Total Consultas', $stats['total_queries']],
            ]
        );

        // Mostrar patrones problemáticos
        if (!empty($stats['problematic_patterns'])) {
            $this->warn("\nPatrones Problemáticos Detectados:");
            foreach ($stats['problematic_patterns'] as $pattern => $queries) {
                $this->line("- $pattern: " . count($queries) . " ocurrencias");
            }
        }

        // Generar gráfico de rendimiento si está disponible
        if (class_exists('PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor')) {
            $this->drawPerformanceGraph();
        }
    }

    protected function suggestOptimizations()
    {
        $this->info('Generando sugerencias de optimización...');

        $recommendations = $this->queryOptimizer->generateOptimizationRecommendations();

        foreach ($recommendations as $rec) {
            switch ($rec['type']) {
                case 'critical':
                    $this->error($rec['message']);
                    break;
                case 'warning':
                    $this->warn($rec['message']);
                    break;
                default:
                    $this->info($rec['message']);
            }
            $this->line("  Acción sugerida: " . $rec['action'] . "\n");
        }

        // Sugerir índices específicos
        $this->suggestIndexes();
    }

    protected function monitorPerformance()
    {
        $this->info('Iniciando monitoreo de rendimiento...');
        $this->line('Presione Ctrl+C para detener el monitoreo.');

        $interval = 5; // segundos
        $iterations = 0;

        while (true) {
            $stats = $this->performanceMonitor->generatePerformanceReport();

            $this->line("\n" . date('Y-m-d H:i:s'));
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Consultas/seg', number_format($stats['database']['query_count'] / $interval, 2)],
                    ['Tiempo promedio', number_format($stats['database']['query_time'], 4) . ' seg'],
                    ['Uso de memoria', $this->formatBytes($stats['memory']['usage'])],
                ]
            );

            if ($iterations++ % 12 === 0) { // Cada minuto
                $this->checkPerformanceAlerts();
            }

            sleep($interval);
        }
    }

    protected function analyzeCommonQueries(string $table)
    {
        $commonQueries = [
            "SELECT * FROM $table ORDER BY created_at DESC LIMIT 10",
            "SELECT COUNT(*) FROM $table GROUP BY status",
            "SELECT * FROM $table WHERE status = 'active'",
        ];

        foreach ($commonQueries as $sql) {
            $start = microtime(true);
            DB::select($sql);
            $time = microtime(true) - $start;

            $analysis = $this->queryOptimizer->analyzeQuery($sql, [], $time);

            if (!empty($analysis['potential_issues'])) {
                $this->warn("Consulta potencialmente problemática:");
                $this->line("  " . $sql);
                foreach ($analysis['potential_issues'] as $issue) {
                    $this->line("  - " . $issue);
                }
            }
        }
    }

    protected function getTableIndexes(string $table): array
    {
        return Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);
    }

    protected function showDetailedAnalysis()
    {
        $this->info("\nAnálisis Detallado:");

        // Análisis de fragmentación
        $this->line("\nFragmentación de Tablas:");
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($tables as $table) {
            $this->line("- $table:");
            // Aquí iría el análisis de fragmentación específico para tu motor de BD
        }

        // Análisis de distribución de datos
        $this->line("\nDistribución de Datos:");
        // Aquí irían estadísticas de distribución de datos
    }

    protected function suggestIndexes()
    {
        $this->info("\nSugerencias de Índices:");

        // Analizar consultas frecuentes y sugerir índices
        $suggestions = [
            'surgeries' => [
                'Índice compuesto (status, priority, surgery_date)',
                'Índice para búsquedas full-text en notes',
            ],
            'equipment' => [
                'Índice parcial para equipos activos',
                'Índice para próximo mantenimiento',
            ],
        ];

        foreach ($suggestions as $table => $indexes) {
            $this->line("\nTabla: $table");
            foreach ($indexes as $index) {
                $this->line("- $index");
            }
        }
    }

    protected function checkPerformanceAlerts()
    {
        if ($this->performanceMonitor->shouldShowPerformanceAlert()) {
            $this->error(
                $this->performanceMonitor->getPerformanceAlertMessage()
            );
        }
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    protected function drawPerformanceGraph()
    {
        // Implementación básica de gráfico ASCII
        $this->line("\nRendimiento últimas 24 horas:");
        // Aquí iría la lógica para dibujar el gráfico
    }
}
