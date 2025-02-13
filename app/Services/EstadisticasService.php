<?php

namespace App\Services;

use App\Models\Cirugia;
use App\Models\Paciente;
use App\Models\Inventario;
use App\Models\MovimientosInventario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstadisticasService
{
    /**
     * Obtener estadísticas generales del sistema
     */
    public function getEstadisticasGenerales(): array
    {
        $ahora = Carbon::now();
        $inicioMes = $ahora->startOfMonth();
        $finMes = $ahora->copy()->endOfMonth();

        return [
            'cirugias' => [
                'total' => Cirugia::count(),
                'mes_actual' => Cirugia::whereBetween('fecha_hora', [$inicioMes, $finMes])->count(),
                'programadas' => Cirugia::where('estado', 'programada')->count(),
                'completadas' => Cirugia::where('estado', 'completada')->count(),
                'canceladas' => Cirugia::where('estado', 'cancelada')->count(),
            ],
            'pacientes' => [
                'total' => Paciente::count(),
                'nuevos_mes' => Paciente::whereBetween('created_at', [$inicioMes, $finMes])->count(),
                'con_cirugias' => Paciente::has('cirugias')->count(),
            ],
            'inventario' => [
                'total_items' => Inventario::count(),
                'stock_bajo' => Inventario::whereRaw('quantity <= nivel_minimo')->count(),
                'valor_total' => Inventario::sum(DB::raw('quantity * precio_unitario')),
            ]
        ];
    }

    /**
     * Obtener estadísticas de cirugías por período
     */
    public function getEstadisticasCirugias(string $periodo = 'mes'): array
    {
        $query = Cirugia::query();
        
        switch ($periodo) {
            case 'semana':
                $inicio = Carbon::now()->startOfWeek();
                $fin = Carbon::now()->endOfWeek();
                break;
            case 'mes':
                $inicio = Carbon::now()->startOfMonth();
                $fin = Carbon::now()->endOfMonth();
                break;
            case 'año':
                $inicio = Carbon::now()->startOfYear();
                $fin = Carbon::now()->endOfYear();
                break;
            default:
                $inicio = Carbon::now()->startOfMonth();
                $fin = Carbon::now()->endOfMonth();
        }

        return [
            'total' => $query->whereBetween('fecha_hora', [$inicio, $fin])->count(),
            'por_estado' => $query->whereBetween('fecha_hora', [$inicio, $fin])
                ->groupBy('estado')
                ->select('estado', DB::raw('count(*) as total'))
                ->pluck('total', 'estado'),
            'por_prioridad' => $query->whereBetween('fecha_hora', [$inicio, $fin])
                ->groupBy('prioridad')
                ->select('prioridad', DB::raw('count(*) as total'))
                ->pluck('total', 'prioridad'),
            'tiempo_promedio' => $query->whereBetween('fecha_hora', [$inicio, $fin])
                ->avg('duracion_estimada'),
            'costo_total' => $query->whereBetween('fecha_hora', [$inicio, $fin])
                ->sum('costo_estimado'),
        ];
    }

    /**
     * Obtener análisis de inventario
     */
    public function getAnalisisInventario(): array
    {
        return [
            'productos_criticos' => Inventario::whereRaw('quantity <= nivel_minimo')
                ->with('movimientos')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nombre' => $item->nombre,
                        'stock_actual' => $item->quantity,
                        'nivel_minimo' => $item->nivel_minimo,
                        'consumo_mensual' => $item->movimientos()
                            ->where('tipo_movimiento', 'salida')
                            ->where('created_at', '>=', Carbon::now()->subMonth())
                            ->sum('cantidad'),
                    ];
                }),
            'valor_inventario' => [
                'total' => Inventario::sum(DB::raw('quantity * precio_unitario')),
                'por_categoria' => Inventario::groupBy('categoria')
                    ->select('categoria', DB::raw('SUM(quantity * precio_unitario) as valor'))
                    ->pluck('valor', 'categoria'),
            ],
            'rotacion' => $this->calcularRotacionInventario(),
        ];
    }

    /**
     * Calcular la rotación del inventario
     */
    protected function calcularRotacionInventario(): array
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        return Inventario::with(['movimientos' => function ($query) use ($inicioMes, $finMes) {
            $query->whereBetween('created_at', [$inicioMes, $finMes]);
        }])
        ->get()
        ->map(function ($item) {
            $salidas = $item->movimientos
                ->where('tipo_movimiento', 'salida')
                ->sum('cantidad');
            
            $stockPromedio = ($item->quantity + ($item->quantity + $salidas)) / 2;
            
            return [
                'id' => $item->id,
                'nombre' => $item->nombre,
                'rotacion' => $stockPromedio > 0 ? $salidas / $stockPromedio : 0,
                'dias_inventario' => $stockPromedio > 0 ? ($stockPromedio / ($salidas / 30)) : 0,
            ];
        })
        ->toArray();
    }

    /**
     * Obtener predicciones de demanda
     */
    public function getPredicionesDemanda(): array
    {
        $inventarios = Inventario::with(['movimientos' => function ($query) {
            $query->where('tipo_movimiento', 'salida')
                ->where('created_at', '>=', Carbon::now()->subMonths(3));
        }])->get();

        return $inventarios->map(function ($item) {
            $consumoMensual = $item->movimientos->groupBy(function ($movimiento) {
                return $movimiento->created_at->format('Y-m');
            })->map(function ($grupo) {
                return $grupo->sum('cantidad');
            });

            $promedioConsumo = $consumoMensual->avg();
            $desviacionEstandar = $this->calcularDesviacionEstandar($consumoMensual->values()->toArray());

            return [
                'id' => $item->id,
                'nombre' => $item->nombre,
                'consumo_promedio' => $promedioConsumo,
                'desviacion_estandar' => $desviacionEstandar,
                'stock_seguridad' => ceil($promedioConsumo + (2 * $desviacionEstandar)),
                'punto_reorden' => ceil($promedioConsumo * 1.5),
                'prediccion_siguiente_mes' => ceil($promedioConsumo * 1.1),
            ];
        })->toArray();
    }

    /**
     * Calcular la desviación estándar
     */
    protected function calcularDesviacionEstandar(array $valores): float
    {
        $n = count($valores);
        if ($n === 0) return 0;

        $media = array_sum($valores) / $n;
        $sumaCuadrados = array_sum(array_map(function($x) use ($media) {
            return pow($x - $media, 2);
        }, $valores));

        return sqrt($sumaCuadrados / $n);
    }
}
