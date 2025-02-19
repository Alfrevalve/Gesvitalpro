<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Surgery;
use App\Models\Visita;
use App\Services\DashboardOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    /** @test */
    public function dashboard_loads_successfully()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSeeLivewire('filament.pages.optimized-dashboard');
    }

    /** @test */
    public function dashboard_shows_correct_stats()
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Crear datos de prueba
        Surgery::factory()->count(5)->create(['status' => 'pending']);
        Surgery::factory()->count(3)->create(['status' => 'completed']);
        Visita::factory()->count(4)->create(['estado' => 'pendiente']);

        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        $response->assertSee('5 cirugías pendientes');
        $response->assertSee('4 visitas programadas');
    }

    /** @test */
    public function cache_is_working_correctly()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $optimizer = app(DashboardOptimizer::class);

        // Limpiar caché existente
        Cache::flush();

        // Primera carga - debería generar caché
        $this->actingAs($user)->get('/admin/dashboard');
        $this->assertTrue(Cache::has('dashboard_stats'));

        // Segunda carga - debería usar caché
        $startTime = microtime(true);
        $this->actingAs($user)->get('/admin/dashboard');
        $endTime = microtime(true);

        // La segunda carga debería ser más rápida
        $this->assertLessThan(0.1, $endTime - $startTime);
    }

    /** @test */
    public function performance_monitoring_is_active()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'page_load',
            'model_type' => 'dashboard'
        ]);
    }

    /** @test */
    public function rate_limiting_is_working()
    {
        $user = User::factory()->create(['role' => 'user']);

        // Realizar múltiples solicitudes rápidas
        for ($i = 0; $i < 61; $i++) {
            $response = $this->actingAs($user)->get('/admin/dashboard');
        }

        // La última solicitud debería ser limitada
        $response->assertStatus(429);
    }

    /** @test */
    public function widgets_load_correctly()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        $response->assertSeeLivewire('filament.widgets.optimized-stats-overview-widget');
        $response->assertSeeLivewire('filament.widgets.optimized-proximas-visitas-widget');
    }

    /** @test */
    public function dashboard_updates_correctly()
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Estado inicial
        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        // Crear nueva cirugía
        Surgery::factory()->create(['status' => 'pending']);

        // Actualizar dashboard
        $response = $this->actingAs($user)
            ->post('/admin/dashboard/refresh');

        $response->assertStatus(200);
        $this->assertTrue(Cache::has('dashboard_stats'));
    }

    /** @test */
    public function permissions_are_enforced()
    {
        // Usuario regular
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->get('/admin/dashboard/performance');

        $response->assertStatus(403);

        // Admin
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get('/admin/dashboard/performance');

        $response->assertStatus(200);
    }

    /** @test */
    public function database_queries_are_optimized()
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Contar queries antes de optimización
        $queriesBeforeOptimization = count(\DB::getQueryLog());

        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        // Contar queries después de optimización
        $queriesAfterOptimization = count(\DB::getQueryLog());

        // Verificar que el número de queries no exceda el límite esperado
        $this->assertLessThan(10, $queriesAfterOptimization - $queriesBeforeOptimization);
    }

    /** @test */
    public function memory_usage_is_within_limits()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $initialMemory = memory_get_usage();

        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        $finalMemory = memory_get_usage();

        // Verificar que el uso de memoria no exceda 50MB
        $this->assertLessThan(50 * 1024 * 1024, $finalMemory - $initialMemory);
    }
}
