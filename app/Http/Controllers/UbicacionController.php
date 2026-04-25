<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use App\Models\Area;
use App\Http\Requests\UbicacionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UbicacionController extends Controller
{
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
            $searchLower = strtolower($search);
            $query->where(function($q) use ($search, $searchLower) {
                $q->where('id_ubicacion', 'LIKE', "%{$search}%")
                  ->orWhereRaw('LOWER(nombre_sede) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(ambiente) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(piso_ubicacion) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereHas('area', function($q) use ($searchLower) {
                      $q->whereRaw('LOWER(nombre_area) LIKE ?', ["%{$searchLower}%"]);
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
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // ⭐ Si se marca como recepción, desmarcar las demás
            if (isset($data['es_recepcion_inicial']) && $data['es_recepcion_inicial']) {
                Ubicacion::where('es_recepcion_inicial', true)
                    ->update(['es_recepcion_inicial' => false]);

                Log::info("✅ Nueva ubicación de recepción establecida");
            }

            $ubicacion = Ubicacion::create($data);
            $ubicacion->load('area');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ubicación registrada exitosamente',
                'data' => $ubicacion
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // ⭐ Si se marca como recepción, desmarcar las demás
            if (isset($data['es_recepcion_inicial']) && $data['es_recepcion_inicial']) {
                Ubicacion::where('es_recepcion_inicial', true)
                    ->where('id_ubicacion', '!=', $ubicacion->id_ubicacion)
                    ->update(['es_recepcion_inicial' => false]);

                Log::info("✅ Ubicación de recepción actualizada a: {$ubicacion->nombre_sede}");
            }

            $ubicacion->update($data);
            $ubicacion->load('area');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ubicación actualizada exitosamente',
                'data' => $ubicacion
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }
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

    /**
     * ⭐⭐⭐ MARCAR UBICACIÓN COMO RECEPCIÓN INICIAL ⭐⭐⭐
     */
    public function marcarRecepcion(Ubicacion $ubicacion)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }
        try {
            DB::beginTransaction();

            // Desmarcar todas las demás
            Ubicacion::where('es_recepcion_inicial', true)
                ->update(['es_recepcion_inicial' => false]);

            // Marcar esta
            $ubicacion->es_recepcion_inicial = true;
            $ubicacion->save();

            DB::commit();

            Log::info("✅ Ubicación de recepción establecida: {$ubicacion->nombre_sede} (ID: {$ubicacion->id_ubicacion})");

            return response()->json([
                'success' => true,
                'message' => "Ubicación '{$ubicacion->nombre_sede}' marcada como recepción inicial"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Error al marcar recepción: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al marcar ubicación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ⭐⭐⭐ DESMARCAR UBICACIÓN COMO RECEPCIÓN ⭐⭐⭐
     */
    public function desmarcarRecepcion(Ubicacion $ubicacion)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }
        try {
            $ubicacion->es_recepcion_inicial = false;
            $ubicacion->save();

            Log::info("⚠️ Ubicación de recepción desmarcada: {$ubicacion->nombre_sede}");

            return response()->json([
                'success' => true,
                'message' => "Ubicación desmarcada como recepción"
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Error al desmarcar recepción: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al desmarcar: ' . $e->getMessage()
            ], 500);
        }
    }
}
