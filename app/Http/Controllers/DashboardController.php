<?php

namespace App\Http\Controllers;

use App\Models\Paciente; // Use the existing Paciente model
use App\Models\Visita;
use App\Models\Cirugia;
use App\Models\Attendance; // Include Attendance model
use App\Models\Instrumentista; // Include Instrumentista model
use App\Models\Doctor; // Include Doctor model
use App\Models\Institucion; // Include Institucion model
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_patients' => Paciente::count(), // Cambiar a pacientes
            'total_visits' => Visita::count(),
            'today_surgeries' => Cirugia::whereDate('fecha_hora', today())->count(),
            'today_new_patients' => Paciente::whereDate('created_at', today())->count(), // Cambiar a pacientes
            'today_staff_attendance' => Attendance::whereDate('date', today())->count(),
            'recent_patients' => Paciente::latest()->take(5)->get(), // Cambiar a pacientes
            'total_surgeries' => Cirugia::count(), // Total de cirugías
            'total_instrumentistas' => Instrumentista::count(), // Total de instrumentistas
            'total_doctors' => Doctor::count(), // Total de doctores
            'total_institutions' => Institucion::count(), // Total de instituciones
        ];

        return view('dashboard', compact('data')); // Pass data to the dashboard view
    }
}
