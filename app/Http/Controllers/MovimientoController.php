<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Bien;
use App\Models\TipoMvto;
use App\Models\User;
use App\Models\Ubicacion;
use App\Models\EstadoBien;
use App\Models\DocumentoSustento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PDF; // Agregar esta línea al inicio del archivo (después de otros use)


class MovimientoController extends Controller
{
        public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        $total = Movimiento::count();

        // ⭐⭐⭐ ESTADÍSTICAS PARA LAS CARDS DE DASHBOARD ⭐⭐⭐
        $totalBienes = Bien::where('activo', true)->count();

        // Obtener el último movimiento de cada bien y contar por tipo
        $estadisticas = DB::table('movimiento as m1')
            ->select('tm.tipo_mvto', DB::raw('COUNT(DISTINCT m1.idbien) as cantidad'))
            ->join('tipo_mvto as tm', 'm1.tipo_mvto', '=', 'tm.id_tipo_mvto')
            ->join(DB::raw('(SELECT idbien, MAX(fecha_mvto) as max_fecha FROM movimiento GROUP BY idbien) as m2'), function($join) {
                $join->on('m1.idbien', '=', 'm2.idbien')
                    ->on('m1.fecha_mvto', '=', 'm2.max_fecha');
            })
            ->groupBy('tm.tipo_mvto')
            ->get()
            ->keyBy('tipo_mvto');

        // Extraer contadores por tipo (con valores por defecto en 0)
        $bienesAsignados = 0;
        $bienesRegistro = 0;
        $bienesBaja = 0;

        foreach ($estadisticas as $tipo => $data) {
            $tipoUpper = strtoupper($tipo);

            if (str_contains($tipoUpper, 'ASIGNACION') || str_contains($tipoUpper, 'ASIGNACIÓN')) {
                $bienesAsignados = $data->cantidad;
            } elseif (str_contains($tipoUpper, 'REGISTRO')) {
                $bienesRegistro = $data->cantidad;
            } elseif (str_contains($tipoUpper, 'BAJA')) {
                $bienesBaja = $data->cantidad;
            }
        }

        $query = Movimiento::with([
            'bien.tipoBien',
            'tipoMovimiento',
            'usuario',
            'ubicacion.area',
            'estadoConservacion',
            'documentoSustento'
        ]);

        // ✅ FILTRO DE ESTADO DEL BIEN (NUEVO)
        if ($request->filled('estado_bien')) {
            $estadoBien = $request->estado_bien;

            if ($estadoBien === 'todos') {
                // No aplicar filtro, mostrar todos
            } elseif ($estadoBien === '1') {
                // Solo activos
                $query->whereHas('bien', function($q) {
                    $q->where('activo', true);
                });
            } elseif ($estadoBien === '0') {
                // Solo inactivos
                $query->whereHas('bien', function($q) {
                    $q->where('activo', false);
                });
            }
        } else {
            // Por defecto: solo activos (comportamiento actual)
            $query->whereHas('bien', function($q) {
                $q->where('activo', true);
            });
        }

        // ✅ FILTRO DE UBICACIÓN (NUEVO)
        if ($request->filled('ubicacion')) {
            $query->where('idubicacion', $request->ubicacion);
        }

