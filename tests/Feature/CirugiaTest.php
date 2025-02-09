<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Cirugia;

class CirugiaTest extends TestCase
{
    use RefreshDatabase;

    public function testCirugiaCanBeCreated()
    {
        $response = $this->post('/cirugias', [
            'patient_id' => 1, // Assuming a patient with ID 1 exists
            'surgery_date' => '2023-10-01',
            'description' => 'Test surgery description',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('cirugias', [
            'description' => 'Test surgery description',
        ]);
    }

    public function testCirugiaCanBeRetrieved()
    {
        $cirugia = Cirugia::factory()->create();

        $response = $this->get('/cirugias/' . $cirugia->id);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $cirugia->id,
            'description' => $cirugia->description,
        ]);
    }
}
