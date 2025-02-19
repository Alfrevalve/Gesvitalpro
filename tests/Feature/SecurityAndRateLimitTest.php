<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class SecurityAndRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
        RateLimiter::clear('login');
        Cache::flush();
    }

    /** @test */
    public function rate_limiting_blocks_excessive_requests()
    {
        $user = User::factory()->create(['role' => 'user']);

        // Realizar solicitudes hasta alcanzar el límite
        for ($i = 0; $i < 60; $i++) {
            $response = $this->actingAs($user)->get('/admin/dashboard');
            $response->assertStatus(200);
        }

        // La siguiente solicitud debería ser bloqueada
        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(429);
    }

    /** @test */
    public function different_rate_limits_per_role()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // Usuario normal alcanza límite más rápido
        for ($i = 0; $i < 61; $i++) {
            $response = $this->actingAs($user)->get('/admin/dashboard');
        }
        $response->assertStatus(429);

        // Admin tiene límite más alto
        for ($i = 0; $i < 61; $i++) {
            $response = $this->actingAs($admin)->get('/admin/dashboard');
        }
        $response->assertStatus(200);
    }

    /** @test */
    public function detects_suspicious_behavior()
    {
        $user = User::factory()->create();

        // Simular múltiples IPs sospechosas
        $ips = ['192.168.1.1', '192.168.1.2', '192.168.1.3', '192.168.1.4', '192.168.1.5', '192.168.1.6'];

        foreach ($ips as $ip) {
            $response = $this->actingAs($user)
                ->withServerVariables(['REMOTE_ADDR' => $ip])
                ->get('/admin/dashboard');
        }

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'suspicious_activity',
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function logs_failed_login_attempts()
    {
        // Intentar login con credenciales incorrectas
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post('/login', [
                'email' => 'wrong@email.com',
                'password' => 'wrongpassword'
            ]);
        }

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'failed_login',
            'ip_address' => request()->ip()
        ]);
    }

    /** @test */
    public function blocks_suspicious_user_agents()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders([
                'User-Agent' => 'python-requests/2.25.1'
            ])
            ->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function rate_limit_resets_after_cooldown()
    {
        $user = User::factory()->create();

        // Alcanzar el límite
        for ($i = 0; $i < 61; $i++) {
            $this->actingAs($user)->get('/admin/dashboard');
        }

        // Simular espera del período de cooldown
        $this->travel(1)->minutes();

        // Debería poder hacer solicitudes nuevamente
        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function sensitive_routes_have_stricter_limits()
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Rutas sensibles tienen límites más estrictos
        for ($i = 0; $i < 6; $i++) {
            $response = $this->actingAs($user)->post('/admin/surgeries');
        }

        $response->assertStatus(429);
    }

    /** @test */
    public function logs_denied_access_attempts()
    {
        $user = User::factory()->create(['role' => 'user']);

        // Intentar acceder a ruta protegida
        $response = $this->actingAs($user)
            ->get('/admin/performance');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'access_denied',
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function handles_concurrent_requests()
    {
        $user = User::factory()->create();

        // Simular solicitudes concurrentes
        $promises = [];
        for ($i = 0; $i < 5; $i++) {
            $promises[] = $this->actingAs($user)->get('/admin/dashboard');
        }

        // Verificar que todas las solicitudes fueron manejadas
        foreach ($promises as $response) {
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function rate_limit_headers_are_present()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        $response->assertHeader('X-RateLimit-Remaining');
        $response->assertHeader('X-RateLimit-Reset');
    }

    /** @test */
    public function ip_based_blocking_works()
    {
        // Simular múltiples intentos fallidos desde la misma IP
        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', [
                'email' => 'wrong@email.com',
                'password' => 'wrongpassword'
            ]);
        }

        // La siguiente solicitud debería ser bloqueada
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /** @test */
    public function csrf_protection_is_working()
    {
        $user = User::factory()->create();

        // Intentar solicitud POST sin token CSRF
        $response = $this->actingAs($user)
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->post('/admin/surgeries', [
                'patient_name' => 'Test Patient'
            ]);

        $response->assertStatus(419);
    }

    /** @test */
    public function validates_request_signatures()
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Solicitud con firma inválida
        $response = $this->actingAs($user)
            ->get('/admin/dashboard', [
                'signature' => 'invalid_signature'
            ]);

        $response->assertStatus(403);
    }
}
