@extends('layouts.main')
@section('title', 'Dashboard')

@section('content_header')
<div class="dash-topbar d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1 class="dash-title">
            <i class="fas fa-chart-line dash-icon-title"></i> Dashboard
        </h1>
        <p class="dash-subtitle">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
    </div>
    <div class="d-flex align-items-center flex-wrap mt-2 mt-md-0" style="gap:10px;">
        @if($esAdmin)
            <span class="pill pill-admin"><i class="fas fa-globe-americas"></i> Vista Global</span>
        @else
            @foreach($areasDelUsuario as $area)
                <span class="pill pill-area"><i class="fas fa-building"></i> {{ strtoupper($area->nombre_area) }}</span>
            @endforeach
        @endif
        <span class="pill pill-time" id="lastUpdate"><span class="dot-live"></span> {{ now()->format('H:i:s') }}</span>
    </div>
</div>
@stop

@section('content')


{{-- ============ BANNER DE BIENVENIDA ============ --}}
@php
    $hora   = now()->hour;
    $saludo = $hora < 12 ? '¡Buenos días' : ($hora < 18 ? '¡Buenas tardes' : '¡Buenas noches');
    $emoji  = $hora < 12 ? '☀️' : ($hora < 18 ? '👋' : '🌙');
    $rolDisplay = strtoupper(Auth::user()->rol_usuario);
    $nombreUsuario = Auth::user()->name ?? Auth::user()->email;
@endphp
<div class="welcome-card mb-4 border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 45%, #8b5cf6 100%); border-radius: 20px; box-shadow: 0 12px 30px rgba(59,130,246,0.25);">
    <!-- background decorative shapes -->
    <div style="position:absolute; top:-60px; left:-60px; width:220px; height:220px; background:rgba(255,255,255,0.12); border-radius:50%; filter:blur(24px); pointer-events: none;"></div>
    <div style="position:absolute; bottom:-120px; right:-60px; width:350px; height:350px; background:rgba(255,255,255,0.08); border-radius:50%; filter:blur(30px); pointer-events: none;"></div>
    <div class="welcome-bg-pattern" style="opacity: 0.04;"></div>
    
    <div class="dash-card-body d-flex flex-wrap align-items-center justify-content-between position-relative z-index-1" style="padding: 34px 42px !important;">
        <div class="hero-left pe-lg-4">
            <h3 class="fw-bold mb-2 text-white">{{ $saludo }}, {{ $nombreUsuario }} {{ $emoji }}</h3>
            <p class="text-white-50 mb-4 fs-6">
                @if($esAdmin)
                    Tienes acceso completo al sistema de inventario. Aquí tienes el resumen de hoy.
                @else
                    Bienvenido a tu panel de gestión de inventario.
                @endif
            </p>
            <div class="d-flex flex-wrap gap-2 align-items-center pt-2">
                <span class="badge" style="background: rgba(255,255,255,0.25); color: #fff; padding: 7px 16px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px; backdrop-filter: blur(4px);">
                    @if($esAdmin)
                        <i class="fas fa-shield-alt me-1"></i> {{ $rolDisplay }}
                    @else
                        <i class="fas fa-user-circle me-1"></i> {{ $rolDisplay }}
                    @endif
                </span>
                <span class="badge" style="background: rgba(255,255,255,0.12); color: #fff; padding: 7px 16px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px; border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
                    <i class="fas fa-calendar-alt me-1"></i>
                    {{ now()->locale('es')->isoFormat('dddd, D MMM YYYY') }}
                </span>
                <span class="badge" id="heroClock" style="background: rgba(16, 185, 129, 0.35); color: #fff; padding: 7px 16px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.5px; border: 1px solid rgba(16, 185, 129, 0.4); backdrop-filter: blur(4px);">
                    <i class="fas fa-clock me-1"></i>
                    {{ now()->format('H:i') }}
                </span>
            </div>
        </div>

        <div class="hero-right d-none d-lg-flex align-items-center mt-3 mt-lg-0">
            <div class="welcome-stats me-4 text-end">
                <h3 class="fw-bold text-white mb-0">{{ $totalBienes }}</h3>
                <p class="text-white-50 fw-semibold fs-7 text-uppercase tracking-wide mb-0">Bienes Activos</p>
            </div>
            <div class="illustration-wrap" style="background: rgba(255,255,255,0.15); border-radius: 20px; width: 75px; height: 75px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.25); box-shadow: 0 8px 24px rgba(0,0,0,0.1);">
                <i class="fas fa-box-open text-white floating-icon" style="font-size: 2.2rem; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));"></i>
            </div>
        </div>
    </div>
</div>

