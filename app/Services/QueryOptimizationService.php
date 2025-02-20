<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\PerformanceMonitor;

class QueryOptimizationService
{
    protected $performanceMonitor;
    protected $slowQueryThreshold = 1.0; // segundos
    protected $queryCache = [];

    public function __construct(PerformanceMonitor $performanceMonitor)
    {
        $this->performanceMonitor = $performanceMonitor;
    }

    /**
     * Analizar y optimizar una consulta SQL
     */
    public function analyzeQuery(string $sql, array $bindings = [], float $time = 0.0): array
    {
        $analysis = [
            'query' => $this->interpolateQuery($sql, $bindings),
            'execution_time' => $time,
            'recommendations' => [],
            'indexes_used' => [],
            'potential_issues' => [],
        ];

        // Obtener el plan de ejecución
        try {
            $explainResults = DB::select("EXPLAIN FORMAT=JSON $sql", $bindings);
            $analysis['execution_plan'] = json_decode(json_encode($explainResults), true);

            // Analizar el plan de ejecución
            $this->analyzePlan($analysis);
        } catch (\Exception $e) {
            Log::error("Error al analizar consulta: {$e->getMessage()}", [
                'sql' => $sql,
                'bindings' => $bindings
            ]);
        }

        // Registrar métricas
        $this->performanceMonitor->recordMetric(
            "query_analysis.execution_time",
            $time
        );

        return $analysis;
    }

    /**
     * Analizar el plan de ejecución de una consulta
     */
    protected function analyzePlan(array &$analysis): void
    {
        if (empty($analysis['execution_plan'])) {
            return;
        }

        foreach ($analysis['execution_plan'] as $step) {
            // Detectar full table scans
            if (isset($step['type']) && $step['type'] === 'ALL') {
                $analysis['potential_issues'][] = 'Se detectó un full table scan';
                $analysis['recommendations'][] = 'Considerar añadir índices apropiados';
            }

            // Detectar índices utilizados
            if (isset($step['key'])) {
                $analysis['indexes_used'][] = $step['key'];
            }

            // Analizar número de filas escaneadas
            if (isset($step['rows']) && $step['rows'] > 1000) {
                $analysis['potential_issues'][] = 'Gran cantidad de filas escaneadas';
                $analysis['recommendations'][] = 'Considerar limitar el conjunto de resultados';
            }
        }
    }

    /**
     * Registrar una consulta lenta
     */
    public function logSlowQuery(string $sql, array $bindings, float $time): void
    {
        if ($time >= $this->slowQueryThreshold) {
            $analysis = $this->analyzeQuery($sql, $bindings, $time);

            Log::warning('Consulta lenta detectada', [
                'sql' => $analysis['query'],
                'time' => $time,
                'recommendations' => $analysis['recommendations'],
                'issues' => $analysis['potential_issues']
            ]);

            // Almacenar para análisis posterior
            Cache::tags(['slow_queries'])->put(
                "slow_query_" . md5($sql),
                $analysis,
                now()->addDays(7)
            );
        }
    }

    /**
     * Obtener estadísticas de consultas
     */
    public function getQueryStats(): array
    {
        return [
            'slow_queries_count' => Cache::tags(['slow_queries'])->count(),
            'average_query_time' => $this->performanceMonitor->getMetric('database_query_time'),
            'total_queries' => $this->performanceMonitor->getMetric('database_query_count'),
            'problematic_patterns' => $this->identifyProblematicPatterns(),
        ];
    }

    /**
     * Identificar patrones problemáticos en consultas
     */
    protected function identifyProblematicPatterns(): array
    {
        $patterns = [];
        $slowQueries = Cache::tags(['slow_queries'])->get();

        foreach ($slowQueries as $query) {
            // Analizar patrones comunes en consultas lentas
            if (stripos($query['sql'], 'NOT IN') !== false) {
                $patterns['not_in_subqueries'][] = $query;
            }
            if (stripos($query['sql'], 'OR') !== false) {
                $patterns['multiple_or_conditions'][] = $query;
            }
            // Más patrones según sea necesario
        }

        return $patterns;
    }

    /**
     * Generar recomendaciones de optimización
     */
    public function generateOptimizationRecommendations(): array
    {
        $stats = $this->getQueryStats();
        $recommendations = [];

        // Analizar consultas lentas
        if ($stats['slow_queries_count'] > 0) {
            $recommendations[] = [
                'type' => 'critical',
                'message' => "Se detectaron {$stats['slow_queries_count']} consultas lentas",
                'action' => 'Revisar y optimizar consultas lentas identificadas'
            ];
        }

        // Analizar patrones problemáticos
        foreach ($stats['problematic_patterns'] as $pattern => $queries) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => "Patrón problemático detectado: $pattern",
                'action' => 'Revisar y refactorizar consultas afectadas'
            ];
        }

        // Analizar rendimiento general
        if ($stats['average_query_time'] > 0.5) {
            $recommendations[] = [
                'type' => 'improvement',
                'message' => 'Tiempo promedio de consulta elevado',
                'action' => 'Considerar implementación de caché adicional'
            ];
        }

        return $recommendations;
    }

    /**
     * Interpolar los bindings en la consulta SQL
     */
    protected function interpolateQuery(string $sql, array $bindings): string
    {
        $sql = str_replace(['%', '?'], ['%%', '%s'], $sql);
        $bindings = array_map(function ($binding) {
            if (is_string($binding)) {
                return "'" . addslashes($binding) . "'";
            } elseif (is_bool($binding)) {
                return $binding ? '1' : '0';
            } elseif (is_null($binding)) {
                return 'NULL';
            }
            return $binding;
        }, $bindings);

        return vsprintf($sql, $bindings);
    }

    /**
     * Establecer el umbral para consultas lentas
     */
    public function setSlowQueryThreshold(float $seconds): void
    {
        $this->slowQueryThreshold = $seconds;
    }
}
