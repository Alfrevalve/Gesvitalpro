<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use App\Services\EquipmentMaintenanceService;

class EquipmentController extends Controller
{
    protected $maintenanceService;

    public function __construct(EquipmentMaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * Listar todos los equipos
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Equipment::query();

        // Filtrar por estado
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrar por línea
        if ($request->has('line_id')) {
            $query->where('line_id', $request->line_id);
        }

        // Filtrar por búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Eager loading de relaciones necesarias
        $query->with(['line:id,name', 'surgeries' => function ($query) {
            $query->select('id', 'equipment_id', 'date', 'status')
                ->where('date', '>=', now())
                ->orderBy('date');
        }]);

        // Paginación
        $perPage = $request->input('per_page', 15);
        $equipment = $query->paginate($perPage);

        return response()->json([
            'data' => $equipment->items(),
            'meta' => [
                'current_page' => $equipment->currentPage(),
                'last_page' => $equipment->lastPage(),
                'per_page' => $equipment->perPage(),
                'total' => $equipment->total()
            ]
        ]);
    }

    /**
     * Obtener un equipo específico
     *
     * @param Equipment $equipment
     * @return JsonResponse
     */
    public function show(Equipment $equipment): JsonResponse
    {
        // Verificar permisos
        $this->authorize('view', $equipment);

        // Cargar relaciones necesarias
        $equipment->load([
            'line:id,name',
            'surgeries' => function ($query) {
                $query->select('id', 'equipment_id', 'date', 'status', 'description')
                    ->where('date', '>=', now())
                    ->orderBy('date')
                    ->take(5);
            }
        ]);

        // Obtener información de mantenimiento
        $maintenanceInfo = $this->maintenanceService->getMaintenanceInfo($equipment);

        return response()->json([
            'data' => array_merge($equipment->toArray(), [
                'maintenance_info' => $maintenanceInfo
            ])
        ]);
    }

    /**
     * Obtener información de mantenimiento de equipos
     *
     * @return JsonResponse
     */
    public function maintenance(): JsonResponse
    {
        // Cachear los resultados por 1 hora
        $maintenanceData = Cache::remember('equipment_maintenance', now()->addHour(), function () {
            return Equipment::where('status', 'maintenance')
                ->orWhere('next_maintenance_date', '<=', now()->addDays(30))
                ->with(['line:id,name'])
                ->get()
                ->map(function ($equipment) {
                    $maintenanceInfo = $this->maintenanceService->getMaintenanceInfo($equipment);
                    return [
                        'id' => $equipment->id,
                        'name' => $equipment->name,
                        'line' => $equipment->line->name,
                        'status' => $equipment->status,
                        'last_maintenance_date' => $equipment->last_maintenance_date,
                        'next_maintenance_date' => $equipment->next_maintenance_date,
                        'days_until_maintenance' => $maintenanceInfo['days_until_maintenance'],
                        'maintenance_status' => $maintenanceInfo['status'],
                        'maintenance_priority' => $maintenanceInfo['priority']
                    ];
                });
        });

        return response()->json([
            'data' => $maintenanceData,
            'meta' => [
                'total_in_maintenance' => $maintenanceData->where('status', 'maintenance')->count(),
                'total_due_soon' => $maintenanceData->where('status', '!=', 'maintenance')
                    ->where('days_until_maintenance', '<=', 30)
                    ->count()
            ]
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
            'error' => 'Equipo no encontrado',
            'message' => "No se encontró el equipo con ID {$id}",
            'code' => 404
        ], 404);
    }
}
