<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Visita;
use App\Models\Patient;

class VisitaRelationshipsTest extends TestCase
{
    protected $visita;

    protected function setUp(): void
    {
        $this->visita = Visita::factory()->create(); // Assuming you have a Visita factory
    }

    public function testVisitaBelongsToPatient()
    {
        $patient = Patient::factory()->create();
        $this->visita->patient_id = $patient->id;
        $this->visita->save();

        $this->assertEquals($patient->id, $this->visita->patient->id); // Ensure the visita belongs to the patient
    }

    // Add more tests for other relationships in Visita model
}
