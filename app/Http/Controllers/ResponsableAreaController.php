<?php

namespace App\Http\Controllers;

use App\Models\ResponsableArea;
use App\Models\Responsable;
use App\Models\Area;
use App\Http\Requests\ResponsableAreaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ResponsableAreaController extends Controller
{
    /**
     * Listar asignaciones con búsqueda, ordenamiento y filtros
     */
    public function index(Request $request)
    {
        $search            = $request->get('search', '');
        $areaFiltro        = $request->get('area_filtro', '');
        $responsableFiltro = $request->get('responsable_filtro', '');
        $anioFiltro        = $request->get('anio_filtro', '');
        $perPage = 10;

        // ───────────────────────────────────────────────────────────
        // 📌 Solo mostrar el ÚLTIMO registro por DNI (1 fila por persona)
        // Usamos la subconsulta: MAX(id_responsable_area) agrupado por dni
        // ───────────────────────────────────────────────────────────
        $ultimosIdsSubquery = DB::table('responsable_area')
            ->selectRaw('MAX(id_responsable_area)')
            ->groupBy('dni_responsable');

        $query = ResponsableArea::with(['responsable', 'area'])
            ->whereIn('id_responsable_area', $ultimosIdsSubquery);

        // 🔍 BÚSQUEDA
        if (!empty($search)) {
            $query->buscar($search);
        }

        // 🎯 FILTRO POR ÁREA (última asignación que esté en esa área)
        if (!empty($areaFiltro)) {
            $query->porArea($areaFiltro);
        }

        // 📅 FILTRO POR AÑO (última asignación en ese año)
        if (!empty($anioFiltro)) {
            $query->where('periodo_anio', $anioFiltro);
        }

        // 🎯 FILTRO POR RESPONSABLE
        if (!empty($responsableFiltro)) {
            $query->porResponsable($responsableFiltro);
        }

        // 📊 ORDENAMIENTO DINÁMICO
        $columna   = $request->get('orden', 'fecha_asignacion');
        $direccion = $request->get('direccion', 'desc');

        $columnasPermitidas = [
            'id'          => 'id_responsable_area',
            'dni'         => 'dni_responsable',
            'responsable' => 'dni_responsable',
            'area'        => 'idarea',
            'anio'        => 'periodo_anio',
            'fecha'       => 'fecha_asignacion'
        ];

        $columnaReal = $columnasPermitidas[$columna] ?? 'fecha_asignacion';
        $direccion   = in_array(strtolower($direccion), ['asc', 'desc']) ? strtolower($direccion) : 'desc';

        $query->orderBy($columnaReal, $direccion);

        // Total de responsables únicos (sin filtros)
        $total = DB::table('responsable_area')->distinct()->count('dni_responsable');

        // 📄 PAGINACIÓN
        $asignaciones = $query->paginate($perPage);

        // Datos para filtros y comboboxes
        $areas        = Area::orderBy('nombre_area')->get();
        $responsables = Responsable::orderBy('apellidos_responsable')->get();

        $anioActual       = (int) date('Y');
        $aniosDisponibles = array_unique(array_merge(
            range($anioActual - 2, $anioActual + 2),
            ResponsableArea::selectRaw('DISTINCT periodo_anio')
                ->orderBy('periodo_anio', 'desc')
                ->pluck('periodo_anio')
                ->toArray()
        ));
        rsort($aniosDisponibles);

        // 📤 RESPUESTA AJAX
        if ($request->ajax()) {
            return response()->json([
                'success'      => true,
                'data'         => $asignaciones->items(),
                'total'        => $total,
                'resultados'   => $asignaciones->total(),
                'current_page' => $asignaciones->currentPage(),
                'last_page'    => $asignaciones->lastPage(),
                'per_page'     => $asignaciones->perPage(),
                'from'         => $asignaciones->firstItem(),
                'to'           => $asignaciones->lastItem()
            ]);
        }

        return view('responsable-area.index', compact(
            'asignaciones',
            'total',
            'areas',
            'responsables',
            'aniosDisponibles',
            'anioActual'
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
                'idarea'          => $request->idarea,
                'periodo_anio'    => $request->periodo_anio ?? date('Y'),
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
            $request->validate([
                'idarea'       => 'required|integer|exists:area,id_area',
                'periodo_anio' => 'required|integer|min:2020|max:2099'
            ]);

            $responsableArea->update([
                'idarea'       => $request->idarea,
                'periodo_anio' => $request->periodo_anio
            ]);

            $responsableArea->load(['responsable', 'area']);

            return response()->json([
                'success' => true,
                'message' => 'Asignación actualizada exitosamente',
                'data'    => $responsableArea
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
     * Historial/Trazabilidad de un responsable por DNI
     */
    public function historial($dni)
    {
        $responsable = \App\Models\Responsable::where('dni_responsable', $dni)->first();

        if (!$responsable) {
            return response()->json(['success' => false, 'message' => 'Responsable no encontrado'], 404);
        }

        $asignaciones = ResponsableArea::with('area')
            ->where('dni_responsable', $dni)
            ->orderBy('periodo_anio', 'desc')
            ->orderBy('fecha_asignacion', 'desc')
            ->get()
            ->map(function ($a) {
                return [
                    'id'              => $a->id_responsable_area,
                    'periodo_anio'    => $a->periodo_anio,
                    'area'            => $a->area ? strtoupper($a->area->nombre_area) : 'N/A',
                    'fecha_asignacion'=> $a->fecha_asignacion
                        ? \Carbon\Carbon::parse($a->fecha_asignacion)->format('d/m/Y H:i')
                        : 'N/A',
                ];
            });

        return response()->json([
            'success'     => true,
            'responsable' => [
                'dni'     => $responsable->dni_responsable,
                'nombre'  => strtoupper(trim($responsable->nombre_responsable . ' ' . $responsable->apellidos_responsable)),
                'cargo'   => strtoupper($responsable->cargo_responsable ?? ''),
            ],
            'asignaciones' => $asignaciones
        ]);
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
