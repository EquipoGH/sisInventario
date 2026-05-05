<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Bien;
use App\Models\Baja;
use App\Models\TipoMvto;
use App\Models\User;
use App\Models\Ubicacion;
use App\Models\EstadoBien;
use App\Models\EstadoConservacion;
use App\Models\DocumentoSustento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PDF;
use Illuminate\Validation\ValidationException;
use App\Helpers\PermisosHelper;



class MovimientoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        // ⭐⭐⭐ TOTAL DE MOVIMIENTOS FILTRADO POR ÁREA ⭐⭐⭐
        $user = Auth::user();
        $idsAreas = null;
        if ($user && $user->esAdmin()) {
            $total = PermisosHelper::getMovimientosQuery()->count();
        } else {
            $idsAreas = $user ? $user->getIdsAreasPermitidas() : [];
            $totalQuery = PermisosHelper::getMovimientosQuery();
            if (empty($idsAreas)) {
                // Usuario sin áreas asignadas: no ver resultados
                $totalQuery->whereRaw('0 = 1');
            } else {
                $totalQuery->whereHas('ubicacion', function ($q) use ($idsAreas) {
                    $q->whereIn('idarea', $idsAreas);
                });
            }
            $total = $totalQuery->count();
        }


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

        // ✅ APLICAR FILTRO DE VISTA A ESTADÍSTICAS
        $vista = $request->get('vista', 'activos');

        if ($vista === 'activos') {
            // ✅ INCLUIR: ASIGNACIÓN + ALTA + REGISTRO + SIN ASIGNAR (todos los tipos de bien activo)
            // EXCLUIR: solo BAJA
            $tiposBaja = TipoMvto::whereRaw("LOWER(tipo_mvto) LIKE '%baja%'")
                ->pluck('id_tipo_mvto');

            if ($tiposBaja->isNotEmpty()) {
                $queryEstadisticas->whereNotIn('m1.tipo_mvto', $tiposBaja);
            }
        }

        // ✅ APLICAR FILTRO DE TIPO DE MOVIMIENTO BD A ESTADÍSTICAS
        if ($request->filled('tipo_mvto')) {
            $queryEstadisticas->where('m1.tipo_mvto', $request->tipo_mvto);
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

        // Aplicar restricción de áreas según usuario (si no es ADMIN)
        if (!($user && $user->esAdmin())) {
            if (empty($idsAreas)) {
                $queryEstadisticas->whereRaw('0 = 1');
            } else {
                $queryEstadisticas->whereExists(function ($q) use ($idsAreas) {
                    $q->select(DB::raw(1))
                        ->from('ubicacion as u')
                        ->whereRaw('u.id_ubicacion = m1.idubicacion')
                        ->whereIn('u.idarea', $idsAreas);
                });
            }
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

            // Aplicar filtro por áreas del usuario si no es ADMIN
            if (!($user && $user->esAdmin())) {
                if (empty($idsAreas)) {
                    $query->whereRaw('0 = 1');
                } else {
                    $query->whereHas('ubicacion', function ($q) use ($idsAreas) {
                        $q->whereIn('idarea', $idsAreas);
                    });
                }
            }

        // ⭐⭐⭐ RESTRICCIÓN USUARIO: MOSTRAR SOLO EL ÚLTIMO MOVIMIENTO (VIGENTE O ANULADO) POR BIEN ⭐⭐⭐
        // Esto evita mostrar el historial completo en la grilla y confundir al operador.
        // ✅ CORRECCIÓN CRÍTICA: La subconsulta debe respetar si estamos viendo activos o anulados
        // para que, si se anula el movimiento #10, el sistema automáticamente tome el #9 como el "actual".
        $query->whereIn('movimiento.id_movimiento', function($q) use ($mostrarAnulados) {
            $q->select(\Illuminate\Support\Facades\DB::raw('MAX(id_movimiento)'))
              ->from('movimiento')
              ->where('anulado', $mostrarAnulados ? true : false)
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
            $searchLower = strtolower($search);
            $query->where(function($q) use ($search, $searchLower) {
                $q->where('id_movimiento', 'LIKE', "%{$search}%")
                  ->orWhereRaw('LOWER(detalle_tecnico) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(NumDocto) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereHas('bien', function($q) use ($searchLower) {
                      $q->whereRaw('LOWER(codigo_patrimonial) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereRaw('LOWER(denominacion_bien) LIKE ?', ["%{$searchLower}%"]);
                  })
                  ->orWhereHas('tipoMovimiento', function($q) use ($searchLower) {
                      $q->whereRaw('LOWER(tipo_mvto) LIKE ?', ["%{$searchLower}%"]);
                  })
                  ->orWhereHas('usuario', function($q) use ($searchLower) {
                      $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
                  })
                  ->orWhereHas('documentoSustento', function($q) use ($searchLower) {
                      $q->whereRaw('LOWER(numero_documento) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereRaw('LOWER(tipo_documento) LIKE ?', ["%{$searchLower}%"]);
                  });
            });
        }

        // 📊 FILTROS ADICIONALES
        // ✅ FILTRO DE VISTA (ACTIVOS vs TODOS)
        $vista = $request->get('vista', 'activos'); // Por defecto 'activos'

        if ($vista === 'activos') {
            // ✅ VISTA "ACTIVOS": Mostrar todos los bienes activos (ALTA, ASIGNACIÓN, etc.)
            // EXCLUIR solo los bienes que están de BAJA
            $tiposBaja = TipoMvto::whereRaw("LOWER(tipo_mvto) LIKE '%baja%'")
                ->pluck('id_tipo_mvto');

            if ($tiposBaja->isNotEmpty()) {
                $query->whereNotIn('tipo_mvto', $tiposBaja);
            }

            // ⭐ Si el bien tiene un movimiento de ASIGNACIÓN vigente,
            // mostrar ese (el más reciente ya está garantizado por el whereIn de MAX id_movimiento)
            // Esta lógica ya está cubierta por la subconsulta MAX(id_movimiento) de arriba.
        }

        // ✅ FILTRO DE TIPO DE MOVIMIENTO BD
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
        $tiposMovimiento     = TipoMvto::orderBy('tipo_mvto')->get();
        $bienes              = Bien::with('tipoBien')->orderBy('codigo_patrimonial')->get();
        $usuarios            = User::orderBy('name')->get();
        $ubicaciones         = Ubicacion::with('area')->orderBy('nombre_sede')->get();
        // ✅ CORREGIDO: usar EstadoConservacion (condición física)
        $estadosConservacion = EstadoConservacion::orderBy('nombre_conservacion')->get();
        $documentos          = DocumentoSustento::orderBy('fecha_documento', 'desc')->get();
        $areas               = \App\Models\Area::orderBy('nombre_area')->get();

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
                        'id_estado_conservacion' => $movimiento->estadoConservacion->id_estado_conservacion,
                        'nombre_conservacion'    => $movimiento->estadoConservacion->nombre_conservacion
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
        // ⭐ VALIDAR PERMISO
        if (!Auth::user()->esAdmin() && strtoupper(Auth::user()->rol_usuario) !== 'INFORMATICA') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para registrar movimientos'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'idbien' => 'required|exists:bien,id_bien',
                'tipo_mvto' => 'required|exists:tipo_mvto,id_tipo_mvto',
                'fecha_mvto' => 'required|date',
                'detalle_tecnico' => 'nullable|string|max:500',
                'idubicacion' => 'nullable|exists:ubicacion,id_ubicacion',
                'id_estado_conservacion_bien' => 'nullable|exists:estado_conservacion,id_estado_conservacion',
                'documento_sustentatorio' => 'nullable|exists:documento_sustento,id_documento',
                'NumDocto' => 'nullable|string|max:20'
            ]);

            $validated['idusuario'] = Auth::id();

            $bien = Bien::findOrFail($validated['idbien']);

            // ⭐ VALIDACIÓN: NO MOVER SI ESTÁ EN INVENTARIO ACTIVO
            if ($bien->estaEnInventarioActivo()) {
                $tipoMvto = TipoMvto::find($validated['tipo_mvto']);
                $tipoNombre = strtoupper($tipoMvto->tipo_mvto ?? '');

                // Permitir si es una regularización (usualmente hecha por el sistema, pero por si acaso)
                $esRegularizacion = str_contains($tipoNombre, 'REGULARIZA');

                if (!$esRegularizacion) {
                    $inventarios = $bien->getInventariosActivos()->pluck('codigoinventario')->implode(', ');
                    return response()->json([
                        'success' => false,
                        'message' => "No se puede registrar el movimiento porque el bien forma parte de los siguientes inventarios EN PROCESO: {$inventarios}. Finalice o anule los inventarios primero."
                    ], 422);
                }
            }

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
                        $q->whereRaw("LOWER(tipo_mvto) LIKE '%baja%'");
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

                // ✅ ASIGNAR ESTADO CONSERVACIÓN "BUENO" SI NO TIENE
                if (empty($validated['id_estado_conservacion_bien'])) {
                    $estadoBueno = EstadoConservacion::whereRaw('UPPER(TRIM(nombre_conservacion)) IN (?)', ['BUENO'])
                        ->orWhereRaw('UPPER(TRIM(nombre_conservacion)) LIKE ?', ['%BUENO%'])
                        ->first();

                    if ($estadoBueno) {
                        $validated['id_estado_conservacion_bien'] = $estadoBueno->id_estado_conservacion;
                        \Log::info("✅ REGISTRO - Estado conservación asignado: {$estadoBueno->nombre_conservacion}");
                    }
                }

                // ✅ ACTUALIZAR ESTADO ADMINISTRATIVO DEL BIEN → ACTIVO
                $bien = Bien::find($validated['idbien']);
                if ($bien) {
                    $idActivo = EstadoBien::obtenerIdPorNombreNullable(EstadoBien::ACTIVO);
                    if ($idActivo) {
                        $bien->forceFill(['id_estado_bien' => $idActivo, 'activo' => true])->save();
                    }
                }
            }

            // ⭐⭐⭐ LÓGICA PARA BAJA ⭐⭐⭐
            if ($esBaja) {
                // ✅ 1. OBTENER ÚLTIMA ASIGNACIÓN DEL BIEN
                $ultimaAsignacion = Movimiento::where('idbien', $validated['idbien'])
                    ->whereHas('tipoMovimiento', function($q) {
                        $q->whereRaw("LOWER(tipo_mvto) LIKE '%asignaci%'");
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

                // ✅ 3. ASIGNAR ESTADO CONSERVACIÓN "MALO" (condición física en la baja)
                $estadoMalo = EstadoConservacion::whereRaw('UPPER(TRIM(nombre_conservacion)) LIKE ?', ['%MALO%'])
                    ->orWhereRaw('UPPER(TRIM(nombre_conservacion)) LIKE ?', ['%DETERIORA%'])
                    ->first();

                if ($estadoMalo) {
                    $validated['id_estado_conservacion_bien'] = $estadoMalo->id_estado_conservacion;
                    \Log::info("✅ BAJA - Estado conservación: {$estadoMalo->nombre_conservacion}");
                } else {
                    \Log::warning("⚠️ BAJA - No se encontró estado conservación 'MALO' en la BD");
                }

                // ✅ 4. ACTUALIZAR ESTADO ADMINISTRATIVO DEL BIEN → BAJA
                $bien = Bien::find($validated['idbien']);
                if ($bien) {
                    $idEstadoBaja = EstadoBien::obtenerIdPorNombreNullable(EstadoBien::BAJA);
                    $bien->forceFill([
                        'activo'         => false,
                        'eliminado_en'   => now(),
                        'id_estado_bien' => $idEstadoBaja,
                    ])->save();

                    // ✅ 5. CREAR REGISTRO EN TABLA BAJA (si no existe ya)
                    if (!$bien->baja()->exists()) {
                        Baja::create([
                            'id_bien'     => $bien->id_bien,
                            'fecha_baja'  => now()->toDateString(),
                            'motivo_baja' => $validated['detalle_tecnico'] ?? 'Baja registrada desde módulo de movimientos',
                            'resolucion'  => null,
                            'observacion' => 'Generado automáticamente al registrar movimiento de BAJA',
                        ]);
                        \Log::info("✅ BAJA - Registro formal creado en tabla baja para bien #{$bien->id_bien}");
                    }
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
                $q->whereRaw("LOWER(nombre_sede) LIKE '%abastecimiento%'")
                ->orWhereRaw("LOWER(nombre_sede) LIKE '%almacen%'")
                ->orWhereRaw("LOWER(nombre_sede) LIKE '%almacen%'")
                ->orWhereRaw("LOWER(nombre_sede) LIKE '%deposito%'")
                ->orWhereRaw("LOWER(nombre_sede) LIKE '%deposito%'");
            })
            ->orWhereHas('area', function($q) {
                $q->whereRaw("LOWER(nombre_area) LIKE '%abastecimiento%'")
                ->orWhereRaw("LOWER(nombre_area) LIKE '%almacen%'")
                ->orWhereRaw("LOWER(nombre_area) LIKE '%logistica%'")
                ->orWhereRaw("LOWER(nombre_area) LIKE '%patrimonio%'")
                ->orWhereRaw("LOWER(nombre_area) LIKE '%bodega%'");
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
        // ⭐ VALIDAR PERMISO
        if (!Auth::user()->esAdmin() && strtoupper(Auth::user()->rol_usuario) !== 'INFORMATICA') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar movimientos'
            ], 403);
        }

        $movimiento->load([
            'bien.tipoBien',
            'tipoMovimiento',
            'usuario',
            'ubicacion.area',
            'estadoConservacion',
            'documentoSustento'
        ]);

        $tiposMovimiento     = TipoMvto::orderBy('tipo_mvto')->get();
        $bienes              = Bien::with('tipoBien')->orderBy('codigo_patrimonial')->get();
        $ubicaciones         = Ubicacion::with('area')->orderBy('nombre_sede')->get();
        // ✅ CORREGIDO: usar EstadoConservacion
        $estadosConservacion = EstadoConservacion::orderBy('nombre_conservacion')->get();
        $documentos          = DocumentoSustento::orderBy('fecha_documento', 'desc')->get();

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
        // ⭐ VALIDAR PERMISO
        if (!Auth::user()->esAdmin() && strtoupper(Auth::user()->rol_usuario) !== 'INFORMATICA') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar movimientos'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'idbien' => 'required|exists:bien,id_bien',
                'tipo_mvto' => 'required|exists:tipo_mvto,id_tipo_mvto',
                'fecha_mvto' => 'required|date',
                'detalle_tecnico' => 'nullable|string|max:500',
                'idubicacion' => 'nullable|exists:ubicacion,id_ubicacion',
                'id_estado_conservacion_bien' => 'nullable|exists:estado_conservacion,id_estado_conservacion',
                'documento_sustentatorio' => 'nullable|exists:documento_sustento,id_documento',
                'NumDocto' => 'nullable|string|max:20'
            ]);

            // ⭐ VALIDACIÓN: NO ACTUALIZAR SI ESTÁ EN INVENTARIO ACTIVO
            $bien = Bien::find($validated['idbien'] ?? $movimiento->idbien);
            if ($bien && $bien->estaEnInventarioActivo()) {
                $inventarios = $bien->getInventariosActivos()->pluck('codigoinventario')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => "No se puede actualizar el movimiento porque el bien forma parte de una auditoría activa ({$inventarios})."
                ], 422);
            }

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
                return response()->json(['success' => false, 'message' => 'El movimiento ya se encuentra anulado.'], 400);
            }

            // ⭐ VALIDACIÓN: NO ANULAR SI ESTÁ EN INVENTARIO ACTIVO
            $bien = $movimiento->bien;
            if ($bien && $bien->estaEnInventarioActivo()) {
                $inventarios = $bien->getInventariosActivos()->pluck('codigoinventario')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => "No se puede anular el movimiento porque el bien forma parte de una auditoría activa ({$inventarios})."
                ], 422);
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
            $omitidos = [];

            foreach ($validated['movimientos_ids'] as $id) {
                $movimiento = Movimiento::find($id);

                // ⭐ VALIDACIÓN: NO ANULAR SI ESTÁ EN INVENTARIO ACTIVO
                $bien = $movimiento ? $movimiento->bien : null;
                if ($bien && $bien->estaEnInventarioActivo()) {
                    $inventarios = $bien->getInventariosActivos()->pluck('codigoinventario')->implode(', ');
                    $omitidos[] = [
                        'id' => $id,
                        'codigo' => $bien->codigo_patrimonial,
                        'motivo' => "Bloqueado por Inventario: {$inventarios}"
                    ];
                    continue;
                }

                if ($movimiento && !$movimiento->anulado) {

                    // ⭐⭐⭐ REGLA DE NEGOCIO: No se puede anular ALTA ni BAJA ⭐⭐⭐
                    $tipoNombre = strtoupper($movimiento->tipoMovimiento->tipo_mvto ?? '');
                    if (str_contains($tipoNombre, 'REGISTRO') || str_contains($tipoNombre, 'ALTA')) {
                        throw new \Exception("El movimiento #{$movimiento->id_movimiento} es el ALTA inicial del bien y no puede ser anulado.");
                    }
                    if (str_contains($tipoNombre, 'BAJA')) {
                        throw new \Exception("El movimiento #{$movimiento->id_movimiento} es una BAJA patrimonial y no puede ser anulado directamente.");
                    }

                    $movimiento->update([
                        'anulado' => true,
                        'anulado_por' => Auth::id(),
                        'fecha_anulacion' => now(),
                        'motivo_anulacion' => $validated['motivo_anulacion']
                    ]);

                    // ✅ SINCRONIZAR BIEN CON MOVIMIENTO ANTERIOR
                    // Al anular este movimiento, buscamos cuál era el movimiento válido anterior
                    $movimientoAnterior = Movimiento::where('idbien', $movimiento->idbien)
                        ->where('anulado', false)
                        ->orderBy('fecha_mvto', 'desc')
                        ->orderBy('id_movimiento', 'desc')
                        ->first();

                    if ($movimientoAnterior) {
                        $bien = Bien::find($movimiento->idbien);
                        if ($bien) {
                            // Si el movimiento anterior tenía un estado de conservación, lo heredamos
                            if ($movimientoAnterior->id_estado_conservacion_bien) {
                                $bien->id_estado_conservacion = $movimientoAnterior->id_estado_conservacion_bien;
                                $bien->saveQuietly();
                            }
                        }
                    }

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
                'ya_anulados' => $yaAnulados,
                'omitidos' => $omitidos
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
        // ⭐ VALIDAR PERMISO
        if (!Auth::user()->esAdmin() && strtoupper(Auth::user()->rol_usuario) !== 'INFORMATICA') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para realizar asignaciones masivas'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'bienes_ids' => 'required|array|min:1',
                'bienes_ids.*' => 'exists:bien,id_bien',
                'fecha_mvto' => 'required|date',
                'detalle_tecnico' => 'nullable|string|max:500',
                'idubicacion' => 'nullable|exists:ubicacion,id_ubicacion',
                'id_estado_conservacion_bien' => 'nullable|exists:estado_conservacion,id_estado_conservacion',
                'documento_sustentatorio' => 'nullable|exists:documento_sustento,id_documento',
                'NumDocto' => 'nullable|string|max:20'
            ]);

            // ⭐ FORZAR TIPO DE MOVIMIENTO A "ASIGNACIÓN"
            $tipoAsignacion = TipoMvto::whereRaw("LOWER(tipo_mvto) LIKE '%asignaci%'")->first();

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

                // ⭐⭐⭐ VALIDACIÓN: NO MOVER SI ESTÁ EN INVENTARIO ACTIVO ⭐⭐⭐
                if ($bien->estaEnInventarioActivo()) {
                    $inventarios = $bien->getInventariosActivos()->pluck('codigoinventario')->implode(', ');
                    $bienesOmitidos[] = [
                        'idbien' => $bienId,
                        'codigo' => $bien->codigo_patrimonial,
                        'denominacion' => $bien->denominacion_bien,
                        'motivo' => "En Inventario Activo: {$inventarios}"
                    ];
                    continue;
                }

                // ⭐⭐⭐ VALIDAR QUE EL BIEN NO ESTÉ DE BAJA ⭐⭐⭐
                $ultimaBaja = Movimiento::where('idbien', $bienId)
                    ->whereHas('tipoMovimiento', function($q) {
                        $q->whereRaw("LOWER(tipo_mvto) LIKE '%baja%'");
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
            $totalCreados = count($movimientosCreados);
            $totalOmitidos = count($bienesOmitidos);

            $mensaje = "{$totalCreados} movimiento(s) de ASIGNACIÓN creado(s).";
            if ($totalOmitidos > 0) {
                $mensaje .= " {$totalOmitidos} bien(es) omitido(s) por restricciones de seguridad (Baja o Inventario).";
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
            $tipoBaja = TipoMvto::whereRaw("LOWER(tipo_mvto) LIKE '%baja%'")->first();

            if (!$tipoBaja) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe el tipo de movimiento BAJA en el sistema'
                ], 400);
            }

            // ⭐ OBTENER ESTADO "MALO"
            $estadoMalo = EstadoConservacion::whereRaw("LOWER(nombre_conservacion) LIKE '%malo%'")
                ->orWhereRaw("LOWER(nombre_conservacion) LIKE '%inoperativo%'")
                ->orWhereRaw("LOWER(nombre_conservacion) LIKE '%dañado%'")
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
                        $q->whereRaw("LOWER(tipo_mvto) LIKE '%asignaci%'");
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
                    'id_estado_conservacion_bien' => $estadoMalo->id_estado_conservacion, // ✅ FORZADO A MALO
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
                    'estado' => $estadoMalo->nombre_conservacion
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
                'estado_aplicado' => $estadoMalo->nombre_conservacion,
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
                'estado_aplicado' => $estadoMalo->nombre_conservacion
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
        // ⭐ VALIDAR PERMISO
        if (!Auth::user()->esAdmin() && strtoupper(Auth::user()->rol_usuario) !== 'INFORMATICA') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para registrar movimientos masivos'
            ], 403);
        }

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
                'id_estado_conservacion_bien' => 'nullable|exists:estado_conservacion,id_estado_conservacion',
                'documento_sustentatorio' => 'nullable|exists:documento_sustento,id_documento',
                'NumDocto' => 'nullable|string|max:20'
            ]);

            $movimientosCreados = [];
            $omitidos = [];
            $usuarioId = Auth::id();

            foreach ($bienesIds as $bienId) {
                $bien = Bien::find($bienId);

                if (!$bien) continue;

                // ⭐ VALIDACIÓN: NO MOVER SI ESTÁ EN INVENTARIO ACTIVO
                if ($bien->estaEnInventarioActivo()) {
                    $inventarios = $bien->getInventariosActivos()->pluck('codigoinventario')->implode(', ');
                    $omitidos[] = [
                        'id' => $bienId,
                        'codigo' => $bien->codigo_patrimonial,
                        'motivo' => "En Auditoría: {$inventarios}"
                    ];
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
     * ⛔ INHABILITADO — Directiva N° 001-2015/SBN
     * La reversión de baja no está permitida en la norma legal.
     */
    public function revertirBaja(Request $request, $bienId)
    {
        return response()->json(['success' => false, 'message' => 'La reversión de bajas no está permitida según la Directiva N° 001-2015/SBN.'], 410);
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
