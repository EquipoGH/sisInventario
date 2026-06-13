<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\DetalleInventario;
use App\Models\Bien;
use App\Models\Movimiento;
use App\Models\EstadoConservacion;
use App\Models\Responsable;
use App\Models\Ubicacion;
use App\Models\TipoMvto;
use App\Models\User;
use App\Http\Requests\InventarioRequest;
use App\Http\Requests\DetalleInventarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class InventarioController extends Controller
{
    // ==================== INDEX ====================

    /**
     * Listado principal de inventarios con búsqueda, filtros y paginación.
     */
    public function index(Request $request)
    {
        $search  = $request->get('search', '');
        $perPage = (int) $request->get('per_page', 10);

        // Total global sin filtros
        $total = Inventario::count();

        $query = Inventario::with(['responsablePersona', 'usuarioRegistro', 'usuarioCierre']);

        // 🔍 BÚSQUEDA
        if (!empty($search)) {
            $query->buscar($search);
        }

        // FILTRO: Estado
        if ($request->filled('estado') && $request->estado !== 'ALL') {
            $query->where('estadoinventario', $request->estado);
        }

        // FILTRO: Tipo
        if ($request->filled('tipo')) {
            $query->where('tipoinventario', $request->tipo);
        }

        // FILTRO: Fecha desde
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        // FILTRO: Fecha hasta
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_inicio', '<=', $request->fecha_hasta);
        }

        // 📊 ORDENAMIENTO DINÁMICO
        $columna   = $request->get('orden', 'fecha');
        $direccion = $request->get('direccion', 'desc');

        $columnasPermitidas = [
            'id'     => 'id_inventario',
            'codigo' => 'codigoinventario',
            'fecha'  => 'fecha_inicio',
            'tipo'   => 'tipoinventario',
            'estado' => 'estadoinventario',
        ];

        $columnaReal = $columnasPermitidas[$columna] ?? 'fecha_inicio';
        $direccion   = in_array(strtolower($direccion), ['asc', 'desc']) ? strtolower($direccion) : 'desc';

        $query->orderBy($columnaReal, $direccion);

        // 📄 PAGINACIÓN
        $inventarios = $query->with('responsablePersona', 'usuarioRegistro')
                             ->withCount('detalles')
                             ->paginate($perPage);

        // Estadísticas rápidas
        $estadisticas = [
            'total'      => $total,
            'pendiente'  => Inventario::where('estadoinventario', 'pendiente')->count(),
            'en_proceso' => Inventario::where('estadoinventario', 'en_proceso')->count(),
            'cerrado'    => Inventario::where('estadoinventario', 'cerrado')->count(),
        ];

        // 📤 RESPUESTA AJAX
        if ($request->ajax()) {
            $data = $inventarios->getCollection()->map(fn ($inv) => $this->formatInventario($inv));

            return response()->json([
                'success'      => true,
                'data'         => $data,
                'total'        => $total,
                'resultados'   => $inventarios->total(),
                'current_page' => $inventarios->currentPage(),
                'last_page'    => $inventarios->lastPage(),
                'per_page'     => $inventarios->perPage(),
                'from'         => $inventarios->firstItem(),
                'to'           => $inventarios->lastItem(),
                'estadisticas' => $estadisticas,
            ]);
        }

        // Datos para filtros y alcances
        $responsables       = Responsable::orderBy('apellidos_responsable')->get();
        $estadosConservacion = EstadoConservacion::orderBy('nombre_conservacion')->get();
        $areas              = \App\Models\Area::orderBy('nombre_area')->get();
        $ubicaciones        = Ubicacion::orderBy('ambiente')->get();

        return view('inventario.index', compact(
            'inventarios',
            'total',
            'estadisticas',
            'responsables',
            'estadosConservacion',
            'areas',
            'ubicaciones'
        ));
    }
    
    /**
     * API: Estima cuántos bienes entrarán en un alcance antes de crear el inventario.
     * Útil para feedback en tiempo real en el modal de creación.
     */
    public function estimarAlcance(Request $request)
    {
        $tipo   = $request->get('alcance_tipo', 'responsable');
        $idArea = $request->get('id_area');
        $idUbic = $request->get('id_ubicacion');
        $dni    = $request->get('responsable');

        // Mock de un objeto inventario para reutilizar la lógica del modelo
        $inv = new Inventario();
        $inv->tipoinventario = 'Estimación';
        
        // Inyectar el tag en la observación (simulado)
        $tag = '';
        if ($tipo === 'ubicacion' && $idUbic) $tag = "[ALCANCE_UBICACION:{$idUbic}]";
        elseif ($tipo === 'area' && $idArea) $tag = "[ALCANCE_AREA:{$idArea}]";
        elseif ($tipo === 'general') $tag = "[ALCANCE_GENERAL]";
        
        $inv->observacion = $tag;
        $inv->responsable = $dni;

        $count = count($inv->getBienesEsperadosIds());

        return response()->json([
            'success' => true,
            'total' => $count,
            'mensaje' => $count > 0 ? "Se incluirán {$count} bienes en este inventario." : "No se encontraron bienes para este alcance."
        ]);
    }

    /**
     * Vista de creación (maneja accesos directos por URL)
     */
    public function create()
    {
        if (!Auth::user()->esAdmin()) {
            return redirect()->route('inventario.index')->with('error', 'No tiene permisos para realizar esta acción.');
        }

        $hayActivo = Inventario::whereIn('estadoinventario', [Inventario::ESTADO_PENDIENTE, Inventario::ESTADO_EN_PROCESO])->first();
        if ($hayActivo) {
            return redirect()->route('inventario.index')->with('error', 'No se puede crear un nuevo inventario mientras exista uno activo (' . $hayActivo->codigoinventario . ').');
        }

        return redirect()->route('inventario.index'); // Todo se maneja vía modal en el index
    }


    // ==================== STORE ====================

    public function store(InventarioRequest $request)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['usuarioregistro'] = Auth::id();
            $data['estadoinventario'] = $data['estadoinventario'] ?? 'pendiente';

            // 🛑 RESTRICCIÓN DE SEGURIDAD: No permitir más de un inventario activo
            $hayActivo = Inventario::whereIn('estadoinventario', [Inventario::ESTADO_PENDIENTE, Inventario::ESTADO_EN_PROCESO])->first();
            if ($hayActivo) {
                return response()->json([
                    'success' => false, 
                    'message' => 'SEGURIDAD: No se puede crear un nuevo inventario mientras exista uno PENDIENTE o EN PROCESO (Activo: ' . $hayActivo->codigoinventario . '). Finalice o anule el actual primero.'
                ], 422);
            }

            // Generar código automático si no viene
            if (empty($data['codigoinventario'])) {
                $ultimo = Inventario::max('id_inventario') ?? 0;
                $data['codigoinventario'] = 'INV-' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
            }

            // --- MANEJO DE ALCANCE SIN MIGRACIONES ---
            $alcanceTipo = $request->input('alcance_tipo', 'responsable'); // 'responsable', 'ubicacion', 'area', 'general'
            $idArea      = $request->input('id_area');
            $idUbic      = $request->input('id_ubicacion');
            $tagAlcance  = '';

            if ($alcanceTipo === 'ubicacion' && $idUbic) {
                $tagAlcance = "[ALCANCE_UBICACION:{$idUbic}]";
            } elseif ($alcanceTipo === 'area' && $idArea) {
                $tagAlcance = "[ALCANCE_AREA:{$idArea}]";
            } elseif ($alcanceTipo === 'general') {
                $tagAlcance = "[ALCANCE_GENERAL]";
            }

            if ($tagAlcance) {
                $data['observacion'] = $tagAlcance . ' ' . ($data['observacion'] ?? '');
            }

            $inventario = Inventario::create($data);
            $inventario->load(['responsablePersona', 'usuarioRegistro']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventario registrado exitosamente.',
                'data'    => $this->formatInventario($inventario),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear inventario: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al registrar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Fuerza la regeneración del snapshot si el inventario está en proceso pero vacío.
     */
    public function regenerarSnapshot(Inventario $inventario)
    {
        // Eliminamos la restricción bloqueante para permitir "actualizar" el snapshot
        // incluso si ya hay avances.
        
        $procesados = $this->generarSnapshot($inventario);
        $inventario->refresh();

        return response()->json([
            'success' => true,
            'message' => "Se han sincronizado {$procesados} nuevos bienes a la lista oficial.",
            'data'    => $this->formatInventario($inventario, true)
        ]);
    }

    public function show(Inventario $inventario)
    {
        $inventario->load([
            'responsablePersona',
            'usuarioRegistro',
            'usuarioCierre',
            'detalles.movimiento.bien.tipoBien',
            'detalles.movimiento.ubicacion.area',
            'detalles.estadoConservacion',
            'detalles.ubicacionDetectada.area',
            'detalles.usuarioVerificador',
        ]);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'data' => $this->formatInventario($inventario, true)]);
        }

        $movimientos         = Movimiento::with(['bien.tipoBien', 'ubicacion.area'])->activos()->get();
        $estadosConservacion = EstadoConservacion::orderBy('nombre_conservacion')->get();
        $ubicaciones         = Ubicacion::with('area')->orderBy('nombre_sede')->get();
        $areas               = \App\Models\Area::orderBy('nombre_area')->get();
        $usuarios            = User::orderBy('name')->get();

        $estadisticas_conciliacion = $inventario->getEstadisticasConciliacion();

        return view('inventario.show', compact(
            'inventario',
            'movimientos',
            'estadosConservacion',
            'ubicaciones',
            'areas',
            'usuarios',
            'estadisticas_conciliacion'
        ));

    }

    // ==================== EDIT ====================

    public function edit(Inventario $inventario)
    {
        $inventario->load(['responsablePersona', 'usuarioRegistro']);

        return response()->json([
            'success' => true,
            'data'    => $this->formatInventario($inventario),
        ]);
    }

    // ==================== UPDATE ====================

    public function update(InventarioRequest $request, Inventario $inventario)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }

        if (!$inventario->puedeEditarse()) {
            return response()->json(['success' => false, 'message' => 'No se puede editar un inventario cerrado o anulado.'], 422);
        }

        try {
            $data = $request->validated();
            $inventario->update($data);
            $inventario->refresh()->load(['responsablePersona', 'usuarioRegistro']);

            return response()->json([
                'success' => true,
                'message' => 'Inventario actualizado exitosamente.',
                'data'    => $this->formatInventario($inventario),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar inventario: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    // ==================== DESTROY ====================

    public function destroy(Inventario $inventario)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }

        if ($inventario->estaCerrado()) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar un inventario cerrado.'], 422);
        }

        // Recomendación Profesional: No eliminar inventarios que ya tienen bienes registrados
        // para no perder el historial de auditoría. Es mejor anularlos.
        if ($inventario->detalles()->count() > 0) {
            return response()->json([
                'success' => false, 
                'message' => 'No se puede eliminar porque ya tiene bienes registrados. Por integridad y auditoría, se recomienda cambiar su estado a "Anulado".'
            ], 422);
        }

        try {
            $inventario->detalles()->delete();
            $inventario->delete();
            return response()->json(['success' => true, 'message' => 'Inventario eliminado exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar. Puede estar en uso.'], 500);
        }
    }

    // ==================== CAMBIAR ESTADO ====================

    public function cambiarEstado(Request $request, Inventario $inventario)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede cambiar el estado.'], 403);
        }

        $request->validate([
            'estadoinventario' => 'required|in:pendiente,en_proceso,cerrado,anulado',
        ]);

        $nuevo = $request->estadoinventario;

        $data = ['estadoinventario' => $nuevo];

        if ($nuevo === 'cerrado') {
            // Nota: Bajo la nueva lógica de conciliación (snapshots), los bienes en estado 'pendiente' 
            // son legítimamente los "Bienes Faltantes" (no ubicados). 
            // Por lo tanto, SI SE PERMITE cerrar el inventario dejando bienes pendientes.

            $data['usuariocierre'] = Auth::id();
            $data['fechacierre']   = now();
            if (!$inventario->fecha_fin) {
                $data['fecha_fin'] = now()->toDateString();
            }
        }

        $viejoEstado = strtolower($inventario->getRawOriginal('estadoinventario') ?? '');
        $inventario->update($data);
        $inventario->refresh();

        // ⭐⭐⭐ GENERAR SNAPSHOT (PRECARTGA DE BIENES) ⭐⭐⭐
        // Si pasa de 'pendiente' a 'en_proceso', pre-poblamos el detalle
        if ($viejoEstado === 'pendiente' && $nuevo === 'en_proceso') {
            $this->generarSnapshot($inventario);
        }

        // Si se anula el inventario, podríamos anular también incidencias pendientes si fuera necesario
        // por ahora solo retornamos
        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado a "' . ucfirst($nuevo) . '".',
            'data'    => $this->formatInventario($inventario),
        ]);
    }

    /**
    * Descargar acta PDF del inventario (solo si está cerrado)
    */
    public function downloadActa(Inventario $inventario)
    {
        // Verificar estado (usar getRawOriginal para evitar el accessor ucfirst del modelo)
        if (!$inventario->estaCerrado()) {
            return response()->json(['success' => false, 'message' => 'El acta sólo está disponible para inventarios cerrados.'], 422);
        }

        // Cargar relaciones necesarias para la vista del acta
        $inventario->load([
            'responsablePersona',
            'usuarioRegistro',
            'usuarioCierre',
            'detalles.movimiento.bien.tipoBien',
            'detalles.movimiento.ubicacion.area',
            'detalles.estadoConservacion',
            'detalles.ubicacionDetectada.area',
            'incidencias.bien',
            'incidencias.area',
            'incidencias.ubicacion',
            'incidencias.usuarioRevision',
        ]);

        // Estadísticas de conciliación (incluye bienes_faltantes con info detallada)
        $estadisticas = $inventario->getEstadisticasConciliacion();

        // Enriquecer faltantes con info del Bien para mostrar en el acta
        if (!empty($estadisticas['faltantes_ids'])) {
            $faltantes = \App\Models\Bien::with('tipoBien')
                ->whereIn('id_bien', $estadisticas['faltantes_ids'])
                ->get();
            $estadisticas['bienes_faltantes'] = $faltantes->map(fn($b) => [
                'codigo_patrimonial' => $b->codigo_patrimonial,
                'denominacion_bien'  => $b->denominacion_bien,
                'tipo_bien'          => $b->tipoBien->nombre_tipo ?? '---',
            ])->values()->toArray();
        } else {
            $estadisticas['bienes_faltantes'] = [];
        }

        // Obtener configuración del sistema para el encabezado
        $settings = SystemSetting::pluck('value', 'key')->toArray();

        $data = [
            'inventario'   => $inventario,
            'estadisticas' => $estadisticas,
            'fecha_actual' => now()->format('d/m/Y H:i'),
            'settings'     => $settings,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('inventario.acta', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Acta_Inv_' . $inventario->codigoinventario . '.pdf';
        return $pdf->download($filename);
    }

    /**
    * Descargar Excel consolidado (Verificados, Faltantes, Sobrantes, Incidencias)
    */
    public function downloadExcel(Inventario $inventario)
    {
        $filename = 'Reporte_Inv_' . $inventario->codigoinventario . '_' . date('Ymd_Hi') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InventarioMultiExport($inventario), $filename);
    }

    // ==================== ELIMINAR MÚLTIPLES ====================
    public function eliminarMultiples(Request $request)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:inventario,id_inventario'
        ]);

        $ids = $request->ids;
        $eliminados = 0;
        $errores = 0;

        foreach ($ids as $id) {
            $inv = Inventario::find($id);
            if ($inv && !$inv->estaCerrado()) {
                // Validación estricta: solo si tiene 0 detalles
                if ($inv->detalles()->count() === 0) {
                    $inv->delete();
                    $eliminados++;
                } else {
                    $errores++;
                }
            }
        }

        if ($eliminados > 0 && $errores === 0) {
            return response()->json([
                'success' => true,
                'message' => "$eliminados inventario(s) vacíos eliminados exitosamente."
            ]);
        } elseif ($eliminados > 0 && $errores > 0) {
            return response()->json([
                'success' => true,
                'message' => "$eliminados inventario(s) eliminados. $errores no se pudieron eliminar porque tenían bienes registrados."
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => "No se pudo eliminar ningún inventario. Es probable que ya tengan bienes registrados."
            ], 422);
        }
    }

    // ==================== REGULARIZACIÓN ====================

    /**
     * Formalizar las ubicaciones detectadas en el inventario.
     * Crea movimientos de reasignación para los bienes hallados en lugares distintos.
     */
    public function regularizarUbicaciones(Inventario $inventario)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede regularizar.'], 403);
        }

        if (!$inventario->estaCerrado()) {
            return response()->json(['success' => false, 'message' => 'El inventario debe estar CERRADO y el Acta firmada antes de regularizar.'], 422);
        }

        try {
            DB::beginTransaction();

            $detalles = $inventario->detalles()
                ->whereNotNull('ubicaciondetectada')
                ->with(['movimiento.bien'])
                ->get();

            $procesados = 0;
            $tipoMvto = TipoMvto::whereRaw("LOWER(tipo_mvto) LIKE '%traslado%'")
                ->orWhereRaw("LOWER(tipo_mvto) LIKE '%asignacion%'")
                ->orWhereRaw("LOWER(tipo_mvto) LIKE '%asignación%'")
                ->first();
            $idTipoMvto = $tipoMvto ? $tipoMvto->id_tipo_mvto : 2; // Default to 2 (Asignación) if not found

            foreach ($detalles as $detalle) {
                $originalUbicId = $detalle->movimiento ? $detalle->movimiento->idubicacion : null;
                $detectadaUbicId = $detalle->ubicaciondetectada;

                // Solo si la ubicación es distinta
                if ($originalUbicId && $detectadaUbicId && $originalUbicId != $detectadaUbicId) {
                    
                    // Crear nuevo movimiento formal
                    Movimiento::create([
                        'idbien'                      => $detalle->movimiento->idbien,
                        'idubicacion'                 => $detectadaUbicId,
                        'tipo_mvto'                   => $idTipoMvto,
                        'fecha_mvto'                  => now(),
                        'NumDocto'                    => $inventario->codigoinventario,
                        'detalle_tecnico'             => 'Regularización automática post-inventario. ' . ($detalle->observacion ?? ''),
                        'idusuario'                   => Auth::id(),
                        'id_estado_conservacion_bien' => $detalle->estado_conservacion,
                    ]);

                    $procesados++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Se han formalizado las ubicaciones de $procesados bienes exitosamente.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en regularización: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ==================== DETALLES ====================

    /**
     * Agregar bien al detalle del inventario
     */
    public function agregarDetalle(DetalleInventarioRequest $request, Inventario $inventario)
    {
        if (!$inventario->puedeEditarse()) {
            return response()->json(['success' => false, 'message' => 'El inventario está cerrado o anulado.'], 422);
        }

        try {
            $data = $request->validated();
            $data['id_inventario']      = $inventario->id_inventario;
            $data['usuarioverificador'] = $data['usuarioverificador'] ?? Auth::id();
            $data['fechaverificacion']  = $data['fechaverificacion'] ?? now();

            // Evitar duplicados en el mismo inventario
            $existe = DetalleInventario::where('id_inventario', $inventario->id_inventario)
                ->where('id_movimiento', $data['id_movimiento'])
                ->first();

            if ($existe) {
                if ($existe->getRawOriginal('estadoverificacion') === \App\Models\DetalleInventario::PENDIENTE) {
                    $existe->update([
                        'estadoverificacion'  => $data['estadoverificacion'] ?? \App\Models\DetalleInventario::VERIFICADO,
                        'estado_conservacion' => $data['estado_conservacion'] ?? $existe->movimiento->id_estado_conservacion_bien,
                        'ubicaciondetectada'  => $data['ubicaciondetectada'] ?? null,
                        'usuarioverificador'  => $data['usuarioverificador'],
                        'fechaverificacion'   => $data['fechaverificacion'],
                        'observacion'         => $data['observacion'] ?? null,
                    ]);

                    if ($existe->ubicaciondetectada && $existe->ubicaciondetectada != $existe->movimiento->idubicacion) {
                        $existe->update(['requiereregularizacion' => true]);
                    }

                    if ($inventario->getRawOriginal('estadoinventario') === \App\Models\Inventario::ESTADO_PENDIENTE) {
                        $inventario->update(['estadoinventario' => \App\Models\Inventario::ESTADO_EN_PROCESO]);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Bien verificado exitosamente desde la búsqueda.',
                        'data'    => $this->formatDetalle($existe),
                    ]);
                } else {
                    return response()->json(['success' => false, 'message' => 'Este bien ya está registrado y verificado en el inventario.'], 422);
                }
            }

            DB::beginTransaction();
            $detalle = \App\Models\DetalleInventario::create($data);

            // ⭐ AUTOMÁTICO: Si el inventario está en 'pendiente', pasar a 'en_proceso'
            if ($inventario->getRawOriginal('estadoinventario') === \App\Models\Inventario::ESTADO_PENDIENTE) {
                $inventario->update(['estadoinventario' => \App\Models\Inventario::ESTADO_EN_PROCESO]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bien registrado exitosamente.',
                'data'    => $this->formatDetalle($detalle),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al agregar detalle: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar verificación de un detalle
     */
    public function actualizarDetalle(DetalleInventarioRequest $request, Inventario $inventario, DetalleInventario $detalle)
    {
        if (!$inventario->puedeEditarse()) {
            return response()->json(['success' => false, 'message' => 'El inventario está cerrado o anulado.'], 422);
        }

        try {
            DB::beginTransaction();

            $data = $request->only([
                'estado_conservacion',
                'observacion',
                'estadoverificacion',
                'ubicaciondetectada',
            ]);

            $data['usuarioverificador'] = Auth::id();
            $data['fechaverificacion']  = now();

            $detalle->update($data);
            $detalle->refresh()->load([
                'movimiento.bien.tipoBien',
                'estadoConservacion',
                'ubicacionDetectada.area',
                'usuarioVerificador',
            ]);

            // ==================== RECONCILIACIÓN INTELIGENTE PRO ====================
            // 1. Si el bien ahora es VERIFICADO, resolver automáticamente incidencias de 'faltante'
            if ($detalle->estadoverificacion === \App\Models\DetalleInventario::VERIFICADO) {
                \App\Models\Incidencia::where('id_inventario', $inventario->id_inventario)
                    ->where('id_bien', $detalle->movimiento?->idbien)
                    ->where('tipo_incidencia', 'faltante')
                    ->where('estado', '!=', \App\Models\Incidencia::ESTADO_REVISADO)
                    ->update([
                        'estado' => \App\Models\Incidencia::ESTADO_REVISADO,
                        'resolucion' => 'Resuelto automáticamente: El bien fue verificado físicamente con éxito durante la auditoría.',
                        'fecha_revision' => now(),
                        'id_usuario_revision' => Auth::id()
                    ]);
            }

            // ==================== INTEGRACIÓN PROFESIONAL CON INCIDENCIAS ====================
            // 1. Si el bien es "No Encontrado", reportar incidencia de tipo 'faltante'
            if ($detalle->estadoverificacion === \App\Models\DetalleInventario::NO_ENCONTRADO) {
                \App\Models\Incidencia::updateOrCreate(
                    [
                        'id_inventario' => $inventario->id_inventario,
                        'id_bien'       => $detalle->movimiento?->idbien,
                        'tipo_incidencia' => 'faltante'
                    ],
                    [
                        'id_ubicacion' => $detalle->movimiento?->idubicacion,
                        'id_area'      => $detalle->ubicacionDetectada?->idarea ?? $detalle->movimiento?->ubicacion?->idarea ?? null,
                        'observacion'  => 'Reportado como NO ENCONTRADO durante verificación física. ' . ($detalle->observacion ?? ''),
                        'estado'       => \App\Models\Incidencia::ESTADO_NO_REVISADO
                    ]
                );
            }

            // 2. Si el estado de conservación es MALO o CHATARRA, reportar incidencia de tipo 'deteriorado'
            $estadoMaloId = \App\Models\EstadoConservacion::whereIn('nombre_conservacion', ['Malo', 'Chatarra'])->pluck('id_estado_conservacion')->toArray();
            if (in_array($detalle->estado_conservacion, $estadoMaloId)) {
                \App\Models\Incidencia::updateOrCreate(
                    [
                        'id_inventario'   => $inventario->id_inventario,
                        'id_bien'         => $detalle->movimiento?->idbien,
                        'tipo_incidencia' => 'deteriorado'
                    ],
                    [
                        'id_ubicacion' => $detalle->ubicaciondetectada ?? $detalle->movimiento?->idubicacion,
                        'id_area'      => $detalle->ubicacionDetectada?->idarea ?? $detalle->movimiento?->ubicacion?->idarea ?? null,
                        'observacion'  => 'Reportado con deterioro grave durante inventario. Estado: ' . ($detalle->estadoConservacion?->nombre_conservacion ?? 'Malo') . '. ' . ($detalle->observacion ?? ''),
                        'estado'       => \App\Models\Incidencia::ESTADO_NO_REVISADO
                    ]
                );
            }

            // 3. Si es SOBRANTE (Ubicación distinta a la esperada), marcar que requiere regularización
            if ($detalle->ubicaciondetectada && $detalle->ubicaciondetectada != $detalle->movimiento->idubicacion) {
                $detalle->update(['requiereregularizacion' => true]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Verificación actualizada y sincronizada con incidencias.',
                'data'    => $this->formatDetalle($detalle),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar detalle: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar detalle del inventario
     */
    public function eliminarDetalle(Inventario $inventario, DetalleInventario $detalle)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede eliminar detalles.'], 403);
        }

        if (!$inventario->puedeEditarse()) {
            return response()->json(['success' => false, 'message' => 'El inventario está cerrado o anulado.'], 422);
        }

        $detalle->delete();
        return response()->json(['success' => true, 'message' => 'Bien eliminado del inventario.']);
    }

    /**
     * Verificación masiva de bienes (pasar de faltantes a verificados)
     */
    public function verificarMasivo(Request $request, Inventario $inventario)
    {
        if (!$inventario->puedeEditarse()) {
            return response()->json(['success' => false, 'message' => 'El inventario no permite ediciones.'], 422);
        }

        $request->validate([
            'bienes_ids'          => 'required|array',
            'bienes_ids.*'        => 'integer|exists:bien,id_bien',
            'estado_conservacion' => 'required|exists:estado_conservacion,id_estado_conservacion',
        ]);

        try {
            DB::beginTransaction();

            $bienesIds = $request->bienes_ids;
            $estadoCons = $request->estado_conservacion;
            $procesados = 0;

            foreach ($bienesIds as $idBien) {
                // Obtener el último movimiento vigente del bien
                $mov = Movimiento::where('idbien', $idBien)
                    ->activos()
                    ->orderBy('id_movimiento', 'desc')
                    ->first();

                if ($mov) {
                    // Evitar duplicados
                    $existe = DetalleInventario::where('id_inventario', $inventario->id_inventario)
                        ->where('id_movimiento', $mov->id_movimiento)
                        ->first();

                    if ($existe) {
                        $existe->update([
                            'estado_conservacion' => $estadoCons,
                            'estadoverificacion'  => 'verificado',
                            'usuarioverificador'  => Auth::id(),
                            'fechaverificacion'   => now(),
                        ]);
                    } else {
                        DetalleInventario::create([
                            'id_inventario'       => $inventario->id_inventario,
                            'id_movimiento'       => $mov->id_movimiento,
                            'estado_conservacion' => $estadoCons,
                            'estadoverificacion'  => 'verificado',
                            'usuarioverificador'  => Auth::id(),
                            'fechaverificacion'   => now(),
                        ]);
                    }
                    $procesados++;
                }
            }

            // Cambiar estado a 'en_proceso' si estaba 'pendiente'
            if ($procesados > 0 && $inventario->getRawOriginal('estadoinventario') === Inventario::ESTADO_PENDIENTE) {
                $inventario->update(['estadoinventario' => Inventario::ESTADO_EN_PROCESO]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Se han verificado $procesados bienes exitosamente."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Quitar/Eliminar detalles de verificación de forma masiva.
     */
    public function eliminarDetallesMasivo(Request $request, Inventario $inventario)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede realizar esta acción.'], 403);
        }

        if (!$inventario->puedeEditarse()) {
            return response()->json(['success' => false, 'message' => 'El inventario está cerrado o anulado.'], 422);
        }

        $request->validate([
            'detalles_ids'   => 'required|array',
            'detalles_ids.*' => 'integer|exists:detalle_inventario,id_detalle_inv'
        ]);

        try {
            DB::beginTransaction();

            // Opción PRO implementada: No borrar si es parte del snapshot (solo pasar a pendiente).
            // Si es un bien sobrante (no esperado) agregado manualmente, sí se borra.
            $esperadosIds = $inventario->getBienesEsperadosIds();
            $detalles = DetalleInventario::with('movimiento')
                ->whereIn('id_detalle_inv', $request->detalles_ids)
                ->where('id_inventario', $inventario->id_inventario)
                ->get();

            foreach ($detalles as $detalle) {
                $idBien = $detalle->movimiento ? $detalle->movimiento->idbien : null;
                if ($idBien && in_array($idBien, $esperadosIds)) {
                    // Es un bien esperado (del snapshot), se regresa a pendiente
                    $detalle->update([
                        'estadoverificacion'     => \App\Models\DetalleInventario::PENDIENTE,
                        'usuarioverificador'     => null,
                        'fechaverificacion'      => null,
                        'ubicaciondetectada'     => null,
                        'requiereregularizacion' => false,
                        'observacion'            => null
                    ]);
                } else {
                    // Es un bien agregado manualmente (no esperado), se elimina por completo
                    $detalle->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Se han quitado ' . count($request->detalles_ids) . ' bienes del listado de verificados.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en eliminar masivo detalles: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener bienes disponibles para añadir al inventario (no duplicados)
     */
    public function bienesDisponibles(Request $request, Inventario $inventario)
    {
        $term = $request->get('q');

        $query = Movimiento::with(['bien.tipoBien', 'ubicacion.area'])
            ->vigentes()
            ->whereIn('id_movimiento', function($sub) {
                $sub->selectRaw('MAX(id_movimiento)')
                    ->from('movimiento')
                    ->where('anulado', false)
                    ->where('revertido', false)
                    ->groupBy('idbien');
            });

        // Si NO estamos buscando para incidencias, excluimos los bienes que ya están en el inventario Y VERIFICADOS.
        // Los bienes en estado 'pendiente' (faltantes) SÍ deben aparecer para poder verificarlos desde el buscador.
        if (!$request->has('incidencia')) {
            $yaAgregados = DetalleInventario::where('id_inventario', $inventario->id_inventario)
                ->where('estadoverificacion', '!=', \App\Models\DetalleInventario::PENDIENTE)
                ->pluck('id_movimiento')
                ->toArray();

            if (!empty($yaAgregados)) {
                $query->whereNotIn('id_movimiento', $yaAgregados);
            }
        }

        // Lógica estricta por tipo de inventario
        if ($inventario->getRawOriginal('tipoinventario') === Inventario::TIPO_BAJA) {
            // Inventario de BAJA: Solo bienes ya marcados como INACTIVOS o de BAJA
            $query->whereHas('bien', fn($q) => $q->where('activo', false));
        } else {
            // Otros inventarios: Solo bienes ACTIVOS
            $query->whereHas('bien', fn($q) => $q->where('activo', true));
        }

        if (!empty($term)) {
            $query->whereHas('bien', function($q) use ($term) {
                $q->where('codigo_patrimonial', 'LIKE', "%{$term}%")
                  ->orWhere('denominacion_bien', 'LIKE', "%{$term}%");
            });
        }

        $movimientos = $query->orderBy('id_movimiento', 'desc')
            ->limit(30)
            ->get()
            ->map(fn ($m) => [
                'id'           => $m->id_movimiento,
                'id_area'      => $m->ubicacion ? $m->ubicacion->idarea : null,
                'id_ubicacion' => $m->ubicacion ? $m->ubicacion->id_ubicacion : null,
                'text'         => '[' . ($m->bien->codigo_patrimonial ?? '-') . '] ' . ($m->bien->denominacion_bien ?? '-') . ' - Ubic: ' . 
                                  ($m->ubicacion ? (($m->ubicacion->area->nombre_area ?? '-') . ' (' . ($m->ubicacion->ambiente ?? '-') . ')') : 'Sin ubicación'),
            ]);

        return response()->json(['results' => $movimientos]);
    }

    // ==================== HELPERS PRIVADOS ====================

    /**
     * Pre-pobla la tabla detalle_inventario con todos los bienes esperados.
     * "Congela" el alcance del inventario para evitar inconsistencias futuras.
     */
    private function generarSnapshot(Inventario $inventario)
    {
        try {
            DB::beginTransaction();

            $bienesIds = $inventario->getBienesEsperadosIds();
            $esperadosCount = count($bienesIds);
            $procesados = 0;

            // Obtener el estado de conservación "Bueno" como default
            $estadoDefault = EstadoConservacion::whereRaw("UPPER(nombre_conservacion) LIKE '%BUENO%'")->first() 
                             ?? EstadoConservacion::first();

            foreach ($bienesIds as $idBien) {
                // Obtener estrictamente el ÚLTIMO movimiento vigente del bien
                // Usando una lógica idéntica a la del listado para evitar discrepancias
                $mov = Movimiento::where('idbien', $idBien)
                    ->where('anulado', false)
                    ->where('revertido', false)
                    ->latest('id_movimiento')
                    ->first();

                if ($mov) {
                    $existe = DetalleInventario::where('id_inventario', $inventario->id_inventario)
                        ->where('id_movimiento', $mov->id_movimiento)
                        ->exists();

                    if (!$existe) {
                        DetalleInventario::create([
                            'id_inventario'       => $inventario->id_inventario,
                            'id_movimiento'       => $mov->id_movimiento,
                            'estado_conservacion' => $estadoDefault?->id_estado_conservacion,
                            'estadoverificacion'  => 'pendiente',
                            'usuarioverificador'  => null,
                            'fechaverificacion'   => null,
                        ]);
                        $procesados++;
                    }
                }
            }

            DB::commit();
            
            if ($procesados < $esperadosCount && $esperadosCount > 0) {
                Log::warning("Snapshot incompleto para INV {$inventario->codigoinventario}. Esperados: {$esperadosCount}, Procesados: {$procesados}");
            }
            
            return $procesados;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Fallo al generar snapshot INV {$inventario->id_inventario}: " . $e->getMessage());
            return 0;
        }
    }

    private function formatInventario(Inventario $inv, bool $conDetalles = false): array
    {
        $data = [
            'id_inventario'    => $inv->id_inventario,
            'codigoinventario' => $inv->codigoinventario,
            'fecha_inicio'     => $inv->fecha_inicio?->format('Y-m-d'),
            'fecha_fin'        => $inv->fecha_fin?->format('Y-m-d'),
            'tipoinventario'   => $inv->tipoinventario,
            'estadoinventario' => $inv->getRawOriginal('estadoinventario'),
            'estadolabel'      => $inv->estadoinventario,
            'badgeestado'      => $inv->getBadgeEstado(),
            'badgeclase'       => $inv->getBadgeClaseEstado(),
            'alcancebadge'     => $inv->getAlcanceBadge(),
            'alcancehumanizado' => $inv->getAlcanceHumanizado(),
            'observacion'      => $inv->observacion,
            'fechacierre'      => $inv->fechacierre?->format('Y-m-d H:i'),
            'puedeEditarse'    => $inv->puedeEditarse(),
            'created_at'       => $inv->created_at?->format('Y-m-d H:i:s'),
            'responsable' => $inv->responsablePersona ? [
                'dni'      => $inv->responsablePersona->dni_responsable,
                'nombre'   => $inv->responsablePersona->nombre_responsable . ' ' . $inv->responsablePersona->apellidos_responsable,
            ] : null,
            'usuarioregistro' => $inv->usuarioRegistro ? [
                'id'   => $inv->usuarioRegistro->id,
                'name' => $inv->usuarioRegistro->name,
            ] : null,
            'total_detalles'     => $inv->detalles()->count(),
            'total_verificados'  => $inv->detalles()->whereIn('estadoverificacion', [\App\Models\DetalleInventario::VERIFICADO, \App\Models\DetalleInventario::OBSERVADO])->count(),
            'total_perdidos'     => $inv->detalles()->where('estadoverificacion', \App\Models\DetalleInventario::NO_ENCONTRADO)->count(),
        ];

        if ($conDetalles) {
            $data['detalles'] = $inv->detalles->map(fn ($d) => $this->formatDetalle($d))->values();
            $estadisticas = $inv->getEstadisticasConciliacion();
            
            // Traer información detallada de los faltantes para mostrar en la tabla
            if (!empty($estadisticas['faltantes_ids'])) {
                $faltantes = Bien::with(['tipoBien'])->whereIn('id_bien', $estadisticas['faltantes_ids'])->get();
                $estadisticas['bienes_faltantes'] = $faltantes->map(fn($b) => [
                    'id_bien'            => $b->id_bien,
                    'codigo_patrimonial' => $b->codigo_patrimonial,
                    'denominacion_bien'  => $b->denominacion_bien,
                    'tipo_bien'          => $b->tipoBien->nombre_tipo ?? '-'
                ])->values();
            } else {
                $estadisticas['bienes_faltantes'] = [];
            }

            $data['estadisticas_conciliacion'] = $estadisticas;
        }

        return $data;
    }

    private function formatDetalle(DetalleInventario $d): array
    {
        return [
            'id_detalle_inv'         => $d->id_detalle_inv,
            'id_inventario'          => $d->id_inventario,
            'id_movimiento'          => $d->id_movimiento,
            'estadoverificacion'     => $d->estadoverificacion,
            'badgeverificacion'      => $d->getBadgeVerificacion(),
            'badgeclaseverificacion' => $d->getBadgeClaseVerificacion(),
            'observacion'            => $d->observacion,
            'fechaverificacion'      => $d->fechaverificacion?->format('Y-m-d H:i'),
            'bien' => $d->movimiento && $d->movimiento->bien ? [
                'id_bien'            => $d->movimiento->bien->id_bien,
                'codigo_patrimonial' => $d->movimiento->bien->codigo_patrimonial,
                'denominacion_bien'  => $d->movimiento->bien->denominacion_bien,
                'tipo_bien'          => $d->movimiento->bien->tipoBien->nombre_tipo ?? '-',
            ] : null,
            'ubicacion_original' => $d->movimiento && $d->movimiento->ubicacion ? [
                'id_ubicacion' => $d->movimiento->ubicacion->id_ubicacion,
                'id_area'     => $d->movimiento->ubicacion->idarea,
                'nombre_sede' => $d->movimiento->ubicacion->nombre_sede,
                'area'        => $d->movimiento->ubicacion->area->nombre_area ?? '-',
                'ambiente'    => $d->movimiento->ubicacion->ambiente ?? '-',
            ] : null,
            'ubicacion_detectada' => $d->ubicacionDetectada ? [
                'id_ubicacion' => $d->ubicacionDetectada->id_ubicacion,
                'id_area'      => $d->ubicacionDetectada->idarea,
                'nombre_sede'  => $d->ubicacionDetectada->nombre_sede,
                'area'         => $d->ubicacionDetectada->area->nombre_area ?? '-',
                'ambiente'     => $d->ubicacionDetectada->ambiente ?? '-',
            ] : null,
            'estado_conservacion' => $d->estadoConservacion ? [
                'id'     => $d->estadoConservacion->id_estado_conservacion,
                'nombre' => $d->estadoConservacion->nombre_conservacion,
                'badge'  => $d->estadoConservacion->getBadgeClass(),
            ] : null,
            'usuario_verificador' => $d->usuarioVerificador ? [
                'id'   => $d->usuarioVerificador->id,
                'name' => $d->usuarioVerificador->name,
            ] : null,
        ];
    }
}
