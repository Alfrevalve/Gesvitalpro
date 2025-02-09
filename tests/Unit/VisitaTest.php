<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Visita;

class VisitaTest extends TestCase
{
    public function testVisitaCreation()
    {
        $data = [
            'patient_id' => 1, // Assuming a patient with ID 1 exists
            'date' => '2023-10-01',
            'notes' => 'Test visit notes',
        ];
        
        $visita = Visita::create($data);
        $this->assertNotNull($visita->id); // Ensure the visita was created
    }
}
