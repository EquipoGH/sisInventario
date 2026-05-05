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

        // Datos para filtros
        $responsables       = Responsable::orderBy('apellidos_responsable')->get();
        $estadosConservacion = EstadoConservacion::orderBy('nombre_conservacion')->get();

        return view('inventario.index', compact(
            'inventarios',
            'total',
            'estadisticas',
            'responsables',
            'estadosConservacion'
        ));
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

    // ==================== SHOW ====================

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
        $usuarios            = User::orderBy('name')->get();
        $estadisticas_conciliacion = $inventario->getEstadisticasConciliacion();

        return view('inventario.show', compact(
            'inventario',
            'movimientos',
            'estadosConservacion',
            'ubicaciones',
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
            // ⭐ VALIDACIÓN PROFESIONAL: No cerrar si hay detalles marcados como "pendiente"
            $pendientes = $inventario->detalles()->where('estadoverificacion', 'pendiente')->count();
            if ($pendientes > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede cerrar el inventario porque existen {$pendientes} bienes con estado 'Pendiente'. Debe verificar todos los bienes agregados antes de finalizar."
                ], 422);
            }

            $data['usuariocierre'] = Auth::id();
            $data['fechacierre']   = now();
            if (!$inventario->fecha_fin) {
                $data['fecha_fin'] = now()->toDateString();
            }
        }

        $inventario->update($data);
        $inventario->refresh();

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
            $tipoMvto = TipoMvto::where('tipo_mvto', 'ASIGNACIÓN')->orWhere('tipo_mvto', 'Reasignación')->first();
            $idTipoMvto = $tipoMvto ? $tipoMvto->id_tipo_mvto : 2; // Default a 2 si no se encuentra

            foreach ($detalles as $detalle) {
                $originalUbicId = $detalle->movimiento ? $detalle->movimiento->idubicacion : null;
                $detectadaUbicId = $detalle->ubicaciondetectada;

                // Solo si la ubicación es distinta
                if ($originalUbicId && $detectadaUbicId && $originalUbicId != $detectadaUbicId) {
                    
                    // Crear nuevo movimiento formal
                    Movimiento::create([
                        'idbien'            => $detalle->movimiento->idbien,
                        'idubicacion'       => $detectadaUbicId,
                        'idresponsable'     => $inventario->responsable, // Asignar al responsable del inventario (o del área)
                        'tipo_mvto'         => $idTipoMvto,
                        'fecha_mvto'        => now(),
                        'documento_sustento'=> 'Regularización Acta ' . $inventario->codigoinventario,
                        'observacion'       => 'Actualización automática post-inventario. ' . $detalle->observacion,
                        'id_usuario'        => Auth::id(),
                        'id_estado_conservacion_bien' => $detalle->estado_conservacion,
                        'activo'            => true
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
                return response()->json(['success' => false, 'message' => 'Este bien ya está registrado en el inventario.'], 422);
            }

            $detalle = DetalleInventario::create($data);

            // ⭐ AUTOMÁTICO: Si el inventario está en 'pendiente', pasar a 'en_proceso' al agregar el primer bien
            if ($inventario->getRawOriginal('estadoinventario') === Inventario::ESTADO_PENDIENTE) {
                $inventario->update(['estadoinventario' => Inventario::ESTADO_EN_PROCESO]);
            }
            $detalle->load([
                'movimiento.bien.tipoBien',
                'movimiento.ubicacion.area',
                'estadoConservacion',
                'ubicacionDetectada.area',
                'usuarioVerificador',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bien agregado al inventario exitosamente.',
                'data'    => $this->formatDetalle($detalle),
            ]);
        } catch (\Exception $e) {
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

            return response()->json([
                'success' => true,
                'message' => 'Verificación actualizada.',
                'data'    => $this->formatDetalle($detalle),
            ]);
        } catch (\Exception $e) {
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
     * Obtener bienes disponibles para añadir al inventario (no duplicados)
     */
    public function bienesDisponibles(Request $request, Inventario $inventario)
    {
        $term = $request->get('q');

        $yaAgregados = DetalleInventario::where('id_inventario', $inventario->id_inventario)
            ->pluck('id_movimiento')
            ->toArray();

        $query = Movimiento::with(['bien.tipoBien', 'ubicacion.area'])
            ->vigentes()
            ->whereIn('id_movimiento', function($sub) {
                $sub->selectRaw('MAX(id_movimiento)')
                    ->from('movimiento')
                    ->where('anulado', false)
                    ->where('revertido', false)
                    ->groupBy('idbien');
            })
            ->whereNotIn('id_movimiento', $yaAgregados);

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
                'id'   => $m->id_movimiento,
                'text' => '[' . ($m->bien->codigo_patrimonial ?? '-') . '] ' . ($m->bien->denominacion_bien ?? '-') . ' - Ubic: ' . 
                          ($m->ubicacion ? (($m->ubicacion->area->nombre_area ?? '-') . ' (' . ($m->ubicacion->ambiente ?? '-') . ')') : 'Sin ubicación'),
            ]);

        return response()->json(['results' => $movimientos]);
    }

    // ==================== HELPERS PRIVADOS ====================

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
            'total_verificados'  => $inv->detalles()->where('estadoverificacion', 'verificado')->count(),
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
                'nombre_sede' => $d->movimiento->ubicacion->nombre_sede,
                'area'        => $d->movimiento->ubicacion->area->nombre_area ?? '-',
                'ambiente'    => $d->movimiento->ubicacion->ambiente ?? '-',
            ] : null,
            'ubicacion_detectada' => $d->ubicacionDetectada ? [
                'id_ubicacion' => $d->ubicacionDetectada->id_ubicacion,
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
