<?php

namespace App\Http\Controllers;

use App\Exports\BienesExport;
use App\Models\Area;
use App\Models\Bien;
use App\Models\EstadoBien;
use App\Models\Responsable;
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
            $s['logo_reportes_abs'] = Storage::disk('public')->path($s['logo_reportes_path']);
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
     * Solo admin puede ver "inventario por estado"
     */
    private function authorizeReporte(string $reporte): void
    {
        if ($reporte === 'inventario_estado_admin') {
            abort_unless(auth()->check() && auth()->user()->esAdmin(), 403);
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

        $q = Bien::query()
            ->with([
                'tipoBien',
                // Esto NO se muestra en tabla, pero sirve en PDF/Excel si lo usas allá:
                'documentoSustento',
                'latestMovimiento.tipoMovimiento',
                'latestMovimiento.ubicacion.area',
                'latestMovimiento.responsable',
                'estadoBien',
            ]);

        // Activos/Inactivos/Todos (robusto)
        $this->applyEstadoFilter($q, $estado);

        // Inventario general por año (fecha_registro del bien)
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
                $q->whereHas('latestMovimiento', function ($m) use ($request) {
                    $m->where('idresponsable', $request->responsable_id);
                });
            }
            return $q;
        }

        if ($reporte === 'inventario_estado_admin') {
            if ($request->filled('estado_bien_id')) {
                // Ajusta si tu FK real difiere
                $q->where('id_estado_bien', $request->estado_bien_id);
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

            $estadoBienNombre = $b->estadoBien?->nombre_estado;

            return [
                'codigo_patrimonial' => $b->codigo_patrimonial,
                'denominacion_bien'  => $b->denominacion_bien,
                'tipo_bien'          => optional($b->tipoBien)->nombre_tipo,
                'marca_bien'         => $b->marca_bien,
                'modelo_bien'        => $b->modelo_bien,
                'nserie_bien'        => $b->nserie_bien,
                'area'               => $area?->nombre_area,
                'ubicacion'          => $ubic ? trim(($ubic->nombre_sede ?? '') . ' - ' . ($ubic->ambiente ?? '')) : null,

                // === PARA COLOR EN LA TABLA ===
                'estado_registro'    => $estadoRegistro, // activo/inactivo o null si no hay columna
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

        $filtros = $request->all();
        $filtros['estado'] = $estado;

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

        $filtros = $request->all();
        $filtros['estado'] = $estado;

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