{{-- ============ KPI CARDS ============ --}}
<div class="row kpi-row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card">
            <div class="kpi-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="kpi-label text-secondary text-uppercase fw-bold tracking-wide" style="font-size: 0.75rem;">Total de Bienes</div>
                    <div class="kpi-icon-wrap vibrant-primary">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
                <div>
                    <h3 class="kpi-number text-dark fw-bolder mb-1 counter" data-target="{{ $totalBienes }}" style="font-size: 2.2rem; letter-spacing: -1px;">0</h3>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-3">
                    @if($bienesSinMovimiento > 0)
                        <span class="badge bg-warning-subtle text-warning px-2 py-1"><i class="fas fa-exclamation-circle"></i> {{ $bienesSinMovimiento }} sin mod</span>
                    @else
                        <span class="badge bg-success-subtle text-success px-2 py-1"><i class="fas fa-check-circle"></i> 100% activos</span>
                    @endif
                    <a href="{{ route('bien.index') }}" class="text-primary fs-7 fw-bold text-decoration-none">Ver <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card">
            <div class="kpi-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="kpi-label text-secondary text-uppercase fw-bold tracking-wide" style="font-size: 0.75rem;">{{ $esAdmin ? 'Áreas Activas' : 'Mis Áreas' }}</div>
                    <div class="kpi-icon-wrap vibrant-success">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div>
                    <h3 class="kpi-number text-dark fw-bolder mb-1 counter" data-target="{{ $totalAreas }}" style="font-size: 2.2rem; letter-spacing: -1px;">0</h3>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-3">
                    <span class="badge bg-light text-secondary px-2 py-1 border"><i class="fas fa-map-marker-alt"></i> {{ $totalUbicaciones }} const</span>
                    <a href="{{ route('area.index') }}" class="text-success fs-7 fw-bold text-decoration-none">Ver <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card">
            <div class="kpi-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="kpi-label text-secondary text-uppercase fw-bold tracking-wide" style="font-size: 0.75rem;">Tipos de Bien</div>
                    <div class="kpi-icon-wrap vibrant-purple">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
                <div>
                    <h3 class="kpi-number text-dark fw-bolder mb-1 counter" data-target="{{ $totalTiposBien }}" style="font-size: 2.2rem; letter-spacing: -1px;">0</h3>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-3">
                    <span class="badge bg-light text-secondary px-2 py-1 border"><i class="fas fa-layer-group"></i> Categorías</span>
                    <a href="{{ route('tipo-bien.index') }}" class="text-purple fs-7 fw-bold text-decoration-none">Ver <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card">
            <div class="kpi-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="kpi-label text-secondary text-uppercase fw-bold tracking-wide" style="font-size: 0.75rem;">Movto. Hoy</div>
                    <div class="kpi-icon-wrap vibrant-warning">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                <div>
                    <h3 class="kpi-number text-dark fw-bolder mb-1 counter" data-target="{{ $movimientosHoy }}" style="font-size: 2.2rem; letter-spacing: -1px;">0</h3>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-3">
                    @php $tend = $tendenciaMovimientos; @endphp
                    @if($tend > 0)
                        <span class="badge bg-success-subtle text-success px-2 py-1"><i class="fas fa-arrow-trend-up"></i> +{{ $tend }}%</span>
                    @elseif($tend < 0)
                        <span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="fas fa-arrow-trend-down"></i> {{ $tend }}%</span>
                    @else
                        <span class="badge bg-light text-secondary px-2 py-1 border"><i class="fas fa-minus"></i> 0%</span>
                    @endif
                    <a href="{{ route('movimiento.index') }}" class="text-warning fs-7 fw-bold text-decoration-none" style="color:#d97706!important;">Ver <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="trend-bar" style="background: rgba(var(--primary-rgb), 0.03); border: 1px solid rgba(var(--primary-rgb), 0.1);">
            <div class="trend-bar-section-label text-primary">
                <i class="fas fa-exchange-alt me-1"></i> Resumen
            </div>
            <div class="trend-divider" style="background: rgba(var(--primary-rgb), 0.1);"></div>
            <div class="trend-bar-item">
                <span class="trend-label text-dark fw-bold">Mes Actual</span>
                <span class="trend-value text-primary fw-bolder">{{ $movimientosMesActual }}</span>
                <span class="trend-sublabel">movimientos</span>
            </div>
            <div class="trend-divider" style="background: rgba(var(--primary-rgb), 0.1);"></div>
            <div class="trend-bar-item">
                <span class="trend-label text-secondary fw-bold">Mes Anterior</span>
                <span class="trend-value text-dark fw-bolder">{{ $movimientosMesAnterior }}</span>
                <span class="trend-sublabel">movimientos</span>
            </div>
            <div class="trend-divider" style="background: rgba(var(--primary-rgb), 0.1);"></div>
            <div class="trend-bar-item">
                <span class="trend-label text-success fw-bold">Esta Semana</span>
                <span class="trend-value text-success fw-bolder">{{ $movimientosSemana }}</span>
                <span class="trend-sublabel">movimientos</span>
            </div>
            <div class="trend-divider" style="background: rgba(var(--primary-rgb), 0.1);"></div>
            <div class="trend-bar-item">
                <span class="trend-label text-warning fw-bold">Hoy</span>
                <span class="trend-value text-warning fw-bolder">{{ $movimientosHoy }}</span>
                <span class="trend-sublabel">movimientos</span>
            </div>
            <div class="trend-divider" style="background: rgba(var(--primary-rgb), 0.1);"></div>
            <div class="trend-bar-item">
                <span class="trend-label text-dark fw-bold">Tendencia</span>
                <span class="trend-value fw-bolder {{ $tendenciaMovimientos >= 0 ? 'text-success' : 'text-danger' }}">
                    @if($tendenciaMovimientos >= 0)<i class="fas fa-arrow-up fs-6"></i>@else<i class="fas fa-arrow-down fs-6"></i>@endif {{ abs($tendenciaMovimientos) }}%
                </span>
                <span class="trend-sublabel">vs mes anterior</span>
            </div>
        </div>
    </div>
</div>

@php
    $saludTotal = $totalBienes > 0
        ? round((($totalBienes - $bienesSinMovimiento) / $totalBienes) * 100)
        : 0;
    $saludColor = $saludTotal >= 80 ? 'emerald' : ($saludTotal >= 50 ? 'amber' : 'rose');
    $saludHex   = $saludTotal >= 80 ? '#10b981' : ($saludTotal >= 50 ? '#f59e0b' : '#f43f5e');
    $saludLabel = $saludTotal >= 80 ? 'Excelente' : ($saludTotal >= 50 ? 'Regular' : 'Crítico');
    $saludIcon  = $saludTotal >= 80 ? 'fas fa-shield-alt' : ($saludTotal >= 50 ? 'fas fa-exclamation-triangle' : 'fas fa-times-circle');
    $pctActivos = $totalBienes > 0 ? round((($totalBienes - $bienesSinMovimiento)/$totalBienes)*100) : 0;
    $pctInact   = $totalBienes > 0 ? round(($bienesInactivos30/$totalBienes)*100) : 0;
