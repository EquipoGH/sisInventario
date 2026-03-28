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
use Illuminate\Validation\ValidationException;  // ⭐ AGREGAR ESTO
use App\Helpers\PermisosHelper; // ⭐⭐⭐ AGREGAR ESTO ⭐⭐⭐



class MovimientoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        // ⭐⭐⭐ TOTAL DE MOVIMIENTOS FILTRADO POR ÁREA ⭐⭐⭐
        $total = PermisosHelper::getMovimientosQuery()->count();


        // ⭐⭐⭐ ESTADÍSTICAS DINÁMICAS SEGÚN FILTROS ACTIVOS ⭐⭐⭐

        // Crear query base para estadísticas (reutilizar la misma lógica de filtros)
        $queryEstadisticas = DB::table('movimiento as m1')
            ->join('tipo_mvto as tm', 'm1.tipo_mvto', '=', 'tm.id_tipo_mvto')
            ->join('bien as b', 'm1.idbien', '=', 'b.id_bien')
            ->join(DB::raw('(SELECT idbien, MAX(fecha_mvto) as max_fecha FROM movimiento GROUP BY idbien) as m2'), function($join) {
                $join->on('m1.idbien', '=', 'm2.idbien')
                    ->on('m1.fecha_mvto', '=', 'm2.max_fecha');
            });

        // ✅ APLICAR FILTRO DE ESTADO DEL BIEN A ESTADÍSTICAS
        if ($request->filled('estado_bien')) {
            $estadoBien = $request->estado_bien;
            if ($estadoBien === '1') {
                $queryEstadisticas->where('b.activo', true);
            } elseif ($estadoBien === '0') {
                $queryEstadisticas->where('b.activo', false);
            }
            // Si es 'todos', no aplica filtro
        } else {
            // Por defecto: solo activos
            $queryEstadisticas->where('b.activo', true);
        }

        // ✅ APLICAR FILTRO DE TIPO DE MOVIMIENTO A ESTADÍSTICAS
        if ($request->filled('tipo_mvto')) {
            if ($request->tipo_mvto === 'activos') {
                // Filtrar solo SIN ASIGNAR y ASIGNACIÓN
                $tiposActivos = TipoMvto::where(function($q) {
                    $q->where('tipo_mvto', 'ILIKE', '%asignaci%')
                      ->orWhere('tipo_mvto', 'ILIKE', '%sin asignar%')
                      ->orWhere('tipo_mvto', 'ILIKE', '%registro%'); // Fallback
                })->pluck('id_tipo_mvto');

                if ($tiposActivos->isNotEmpty()) {
                    $queryEstadisticas->whereIn('m1.tipo_mvto', $tiposActivos);
                }
            } elseif ($request->tipo_mvto !== '') {
                // Filtro específico por ID
                $queryEstadisticas->where('m1.tipo_mvto', $request->tipo_mvto);
            }
            // Si es '', no aplica filtro (todos los tipos)
        } else {
            // Por defecto: solo SIN ASIGNAR y ASIGNACIÓN
            $tiposActivos = TipoMvto::where(function($q) {
                $q->where('tipo_mvto', 'ILIKE', '%asignaci%')
                  ->orWhere('tipo_mvto', 'ILIKE', '%sin asignar%')
                  ->orWhere('tipo_mvto', 'ILIKE', '%registro%'); // Fallback
            })->pluck('id_tipo_mvto');

            if ($tiposActivos->isNotEmpty()) {
                $queryEstadisticas->whereIn('m1.tipo_mvto', $tiposActivos);
            }
        }

        // ✅ APLICAR FILTRO DE UBICACIÓN A ESTADÍSTICAS
        if ($request->filled('ubicacion')) {
            $queryEstadisticas->where('m1.idubicacion', $request->ubicacion);
        }

        // ⭐⭐⭐ APLICAR FILTRO DE ÁREA A ESTADÍSTICAS (NUEVO) ⭐⭐⭐
        if ($request->filled('area')) {
            $queryEstadisticas->whereExists(function($query) use ($request) {
                $query->select(DB::raw(1))
                    ->from('ubicacion as u')
                    ->whereRaw('u.id_ubicacion = m1.idubicacion')
                    ->where('u.idarea', $request->area);
            });
        }

        // ✅ APLICAR FILTRO DE FECHAS A ESTADÍSTICAS
        if ($request->filled('fecha_desde')) {
            $queryEstadisticas->whereDate('m1.fecha_mvto', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $queryEstadisticas->whereDate('m1.fecha_mvto', '<=', $request->fecha_hasta);
        }

        // ✅ CALCULAR TOTAL DE BIENES (según filtros)
        $totalBienes = $queryEstadisticas->distinct()->count('m1.idbien');

        // ✅ CALCULAR ESTADÍSTICAS POR TIPO
        $estadisticas = (clone $queryEstadisticas)
            ->select('tm.tipo_mvto', DB::raw('COUNT(DISTINCT m1.idbien) as cantidad'))
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
            } elseif (str_contains($tipoUpper, 'SIN ASIGNAR') || str_contains($tipoUpper, 'REGISTRO')) {
                $bienesRegistro = $data->cantidad;
            } elseif (str_contains($tipoUpper, 'BAJA')) {
                $bienesBaja = $data->cantidad;
            }
        }

        // ⭐⭐⭐ QUERY PRINCIPAL DE MOVIMIENTOS ⭐⭐⭐
        // ⭐⭐⭐ FILTRAR POR ÁREA + ESTADO (ACTIVOS/ANULADOS) ⭐⭐⭐
            $mostrarAnulados = $request->filled('mostrar_anulados') && $request->mostrar_anulados === '1';

            if ($mostrarAnulados) {
                // Solo admin puede ver anulados
                if (!Auth::user()->esAdmin()) {
                    abort(403, 'No autorizado para ver movimientos anulados');
                }

                // ⭐ FILTRAR POR ÁREA + ANULADOS
                $query = PermisosHelper::getMovimientosQuery()
                    ->anulados()
                    ->with([
                        'bien.tipoBien',
                        'tipoMovimiento',
                        'usuario',
                        'usuarioAnulo',  // ⭐ Cargar quien anuló
                        'ubicacion.area',
                        'estadoConservacion',
                        'documentoSustento'
                    ]);
            } else {
                // ⭐ FILTRAR POR ÁREA + ACTIVOS
                $query = PermisosHelper::getMovimientosQuery()
                    ->activos()
                    ->with([
                        'bien.tipoBien',
                        'tipoMovimiento',
                        'usuario',
                        'ubicacion.area',
                        'estadoConservacion',
                        'documentoSustento'
                    ]);
            }

        // ⭐⭐⭐ RESTRICCIÓN USUARIO: MOSTRAR SOLO EL ÚLTIMO MOVIMIENTO POR BIEN ⭐⭐⭐
        // Esto evita mostrar el historial completo en la grilla y confundir al operador
        $query->whereIn('movimiento.id_movimiento', function($q) {
            $q->select(\Illuminate\Support\Facades\DB::raw('MAX(id_movimiento)'))
              ->from('movimiento')
              ->groupBy('idbien');
        });

        // ✅ FILTRO DE ESTADO DEL BIEN
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

        // ✅ FILTRO DE UBICACIÓN
        if ($request->filled('ubicacion')) {
            $query->where('idubicacion', $request->ubicacion);
        }

        // ⭐⭐⭐ FILTRO DE ÁREA (NUEVO) ⭐⭐⭐
        if ($request->filled('area')) {
            $query->whereHas('ubicacion', function($q) use ($request) {
                $q->where('idarea', $request->area);
            });
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
        // ✅ FILTRO DE TIPO DE MOVIMIENTO (CORREGIDO - BUENAS PRÁCTICAS UX)
        if ($request->filled('tipo_mvto')) {
            if ($request->tipo_mvto === 'activos') {
                // ✅ OPCIÓN "MOVIMIENTOS ACTIVOS" → SIN ASIGNAR + ASIGNACIÓN
                $tiposActivos = TipoMvto::where(function($q) {
                    $q->where('tipo_mvto', 'ILIKE', '%asignaci%')
                      ->orWhere('tipo_mvto', 'ILIKE', '%sin asignar%')
                      ->orWhere('tipo_mvto', 'ILIKE', '%registro%'); // Fallback
                })->pluck('id_tipo_mvto');

                if ($tiposActivos->isNotEmpty()) {
                    $query->whereIn('tipo_mvto', $tiposActivos);
                }

                // ⭐ EXCLUIR filas de REGISTRO si el bien ya tiene ASIGNACIÓN vigente
                // (evita confusión visual de ver REGISTRO + ASIGNACION del mismo bien)
                $tiposRegistro = TipoMvto::where(function($q) {
                    $q->where('tipo_mvto', 'ILIKE', '%registro%')
                      ->orWhere('tipo_mvto', 'ILIKE', '%sin asignar%');
                })->pluck('id_tipo_mvto');

                $tiposAsignacion = TipoMvto::where('tipo_mvto', 'ILIKE', '%asignaci%')
                    ->pluck('id_tipo_mvto');

                if ($tiposRegistro->isNotEmpty() && $tiposAsignacion->isNotEmpty()) {
                    $query->where(function($q) use ($tiposRegistro, $tiposAsignacion) {
                        $q->whereNotIn('tipo_mvto', $tiposRegistro)
                          ->orWhere(function($q2) use ($tiposRegistro, $tiposAsignacion) {
                              // Es REGISTRO pero el bien NO tiene asignación vigente
                              $q2->whereIn('tipo_mvto', $tiposRegistro)
                                 ->whereDoesntHave('bien.movimientos', function($q3) use ($tiposAsignacion) {
                                     $q3->whereIn('tipo_mvto', $tiposAsignacion)
                                        ->where('anulado', false);
                                 });
                          });
                    });
                }
            } elseif ($request->tipo_mvto === '') {
                // ✅ OPCIÓN "TODOS LOS MOVIMIENTOS" → SIN FILTRO (muestra TODO)
                // No aplicar filtro de tipo, incluye BAJA
            } else {
                // ✅ FILTRO ESPECÍFICO POR ID (un tipo individual)
                $query->where('tipo_mvto', $request->tipo_mvto);
            }
        } else {
            // ✅ POR DEFECTO AL CARGAR: MOVIMIENTOS ACTIVOS (SIN ASIGNAR + ASIGNACIÓN)
            $tiposActivos = TipoMvto::where(function($q) {
                $q->where('tipo_mvto', 'ILIKE', '%asignaci%')
                  ->orWhere('tipo_mvto', 'ILIKE', '%sin asignar%')
                  ->orWhere('tipo_mvto', 'ILIKE', '%registro%'); // Fallback
            })->pluck('id_tipo_mvto');

            if ($tiposActivos->isNotEmpty()) {
                $query->whereIn('tipo_mvto', $tiposActivos);
            }

            // ⭐ EXCLUIR filas de REGISTRO si el bien ya tiene ASIGNACIÓN vigente
            $tiposRegistro = TipoMvto::where(function($q) {
                $q->where('tipo_mvto', 'ILIKE', '%registro%')
                  ->orWhere('tipo_mvto', 'ILIKE', '%sin asignar%');
            })->pluck('id_tipo_mvto');

            $tiposAsignacion = TipoMvto::where('tipo_mvto', 'ILIKE', '%asignaci%')
                ->pluck('id_tipo_mvto');

            if ($tiposRegistro->isNotEmpty() && $tiposAsignacion->isNotEmpty()) {
                $query->where(function($q) use ($tiposRegistro, $tiposAsignacion) {
                    $q->whereNotIn('tipo_mvto', $tiposRegistro)
                      ->orWhere(function($q2) use ($tiposRegistro, $tiposAsignacion) {
                          $q2->whereIn('tipo_mvto', $tiposRegistro)
                             ->whereDoesntHave('bien.movimientos', function($q3) use ($tiposAsignacion) {
                                 $q3->whereIn('tipo_mvto', $tiposAsignacion)
                                    ->where('anulado', false);
                             });
                      });
                });
            }
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

        // ✅ ORDENAMIENTO PRINCIPAL
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

        // ⭐⭐⭐ AGREGAR LISTA DE ÁREAS (NUEVO) ⭐⭐⭐
        $areas = \App\Models\Area::orderBy('nombre_area')->get();

        // ✅ RESPUESTA AJAX (CON ESTADÍSTICAS DINÁMICAS)
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
                'to' => $movimientos->lastItem(),

                // ⭐⭐⭐ ESTADÍSTICAS DINÁMICAS SEGÚN FILTROS ⭐⭐⭐
                'estadisticas' => [
                    'totalBienes' => $totalBienes,
                    'bienesAsignados' => $bienesAsignados,
                    'bienesRegistro' => $bienesRegistro,
                    'bienesBaja' => $bienesBaja
                ]
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
            'areas',
            'total',
            'totalBienes',
            'bienesAsignados',
            'bienesRegistro',
            'bienesBaja'
        ));
    } // ⭐ CIERRE DE index()

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

            // ⭐⭐⭐ OBTENER TIPO DE MOVIMIENTO ⭐⭐⭐
            $tipoMovimiento = TipoMvto::find($validated['tipo_mvto']);
            $tipoNombre = strtoupper($tipoMovimiento->tipo_mvto ?? '');

            $esRegistro = stripos($tipoNombre, 'registro') !== false;
            $esAsignacion = stripos($tipoNombre, 'ASIGNACION') !== false || stripos($tipoNombre, 'ASIGNACIÓN') !== false;
            $esBaja = stripos($tipoNombre, 'baja') !== false;

            // ⭐⭐⭐ VALIDAR: NO SE PUEDE ASIGNAR UN BIEN QUE ESTÁ DE BAJA ⭐⭐⭐
            if ($esAsignacion) {
                $ultimaBaja = Movimiento::where('idbien', $validated['idbien'])
                    ->whereHas('tipoMovimiento', function($q) {
                        $q->where('tipo_mvto', 'ILIKE', '%baja%');
                    })
                    ->where('anulado', false)
                    ->where('revertido', false)
                    ->orderBy('fecha_mvto', 'desc')
                    ->orderBy('id_movimiento', 'desc')
                    ->first();

                if ($ultimaBaja) {
                    \Log::warning('❌ INTENTO DE ASIGNACIÓN A BIEN DE BAJA', [
                        'usuario' => Auth::user()->name,
                        'bien' => $validated['idbien'],
                        'movimiento_baja' => $ultimaBaja->id_movimiento,
                        'fecha_baja' => $ultimaBaja->fecha_mvto
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede asignar un bien que está de BAJA. Primero debe revertir la baja (solo ADMIN puede hacerlo).'
                    ], 400);
                }
            }

            // ⭐⭐⭐ LÓGICA PARA REGISTRO ⭐⭐⭐
            if ($esRegistro) {
                // ✅ SI NO TIENE UBICACIÓN, ASIGNAR LA DE RECEPCIÓN
                if (empty($validated['idubicacion'])) {
                    $ubicacionRecepcion = $this->obtenerUbicacionRecepcion();

                    if ($ubicacionRecepcion) {
                        $validated['idubicacion'] = $ubicacionRecepcion->id_ubicacion;
                        \Log::info("✅ REGISTRO - Ubicación asignada automáticamente: {$ubicacionRecepcion->nombre_sede} (ID: {$ubicacionRecepcion->id_ubicacion})");
                    } else {
                        \Log::warning("⚠️ REGISTRO - No se encontró ubicación de recepción configurada. Ubicación = NULL");
                    }
                }

                // ✅ ASIGNAR ESTADO "NUEVO" SI NO TIENE
                if (empty($validated['id_estado_conservacion_bien'])) {
                    $estadoNuevo = EstadoBien::where('nombre_estado', 'ILIKE', '%nuevo%')
                        ->orWhere('nombre_estado', 'ILIKE', '%bueno%')
                        ->first();

                    if ($estadoNuevo) {
                        $validated['id_estado_conservacion_bien'] = $estadoNuevo->id_estado;
                        \Log::info("✅ REGISTRO - Estado asignado: {$estadoNuevo->nombre_estado}");
                    }
                }
            }

            // ⭐⭐⭐ LÓGICA PARA BAJA ⭐⭐⭐
            if ($esBaja) {
                // ✅ 1. OBTENER ÚLTIMA ASIGNACIÓN DEL BIEN
                $ultimaAsignacion = Movimiento::where('idbien', $validated['idbien'])
                    ->whereHas('tipoMovimiento', function($q) {
                        $q->where('tipo_mvto', 'ILIKE', '%asignaci%');
                    })
                    ->orderBy('fecha_mvto', 'desc')
                    ->orderBy('id_movimiento', 'desc')
                    ->first();

                // ✅ 2. HEREDAR UBICACIÓN DE LA ÚLTIMA ASIGNACIÓN
                if ($ultimaAsignacion && $ultimaAsignacion->idubicacion) {
                    $validated['idubicacion'] = $ultimaAsignacion->idubicacion;
                    \Log::info("✅ BAJA - Heredando ubicación de asignación #{$ultimaAsignacion->id_movimiento}: {$ultimaAsignacion->idubicacion}");
                } else {
                    // Si no hay asignación previa, dejar NULL
                    \Log::warning("⚠️ BAJA - Bien sin asignación previa. Ubicación = NULL");
                }

                // ✅ 3. FORZAR ESTADO "MALO"
                $estadoMalo = EstadoBien::where('nombre_estado', 'ILIKE', '%malo%')
                    ->orWhere('nombre_estado', 'ILIKE', '%inoperativo%')
                    ->orWhere('nombre_estado', 'ILIKE', '%dañado%')
                    ->first();

                if ($estadoMalo) {
                    $validated['id_estado_conservacion_bien'] = $estadoMalo->id_estado;
                    \Log::info("✅ BAJA - Estado forzado a: {$estadoMalo->nombre_estado}");
                } else {
                    \Log::error("❌ BAJA - No se encontró estado 'MALO' en la BD");
                }
            }

            // ⭐ CREAR MOVIMIENTO CON LÓGICA APLICADA
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



        /**
         * ⭐⭐⭐ OBTENER UBICACIÓN DE RECEPCIÓN INICIAL ⭐⭐⭐
         * Prioridad:
         * 1. Campo 'es_recepcion_inicial' en ubicacion (BD)
         * 2. Búsqueda inteligente por nombre (FALLBACK)
         */
        private function obtenerUbicacionRecepcion()
        {
            // ✅ PRIORIDAD 1: Campo en BD
            $ubicacion = Ubicacion::where('es_recepcion_inicial', true)->first();

            if ($ubicacion) {
                \Log::info("✅ Ubicación de recepción desde BD: {$ubicacion->nombre_sede}");
                return $ubicacion;
            }

            // ✅ PRIORIDAD 2: Búsqueda inteligente por nombre (FALLBACK)
            $ubicacion = Ubicacion::where(function($q) {
                $q->where('nombre_sede', 'ILIKE', '%abastecimiento%')
                ->orWhere('nombre_sede', 'ILIKE', '%almacen%')
                ->orWhere('nombre_sede', 'ILIKE', '%almacén%')
                ->orWhere('nombre_sede', 'ILIKE', '%deposito%')
                ->orWhere('nombre_sede', 'ILIKE', '%depósito%');
            })
            ->orWhereHas('area', function($q) {
                $q->where('nombre_area', 'ILIKE', '%abastecimiento%')
                ->orWhere('nombre_area', 'ILIKE', '%almacen%')
                ->orWhere('nombre_area', 'ILIKE', '%logistica%')
                ->orWhere('nombre_area', 'ILIKE', '%patrimonio%')
                ->orWhere('nombre_area', 'ILIKE', '%bodega%');
            })
            ->first();

            if ($ubicacion) {
                \Log::info("⚠️ Ubicación de recepción por búsqueda: {$ubicacion->nombre_sede}");
                return $ubicacion;
            }

            \Log::warning("❌ No se encontró ubicación de recepción inicial");
            return null;
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

    /**
     * ⭐⭐⭐ ANULAR MOVIMIENTO (SOFT DELETE) ⭐⭐⭐
     * No elimina físicamente, marca como anulado
     */
    public function anular(Request $request, Movimiento $movimiento)
    {
        try {
            // ✅ VALIDAR QUE SOLO ADMIN PUEDA ANULAR
            if (!Auth::user()->esAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo el administrador puede anular movimientos'
                ], 403);
            }

            // ✅ VALIDAR QUE NO ESTÉ YA ANULADO
            if ($movimiento->anulado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este movimiento ya fue anulado el ' .
                                \Carbon\Carbon::parse($movimiento->fecha_anulacion)->format('d/m/Y H:i')
                ], 400);
            }

            // ✅ VALIDAR MOTIVO (OBLIGATORIO)
            $validated = $request->validate([
                'motivo_anulacion' => 'required|string|min:10|max:200'
            ], [
                'motivo_anulacion.required' => 'El motivo de anulación es obligatorio',
                'motivo_anulacion.min' => 'El motivo debe tener al menos 10 caracteres'
            ]);

            // ⭐⭐⭐ MARCAR COMO ANULADO (NO ELIMINAR) ⭐⭐⭐
            $movimiento->update([
                'anulado' => true,
                'anulado_por' => Auth::id(),
                'fecha_anulacion' => now(),
                'motivo_anulacion' => $validated['motivo_anulacion']
            ]);

            // 📊 LOG DE AUDITORÍA
            Log::warning('MOVIMIENTO ANULADO', [
                'id_movimiento' => $movimiento->id_movimiento,
                'bien_codigo' => $movimiento->bien->codigo_patrimonial,
                'tipo' => $movimiento->tipoMovimiento->tipo_mvto,
                'anulado_por' => Auth::user()->name,
                'motivo' => $validated['motivo_anulacion'],
                'fecha' => now()->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento anulado exitosamente. Se mantiene en el historial para auditoría.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al anular movimiento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al anular movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ⭐⭐⭐ RESTAURAR MOVIMIENTO ANULADO ⭐⭐⭐
     * Solo ADMIN puede restaurar
     */
    public function restaurar(Request $request, Movimiento $movimiento)
    {
        try {
            // ✅ VALIDAR QUE SOLO ADMIN PUEDA RESTAURAR
            if (!Auth::user()->esAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo el administrador puede restaurar movimientos'
                ], 403);
            }

            // ✅ VALIDAR QUE ESTÉ ANULADO
            if (!$movimiento->anulado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este movimiento no está anulado'
                ], 400);
            }

            // ⭐⭐⭐ RESTAURAR ⭐⭐⭐
            $movimiento->update([
                'anulado' => false,
                'anulado_por' => null,
                'fecha_anulacion' => null,
                'motivo_anulacion' => null
            ]);

            // 📊 LOG DE AUDITORÍA
            Log::info('MOVIMIENTO RESTAURADO', [
                'id_movimiento' => $movimiento->id_movimiento,
                'bien_codigo' => $movimiento->bien->codigo_patrimonial,
                'restaurado_por' => Auth::user()->name,
                'fecha' => now()->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento restaurado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al restaurar movimiento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * ⭐⭐⭐ ANULAR MOVIMIENTOS MASIVAMENTE (SOFT DELETE) ⭐⭐⭐
     */
    public function anularMasivo(Request $request)
    {
        try {
            DB::beginTransaction();

            // ✅ VALIDAR QUE SOLO ADMIN PUEDA ANULAR
            if (!Auth::user()->esAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo el administrador puede anular movimientos'
                ], 403);
            }

            $validated = $request->validate([
                'movimientos_ids' => 'required|array|min:1',
                'movimientos_ids.*' => 'exists:movimiento,id_movimiento',
                'motivo_anulacion' => 'required|string|min:10|max:200'
            ], [
                'movimientos_ids.required' => 'Debe seleccionar al menos un movimiento',
                'movimientos_ids.*.exists' => 'Uno o más movimientos no existen',
                'motivo_anulacion.required' => 'El motivo es obligatorio',
                'motivo_anulacion.min' => 'El motivo debe tener al menos 10 caracteres'
            ]);

            $cantidadAnulados = 0;
            $yaAnulados = [];

            foreach ($validated['movimientos_ids'] as $movimientoId) {
                $movimiento = Movimiento::find($movimientoId);

                if ($movimiento && !$movimiento->anulado) {
                    $movimiento->update([
                        'anulado' => true,
                        'anulado_por' => Auth::id(),
                        'fecha_anulacion' => now(),
                        'motivo_anulacion' => $validated['motivo_anulacion']
                    ]);
                    $cantidadAnulados++;
                } elseif ($movimiento && $movimiento->anulado) {
                    $yaAnulados[] = $movimiento->id_movimiento;
                }
            }

            DB::commit();

            // 📊 LOG DE AUDITORÍA
            Log::warning('ANULACIÓN MASIVA DE MOVIMIENTOS', [
                'cantidad_anulados' => $cantidadAnulados,
                'ya_anulados' => count($yaAnulados),
                'anulado_por' => Auth::user()->name,
                'motivo' => $validated['motivo_anulacion'],
                'fecha' => now()->format('Y-m-d H:i:s')
            ]);

            $mensaje = "$cantidadAnulados movimientos anulados exitosamente";
            if (count($yaAnulados) > 0) {
                $mensaje .= ". " . count($yaAnulados) . " ya estaban anulados.";
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'cantidad' => $cantidadAnulados,
                'ya_anulados' => $yaAnulados
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
            Log::error('Error en anulación masiva de movimientos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al anular: ' . $e->getMessage()
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
            $bienesOmitidos = [];
            $usuarioId = Auth::id();

            foreach ($validated['bienes_ids'] as $bienId) {
                $bien = Bien::find($bienId);

                if (!$bien) {
                    $bienesOmitidos[] = [
                        'idbien' => $bienId,
                        'motivo' => 'Bien no encontrado'
                    ];
                    continue;
                }

                // ⭐⭐⭐ VALIDAR QUE EL BIEN NO ESTÉ DE BAJA ⭐⭐⭐
                $ultimaBaja = Movimiento::where('idbien', $bienId)
                    ->whereHas('tipoMovimiento', function($q) {
                        $q->where('tipo_mvto', 'ILIKE', '%baja%');
                    })
                    ->where('anulado', false)
                    ->where('revertido', false)
                    ->orderBy('fecha_mvto', 'desc')
                    ->orderBy('id_movimiento', 'desc')
                    ->first();

                if ($ultimaBaja) {
                    $bienesOmitidos[] = [
                        'idbien' => $bienId,
                        'codigo' => $bien->codigo_patrimonial,
                        'denominacion' => $bien->denominacion_bien,
                        'motivo' => 'Bien de BAJA (movimiento #' . $ultimaBaja->id_movimiento . ')',
                        'fecha_baja' => $ultimaBaja->fecha_mvto->format('d/m/Y H:i')
                    ];
                    
                    \Log::warning('❌ ASIGNACIÓN MASIVA - Bien omitido por BAJA', [
                        'usuario' => Auth::user()->name,
                        'bien' => $bien->codigo_patrimonial,
                        'movimiento_baja' => $ultimaBaja->id_movimiento
                    ]);
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

            // ⭐ MENSAJE PERSONALIZADO CON DETALLES
            $mensaje = count($movimientosCreados) . ' movimiento(s) de ASIGNACIÓN creado(s)';
            if (count($bienesOmitidos) > 0) {
                $mensaje .= '. ' . count($bienesOmitidos) . ' bien(es) omitido(s) por estar de BAJA.';
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'data' => $movimientosCreados,
                'cantidad' => count($movimientosCreados),
                'omitidos' => $bienesOmitidos
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
     * ⭐⭐⭐ BAJA MASIVA DE BIENES CON HERENCIA DE UBICACIÓN/ÁREA Y ESTADO MALO ⭐⭐⭐
     * Tipo de movimiento forzado a BAJA
     */
    public function bajarMasivo(Request $request)
    {
        try {
            // ⭐⭐⭐ VALIDAR QUE SOLO ADMIN PUEDA DAR DE BAJA ⭐⭐⭐
            if (!\App\Helpers\PermisosHelper::esAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Solo el administrador puede dar de baja bienes'
                ], 403);
            }

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

            // ⭐ OBTENER ESTADO "MALO"
            $estadoMalo = EstadoBien::where('nombre_estado', 'ILIKE', '%malo%')
                ->orWhere('nombre_estado', 'ILIKE', '%inoperativo%')
                ->orWhere('nombre_estado', 'ILIKE', '%dañado%')
                ->first();

            if (!$estadoMalo) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el estado de conservación "MALO" en la base de datos. Por favor, créelo primero.'
                ], 404);
            }

            $movimientosCreados = [];
            $bienesYaDeBaja = [];
            $bienesSinAsignacion = [];
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
                    ->orderBy('id_movimiento', 'desc')
                    ->first();

                if ($ultimoMovimiento) {
                    $tipoUltimoMov = strtoupper($ultimoMovimiento->tipoMovimiento->tipo_mvto ?? '');
                    if (str_contains($tipoUltimoMov, 'BAJA')) {
                        $bienesYaDeBaja[] = $bien->codigo_patrimonial;
                        continue; // Saltar este bien
                    }
                }

                // ⭐⭐⭐ OBTENER ÚLTIMA ASIGNACIÓN PARA HEREDAR UBICACIÓN ⭐⭐⭐
                $ultimaAsignacion = Movimiento::where('idbien', $bienId)
                    ->whereHas('tipoMovimiento', function($q) {
                        $q->where('tipo_mvto', 'ILIKE', '%asignaci%');
                    })
                    ->orderBy('fecha_mvto', 'desc')
                    ->orderBy('id_movimiento', 'desc')
                    ->first();

                // ✅ HEREDAR UBICACIÓN (o NULL si no hay asignación)
                $ubicacionId = null;
                if ($ultimaAsignacion && $ultimaAsignacion->idubicacion) {
                    $ubicacionId = $ultimaAsignacion->idubicacion;

                    Log::info("✅ BAJA MASIVA - Bien {$bien->codigo_patrimonial}: Heredando ubicación de asignación #{$ultimaAsignacion->id_movimiento}");
                } else {
                    $bienesSinAsignacion[] = $bien->codigo_patrimonial;
                    Log::warning("⚠️ BAJA MASIVA - Bien {$bien->codigo_patrimonial}: Sin asignación previa, ubicación = NULL");
                }

                // ⭐ PREPARAR FECHA (con hora actual si no tiene)
                $fechaMovimiento = $validated['fecha_mvto'];
                $fecha = \Carbon\Carbon::parse($fechaMovimiento);
                if ($fecha->format('H:i:s') === '00:00:00') {
                    $fechaMovimiento = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
                }

                // ⭐⭐⭐ CREAR MOVIMIENTO DE BAJA CON UBICACIÓN HEREDADA Y ESTADO MALO ⭐⭐⭐
                $movimiento = Movimiento::create([
                    'idbien' => $bienId,
                    'tipo_mvto' => $tipoBaja->id_tipo_mvto,
                    'fecha_mvto' => $fechaMovimiento,
                    'detalle_tecnico' => $validated['detalle_tecnico'], // ⭐ MOTIVO DE BAJA
                    'idubicacion' => $ubicacionId, // ✅ Heredado de última asignación (o NULL)
                    'id_estado_conservacion_bien' => $estadoMalo->id_estado, // ✅ FORZADO A MALO
                    'idusuario' => $usuarioId,
                    'documento_sustentatorio' => $validated['documento_sustentatorio'] ?? null,
                    'NumDocto' => $validated['NumDocto'] ?? null
                ]);

                $movimiento->load([
                    'bien.tipoBien',
                    'tipoMovimiento',
                    'usuario',
                    'ubicacion.area', // ⭐ Cargar área heredada
                    'estadoConservacion',
                    'documentoSustento'
                ]);

                $movimientosCreados[] = $movimiento;

                Log::info("✅ BAJA CREADA", [
                    'bien' => $bien->codigo_patrimonial,
                    'ubicacion_id' => $ubicacionId,
                    'area' => $movimiento->ubicacion?->area?->nombre_area ?? 'Sin área',
                    'estado' => $estadoMalo->nombre_estado
                ]);
            }

            DB::commit();

            // ⭐ LOG DE AUDITORÍA COMPLETO
            Log::info("✅ BAJA MASIVA EJECUTADA", [
                'cantidad_procesados' => count($validated['bienes_ids']),
                'cantidad_dados_baja' => count($movimientosCreados),
                'cantidad_ya_baja' => count($bienesYaDeBaja),
                'cantidad_sin_asignacion' => count($bienesSinAsignacion),
                'usuario' => Auth::user()->name ?? 'Desconocido',
                'usuario_id' => $usuarioId,
                'motivo' => substr($validated['detalle_tecnico'], 0, 100),
                'estado_aplicado' => $estadoMalo->nombre_estado,
                'fecha' => now()->format('Y-m-d H:i:s')
            ]);

            // ⭐ MENSAJE PERSONALIZADO CON DETALLES
            $mensaje = count($movimientosCreados) . ' bien(es) dado(s) de baja exitosamente';

            if (count($bienesYaDeBaja) > 0) {
                $mensaje .= '. ' . count($bienesYaDeBaja) . ' bien(es) ya estaban de baja y fueron omitidos';
            }

            if (count($bienesSinAsignacion) > 0) {
                $mensaje .= '. ' . count($bienesSinAsignacion) . ' bien(es) sin asignación previa (sin ubicación)';
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'data' => $movimientosCreados,
                'cantidad' => count($movimientosCreados),
                'bienes_omitidos' => $bienesYaDeBaja,
                'bienes_sin_asignacion' => $bienesSinAsignacion,
                'estado_aplicado' => $estadoMalo->nombre_estado
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
            if (!\App\Helpers\PermisosHelper::esAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Solo el administrador puede revertir bajas'
                ], 403);
            }

            // 2️⃣ VALIDAR DATOS DE ENTRADA
            $validated = $request->validate([
                'detalle_tecnico' => 'required|string|max:200',
                'fecha_mvto' => 'nullable|date',
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
                ->orderBy('id_movimiento', 'desc')
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

            // 6️⃣ BUSCAR EL MOVIMIENTO ANTERIOR A LA BAJA (PARA CLONARLO)
            $movimientoAnterior = Movimiento::with(['tipoMovimiento'])
                ->where('idbien', $ultimoMovimiento->idbien)
                ->where('fecha_mvto', '<', $ultimoMovimiento->fecha_mvto)
                ->where('id_movimiento', '!=', $ultimoMovimiento->id_movimiento)
                ->orderBy('fecha_mvto', 'DESC')
                ->orderBy('id_movimiento', 'DESC')
                ->first();

            if (!$movimientoAnterior) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => '❌ No existe un movimiento anterior a la baja para restaurar'
                ], 400);
            }

            // 7️⃣ USAR HORA ACTUAL DEL SERVIDOR
            $fechaReversion = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
            $motivoUsuario = $validated['detalle_tecnico'];

            // DETALLE TÉCNICO PERSONALIZADO
            $detalleNuevo = sprintf(
                "Reversión de BAJA #%d | Motivo: %s",
                $ultimoMovimiento->id_movimiento,
                substr($motivoUsuario, 0, 150)
            );
            $detalleNuevo = substr($detalleNuevo, 0, 200);

            // 8️⃣ CREAR NUEVO MOVIMIENTO (COPIA DEL ANTERIOR)
            $nuevoMovimiento = Movimiento::create([
                'idbien' => $movimientoAnterior->idbien,
                'tipo_mvto' => $movimientoAnterior->tipo_mvto,
                'fecha_mvto' => $fechaReversion,
                'idubicacion' => $movimientoAnterior->idubicacion,
                'id_estado_conservacion_bien' => $movimientoAnterior->id_estado_conservacion_bien,
                'detalle_tecnico' => $detalleNuevo,
                'NumDocto' => $validated['NumDocto'] ?? $movimientoAnterior->NumDocto,
                'idusuario' => Auth::id(),
                'documento_sustentatorio' => $validated['documento_sustentatorio'] ?? $movimientoAnterior->documento_sustentatorio
            ]);

            // 9️⃣ MARCAR EL MOVIMIENTO DE BAJA COMO REVERTIDO
            $ultimoMovimiento->update([
                'revertido' => true,
                'revertido_por' => Auth::id(),
                'fecha_reversion' => $fechaReversion,
                'movimiento_reversion_id' => $nuevoMovimiento->id_movimiento
            ]);

            DB::commit();

            // 🔟 LOG DE AUDITORÍA
            Log::info("✅ REVERSIÓN DE BAJA EJECUTADA (CLONÓ MOVIMIENTO ANTERIOR)", [
                'movimiento_baja' => $ultimoMovimiento->id_movimiento,
                'movimiento_anterior_clonado' => $movimientoAnterior->id_movimiento,
                'nuevo_movimiento' => $nuevoMovimiento->id_movimiento,
                'tipo_restaurado' => $movimientoAnterior->tipoMovimiento->tipo_mvto,
                'admin' => Auth::user()->name,
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
                        'revertidopor' => Auth::user()->name,
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
