<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Inventario;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventarioControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventario_can_be_created()
    {
        $response = $this->post('/inventarios', [
            'nombre' => 'Test Inventario',
            'categoria' => 'Test Categoria',
            'quantity' => 10,
            'nivel_minimo' => 5,
            'ubicacion' => 'Test Ubicacion',
            'fecha_mantenimiento' => now(),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('inventarios', [
            'nombre' => 'Test Inventario',
        ]);
    }

    public function test_inventario_can_be_updated()
    {
        $inventario = Inventario::create([
            'nombre' => 'Test Inventario',
            'categoria' => 'Test Categoria',
            'quantity' => 10,
            'nivel_minimo' => 5,
            'ubicacion' => 'Test Ubicacion',
            'fecha_mantenimiento' => now(),
        ]);

        $response = $this->put('/inventarios/' . $inventario->id, [
            'nombre' => 'Updated Inventario',
            'quantity' => 15,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('inventarios', [
            'nombre' => 'Updated Inventario',
        ]);
    }

    public function test_inventario_can_be_deleted()
    {
        $inventario = Inventario::create([
            'nombre' => 'Test Inventario',
            'categoria' => 'Test Categoria',
            'quantity' => 10,
            'nivel_minimo' => 5,
            'ubicacion' => 'Test Ubicacion',
            'fecha_mantenimiento' => now(),
        ]);

        $response = $this->delete('/inventarios/' . $inventario->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('inventarios', [
            'nombre' => 'Test Inventario',
        ]);
    }
}
