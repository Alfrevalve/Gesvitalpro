<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PacienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_paciente_can_be_created()
    {
        $paciente = Paciente::create([
            'nombre' => 'Test Paciente',
            'apellido' => 'Test Apellido',
            'institucion' => 'Test Institucion',
        ]);

        $this->assertDatabaseHas('pacientes', [
            'nombre' => 'Test Paciente',
        ]);
    }

    public function test_paciente_can_be_updated()
    {
        $paciente = Paciente::create([
            'nombre' => 'Test Paciente',
            'apellido' => 'Test Apellido',
            'institucion' => 'Test Institucion',
        ]);

        $paciente->update(['nombre' => 'Updated Paciente']);

        $this->assertDatabaseHas('pacientes', [
            'nombre' => 'Updated Paciente',
        ]);
    }

    public function test_paciente_can_be_deleted()
    {
        $paciente = Paciente::create([
            'nombre' => 'Test Paciente',
            'apellido' => 'Test Apellido',
            'institucion' => 'Test Institucion',
        ]);

        $paciente->delete();

        $this->assertDatabaseMissing('pacientes', [
            'nombre' => 'Test Paciente',
        ]);
    }
}
