<?php

namespace App\Http\Controllers;

use App\Models\TipoMvto;
use App\Http\Requests\TipoMvtoRequest;
use Illuminate\Http\Request;

class TipoMvtoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permiso:Tipo De Movimientos');
    }
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

        $query = TipoMvto::query();

        // Aplicar búsqueda
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_tipo_mvto', 'LIKE', "%{$search}%")
                  ->orWhere('tipo_mvto', 'ILIKE', "%{$search}%");
            });
        }

        // 🔥 APLICAR ORDENAMIENTO
        switch ($orden) {
            case 'id':
                $query->orderBy('id_tipo_mvto', $direccion);
                break;
            case 'nombre':
                $query->orderBy('tipo_mvto', $direccion);
                break;
            case 'fecha':
                $query->orderBy('created_at', $direccion);
                break;
            default:
                $query->orderBy('id_tipo_mvto', 'desc');
        }

        // PAGINACIÓN
        $tiposMvto = $query->paginate($perPage);
        $total = TipoMvto::count();

        // Si es AJAX, devolver JSON con paginación
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $tiposMvto->items(),
                'total' => $total,
                'resultados' => $tiposMvto->total(),
                'current_page' => $tiposMvto->currentPage(),
                'last_page' => $tiposMvto->lastPage(),
                'per_page' => $tiposMvto->perPage(),
                'from' => $tiposMvto->firstItem(),
                'to' => $tiposMvto->lastItem()
            ]);
        }

        return view('tipo_mvto.index', compact('tiposMvto', 'total'));
    }

    public function store(TipoMvtoRequest $request)
    {
        \Log::info('Datos recibidos en store:', $request->all());

        try {
            $tipoMvto = TipoMvto::create($request->validated());

            \Log::info('TipoMvto creado:', $tipoMvto->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Tipo de movimiento creado exitosamente',
                'data' => $tipoMvto
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al crear TipoMvto:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el tipo de movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(TipoMvto $tipoMvto)
    {
        return response()->json($tipoMvto);
    }

    public function update(TipoMvtoRequest $request, TipoMvto $tipoMvto)
    {
        try {
            $tipoMvto->update($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Tipo de movimiento actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(TipoMvto $tipoMvto)
    {
        try {
            $tipoMvto->delete();
            return response()->json([
                'success' => true,
                'message' => 'Tipo de movimiento eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
