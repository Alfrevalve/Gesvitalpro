<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Inventario;

class InventarioManagementE2ETest extends TestCase
{
    use RefreshDatabase;

    public function testInventarioCanBeCreatedAndRetrieved()
    {
        // Create an inventario
        $response = $this->post('/inventarios', [
            'item_name' => 'Test Item',
            'quantity' => 100,
            'location' => 'Test Location',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('inventarios', [
            'item_name' => 'Test Item',
        ]);

        // Retrieve the inventario
        $inventario = Inventario::first();
        $response = $this->get('/inventarios/' . $inventario->id);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $inventario->id,
            'item_name' => $inventario->item_name,
        ]);
    }
}
