<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    protected $model;
    protected $routePrefix;
    protected $viewPrefix;
    protected $rules = [];
    protected $messages = [];

    /**
     * Mostrar una lista del recurso.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->model::query();
            return DataTables::of($query)
                ->addColumn('action', function ($item) {
                    return view('admin.partials.actions', [
                        'item' => $item,
                        'route_prefix' => $this->routePrefix
                    ])->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view($this->viewPrefix . '.index');
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     */
    public function create()
    {
        return view($this->viewPrefix . '.create');
    }

    /**
     * Almacenar un recurso recién creado.
     */
    public function store(Request $request)
    {
        $request->validate($this->rules, $this->messages);

        $item = $this->model::create($request->all());

        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    /**
     * Mostrar el recurso especificado.
     */
    public function show(Model $item)
    {
        return view($this->viewPrefix . '.show', compact('item'));
    }

    /**
     * Mostrar el formulario para editar el recurso especificado.
     */
    public function edit(Model $item)
    {
        return view($this->viewPrefix . '.edit', compact('item'));
    }

    /**
     * Actualizar el recurso especificado.
     */
    public function update(Request $request, Model $item)
    {
        $request->validate($this->rules, $this->messages);

        $item->update($request->all());

        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    /**
     * Eliminar el recurso especificado.
     */
    public function destroy(Model $item)
    {
        try {
            $item->delete();
            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar un registro eliminado.
     */
    public function restore($id)
    {
        try {
            $this->model::withTrashed()->findOrFail($id)->restore();
            return response()->json([
                'success' => true,
                'message' => 'Registro restaurado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos para DataTables con formato.
     */
    protected function getFormattedDataTablesData($query)
    {
        return DataTables::of($query)
            ->addColumn('action', function ($item) {
                return view('admin.partials.actions', [
                    'item' => $item,
                    'route_prefix' => $this->routePrefix
                ])->render();
            })
            ->addColumn('status_badge', function ($item) {
                return view('admin.partials.status-badge', [
                    'status' => $item->status
                ])->render();
            })
            ->rawColumns(['action', 'status_badge'])
            ->make(true);
    }
}
