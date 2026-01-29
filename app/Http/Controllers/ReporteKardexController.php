<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KardexMovimientosExport;

class ReporteKardexController extends Controller
{
    public function __construct()
    {
        $this->middleware('permiso:Reportes');
    }

    public function index()
    {
        $tiposMovto = DB::table('tipo_mvto')->orderBy('tipo_mvto')->get();
        $tiposBien  = DB::table('tipo_bien')->orderBy('nombre_tipo')->get();
        $areas      = DB::table('area')->orderBy('nombre_area')->get();
        $estados    = DB::table('estado_bien')->orderBy('nombre_estado')->get();

        return view('reportes.kardex.index', compact('tiposMovto','tiposBien','areas','estados'));
    }

    private function baseQuery(Request $request)
    {
        $q = DB::table('movimiento as m')
            ->join('bien as b', 'b.id_bien', '=', 'm.idbien')
            ->join('tipo_bien as tb', 'tb.id_tipo_bien', '=', 'b.id_tipobien')
            ->join('tipo_mvto as tm', 'tm.id_tipo_mvto', '=', 'm.tipo_mvto')
            ->leftJoin('ubicacion as u', 'u.id_ubicacion', '=', 'm.idubicacion')
            ->leftJoin('area as a', 'a.id_area', '=', 'u.idarea')
            ->leftJoin('documento_sustento as d', 'd.id_documento', '=', 'm.documento_sustentatorio')
            ->leftJoin('estado_bien as eb', 'eb.id_estado', '=', 'm.id_estado_conservacion_bien')
            ->leftJoin('users as us', 'us.id', '=', 'm.idusuario')
            ->select([
                'm.id_movimiento',
                'm.fecha_mvto',
                'tm.tipo_mvto as tipo_movimiento',
                'b.codigo_patrimonial',
                'b.denominacion_bien',
                'tb.nombre_tipo as tipo_bien',
                DB::raw("COALESCE(a.nombre_area,'-') as area"),
                DB::raw("COALESCE(u.nombre_sede,'-') as sede"),
                DB::raw("COALESCE(u.ambiente,'-') as ambiente"),
                DB::raw("COALESCE(u.piso_ubicacion,'-') as piso"),
                DB::raw("COALESCE(eb.nombre_estado,'-') as estado_bien"),
                DB::raw("COALESCE(d.tipo_documento,'-') as tipo_documento"),
                DB::raw("COALESCE(d.numero_documento, m.\"NumDocto\", '-') as numero_documento"),
                'm.detalle_tecnico',
                DB::raw("COALESCE(us.name,'-') as usuario"),
            ]);

        // filtros (dropdowns/fechas)
        if ($request->filled('desde')) $q->whereDate('m.fecha_mvto', '>=', $request->desde);
        if ($request->filled('hasta')) $q->whereDate('m.fecha_mvto', '<=', $request->hasta);
        if ($request->filled('tipo_mvto')) $q->where('m.tipo_mvto', $request->tipo_mvto);
        if ($request->filled('tipo_bien')) $q->where('b.id_tipobien', $request->tipo_bien);
        if ($request->filled('area')) $q->where('a.id_area', $request->area);
        if ($request->filled('estado')) $q->where('m.id_estado_conservacion_bien', $request->estado);

        // solo operativos
        if ($request->boolean('solo_operativos')) {
            $q->whereRaw("LOWER(COALESCE(eb.nombre_estado,'')) like '%operat%'");
        }

        // BÚSQUEDA GLOBAL: DataTables manda search[value] [web:1568]
        $term = trim((string) $request->input('search.value', ''));

        // (opcional) si también quieres soportar ?q=...
        if ($term === '') {
            $term = trim((string) $request->input('q', ''));
        }

        if ($term !== '') {
            $q->where(function ($w) use ($term) {
                $w->where('b.codigo_patrimonial', 'ilike', "%{$term}%")
                  ->orWhere('b.denominacion_bien', 'ilike', "%{$term}%")
                  ->orWhere('m.detalle_tecnico', 'ilike', "%{$term}%")
                  ->orWhere('d.numero_documento', 'ilike', "%{$term}%")
                  ->orWhereRaw('CAST(m."NumDocto" AS TEXT) ILIKE ?', ["%{$term}%"]);
            });
        }

        return $q;
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $base = $this->baseQuery($request);

        // total sin filtros
        $recordsTotal = DB::table('movimiento as m')->count();

        // total con filtros (incluye search[value]) [web:1568]
        $recordsFiltered = (clone $base)->count();

        // orden + paginación
        $data = $base
            ->orderBy('m.fecha_mvto', 'desc')
            ->orderBy('m.id_movimiento', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function pdf(Request $request)
    {
        $rows = $this->baseQuery($request)
            ->orderBy('m.fecha_mvto', 'desc')
            ->get();

        $pdf = Pdf::loadView('reportes.kardex.pdf', [
            'rows' => $rows,
            'filtros' => $request->all(),
            'generado' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('kardex_movimientos.pdf');
    }

    public function excel(Request $request)
    {
        return Excel::download(new KardexMovimientosExport($request->all()), 'kardex_movimientos.xlsx');
    }
}