@endphp
<div class="inv-widget mb-4">
    {{-- Header strip --}}
    <div class="inv-widget-header">
        <div class="d-flex align-items-center gap-2">
            <div class="inv-widget-icon"><i class="fas fa-heartbeat"></i></div>
            <div>
                <div class="inv-widget-title">Estado del Inventario</div>
                <div class="inv-widget-sub">Resumen de salud del sistema</div>
            </div>
        </div>
        <span class="inv-health-badge inv-health-{{ $saludColor }}">
            <i class="{{ $saludIcon }}"></i> {{ $saludLabel }}
        </span>
    </div>

    <div class="inv-body">
        {{-- Izquierda: gauge circular SVG --}}
        <div class="inv-gauge-side">
            @php $circumference = 2 * M_PI * 54; $offset = $circumference - ($saludTotal / 100) * $circumference; @endphp
            <div class="inv-circle-wrap">
                <svg width="140" height="140" viewBox="0 0 140 140">
                    <circle cx="70" cy="70" r="54" fill="none" stroke="#f1f5f9" stroke-width="12"/>
                    <circle cx="70" cy="70" r="54" fill="none"
                        stroke="{{ $saludHex }}" stroke-width="12"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $offset }}"
                        transform="rotate(-90 70 70)"
                        style="transition:stroke-dashoffset 1.5s ease;"/>
                </svg>
                <div class="inv-circle-center">
                    <div class="inv-circle-num" style="color:{{ $saludHex }};">{{ $saludTotal }}%</div>
                    <div class="inv-circle-label">Salud</div>
                </div>
            </div>
            <div class="inv-gauge-caption">
                <i class="{{ $saludIcon }}" style="color:{{ $saludHex }};"></i>
                <span style="color:{{ $saludHex }}; font-weight:700;">{{ $saludLabel }}</span>
            </div>
        </div>

        {{-- Derecha: 4 mini-cards en grid --}}
        <div class="inv-cards-grid">
            {{-- Card 1 --}}
            <div class="inv-mini-card">
                <div class="inv-mini-top">
                    <div class="inv-mini-icon" style="background:#d1fae5;">
                        <i class="fas fa-check-circle" style="color:#059669;"></i>
                    </div>
                    <div class="inv-mini-num" style="color:#059669;">{{ $totalBienes - $bienesSinMovimiento }}</div>
                </div>
                <div class="inv-mini-label">Con actividad</div>
                <div class="inv-mini-bar-track">
                    <div class="inv-mini-bar" style="width:{{ $pctActivos }}%; background:#10b981;"></div>
                </div>
                <div class="inv-mini-pct">{{ $pctActivos }}% del total</div>
            </div>

            {{-- Card 2 --}}
            <div class="inv-mini-card">
                <div class="inv-mini-top">
                    <div class="inv-mini-icon" style="background:#fef3c7;">
                        <i class="fas fa-clock" style="color:#d97706;"></i>
                    </div>
                    <div class="inv-mini-num" style="color:#d97706;">{{ $bienesInactivos30 }}</div>
                </div>
                <div class="inv-mini-label">Inactivos (30 días)</div>
                <div class="inv-mini-bar-track">
                    <div class="inv-mini-bar" style="width:{{ $pctInact > 0 ? max($pctInact, 4) : 0 }}%; background:#f59e0b;"></div>
                </div>
                <div class="inv-mini-pct">{{ $pctInact }}% del total</div>
            </div>

            {{-- Card 3 --}}
            <div class="inv-mini-card">
                <div class="inv-mini-top">
                    <div class="inv-mini-icon" style="background:#dbeafe;">
                        <i class="fas fa-plus-circle" style="color:#1d4ed8;"></i>
                    </div>
                    <div class="inv-mini-num" style="color:#1d4ed8;">{{ $bienesSemana }}</div>
                </div>
                <div class="inv-mini-label">Nuevos esta semana</div>
                <div class="inv-mini-bar-track">
                    @php $pctSem = $totalBienes > 0 ? round(($bienesSemana/$totalBienes)*100) : 0; @endphp
                    <div class="inv-mini-bar" style="width:{{ $bienesSemana > 0 ? max($pctSem, 4) : 0 }}%; background:#3b82f6;"></div>
                </div>
                <div class="inv-mini-pct">{{ $bienesSemana > 0 ? '+'.$bienesSemana.' registrados' : 'Sin nuevos' }}</div>
            </div>

            {{-- Card 4 --}}
            <div class="inv-mini-card">
                <div class="inv-mini-top">
                    <div class="inv-mini-icon" style="background:#ede9fe;">
                        <i class="fas fa-star" style="color:#7c3aed;"></i>
                    </div>
                    <div class="inv-mini-num" style="color:#7c3aed;">{{ $pctBuenEstado }}%</div>
                </div>
                <div class="inv-mini-label">Buen estado conserv.</div>
                <div class="inv-mini-bar-track">
                    <div class="inv-mini-bar" style="width:{{ $pctBuenEstado > 0 ? max($pctBuenEstado, 4) : 0 }}%; background:#8b5cf6;"></div>
                </div>
                <div class="inv-mini-pct">{{ $pctBuenEstado > 0 ? $pctBuenEstado.'% en buen estado' : 'Sin datos de estado' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ============ GRÁFICOS PRINCIPALES ============ --}}
<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-icon bg-blue-soft"><i class="fas fa-chart-bar text-blue"></i></span>
                    Movimientos por Tipo
                </div>
                <span class="dash-badge">Top 5</span>
            </div>
            <div class="dash-card-body">
                <canvas id="chartEstados" height="260"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-icon bg-violet-soft"><i class="fas fa-chart-pie text-violet"></i></span>
                    Distribución por Tipo de Bien
                </div>
                <span class="dash-badge">{{ $totalTiposBien }} cat.</span>
            </div>
            <div class="dash-card-body">
                <canvas id="chartTipos" height="260"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ============ TENDENCIA + TOP ÁREAS ============ --}}
<div class="row mb-4">
    <div class="col-lg-8 mb-4">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-icon bg-emerald-soft"><i class="fas fa-chart-line text-emerald"></i></span>
                    Actividad — Últimos 7 días
                </div>
                <span class="dash-badge badge-emerald">{{ $movimientosSemana }} esta semana</span>
            </div>
            <div class="dash-card-body">
                <canvas id="chartMovimientos" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-icon bg-amber-soft"><i class="fas fa-trophy text-amber"></i></span>
                    Top Áreas
                </div>
            </div>
            <div class="dash-card-body">
                <canvas id="chartAreas" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ============ CONSERVACIÓN + UBICACIONES ============ --}}
