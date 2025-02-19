<?php

namespace App\Http\Controllers\Admin;

use App\Models\Surgery;
use App\Models\Line;
use App\Models\User;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreSurgeryRequest;
use App\Http\Requests\UpdateSurgeryRequest;

class SurgeryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $surgeries = Surgery::with(['line', 'equipment', 'staff'])
            ->when(!$user->isAdmin() && !$user->isGerente(), function($query) use ($user) {
                $query->whereHas('line.staff', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })
            ->latest()
            ->paginate(10);

        return view('surgeries.index', compact('surgeries'));
    }

    public function create()
    {
        $user = Auth::user();
        $lines = Line::when(!$user->isAdmin() && !$user->isGerente(), function($query) use ($user) {
            $query->whereHas('staff', function($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })->get();

        $instrumentistas = User::whereHas('roles', function($query) {
            $query->where('slug', 'instrumentista');
        })->get();

        $equipment = Equipment::where('status', 'available')->get();

        return view('surgeries.create', compact('lines', 'instrumentistas', 'equipment'));
    }

    public function store(StoreSurgeryRequest $request)
    {
        $validated = $request->validated();

        $surgery = Surgery::create([
            'line_id' => $validated['line_id'],
            'description' => $validated['description'],
            'notes' => $validated['notes'],
            'status' => Surgery::STATUS_PENDING,
        ]);

        $surgery->equipment()->attach($validated['equipment_ids']);
        $surgery->staff()->attach($validated['staff_ids']);
        Equipment::whereIn('id', $validated['equipment_ids'])->update(['status' => 'in_use']);

        return redirect()->route('surgeries.show', $surgery)->with('success', 'Cirugía programada exitosamente.');
    }

    public function show(Surgery $surgery)
    {
        $surgery->load(['line', 'equipment', 'staff', 'institucion', 'medico']);

        return view('surgeries.show', compact('surgery'));
    }

    public function edit(Surgery $surgery)
    {
        $user = Auth::user();
        $lines = Line::when(!$user->isAdmin() && !$user->isGerente(), function($query) use ($user) {
            $query->whereHas('staff', function($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        })->get();

        $instrumentistas = User::whereHas('roles', function($query) {
            $query->where('slug', 'instrumentista');
        })->get();

        $equipment = Equipment::where('status', 'available')
            ->orWhereIn('id', $surgery->equipment->pluck('id'))
            ->get();

        return view('surgeries.edit', compact('surgery', 'lines', 'instrumentistas', 'equipment'));
    }

    public function update(UpdateSurgeryRequest $request, Surgery $surgery)
    {
        $validated = $request->validated();
        Equipment::whereIn('id', $surgery->equipment->pluck('id'))->update(['status' => 'available']);
        $surgery->update([
            'line_id' => $validated['line_id'],
            'description' => $validated['description'],
            'notes' => $validated['notes'],
        ]);
        $surgery->equipment()->sync($validated['equipment_ids']);
        $surgery->staff()->sync($validated['staff_ids']);
        Equipment::whereIn('id', $validated['equipment_ids'])->update(['status' => 'in_use']);

        return redirect()->route('surgeries.show', $surgery)->with('success', 'Cirugía actualizada exitosamente.');
    }

    public function destroy(Surgery $surgery)
    {
        Equipment::whereIn('id', $surgery->equipment->pluck('id'))->update(['status' => 'available']);
        $surgery->delete();

        return redirect()->route('surgeries.index')->with('success', 'Cirugía eliminada exitosamente.');
    }

    public function status()
    {
        $user = Auth::user();
        $surgeries = Surgery::with(['line', 'equipment', 'staff'])
            ->when(!$user->isAdmin() && !$user->isGerente(), function($query) use ($user) {
                $query->whereHas('line.staff', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => Surgery::count(),
            'pending' => Surgery::where('status', Surgery::STATUS_PENDING)->count(),
            'in_progress' => Surgery::where('status', Surgery::STATUS_IN_PROGRESS)->count(),
            'completed' => Surgery::where('status', Surgery::STATUS_COMPLETED)->count(),
            'cancelled' => Surgery::where('status', Surgery::STATUS_CANCELLED)->count(),
        ];

        return view('surgeries.status', compact('surgeries', 'stats'));
    }

    public function updateStatus(Request $request, Surgery $surgery)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [
                Surgery::STATUS_PENDING,
                Surgery::STATUS_IN_PROGRESS,
                Surgery::STATUS_COMPLETED,
                Surgery::STATUS_CANCELLED,
                Surgery::STATUS_RESCHEDULED
            ]),
        ]);

        try {
            $surgery->updateStatus($validated['status']);
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'El estado de la cirugía ha sido actualizado exitosamente'
            ]);
        }

        return back()->with('success', 'El estado de la cirugía ha sido actualizado exitosamente');
    }

    public function kanban()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Debe iniciar sesión para acceder al tablero Kanban.');
            }

            // Verificar si el usuario tiene permiso para ver el tablero Kanban
            if (!$user->hasAnyRole(['admin', 'gerente', 'jefe_linea', 'instrumentista'])) {
                return redirect()->route('dashboard')
                    ->with('error', 'No tiene permisos para acceder al tablero Kanban.');
            }

            // Obtener todas las cirugías con sus relaciones
            $query = Surgery::with(['line', 'equipment', 'staff', 'institucion', 'medico']);

            // Filtrar cirugías según el rol del usuario
            if (!$user->hasAnyRole(['admin', 'gerente'])) {
                $query->whereHas('line.staff', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }

            // Obtener y ordenar las cirugías
            $surgeries = $query->orderBy('surgery_date', 'desc')->get();

            // Agrupar cirugías por estado
            $columns = [
                Surgery::STATUS_PENDING => $surgeries->where('status', Surgery::STATUS_PENDING),
                Surgery::STATUS_IN_PROGRESS => $surgeries->where('status', Surgery::STATUS_IN_PROGRESS),
                Surgery::STATUS_COMPLETED => $surgeries->where('status', Surgery::STATUS_COMPLETED)->take(10),
                Surgery::STATUS_CANCELLED => $surgeries->where('status', Surgery::STATUS_CANCELLED)->take(10),
            ];

            $columnTitles = [
                Surgery::STATUS_PENDING => 'Pendientes',
                Surgery::STATUS_IN_PROGRESS => 'En Progreso',
                Surgery::STATUS_COMPLETED => 'Completadas',
                Surgery::STATUS_CANCELLED => 'Canceladas',
            ];

            $columnColors = [
                Surgery::STATUS_PENDING => 'warning',
                Surgery::STATUS_IN_PROGRESS => 'info',
                Surgery::STATUS_COMPLETED => 'success',
                Surgery::STATUS_CANCELLED => 'danger',
            ];

            return view('surgeries.kanban', compact('columns', 'columnTitles', 'columnColors'));

        } catch (\Exception $e) {
            \Log::error('Error en el tablero Kanban: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Ha ocurrido un error al cargar el tablero Kanban. Por favor, inténtelo de nuevo.');
        }
    }
}
