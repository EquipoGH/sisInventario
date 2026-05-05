<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\Movimiento;
use App\Models\EstadoBien;
use App\Models\EstadoConservacion;
use App\Models\TipoMvto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * MovilApiController
 * ─────────────────────────────────────────────────────────────────────────────
 * Endpoints exclusivos para la app móvil Flutter (GestInventario).
 * Todas las rutas están en /api/movil/ y NO requieren autenticación de sesión.
 *
 * Endpoints:
 *   GET  /api/movil/ping                           → test de conectividad
 *   GET  /api/movil/estados-bien                   → catálogo administrativo
 *   GET  /api/movil/estados-conservacion           → condición física del bien
 *   GET  /api/movil/areas                          → lista de áreas
 *   GET  /api/movil/ubicaciones                    → lista de ubicaciones
 *   GET  /api/movil/bienes                         → lista de bienes
 *   GET  /api/movil/bien/{codigo}                  → detalle del bien
 *   GET  /api/movil/bien/{codigo}/ultimo-movimiento → bien + último movimiento
 *   PATCH /api/movil/inventario/{codigo}/conservacion → actualizar condición física
 *   POST  /api/movil/movimiento                    → registrar movimiento
 *   POST  /api/movil/auth/login                    → autenticación
 *   POST  /api/movil/bienes/auditoria-lote         → auditoría en lote
 * ─────────────────────────────────────────────────────────────────────────────
 */
class MovilApiController extends Controller
{
    // =========================================================================
    // PING
    // =========================================================================

    public function ping()
    {
        return response()->json([
            'ok'      => true,
            'message' => 'GestInventario API OK',
            'version' => '2.0',
            'time'    => now()->toIso8601String(),
        ]);
    }

    // =========================================================================
    // AUTENTICACIÓN
    // =========================================================================

