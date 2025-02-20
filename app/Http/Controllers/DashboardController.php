<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\Institucion;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Basic stats for regular users
        $stats = [
            'my_surgeries' => Surgery::where('user_id', $user->id)->count(),
            'upcoming_surgeries' => Surgery::where('user_id', $user->id)
                                         ->where('status', 'pending')
                                         ->count(),
            'recent_surgeries' => Surgery::where('user_id', $user->id)
                                       ->with(['medico', 'institucion'])
                                       ->latest()
                                       ->take(5)
                                       ->get(),
        ];

        return view('dashboard', compact('stats'));
    }
}
