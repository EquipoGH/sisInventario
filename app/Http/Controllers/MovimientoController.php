<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Bien;
use App\Models\TipoMvto;
use App\Models\User;
use App\Models\Ubicacion;
use App\Models\EstadoBien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MovimientoController extends Controller
{
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
            'estadoConservacion'
        ]);

        // 🔍 BÚSQUEDA AVANZADA
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_movimiento', 'LIKE', "%{$search}%")
                  ->orWhere('detalle_tecnico', 'ILIKE', "%{$search}%")
                //   ->orWhere('detalle_administrativo', 'ILIKE', "%{$search}%")
                  ->orWhereHas('bien', function($q) use ($search) {
                      $q->where('codigo_patrimonial', 'ILIKE', "%{$search}%")
                        ->orWhere('denominacion_bien', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('tipoMovimiento', function($q) use ($search) {
                      $q->where('tipo_mvto', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('usuario', function($q) use ($search) {
                      $q->where('name', 'ILIKE', "%{$search}%");
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

        // 📊 ORDENAMIENTO DINÁMICO
        $columna = $request->get('orden', 'fecha');
        $direccion = $request->get('direccion', 'desc');

        $columnasPermitidas = [
            'id' => 'id_movimiento',
            'fecha' => 'fecha_mvto',
            'tipo' => 'tipo_mvto',
            'bien' => 'idbien'
        ];

        if (array_key_exists($columna, $columnasPermitidas)) {
            $columnaReal = $columnasPermitidas[$columna];
        } else {
            $columnaReal = 'fecha_mvto';
        }

        $direccion = in_array(strtolower($direccion), ['asc', 'desc'])
            ? strtolower($direccion)
            : 'desc';

        $query->orderBy($columnaReal, $direccion);

        // 📄 PAGINACIÓN
        $movimientos = $query->paginate($perPage);

        // Datos para los selectores
        $tiposMovimiento = TipoMvto::orderBy('tipo_mvto')->get();
        $bienes = Bien::with('tipoBien')->orderBy('codigo_patrimonial')->get();
        $usuarios = User::orderBy('name')->get();
        $ubicaciones = Ubicacion::with('area')->orderBy('nombre_sede')->get();
        $estadosConservacion = EstadoBien::orderBy('nombre_estado')->get();

        // ✅ RESPUESTA AJAX COMPLETA CON TODAS LAS RELACIONES
        if ($request->ajax()) {
            // Transformar cada movimiento para incluir todas las relaciones
            $movimientosData = $movimientos->getCollection()->map(function ($movimiento) {
                return [
                    'id_movimiento' => $movimiento->id_movimiento,
                    'fecha_mvto' => $movimiento->fecha_mvto,
                    'detalle_tecnico' => $movimiento->detalle_tecnico,
                    'detalle_administrativo' => $movimiento->detalle_administrativo,
                    'idbien' => $movimiento->idbien,
                    'tipo_mvto' => $movimiento->tipo_mvto,
                    'idubicacion' => $movimiento->idubicacion,
                    'id_estado_conservacion_bien' => $movimiento->id_estado_conservacion_bien,  // ✅ CORREGIDO
                    'idusuario' => $movimiento->idusuario,
                    'created_at' => $movimiento->created_at,
                    'updated_at' => $movimiento->updated_at,

                    // ✅ BIEN CON TIPO
                    'bien' => [
                        'id_bien' => $movimiento->bien->id_bien,
                        'codigo_patrimonial' => $movimiento->bien->codigo_patrimonial,
                        'denominacion_bien' => $movimiento->bien->denominacion_bien,
                        'tipo_bien' => $movimiento->bien->tipoBien ? [
                            'id_tipo_bien' => $movimiento->bien->tipoBien->id_tipo_bien,
                            'nombre_tipo' => $movimiento->bien->tipoBien->nombre_tipo
                        ] : null
                    ],

                    // ✅ TIPO DE MOVIMIENTO
                    'tipo_movimiento' => [
                        'id_tipo_mvto' => $movimiento->tipoMovimiento->id_tipo_mvto,
                        'tipo_mvto' => $movimiento->tipoMovimiento->tipo_mvto
                    ],

                    // ✅ USUARIO
                    'usuario' => [
                        'id' => $movimiento->usuario->id,
                        'name' => $movimiento->usuario->name,
                        'email' => $movimiento->usuario->email
                    ],

                    // ✅ UBICACIÓN COMPLETA
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

                    // ✅ ESTADO DE CONSERVACIÓN
                    'estado_conservacion' => $movimiento->estadoConservacion ? [
                        'id_estado' => $movimiento->estadoConservacion->id_estado,
                        'nombre_estado' => $movimiento->estadoConservacion->nombre_estado
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
                // 'detalle_administrativo' => 'nullable|string|max:500',
                'idubicacion' => 'nullable|exists:ubicacion,id_ubicacion',
                'id_estado_conservacion_bien' => 'nullable|exists:estado_bien,id_estado'  // ✅ CORREGIDO
            ]);

            $validated['idusuario'] = Auth::id();

            $movimiento = Movimiento::create($validated);

            // Cargar relaciones completas
            $movimiento->load([
                'bien.tipoBien',
                'tipoMovimiento',
                'usuario',
                'ubicacion.area',
                'estadoConservacion'
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
            'estadoConservacion'
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
            'estadoConservacion'
        ]);

        $tiposMovimiento = TipoMvto::orderBy('tipo_mvto')->get();
        $bienes = Bien::with('tipoBien')->orderBy('codigo_patrimonial')->get();
        $ubicaciones = Ubicacion::with('area')->orderBy('nombre_sede')->get();
        $estadosConservacion = EstadoBien::orderBy('nombre_estado')->get();

        return response()->json([
            'success' => true,
            'data' => $movimiento,
            'catalogos' => [
                'tiposMovimiento' => $tiposMovimiento,
                'bienes' => $bienes,
                'ubicaciones' => $ubicaciones,
                'estadosConservacion' => $estadosConservacion
            ]
        ]);
    }

    public function update(Request $request, Movimiento $movimiento)
    {
        try {
            // 🔍 DEBUGGING
            \Log::info('═══════════════════════════════════════');
            \Log::info('📥 UPDATE - DATOS RECIBIDOS:', $request->all());

            $validated = $request->validate([
                'idbien' => 'required|exists:bien,id_bien',
                'tipo_mvto' => 'required|exists:tipo_mvto,id_tipo_mvto',
                'fecha_mvto' => 'required|date',
                'detalle_tecnico' => 'nullable|string|max:500',
                // 'detalle_administrativo' => 'nullable|string|max:500',
                'idubicacion' => 'nullable|exists:ubicacion,id_ubicacion',
                'id_estado_conservacion_bien' => 'nullable|exists:estado_bien,id_estado'  // ✅ CORREGIDO
            ]);

            \Log::info('✅ DATOS VALIDADOS:', $validated);

            $movimiento->update($validated);

            $movimiento->refresh();

            \Log::info('💾 DESPUÉS DE ACTUALIZAR:', [
                'id' => $movimiento->id_movimiento,
                'estado' => $movimiento->id_estado_conservacion_bien
            ]);

            // Cargar relaciones completas
            $movimiento->load([
                'bien.tipoBien',
                'tipoMovimiento',
                'usuario',
                'ubicacion.area',
                'estadoConservacion'
            ]);

            \Log::info('═══════════════════════════════════════');

            return response()->json([
                'success' => true,
                'message' => 'Movimiento actualizado exitosamente',
                'data' => $movimiento
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ ERROR DE VALIDACIÓN:', $e->errors());

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
                'estadoConservacion'
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
                'estadoConservacion'
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
                'estadoConservacion'
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
                    'usuario'
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
}
