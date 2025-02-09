<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cirugia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CirugiaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cirugia_can_be_created()
    {
        $response = $this->post('/cirugias', [
            'fecha_cirugia' => now(),
            'hospital' => 'Test Hospital',
            'equipo_requerido' => 'Test Equipo',
            'consumibles' => 'Test Consumibles',
            'personal_asignado' => 'Test Personal',
            'tiempo_estimado' => '1 hora',
            'patient_id' => 1, // Asegúrate de que el paciente exista
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('cirugias', [
            'hospital' => 'Test Hospital',
        ]);
    }

    public function test_cirugia_can_be_updated()
    {
        $cirugia = Cirugia::create([
            'fecha_cirugia' => now(),
            'hospital' => 'Test Hospital',
            'equipo_requerido' => 'Test Equipo',
            'consumibles' => 'Test Consumibles',
            'personal_asignado' => 'Test Personal',
            'tiempo_estimado' => '1 hora',
            'patient_id' => 1, // Asegúrate de que el paciente exista
        ]);

        $response = $this->put('/cirugias/' . $cirugia->id, [
            'hospital' => 'Updated Hospital',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cirugias', [
            'hospital' => 'Updated Hospital',
        ]);
    }

    public function test_cirugia_can_be_deleted()
    {
        $cirugia = Cirugia::create([
            'fecha_cirugia' => now(),
            'hospital' => 'Test Hospital',
            'equipo_requerido' => 'Test Equipo',
            'consumibles' => 'Test Consumibles',
            'personal_asignado' => 'Test Personal',
            'tiempo_estimado' => '1 hora',
            'patient_id' => 1, // Asegúrate de que el paciente exista
        ]);

        $response = $this->delete('/cirugias/' . $cirugia->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cirugias', [
            'hospital' => 'Test Hospital',
        ]);
    }
}
