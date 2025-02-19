<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\PerformanceMonitor;
use App\Services\DashboardOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected PerformanceMonitor $monitor;
    protected DashboardOptimizer $optimizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
        $this->monitor = app(PerformanceMonitor::class);
        $this->optimizer = app(DashboardOptimizer::class);
    }

    /** @test */
    public function monitors_slow_queries()
    {
        DB::enableQueryLog();

        // Ejecutar una consulta compleja que debería ser lenta
        $result = DB::table('surgeries')
            ->join('medicos', 'surgeries.medico_id', '=', 'medicos.id')
            ->join('instituciones', 'surgeries.institucion_id', '=', 'instituciones.id')
            ->whereDate('surgery_date', '>=', now())
            ->orderBy('surgery_date')
            ->get();

        $queryLog = DB::getQueryLog();
        $lastQuery = end($queryLog);

        // Verificar que el monitor detectó la consulta lenta
        $this->assertTrue(
            $this->monitor->wasQuerySlow($lastQuery['time'])
        );
    }

    /** @test */
    public function cache_system_works_efficiently()
    {
        Cache::flush();

        // Primera carga - debería generar caché
        $startTime = microtime(true);
        $this->optimizer->getSurgeriesStats();
        $firstLoadTime = microtime(true) - $startTime;

        // Segunda carga - debería usar caché
        $startTime = microtime(true);
        $this->optimizer->getSurgeriesStats();
        $secondLoadTime = microtime(true) - $startTime;

        // La segunda carga debería ser significativamente más rápida
        $this->assertLessThan($firstLoadTime / 2, $secondLoadTime);
    }

    /** @test */
    public function monitors_memory_usage()
    {
        $initialMemory = memory_get_usage(true);

        // Realizar operaciones que consumen memoria
        $data = [];
        for ($i = 0; $i < 1000; $i++) {
            $data[] = str_repeat('a', 1000);
        }

        $finalMemory = memory_get_usage(true);

        // Verificar que el monitor detectó el incremento de memoria
        $memoryIncrease = ($finalMemory - $initialMemory) / 1024 / 1024; // MB
        $this->assertTrue(
            $this->monitor->isMemoryUsageHigh($memoryIncrease)
        );
    }

    /** @test */
    public function generates_performance_report()
    {
        $report = $this->monitor->generatePerformanceReport();

        $this->assertArrayHasKey('slow_queries', $report);
        $this->assertArrayHasKey('memory_usage', $report);
        $this->assertArrayHasKey('cache', $report);
        $this->assertArrayHasKey('database_size', $report);
    }

    /** @test */
    public function suggests_database_optimizations()
    {
        $suggestions = $this->monitor->suggestIndexes();

        $this->assertIsArray($suggestions);
        $this->assertArrayHasKey('suggested_indexes', $suggestions);
    }

    /** @test */
    public function cache_invalidation_works()
    {
        // Generar caché
        $this->optimizer->warmupDashboardCache();

        // Verificar que el caché existe
        $this->assertTrue(Cache::has('dashboard_stats'));

        // Invalidar caché
        $this->optimizer->clearDashboardCache();

        // Verificar que el caché fue eliminado
        $this->assertFalse(Cache::has('dashboard_stats'));
    }

    /** @test */
    public function monitors_query_rate()
    {
        DB::enableQueryLog();

        // Ejecutar múltiples consultas
        for ($i = 0; $i < 10; $i++) {
            DB::table('users')->count();
        }

        $queryRate = $this->monitor->getQueriesPerMinute();

        $this->assertGreaterThanOrEqual(10, $queryRate);
    }

    /** @test */
    public function cache_hit_ratio_is_tracked()
    {
        // Simular hits y misses de caché
        Cache::increment('cache_hits', 80);
        Cache::increment('cache_misses', 20);

        $ratio = $this->monitor->getCacheHitRatio();

        $this->assertEquals(0.8, $ratio);
    }

    /** @test */
    public function performance_alerts_are_triggered()
    {
        // Simular condiciones que deberían disparar alertas
        Cache::put('slow_queries:' . now()->format('Y-m-d'), 15, 120);

        $this->assertTrue($this->monitor->shouldShowPerformanceAlert());
        $this->assertNotNull($this->monitor->getPerformanceAlertMessage());
    }

    /** @test */
    public function dashboard_optimization_improves_performance()
    {
        // Medir rendimiento sin optimización
        Cache::flush();
        $startTime = microtime(true);
        $this->get('/admin/dashboard');
        $unoptimizedTime = microtime(true) - $startTime;

        // Aplicar optimizaciones
        $this->optimizer->warmupDashboardCache();

        // Medir rendimiento con optimización
        $startTime = microtime(true);
        $this->get('/admin/dashboard');
        $optimizedTime = microtime(true) - $startTime;

        // Verificar mejora en rendimiento
        $this->assertLessThan($unoptimizedTime, $optimizedTime);
    }

    /** @test */
    public function redis_cache_is_working()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis is not available.');
        }

        // Limpiar caché Redis
        Redis::flushall();

        // Almacenar datos en caché
        $this->optimizer->warmupDashboardCache();

        // Verificar que los datos están en Redis
        $this->assertTrue(Redis::exists('dashboard_stats'));
    }

    /** @test */
    public function performance_metrics_are_logged()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->get('/admin/dashboard');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'performance_metric',
            'model_type' => 'dashboard'
        ]);
    }
}
