<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Instrumentista;
use App\Models\Doctor;
use App\Models\Institucion;
use App\Models\Cirugia;

class StatisticsTest extends TestCase
{
    public function testTotalCirugiasPorInstrumentista()
    {
        $instrumentista = Instrumentista::factory()->create();
        Cirugia::factory()->count(5)->create(['instrumentista_id' => $instrumentista->id]);

        $response = $this->get(route('statistics.index'));

        $response->assertStatus(200);
        $this->assertContains($instrumentista->nombre, $response->getContent());
        $this->assertContains('5', $response->getContent());
    }

    public function testTiposCirugiasPorDoctor()
    {
        $doctor = Doctor::factory()->create();
        Cirugia::factory()->count(3)->create(['doctor_id' => $doctor->id, 'type' => 'Cirugía General']);

        $response = $this->get(route('statistics.index'));

        $response->assertStatus(200);
        $this->assertContains($doctor->nombre, $response->getContent());
        $this->assertContains('Cirugía General', $response->getContent());
    }

    public function testCirugiasPorInstitucion()
    {
        $institucion = Institucion::factory()->create();
        Cirugia::factory()->count(4)->create(['institucion_id' => $institucion->id]);

        $response = $this->get(route('statistics.index'));

        $response->assertStatus(200);
        $this->assertContains($institucion->nombre, $response->getContent());
        $this->assertContains('4', $response->getContent());
    }
}
