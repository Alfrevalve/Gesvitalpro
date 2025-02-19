<?php

namespace App\Http\Controllers;

use App\Models\Line;
use App\Models\User;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LineController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        $user = Auth::user();
        $query = Line::withCount(['equipment', 'surgeries', 'staff']);

        // Si no es admin o gerente, filtrar por líneas asignadas
        if (!$user->isAdmin() && !$user->isGerente()) {
            $query->whereHas('staff', function($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $lines = $query->latest()->paginate(10);

        return view('lines.index', compact('lines'));
    }

    public function create()
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerente()) {
            abort(403, 'No autorizado');
        }

        return view('lines.create');
    }

    public function store(StoreLineRequest $request)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerente()) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validated();

        Line::create($validated);

        return redirect()
            ->route('lines.index')
            ->with('success', 'Línea creada exitosamente.');
    }

    public function show(Line $line)
    {
        if (!Auth::user()->canManageLine($line)) {
            abort(403, 'No autorizado');
        }

        $line->load([
            'equipment' => function($query) {
                $query->latest();
            },
            'staff',
            'surgeries' => function($query) {
                $query->latest()->take(5);
            }
        ]);

        $equipmentStats = [
            'total' => $line->equipment()->count(),
            'available' => $line->equipment()->where('status', 'available')->count(),
            'in_use' => $line->equipment()->where('status', 'in_use')->count(),
            'maintenance' => $line->equipment()->where('status', 'maintenance')->count(),
        ];

        $surgeryStats = [
            'total' => $line->surgeries()->count(),
            'pending' => $line->surgeries()->where('status', 'pending')->count(),
            'completed' => $line->surgeries()->where('status', 'completed')->count(),
            'cancelled' => $line->surgeries()->where('status', 'cancelled')->count(),
        ];

        $staffStats = [
            'total' => $line->staff()->count(),
            'jefes' => $line->staff()->whereHas('roles', function($q) {
                $q->where('slug', 'jefe_linea');
            })->count(),
            'instrumentistas' => $line->staff()->whereHas('roles', function($q) {
                $q->where('slug', 'instrumentista');
            })->count(),
            'vendedores' => $line->staff()->whereHas('roles', function($q) {
                $q->where('slug', 'vendedor');
            })->count(),
        ];

        return view('lines.show', compact(
            'line',
            'equipmentStats',
            'surgeryStats',
            'staffStats'
        ));
    }

    public function edit(Line $line)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerente()) {
            abort(403, 'No autorizado');
        }

        return view('lines.edit', compact('line'));
    }

    public function update(UpdateLineRequest $request, Line $line)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isGerente()) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validated();

        $line->update($validated);

        return redirect()
            ->route('lines.show', $line)
            ->with('success', 'Línea actualizada exitosamente.');
    }

    public function destroy(Line $line)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'No autorizado');
        }

        $line->delete();

        return redirect()
            ->route('lines.index')
            ->with('success', 'Línea eliminada exitosamente.');
    }

    public function dashboard(Line $line)
    {
        if (!Auth::user()->canManageLine($line)) {
            abort(403, 'No autorizado');
        }

        $upcomingSurgeries = $line->surgeries()
            ->with(['equipment', 'staff'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $equipmentMaintenance = $line->equipment()
            ->where('status', 'maintenance')
            ->latest()
            ->take(5)
            ->get();

        return view('lines.dashboard', compact(
            'line',
            'upcomingSurgeries',
            'equipmentMaintenance'
        ));
    }

    public function schedule(Line $line)
    {
        if (!Auth::user()->canManageLine($line)) {
            abort(403, 'No autorizado');
        }

        $surgeries = $line->surgeries()
            ->with(['equipment', 'staff'])
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->paginate(10);

        return view('lines.schedule', compact('line', 'surgeries'));
    }
}
