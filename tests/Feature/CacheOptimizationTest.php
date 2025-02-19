<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Surgery;
use App\Models\Visita;
use App\Services\DashboardOptimizer;
use App\Services\PerformanceMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Event;

class CacheOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardOptimizer $optimizer;
    protected PerformanceMonitor $monitor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
        $this->optimizer = app(DashboardOptimizer::class);
        $this->monitor = app(PerformanceMonitor::class);
        Cache::flush();
    }

    /** @test */
    public function dashboard_cache_warming_works()
    {
        // Verificar que no hay caché inicialmente
        $this->assertFalse(Cache::has('dashboard_stats'));

        // Ejecutar precalentamiento de caché
        $this->optimizer->warmupDashboardCache();

        // Verificar que el caché existe
        $this->assertTrue(Cache::has('dashboard_stats'));

        // Verificar estructura del caché
        $stats = Cache::get('dashboard_stats');
        $this->assertArrayHasKey('surgeries', $stats);
        $this->assertArrayHasKey('equipment', $stats);
        $this->assertArrayHasKey('visits', $stats);
    }

    /** @test */
    public function cache_invalidation_on_model_updates()
    {
        // Generar caché inicial
        $this->optimizer->warmupDashboardCache();

        // Crear nueva cirugía
        $surgery = Surgery::factory()->create([
            'status' => 'pending'
        ]);

        // Verificar que el caché fue invalidado
        $this->assertFalse(Cache::has('dashboard_stats'));

        // Verificar que el caché se regenera correctamente
        $this->optimizer->warmupDashboardCache();
        $stats = Cache::get('dashboard_stats');
        $this->assertEquals(
            $surgery->id,
            $stats['surgeries']['pending']
        );
    }

    /** @test */
    public function selective_cache_clearing()
    {
        // Generar diferentes tipos de caché
        Cache::put('dashboard_stats', ['key' => 'value'], 60);
        Cache::put('user_preferences', ['theme' => 'dark'], 60);

        // Limpiar selectivamente
        $this->optimizer->clearDashboardCache();

        // Verificar que solo se limpió el caché del dashboard
        $this->assertFalse(Cache::has('dashboard_stats'));
        $this->assertTrue(Cache::has('user_preferences'));
    }

    /** @test */
    public function cache_performance_improvement()
    {
        // Medir tiempo sin caché
        $startTime = microtime(true);
        $this->get('/admin/dashboard');
        $uncachedTime = microtime(true) - $startTime;

        // Precalentar caché
        $this->optimizer->warmupDashboardCache();

        // Medir tiempo con caché
        $startTime = microtime(true);
        $this->get('/admin/dashboard');
        $cachedTime = microtime(true) - $startTime;

        // Verificar mejora en rendimiento
        $this->assertLessThan($uncachedTime, $cachedTime);
    }

    /** @test */
    public function cache_handles_concurrent_requests()
    {
        $users = User::factory()->count(5)->create();

        // Simular solicitudes concurrentes
        $responses = [];
        foreach ($users as $user) {
            $responses[] = $this->actingAs($user)
                ->get('/admin/dashboard');
        }

        // Verificar que todas las solicitudes fueron exitosas
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // Verificar que el caché se mantiene consistente
        $this->assertTrue(Cache::has('dashboard_stats'));
    }

    /** @test */
    public function cache_handles_race_conditions()
    {
        Event::fake();

        // Simular múltiples actualizaciones simultáneas
        $surgeries = Surgery::factory()->count(5)->create();

        foreach ($surgeries as $surgery) {
            $surgery->update(['status' => 'completed']);
        }

        // Verificar que el caché se mantiene consistente
        $this->optimizer->warmupDashboardCache();
        $stats = Cache::get('dashboard_stats');

        $this->assertEquals(
            5,
            $stats['surgeries']['completed']
        );
    }

    /** @test */
    public function cache_respects_ttl()
    {
        $this->optimizer->warmupDashboardCache();

        // Avanzar tiempo más allá del TTL
        $this->travel(6)->minutes();

        // Verificar que el caché expiró
        $this->assertFalse(Cache::has('dashboard_stats'));
    }

    /** @test */
    public function cache_handles_large_datasets()
    {
        // Crear gran cantidad de datos
        Surgery::factory()->count(1000)->create();
        Visita::factory()->count(1000)->create();

        // Verificar que el caché maneja los datos eficientemente
        $this->optimizer->warmupDashboardCache();

        $stats = Cache::get('dashboard_stats');
        $this->assertNotNull($stats);
        $this->assertArrayHasKey('surgeries', $stats);
    }

    /** @test */
    public function cache_handles_redis_failover()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis is not available.');
        }

        // Simular fallo de Redis
        Redis::flushall();

        // Verificar que el sistema sigue funcionando
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function optimization_suggestions_are_valid()
    {
        // Generar datos de prueba
        Surgery::factory()->count(100)->create();

        // Obtener sugerencias de optimización
        $suggestions = $this->monitor->suggestOptimizations();

        // Verificar que las sugerencias son válidas
        foreach ($suggestions as $suggestion) {
            $this->assertArrayHasKey('type', $suggestion);
            $this->assertArrayHasKey('reason', $suggestion);
            $this->assertArrayHasKey('impact', $suggestion);
        }
    }

    /** @test */
    public function cache_warming_command_works()
    {
        // Ejecutar comando de precalentamiento
        $this->artisan('dashboard:cache-warm')
            ->assertSuccessful();

        // Verificar que el caché fue generado
        $this->assertTrue(Cache::has('dashboard_stats'));
    }

    /** @test */
    public function cache_adapts_to_load()
    {
        // Simular diferentes niveles de carga
        for ($i = 0; $i < 3; $i++) {
            $this->optimizer->warmupDashboardCache();

            // Crear más datos
            Surgery::factory()->count(50)->create();

            // Verificar que el caché se adapta
            $stats = Cache::get('dashboard_stats');
            $this->assertEquals(
                ($i + 1) * 50,
                $stats['surgeries']['total']
            );
        }
    }
}
