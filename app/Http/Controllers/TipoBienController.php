<?php

namespace App\Http\Controllers;

use App\Models\TipoBien;
use App\Http\Requests\TipoBienRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TipoBienController extends Controller
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

        $query = TipoBien::query();

        // Aplicar búsqueda
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_tipo_bien', 'LIKE', "%{$search}%")
                  ->orWhere('nombre_tipo', 'ILIKE', "%{$search}%");
            });
        }

        // 🔥 APLICAR ORDENAMIENTO
        switch ($orden) {
            case 'id':
                $query->orderBy('id_tipo_bien', $direccion);
                break;
            case 'nombre':
                $query->orderBy('nombre_tipo', $direccion);
                break;
            case 'fecha':
                $query->orderBy('created_at', $direccion);
                break;
            default:
                $query->orderBy('id_tipo_bien', 'desc');
        }

        // PAGINACIÓN
        $tipoBienes = $query->paginate($perPage);
        $total = TipoBien::count();

        // Si es AJAX, devolver JSON con paginación
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $tipoBienes->items(),
                'total' => $total,
                'resultados' => $tipoBienes->total(),
                'current_page' => $tipoBienes->currentPage(),
                'last_page' => $tipoBienes->lastPage(),
                'per_page' => $tipoBienes->perPage(),
                'from' => $tipoBienes->firstItem(),
                'to' => $tipoBienes->lastItem()
            ]);
        }

        return view('tipo_bien.index', compact('tipoBienes', 'total'));
    }

    public function store(TipoBienRequest $request)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }
        try {
            $tipoBien = TipoBien::create($request->validated());
            return response()->json(['success' => true, 'message' => 'Tipo de bien creado exitosamente', 'data' => $tipoBien]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear el tipo de bien: ' . $e->getMessage()], 500);
        }
    }

    public function edit(TipoBien $tipoBien)
    {
        return response()->json($tipoBien);
    }

    public function update(TipoBienRequest $request, TipoBien $tipoBien)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }
        try {
            $tipoBien->update($request->validated());
            return response()->json(['success' => true, 'message' => 'Tipo de bien actualizado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(TipoBien $tipoBien)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }
        try {
            $tipoBien->delete();
            return response()->json(['success' => true, 'message' => 'Tipo de bien eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
