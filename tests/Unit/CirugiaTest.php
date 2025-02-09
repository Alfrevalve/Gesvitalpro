<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Cirugia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CirugiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cirugia_can_be_created()
    {
        $cirugia = Cirugia::create([
            'fecha_hora' => now(),
            'hospital' => 'Test Hospital',
            'equipo_requerido' => 'Test Equipo',
            'consumibles' => 'Test Consumibles',
            'personal_asignado' => 'Test Personal',
            'tiempo_estimado' => '1 hora',
            'patient_id' => 1, // Asegúrate de que el paciente exista
        ]);

        $this->assertDatabaseHas('cirugias', [
            'hospital' => 'Test Hospital',
        ]);
    }

    public function test_cirugia_can_be_updated()
    {
        $cirugia = Cirugia::create([
            'fecha_hora' => now(),
            'hospital' => 'Test Hospital',
            'equipo_requerido' => 'Test Equipo',
            'consumibles' => 'Test Consumibles',
            'personal_asignado' => 'Test Personal',
            'tiempo_estimado' => '1 hora',
            'patient_id' => 1, // Asegúrate de que el paciente exista
        ]);

        $cirugia->update(['hospital' => 'Updated Hospital']);

        $this->assertDatabaseHas('cirugias', [
            'hospital' => 'Updated Hospital',
        ]);
    }

    public function test_cirugia_can_be_deleted()
    {
        $cirugia = Cirugia::create([
            'fecha_hora' => now(),
            'hospital' => 'Test Hospital',
            'equipo_requerido' => 'Test Equipo',
            'consumibles' => 'Test Consumibles',
            'personal_asignado' => 'Test Personal',
            'tiempo_estimado' => '1 hora',
            'patient_id' => 1, // Asegúrate de que el paciente exista
        ]);

        $cirugia->delete();

        $this->assertDatabaseMissing('cirugias', [
            'hospital' => 'Test Hospital',
        ]);
    }
}
