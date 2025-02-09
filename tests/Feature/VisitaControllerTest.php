<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VisitaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $response = $this->get(route('visitas.index'));
        $response->assertStatus(200);
    }

    public function testCreate()
    {
        $response = $this->get(route('visitas.create'));
        $response->assertStatus(200);
    }

    public function testStore()
    {
        $data = [
            'fecha_hora' => now(),
            'institucion' => 'Test Institution',
            'persona_contactada' => 'John Doe',
            'motivo' => 'Test Reason',
            'seguimiento_requerido' => true,
        ];

        $response = $this->post(route('visitas.store'), $data);
        $response->assertRedirect(route('visitas.index'));
        $this->assertDatabaseHas('visitas', $data);
    }

    public function testEdit()
    {
        $visita = Visita::factory()->create();
        $response = $this->get(route('visitas.edit', $visita));
        $response->assertStatus(200);
    }

    public function testUpdate()
    {
        $visita = Visita::factory()->create();
        $data = [
            'fecha_hora' => now(),
            'institucion' => 'Updated Institution',
            'persona_contactada' => 'Jane Doe',
            'motivo' => 'Updated Reason',
            'seguimiento_requerido' => false,
        ];

        $response = $this->put(route('visitas.update', $visita), $data);
        $response->assertRedirect(route('visitas.index'));
        $this->assertDatabaseHas('visitas', $data);
    }

    public function testDestroy()
    {
        $visita = Visita::factory()->create();
        $response = $this->delete(route('visitas.destroy', $visita));
        $response->assertRedirect(route('visitas.index'));
        $this->assertDeleted($visita);
    }
}
