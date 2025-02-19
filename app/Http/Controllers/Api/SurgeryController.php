<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Surgery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use App\Services\SurgeryScheduler;
use App\Notifications\SurgeryStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurgeryController extends Controller
{
    protected $scheduler;

    public function __construct(SurgeryScheduler $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    /**
     * Listar todas las cirugías
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Surgery::query();

        // Filtrar por estado
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrar por línea
        if ($request->has('line_id')) {
            $query->where('line_id', $request->line_id);
        }

        // Filtrar por fecha
        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Filtrar por búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function (Builder $query) use ($search) {
                $query->where('description', 'like', "%{$search}%")
                    ->orWhereHas('medico', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('institucion', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        // Eager loading de relaciones necesarias
        $query->with([
            'line:id,name',
            'equipment:id,name,status',
            'medico:id,name,especialidad',
            'institucion:id,nombre,direccion',
            'staff:id,name,role_id'
        ]);

        // Ordenar por fecha
        $query->orderBy('date', $request->input('order', 'desc'));

        // Paginación
        $perPage = $request->input('per_page', 15);
        $surgeries = $query->paginate($perPage);

        // Transformar los datos para la respuesta
        $data = $surgeries->through(function ($surgery) {
            return [
                'id' => $surgery->id,
                'description' => $surgery->description,
                'date' => $surgery->date,
                'status' => $surgery->status,
                'line' => [
                    'id' => $surgery->line->id,
                    'name' => $surgery->line->name
                ],
                'equipment' => [
                    'id' => $surgery->equipment->id,
                    'name' => $surgery->equipment->name,
                    'status' => $surgery->equipment->status
                ],
                'medico' => [
                    'id' => $surgery->medico->id,
                    'name' => $surgery->medico->name,
                    'especialidad' => $surgery->medico->especialidad
                ],
                'institucion' => [
                    'id' => $surgery->institucion->id,
                    'nombre' => $surgery->institucion->nombre,
                    'direccion' => $surgery->institucion->direccion
                ],
                'staff' => $surgery->staff->map(function ($staff) {
                    return [
                        'id' => $staff->id,
                        'name' => $staff->name,
                        'role' => $staff->role->name
                    ];
                })
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $surgeries->currentPage(),
                'last_page' => $surgeries->lastPage(),
                'per_page' => $surgeries->perPage(),
                'total' => $surgeries->total()
            ]
        ]);
    }

    /**
     * Obtener una cirugía específica
     *
     * @param Surgery $surgery
     * @return JsonResponse
     */
    public function show(Surgery $surgery): JsonResponse
    {
        // Verificar permisos
        $this->authorize('view', $surgery);

        // Cargar relaciones necesarias
        $surgery->load([
            'line:id,name',
            'equipment:id,name,status',
            'medico:id,name,especialidad',
            'institucion:id,nombre,direccion',
            'staff:id,name,role_id',
            'materials.preparation',
            'materials.delivery'
        ]);

        return response()->json([
            'data' => [
                'id' => $surgery->id,
                'description' => $surgery->description,
                'date' => $surgery->date,
                'status' => $surgery->status,
                'line' => $surgery->line,
                'equipment' => $surgery->equipment,
                'medico' => $surgery->medico,
                'institucion' => $surgery->institucion,
                'staff' => $surgery->staff,
                'materials' => $surgery->materials->map(function ($material) {
                    return [
                        'id' => $material->id,
                        'name' => $material->name,
                        'quantity' => $material->quantity,
                        'preparation_status' => $material->preparation?->status,
                        'delivery_status' => $material->delivery?->status
                    ];
                }),
                'timeline' => $this->getTimeline($surgery)
            ]
        ]);
    }

    /**
     * Actualizar el estado de una cirugía
     *
     * @param Request $request
     * @param Surgery $surgery
     * @return JsonResponse
     * @throws ValidationException
     */
    public function updateStatus(Request $request, Surgery $surgery): JsonResponse
    {
        // Verificar permisos
        $this->authorize('update', $surgery);

        // Validar el nuevo estado
        $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Actualizar estado
            $oldStatus = $surgery->status;
            $surgery->status = $request->status;
            $surgery->status_notes = $request->notes;
            $surgery->save();

            // Registrar el cambio en la línea de tiempo
            $surgery->timeline()->create([
                'action' => 'status_changed',
                'description' => "Estado cambiado de {$oldStatus} a {$request->status}",
                'user_id' => auth()->id(),
                'metadata' => [
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'notes' => $request->notes
                ]
            ]);

            // Notificar a los involucrados
            $this->notifyStatusChange($surgery, $oldStatus);

            DB::commit();

            // Limpiar caché relacionado
            Cache::tags(['surgeries', "surgery_{$surgery->id}"])->flush();

            return response()->json([
                'message' => 'Estado actualizado correctamente',
                'data' => [
                    'id' => $surgery->id,
                    'status' => $surgery->status,
                    'updated_at' => $surgery->updated_at
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener la línea de tiempo de una cirugía
     *
     * @param Surgery $surgery
     * @return array
     */
    protected function getTimeline(Surgery $surgery): array
    {
        return $surgery->timeline()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($event) {
                return [
                    'action' => $event->action,
                    'description' => $event->description,
                    'user' => $event->user->name,
                    'timestamp' => $event->created_at,
                    'metadata' => $event->metadata
                ];
            })
            ->toArray();
    }

    /**
     * Notificar cambio de estado a los involucrados
     *
     * @param Surgery $surgery
     * @param string $oldStatus
     * @return void
     */
    protected function notifyStatusChange(Surgery $surgery, string $oldStatus): void
    {
        $notification = new SurgeryStatusChanged($surgery, $oldStatus);

        // Notificar al médico si tiene usuario en el sistema
        if ($surgery->medico->user) {
            $surgery->medico->user->notify($notification);
        }

        // Notificar al personal asignado
        $surgery->staff->each(function ($staff) use ($notification) {
            $staff->notify($notification);
        });

        // Notificar a los supervisores de la línea
        $surgery->line->staff()
            ->whereHas('role', function ($query) {
                $query->where('slug', 'jefe_linea');
            })
            ->get()
            ->each(function ($supervisor) use ($notification) {
                $supervisor->notify($notification);
            });
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
            'error' => 'Cirugía no encontrada',
            'message' => "No se encontró la cirugía con ID {$id}",
            'code' => 404
        ], 404);
    }
}