        // 🔍 BÚSQUEDA AVANZADA
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_movimiento', 'LIKE', "%{$search}%")
                ->orWhere('detalle_tecnico', 'ILIKE', "%{$search}%")
                ->orWhere('NumDocto', 'ILIKE', "%{$search}%")
                ->orWhereHas('bien', function($q) use ($search) {
                    $q->where('codigo_patrimonial', 'ILIKE', "%{$search}%")
                        ->orWhere('denominacion_bien', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('tipoMovimiento', function($q) use ($search) {
                    $q->where('tipo_mvto', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('usuario', function($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('documentoSustento', function($q) use ($search) {
                    $q->where('numero_documento', 'ILIKE', "%{$search}%")
                        ->orWhere('tipo_documento', 'ILIKE', "%{$search}%");
                });
            });
        }

        // 📊 FILTROS ADICIONALES
        if ($request->filled('tipo_mvto')) {
            $query->where('tipo_mvto', $request->tipo_mvto);
        }

        if ($request->filled('bien_id')) {
            $query->where('idbien', $request->bien_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_mvto', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_mvto', '<=', $request->fecha_hasta);
        }

        if ($request->filled('usuario_id')) {
            $query->where('idusuario', $request->usuario_id);
        }

        // ⭐⭐⭐ ORDENAMIENTO DINÁMICO (POR DEFECTO ID DESC) ⭐⭐⭐
        $columna = $request->get('orden', 'id');          // ✅ CAMBIADO DE 'fecha' A 'id'
        $direccion = $request->get('direccion', 'desc');

        $columnasPermitidas = [
            'id' => 'id_movimiento',
            'fecha' => 'fecha_mvto',
            'tipo' => 'tipo_mvto',
            'bien' => 'idbien',
            'responsable' => 'idusuario'
        ];

        if (array_key_exists($columna, $columnasPermitidas)) {
            $columnaReal = $columnasPermitidas[$columna];
        } else {
            $columnaReal = 'id_movimiento';  // ✅ CAMBIADO DE 'fecha_mvto' A 'id_movimiento'
        }

        $direccion = in_array(strtolower($direccion), ['asc', 'desc'])
            ? strtolower($direccion)
            : 'desc';

        // ✅ ORDENAMIENTO PRINCIPAL POR ID DESCENDENTE
        $query->orderBy($columnaReal, $direccion);

        // ✅ ORDENAMIENTO SECUNDARIO SOLO SI NO ES POR ID
        if ($columnaReal !== 'id_movimiento') {
            $query->orderBy('id_movimiento', 'desc');
        }

        // 📄 PAGINACIÓN
        $movimientos = $query->paginate($perPage);

        // ⭐ DATOS PARA LOS SELECTORES
        $tiposMovimiento = TipoMvto::orderBy('tipo_mvto')->get();
        $bienes = Bien::with('tipoBien')->orderBy('codigo_patrimonial')->get();
        $usuarios = User::orderBy('name')->get();
        $ubicaciones = Ubicacion::with('area')->orderBy('nombre_sede')->get();
        $estadosConservacion = EstadoBien::orderBy('nombre_estado')->get();
        $documentos = DocumentoSustento::orderBy('fecha_documento', 'desc')->get();

        // ✅ RESPUESTA AJAX
        if ($request->ajax()) {
            $movimientosData = $movimientos->getCollection()->map(function ($movimiento) {
                return [
                    'id_movimiento' => $movimiento->id_movimiento,
                    'fecha_mvto' => $movimiento->fecha_mvto,
                    'detalle_tecnico' => $movimiento->detalle_tecnico,
                    'idbien' => $movimiento->idbien,
                    'tipo_mvto' => $movimiento->tipo_mvto,
                    'idubicacion' => $movimiento->idubicacion,
                    'id_estado_conservacion_bien' => $movimiento->id_estado_conservacion_bien,
                    'idusuario' => $movimiento->idusuario,
                    'documento_sustentatorio' => $movimiento->documento_sustentatorio,
                    'NumDocto' => $movimiento->NumDocto,

                    'bien' => [
                        'id_bien' => $movimiento->bien->id_bien,
                        'codigo_patrimonial' => $movimiento->bien->codigo_patrimonial,
                        'denominacion_bien' => $movimiento->bien->denominacion_bien,
                        'tipo_bien' => $movimiento->bien->tipoBien ? [
                            'id_tipo_bien' => $movimiento->bien->tipoBien->id_tipo_bien,
                            'nombre_tipo' => $movimiento->bien->tipoBien->nombre_tipo
                        ] : null
                    ],

                    'tipo_movimiento' => [
                        'id_tipo_mvto' => $movimiento->tipoMovimiento->id_tipo_mvto,
                        'tipo_mvto' => $movimiento->tipoMovimiento->tipo_mvto
                    ],

                    'usuario' => [
                        'id' => $movimiento->usuario->id,
                        'name' => $movimiento->usuario->name,
                        'email' => $movimiento->usuario->email
                    ],

                    'ubicacion' => $movimiento->ubicacion ? [
                        'id_ubicacion' => $movimiento->ubicacion->id_ubicacion,
                        'nombre_sede' => $movimiento->ubicacion->nombre_sede,
                        'ambiente' => $movimiento->ubicacion->ambiente,
                        'piso_ubicacion' => $movimiento->ubicacion->piso_ubicacion,
                        'idarea' => $movimiento->ubicacion->idarea,
                        'area' => $movimiento->ubicacion->area ? [
                            'id_area' => $movimiento->ubicacion->area->id_area,
                            'nombre_area' => $movimiento->ubicacion->area->nombre_area
                        ] : null
                    ] : null,

                    'estado_conservacion' => $movimiento->estadoConservacion ? [
                        'id_estado' => $movimiento->estadoConservacion->id_estado,
                        'nombre_estado' => $movimiento->estadoConservacion->nombre_estado
                    ] : null,

                    'documento_sustento' => $movimiento->documentoSustento ? [
                        'id_documento' => $movimiento->documentoSustento->id_documento,
                        'tipo_documento' => $movimiento->documentoSustento->tipo_documento,
                        'numero_documento' => $movimiento->documentoSustento->numero_documento,
                        'fecha_documento' => $movimiento->documentoSustento->fecha_documento
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $movimientosData,
                'total' => $total,
                'resultados' => $movimientos->total(),
                'current_page' => $movimientos->currentPage(),
                'last_page' => $movimientos->lastPage(),
                'per_page' => $movimientos->perPage(),
                'from' => $movimientos->firstItem(),
                'to' => $movimientos->lastItem()
            ]);
        }

        return view('movimiento.index', compact(
            'movimientos',
            'tiposMovimiento',
            'bienes',
            'usuarios',
            'ubicaciones',
            'estadosConservacion',
            'documentos',
            'total',
            'totalBienes',
            'bienesAsignados',
            'bienesRegistro',
            'bienesBaja'
        ));
    }



    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'idbien' => 'required|exists:bien,id_bien',
                'tipo_mvto' => 'required|exists:tipo_mvto,id_tipo_mvto',
                'fecha_mvto' => 'required|date',
                'detalle_tecnico' => 'nullable|string|max:500',
                'idubicacion' => 'nullable|exists:ubicacion,id_ubicacion',
                'id_estado_conservacion_bien' => 'nullable|exists:estado_bien,id_estado',
                'documento_sustentatorio' => 'nullable|exists:documento_sustento,id_documento',
                'NumDocto' => 'nullable|string|max:20'
            ]);

            $validated['idusuario'] = Auth::id();

            if ($validated['fecha_mvto']) {
                $fecha = \Carbon\Carbon::parse($validated['fecha_mvto']);
                if ($fecha->format('H:i:s') === '00:00:00') {
                    $validated['fecha_mvto'] = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
                }
            }

            $movimiento = Movimiento::create($validated);

            $movimiento->load([
                'bien.tipoBien',
                'tipoMovimiento',
                'usuario',
                'ubicacion.area',
                'estadoConservacion',
                'documentoSustento'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento registrado exitosamente',
                'data' => $movimiento
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al crear movimiento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Movimiento $movimiento)
    {
        $movimiento->load([
            'bien.tipoBien',
            'tipoMovimiento',
            'usuario',
            'ubicacion.area',
            'estadoConservacion',
            'documentoSustento'
        ]);

        return response()->json([
            'success' => true,
            'data' => $movimiento
        ]);
    }

    public function edit(Movimiento $movimiento)
    {
        $movimiento->load([
            'bien.tipoBien',
            'tipoMovimiento',
            'usuario',
            'ubicacion.area',
            'estadoConservacion',
            'documentoSustento'
        ]);

        $tiposMovimiento = TipoMvto::orderBy('tipo_mvto')->get();
        $bienes = Bien::with('tipoBien')->orderBy('codigo_patrimonial')->get();
        $ubicaciones = Ubicacion::with('area')->orderBy('nombre_sede')->get();
        $estadosConservacion = EstadoBien::orderBy('nombre_estado')->get();
        $documentos = DocumentoSustento::orderBy('fecha_documento', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $movimiento,
            'catalogos' => [
                'tiposMovimiento' => $tiposMovimiento,
                'bienes' => $bienes,
                'ubicaciones' => $ubicaciones,
                'estadosConservacion' => $estadosConservacion,
                'documentos' => $documentos
            ]
        ]);
    }

    public function update(Request $request, Movimiento $movimiento)
    {
        try {
            $validated = $request->validate([
                'idbien' => 'required|exists:bien,id_bien',
                'tipo_mvto' => 'required|exists:tipo_mvto,id_tipo_mvto',
                'fecha_mvto' => 'required|date',
                'detalle_tecnico' => 'nullable|string|max:500',
                'idubicacion' => 'nullable|exists:ubicacion,id_ubicacion',
                'id_estado_conservacion_bien' => 'nullable|exists:estado_bien,id_estado',
                'documento_sustentatorio' => 'nullable|exists:documento_sustento,id_documento',
                'NumDocto' => 'nullable|string|max:20'
            ]);

            $movimiento->update($validated);
            $movimiento->refresh();

            $movimiento->load([
                'bien.tipoBien',
                'tipoMovimiento',
                'usuario',
                'ubicacion.area',
                'estadoConservacion',
                'documentoSustento'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento actualizado exitosamente',
                'data' => $movimiento
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al actualizar movimiento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Movimiento $movimiento)
    {
        try {
            $movimiento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar movimiento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar movimientos masivamente (hard delete)
     */
    public function eliminarMasivo(Request $request)
    {
        try {
            $validated = $request->validate([
                'movimientos_ids' => 'required|array|min:1',
                'movimientos_ids.*' => 'exists:movimiento,id_movimiento'
            ], [
                'movimientos_ids.required' => 'Debe seleccionar al menos un movimiento',
                'movimientos_ids.*.exists' => 'Uno o más movimientos no existen'
            ]);

            $cantidadEliminados = 0;

            foreach ($validated['movimientos_ids'] as $movimientoId) {
                $movimiento = Movimiento::find($movimientoId);

                if ($movimiento) {
                    $movimiento->delete(); // Hard delete (elimina permanentemente)
                    $cantidadEliminados++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$cantidadEliminados} movimiento(s) eliminado(s) exitosamente",
                'cantidad' => $cantidadEliminados
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en eliminación masiva de movimientos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }


    public function porTipo(Request $request)
    {
        try {
            $tipoId = $request->get('tipo_id');

            if (!$tipoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe especificar un tipo de movimiento'
                ], 400);
            }

            $movimientos = Movimiento::with([
                'bien.tipoBien',
                'tipoMovimiento',
                'usuario',
                'ubicacion.area',
                'estadoConservacion',
                'documentoSustento'
            ])
            ->where('tipo_mvto', $tipoId)
            ->orderBy('fecha_mvto', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $movimientos,
                'total' => $movimientos->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al filtrar por tipo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al filtrar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function porBien(Request $request)
    {
        try {
            $bienId = $request->get('bien_id');

            if (!$bienId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe especificar un bien'
                ], 400);
            }

            $movimientos = Movimiento::with([
                'bien.tipoBien',
                'tipoMovimiento',
                'usuario',
                'ubicacion.area',
                'estadoConservacion',
                'documentoSustento'
            ])
            ->where('idbien', $bienId)
            ->orderBy('fecha_mvto', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $movimientos,
                'total' => $movimientos->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al filtrar por bien: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al filtrar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function porFecha(Request $request)
    {
        try {
            $desde = $request->get('desde');
            $hasta = $request->get('hasta');

            if (!$desde || !$hasta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe especificar rango de fechas'
                ], 400);
            }

            $movimientos = Movimiento::with([
                'bien.tipoBien',
                'tipoMovimiento',
                'usuario',
                'ubicacion.area',
                'estadoConservacion',
                'documentoSustento'
            ])
            ->whereBetween('fecha_mvto', [$desde, $hasta])
            ->orderBy('fecha_mvto', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $movimientos,
                'total' => $movimientos->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al filtrar por fecha: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al filtrar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_movimientos' => Movimiento::count(),
                'movimientos_hoy' => Movimiento::whereDate('fecha_mvto', today())->count(),
                'movimientos_mes' => Movimiento::whereMonth('fecha_mvto', now()->month)
                                              ->whereYear('fecha_mvto', now()->year)
                                              ->count(),
                'por_tipo' => Movimiento::select('tipo_mvto', DB::raw('count(*) as total'))
                                       ->groupBy('tipo_mvto')
                                       ->with('tipoMovimiento')
                                       ->get(),
                'ultimos_5' => Movimiento::with([
                    'bien.tipoBien',
                    'tipoMovimiento',
                    'usuario',
                    'documentoSustento'
                ])
                ->orderBy('fecha_mvto', 'desc')
                ->limit(5)
                ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $estadisticas
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ⭐⭐⭐ ASIGNACIÓN MASIVA DE MOVIMIENTOS ⭐⭐⭐
     * MODIFICADO: Tipo de movimiento forzado a ASIGNACIÓN
     */
    public function asignarMasivo(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'bienes_ids' => 'required|array|min:1',
                'bienes_ids.*' => 'exists:bien,id_bien',
                'fecha_mvto' => 'required|date',
                'detalle_tecnico' => 'nullable|string|max:500',
                'idubicacion' => 'nullable|exists:ubicacion,id_ubicacion',
                'id_estado_conservacion_bien' => 'nullable|exists:estado_bien,id_estado',
                'documento_sustentatorio' => 'nullable|exists:documento_sustento,id_documento',
                'NumDocto' => 'nullable|string|max:20'
            ]);

            // ⭐ FORZAR TIPO DE MOVIMIENTO A "ASIGNACIÓN"
            $tipoAsignacion = TipoMvto::where('tipo_mvto', 'ILIKE', '%asignaci%')->first();

            if (!$tipoAsignacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe el tipo de movimiento ASIGNACIÓN en el sistema'
                ], 400);
            }

            $movimientosCreados = [];
            $usuarioId = Auth::id();

            foreach ($validated['bienes_ids'] as $bienId) {
                $bien = Bien::find($bienId);

                if (!$bien) {
                    continue;
                }

                $fechaMovimiento = $validated['fecha_mvto'];
                $fecha = \Carbon\Carbon::parse($fechaMovimiento);
                if ($fecha->format('H:i:s') === '00:00:00') {
                    $fechaMovimiento = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
                }

                $movimiento = Movimiento::create([
                    'idbien' => $bienId,
                    'tipo_mvto' => $tipoAsignacion->id_tipo_mvto, // ⭐ FORZADO
                    'fecha_mvto' => $fechaMovimiento,
                    'detalle_tecnico' => $validated['detalle_tecnico'] ?? 'Asignación masiva: ' . strtoupper($bien->denominacion_bien),
                    'idubicacion' => $validated['idubicacion'] ?? null,
                    'id_estado_conservacion_bien' => $validated['id_estado_conservacion_bien'] ?? null,
                    'idusuario' => $usuarioId,
                    'documento_sustentatorio' => $validated['documento_sustentatorio'] ?? null,
                    'NumDocto' => $validated['NumDocto'] ?? null
                ]);

                $movimiento->load([
                    'bien.tipoBien',
                    'tipoMovimiento',
                    'usuario',
                    'ubicacion.area',
                    'estadoConservacion',
                    'documentoSustento'
                ]);

                $movimientosCreados[] = $movimiento;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($movimientosCreados) . ' movimiento(s) de ASIGNACIÓN creado(s)',
                'data' => $movimientosCreados,
                'cantidad' => count($movimientosCreados)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en asignación masiva: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear movimientos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
    * ⭐⭐⭐ BAJA MASIVA DE BIENES (NUEVA FUNCIÓN) ⭐⭐⭐
    * Tipo de movimiento forzado a BAJA
    */
    public function bajarMasivo(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'bienes_ids' => 'required|array|min:1',
                'bienes_ids.*' => 'exists:bien,id_bien',
                'fecha_mvto' => 'required|date',
                'detalle_tecnico' => 'required|string|min:10|max:500', // ⭐ MÍNIMO 10 caracteres
                'documento_sustentatorio' => 'nullable|exists:documento_sustento,id_documento',
                'NumDocto' => 'nullable|string|max:20'
            ], [
                'detalle_tecnico.required' => 'El motivo de baja es obligatorio',
                'detalle_tecnico.min' => 'El motivo debe tener al menos 10 caracteres',
                'detalle_tecnico.max' => 'El motivo no puede exceder los 500 caracteres',
                'bienes_ids.required' => 'Debe seleccionar al menos un bien',
                'fecha_mvto.required' => 'La fecha de baja es obligatoria'
            ]);

            // ⭐ FORZAR TIPO DE MOVIMIENTO A "BAJA"
            $tipoBaja = TipoMvto::where('tipo_mvto', 'ILIKE', '%baja%')->first();

            if (!$tipoBaja) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe el tipo de movimiento BAJA en el sistema'
                ], 400);
            }

            $movimientosCreados = [];
            $bienesYaDeBaja = [];
            $usuarioId = Auth::id();

            foreach ($validated['bienes_ids'] as $bienId) {
                $bien = Bien::with('tipoBien')->find($bienId);

                if (!$bien) {
                    continue;
                }

                // ⭐ VALIDAR QUE EL BIEN NO ESTÉ YA DE BAJA
                $ultimoMovimiento = Movimiento::with('tipoMovimiento')
                    ->where('idbien', $bienId)
                    ->orderBy('fecha_mvto', 'desc')
                    ->first();

                if ($ultimoMovimiento) {
                    $tipoUltimoMov = strtoupper($ultimoMovimiento->tipoMovimiento->tipo_mvto ?? '');
                    if (str_contains($tipoUltimoMov, 'BAJA')) {
                        $bienesYaDeBaja[] = $bien->codigo_patrimonial;
                        continue; // Saltar este bien
                    }
                }

                // ⭐ PREPARAR FECHA (con hora actual si no tiene)
                $fechaMovimiento = $validated['fecha_mvto'];
                $fecha = \Carbon\Carbon::parse($fechaMovimiento);
                if ($fecha->format('H:i:s') === '00:00:00') {
                    $fechaMovimiento = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
                }

                // ⭐ CREAR MOVIMIENTO DE BAJA
                $movimiento = Movimiento::create([
                    'idbien' => $bienId,
                    'tipo_mvto' => $tipoBaja->id_tipo_mvto,
                    'fecha_mvto' => $fechaMovimiento,
                    'detalle_tecnico' => $validated['detalle_tecnico'], // ⭐ MOTIVO DE BAJA
                    'idubicacion' => null, // Sin ubicación (ya no está operativo)
                    'id_estado_conservacion_bien' => null,
                    'idusuario' => $usuarioId,
                    'documento_sustentatorio' => $validated['documento_sustentatorio'] ?? null,
                    'NumDocto' => $validated['NumDocto'] ?? null
                ]);

                $movimiento->load([
                    'bien.tipoBien',
                    'tipoMovimiento',
                    'usuario',
                    'documentoSustento'
                ]);

                $movimientosCreados[] = $movimiento;
            }

            DB::commit();

            // ⭐ LOG DE AUDITORÍA
            Log::info("✅ BAJA MASIVA EJECUTADA", [
                'cantidad_procesados' => count($validated['bienes_ids']),
                'cantidad_dados_baja' => count($movimientosCreados),
                'cantidad_ya_baja' => count($bienesYaDeBaja),
                'usuario' => Auth::user()->name ?? 'Desconocido',
                'usuario_id' => $usuarioId,
                'motivo' => substr($validated['detalle_tecnico'], 0, 100), // Primeros 100 chars
                'fecha' => now()->format('Y-m-d H:i:s')
            ]);

            // ⭐ MENSAJE PERSONALIZADO
            $mensaje = count($movimientosCreados) . ' bien(es) dado(s) de baja exitosamente';

            if (count($bienesYaDeBaja) > 0) {
                $mensaje .= '. ' . count($bienesYaDeBaja) . ' bien(es) ya estaban de baja y fueron omitidos.';
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'data' => $movimientosCreados,
                'cantidad' => count($movimientosCreados),
                'bienes_omitidos' => $bienesYaDeBaja
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ ERROR EN BAJA MASIVA: ' . $e->getMessage(), [
                'usuario_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al dar de baja: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * ⭐ CREAR MOVIMIENTOS MASIVOS
     * Recibe bienes_ids como JSON string
     */
    public function crearMasivo(Request $request)
    {
        try {
            DB::beginTransaction();

            $bienesIds = json_decode($request->input('bienes_ids'), true);

            if (empty($bienesIds) || !is_array($bienesIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se proporcionaron bienes válidos'
                ], 400);
            }

            $validated = $request->validate([
                'tipo_mvto' => 'required|exists:tipo_mvto,id_tipo_mvto',
                'fecha_mvto' => 'required|date',
                'detalle_tecnico' => 'nullable|string|max:500',
                'idubicacion' => 'nullable|exists:ubicacion,id_ubicacion',
                'id_estado_conservacion_bien' => 'nullable|exists:estado_bien,id_estado',
                'documento_sustentatorio' => 'nullable|exists:documento_sustento,id_documento',
                'NumDocto' => 'nullable|string|max:20'
            ]);

            $movimientosCreados = [];
            $usuarioId = Auth::id();

            foreach ($bienesIds as $bienId) {
                $bien = Bien::find($bienId);

                if (!$bien) {
                    continue;
                }

                $fechaMovimiento = $validated['fecha_mvto'];
                $fecha = \Carbon\Carbon::parse($fechaMovimiento);
                if ($fecha->format('H:i:s') === '00:00:00') {
                    $fechaMovimiento = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
                }

                $movimiento = Movimiento::create([
                    'idbien' => $bienId,
                    'tipo_mvto' => $validated['tipo_mvto'],
                    'fecha_mvto' => $fechaMovimiento,
                    'detalle_tecnico' => $validated['detalle_tecnico'] ?? 'Movimiento masivo: ' . strtoupper($bien->denominacion_bien),
                    'idubicacion' => $validated['idubicacion'] ?? null,
                    'id_estado_conservacion_bien' => $validated['id_estado_conservacion_bien'] ?? null,
                    'idusuario' => $usuarioId,
                    'documento_sustentatorio' => $validated['documento_sustentatorio'] ?? null,
                    'NumDocto' => $validated['NumDocto'] ?? null
                ]);

                $movimiento->load([
                    'bien.tipoBien',
                    'tipoMovimiento',
                    'usuario',
                    'ubicacion.area',
                    'estadoConservacion',
                    'documentoSustento'
                ]);

                $movimientosCreados[] = $movimiento;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($movimientosCreados) . ' movimiento(s) creado(s) exitosamente',
                'data' => $movimientosCreados,
                'cantidad' => count($movimientosCreados)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en creación masiva: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear movimientos: ' . $e->getMessage()
            ], 500);
        }
    }




    /**
     * ⭐⭐⭐ TRAZABILIDAD DE UN BIEN (NUEVA FUNCIÓN) ⭐⭐⭐
     * Obtiene el historial completo de movimientos de un bien
     */
    public function trazabilidad(Request $request, $bienId)
    {
        try {
            $bien = Bien::with('tipoBien')->find($bienId);

            if (!$bien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bien no encontrado'
                ], 404);
            }

            $filtro = $request->get('filtro', 'todos');

            $query = Movimiento::with([
                'tipoMovimiento',
                'usuario',
                'ubicacion.area',
                'estadoConservacion',
                'documentoSustento'
            ])
            ->where('idbien', $bienId);

            // Aplicar filtros de fecha
            switch($filtro) {
                case 'mes':
                    $query->where('fecha_mvto', '>=', now()->subMonth());
                    break;
                case 'trimestre':
                    $query->where('fecha_mvto', '>=', now()->subMonths(3));
                    break;
                case 'año':
                    $query->where('fecha_mvto', '>=', now()->subYear());
                    break;
                // 'todos' no aplica filtro
            }

            // ✅✅✅ ORDENAMIENTO CORREGIDO: PRIMERO FECHA, LUEGO ID ✅✅✅
            $movimientos = $query
                ->orderBy('fecha_mvto', 'desc')
                ->orderBy('id_movimiento', 'desc')  // ⭐ AGREGADO PARA DESEMPATAR
                ->get();

            // Estadísticas rápidas
            $estadisticas = [
                'total_movimientos' => $movimientos->count(),
                'ultimo_movimiento' => $movimientos->first() ? $movimientos->first()->fecha_mvto : null,
                'tipos' => $movimientos->groupBy('tipoMovimiento.tipo_mvto')->map(function($items) {
                    return $items->count();
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $movimientos,
                'total' => $movimientos->count(),
                'bien' => [
                    'id_bien' => $bien->id_bien,
                    'codigo_patrimonial' => $bien->codigo_patrimonial,
                    'denominacion_bien' => $bien->denominacion_bien,
                    'tipo_bien' => $bien->tipoBien ? $bien->tipoBien->nombre_tipo : null
                ],
                'estadisticas' => $estadisticas,
                'filtro_aplicado' => $filtro
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener trazabilidad: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener trazabilidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ⭐⭐⭐ GENERAR PDF DE TRAZABILIDAD ⭐⭐⭐
     * Genera un PDF con el historial completo de movimientos del bien
     */
    public function generarPDFTrazabilidad(Request $request, $bienId)
    {
        try {
            // Validar que el usuario esté autenticado
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            $bien = Bien::with('tipoBien')->find($bienId);

            if (!$bien) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bien no encontrado'
                ], 404);
            }

            $filtro = $request->get('filtro', 'todos');

            // ⭐ Cargar todas las relaciones necesarias
            $query = Movimiento::with([
                'tipoMovimiento',
                'usuario',
                'ubicacion.area',
                'estadoConservacion',
                'documentoSustento'
            ])
            ->where('idbien', $bienId);

            // Aplicar filtros de fecha
            switch($filtro) {
                case 'mes':
                    $query->where('fecha_mvto', '>=', now()->subMonth());
                    $periodoTexto = 'Último Mes';
                    break;
                case 'trimestre':
                    $query->where('fecha_mvto', '>=', now()->subMonths(3));
                    $periodoTexto = 'Último Trimestre';
                    break;
                case 'año':
                    $query->where('fecha_mvto', '>=', now()->subYear());
                    $periodoTexto = 'Último Año';
                    break;
                default:
                    $periodoTexto = 'Todos los Movimientos';
            }

            // ✅✅✅ ORDENAMIENTO CORREGIDO: PRIMERO FECHA, LUEGO ID ✅✅✅
            $movimientos = $query
                ->orderBy('fecha_mvto', 'desc')
                ->orderBy('id_movimiento', 'desc')  // ⭐ AGREGADO PARA DESEMPATAR
                ->get();

            // ⭐⭐⭐ DEBUG - Registrar en log qué se está cargando ⭐⭐⭐
            \Log::info('=== PDF TRAZABILIDAD - DEBUG ===');
            foreach($movimientos as $mov) {
                \Log::info("Movimiento ID: {$mov->id_movimiento}");
                \Log::info("FK documento_sustentatorio: " . ($mov->documento_sustentatorio ?? 'NULL'));
                \Log::info("Relación documentoSustento cargada: " . ($mov->documentoSustento ? 'SÍ' : 'NO'));

                if($mov->documentoSustento) {
                    \Log::info("ID del documento: {$mov->documentoSustento->id_documento}");
                    \Log::info("nombre_documento: " . ($mov->documentoSustento->nombre_documento ?? 'NULL'));
                    \Log::info("Todos los campos: " . json_encode($mov->documentoSustento->toArray()));
                }
                \Log::info('---');
            }

            // Estadísticas mejoradas
            $estadisticas = [
                'total_movimientos' => $movimientos->count(),
                'ultimo_movimiento' => $movimientos->first() ? $movimientos->first()->fecha_mvto : null,
                'tipos' => $movimientos->groupBy('tipoMovimiento.tipo_mvto')->map(function($items) {
                    return $items->count();
                }),
                'con_documentos' => $movimientos->filter(function($mov) {
                    return $mov->documentoSustento !== null;
                })->count()
            ];

            // Datos para la vista
            $data = [
                'bien' => $bien,
                'movimientos' => $movimientos,
                'estadisticas' => $estadisticas,
                'periodo' => $periodoTexto,
                'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
                'usuario' => Auth::user()
            ];

            // Generar PDF
            $pdf = \PDF::loadView('movimiento.pdf-trazabilidad', $data);

            // ⭐ Configuración MEJORADA del PDF
            $pdf->setPaper('A4', 'portrait');

            // ⚠️ NOTA: Los márgenes se controlan desde la vista con @page
            // Estas opciones son para dompdf antiguo, pero @page es más confiable
            $pdf->setOption('enable-local-file-access', true);

            // Nombre del archivo descriptivo
            $nombreArchivo = 'Trazabilidad_'
                . str_replace(' ', '_', $bien->codigo_patrimonial)
                . '_' . now()->format('Ymd_His')
                . '.pdf';

            // Descargar PDF
            return $pdf->download($nombreArchivo);

        } catch (\Exception $e) {
            Log::error('Error al generar PDF de trazabilidad: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Si es petición AJAX, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al generar PDF: ' . $e->getMessage()
                ], 500);
            }

            // Si es petición normal, redirigir con error
            return back()->with('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }






    /**
     * ⭐⭐⭐ REVERTIR BAJA - CLONA EL MOVIMIENTO ANTERIOR ⭐⭐⭐
     * Cuando se revierte una baja, se crea un NUEVO movimiento que es COPIA EXACTA
     * del movimiento anterior a la baja (mismo tipo, ubicación, estado, etc.)
     */
    public function revertirBaja(Request $request, $bienId)
{
    try {
        // 1️⃣ VALIDAR QUE SOLO EL ADMIN PUEDA EJECUTAR
        $usuario = Auth::user();

        if (method_exists($usuario, 'esAdmin')) {
            if (!$usuario->esAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Solo el administrador puede revertir bajas'
                ], 403);
            }
        } else {
            if (!isset($usuario->rol) || ($usuario->rol !== 'admin' && $usuario->rol !== 'administrador')) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Solo el administrador puede revertir bajas'
                ], 403);
            }
        }

        // 2️⃣ VALIDAR DATOS DE ENTRADA
        $validated = $request->validate([
            'detalle_tecnico' => 'required|string|max:200',
            'fecha_mvto' => 'nullable|date',  // ✅ Cambié a nullable
            'documento_sustentatorio' => 'nullable|integer',
            'NumDocto' => 'nullable|string|max:20',
        ], [
            'detalle_tecnico.required' => 'El motivo de reversión es obligatorio',
            'detalle_tecnico.max' => 'El motivo no puede exceder los 200 caracteres',
            'fecha_mvto.date' => 'La fecha debe ser válida',
        ]);

        // 3️⃣ BUSCAR EL ÚLTIMO MOVIMIENTO DEL BIEN (debe ser BAJA)
        $ultimoMovimiento = Movimiento::with(['tipoMovimiento', 'bien'])
            ->where('idbien', $bienId)
            ->orderBy('fecha_mvto', 'desc')
            ->orderBy('id_movimiento', 'desc')  // ✅ Agregado para desempate
            ->first();

        if (!$ultimoMovimiento) {
            return response()->json([
                'success' => false,
                'message' => '❌ Este bien no tiene movimientos registrados'
            ], 404);
        }

        // 4️⃣ VALIDAR QUE SEA UN MOVIMIENTO DE BAJA
        $tipoBaja = strtoupper($ultimoMovimiento->tipoMovimiento->tipo_mvto);
        if (!str_contains($tipoBaja, 'BAJA')) {
            return response()->json([
                'success' => false,
                'message' => '❌ Este movimiento no es de tipo BAJA (tipo actual: ' . $ultimoMovimiento->tipoMovimiento->tipo_mvto . ')'
            ], 400);
        }

        // 5️⃣ VALIDAR QUE NO ESTÉ YA REVERTIDO
        if ($ultimoMovimiento->revertido) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Este movimiento ya fue revertido anteriormente el ' .
                            \Carbon\Carbon::parse($ultimoMovimiento->fecha_reversion)->format('d/m/Y H:i')
            ], 400);
        }

        DB::beginTransaction();

        // 6️⃣ ⭐⭐⭐ BUSCAR EL MOVIMIENTO ANTERIOR A LA BAJA (PARA CLONARLO) ⭐⭐⭐
        $movimientoAnterior = Movimiento::with(['tipoMovimiento'])
            ->where('idbien', $ultimoMovimiento->idbien)
            ->where('fecha_mvto', '<', $ultimoMovimiento->fecha_mvto)
            ->where('id_movimiento', '!=', $ultimoMovimiento->id_movimiento)
            ->orderBy('fecha_mvto', 'DESC')
            ->orderBy('id_movimiento', 'DESC')  // ✅ Agregado para desempate
            ->first();

        if (!$movimientoAnterior) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '❌ No existe un movimiento anterior a la baja para restaurar'
            ], 400);
        }

        // 7️⃣ ⭐⭐⭐ USAR HORA ACTUAL DEL SERVIDOR ⭐⭐⭐
        $fechaReversion = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $motivoUsuario = $validated['detalle_tecnico'];

        // ✅ DETALLE TÉCNICO PERSONALIZADO
        $detalleNuevo = sprintf(
            "Reversión de BAJA #%d | Motivo: %s",
            $ultimoMovimiento->id_movimiento,
            substr($motivoUsuario, 0, 150)
        );
        $detalleNuevo = substr($detalleNuevo, 0, 200);

        // ✅✅✅ CREAR NUEVO MOVIMIENTO (COPIA DEL ANTERIOR) ✅✅✅
        $nuevoMovimiento = Movimiento::create([
            'idbien' => $movimientoAnterior->idbien,
            'tipo_mvto' => $movimientoAnterior->tipo_mvto,
            'fecha_mvto' => $fechaReversion,  // ✅ Usa hora actual del servidor
            'idubicacion' => $movimientoAnterior->idubicacion,
            'id_estado_conservacion_bien' => $movimientoAnterior->id_estado_conservacion_bien,
            'detalle_tecnico' => $detalleNuevo,
            'NumDocto' => $validated['NumDocto'] ?? $movimientoAnterior->NumDocto,
            'idusuario' => Auth::id(),
            'documento_sustentatorio' => $validated['documento_sustentatorio'] ?? $movimientoAnterior->documento_sustentatorio
        ]);

        // 8️⃣ MARCAR EL MOVIMIENTO DE BAJA COMO REVERTIDO
        $ultimoMovimiento->update([
            'revertido' => true,
            'revertido_por' => Auth::id(),
            'fecha_reversion' => $fechaReversion,
            'movimiento_reversion_id' => $nuevoMovimiento->id_movimiento
        ]);

        DB::commit();

        // 9️⃣ LOG DE AUDITORÍA
        Log::info("✅ REVERSIÓN DE BAJA EJECUTADA (CLONÓ MOVIMIENTO ANTERIOR)", [
            'movimiento_baja' => $ultimoMovimiento->id_movimiento,
            'movimiento_anterior_clonado' => $movimientoAnterior->id_movimiento,
            'nuevo_movimiento' => $nuevoMovimiento->id_movimiento,
            'tipo_restaurado' => $movimientoAnterior->tipoMovimiento->tipo_mvto,
            'admin' => $usuario->name,
            'bien_codigo' => $ultimoMovimiento->bien->codigo_patrimonial,
            'fecha_reversion' => $fechaReversion
        ]);

        // Cargar relaciones para la respuesta
        $nuevoMovimiento->load([
            'tipoMovimiento',
            'usuario',
            'ubicacion.area',
            'estadoConservacion',
            'bien.tipoBien',
            'documentoSustento'
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Baja revertida exitosamente',
            'data' => [
                'movimientooriginal' => [
                    'id' => $ultimoMovimiento->id_movimiento,
                    'fechabaja' => \Carbon\Carbon::parse($ultimoMovimiento->fecha_mvto)->format('d/m/Y H:i'),
                    'revertidopor' => $usuario->name,
                    'fechareversion' => \Carbon\Carbon::parse($fechaReversion)->format('d/m/Y H:i')
                ],
                'movimientoreversion' => $nuevoMovimiento,
                'bien' => [
                    'id' => $ultimoMovimiento->bien->id_bien,
                    'codigo' => $ultimoMovimiento->bien->codigo_patrimonial,
                    'denominacion' => $ultimoMovimiento->bien->denominacion_bien
                ],
                'estadorestaurado' => "Restaurado al estado: " . $movimientoAnterior->tipoMovimiento->tipo_mvto
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("❌ ERROR AL REVERTIR BAJA: " . $e->getMessage(), [
            'bien_id' => $bienId ?? 'N/A',
            'user_id' => Auth::id(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => '❌ Error al revertir: ' . $e->getMessage()
        ], 500);
    }
}





/**
 * ⭐⭐⭐ OBTENER ESTADÍSTICAS EN TIEMPO REAL (AJAX)
 */
public function getEstadisticas()
{
    $totalBienes = Bien::where('activo', true)->count(); // ✅ Solo activos


    // Obtener el último movimiento de cada bien y contar por tipo
    $estadisticas = DB::table('movimiento as m1')
        ->select('tm.tipo_mvto', DB::raw('COUNT(DISTINCT m1.idbien) as cantidad'))
        ->join('tipo_mvto as tm', 'm1.tipo_mvto', '=', 'tm.id_tipo_mvto')
        ->join(DB::raw('(SELECT idbien, MAX(fecha_mvto) as max_fecha FROM movimiento GROUP BY idbien) as m2'), function($join) {
            $join->on('m1.idbien', '=', 'm2.idbien')
                 ->on('m1.fecha_mvto', '=', 'm2.max_fecha');
        })
        ->groupBy('tm.tipo_mvto')
        ->get()
        ->keyBy('tipo_mvto');

    // Extraer contadores por tipo
    $bienesAsignados = 0;
    $bienesRegistro = 0;
    $bienesBaja = 0;

    foreach ($estadisticas as $tipo => $data) {
        $tipoUpper = strtoupper($tipo);

        if (str_contains($tipoUpper, 'ASIGNACION') || str_contains($tipoUpper, 'ASIGNACIÓN')) {
            $bienesAsignados = $data->cantidad;
        } elseif (str_contains($tipoUpper, 'REGISTRO')) {
            $bienesRegistro = $data->cantidad;
        } elseif (str_contains($tipoUpper, 'BAJA')) {
            $bienesBaja = $data->cantidad;
        }
    }

    return response()->json([
        'success' => true,
        'data' => [
            'totalBienes' => $totalBienes,
            'bienesAsignados' => $bienesAsignados,
            'bienesRegistro' => $bienesRegistro,
            'bienesBaja' => $bienesBaja
        ]
    ]);
}




}