<div class="row mb-4">
    {{-- Estado de Conservación --}}
    <div class="col-lg-5 mb-4">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-icon bg-rose-soft"><i class="fas fa-heartbeat text-rose"></i></span>
                    Estado de Conservación
                </div>
                <span class="dash-badge">Por bienes</span>
            </div>
            <div class="dash-card-body">
                <canvas id="chartConservacion" height="240"></canvas>
            </div>
            {{-- Leyenda Visual --}}
            <div class="px-3 pb-3">
                @foreach($estadosConservacion as $idx => $est)
                @php
                    $paleta = ['#10b981','#3b82f6','#f59e0b','#f43f5e','#8b5cf6','#6366f1'];
                    $c = $paleta[$idx % count($paleta)];
                    $total = $conservacionData->sum();
                    $pct = $total > 0 ? round(($est->total / $total) * 100) : 0;
                @endphp
                <div class="conserv-row mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-semibold" style="font-size:.8rem;color:#334155;">{{ strtoupper($est->nombre_estado) }}</span>
                        <span style="font-size:.8rem;color:#64748b;">{{ $est->total }} ({{ $pct }}%)</span>
                    </div>
                    <div class="progress-slim">
                        <div class="progress-slim-bar" style="width:{{ $pct }}%;background:{{ $c }};"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Top Ubicaciones --}}
    <div class="col-lg-7 mb-4">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-icon bg-indigo-soft"><i class="fas fa-map-marked-alt text-indigo"></i></span>
                    Top Ubicaciones con más Bienes
                </div>
            </div>
            <div class="dash-card-body">
                <canvas id="chartUbicaciones" height="240"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ============ ACTIVIDAD RECIENTE ============ --}}
