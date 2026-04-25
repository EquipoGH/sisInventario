<?php

namespace App\Http\Controllers;

use App\Exports\MovimientosBienesExport;
use App\Models\Area;
use App\Models\TipoMvto;
use App\Models\Ubicacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReporteMovimientosController extends Controller
{
    public function index()
    {
        $tiposMovimiento = TipoMvto::orderBy('tipo_mvto')->get();
        $ubicaciones = Ubicacion::orderBy('nombre_sede')->orderBy('ambiente')->get();
        $areas = Area::orderBy('nombre_area')->get();
        $settings = $this->reportSettings();

        return view('reportes.movimientos.index', compact('tiposMovimiento', 'ubicaciones', 'areas', 'settings'));
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

    /**
     * Eliminado: ultimosMovimientosSub ya que ahora el reporte actúa como Kárdex Histórico Real.
     */

    private function baseQuery(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        return DB::table('movimiento as m')
            ->join('bien as b', 'b.id_bien', '=', 'm.idbien')
            ->leftJoin('tipo_bien as tb', 'tb.id_tipo_bien', '=', 'b.id_tipobien')
            ->leftJoin('tipo_mvto as tm', 'tm.id_tipo_mvto', '=', 'm.tipo_mvto')
            ->leftJoin('ubicacion as u', 'u.id_ubicacion', '=', 'm.idubicacion')
            ->leftJoin('area as a', 'a.id_area', '=', 'u.idarea')
            ->leftJoin('users as usr', 'usr.id', '=', 'm.idusuario')
            ->where(function ($w) {
                // ✅ PostgreSQL usa boolean: false, no 0
                $w->whereNull('m.anulado')->orWhere('m.anulado', false);
            })
            ->select([
                'b.id_bien',
                'b.codigo_patrimonial',
                'b.denominacion_bien',
                'tb.nombre_tipo as tipo_bien',
                'm.fecha_mvto',
                'tm.tipo_mvto as tipo_mov',
                'a.nombre_area as area',
                'u.nombre_sede',
                'u.ambiente',
                'usr.name as usuario_nombre',
            ])
            ->when($desde, function ($q, $desde) {
                $q->whereDate('m.fecha_mvto', '>=', $desde);
            })
            ->when($hasta, function ($q, $hasta) {
                $q->whereDate('m.fecha_mvto', '<=', $hasta);
            })
            ->when($request->filled('tipo_mvto'), function ($q) use ($request) {
                $q->where('m.tipo_mvto', $request->tipo_mvto);
            })
            ->when($request->filled('ubicacion_id'), function ($q) use ($request) {
                $q->where('m.idubicacion', $request->ubicacion_id);
            })
            ->when($request->filled('area_id'), function ($q) use ($request) {
                $q->where('u.idarea', $request->area_id);
            })
            ->when(trim((string) $request->input('q', '')) !== '', function ($q) use ($request) {
                $term = trim((string) $request->input('q', ''));
                $termLower = strtolower($term);
                $q->where(function ($w) use ($termLower) {
                    $w->whereRaw('LOWER(b.codigo_patrimonial) LIKE ?', ["%{$termLower}%"])
                      ->orWhereRaw('LOWER(b.denominacion_bien) LIKE ?', ["%{$termLower}%"])
                      ->orWhereRaw('LOWER(b.marca_bien) LIKE ?', ["%{$termLower}%"])
                      ->orWhereRaw('LOWER(b.modelo_bien) LIKE ?', ["%{$termLower}%"])
                      ->orWhereRaw('LOWER(b.nserie_bien) LIKE ?', ["%{$termLower}%"]);
                });
            })
            ->when(\App\Helpers\PermisosHelper::esInvitado(), function ($q) {
                // ⭐ OPCIÓN B: Ocultamiento Total para Invitados (A prueba de balas) ⭐
                // Evaluamos el estado REAL ACTUAL del bien globalmente.
                try {
                    $estadoBuenoId = \App\Models\EstadoBien::obtenerIdPorNombre('bueno');
                    $idsBaja = \App\Models\TipoMvto::whereRaw("LOWER(tipo_mvto) LIKE '%baja%'")->pluck('id_tipo_mvto')->toArray();

                    $q->whereIn('b.id_bien', function($query) use ($estadoBuenoId, $idsBaja) {
                        $query->select('idbien')
                              ->from(function($sub) {
                                  $sub->selectRaw('DISTINCT ON (idbien) idbien, id_estado_conservacion_bien, tipo_mvto')
                                      ->from('movimiento')
                                      ->where(function($w){
                                           $w->whereNull('anulado')->orWhere('anulado', false);
                                      })
                                      ->orderBy('idbien')
                                      ->orderByDesc('fecha_mvto')
                                      ->orderByDesc('id_movimiento');
                              }, 'ultimos')
                              ->where('id_estado_conservacion_bien', $estadoBuenoId);
                        
                        if (!empty($idsBaja)) {
                            $query->whereNotIn('tipo_mvto', $idsBaja);
                        }
                    });

                    // Nos aseguramos que ninguna fila individual en el historial mostrado sea una Baja visible.
                    if (!empty($idsBaja)) {
                        $q->whereNotIn('m.tipo_mvto', $idsBaja);
                    }
                } catch (\Exception $e) {
                    $q->whereRaw('1 = 0');
                }
            });
    }

    private function countFiltered(Request $request): int
    {
        // Cuenta sobre la misma query, pero sin select y sin paginación
        return (int) DB::query()->fromSub($this->baseQuery($request), 'q')->count();
    }

    public function data(Request $request)
    {
        try {
            $draw   = (int) $request->input('draw', 1);
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);

            $query = $this->baseQuery($request);

            $recordsFiltered = $this->countFiltered($request);
            $recordsTotal    = $recordsFiltered;

            $rows = $query->orderByDesc('m.fecha_mvto')
                ->orderBy('b.codigo_patrimonial')
                ->offset($start)
                ->limit($length)
                ->get();

            $data = $rows->values()->map(function ($r, $idx) use ($start) {
                $ubicTxt = trim($r->ambiente ?? '');
                if ($ubicTxt === '') $ubicTxt = '-';

                $docTxt = trim(($r->tipo_documento ?? '') . ' ' . ($r->numero_documento ?? ''));
                if ($docTxt === '') $docTxt = '-';

                $fechaTxt = '-';
                if (!empty($r->fecha_mvto)) {
                    try { $fechaTxt = Carbon::parse($r->fecha_mvto)->format('d/m/Y'); }
                    catch (\Throwable $e) { $fechaTxt = (string) $r->fecha_mvto; }
                }

                return [
                    'num'          => $start + $idx + 1,
                    'codigo'       => $r->codigo_patrimonial ?? '-',
                    'denominacion' => mb_strtoupper($r->denominacion_bien ?? ''),
                    'tipo_bien'    => $r->tipo_bien ?? '-',
                    'fecha_mov'    => $fechaTxt,
                    'tipo_mov'     => $r->tipo_mov ?? '-',
                    'area'         => $r->area ?? '-',
                    'ubicacion'    => $ubicTxt,
                ];
            });

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function pdf(Request $request)
    {
        $rows = $this->baseQuery($request)->orderByDesc('m.fecha_mvto')->orderBy('b.codigo_patrimonial')->get();

        $settings = $this->reportSettings();

        // Obtener nombres para los filtros
        $tipoMvtoNombre = null;
        if ($request->filled('tipo_mvto')) {
            $tm = TipoMvto::find($request->input('tipo_mvto'));
            $tipoMvtoNombre = $tm ? ($tm->tipo_mvto ?? $tm->nombre ?? $tm->id_tipo_mvto) : $request->input('tipo_mvto');
        }

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

        $filtros = [
            'desde' => $request->input('desde'),
            'hasta' => $request->input('hasta'),
            'tipo_mvto' => $request->input('tipo_mvto'),
            'tipo_mvto_nombre' => $tipoMvtoNombre,
            'ubicacion_id' => $request->input('ubicacion_id'),
            'ubicacion_nombre' => $ubicacionNombre,
            'area_id' => $request->input('area_id'),
            'area_nombre' => $areaNombre,
            'q' => $request->input('q'),
        ];

        return Pdf::loadView('reportes.movimientos.pdf', [
            'rows' => $rows,
            'settings' => $settings,
            'filtros' => $filtros,
            'usuario' => Auth::user(),
        ])->setPaper('a4', 'portrait')->stream('reporte_movimientos_por_fecha.pdf');
    }

    public function excel(Request $request)
    {
        $rows = $this->baseQuery($request)->orderByDesc('m.fecha_mvto')->orderBy('b.codigo_patrimonial')->get();

        $settings = $this->reportSettings();

        // Obtener nombres para los filtros
        $tipoMvtoNombre = null;
        if ($request->filled('tipo_mvto')) {
            $tm = TipoMvto::find($request->input('tipo_mvto'));
            $tipoMvtoNombre = $tm ? ($tm->tipo_mvto ?? $tm->nombre ?? $tm->id_tipo_mvto) : $request->input('tipo_mvto');
        }

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

        $filtros = [
            'desde' => $request->input('desde'),
            'hasta' => $request->input('hasta'),
            'tipo_mvto' => $request->input('tipo_mvto'),
            'tipo_mvto_nombre' => $tipoMvtoNombre,
            'ubicacion_id' => $request->input('ubicacion_id'),
            'ubicacion_nombre' => $ubicacionNombre,
            'area_id' => $request->input('area_id'),
            'area_nombre' => $areaNombre,
            'q' => $request->input('q'),
        ];

        return Excel::download(
            new MovimientosBienesExport($rows, $settings, $filtros, 'movimientos_por_fecha'),
            'reporte_movimientos_por_fecha.xlsx'
        );
    }
}
