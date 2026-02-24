<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\TipoBien;
use App\Models\Bien;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\TipoMvto;
use App\Helpers\PermisosHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ==================== CONTEXTO DEL USUARIO ====================
        $user    = auth()->user();
        $esAdmin = $user->esAdmin();

        // IDs de áreas a las que tiene acceso el usuario
        $idsAreas        = $user->getIdsAreasPermitidas();   // array de IDs
        $areasDelUsuario = $user->getAreasAcceso();          // colección de modelos Area

        // ==================== CONTADORES BÁSICOS ====================
        $totalAreas = $esAdmin ? Area::count() : count($idsAreas);

        $totalTiposBien = TipoBien::count();

        // ⭐ Usar el mismo helper que usa BienController
        $totalBienes = PermisosHelper::getBienesQuery()->where('activo', true)->count();

        $totalUbicaciones = $esAdmin
            ? Ubicacion::count()
            : Ubicacion::whereIn('idarea', $idsAreas)->count();

        // ==================== MOVIMIENTOS HOY / SEMANA ====================
        // ⭐ Usar el mismo helper que usa MovimientoController
        $baseMovimientos = PermisosHelper::getMovimientosQuery();

        $movimientosHoy    = (clone $baseMovimientos)->whereDate('fecha_mvto', today())->count();
        $movimientosSemana = (clone $baseMovimientos)->whereBetween('fecha_mvto', [now()->startOfWeek(), now()->endOfWeek()])->count();

        // ==================== ÚLTIMOS BIENES ====================
        // ⭐ Usar el mismo helper que usa BienController
        $ultimosBienes = PermisosHelper::getBienesQuery()
            ->with(['tipoBien'])
            ->where('activo', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ==================== ÚLTIMOS MOVIMIENTOS ====================
        // ⭐ Usar el mismo helper que usa MovimientoController
        $ultimosMovimientos = PermisosHelper::getMovimientosQuery()
            ->with(['bien', 'tipoMovimiento', 'ubicacion.area'])
            ->orderBy('fecha_mvto', 'desc')
            ->limit(10)
            ->get();

        // ==================== GRÁFICO 1: MOVIMIENTOS POR TIPO ====================
        $queryG1 = DB::table('movimiento as m')
            ->join('tipo_mvto as tm', 'm.tipo_mvto', '=', 'tm.id_tipo_mvto')
            ->select('tm.tipo_mvto', DB::raw('COUNT(*) as total'))
            ->groupBy('tm.id_tipo_mvto', 'tm.tipo_mvto')
            ->orderByDesc('total')
            ->limit(5);

        if (!$esAdmin) {
            $queryG1->join('ubicacion as u_g1', 'm.idubicacion', '=', 'u_g1.id_ubicacion')
                    ->whereIn('u_g1.idarea', $idsAreas);
        }

        $movimientosPorTipo = $queryG1->get();
        $estadosLabels = $movimientosPorTipo->pluck('tipo_mvto')->map(fn($t) => strtoupper($t));
        $estadosData   = $movimientosPorTipo->pluck('total');

        if ($estadosLabels->isEmpty()) {
            $estadosLabels = collect(['REGISTRO', 'ASIGNACIÓN', 'BAJA']);
            $estadosData   = collect([0, 0, 0]);
        }

        // ==================== GRÁFICO 2: BIENES POR TIPO ====================
        $queryG2 = DB::table('bien as b')
            ->join('tipo_bien as tb', 'b.id_tipobien', '=', 'tb.id_tipo_bien')
            ->select('tb.nombre_tipo', DB::raw('COUNT(b.id_bien) as total'))
            ->where('b.activo', true)
            ->whereNotNull('b.id_tipobien')
            ->whereNotNull('tb.nombre_tipo')
            ->groupBy('tb.id_tipo_bien', 'tb.nombre_tipo')
            ->orderByDesc('total');

        if (!$esAdmin) {
            $queryG2->join('movimiento as mv_g2', 'b.id_bien', '=', 'mv_g2.idbien')
                    ->join('ubicacion as u_g2', 'mv_g2.idubicacion', '=', 'u_g2.id_ubicacion')
                    ->whereIn('u_g2.idarea', $idsAreas);
        }

        $bienesPorTipo = $queryG2->get();
        $tiposLabels   = $bienesPorTipo->pluck('nombre_tipo')->map(fn($t) => strtoupper($t));
        $tiposData     = $bienesPorTipo->pluck('total');

        if ($tiposLabels->isEmpty()) {
            $tiposLabels = collect(['SIN DATOS']);
            $tiposData   = collect([0]);
        }

        // ==================== GRÁFICO 3 + SPARKLINES: ÚLTIMOS 7 DÍAS ====================
        $movimientosDias  = [];
        $diasLabels       = [];
        $sparkBienes      = [];   // bienes registrados día a día
        $sparkMovimientos = [];   // alias de movimientosDias (para sparkline de la KPI)

        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $dateStr = $fecha->format('Y-m-d');

            // Movimientos del día
            $qMov = Movimiento::whereDate('fecha_mvto', $dateStr);
            if (!$esAdmin) {
                $qMov->whereHas('ubicacion', fn($q2) => $q2->whereIn('idarea', $idsAreas));
            }
            $totalMov = $qMov->count();
            $movimientosDias[] = $totalMov;
            $sparkMovimientos[] = $totalMov;

            // Bienes registrados del día
            $qBien = PermisosHelper::getBienesQuery()->where('activo', true)->whereDate('created_at', $dateStr);
            $sparkBienes[] = $qBien->count();

            $diasLabels[] = ucfirst($fecha->locale('es')->isoFormat('ddd D'));
        }

        // ==================== GRÁFICO 4: TOP ÁREAS ====================
        $queryG4 = DB::table('movimiento as m')
            ->join('ubicacion as u_g4', 'm.idubicacion', '=', 'u_g4.id_ubicacion')
            ->join('area as a', 'u_g4.idarea', '=', 'a.id_area')
            ->select('a.nombre_area', DB::raw('COUNT(DISTINCT m.idbien) as total'))
            ->groupBy('a.id_area', 'a.nombre_area')
            ->orderByDesc('total')
            ->limit(5);

        if (!$esAdmin) {
            $queryG4->whereIn('u_g4.idarea', $idsAreas);
        }

        $topAreas    = $queryG4->get();
        $areasLabels = $topAreas->pluck('nombre_area')->map(fn($a) => strtoupper($a));
        $areasData   = $topAreas->pluck('total');

        if ($areasLabels->isEmpty()) {
            $areasLabels = collect(['SIN DATOS']);
            $areasData   = collect([0]);
        }

        // ==================== GRÁFICO 5: TOP UBICACIONES ====================
        $queryG5 = DB::table('movimiento as m')
            ->join('ubicacion as u_g5', 'm.idubicacion', '=', 'u_g5.id_ubicacion')
            ->select('u_g5.nombre_sede', DB::raw('COUNT(DISTINCT m.idbien) as total'))
            ->whereNotNull('m.idubicacion')
            ->groupBy('u_g5.id_ubicacion', 'u_g5.nombre_sede')
            ->orderByDesc('total')
            ->limit(5);

        if (!$esAdmin) {
            $queryG5->whereIn('u_g5.idarea', $idsAreas);
        }

        $topUbicaciones    = $queryG5->get();
        $ubicacionesLabels = $topUbicaciones->pluck('nombre_sede')->map(fn($u) => strtoupper($u));
        $ubicacionesData   = $topUbicaciones->pluck('total');

        if ($ubicacionesLabels->isEmpty()) {
            $ubicacionesLabels = collect(['SIN DATOS']);
            $ubicacionesData   = collect([0]);
        }

        // ==================== ESTADÍSTICAS ADICIONALES ====================
        $porcentajeBuenos = 0;

        $qMesActual   = Movimiento::whereMonth('fecha_mvto', now()->month)->whereYear('fecha_mvto', now()->year);
        $qMesAnterior = Movimiento::whereMonth('fecha_mvto', now()->subMonth()->month)->whereYear('fecha_mvto', now()->subMonth()->year);

        if (!$esAdmin) {
            $qMesActual->whereHas('ubicacion', fn($q)   => $q->whereIn('idarea', $idsAreas));
            $qMesAnterior->whereHas('ubicacion', fn($q) => $q->whereIn('idarea', $idsAreas));
        }

        $movimientosMesActual   = $qMesActual->count();
        $movimientosMesAnterior = $qMesAnterior->count();

        $tendenciaMovimientos = $movimientosMesAnterior > 0
            ? round((($movimientosMesActual - $movimientosMesAnterior) / $movimientosMesAnterior) * 100, 1)
            : 0;

        // ==================== ESTADO DE CONSERVACIÓN ====================
        $queryEstados = DB::table('movimiento as m')
            ->join('estado_bien as eb', 'm.id_estado_conservacion_bien', '=', 'eb.id_estado')
            ->select('eb.nombre_estado', DB::raw('COUNT(DISTINCT m.idbien) as total'))
            ->whereNotNull('m.id_estado_conservacion_bien')
            ->where(function($q) {
                $q->whereNull('m.anulado')->orWhere('m.anulado', false);
            })
            ->groupBy('eb.id_estado', 'eb.nombre_estado')
            ->orderByDesc('total');

        if (!$esAdmin) {
            $queryEstados->join('ubicacion as u_est', 'm.idubicacion', '=', 'u_est.id_ubicacion')
                         ->whereIn('u_est.idarea', $idsAreas);
        }

        $estadosConservacion = $queryEstados->get();
        $conservacionLabels  = $estadosConservacion->pluck('nombre_estado')->map(fn($e) => strtoupper($e));
        $conservacionData    = $estadosConservacion->pluck('total');

        if ($conservacionLabels->isEmpty()) {
            $conservacionLabels = collect(['SIN DATOS']);
            $conservacionData   = collect([0]);
        }

        // ==================== BIENES SIN MOVIMIENTOS ====================
        $bienesSinMovimiento = $esAdmin
            ? Bien::where('activo', true)->doesntHave('movimientos')->count()
            : PermisosHelper::getBienesQuery()->where('activo', true)->doesntHave('movimientos')->count();

        // ==================== WIDGET ESTADO DEL INVENTARIO ====================
        // Bienes sin actividad en los últimos 30 días
        $hace30Dias = now()->subDays(30)->format('Y-m-d');
        $qInactivos = PermisosHelper::getBienesQuery()->where('activo', true)
            ->where(function($q) use ($hace30Dias) {
                $q->doesntHave('movimientos')
                  ->orWhereHas('movimientos', function($q2) use ($hace30Dias) {
                      $q2->where('anulado', false)->select(DB::raw('MAX(fecha_mvto)'));
                  }, '=', 0);
            });
        // Simplificado: bienes cuyo último movimiento es anterior a 30 días
        $bienesInactivos30 = PermisosHelper::getBienesQuery()
            ->where('activo', true)
            ->where(function($q) use ($hace30Dias) {
                $q->doesntHave('movimientos')
                  ->orWhereHas('latestMovimiento', function($q2) use ($hace30Dias) {
                      $q2->where('fecha_mvto', '<', $hace30Dias)->where('anulado', false);
                  });
            })->count();

        // Bienes registrados esta semana
        $bienesSemana = PermisosHelper::getBienesQuery()
            ->where('activo', true)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        // % bienes en buen estado (primer estado de $estadosConservacion, o calculado)
        $totalConEstado = $conservacionData->sum();
        $buenosCount    = 0;
        foreach ($estadosConservacion as $est) {
            if (str_contains(strtoupper($est->nombre_estado), 'BUEN') ||
                str_contains(strtoupper($est->nombre_estado), 'EXCE') ||
                str_contains(strtoupper($est->nombre_estado), 'OPTIM')) {
                $buenosCount += $est->total;
            }
        }
        $pctBuenEstado = $totalConEstado > 0 ? round(($buenosCount / $totalConEstado) * 100) : 0;

        return view('admin.dashboard', compact(
            'esAdmin',
            'areasDelUsuario',
            'totalAreas',
            'totalTiposBien',
            'totalBienes',
            'totalUbicaciones',
            'movimientosHoy',
            'movimientosSemana',
            'ultimosBienes',
            'ultimosMovimientos',
            'estadosLabels',
            'estadosData',
            'tiposLabels',
            'tiposData',
            'diasLabels',
            'movimientosDias',
            'areasLabels',
            'areasData',
            'ubicacionesLabels',
            'ubicacionesData',
            'porcentajeBuenos',
            'tendenciaMovimientos',
            'movimientosMesActual',
            'movimientosMesAnterior',
            'estadosConservacion',
            'conservacionLabels',
            'conservacionData',
            'bienesSinMovimiento',
            'sparkBienes',
            'sparkMovimientos',
            'bienesInactivos30',
            'bienesSemana',
            'pctBuenEstado'
        ));
    }

    /**
     * ⭐ API para actualizar datos en tiempo real (AJAX)
     */
    public function getStats()
    {
        $user    = auth()->user();
        $esAdmin = $user->esAdmin();
        $idsAreas = $user->getIdsAreasPermitidas();

        $totalBienes = $esAdmin
            ? Bien::where('activo', true)->count()
            : Bien::where('activo', true)
                ->whereHas('movimientos', function ($q) use ($idsAreas) {
                    $q->whereHas('ubicacion', fn($q2) => $q2->whereIn('idarea', $idsAreas));
                })->count();

        $qHoy = Movimiento::whereDate('fecha_mvto', today());
        if (!$esAdmin) {
            $qHoy->whereHas('ubicacion', fn($q) => $q->whereIn('idarea', $idsAreas));
        }

        return response()->json([
            'totalBienes'     => $totalBienes,
            'movimientosHoy'  => $qHoy->count(),
            'ultimoMovimiento' => Movimiento::with('bien')->latest('fecha_mvto')->first(),
            'timestamp'       => now()->format('H:i:s')
        ]);
    }
}