    public function login(Request $request)
    {
        $request->validate([
            'dni'   => 'required|string',
            'clave' => 'required|string',
        ]);

        try {
            $user = User::where('dni', $request->dni)->first();

            if (!$user) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            // Verificar contraseña (hash bcrypt)
            if (!\Illuminate\Support\Facades\Hash::check($request->clave, $user->password)) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Contraseña incorrecta',
                ], 401);
            }

            if (!$user->activo) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Usuario inactivo. Contacta al administrador.',
                ], 403);
            }

            return response()->json([
                'ok'      => true,
                'message' => 'Login exitoso',
                'user'    => [
                    'id'         => $user->id,
                    'id_usuario' => $user->id,
                    'name'       => $user->name,
                    'dni'        => $user->dni,
                    'email'      => $user->email,
                    'rol'        => $user->rol ?? $user->perfil ?? 'Operador',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('MovilApi::login error: ' . $e->getMessage());
            return response()->json([
                'ok'      => false,
                'message' => 'Error en el servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // CATÁLOGOS
    // =========================================================================

    /** Catálogo: estados administrativos del bien (Activo, Baja, Prestado…) */
    public function estadosBien()
    {
        try {
            $estados = EstadoBien::orderBy('nombre_estado')->get([
                'id_estado_bien',
                'nombre_estado',
            ]);

            return response()->json([
                'ok'      => true,
                'estados' => $estados,
                'total'   => $estados->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /** Catálogo: condición FÍSICA del bien (Bueno, Regular, Malo, Chatarra) */
    public function estadosConservacion()
    {
        try {
            $estados = EstadoConservacion::orderBy('nombre_conservacion')->get([
                'id_estado_conservacion',
                'nombre_conservacion',
            ]);

            return response()->json([
                'ok'      => true,
                'estados' => $estados,
                'total'   => $estados->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /** Catálogo: áreas */
    public function areas()
    {
        try {
            $areas = \App\Models\Area::orderBy('nombre_area')->get([
                'id_area',
                'nombre_area',
            ]);

            return response()->json([
                'ok'     => true,
                'areas'  => $areas,
                'total'  => $areas->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /** Catálogo: ubicaciones (amb. + área) */
    public function ubicaciones(Request $request)
    {
        try {
            $query = \App\Models\Ubicacion::with('area')
                ->orderBy('ambiente');

            if ($request->filled('id_area')) {
                $query->where('id_area', $request->id_area);
            }

            $ubicaciones = $query->get()->map(fn($u) => [
                'id_ubicacion' => $u->id_ubicacion,
                'ambiente'     => $u->ambiente,
                'id_area'      => $u->id_area,
                'nombre_area'  => $u->area?->nombre_area,
            ]);

            return response()->json([
                'ok'          => true,
                'ubicaciones' => $ubicaciones,
                'total'       => $ubicaciones->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // BIENES
    // =========================================================================

    /** Lista de bienes activos */
    public function bienes(Request $request)
    {
        try {
            $query = Bien::with(['tipoBien', 'estadoBien', 'estadoConservacion'])
                ->where('activo', true);

            if ($request->filled('search')) {
                $term = strtolower($request->search);
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(codigo_patrimonial) LIKE ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(denominacion_bien) LIKE ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(marca_bien) LIKE ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(modelo_bien) LIKE ?', ["%{$term}%"]);
                });
            }

            $bienes = $query->orderByDesc('created_at')->get()
                ->map(fn($b) => $this->_formatBien($b));

            return response()->json([
                'ok'     => true,
                'bienes' => $bienes,
                'total'  => $bienes->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('MovilApi::bienes: ' . $e->getMessage());
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /** Detalle de un bien por codigo_patrimonial */
    public function detalleBien(string $codigo)
    {
        try {
            $bien = Bien::with(['tipoBien', 'estadoBien', 'estadoConservacion', 'documentoSustento'])
                ->where('codigo_patrimonial', $codigo)
                ->first();

            if (!$bien) {
                return response()->json([
                    'ok'      => false,
                    'message' => "Bien no encontrado con código: {$codigo}",
                ], 404);
            }

            return response()->json([
                'ok'   => true,
                'bien' => $this->_formatBien($bien),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bien + su último movimiento + catálogo de estados de conservación.
     * Usado por el módulo Inventario de la app móvil.
     */
    public function ultimoMovimientoInventario(string $codigo)
    {
        try {
            $bien = Bien::with(['tipoBien', 'estadoBien', 'estadoConservacion'])
                ->where('codigo_patrimonial', $codigo)
                ->first();

            if (!$bien) {
                return response()->json([
                    'ok'      => false,
                    'message' => "Bien no encontrado: {$codigo}",
                ], 404);
            }

            // Obtener el ÚLTIMO movimiento válido (no anulado)
            $movimiento = Movimiento::with(['tipoMovimiento', 'ubicacion.area', 'usuario', 'estadoConservacion'])
                ->where('idbien', $bien->id_bien)
                ->where(fn($q) => $q->whereNull('anulado')->orWhere('anulado', false))
                ->orderByDesc('fecha_mvto')
                ->orderByDesc('id_movimiento')
                ->first();

            // Catálogo de condiciones físicas para el combo
            $estados = EstadoConservacion::orderBy('nombre_conservacion')->get([
                'id_estado_conservacion',
                'nombre_conservacion',
            ]);

            return response()->json([
                'ok'                   => true,
                'bien'                 => $this->_formatBien($bien),
                'ultimo_movimiento'    => $movimiento ? $this->_formatMovimiento($movimiento) : null,
                'estados_conservacion' => $estados,
            ]);
        } catch (\Exception $e) {
            Log::error('MovilApi::ultimoMovimiento: ' . $e->getMessage());
            return response()->json([
                'ok'      => false,
                'message' => 'Error al consultar: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // INVENTARIO — ACTUALIZAR ESTADO DE CONSERVACIÓN
    // =========================================================================

    /**
     * Actualiza SOLO el estado de conservación de un bien.
     * Crea un movimiento de tipo INVENTARIO en el historial.
     * PATCH /api/movil/inventario/{codigo}/conservacion
     */
    public function actualizarConservacion(Request $request, string $codigo)
    {
        $request->validate([
            'id_estado_conservacion' => 'required|integer|exists:estado_conservacion,id_estado_conservacion',
            'idusuario'              => 'required|integer',
            'observacion'            => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $bien = Bien::where('codigo_patrimonial', $codigo)->first();

            if (!$bien) {
                return response()->json([
                    'ok'      => false,
                    'message' => "Bien no encontrado: {$codigo}",
                ], 404);
            }

            $idEstado    = (int) $request->id_estado_conservacion;
            $idUsuario   = (int) $request->idusuario;
            $observacion = $request->observacion ?? '';

            // Obtener la ubicación actual del bien (del último movimiento)
            $ultimoMov = Movimiento::where('idbien', $bien->id_bien)
                ->where(fn($q) => $q->whereNull('anulado')->orWhere('anulado', false))
                ->orderByDesc('fecha_mvto')
                ->orderByDesc('id_movimiento')
                ->first();

            // Buscar o crear el tipo de movimiento INVENTARIO
            $tipoInventario = TipoMvto::whereRaw("UPPER(TRIM(tipo_mvto)) = 'INVENTARIO'")->first();
            if (!$tipoInventario) {
                $tipoInventario = TipoMvto::create(['tipo_mvto' => 'INVENTARIO']);
            }

            // Actualizar la condición física en la tabla bien
            $bien->id_estado_conservacion = $idEstado;
            $bien->save();

            // Registrar el movimiento de inventario
            $movimiento = Movimiento::create([
                'idbien'                      => $bien->id_bien,
                'tipo_mvto'                   => $tipoInventario->id_tipo_mvto,
                'fecha_mvto'                  => now(),
                'detalle_tecnico'             => 'INVENTARIO — ' .
                    trim('Actualización de estado de conservación. ' . $observacion),
                'id_estado_conservacion_bien' => $idEstado,
                'idubicacion'                 => $ultimoMov?->idubicacion,
                'idusuario'                   => $idUsuario,
                'anulado'                     => false,
            ]);

            DB::commit();

            $estadoNombre = EstadoConservacion::find($idEstado)?->nombre_conservacion ?? '—';

            return response()->json([
                'ok'             => true,
                'success'        => true,
                'message'        => "Estado de conservación actualizado a: {$estadoNombre}",
                'bien'           => [
                    'id_bien'                => $bien->id_bien,
                    'codigo_patrimonial'     => $bien->codigo_patrimonial,
                    'id_estado_conservacion' => $bien->id_estado_conservacion,
                    'nombre_conservacion'    => $estadoNombre,
                ],
                'movimiento_id'  => $movimiento->id_movimiento,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Validación fallida',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MovilApi::actualizarConservacion: ' . $e->getMessage(), [
                'codigo' => $codigo,
                'trace'  => $e->getTraceAsString(),
            ]);
            return response()->json([
                'ok'      => false,
                'message' => 'Error al actualizar: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // REGISTRAR MOVIMIENTO
    // =========================================================================

    public function registrarMovimiento(Request $request)
    {
        $request->validate([
            'idbien'     => 'required|integer',
            'tipo_mvto'  => 'required|integer',
            'idusuario'  => 'required|integer',
            'idubicacion'=> 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            $bien = Bien::findOrFail($request->idbien);

            $movimiento = Movimiento::create([
                'idbien'                      => $request->idbien,
                'tipo_mvto'                   => $request->tipo_mvto,
                'fecha_mvto'                  => $request->fecha_mvto ?? now(),
                'detalle_tecnico'             => $request->detalle_tecnico ?? '',
                'documento_sustentatorio'     => $request->documento_sustentatorio,
                'NumDocto'                    => $request->NumDocto,
                'idubicacion'                 => $request->idubicacion,
                'id_estado_conservacion_bien' => $request->id_estado_conservacion_bien
                    ?? $bien->id_estado_conservacion,
                'idusuario'                   => $request->idusuario,
                'anulado'                     => false,
            ]);

            // Si hay estado de conservación nuevo, actualizar el bien
            if ($request->filled('id_estado_conservacion_bien')) {
                $bien->id_estado_conservacion = $request->id_estado_conservacion_bien;
                $bien->save();
            }

            DB::commit();

            return response()->json([
                'ok'          => true,
                'success'     => true,
                'message'     => 'Movimiento registrado exitosamente',
                'movimiento'  => [
                    'id_movimiento' => $movimiento->id_movimiento,
                    'fecha_mvto'    => $movimiento->fecha_mvto,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MovilApi::registrarMovimiento: ' . $e->getMessage());
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // AUDITORÍA EN LOTE
    // =========================================================================

    public function auditoriaLote(Request $request)
    {
        $request->validate([
            'codigos'   => 'required|array|min:1',
            'idusuario' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $exitosos = 0;
            $fallidos = [];

            // Tipo de movimiento INVENTARIO/AUDITORÍA
            $tipoInv = TipoMvto::whereRaw("UPPER(TRIM(tipo_mvto)) IN ('INVENTARIO', 'AUDITORIA', 'AUDITORÍA')")
                ->first();
            if (!$tipoInv) {
                $tipoInv = TipoMvto::create(['tipo_mvto' => 'INVENTARIO']);
            }

            foreach ($request->codigos as $codigo) {
                $bien = Bien::where('codigo_patrimonial', $codigo)->first();

                if (!$bien) {
                    $fallidos[] = ['codigo' => $codigo, 'motivo' => 'Bien no encontrado'];
                    continue;
                }

                $ultimoMov = Movimiento::where('idbien', $bien->id_bien)
                    ->orderByDesc('fecha_mvto')
                    ->first();

                Movimiento::create([
                    'idbien'                      => $bien->id_bien,
                    'tipo_mvto'                   => $tipoInv->id_tipo_mvto,
                    'fecha_mvto'                  => now(),
                    'detalle_tecnico'             => 'AUDITORÍA EN LOTE',
                    'id_estado_conservacion_bien' => $bien->id_estado_conservacion
                        ?? $ultimoMov?->id_estado_conservacion_bien,
                    'idubicacion'                 => $ultimoMov?->idubicacion,
                    'idusuario'                   => $request->idusuario,
                    'anulado'                     => false,
                ]);

                $exitosos++;
            }

            DB::commit();

            return response()->json([
                'ok'       => true,
                'success'  => true,
                'message'  => "{$exitosos} bienes auditados correctamente",
                'exitosos' => $exitosos,
                'fallidos' => $fallidos,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ok'      => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function _formatBien(Bien $b): array
    {
        return [
            'id_bien'                => $b->id_bien,
            'codigo_patrimonial'     => $b->codigo_patrimonial,
            'denominacion_bien'      => $b->denominacion_bien,
            'marca_bien'             => $b->marca_bien,
            'modelo_bien'            => $b->modelo_bien,
            'color_bien'             => $b->color_bien,
            'nserie_bien'            => $b->nserie_bien,
            'foto_bien'              => $b->foto_bien,
            'fecha_registro'         => $b->fecha_registro,
            'activo'                 => $b->activo,

            // Estado administrativo
            'id_estado_bien'         => $b->id_estado_bien,
            'estado_bien'            => $b->estadoBien
                ? ['id_estado'      => $b->estadoBien->id_estado,     // PK real de estado_bien
                   'id_estado_bien' => $b->estadoBien->id_estado,     // alias para la app
                   'nombre_estado'  => $b->estadoBien->nombre_estado]
                : null,

            // Condición física
            'id_estado_conservacion' => $b->id_estado_conservacion,
            'estado_conservacion'    => $b->estadoConservacion
                ? ['id_estado_conservacion' => $b->estadoConservacion->id_estado_conservacion,
                   'nombre_conservacion'    => $b->estadoConservacion->nombre_conservacion]
                : null,

            // Tipo bien
            'id_tipobien'            => $b->id_tipobien,
            'tipo_bien'              => $b->tipoBien
                ? ['id_tipo_bien' => $b->tipoBien->id_tipo_bien,
                   'nombre_tipo'  => $b->tipoBien->nombre_tipo]
                : null,
        ];
    }

    private function _formatMovimiento(Movimiento $m): array
    {
        return [
            'id_movimiento'               => $m->id_movimiento,
            'fecha_mvto'                  => $m->fecha_mvto?->toIso8601String(),
            'detalle_tecnico'             => $m->detalle_tecnico,
            'id_estado_conservacion_bien' => $m->id_estado_conservacion_bien,

            // Tipo de movimiento
            'tipo_movimiento' => $m->tipoMovimiento
                ? ['id_tipo_mvto' => $m->tipoMovimiento->id_tipo_mvto,
                   'tipo_mvto'    => $m->tipoMovimiento->tipo_mvto]
                : null,

            // Ubicación
            'ubicacion' => $m->ubicacion
                ? ['id_ubicacion' => $m->ubicacion->id_ubicacion,
                   'ambiente'     => $m->ubicacion->ambiente,
                   'area'         => $m->ubicacion->area
                       ? ['id_area'     => $m->ubicacion->area->id_area,
                          'nombre_area' => $m->ubicacion->area->nombre_area]
                       : null]
                : null,

            // Usuario responsable
            'usuario' => $m->usuario
                ? ['id'   => $m->usuario->id,
                   'name' => $m->usuario->name,
                   'dni'  => $m->usuario->dni]
                : null,

            // Estado de conservación en el momento del movimiento
            'estado_conservacion' => $m->estadoConservacion
                ? ['id_estado_conservacion' => $m->estadoConservacion->id_estado_conservacion,
                   'nombre_conservacion'    => $m->estadoConservacion->nombre_conservacion]
                : null,
        ];
    }
}
