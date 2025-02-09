<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_session_data()
    {
        // Simular un usuario autenticado
        $user = \App\Models\User::factory()->create([
            'nombre' => 'Test User',
            'email' => 'test@example.com',
            'contrasena' => bcrypt('password'),
            'rol_id' => 1, // Asignar un rol existente
        ]);

        $this->actingAs($user)
            ->get('/dashboard') // Acceder al dashboard
            ->assertStatus(200);

        // Verificar que la sesión se haya almacenado
        $this->assertNotNull(session('usuario_id'));
    }
}