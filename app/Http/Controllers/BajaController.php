<?php

namespace App\Http\Controllers;

use App\Models\Baja;
use App\Models\Bien;
use App\Models\EstadoBien;
use App\Models\Movimiento;
use App\Models\TipoBien;
use App\Http\Requests\BajaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BajaController extends Controller
{
    public function create()
    {
        return redirect()->route('baja.index');
    }

    // =====================================================
    // LISTADO PRINCIPAL
    // =====================================================
    public function index(Request $request)
    {
        $search      = $request->get('search', '');
        $filtroTipo  = $request->get('tipo_bien', '');
        $filtroDesde = $request->get('fecha_desde', '');
        $filtroHasta = $request->get('fecha_hasta', '');
        $filtroMes   = $request->get('mes', '');
        $filtroAnio  = $request->get('anio', '');
        $perPage     = 15;

        $query = Baja::with(['bien.tipoBien', 'bien.estadoBien'])
            ->when($search, fn ($q) => $q->buscar($search));

        // Filtro tipo de bien — columna real: id_tipobien
        if ($filtroTipo) {
            $query->whereHas('bien', fn ($q) => $q->where('id_tipobien', $filtroTipo));
        }

        // Filtros de fecha
        if ($filtroDesde) {
            $query->whereDate('fecha_baja', '>=', $filtroDesde);
        }
        if ($filtroHasta) {
            $query->whereDate('fecha_baja', '<=', $filtroHasta);
        }
        if ($filtroMes) {
            $query->whereMonth('fecha_baja', $filtroMes);
        }
        if ($filtroAnio) {
            $query->whereYear('fecha_baja', $filtroAnio);
        }

        $total      = Baja::count();
        $totalMes   = Baja::whereMonth('fecha_baja', now()->month)->whereYear('fecha_baja', now()->year)->count();
        $bienesInactivos = Bien::where('activo', false)->count();
        $tiposBien  = TipoBien::orderBy('nombre_tipo')->get();

        $bajas = $query->orderByDesc('fecha_baja')->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'success'      => true,
                'data'         => $bajas->getCollection()->map(fn ($b) => $this->formatBaja($b)),
                'total'        => $total,
                'total_mes'    => $totalMes,
                'bienes_inactivos' => $bienesInactivos,
                'resultados'   => $bajas->total(),
                'current_page' => $bajas->currentPage(),
                'last_page'    => $bajas->lastPage(),
                'per_page'     => $bajas->perPage(),
                'from'         => $bajas->firstItem(),
                'to'           => $bajas->lastItem(),
            ]);
        }

        return view('baja.index', compact('bajas', 'total', 'totalMes', 'bienesInactivos', 'tiposBien'));
    }

    // =====================================================
    // BÚSQUEDA AJAX DE BIENES ACTIVOS (para el modal)
    // =====================================================
    public function buscarBienes(Request $request)
    {
        $q     = $request->get('q', '');
        $tipo  = $request->get('tipo', '');

        $query = Bien::with('tipoBien')
            ->where('activo', true)
            ->doesntHave('baja');

        if ($q) {
            $qLower = strtolower($q);
            $query->where(function ($sq) use ($qLower) {
                $sq->whereRaw('LOWER(codigo_patrimonial) LIKE ?', ["%{$qLower}%"])
                   ->orWhereRaw('LOWER(denominacion_bien) LIKE ?', ["%{$qLower}%"])
                   ->orWhereRaw('LOWER(marca_bien) LIKE ?', ["%{$qLower}%"]);
            });
        }

        if ($tipo) {
            $query->where('id_tipobien', $tipo);
        }

        $bienes = $query->orderBy('codigo_patrimonial')->limit(30)->get();

        return response()->json([
            'success' => true,
            'data'    => $bienes->map(fn ($b) => [
                'id'                 => $b->id_bien,
                'codigo_patrimonial' => $b->codigo_patrimonial,
                'denominacion_bien'  => $b->denominacion_bien,
                'marca_bien'         => $b->marca_bien,
                'tipo_bien'          => $b->tipoBien?->nombre_tipo,
            ]),
        ]);
    }

    // =====================================================
    // REGISTRAR BAJA
    // =====================================================
    public function store(BajaRequest $request)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Solo el ADMIN puede registrar bajas.'], 403);
        }

        try {
            DB::beginTransaction();

            $bien = Bien::findOrFail($request->id_bien);

            if ($bien->estaDadoDeBaja()) {
                return response()->json(['success' => false, 'message' => 'Este bien ya se encuentra en estado de BAJA.'], 400);
            }
            if ($bien->baja()->exists()) {
                return response()->json(['success' => false, 'message' => 'Este bien ya tiene un registro de baja formal.'], 400);
            }

            $baja = Baja::create($request->validated());

            $idEstadoBaja = EstadoBien::obtenerIdPorNombreNullable(EstadoBien::BAJA);
            $bien->forceFill([
                'activo'         => false,
                'eliminado_en'   => now(),
                'id_estado_bien' => $idEstadoBaja,
            ])->save();

            $tipoMvtoBaja = \App\Models\TipoMvto::whereRaw('UPPER(tipo_mvto) LIKE ?', ['%BAJA%'])->first();

            if ($tipoMvtoBaja) {
                $ultimaUbicacion = Movimiento::where('idbien', $bien->id_bien)
                    ->where('anulado', false)
                    ->whereNotNull('idubicacion')
                    ->orderByDesc('fecha_mvto')
                    ->value('idubicacion');

                Movimiento::create([
                    'idbien'          => $bien->id_bien,
                    'tipo_mvto'       => $tipoMvtoBaja->id_tipo_mvto,
                    'fecha_mvto'      => now(),
                    'detalle_tecnico' => "BAJA FORMAL — {$request->motivo_baja}"
                        . ($request->resolucion ? " | RES: {$request->resolucion}" : ''),
                    'idubicacion'     => $ultimaUbicacion,
                    'idusuario'       => Auth::id(),
                ]);
            }

            DB::commit();

            Log::info('BAJA REGISTRADA', [
                'id_baja' => $baja->id_baja,
                'codigo'  => $bien->codigo_patrimonial,
                'motivo'  => $request->motivo_baja,
                'usuario' => Auth::user()->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Bien '{$bien->codigo_patrimonial}' dado de baja exitosamente.",
                'data'    => $this->formatBaja($baja->load('bien.tipoBien')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar baja:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // =====================================================
    // VER DETALLE
    // =====================================================
    public function show(Baja $baja)
    {
        $baja->load(['bien.tipoBien', 'bien.estadoBien']);
        return response()->json(['success' => true, 'data' => $this->formatBaja($baja)]);
    }

    // =====================================================
    // ELIMINAR BAJA
    // =====================================================
    public function destroy(Baja $baja)
    {
        if (!Auth::user()->esAdmin()) {
            return response()->json(['success' => false, 'message' => 'Sin permisos.'], 403);
        }

        try {
            DB::beginTransaction();
            $bien = $baja->bien;
            $baja->delete();

            if ($bien) {
                $idActivo = EstadoBien::obtenerIdPorNombreNullable(EstadoBien::ACTIVO);
                $bien->forceFill(['activo' => true, 'eliminado_en' => null, 'id_estado_bien' => $idActivo])->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Registro eliminado y bien restaurado.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =====================================================
    // EXPORTAR PDF (optimizado / sin emojis)
    // =====================================================
    public function exportarPDF(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $bajas = $this->buildExportQuery($request)->get();

        $data = [
            'bajas'          => $bajas,
            'filtros'        => $this->describeFiltros($request),
            'total'          => $bajas->count(),
            'fechaGeneracion'=> now()->format('d/m/Y H:i:s'),
            'usuario'        => Auth::user(),
        ];

        $pdf = \PDF::loadView('baja.pdf-bajas', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('enable-local-file-access', true);

        return $pdf->download('Registro_Bajas_' . now()->format('Ymd_His') . '.pdf');
    }

    // =====================================================
    // EXPORTAR EXCEL (CSV nativo, sin dependencias extra)
    // =====================================================
    public function exportarExcel(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $bajas = $this->buildExportQuery($request)->get();

        $filename = 'Registro_Bajas_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($bajas) {
            $file = fopen('php://output', 'w');
            // BOM para UTF-8 (Excel lo requiere para tildes)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabecera
            fputcsv($file, ['ID', 'Codigo Patrimonial', 'Denominacion', 'Marca', 'Tipo Bien', 'Fecha Baja', 'Motivo', 'Resolucion', 'Observacion', 'Registrado']);

            foreach ($bajas as $b) {
                fputcsv($file, [
                    $b->id_baja,
                    $b->bien->codigo_patrimonial ?? '-',
                    $b->bien->denominacion_bien  ?? '-',
                    $b->bien->marca_bien          ?? '-',
                    $b->bien->tipoBien->nombre_tipo ?? '-',
                    $b->fecha_baja?->format('d/m/Y') ?? '-',
                    $b->motivo_baja,
                    $b->resolucion ?? '-',
                    $b->observacion ?? '-',
                    $b->created_at?->format('d/m/Y H:i') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =====================================================
    // INHABILITADO — baja es acto definitivo (SBN)
    // =====================================================
    public function revertir(Request $request, Baja $baja)
    {
        return response()->json([
            'success' => false,
            'message' => 'La reversión de bajas no está permitida (Directiva N° 001-2015/SBN). Una baja es un acto definitivo. Si fue un error, registre un nuevo ALTA.',
        ], 410);
    }

    // =====================================================
    // HELPERS PRIVADOS
    // =====================================================
    private function buildExportQuery(Request $request)
    {
        $query = Baja::with(['bien.tipoBien'])
            ->when($request->search,    fn ($q) => $q->buscar($request->search))
            ->when($request->tipo_bien, fn ($q) => $q->whereHas('bien', fn ($sq) => $sq->where('id_tipobien', $request->tipo_bien)))
            ->when($request->fecha_desde, fn ($q) => $q->whereDate('fecha_baja', '>=', $request->fecha_desde))
            ->when($request->fecha_hasta, fn ($q) => $q->whereDate('fecha_baja', '<=', $request->fecha_hasta))
            ->when($request->mes,  fn ($q) => $q->whereMonth('fecha_baja', $request->mes))
            ->when($request->anio, fn ($q) => $q->whereYear('fecha_baja',  $request->anio))
            ->orderByDesc('fecha_baja');

        return $query;
    }

    private function describeFiltros(Request $request): string
    {
        $parts = [];
        if ($request->search)     $parts[] = "Búsqueda: \"{$request->search}\"";
        if ($request->tipo_bien)  $parts[] = "Tipo: " . (TipoBien::find($request->tipo_bien)?->nombre_tipo ?? $request->tipo_bien);
        if ($request->fecha_desde) $parts[] = "Desde: {$request->fecha_desde}";
        if ($request->fecha_hasta) $parts[] = "Hasta: {$request->fecha_hasta}";
        if ($request->mes)         $parts[] = "Mes: {$request->mes}";
        if ($request->anio)        $parts[] = "Año: {$request->anio}";
        return $parts ? implode(' | ', $parts) : 'Todos los registros';
    }

    private function formatBaja(Baja $baja): array
    {
        return [
            'id_baja'     => $baja->id_baja,
            'fecha_baja'  => $baja->fecha_baja?->format('Y-m-d'),
            'motivo_baja' => $baja->motivo_baja,
            'resolucion'  => $baja->resolucion,
            'observacion' => $baja->observacion,
            'created_at'  => $baja->created_at?->format('d/m/Y H:i'),
            'bien'        => $baja->bien ? [
                'id_bien'            => $baja->bien->id_bien,
                'codigo_patrimonial' => $baja->bien->codigo_patrimonial,
                'denominacion_bien'  => $baja->bien->denominacion_bien,
                'marca_bien'         => $baja->bien->marca_bien,
                'tipo_bien'          => $baja->bien->tipoBien?->nombre_tipo,
            ] : null,
        ];
    }
}
