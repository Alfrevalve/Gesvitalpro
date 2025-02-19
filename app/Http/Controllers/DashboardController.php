<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\User;
use App\Models\Institucion;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas para las tarjetas
        $stats = [
            'surgeries_today' => Surgery::whereDate('surgery_date', Carbon::today())->count(),
            'completed_surgeries' => Surgery::where('status', Surgery::STATUS_COMPLETED)->count(),
            'pending_surgeries' => Surgery::where('status', Surgery::STATUS_PENDING)->count(),
            'available_equipment' => Equipment::where('status', 'available')->count(),
            'active_staff' => User::whereHas('roles', function($query) {
                $query->whereIn('slug', ['jefe_linea', 'instrumentista', 'vendedor']);
            })->count(),
            'institutions' => Institucion::where('estado', 'activo')->count()
        ];

        // Actividad reciente
        $recentActivity = ActivityLog::with(['user', 'subject'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Cirugías recientes
        $recentSurgeries = Surgery::with(['institucion', 'medico'])
            ->orderBy('surgery_date', 'desc')
            ->take(5)
            ->get();

        // Equipos en mantenimiento
        $maintenanceEquipment = Equipment::with(['line'])
            ->where('status', 'maintenance')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'recentActivity',
            'recentSurgeries',
            'maintenanceEquipment'
        ));
    }
}
