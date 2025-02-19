<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Line;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

class LineController extends Controller
{
    /**
     * Listar todas las líneas
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Line::query();

        // Filtrar por estado si se proporciona
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrar por búsqueda si se proporciona
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Eager loading de relaciones necesarias
        $query->with(['equipment' => function ($query) {
            $query->select('id', 'line_id', 'name', 'status');
        }]);

        // Paginación
        $perPage = $request->input('per_page', 15);
        $lines = $query->paginate($perPage);

        return response()->json([
            'data' => $lines->items(),
            'meta' => [
                'current_page' => $lines->currentPage(),
                'last_page' => $lines->lastPage(),
                'per_page' => $lines->perPage(),
                'total' => $lines->total()
            ]
        ]);
    }

    /**
     * Obtener una línea específica
     *
     * @param Line $line
     * @return JsonResponse
     */
    public function show(Line $line): JsonResponse
    {
        // Verificar permisos
        $this->authorize('view', $line);

        // Cargar relaciones necesarias
        $line->load([
            'equipment' => function ($query) {
                $query->select('id', 'line_id', 'name', 'status');
            },
            'staff' => function ($query) {
                $query->select('users.id', 'name', 'email');
            }
        ]);

        return response()->json([
            'data' => $line
        ]);
    }

    /**
     * Obtener el horario de una línea específica
     *
     * @param Line $line
     * @param Request $request
     * @return JsonResponse
     */
    public function schedule(Line $line, Request $request): JsonResponse
    {
        // Verificar permisos
        $this->authorize('view', $line);

        // Validar parámetros de fecha
        $request->validate([
            'start_date' => 'date|required',
            'end_date' => 'date|required|after_or_equal:start_date'
        ]);

        // Cachear los resultados por 5 minutos
        $cacheKey = "line_schedule_{$line->id}_{$request->start_date}_{$request->end_date}";

        $schedule = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($line, $request) {
            return $line->surgeries()
                ->whereBetween('date', [$request->start_date, $request->end_date])
                ->with(['equipment:id,name', 'medico:id,name'])
                ->get()
                ->map(function ($surgery) {
                    return [
                        'id' => $surgery->id,
                        'title' => $surgery->description,
                        'start' => $surgery->date,
                        'equipment' => $surgery->equipment->name,
                        'medico' => $surgery->medico->name,
                        'status' => $surgery->status
                    ];
                });
        });

        return response()->json([
            'data' => $schedule
        ]);
    }

    /**
     * Manejar errores de modelo no encontrado
     *
     * @param int $id
     * @return JsonResponse
     */
    protected function modelNotFound($id): JsonResponse
    {
        return response()->json([
            'error' => 'Línea no encontrada',
            'message' => "No se encontró la línea con ID {$id}",
            'code' => 404
        ], 404);
    }
}
