<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Http\Requests\AreaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaController extends Controller
{   

    /**
     * Listar áreas con búsqueda, ordenamiento dinámico y paginación
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        // Total de registros sin filtros
        $total = Area::count();

        $query = Area::query();

        // 🔍 BÚSQUEDA
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_area', 'LIKE', "%{$search}%")
                  ->orWhere('nombre_area', 'ILIKE', "%{$search}%");
            });
        }

        // 📊 ORDENAMIENTO DINÁMICO
        $columna = $request->get('orden', 'created_at');
        $direccion = $request->get('direccion', 'desc');

        // Mapeo de columnas del frontend al backend
        $columnasPermitidas = [
            'id' => 'id_area',
            'nombre' => 'nombre_area',
            'fecha' => 'created_at'
        ];

        // Validar que la columna existe
        if (array_key_exists($columna, $columnasPermitidas)) {
            $columnaReal = $columnasPermitidas[$columna];
        } else {
            $columnaReal = 'created_at'; // Fallback seguro
        }

        // Validar dirección (solo asc o desc)
        $direccion = in_array(strtolower($direccion), ['asc', 'desc'])
            ? strtolower($direccion)
            : 'desc';

        // Aplicar ordenamiento
        $query->orderBy($columnaReal, $direccion);

        // 📄 PAGINACIÓN
        $areas = $query->paginate($perPage);
        $resultados = $query->count(); // Total de resultados filtrados

        // 📤 RESPUESTA AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $areas->items(),
                'total' => $total,
                'resultados' => $areas->total(),
                'current_page' => $areas->currentPage(),
                'last_page' => $areas->lastPage(),
                'per_page' => $areas->perPage(),
                'from' => $areas->firstItem(),
                'to' => $areas->lastItem()
            ]);
        }

        return view('area.index', compact('areas', 'total'));
    }

    /**
     * Guardar nueva área
     */
    public function store(AreaRequest $request)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }
        try {
            $area = Area::create($request->validated());
            return response()->json(['success' => true, 'message' => 'Área registrada exitosamente', 'data' => $area]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener datos para editar
     */
    public function edit(Area $area)
    {
        return response()->json($area);
    }

    /**
     * Actualizar área existente
     */
    public function update(AreaRequest $request, Area $area)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }
        try {
            $area->update($request->validated());
            return response()->json(['success' => true, 'message' => 'Área actualizada exitosamente', 'data' => $area]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar área
     */
    public function destroy(Area $area)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }
        try {
            $area->delete();
            return response()->json(['success' => true, 'message' => 'Área eliminada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar. Puede estar en uso.'], 500);
        }
    }
}
