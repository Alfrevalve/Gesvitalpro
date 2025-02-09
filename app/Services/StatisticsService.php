<?php

namespace App\Services;

use App\Models\Cirugia;
use App\Models\Instrumentista;
use App\Models\Doctor;
use App\Models\Institucion;

class StatisticsService
{
    public function totalCirugiasPorInstrumentista()
    {
        return Instrumentista::withCount('cirugias')->get();
    }

    public function calculateUserStatistics()
    {
        // Implement logic to calculate user statistics
    }

    public function calculateAttendanceStatistics()
    {
        // Implement logic to calculate attendance statistics
    }

    public function generateCharts()
    {
        // Implement logic to visualize data
    }

    public function tiposCirugiasPorDoctor()
    {
        return Doctor::with(['cirugias' => function($query) {
            $query->select('type');
        }])->get();
    }

    public function cirugiasPorInstitucion()
    {
        return Institucion::withCount('cirugias')->get();
    }
}
