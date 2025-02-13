<?php

namespace Tests\Feature;

use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_patient()
    {
        $response = $this->post(route('pacientes.store'), [
            'name' => 'Juan Perez',
            'email' => 'juan@example.com',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('pacientes', ['email' => 'juan@example.com']);
    }

    public function test_can_list_patients()
    {
        $paciente = Paciente::factory()->create();

        $response = $this->get(route('pacientes.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => $paciente->name]);
    }

    public function test_can_update_patient()
    {
        $paciente = Paciente::factory()->create();

        $response = $this->put(route('pacientes.update', $paciente->id), [
            'name' => 'Juan Updated',
            'email' => 'juan_updated@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pacientes', ['email' => 'juan_updated@example.com']);
    }

    public function test_can_delete_patient()
    {
        $paciente = Paciente::factory()->create();

        $response = $this->delete(route('pacientes.destroy', $paciente->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('pacientes', ['id' => $paciente->id]);
    }

    // New tests for error handling

    public function test_cannot_create_patient_with_invalid_data()
    {
        $response = $this->post(route('pacientes.store'), [
            'name' => '', // Invalid name
            'email' => 'invalid-email', // Invalid email
        ]);

        $response->assertStatus(422); // Unprocessable Entity
        $response->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_cannot_update_nonexistent_patient()
    {
        $response = $this->put(route('pacientes.update', 999), [ // Nonexistent ID
            'name' => 'Juan Updated',
            'email' => 'juan_updated@example.com',
        ]);

        $response->assertStatus(404); // Not Found
    }

    public function test_cannot_delete_nonexistent_patient()
    {
        $response = $this->delete(route('pacientes.destroy', 999)); // Nonexistent ID

        $response->assertStatus(404); // Not Found
    }

    public function test_cannot_create_patient_with_empty_fields()
    {
        $response = $this->post(route('pacientes.store'), [
            'name' => '', // Invalid name
            'email' => '', // Invalid email
        ]);

        $response->assertStatus(422); // Unprocessable Entity
        $response->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_cannot_update_patient_with_invalid_data()
    {
        $paciente = Paciente::factory()->create();

        $response = $this->put(route('pacientes.update', $paciente->id), [
            'name' => '', // Invalid name
            'email' => 'invalid-email', // Invalid email
        ]);

        $response->assertStatus(422); // Unprocessable Entity
        $response->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_database_error_handling_on_update()
    {
        $paciente = Paciente::factory()->create();

        // Simulate a database error
        \DB::shouldReceive('table->update')->andThrow(new \Exception('Database error'));

        $response = $this->put(route('pacientes.update', $paciente->id), [
            'name' => 'Juan Updated',
            'email' => 'juan_updated@example.com',
        ]);

        $response->assertStatus(500); // Internal Server Error
    }

    public function test_cannot_create_patient_with_duplicate_email()
    {
        $paciente = Paciente::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->post(route('pacientes.store'), [
            'name' => 'Juan Duplicate',
            'email' => 'duplicate@example.com', // Duplicate email
        ]);

        $response->assertStatus(422); // Unprocessable Entity
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_database_error_handling_on_create()
    {
        // Simulate a database error
        \DB::shouldReceive('table->insert')->andThrow(new \Exception('Database error'));

        $response = $this->post(route('pacientes.store'), [
            'name' => 'Juan Perez',
            'email' => 'juan@example.com',
        ]);

        $response->assertStatus(500); // Internal Server Error
    }
}
