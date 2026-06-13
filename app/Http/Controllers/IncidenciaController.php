<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use App\Models\Inventario;
use App\Models\Bien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class IncidenciaController extends Controller
{
    /**
     * Muestra el listado de incidencias de un inventario.
     */
    public function index(Inventario $inventario)
    {
        $incidencias = $inventario->incidencias()
            ->with(['bien', 'ubicacion', 'area', 'usuarioRevision'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $incidencias
        ]);
    }


    /**
     * Almacena una nueva incidencia.
     */
    public function store(Request $request, Inventario $inventario)
    {
        $request->validate([
            'tipo_incidencia' => 'required|string|in:sobrante,faltante,sin_codigo,deteriorado',
            'id_bien'         => 'nullable|exists:bien,id_bien',
            'id_ubicacion'    => 'nullable|exists:ubicacion,id_ubicacion',
            'id_area'         => 'nullable|exists:area,id_area',
            'observacion'     => 'nullable|string',
            'foto'            => 'nullable|image|max:2048', // Max 2MB
        ]);

        try {
            DB::beginTransaction();

            $data = $request->only([
                'tipo_incidencia',
                'id_bien',
                'id_ubicacion',
                'id_area',
                'observacion'
            ]);

            $data['id_inventario'] = $inventario->id_inventario;
            $data['fecha_registro'] = now();
            $data['estado'] = \App\Models\Incidencia::ESTADO_NO_REVISADO;

            // Manejo de la imagen
            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('incidencias', 'public');
                $data['img_bien'] = $path;
            }

            // ==================== BLINDAJE PRO: EVITAR DUPLICADOS ====================
            // Si la incidencia tiene un bien asociado, usamos updateOrCreate para no duplicar hallazgos del mismo tipo
            if (!empty($data['id_bien'])) {
                $attributes = [
                    'id_ubicacion' => $data['id_ubicacion'],
                    'id_area'      => $data['id_area'],
                    'observacion'  => $data['observacion'],
                    'estado'       => \App\Models\Incidencia::ESTADO_NO_REVISADO, // Se vuelve a poner en pendiente si se actualiza
                    'fecha_registro' => now(),
                ];

                // Solo actualizar la imagen si se subió una nueva
                if (isset($data['img_bien'])) {
                    $attributes['img_bien'] = $data['img_bien'];
                }

                $incidencia = \App\Models\Incidencia::updateOrCreate(
                    [
                        'id_inventario'   => $inventario->id_inventario,
                        'id_bien'         => $data['id_bien'],
                        'tipo_incidencia' => $data['tipo_incidencia']
                    ],
                    $attributes
                );
            } else {
                // Si es un hallazgo sin código (sin id_bien), sí permitimos múltiples registros
                $incidencia = Incidencia::create($data);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Incidencia registrada correctamente.',
                'incidencia' => $incidencia
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la incidencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambia el estado de una incidencia (Revisado / No Revisado).
     */
    public function cambiarEstado(Request $request, Incidencia $incidencia)
    {
        // Si viene resolución, forzamos el estado a REVISADO
        if ($request->has('resolucion')) {
            $nuevoEstado = Incidencia::ESTADO_REVISADO;
        } else {
            // Si no viene resolución (toggle simple), alternamos el estado
            $nuevoEstado = $incidencia->estado === Incidencia::ESTADO_REVISADO 
                ? Incidencia::ESTADO_NO_REVISADO 
                : Incidencia::ESTADO_REVISADO;
        }

        $updateData = [
            'estado' => $nuevoEstado,
            'id_usuario_revision' => $nuevoEstado === Incidencia::ESTADO_REVISADO ? auth()->id() : null,
            'fecha_revision' => $nuevoEstado === Incidencia::ESTADO_REVISADO ? now() : null,
        ];

        if ($request->has('resolucion')) {
            $updateData['resolucion'] = $request->resolucion;
        }

        $incidencia->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Estado de incidencia actualizado.',
            'nuevo_estado' => $nuevoEstado,
            'badge' => $incidencia->getBadgeEstado()
        ]);
    }



    /**
     * Elimina una incidencia.
     */
    public function destroy(Incidencia $incidencia)
    {
        if ($incidencia->img_bien) {
            Storage::disk('public')->delete($incidencia->img_bien);
        }

        $incidencia->delete();

        return response()->json([
            'success' => true,
            'message' => 'Incidencia eliminada correctamente.'
        ]);
    }
}
