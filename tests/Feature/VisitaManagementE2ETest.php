<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Visita;

class VisitaManagementE2ETest extends TestCase
{
    use RefreshDatabase;

    public function testVisitaCanBeCreatedAndRetrieved()
    {
        // Create a visita
        $response = $this->post('/visitas', [
            'patient_id' => 1, // Assuming a patient with ID 1 exists
            'date' => '2023-10-01',
            'notes' => 'Test visit notes',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('visitas', [
            'notes' => 'Test visit notes',
        ]);

        // Retrieve the visita
        $visita = Visita::first();
        $response = $this->get('/visitas/' . $visita->id);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $visita->id,
            'notes' => $visita->notes,
        ]);
    }
}
