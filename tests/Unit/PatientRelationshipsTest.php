<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Patient;
use App\Models\Visita;

class PatientRelationshipsTest extends TestCase
{
    protected $patient;

    protected function setUp(): void
    {
        $this->patient = Patient::factory()->create(); // Assuming you have a Patient factory
    }

    public function testPatientHasVisitas()
    {
        $visita = Visita::factory()->create(['patient_id' => $this->patient->id]);
        
        $this->assertTrue($this->patient->visitas->contains($visita)); // Ensure the patient has the visita
    }

    // Add more tests for other relationships in Patient model
}
