<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Patient;

class PatientTest extends TestCase
{
    public function testPatientCreation()
    {
        $data = [
            'name' => 'Test Patient',
            'age' => 30,
            'gender' => 'Male',
        ];
        
        $patient = Patient::create($data);
        $this->assertNotNull($patient->id); // Ensure the patient was created
    }
}
