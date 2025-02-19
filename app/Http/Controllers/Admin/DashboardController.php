<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\User;
use App\Models\Institucion;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas para el dashboard
        $stats = [
            'total_surgeries' => Surgery::count(),
            'total_equipment' => Equipment::count(),
            'total_users' => User::count(),
            'total_institutions' => Institucion::count(),
            'pending_surgeries' => Surgery::where('status', 'pending')->count(),
            'recent_surgeries' => Surgery::with(['medico', 'institucion'])
                                       ->latest()
                                       ->take(5)
                                       ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function profile()
    {
        return view('admin.profile');
    }
}
