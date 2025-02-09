<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Cirugia;
use App\Models\Patient;

class CirugiaRelationshipsTest extends TestCase
{
    protected $cirugia;

    protected function setUp(): void
    {
        $this->cirugia = Cirugia::factory()->create(); // Assuming you have a Cirugia factory
    }

    public function testCirugiaBelongsToPatient()
    {
        $patient = Patient::factory()->create();
        $this->cirugia->patient_id = $patient->id;
        $this->cirugia->save();

        $this->assertEquals($patient->id, $this->cirugia->patient->id); // Ensure the cirugia belongs to the patient
    }

    // Add more tests for other relationships in Cirugia model
}