<div class="row mb-4">
    {{-- Últimos bienes --}}
    <div class="col-lg-6 mb-4">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-icon bg-blue-soft"><i class="fas fa-box-open text-blue"></i></span>
                    Últimos Bienes Registrados
                </div>
            </div>
            <div class="dash-card-body p-0">
                <div class="recent-table">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Denominación</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosBienes as $bien)
                            <tr class="recent-row">
                                <td><span class="code-badge">{{ $bien->codigo_patrimonial }}</span></td>
                                <td class="text-truncate" style="max-width:160px;" title="{{ $bien->denominacion_bien }}">{{ $bien->denominacion_bien }}</td>
                                <td><span class="type-pill">{{ $bien->tipoBien->nombre_tipo ?? 'N/A' }}</span></td>
                                <td class="text-muted small">{{ $bien->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="empty-state"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Sin bienes</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-card-footer">
                <a href="{{ route('bien.index') }}" class="btn-dash-link"><i class="fas fa-eye"></i> Ver todos los bienes</a>
            </div>
        </div>
    </div>

    {{-- Actividad reciente --}}
    <div class="col-lg-6 mb-4">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-icon bg-rose-soft"><i class="fas fa-history text-rose"></i></span>
                    Actividad Reciente
                </div>
            </div>
            <div class="dash-card-body p-0" style="max-height:380px;overflow-y:auto;">
                <ul class="activity-feed">
                    @forelse($ultimosMovimientos->take(8) as $mov)
                    @php
                        $tipo = strtoupper($mov->tipoMovimiento->tipo_mvto ?? 'MOVIMIENTO');
                        [$icon, $color] = match(true) {
                            str_contains($tipo, 'REGISTRO')  => ['fa-plus-circle',      'emerald'],
                            str_contains($tipo, 'ASIGNAC')   => ['fa-arrow-circle-right','blue'],
                            str_contains($tipo, 'BAJA')      => ['fa-trash-alt',        'rose'],
                            str_contains($tipo, 'TRANSFER')  => ['fa-exchange-alt',     'amber'],
                            default                          => ['fa-circle',           'indigo'],
                        };
                    @endphp
                    <li class="feed-item">
                        <div class="feed-dot bg-{{ $color }}"><i class="fas {{ $icon }}"></i></div>
                        <div class="feed-content">
                            <div class="feed-top">
                                <span class="feed-badge feed-badge-{{ $color }}">{{ $tipo }}</span>
                                <span class="feed-time"><i class="far fa-clock"></i> {{ $mov->fecha_mvto->diffForHumans() }}</span>
                            </div>
                            <div class="feed-desc">{{ Str::limit($mov->bien->denominacion_bien ?? '—', 45) }}</div>
                            @if($mov->ubicacion)
                            <div class="feed-meta">
                                <i class="fas fa-map-marker-alt text-muted"></i> {{ $mov->ubicacion->nombre_sede }}
                                @if($mov->ubicacion->area) · <i class="fas fa-building text-muted"></i> {{ $mov->ubicacion->area->nombre_area }} @endif
                            </div>
                            @endif
                        </div>
                    </li>
                    @empty
                    <li class="empty-state"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Sin movimientos</li>
                    @endforelse
                </ul>
            </div>
            <div class="dash-card-footer">
                <a href="{{ route('movimiento.index') }}" class="btn-dash-link"><i class="fas fa-list"></i> Ver todos los movimientos</a>
            </div>
        </div>
    </div>
</div>

@stop

{{-- ============ CSS ============ --}}
@section('css')
<style>
:root {
    --primary: #6366f1; /* Premium Indigo */
    --primary-rgb: 99, 102, 241;
    --secondary: #64748b;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #0ea5e9;
    --purple: #8b5cf6;
    --dark: #1e293b;
    --light: #f8fafc;
    --bg-color: #f1f5f9;
    --radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -4px rgba(0, 0, 0, 0.02);
    --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
    --tr: all 0.2s ease-in-out;
}

body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: var(--bg-color); color: var(--secondary); }

/* UTIL COLORS */
.text-primary { color: var(--primary) !important; }
.text-secondary { color: var(--secondary) !important; }
.text-success { color: var(--success) !important; }
.text-warning { color: var(--warning) !important; }
.text-danger { color: var(--danger) !important; }
.text-purple { color: var(--purple) !important; }
.text-dark { color: var(--dark) !important; }

.bg-primary-subtle { background-color: rgba(var(--primary-rgb), 0.1) !important; }
.bg-success-subtle { background-color: rgba(16, 185, 129, 0.1) !important; }
.bg-warning-subtle { background-color: rgba(245, 158, 11, 0.1) !important; }
.bg-danger-subtle { background-color: rgba(239, 68, 68, 0.1) !important; }
.bg-purple-subtle { background-color: rgba(139, 92, 246, 0.1) !important; }

/* UTILS TYPOGRAPHY */
.fs-7 { font-size: 0.82rem; }
.tracking-wide { letter-spacing: 0.5px; }

/* ====== WELCOME CARD ====== */
.welcome-card { position: relative; overflow: hidden; border: none; background: linear-gradient(135deg, var(--primary) 0%, #4338ca 100%); border-radius: 16px; box-shadow: 0 12px 30px rgba(99,102,241,0.25); }
.welcome-bg-pattern { position: absolute; inset: 0; opacity: 0.1; background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px; pointer-events: none; }
.illustration-wrap .floating-icon { animation: floatIcon 4s ease-in-out infinite; }
@keyframes floatIcon { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
.hero-pills { display:flex; flex-wrap:wrap; gap:8px; }

/* TOP */
.dash-topbar  { padding: 4px 0 12px; }
.dash-title   { font-size:1.6rem; font-weight:800; color:var(--dark); margin:0; letter-spacing:-0.5px; }
.dash-icon-title { color: var(--primary); margin-right:8px; }
.dash-subtitle { color:var(--secondary); font-size:.87rem; margin:2px 0 0; text-transform:capitalize; }

/* PILLS */
.pill         { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:50px; font-size:.78rem; font-weight:600; }
.pill-admin   { background:rgba(239,68,68,0.1); color:var(--danger); }
.pill-area    { background:rgba(var(--primary-rgb),0.1); color:var(--primary); }
.pill-time    { background:#f8fafc; color:#475569; border:1px solid #e2e8f0; }
.dot-live     { width:8px; height:8px; background:#10b981; border-radius:50%; display:inline-block; animation:blink 1.4s infinite; }
@keyframes blink { 0%,100%{opacity:1}50%{opacity:.3} }

/* KPI CARDS */
.kpi-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.04); transition: var(--tr); display: flex; flex-direction: column; position: relative; overflow: hidden; }
.kpi-card:hover { box-shadow: 0 10px 30px rgba(0,0,0,0.06); transform: translateY(-4px); }
.kpi-body { padding: 24px; flex: 1; z-index: 2; }
.kpi-icon-wrap { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; color: #fff; }

.vibrant-primary { background: linear-gradient(135deg, var(--primary) 0%, #38bdf8 100%); box-shadow: 0 8px 16px rgba(99,102,241,0.25); }
.vibrant-success { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); box-shadow: 0 8px 16px rgba(16,185,129,0.25); }
.vibrant-purple  { background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); box-shadow: 0 8px 16px rgba(139,92,246,0.25); }
.vibrant-warning { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); box-shadow: 0 8px 16px rgba(245,158,11,0.25); }

.kpi-number { transition: color 0.3s; }
.kpi-spark-wrap { width: 100%; height: 45px; opacity: 0.25; overflow: hidden; position: absolute; bottom: 0; left: 0; right: 0; z-index: 1; margin-bottom: -10px; }
.kpi-spark { width: 100% !important; height: 55px !important; }

/* TREND BAR */
.trend-bar { background:#fff; border-radius:var(--radius); box-shadow:var(--shadow); padding:16px 28px; display:flex; align-items:center; flex-wrap:wrap; gap:0; border:1px solid rgba(0,0,0,.06); }
.trend-bar-section-label { font-size:.78rem; font-weight:700; color:#475569; letter-spacing:.3px; display:flex; align-items:center; gap:6px; white-space:nowrap; padding:0 16px 0 4px; }
.trend-bar-item { display:flex; flex-direction:column; align-items:center; flex:1; min-width:100px; padding:8px; }
.trend-label { font-size:.72rem; color:#94a3b8; font-weight:600; letter-spacing:.4px; text-transform:uppercase; margin-bottom:4px; }
.trend-value { font-size:1.35rem; }
.trend-sublabel { font-size:.68rem; color:#94a3b8; margin-top:2px; font-weight:500; }
.trend-divider { width:1px; height:40px; background:#f1f5f9; }
.text-blue    { color:var(--blue)!important; }
.text-emerald { color:var(--emerald)!important; }
.text-amber   { color:var(--amber)!important; }
.text-rose    { color:var(--rose)!important; }
.fw-bold { font-weight:700!important; }
.fw-semibold { font-weight:600!important; }

/* CARDS */
.dash-card { background:#fff; border-radius:var(--radius); box-shadow:var(--shadow); border:1px solid rgba(0,0,0,.06); transition:var(--tr); overflow:hidden; }
.dash-card:hover { box-shadow:var(--shadow-lg); transform:translateY(-2px); }
.dash-card-header { display:flex; align-items:center; justify-content:space-between; padding:18px 22px 14px; border-bottom:1px solid #f1f5f9; }
.dash-card-title  { display:flex; align-items:center; gap:10px; font-size:.93rem; font-weight:700; color:#1e293b; }
.dash-card-icon   { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.9rem; }
.dash-card-body   { padding:18px 22px; }
.dash-card-footer { padding:12px 22px; border-top:1px solid #f1f5f9; text-align:center; }
.btn-dash-link { display:inline-flex; align-items:center; gap:7px; font-size:.82rem; font-weight:600; color:var(--blue); text-decoration:none; padding:6px 16px; border-radius:50px; background:#eff6ff; transition:var(--tr); }
.btn-dash-link:hover { background:var(--blue); color:#fff; }
.dash-badge { display:inline-block; padding:4px 12px; border-radius:50px; font-size:.75rem; font-weight:600; background:#f1f5f9; color:#475569; }
.badge-emerald { background:#d1fae5; color:#065f46; }

/* ICON COLORS */
.bg-blue-soft   {background:#eff6ff} .text-blue    {color:var(--blue)!important}
.bg-emerald-soft{background:#ecfdf5} .text-emerald {color:var(--emerald)!important}
.bg-violet-soft {background:#f5f3ff} .text-violet  {color:var(--violet)!important}
.bg-amber-soft  {background:#fffbeb} .text-amber   {color:var(--amber)!important}
.bg-rose-soft   {background:#fff1f2} .text-rose    {color:var(--rose)!important}
.bg-indigo-soft {background:#eef2ff} .text-indigo  {color:var(--indigo)!important}

/* PROGRESS SLIM */
.progress-slim { height:6px; background:#f1f5f9; border-radius:99px; overflow:hidden; }
.progress-slim-bar { height:100%; border-radius:99px; transition:width 1s ease; }

/* TABLA RECIENTE */
.recent-table .table { margin-bottom: 0; }
.recent-table .table thead th { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--secondary); padding:14px 20px; border-bottom:1px solid #e2e8f0; background:#f8fafc; }
.recent-table .table tbody td { padding:14px 20px; vertical-align:middle; font-size:.85rem; border-color:#f1f5f9; color:var(--dark); }
.recent-row { transition: var(--tr); }
.recent-row:hover { background-color: rgba(var(--primary-rgb), 0.02); }
.code-badge { font-family:'Inter', monospace; font-size:.75rem; font-weight:600; background:#f1f5f9; padding:4px 10px; border-radius:6px; color:var(--secondary); border: 1px solid #e2e8f0; }
.type-pill  { font-size:.7rem; font-weight:600; background:rgba(var(--primary-rgb),0.1); color:var(--primary); padding:4px 12px; border-radius:50px; }
.empty-state { text-align:center; padding:40px 20px; color:var(--secondary); font-size:.9rem; }

/* FEED / TIMELINE */
.activity-feed { list-style:none; margin:0; padding: 20px; position: relative; }
.activity-feed::before { content: ''; position: absolute; left: 36px; top: 30px; bottom: 30px; width: 1px; background: #e2e8f0; }
.feed-item     { display:flex; gap:20px; padding:12px 0 20px 0; position: relative; z-index: 2; transition: transform .15s; }
.feed-item:last-child { padding-bottom: 0; }
.feed-dot { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.75rem; flex-shrink:0; background: #fff; box-shadow: 0 0 0 4px #fff, 0 2px 6px rgba(0,0,0,0.08); z-index: 3; color: var(--primary); }
.feed-dot.bg-emerald { color: var(--success); background: rgba(16, 185, 129, 0.1); }
.feed-dot.bg-blue    { color: var(--info); }
.feed-dot.bg-rose    { color: var(--danger); background: rgba(239, 68, 68, 0.1); }
.feed-dot.bg-amber   { color: var(--warning); background: rgba(245, 158, 11, 0.1); }
.feed-dot.bg-indigo  { color: var(--primary); background: rgba(var(--primary-rgb), 0.1); }
.feed-content { flex:1; min-width:0; background: #f8fafc; padding: 14px 18px; border-radius: 12px; border: 1px solid #f1f5f9; transition: var(--tr); }
.feed-content:hover { background: #fff; box-shadow: var(--shadow-sm); border-color: #e2e8f0; }
.feed-top     { display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; margin-bottom: 6px; }
.feed-badge   { font-size:.65rem; font-weight:700; letter-spacing:.5px; padding:3px 10px; border-radius:50px; text-transform: uppercase; }
.feed-badge-emerald{background:rgba(16, 185, 129, 0.1);color:var(--success)} 
.feed-badge-blue{background:rgba(14, 165, 233, 0.1);color:var(--info)}
.feed-badge-rose{background:rgba(239, 68, 68, 0.1);color:var(--danger)}    
.feed-badge-amber{background:rgba(245, 158, 11, 0.1);color:var(--warning)}
.feed-badge-indigo{background:rgba(var(--primary-rgb), 0.1);color:var(--primary)}
.feed-time { font-size:.72rem; color:var(--secondary); white-space:nowrap; font-weight: 500; }
.feed-desc { font-size:.85rem; color:var(--dark); font-weight:600; margin-top:2px; line-height: 1.4; }
.feed-meta { font-size:.76rem; color:var(--secondary); margin-top:6px; display: flex; align-items: center; gap: 4px; }

/* ====== WIDGET ESTADO DEL INVENTARIO ====== */
.inv-widget {
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid rgba(0,0,0,.06);
    overflow: hidden;
}
.inv-widget-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 26px 16px;
    border-bottom: 1px solid #f1f5f9;
    flex-wrap: wrap;
    gap: 10px;
}
.inv-widget-icon {
    width: 42px; height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg,#ef4444,#f97316);
    display: flex; align-items:center; justify-content:center;
    color: #fff; font-size: 1.1rem;
    animation: pulse-icon 2.5s ease-in-out infinite;
}
@keyframes pulse-icon { 0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.4)} 50%{box-shadow:0 0 0 8px rgba(239,68,68,0)} }
.inv-widget-title { font-size: .95rem; font-weight: 700; color: #1e293b; }
.inv-widget-sub   { font-size: .75rem; color: #94a3b8; margin-top: 1px; }
.inv-health-badge {
    display: inline-flex; align-items:center; gap:6px;
    padding: 6px 16px; border-radius: 50px;
    font-size: .78rem; font-weight: 700;
}
.inv-health-emerald { background:#d1fae5; color:#065f46; }
.inv-health-amber   { background:#fef3c7; color:#92400e; }
.inv-health-rose    { background:#ffe4e6; color:#be123c; }

/* Métricas */
.inv-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 0;
}
.inv-metric {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 18px 26px;
    border-right: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    transition: background .15s;
}
.inv-metric:hover { background: #f8fafc; }
.inv-metric:last-child { border-right: none; }
.inv-metric-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items:center; justify-content:center;
    font-size: 1.1rem; flex-shrink: 0;
}
.inv-metric-body { flex:1; min-width:0; }
.inv-metric-num   { font-size: 1.6rem; font-weight: 800; color: #1e293b; line-height:1; }
.inv-metric-label { font-size: .74rem; color: #64748b; margin-top:3px; font-weight:500; }
.inv-metric-bar-wrap { display:flex; align-items:center; gap:8px; margin-top:10px; width:100%; }
.inv-metric-bar-track {
    flex:1; height:6px; background:#f1f5f9;
    border-radius:99px; overflow:hidden;
}
.inv-metric-bar {
    height:100%; border-radius:99px;
    transition: width 1.2s cubic-bezier(.4,0,.2,1);
}
.inv-metric-pct { font-size:.72rem; font-weight:700; white-space:nowrap; }

/* Gauge */
.inv-gauge-wrap {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 26px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}
.inv-gauge-label { font-size:.78rem; font-weight:700; color:#475569; white-space:nowrap; min-width:100px; }
.inv-gauge-track {
    flex:1; height:10px; background:#e2e8f0;
    border-radius:99px; overflow:hidden;
}
.inv-gauge-fill {
    height:100%; border-radius:99px;
    transition: width 1.5s cubic-bezier(.4,0,.2,1);
}
.inv-gauge-emerald { background:linear-gradient(90deg,#10b981,#34d399); }
.inv-gauge-amber   { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
.inv-gauge-rose    { background:linear-gradient(90deg,#f43f5e,#fb7185); }
.inv-gauge-num { font-size:1rem; font-weight:800; min-width:44px; text-align:right; }
.inv-gauge-num-emerald { color:#059669; }
.inv-gauge-num-amber   { color:#d97706; }
.inv-gauge-num-rose    { color:#e11d48; }

/* ── nuevo layout inv-widget ── */
.inv-body {
    display: flex;
    align-items: stretch;
    gap: 0;
}
/* Lado izquierdo: gauge circular */
.inv-gauge-side {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 28px 32px;
    border-right: 1px solid #f1f5f9;
    min-width: 180px;
    gap: 10px;
}
.inv-circle-wrap {
    position: relative;
    width: 140px; height: 140px;
}
.inv-circle-center {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}
.inv-circle-num   { font-size: 1.7rem; font-weight: 900; line-height: 1; }
.inv-circle-label { font-size: .7rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }
.inv-gauge-caption { display:flex; align-items:center; gap:6px; font-size:.82rem; }

/* Lado derecho: 4 mini-cards en grid 2x2 */
.inv-cards-grid {
    flex: 1;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}
.inv-mini-card {
    padding: 20px 24px;
    border-right: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    transition: background .15s;
}
.inv-mini-card:hover { background: #f8fafc; }
.inv-mini-card:nth-child(2n) { border-right: none; }
.inv-mini-card:nth-child(3),
.inv-mini-card:nth-child(4) { border-bottom: none; }

.inv-mini-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.inv-mini-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items:center; justify-content:center;
    font-size: 1rem;
}
.inv-mini-num   { font-size: 1.8rem; font-weight: 900; line-height: 1; }
.inv-mini-label { font-size: .74rem; color: #64748b; font-weight: 500; margin-bottom: 10px; }
.inv-mini-bar-track {
    width: 100%; height: 5px; background: #f1f5f9;
    border-radius: 99px; overflow: hidden; margin-bottom: 5px;
}
.inv-mini-bar {
    height: 100%; border-radius: 99px;
    transition: width 1.2s cubic-bezier(.4,0,.2,1);
}
.inv-mini-pct { font-size: .68rem; color: #94a3b8; font-weight: 500; }

@media (max-width: 768px) {
    .inv-gauge-side { display: none; }
    .inv-cards-grid { grid-template-columns: 1fr 1fr; }
}

/* ANIMATIONS */
@keyframes fadeUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
.kpi-card,.dash-card,.trend-bar,.quick-actions { animation:fadeUp .5s ease both; }
.kpi-row .col-xl-3:nth-child(1) .kpi-card{animation-delay:.05s}
.kpi-row .col-xl-3:nth-child(2) .kpi-card{animation-delay:.12s}
.kpi-row .col-xl-3:nth-child(3) .kpi-card{animation-delay:.19s}
.kpi-row .col-xl-3:nth-child(4) .kpi-card{animation-delay:.26s}

/* SCROLLBAR */
::-webkit-scrollbar{width:5px} ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px}
</style>
@stop

{{-- ============ JS ============ --}}
@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function(){

    // ── Primary Palette ──
    const pal = {
        blue:   ['rgba(99,102,241,.85)','rgba(99,102,241,1)'], /* Indigo instead of blue */
        emerald:['rgba(16,185,129,.85)','rgba(16,185,129,1)'],
        violet: ['rgba(139,92,246,.85)','rgba(139,92,246,1)'],
        amber:  ['rgba(245,158,11,.85)','rgba(245,158,11,1)'],
        rose:   ['rgba(244,63,94,.85)', 'rgba(244,63,94,1)'],
        indigo: ['rgba(56,189,248,.85)','rgba(56,189,248,1)'], /* Sky Blue */
    };
    const colors = Object.values(pal);

    Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#64748b';
    Chart.defaults.animation   = { duration:900, easing:'easeOutQuart' };

    const tip = { backgroundColor:'rgba(15,23,42,.92)', titleColor:'#f1f5f9', bodyColor:'#cbd5e1', padding:12, cornerRadius:10, boxPadding:5 };

    // ── Gráfico 1: Barras movimientos por tipo ──
    new Chart(document.getElementById('chartEstados'),{type:'bar',data:{
        labels:@json($estadosLabels),
        datasets:[{label:'Movimientos',data:@json($estadosData),
            backgroundColor:colors.map(c=>c[0]),borderWidth:0,borderRadius:10,borderSkipped:false}]
    },options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{...tip}},
        scales:{y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'rgba(0,0,0,.04)'},border:{dash:[4,4]}},x:{grid:{display:false}}}}});

    // ── Gráfico 2: Dona tipos de bien ──
    new Chart(document.getElementById('chartTipos'),{type:'doughnut',data:{
        labels:@json($tiposLabels),
        datasets:[{data:@json($tiposData),backgroundColor:colors.map(c=>c[0]),borderColor:'#fff',borderWidth:4,hoverOffset:14}]
    },options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{
        legend:{position:'right',labels:{padding:14,usePointStyle:true,pointStyleWidth:10,
            generateLabels:chart=>chart.data.labels.map((lbl,i)=>({text:`${lbl}: ${chart.data.datasets[0].data[i]}`,fillStyle:chart.data.datasets[0].backgroundColor[i],hidden:false,index:i}))}},
        tooltip:{...tip}}}});

    // ── Gráfico 3: Línea 7 días ──
    new Chart(document.getElementById('chartMovimientos'),{type:'line',data:{
        labels:@json($diasLabels),
        datasets:[{label:'Movimientos',data:@json($movimientosDias),
            borderColor:pal.emerald[1],backgroundColor:'rgba(16,185,129,.08)',
            borderWidth:3,fill:true,tension:.45,pointRadius:5,pointHoverRadius:8,
            pointBackgroundColor:'#fff',pointBorderColor:pal.emerald[1],pointBorderWidth:2.5}]
    },options:{responsive:true,maintainAspectRatio:false,
        plugins:{legend:{display:true,labels:{usePointStyle:true}},tooltip:{...tip}},
        scales:{y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'rgba(0,0,0,.04)'},border:{dash:[4,4]}},x:{grid:{display:false}}}}});

    // ── Gráfico 4: Barras horizontales top áreas ──
    new Chart(document.getElementById('chartAreas'),{type:'bar',data:{
        labels:@json($areasLabels),
        datasets:[{label:'Bienes',data:@json($areasData),backgroundColor:colors.map(c=>c[0]),borderWidth:0,borderRadius:8}]
    },options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,
        plugins:{legend:{display:false},tooltip:{...tip}},
        scales:{x:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'rgba(0,0,0,.04)'}},y:{grid:{display:false}}}}});

    // ── Gráfico 5: Dona estado conservación ──
    new Chart(document.getElementById('chartConservacion'),{type:'doughnut',data:{
        labels:@json($conservacionLabels),
        datasets:[{data:@json($conservacionData),
            backgroundColor:['rgba(16,185,129,.85)','rgba(59,130,246,.85)','rgba(245,158,11,.85)','rgba(244,63,94,.85)','rgba(139,92,246,.85)'],
            borderColor:'#fff',borderWidth:4,hoverOffset:12}]
    },options:{responsive:true,maintainAspectRatio:false,cutout:'60%',
        plugins:{legend:{display:false},tooltip:{...tip}}}});

    // ── Gráfico 6: Barras ubicaciones ──
    new Chart(document.getElementById('chartUbicaciones'),{type:'bar',data:{
        labels:@json($ubicacionesLabels),
        datasets:[{label:'Bienes',data:@json($ubicacionesData),
            backgroundColor:[pal.blue[0],pal.violet[0],pal.emerald[0],pal.amber[0],pal.rose[0]],
            borderWidth:0,borderRadius:10,borderSkipped:false}]
    },options:{responsive:true,maintainAspectRatio:false,
        plugins:{legend:{display:false},tooltip:{...tip}},
        scales:{y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'rgba(0,0,0,.04)'},border:{dash:[4,4]}},x:{grid:{display:false}}}}});

    // ── Contadores animados ──
    function animateCounter(el){
        const target = parseInt(el.dataset.target)||0;
        const step   = Math.max(1, Math.ceil(target/60));
        let   cur    = 0;
        const timer  = setInterval(()=>{
            cur = Math.min(cur+step, target);
            el.textContent = cur.toLocaleString();
            if(cur >= target) clearInterval(timer);
        },25);
    }
    document.querySelectorAll('.counter').forEach(animateCounter);

    // ── Reloj ──
    setInterval(()=>{
        const n=new Date();
        const hms=[n.getHours(),n.getMinutes(),n.getSeconds()].map(v=>String(v).padStart(2,'0')).join(':');
        const hm =[n.getHours(),n.getMinutes()].map(v=>String(v).padStart(2,'0')).join(':');
        $('#lastUpdate').html(`<span class="dot-live"></span> ${hms}`);
        $('#heroClock').html(`<i class="fas fa-clock"></i> ${hm}`);
    },1000);

    // ── SPARKLINES ──
    function makeSpark(id, data, color) {
        const el = document.getElementById(id);
        if (!el) return;
        new Chart(el, {
            type: 'line',
            data: {
                labels: Array(data.length).fill(''),
                datasets: [{
                    data: data,
                    borderColor: color,
                    borderWidth: 2.5,
                    pointRadius: 0,
                    fill: true,
                    backgroundColor: (ctx) => {
                        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 52);
                        g.addColorStop(0, color.replace(')', ', 0.35)').replace('rgb', 'rgba'));
                        g.addColorStop(1, color.replace(')', ', 0)').replace('rgb', 'rgba'));
                        return g;
                    },
                    tension: 0.45,
                }]
            },
            options: {
                responsive: false,
                animation: { duration: 900, easing: 'easeInOutQuart' },
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    x: { display: false },
                    y: { display: false, beginAtZero: true }
                },
                elements: { line: { borderCapStyle: 'round' } }
            }
        });
    }

    makeSpark('sparkBienes',      @json($sparkBienes),      'rgb(255,255,255)');
    makeSpark('sparkAreas',       @json(collect($areasData)->values()->toArray()),  'rgb(255,255,255)');
    makeSpark('sparkTipos',       @json(collect($tiposData)->values()->toArray()),  'rgb(255,255,255)');
    makeSpark('sparkMovimientos', @json($sparkMovimientos), 'rgb(255,255,255)');

});
</script>
@stop
