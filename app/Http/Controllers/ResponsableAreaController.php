<?php

namespace App\Http\Controllers;

use App\Models\ResponsableArea;
use App\Models\Responsable;
use App\Models\Area;
use App\Http\Requests\ResponsableAreaRequest;
use Illuminate\Http\Request;

class ResponsableAreaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permiso:Asignaciones');
    }
    /**
     * Listar asignaciones con búsqueda, ordenamiento y filtros
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $areaFiltro = $request->get('area_filtro', '');
        $responsableFiltro = $request->get('responsable_filtro', '');
        $perPage = 10;

        // Total de registros sin filtros
        $total = ResponsableArea::count();

        $query = ResponsableArea::with(['responsable', 'area']);

        // 🔍 BÚSQUEDA
        if (!empty($search)) {
            $query->buscar($search);
        }

        // 🎯 FILTRO POR ÁREA
        if (!empty($areaFiltro)) {
            $query->porArea($areaFiltro);
        }

        // 🎯 FILTRO POR RESPONSABLE
        if (!empty($responsableFiltro)) {
            $query->porResponsable($responsableFiltro);
        }

        // 📊 ORDENAMIENTO DINÁMICO
        $columna = $request->get('orden', 'fecha_asignacion');
        $direccion = $request->get('direccion', 'desc');

        // Mapeo de columnas del frontend al backend
        $columnasPermitidas = [
            'id' => 'id_responsable_area',
            'dni' => 'dni_responsable',
            'responsable' => 'dni_responsable',
            'area' => 'idarea',
            'fecha' => 'fecha_asignacion'
        ];

        // Validar que la columna existe
        if (array_key_exists($columna, $columnasPermitidas)) {
            $columnaReal = $columnasPermitidas[$columna];
        } else {
            $columnaReal = 'fecha_asignacion';
        }

        // Validar dirección
        $direccion = in_array(strtolower($direccion), ['asc', 'desc'])
            ? strtolower($direccion)
            : 'desc';

        // Aplicar ordenamiento
        $query->orderBy($columnaReal, $direccion);

        // 📄 PAGINACIÓN
        $asignaciones = $query->paginate($perPage);
        $resultados = $query->count();

        // Obtener datos para los filtros
        $areas = Area::orderBy('nombre_area')->get();
        $responsables = Responsable::orderBy('apellidos_responsable')->get();

        // 📤 RESPUESTA AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $asignaciones->items(),
                'total' => $total,
                'resultados' => $asignaciones->total(),
                'current_page' => $asignaciones->currentPage(),
                'last_page' => $asignaciones->lastPage(),
                'per_page' => $asignaciones->perPage(),
                'from' => $asignaciones->firstItem(),
                'to' => $asignaciones->lastItem()
            ]);
        }

        return view('responsable-area.index', compact(
            'asignaciones',
            'total',
            'areas',
            'responsables'
        ));
    }

    /**
     * Guardar nueva asignación
     */
    public function store(ResponsableAreaRequest $request)
    {
        try {
            $asignacion = ResponsableArea::create([
                'dni_responsable' => $request->dni_responsable,
                'idarea' => $request->idarea,
                'fecha_asignacion' => now()
            ]);

            $asignacion->load(['responsable', 'area']);

            return response()->json([
                'success' => true,
                'message' => 'Responsable asignado al área exitosamente',
                'data' => $asignacion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear asignación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos para editar
     */
    public function edit(ResponsableArea $responsableArea)
    {
        $responsableArea->load(['responsable', 'area']);
        return response()->json($responsableArea);
    }

    /**
     * Actualizar asignación existente
     */
    public function update(Request $request, ResponsableArea $responsableArea)
    {
        try {
            // Validar manualmente ya que no usamos ResponsableAreaRequest
            $request->validate([
                'idarea' => 'required|integer|exists:area,id_area'
            ]);

            // Verificar duplicado si cambia el área
            if ($responsableArea->idarea != $request->idarea) {
                if (ResponsableArea::existeAsignacion(
                    $responsableArea->dni_responsable,
                    $request->idarea
                )) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Este responsable ya está asignado a esta área'
                    ], 422);
                }
            }

            $responsableArea->update([
                'idarea' => $request->idarea
            ]);

            $responsableArea->load(['responsable', 'area']);

            return response()->json([
                'success' => true,
                'message' => 'Asignación actualizada exitosamente',
                'data' => $responsableArea
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar asignación
     */
    public function destroy(ResponsableArea $responsableArea)
    {
        try {
            $responsableArea->delete();

            return response()->json([
                'success' => true,
                'message' => 'Asignación eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener áreas de un responsable (para AJAX)
     */
    public function areasDeResponsable(Request $request)
    {
        $dni = $request->get('dni');

        $areas = ResponsableArea::areasDeResponsable($dni);

        return response()->json([
            'success' => true,
            'data' => $areas
        ]);
    }

    /**
     * Obtener responsables de un área (para AJAX)
     */
    public function responsablesDeArea(Request $request)
    {
        $areaId = $request->get('area_id');

        $responsables = ResponsableArea::responsablesDeArea($areaId);

        return response()->json([
            'success' => true,
            'data' => $responsables
        ]);
    }
}
