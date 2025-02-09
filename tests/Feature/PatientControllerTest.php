<?php

namespace Tests\Feature;

use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_patient()
    {
        $response = $this->post(route('pacientes.store'), [
            'name' => 'Juan Perez',
            'email' => 'juan@example.com',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('pacientes', ['email' => 'juan@example.com']);
    }

    public function test_can_list_patients()
    {
        $paciente = Paciente::factory()->create();

        $response = $this->get(route('pacientes.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => $paciente->name]);
    }

    public function test_can_update_patient()
    {
        $paciente = Paciente::factory()->create();

        $response = $this->put(route('pacientes.update', $paciente->id), [
            'name' => 'Juan Updated',
            'email' => 'juan_updated@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pacientes', ['email' => 'juan_updated@example.com']);
    }

    public function test_can_delete_patient()
    {
        $paciente = Paciente::factory()->create();

        $response = $this->delete(route('pacientes.destroy', $paciente->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('pacientes', ['id' => $paciente->id]);
    }
}
