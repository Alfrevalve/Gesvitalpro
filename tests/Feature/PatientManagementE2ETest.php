<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Patient;

class PatientManagementE2ETest extends TestCase
{
    use RefreshDatabase;

    public function testPatientCanBeCreatedAndRetrieved()
    {
        // Create a patient
        $response = $this->post('/patients', [
            'name' => 'Test Patient',
            'age' => 30,
            'gender' => 'Male',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('patients', [
            'name' => 'Test Patient',
        ]);

        // Retrieve the patient
        $patient = Patient::first();
        $response = $this->get('/patients/' . $patient->id);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $patient->id,
            'name' => $patient->name,
        ]);
    }
}
