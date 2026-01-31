<?php

namespace App\Http\Controllers;

use App\Models\EstadoBien;
use App\Http\Requests\EstadoBienRequest;
use Illuminate\Http\Request;

class EstadoBienController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        // 🔥 PARÁMETROS DE ORDENAMIENTO
        $orden = $request->get('orden', 'id'); // Por defecto: ID
        $direccion = $request->get('direccion', 'desc'); // Por defecto: DESC

        // Validar columnas permitidas (seguridad)
        $columnasPermitidas = ['id', 'nombre', 'fecha'];
        if (!in_array($orden, $columnasPermitidas)) {
            $orden = 'id';
        }

        // Validar dirección
        $direccion = in_array($direccion, ['asc', 'desc']) ? $direccion : 'desc';

        $query = EstadoBien::query();

        // Aplicar búsqueda
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_estado', 'LIKE', "%{$search}%")
                  ->orWhere('nombre_estado', 'ILIKE', "%{$search}%");
            });
        }

        // 🔥 APLICAR ORDENAMIENTO
        switch ($orden) {
            case 'id':
                $query->orderBy('id_estado', $direccion);
                break;
            case 'nombre':
                $query->orderBy('nombre_estado', $direccion);
                break;
            case 'fecha':
                $query->orderBy('created_at', $direccion);
                break;
            default:
                $query->orderBy('id_estado', 'desc');
        }

        // PAGINACIÓN
        $estados = $query->paginate($perPage);
        $total = EstadoBien::count();

        // Si es AJAX, devolver JSON con paginación
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $estados->items(),
                'total' => $total,
                'resultados' => $estados->total(),
                'current_page' => $estados->currentPage(),
                'last_page' => $estados->lastPage(),
                'per_page' => $estados->perPage(),
                'from' => $estados->firstItem(),
                'to' => $estados->lastItem()
            ]);
        }

        return view('estado_bien.index', compact('estados', 'total'));
    }

    public function store(EstadoBienRequest $request)
    {
        \Log::info('Datos recibidos en store:', $request->all());

        try {
            $estado = EstadoBien::create($request->validated());

            \Log::info('EstadoBien creado:', $estado->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Estado del bien creado exitosamente',
                'data' => $estado
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al crear EstadoBien:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(EstadoBien $estadoBien)
    {
        return response()->json($estadoBien);
    }

    public function update(EstadoBienRequest $request, EstadoBien $estadoBien)
    {
        try {
            $estadoBien->update($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Estado del bien actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(EstadoBien $estadoBien)
    {
        try {
            $estadoBien->delete();
            return response()->json([
                'success' => true,
                'message' => 'Estado del bien eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
