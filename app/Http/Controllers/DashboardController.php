<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\TipoBien;
use App\Models\Bien;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\TipoMvto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ==================== CONTADORES BÁSICOS ====================
        $totalAreas = Area::count();
        $totalTiposBien = TipoBien::count();
        $totalBienes = Bien::where('activo', true)->count();
        $totalUbicaciones = Ubicacion::count();

        // ==================== MOVIMIENTOS ====================
        $movimientosHoy = Movimiento::whereDate('fecha_mvto', today())->count();

        $movimientosSemana = Movimiento::whereBetween('fecha_mvto', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();

        // ==================== ÚLTIMOS BIENES ====================
        $ultimosBienes = Bien::with(['tipoBien'])
            ->where('activo', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ==================== ÚLTIMOS MOVIMIENTOS ====================
        $ultimosMovimientos = Movimiento::with(['bien', 'tipoMovimiento', 'ubicacion.area'])
            ->orderBy('fecha_mvto', 'desc')
            ->limit(10)
            ->get();

        // ==================== GRÁFICO 1: MOVIMIENTOS POR TIPO ====================
        $movimientosPorTipo = DB::table('movimiento as m')
            ->join('tipo_mvto as tm', 'm.tipo_mvto', '=', 'tm.id_tipo_mvto')
            ->select('tm.tipo_mvto', DB::raw('COUNT(*) as total'))
            ->groupBy('tm.id_tipo_mvto', 'tm.tipo_mvto')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $estadosLabels = $movimientosPorTipo->pluck('tipo_mvto')->map(fn($tipo) => strtoupper($tipo));
        $estadosData = $movimientosPorTipo->pluck('total');

        if ($estadosLabels->isEmpty()) {
            $estadosLabels = collect(['REGISTRO', 'ASIGNACIÓN', 'BAJA']);
            $estadosData = collect([0, 0, 0]);
        }

        // ==================== GRÁFICO 2: BIENES POR TIPO (CORREGIDO) ====================
        // ✅ Usar query directa con nombre correcto de columna
        $bienesPorTipo = DB::table('bien as b')
            ->join('tipo_bien as tb', 'b.id_tipobien', '=', 'tb.id_tipo_bien')
            ->select(
                'tb.nombre_tipo',
                DB::raw('COUNT(b.id_bien) as total')
            )
            ->where('b.activo', true)
            ->whereNotNull('b.id_tipobien')
            ->whereNotNull('tb.nombre_tipo')
            ->groupBy('tb.id_tipo_bien', 'tb.nombre_tipo')
            ->orderByDesc('total')
            ->get();

        $tiposLabels = $bienesPorTipo->pluck('nombre_tipo')->map(fn($tipo) => strtoupper($tipo));
        $tiposData = $bienesPorTipo->pluck('total');

        if ($tiposLabels->isEmpty()) {
            $tiposLabels = collect(['SIN DATOS']);
            $tiposData = collect([0]);
        }

        // ==================== GRÁFICO 3: MOVIMIENTOS ÚLTIMOS 7 DÍAS ====================
        $movimientosDias = [];
        $diasLabels = [];

        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $count = Movimiento::whereDate('fecha_mvto', $fecha->format('Y-m-d'))->count();

            $movimientosDias[] = $count;
            $diasLabels[] = ucfirst($fecha->locale('es')->isoFormat('ddd D'));
        }

        // ==================== GRÁFICO 4: TOP 5 ÁREAS ====================
        $topAreas = DB::table('movimiento as m')
            ->join('ubicacion as u', 'm.idubicacion', '=', 'u.id_ubicacion')
            ->join('area as a', 'u.idarea', '=', 'a.id_area')
            ->select('a.nombre_area', DB::raw('COUNT(DISTINCT m.idbien) as total'))
            ->groupBy('a.id_area', 'a.nombre_area')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $areasLabels = $topAreas->pluck('nombre_area')->map(fn($area) => strtoupper($area));
        $areasData = $topAreas->pluck('total');

        if ($areasLabels->isEmpty()) {
            $areasLabels = collect(['SIN DATOS']);
            $areasData = collect([0]);
        }

        // ==================== GRÁFICO 5: TOP 5 UBICACIONES ====================
        $topUbicaciones = DB::table('movimiento as m')
            ->join('ubicacion as u', 'm.idubicacion', '=', 'u.id_ubicacion')
            ->select('u.nombre_sede', DB::raw('COUNT(DISTINCT m.idbien) as total'))
            ->whereNotNull('m.idubicacion')
            ->groupBy('u.id_ubicacion', 'u.nombre_sede')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $ubicacionesLabels = $topUbicaciones->pluck('nombre_sede')->map(fn($ubi) => strtoupper($ubi));
        $ubicacionesData = $topUbicaciones->pluck('total');

        if ($ubicacionesLabels->isEmpty()) {
            $ubicacionesLabels = collect(['SIN DATOS']);
            $ubicacionesData = collect([0]);
        }

        // ==================== ESTADÍSTICAS ADICIONALES ====================
        $porcentajeBuenos = 0;

        $movimientosMesActual = Movimiento::whereMonth('fecha_mvto', now()->month)
            ->whereYear('fecha_mvto', now()->year)
            ->count();

        $movimientosMesAnterior = Movimiento::whereMonth('fecha_mvto', now()->subMonth()->month)
            ->whereYear('fecha_mvto', now()->subMonth()->year)
            ->count();

        $tendenciaMovimientos = $movimientosMesAnterior > 0
            ? round((($movimientosMesActual - $movimientosMesAnterior) / $movimientosMesAnterior) * 100, 1)
            : 0;

        return view('admin.dashboard', compact(
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
            'movimientosMesAnterior'
        ));
    }

    /**
     * ⭐ API para actualizar datos en tiempo real (AJAX)
     */
    public function getStats()
    {
        return response()->json([
            'totalBienes' => Bien::where('activo', true)->count(),
            'movimientosHoy' => Movimiento::whereDate('fecha_mvto', today())->count(),
            'ultimoMovimiento' => Movimiento::with('bien')->latest('fecha_mvto')->first(),
            'timestamp' => now()->format('H:i:s')
        ]);
    }
}
