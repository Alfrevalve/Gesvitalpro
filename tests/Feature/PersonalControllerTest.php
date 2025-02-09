<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Personal;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersonalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_can_be_created()
    {
        $response = $this->post('/personales', [
            'nombre' => 'Test Personal',
            'apellido' => 'Test Apellido',
            'institucion' => 'Test Institucion',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('personals', [
            'nombre' => 'Test Personal',
        ]);
    }

    public function test_personal_can_be_updated()
    {
        $personal = Personal::create([
            'nombre' => 'Test Personal',
            'apellido' => 'Test Apellido',
            'institucion' => 'Test Institucion',
        ]);

        $response = $this->put('/personales/' . $personal->id, [
            'nombre' => 'Updated Personal',
            'apellido' => 'Updated Apellido',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('personals', [
            'nombre' => 'Updated Personal',
        ]);
    }

    public function test_personal_can_be_deleted()
    {
        $personal = Personal::create([
            'nombre' => 'Test Personal',
            'apellido' => 'Test Apellido',
            'institucion' => 'Test Institucion',
        ]);

        $response = $this->delete('/personales/' . $personal->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('personals', [
            'nombre' => 'Test Personal',
        ]);
    }
}
