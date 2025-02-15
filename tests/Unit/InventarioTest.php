<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Inventario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InventarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventario_can_be_created()
    {
        $inventario = Inventario::create([
            'nombre' => 'Test Inventario',
            'categoria' => 'Test Categoria',
            'quantity' => 10,
            'nivel_minimo' => 5,
            'ubicacion' => 'Test Ubicacion',
            'fecha_mantenimiento' => now(),
        ]);

        $this->assertDatabaseHas('inventarios', [
            'nombre' => 'Test Inventario',
        ]);
    }

    public function test_inventario_creation_fails_without_nombre()
    {
        $this->expectException(ModelNotFoundException::class);
        
        Inventario::create([
            'categoria' => 'Test Categoria',
            'quantity' => 10,
            'nivel_minimo' => 5,
            'ubicacion' => 'Test Ubicacion',
            'fecha_mantenimiento' => now(),
        ]);
    }

    public function test_inventario_creation_fails_with_negative_quantity()
    {
        $this->expectException(\Exception::class); // Adjust based on your validation logic
        
        Inventario::create([
            'nombre' => 'Test Inventario',
            'categoria' => 'Test Categoria',
            'quantity' => -5, // Invalid quantity
            'nivel_minimo' => 5,
            'ubicacion' => 'Test Ubicacion',
            'fecha_mantenimiento' => now(),
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

        $inventario->update(['nombre' => 'Updated Inventario']);

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

        $inventario->delete();

        $this->assertDatabaseMissing('inventarios', [
            'nombre' => 'Test Inventario',
        ]);
    }

    public function test_inventario_deletion_fails_for_non_existent_record()
    {
        $this->expectException(ModelNotFoundException::class);
        
        $inventario = Inventario::find(999); // Assuming this ID does not exist
        $inventario->delete();
    }
}
