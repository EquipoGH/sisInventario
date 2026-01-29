<?php

namespace App\Http\Controllers;

use App\Models\Responsable;
use App\Http\Requests\ResponsableRequest;
use Illuminate\Http\Request;

class ResponsableController extends Controller
{
    public function __construct()
    {
        $this->middleware('permiso:Responsables');
    }
    /**
     * Listar responsables con búsqueda, ordenamiento dinámico y paginación
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        // Total de registros sin filtros
        $total = Responsable::count();

        $query = Responsable::query();

        // 🔍 BÚSQUEDA
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('dni_responsable', 'LIKE', "%{$search}%")
                  ->orWhere('nombre_responsable', 'ILIKE', "%{$search}%")
                  ->orWhere('apellidos_responsable', 'ILIKE', "%{$search}%")
                  ->orWhere('cargo_responsable', 'ILIKE', "%{$search}%");
            });
        }

        // 📊 ORDENAMIENTO DINÁMICO
        $columna = $request->get('orden', 'created_at');
        $direccion = $request->get('direccion', 'desc');

        // Mapeo de columnas del frontend al backend
        $columnasPermitidas = [
            'dni' => 'dni_responsable',
            'nombre' => 'nombre_responsable',
            'apellidos' => 'apellidos_responsable',
            'cargo' => 'cargo_responsable',
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
        $responsables = $query->paginate($perPage);
        $resultados = $query->count(); // Total de resultados filtrados

        // 📤 RESPUESTA AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $responsables->items(),
                'total' => $total,
                'resultados' => $responsables->total(),
                'current_page' => $responsables->currentPage(),
                'last_page' => $responsables->lastPage(),
                'per_page' => $responsables->perPage(),
                'from' => $responsables->firstItem(),
                'to' => $responsables->lastItem()
            ]);
        }

        return view('responsable.index', compact('responsables', 'total'));
    }

    /**
     * Guardar nuevo responsable
     */
    public function store(ResponsableRequest $request)
    {
        try {
            $responsable = Responsable::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Responsable registrado exitosamente',
                'data' => $responsable
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
    public function edit(Responsable $responsable)
    {
        return response()->json($responsable);
    }

    /**
     * Actualizar responsable existente
     */
    public function update(ResponsableRequest $request, Responsable $responsable)
    {
        try {
            $responsable->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Responsable actualizado exitosamente',
                'data' => $responsable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar responsable
     */
    public function destroy(Responsable $responsable)
    {
        try {
            // Verificar si tiene áreas asignadas
            if ($responsable->responsableAreas()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar. El responsable tiene áreas asignadas.'
                ], 400);
            }

            $responsable->delete();

            return response()->json([
                'success' => true,
                'message' => 'Responsable eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar. Puede estar en uso.'
            ], 500);
        }
    }
}
