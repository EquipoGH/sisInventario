<?php

namespace App\Http\Controllers;

use App\Exports\BienesExport;
use App\Models\Area;
use App\Models\Bien;
use App\Models\TipoBien;
use App\Models\Ubicacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReporteBienController extends Controller
{
    public function index(Request $request)
    {
        $tiposBien = TipoBien::orderBy('nombre_tipo')->get();
        $areas = Area::orderBy('nombre_area')->get();
        $ubicaciones = Ubicacion::with('area')->orderBy('nombre_sede')->orderBy('ambiente')->get();
        $settings = $this->reportSettings();

        return view('reportes.bienes.index', compact('tiposBien', 'areas', 'ubicaciones', 'settings'));
    }

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

    private function estadoFromRequest(Request $request): string
    {
        $estado = $request->input('estado', $request->query('estado', 'activos'));
        $estado = strtolower(trim((string)$estado));

        return in_array($estado, ['activos', 'inactivos', 'todos'], true) ? $estado : 'activos';
    }

    private function reporteFromRequest(Request $request): string
    {
        $reporte = $request->input('reporte', $request->query('reporte', 'general'));
        $reporte = strtolower(trim((string)$reporte));

        return in_array($reporte, ['general', 'registrados', 'asignados', 'bajas'], true) ? $reporte : 'general';
    }

    private function baseQuery(Request $request)
    {
        $estado = $this->estadoFromRequest($request);

        $q = Bien::query()
            ->with([
                'tipoBien',
                'documentoSustento',
                'latestMovimiento.tipoMovimiento',
                'latestMovimiento.ubicacion.area',
            ]);

        // ✅ eliminación lógica
        if ($estado === 'activos') $q->activos();
        if ($estado === 'inactivos') $q->eliminados();
        // 'todos' => sin filtro

        // ⚙️ filtros (puedes mantenerlos aunque no muestres la fecha)
        if ($request->filled('desde')) $q->whereDate('fecha_registro', '>=', $request->desde);
        if ($request->filled('hasta')) $q->whereDate('fecha_registro', '<=', $request->hasta);

        if ($request->filled('tipo_bien')) $q->where('id_tipobien', $request->tipo_bien);

        if ($request->filled('ubicacion_id')) {
            $q->whereHas('latestMovimiento', fn ($m) => $m->where('idubicacion', $request->ubicacion_id));
        }

        if ($request->filled('area_id')) {
            $q->whereHas('latestMovimiento.ubicacion', fn ($u) => $u->where('idarea', $request->area_id));
        }

        // 🔍 DataTables + tu input q
        $term = trim((string)$request->input('search.value', ''));
        if ($term === '') $term = trim((string)$request->input('q', ''));

        if ($term !== '') {
            $q->buscar($term); // tu scopeBuscar()
        }

        return [$q, $estado];
    }

    private function applyReporteFilter($q, string $reporte)
    {
        if ($reporte === 'bajas') {
            $q->whereHas('latestMovimiento', function ($m) {
                $m->where('revertido', false)
                    ->whereHas('tipoMovimiento', fn ($tm) => $tm->where('tipo_mvto', 'ILIKE', '%baja%'));
            });
        }

        if ($reporte === 'registrados') {
            $q->whereHas('latestMovimiento.tipoMovimiento', fn ($tm) => $tm->where('tipo_mvto', 'ILIKE', '%registr%'));
        }

        if ($reporte === 'asignados') {
            $q->whereHas('latestMovimiento.tipoMovimiento', fn ($tm) => $tm->where('tipo_mvto', 'ILIKE', '%asign%'));
        }

        return $q;
    }

    private function recordsTotalByEstado(string $estado): int
    {
        if ($estado === 'activos') return Bien::query()->activos()->count();
        if ($estado === 'inactivos') return Bien::query()->eliminados()->count();
        return Bien::query()->count(); // todos
    }

    public function data(Request $request)
    {
        $reporte = $this->reporteFromRequest($request);

        $draw = (int)$request->input('draw', 1);
        $start = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 10);

        [$q, $estado] = $this->baseQuery($request);
        $base = $this->applyReporteFilter($q, $reporte);

        $recordsTotal = $this->recordsTotalByEstado($estado);
        $recordsFiltered = (clone $base)->count();

        // ✅ Ya NO ordenamos por fecha_registro para no depender de “fecha del bien”
        $rows = $base
            ->orderBy('id_bien', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function ($b) {
            $lm = $b->latestMovimiento;
            $ubic = $lm?->ubicacion;
            $area = $ubic?->area;
            $tipoMvto = $lm?->tipoMovimiento?->tipo_mvto;

            return [
                'id_bien' => $b->id_bien,
                'codigo_patrimonial' => $b->codigo_patrimonial,
                'denominacion_bien' => $b->denominacion_bien,
                'tipo_bien' => optional($b->tipoBien)->nombre_tipo,
                'marca_bien' => $b->marca_bien,
                'modelo_bien' => $b->modelo_bien,
                'nserie_bien' => $b->nserie_bien,
                'area' => $area?->nombre_area,
                'ubicacion' => $ubic ? trim(($ubic->nombre_sede ?? '') . ' - ' . ($ubic->ambiente ?? '')) : null,
                'tipo_mvto' => $tipoMvto, // para colorear filas
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function pdf(Request $request)
    {
        $reporte = $this->reporteFromRequest($request);

        [$q, $estado] = $this->baseQuery($request);
        $bienes = $this->applyReporteFilter($q, $reporte)->get();

        $settings = $this->reportSettings();

        // para header del PDF
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

    public function excel(Request $request)
    {
        $reporte = $this->reporteFromRequest($request);

        [$q, $estado] = $this->baseQuery($request);
        $bienes = $this->applyReporteFilter($q, $reporte)->get();

        $settings = $this->reportSettings();

        $filtros = $request->all();
        $filtros['estado'] = $estado;

        return Excel::download(
            new BienesExport($bienes, $settings, $filtros, $reporte),
            "reporte_bienes_{$reporte}_{$estado}.xlsx"
        );
    }
}
