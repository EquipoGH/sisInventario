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

class MovimientoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permiso:Movimientos');
    }

    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        $total = Movimiento::count();

        $query = Movimiento::with([
            'bien.tipoBien',
            'tipoMovimiento',
            'usuario',
            'ubicacion.area',
            'estadoConservacion',
            'documentoSustento'
        ]);

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

        // ⭐⭐⭐ ORDENAMIENTO DINÁMICO ⭐⭐⭐
        $columna = $request->get('orden', 'id');
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
            $columnaReal = 'id_movimiento';
        }

        $direccion = in_array(strtolower($direccion), ['asc', 'desc'])
            ? strtolower($direccion)
            : 'desc';

        $query->orderBy($columnaReal, $direccion);

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
            'total'
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

            // Si la fecha no tiene hora, agregar hora actual
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
     * Recibe bienes_ids como array directo (desde el modal)
     */
    public function asignarMasivo(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'bienes_ids' => 'required|array|min:1',
                'bienes_ids.*' => 'exists:bien,id_bien',
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

            foreach ($validated['bienes_ids'] as $bienId) {
                $bien = Bien::find($bienId);

                if (!$bien) {
                    continue;
                }

                // Si la fecha no tiene hora, agregar hora actual
                $fechaMovimiento = $validated['fecha_mvto'];
                $fecha = \Carbon\Carbon::parse($fechaMovimiento);
                if ($fecha->format('H:i:s') === '00:00:00') {
                    $fechaMovimiento = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
                }

                $movimiento = Movimiento::create([
                    'idbien' => $bienId,
                    'tipo_mvto' => $validated['tipo_mvto'],
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
            Log::error('Error en asignación masiva: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear movimientos: ' . $e->getMessage()
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

            // Decodificar el JSON de IDs de bienes
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

                // Si la fecha no tiene hora, agregar hora actual
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
     * ⭐ ELIMINAR MOVIMIENTOS MASIVOS
     */
    public function eliminarMasivo(Request $request)
    {
        try {
            DB::beginTransaction();

            $ids = $request->input('ids');

            if (empty($ids) || !is_array($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se proporcionaron IDs válidos'
                ], 400);
            }

            // Validar que todos los IDs existan
            $movimientos = Movimiento::whereIn('id_movimiento', $ids)->get();

            if ($movimientos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron movimientos para eliminar'
                ], 404);
            }

            // Eliminar movimientos
            $eliminados = Movimiento::whereIn('id_movimiento', $ids)->delete();

            DB::commit();

            Log::info("Usuario " . Auth::id() . " eliminó {$eliminados} movimiento(s): " . implode(', ', $ids));

            return response()->json([
                'success' => true,
                'message' => "{$eliminados} movimiento(s) eliminado(s) correctamente",
                'cantidad' => $eliminados
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar movimientos masivos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar movimientos: ' . $e->getMessage()
            ], 500);
        }
    }
}
