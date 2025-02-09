<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Patient;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    public function testPatientCanBeCreated()
    {
        $response = $this->post('/patients', [
            'name' => 'Test Patient',
            'age' => 30,
            'gender' => 'Male',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('patients', [
            'name' => 'Test Patient',
        ]);
    }

    public function testPatientCanBeRetrieved()
    {
        $patient = Patient::factory()->create();

        $response = $this->get('/patients/' . $patient->id);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $patient->id,
            'name' => $patient->name,
        ]);
    }
}
