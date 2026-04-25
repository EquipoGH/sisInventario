<?php

namespace App\Http\Controllers;

use App\Models\EstadoConservacion;
use App\Http\Requests\EstadoConservacionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EstadoConservacionController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = 10;

        $orden     = $request->get('orden', 'id');
        $direccion = $request->get('direccion', 'desc');

        $columnasPermitidas = ['id', 'nombre'];
        if (!in_array($orden, $columnasPermitidas)) {
            $orden = 'id';
        }
        $direccion = in_array($direccion, ['asc', 'desc']) ? $direccion : 'desc';

        $query = EstadoConservacion::query();

        if (!empty($search)) {
            $query->buscar($search);
        }

        match ($orden) {
            'nombre' => $query->orderBy('nombre_conservacion', $direccion),
            default  => $query->orderBy('id_estado_conservacion', $direccion),
        };

        $total  = EstadoConservacion::count();
        $estados = $query->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'success'      => true,
                'data'         => $estados->items(),
                'total'        => $total,
                'resultados'   => $estados->total(),
                'current_page' => $estados->currentPage(),
                'last_page'    => $estados->lastPage(),
                'per_page'     => $estados->perPage(),
                'from'         => $estados->firstItem(),
                'to'           => $estados->lastItem(),
            ]);
        }

        return view('estado_conservacion.index', compact('estados', 'total'));
    }

    public function store(EstadoConservacionRequest $request)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el ADMIN puede realizar esta acción.',
            ], 403);
        }

        try {
            $estado = EstadoConservacion::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Estado de conservación creado exitosamente',
                'data'    => $estado,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(EstadoConservacion $estadoConservacion)
    {
        return response()->json($estadoConservacion);
    }

    public function update(EstadoConservacionRequest $request, EstadoConservacion $estadoConservacion)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el ADMIN puede realizar esta acción.',
            ], 403);
        }

        try {
            $estadoConservacion->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Estado de conservación actualizado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(EstadoConservacion $estadoConservacion)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el ADMIN puede realizar esta acción.',
            ], 403);
        }

        try {
            // Verificar que no tenga movimientos o bienes asociados
            if ($estadoConservacion->movimientos()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar: tiene movimientos asociados.',
                ], 400);
            }

            if ($estadoConservacion->bienes()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar: tiene bienes asociados.',
                ], 400);
            }

            $estadoConservacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Estado de conservación eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
