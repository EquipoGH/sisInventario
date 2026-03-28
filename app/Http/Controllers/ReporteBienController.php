<?php

namespace App\Http\Controllers;

use App\Exports\BienesExport;
use App\Models\Area;
use App\Models\Bien;
use App\Models\EstadoBien;
use App\Models\Responsable;
use App\Models\ResponsableArea;
use App\Models\TipoBien;
use App\Models\Ubicacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReporteBienController extends Controller
{
    /**
     * Vista única (centro de reportes de bienes)
     * - Inventario general por año
     * - Inventario por área + ubicación
     * - Bienes por estado (solo admin)
     * - Bienes por responsable
     */
    public function index(Request $request)
    {
        $tiposBien = TipoBien::orderBy('nombre_tipo')->get();
        $areas = Area::orderBy('nombre_area')->get();

        // Importante: el combo ubicación se llena dinámico cuando eligen área
        $ubicaciones = Ubicacion::with('area')
            ->orderBy('nombre_sede')
            ->orderBy('ambiente')
            ->get();

        $responsables = Responsable::orderBy('apellidos_responsable')
            ->orderBy('nombre_responsable')
            ->get();

        // Estados del bien (catálogo)
        $estadosBien = EstadoBien::orderBy('nombre_estado')->get();

        $settings = $this->reportSettings();
        $anios = $this->yearsDisponibles();

        return view('reportes.bienes.index', compact(
            'tiposBien',
            'areas',
            'ubicaciones',
            'responsables',
            'estadosBien',
            'settings',
            'anios'
        ));
    }

    /**
     * Settings para header/footer PDF/Excel
     */
    private function reportSettings(): array
    {
        $s = [
            'nombre_institucion' => setting('nombre_institucion', ''),
            'ruc' => setting('ruc', ''),
            'direccion' => setting('direccion', ''),
            'telefono' => setting('telefono', ''),
            'pie_reportes' => setting('pie_reportes', ''),
            'texto_legal' => setting('texto_legal', ''),
            'logo_reportes_path' => setting('logo_reportes_path'),
        ];

        $s['logo_reportes_abs'] = null;
        if (!empty($s['logo_reportes_path']) && Storage::disk('public')->exists($s['logo_reportes_path'])) {
            $s['logo_reportes_abs'] = storage_path('app/public/' . $s['logo_reportes_path']);
        }

        return $s;
    }

    /**
     * Años disponibles en base a fecha_registro del bien
     */
    private function yearsDisponibles(): array
    {
        $minDate = Bien::query()->min('fecha_registro');
        $maxDate = Bien::query()->max('fecha_registro');

        $min = $minDate ? (int)date('Y', strtotime($minDate)) : (int)date('Y');
        $max = $maxDate ? (int)date('Y', strtotime($maxDate)) : (int)date('Y');

        if ($min > $max) [$min, $max] = [$max, $min];

        return range($min, $max);
    }

    /**
     * Activos/Inactivos/Todos
     * - Soporta SoftDeletes (deleted_at)
     * - O columna boolean 'activo'
     */
    private function estadoFromRequest(Request $request): string
    {
        $estado = $request->input('estado', $request->query('estado', 'activos'));
        $estado = strtolower(trim((string)$estado));

        return in_array($estado, ['activos', 'inactivos', 'todos'], true) ? $estado : 'activos';
    }

    /**
     * Tipos de reportes
     */
    private function reporteFromRequest(Request $request): string
    {
        $reporte = $request->input('reporte', $request->query('reporte', 'inventario_general'));
        $reporte = strtolower(trim((string)$reporte));

        $valid = [
            'inventario_general',
            'inventario_area',
            'inventario_estado_admin',
            'bienes_responsable',
        ];

        return in_array($reporte, $valid, true) ? $reporte : 'inventario_general';
    }

    private function anioFromRequest(Request $request): ?int
    {
        $anio = $request->input('anio', $request->query('anio', null));
        if ($anio === null || $anio === '') return null;

        $anio = (int)$anio;
        return ($anio >= 2000 && $anio <= 2100) ? $anio : null;
    }

    /**
     * Autorización base por reporte. (Invitados no pueden ver el inventario por estado)
     */
    private function authorizeReporte(string $reporte): void
    {
        if ($reporte === 'inventario_estado_admin') {
            abort_if(auth()->check() && \App\Helpers\PermisosHelper::esInvitado(), 403, 'Acceso denegado a este reporte.');
        }
    }

    /**
     * Aplica filtro activos/inactivos/todos al query de Bien
     * (detecta si hay deleted_at o activo)
     */
    private function applyEstadoFilter($q, string $estado)
    {
        $table = (new Bien())->getTable();
        $hasDeletedAt = Schema::hasColumn($table, 'deleted_at');
        $hasActivo = Schema::hasColumn($table, 'activo');

        if ($hasDeletedAt) {
            if ($estado === 'activos') {
                // default: solo no eliminados
                // $q->withoutTrashed(); // si usas SoftDeletes; opcional
            } elseif ($estado === 'inactivos') {
                $q->onlyTrashed();
            } else { // todos
                $q->withTrashed();
            }
            return $q;
        }

        if ($hasActivo) {
            if ($estado === 'activos') $q->where('activo', true);
            if ($estado === 'inactivos') $q->where('activo', false);
            return $q; // todos: sin filtro
        }

        // Fallback: si no hay ninguno, no filtra para no romper
        return $q;
    }

    /**
     * Query base (reutilizable para DataTables, PDF y Excel)
     */
    private function baseQuery(Request $request)
    {
        $estado = $this->estadoFromRequest($request);

        $q = \App\Helpers\PermisosHelper::getBienesQuery()
            ->with([
                'tipoBien',
                // Esto NO se muestra en tabla, pero sirve en PDF/Excel si lo usas allá:
                'documentoSustento',
                'latestMovimiento.tipoMovimiento',
                'latestMovimiento.ubicacion.area.responsableAreas' => function($query) {
                    $query->orderBy('periodo_anio', 'desc')->orderBy('fecha_asignacion', 'desc');
                },
                'latestMovimiento.ubicacion.area.responsableAreas.responsable',
                'latestMovimiento.usuario',             // Usuario del sistema que registró el movimiento
                'latestMovimiento.estadoConservacion',  // Estado de conservación del bien (viene del último movimiento)
                'registradoPor',                        // Usuario que registró el bien
            ]);

        // Activos/Inactivos/Todos (robusto)
        $this->applyEstadoFilter($q, $estado);

        // Filtro global para INVITADO: Solo ver estado de conservación "BUENO"
        if (\App\Helpers\PermisosHelper::esInvitado()) {
            try {
                $estadoBuenoId = \App\Models\EstadoBien::obtenerIdPorNombre('bueno');
                $q->whereHas('latestMovimiento', function ($m) use ($estadoBuenoId) {
                    $m->where('id_estado_conservacion_bien', $estadoBuenoId);
                });
            } catch (\Exception $e) {
                // Si no existe 'bueno', no mostramos nada
                $q->whereRaw('1 = 0');
            }
        }

        // Filtro por año (aplica a todos los reportes si se envía)
        if ($anio = $this->anioFromRequest($request)) {
            $q->whereYear('fecha_registro', $anio);
        }

        // Filtros comunes
        if ($request->filled('tipo_bien')) $q->where('id_tipobien', $request->tipo_bien);

        if ($request->filled('ubicacion_id')) {
            $q->whereHas('latestMovimiento', fn($m) => $m->where('idubicacion', $request->ubicacion_id));
        }

        if ($request->filled('area_id')) {
            $q->whereHas('latestMovimiento.ubicacion', fn($u) => $u->where('idarea', $request->area_id));
        }

        // Filtro por Estado de Asignación (Buscar por NOMBRE del tipo de movimiento)
        $estadoAsignacion = $request->input('estado_asignacion', 'asignados');
        
        if ($estadoAsignacion === 'asignados') {
            // Buscamos que el último movimiento sea de algún tipo que contenga "asignacion" en su nombre
            $q->whereHas('latestMovimiento.tipoMovimiento', fn($t) => $t->where('tipo_mvto', 'ILIKE', '%asignaci%'));
        } elseif ($estadoAsignacion === 'sin_asignar') {
            // Buscamos que el último movimiento NO sea asignación, o que simplemente no tenga movimientos
            $q->whereDoesntHave('latestMovimiento.tipoMovimiento', fn($t) => $t->where('tipo_mvto', 'ILIKE', '%asignaci%'));
        }

        // Búsqueda global (tu scopeBuscar en Bien)
        $term = trim((string)$request->input('search.value', ''));
        if ($term === '') $term = trim((string)$request->input('q', ''));

        if ($term !== '') {
            $q->buscar($term);
        }

        return [$q, $estado];
    }

    /**
     * Aplica lo específico del tipo de reporte
     */
    private function applyReporteFilter($q, string $reporte, Request $request)
    {
        if ($reporte === 'inventario_area') {
            return $q;
        }

        if ($reporte === 'bienes_responsable') {
            if ($request->filled('responsable_id')) {
                // Filtramos los bienes cuyo último movimiento esté en una ubicación
                // cuya área esté asignada al responsable (vía tabla responsable_area)
                $dniResp = $request->responsable_id;
                $areasDelResponsable = \App\Models\ResponsableArea::where('dni_responsable', $dniResp)
                    ->pluck('idarea');

                if ($areasDelResponsable->isNotEmpty()) {
                    $q->whereHas('latestMovimiento.ubicacion', function ($u) use ($areasDelResponsable) {
                        $u->whereIn('idarea', $areasDelResponsable);
                    });
                } else {
                    // Responsable sin áreas asignadas: no mostrar ningún bien
                    $q->whereRaw('1 = 0');
                }
            }
            return $q;
        }

        if ($reporte === 'inventario_estado_admin') {
            if ($request->filled('estado_bien_id')) {
                // El estado de conservación del bien está en el último movimiento
                $q->whereHas('latestMovimiento', function ($m) use ($request) {
                    $m->where('id_estado_conservacion_bien', $request->estado_bien_id);
                });
            }
            return $q;
        }

        return $q;
    }

    private function recordsTotalByEstado(string $estado): int
    {
        $q = Bien::query();
        $this->applyEstadoFilter($q, $estado);
        return $q->count();
    }

    /**
     * Color para estado del bien (catálogo)
     * Devuelve clases Bootstrap: success/info/warning/danger/secondary
     */
    private function colorEstadoBien(?string $nombre): string
    {
        $v = strtolower(trim((string)$nombre));
        if ($v === '') return 'secondary';

        if (str_contains($v, 'nuevo')) return 'info';
        if (str_contains($v, 'bueno') || str_contains($v, 'operativo')) return 'success';
        if (str_contains($v, 'regular')) return 'warning';
        if (str_contains($v, 'malo') || str_contains($v, 'inoperativo') || str_contains($v, 'dañ')) return 'danger';

        return 'secondary';
    }

    /**
     * DataTables server-side (TABLA MINIMAL + COLOR)
     */
    public function data(Request $request)
    {
        $reporte = $this->reporteFromRequest($request);
        $this->authorizeReporte($reporte);

        $draw = (int)$request->input('draw', 1);
        $start = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 10);

        [$q, $estado] = $this->baseQuery($request);
        $base = $this->applyReporteFilter($q, $reporte, $request);

        $recordsTotal = $this->recordsTotalByEstado($estado);
        $recordsFiltered = (clone $base)->count();

        $rows = $base->orderBy('id_bien', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $table = (new Bien())->getTable();
        $hasDeletedAt = Schema::hasColumn($table, 'deleted_at');
        $hasActivo = Schema::hasColumn($table, 'activo');

        $data = $rows->map(function ($b) use ($hasDeletedAt, $hasActivo) {
            $lm = $b->latestMovimiento;
            $ubic = $lm?->ubicacion;
            $area = $ubic?->area;

            // Estado registro (para badge)
            $estadoRegistro = null;
            if ($hasDeletedAt) {
                $estadoRegistro = $b->deleted_at ? 'inactivo' : 'activo';
            } elseif ($hasActivo) {
                $estadoRegistro = ((bool)$b->activo) ? 'activo' : 'inactivo';
            }

            // El estado de conservación del bien viene del último movimiento
            $estadoBienNombre = $lm?->estadoConservacion?->nombre_estado;

            return [
                'codigo_patrimonial' => $b->codigo_patrimonial,
                'denominacion_bien'  => $b->denominacion_bien,
                'tipo_bien'          => optional($b->tipoBien)->nombre_tipo,
                'marca_bien'         => $b->marca_bien,
                'modelo_bien'        => $b->modelo_bien,
                'nserie_bien'        => $b->nserie_bien,
                'area'               => $area?->nombre_area,
                'ubicacion'          => $ubic ? trim(($ubic->nombre_sede ?? '') . ' - ' . ($ubic->ambiente ?? '')) : null,
                // El usuario del sistema que registró el último movimiento
                'responsable'        => $lm?->usuario
                                          ? trim(($lm->usuario->name ?? ''))
                                          : null,

                // === PARA COLOR EN LA TABLA ===
                'estado_registro'    => $estadoRegistro,
                'estado_bien'        => $estadoBienNombre,
                'estado_bien_color'  => $this->colorEstadoBien($estadoBienNombre),
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Export PDF
     */
    public function pdf(Request $request)
    {
        $reporte = $this->reporteFromRequest($request);
        $this->authorizeReporte($reporte);

        [$q, $estado] = $this->baseQuery($request);
        $bienes = $this->applyReporteFilter($q, $reporte, $request)->get();

        $settings = $this->reportSettings();

        // Nombres de filtros básicos
        $areaNombre = null;
        if ($request->filled('area_id')) {
            $a = Area::find($request->input('area_id'));
            $areaNombre = $a ? ($a->nombre_area ?? $a->id_area) : $request->input('area_id');
        }

        $ubicacionNombre = null;
        if ($request->filled('ubicacion_id')) {
            $u = Ubicacion::find($request->input('ubicacion_id'));
            $ubicacionNombre = $u ? trim(($u->nombre_sede ?? '') . ' - ' . ($u->ambiente ?? '')) : $request->input('ubicacion_id');
        }

        $tipoBienNombre = null;
        if ($request->filled('tipo_bien')) {
            $tb = TipoBien::find($request->input('tipo_bien'));
            $tipoBienNombre = $tb ? ($tb->nombre_tipo ?? $tb->id_tipo_bien) : $request->input('tipo_bien');
        }

        $estadoBienNombre = null;
        if ($reporte === 'inventario_estado_admin' && $request->filled('estado_bien_id')) {
            $eb = EstadoBien::find($request->input('estado_bien_id'));
            $estadoBienNombre = $eb ? $eb->nombre_estado : null;
        }

        $responsableNombre = null;
        if ($reporte === 'bienes_responsable' && $request->filled('responsable_id')) {
            $resp = Responsable::where('dni_responsable', $request->input('responsable_id'))->first();
            $responsableNombre = $resp ? trim($resp->apellidos_responsable . ' ' . $resp->nombre_responsable) : null;
        }

        $filtros = $request->all();
        $filtros['estado'] = $estado;
        $filtros['area_nombre'] = $areaNombre;
        $filtros['ubicacion_nombre'] = $ubicacionNombre;
        $filtros['tipo_bien_nombre'] = $tipoBienNombre;
        $filtros['estado_bien_nombre'] = $estadoBienNombre;
        $filtros['responsable_nombre'] = $responsableNombre;
        $filtros['estado_asignacion'] = $request->input('estado_asignacion', 'asignados');

        return Pdf::loadView('reportes.bienes.pdf', [
            'bienes' => $bienes,
            'settings' => $settings,
            'filtros' => $filtros,
            'reporte' => $reporte,
            'estado' => $estado,
        ])->setPaper('a4', 'portrait')
            ->stream("reporte_bienes_{$reporte}_{$estado}.pdf");
    }

    /**
     * Export Excel
     */
    public function excel(Request $request)
    {
        $reporte = $this->reporteFromRequest($request);
        $this->authorizeReporte($reporte);

        [$q, $estado] = $this->baseQuery($request);
        $bienes = $this->applyReporteFilter($q, $reporte, $request)->get();

        $settings = $this->reportSettings();

        // Nombres de filtros básicos
        $areaNombre = null;
        if ($request->filled('area_id')) {
            $a = Area::find($request->input('area_id'));
            $areaNombre = $a ? ($a->nombre_area ?? $a->id_area) : $request->input('area_id');
        }

        $ubicacionNombre = null;
        if ($request->filled('ubicacion_id')) {
            $u = Ubicacion::find($request->input('ubicacion_id'));
            $ubicacionNombre = $u ? trim(($u->nombre_sede ?? '') . ' - ' . ($u->ambiente ?? '')) : $request->input('ubicacion_id');
        }

        $tipoBienNombre = null;
        if ($request->filled('tipo_bien')) {
            $tb = TipoBien::find($request->input('tipo_bien'));
            $tipoBienNombre = $tb ? ($tb->nombre_tipo ?? $tb->id_tipo_bien) : $request->input('tipo_bien');
        }

        $estadoBienNombre = null;
        if ($reporte === 'inventario_estado_admin' && $request->filled('estado_bien_id')) {
            $eb = EstadoBien::find($request->input('estado_bien_id'));
            $estadoBienNombre = $eb ? $eb->nombre_estado : null;
        }

        $responsableNombre = null;
        if ($reporte === 'bienes_responsable' && $request->filled('responsable_id')) {
            $resp = Responsable::where('dni_responsable', $request->input('responsable_id'))->first();
            $responsableNombre = $resp ? trim($resp->apellidos_responsable . ' ' . $resp->nombre_responsable) : null;
        }

        $filtros = $request->all();
        $filtros['estado'] = $estado;
        $filtros['area_nombre'] = $areaNombre;
        $filtros['ubicacion_nombre'] = $ubicacionNombre;
        $filtros['tipo_bien_nombre'] = $tipoBienNombre;
        $filtros['estado_bien_nombre'] = $estadoBienNombre;
        $filtros['responsable_nombre'] = $responsableNombre;
        $filtros['estado_asignacion'] = $request->input('estado_asignacion', 'asignados');

        return Excel::download(
            new BienesExport($bienes, $settings, $filtros, $reporte),
            "reporte_bienes_{$reporte}_{$estado}.xlsx"
        );
    }

    /**
     * Ubicaciones por área (AJAX)
     */
    public function ubicacionesPorArea(Request $request)
    {
        $areaId = $request->get('area_id');

        $ubicaciones = Ubicacion::query()
            ->when($areaId, fn($q) => $q->where('idarea', $areaId))
            ->orderBy('nombre_sede')
            ->orderBy('ambiente')
            ->get(['id_ubicacion', 'nombre_sede', 'ambiente', 'idarea']);

        return response()->json([
            'success' => true,
            'data' => $ubicaciones,
        ]);
    }
}
