<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use App\Models\Area;
use App\Http\Requests\UbicacionRequest;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permiso:Ubicaciones');
    }
    /**
     * Listar ubicaciones con búsqueda, ordenamiento dinámico y paginación
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $areaFiltro = $request->get('area_filtro', '');
        $perPage = 10;

        // Total de registros sin filtros
        $total = Ubicacion::count();

        $query = Ubicacion::with('area');

        // 🔍 BÚSQUEDA
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_ubicacion', 'LIKE', "%{$search}%")
                  ->orWhere('nombre_sede', 'ILIKE', "%{$search}%")
                  ->orWhere('ambiente', 'ILIKE', "%{$search}%")
                  ->orWhere('piso_ubicacion', 'ILIKE', "%{$search}%")
                  ->orWhereHas('area', function($q) use ($search) {
                      $q->where('nombre_area', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // 🎯 FILTRO POR ÁREA
        if (!empty($areaFiltro)) {
            $query->where('idarea', $areaFiltro);
        }

        // 📊 ORDENAMIENTO DINÁMICO
        $columna = $request->get('orden', 'created_at');
        $direccion = $request->get('direccion', 'desc');

        // Mapeo de columnas del frontend al backend
        $columnasPermitidas = [
            'id' => 'id_ubicacion',
            'sede' => 'nombre_sede',
            'ambiente' => 'ambiente',
            'piso' => 'piso_ubicacion',
            'area' => 'idarea',
            'fecha' => 'created_at'
        ];

        // Validar que la columna existe
        if (array_key_exists($columna, $columnasPermitidas)) {
            $columnaReal = $columnasPermitidas[$columna];
        } else {
            $columnaReal = 'created_at';
        }

        // Validar dirección
        $direccion = in_array(strtolower($direccion), ['asc', 'desc'])
            ? strtolower($direccion)
            : 'desc';

        // Aplicar ordenamiento
        $query->orderBy($columnaReal, $direccion);

        // 📄 PAGINACIÓN
        $ubicaciones = $query->paginate($perPage);
        $resultados = $query->count();

        // Obtener todas las áreas para el filtro
        $areas = Area::orderBy('nombre_area')->get();

        // 📤 RESPUESTA AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $ubicaciones->items(),
                'total' => $total,
                'resultados' => $ubicaciones->total(),
                'current_page' => $ubicaciones->currentPage(),
                'last_page' => $ubicaciones->lastPage(),
                'per_page' => $ubicaciones->perPage(),
                'from' => $ubicaciones->firstItem(),
                'to' => $ubicaciones->lastItem()
            ]);
        }

        return view('ubicacion.index', compact('ubicaciones', 'total', 'areas'));
    }

    /**
     * Guardar nueva ubicación
     */
    public function store(UbicacionRequest $request)
    {
        try {
            $ubicacion = Ubicacion::create($request->validated());
            $ubicacion->load('area');

            return response()->json([
                'success' => true,
                'message' => 'Ubicación registrada exitosamente',
                'data' => $ubicacion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos para editar
     */
    public function edit(Ubicacion $ubicacion)
    {
        $ubicacion->load('area');
        return response()->json($ubicacion);
    }

    /**
     * Actualizar ubicación existente
     */
    public function update(UbicacionRequest $request, Ubicacion $ubicacion)
    {
        try {
            $ubicacion->update($request->validated());
            $ubicacion->load('area');

            return response()->json([
                'success' => true,
                'message' => 'Ubicación actualizada exitosamente',
                'data' => $ubicacion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar ubicación
     */
    public function destroy(Ubicacion $ubicacion)
    {
        try {
            // Verificar si tiene movimientos asociados
            if ($ubicacion->tieneMovimientos()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar. La ubicación tiene movimientos asociados.'
                ], 400);
            }

            $ubicacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ubicación eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar. Puede estar en uso.'
            ], 500);
        }
    }

    /**
     * Obtener ubicaciones por área (para AJAX)
     */
    public function porArea(Request $request)
    {
        $areaId = $request->get('area_id');

        $ubicaciones = Ubicacion::where('idarea', $areaId)
                                ->orderBy('nombre_sede')
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $ubicaciones
        ]);
    }
}
