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
<div class="welcome-hero mb-4">
    {{-- Orbes decorativos de fondo --}}
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-orb hero-orb-3"></div>

    <div class="hero-left">
        <p class="hero-greeting">{{ $saludo }}, {{ $emoji }}</p>
        <h2 class="hero-name">{{ $nombreUsuario }}</h2>
        <p class="hero-sub">
            @if($esAdmin)
                Tienes acceso completo al sistema de inventario.
            @else
                Bienvenido a tu panel de gestión de inventario.
            @endif
        </p>
        <div class="hero-pills">
            <span class="hero-pill hero-pill-role">
                @if($esAdmin)
                    <i class="fas fa-shield-alt"></i> {{ $rolDisplay }}
                @else
                    <i class="fas fa-user-circle"></i> {{ $rolDisplay }}
                @endif
            </span>
            <span class="hero-pill hero-pill-date">
                <i class="fas fa-calendar-check"></i>
                {{ now()->locale('es')->isoFormat('dddd, D MMM YYYY') }}
            </span>
            <span class="hero-pill hero-pill-time" id="heroClock">
                <i class="fas fa-clock"></i>
                {{ now()->format('H:i') }}
            </span>
        </div>
    </div>

    <div class="hero-right">
        {{-- SVG decorativo: inventario --}}
        <svg class="hero-svg" viewBox="0 0 260 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Fondo suave -->
            <ellipse cx="130" cy="170" rx="100" ry="18" fill="rgba(255,255,255,0.08)"/>

            <!-- Caja grande (base) -->
            <rect x="50" y="110" width="80" height="70" rx="8" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.4)" stroke-width="1.5"/>
            <rect x="56" y="116" width="68" height="58" rx="5" fill="rgba(255,255,255,0.08)"/>
            <!-- Líneas en caja -->
            <line x1="65" y1="128" x2="105" y2="128" stroke="rgba(255,255,255,0.5)" stroke-width="2" stroke-linecap="round"/>
            <line x1="65" y1="140" x2="95" y2="140" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="65" y1="152" x2="100" y2="152" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" stroke-linecap="round"/>

            <!-- Caja pequeña (encima a la derecha) -->
            <rect x="140" y="130" width="60" height="50" rx="6" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.35)" stroke-width="1.5"/>
            <rect x="146" y="136" width="48" height="38" rx="4" fill="rgba(255,255,255,0.06)"/>
            <line x1="153" y1="146" x2="183" y2="146" stroke="rgba(255,255,255,0.5)" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="153" y1="156" x2="178" y2="156" stroke="rgba(255,255,255,0.3)" stroke-width="1.5" stroke-linecap="round"/>

            <!-- Portapapeles (clip superior) -->
            <rect x="75" y="70" width="56" height="50" rx="6" fill="rgba(255,255,255,0.22)" stroke="rgba(255,255,255,0.45)" stroke-width="1.5"/>
            <rect x="88" y="63" width="30" height="12" rx="6" fill="rgba(255,255,255,0.35)" stroke="rgba(255,255,255,0.5)" stroke-width="1.2"/>
            <line x1="85" y1="88" x2="117" y2="88" stroke="rgba(255,255,255,0.6)" stroke-width="2" stroke-linecap="round"/>
            <line x1="85" y1="98" x2="110" y2="98" stroke="rgba(255,255,255,0.4)" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="85" y1="108" x2="114" y2="108" stroke="rgba(255,255,255,0.4)" stroke-width="1.5" stroke-linecap="round"/>
            <!-- Check -->
            <circle cx="122" cy="88" r="7" fill="rgba(52,211,153,0.7)"/>
            <polyline points="118,88 121,91 126,84" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

            <!-- Estrella decorativa -->
            <circle cx="195" cy="75" r="12" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
            <text x="195" y="80" text-anchor="middle" font-size="14">📦</text>

            <!-- Puntos flotantes -->
            <circle cx="45" cy="85" r="4" fill="rgba(255,255,255,0.3)"/>
            <circle cx="215" cy="120" r="3" fill="rgba(255,255,255,0.25)"/>
            <circle cx="60" cy="150" r="2.5" fill="rgba(255,255,255,0.2)"/>
            <circle cx="220" cy="90" r="5" fill="rgba(255,255,255,0.15)"/>
        </svg>

        {{-- Tarjeta de stat flotante --}}
        <div class="hero-stat-card">
            <div class="hero-stat-icon"><i class="fas fa-boxes"></i></div>
            <div>
                <div class="hero-stat-num">{{ $totalBienes }}</div>
                <div class="hero-stat-label">Bienes Activos</div>
            </div>
        </div>
    </div>
</div>

{{-- ============ KPI CARDS ============ --}}
<div class="row kpi-row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon-wrap"><i class="fas fa-boxes"></i></div>
            <div class="kpi-body">
                <div class="kpi-number counter" data-target="{{ $totalBienes }}">0</div>
                <div class="kpi-label">Total de Bienes</div>
                <div class="kpi-sub text-white-50 mt-1" style="font-size:.75rem;">
                    @if($bienesSinMovimiento > 0)
                        <i class="fas fa-exclamation-circle"></i> {{ $bienesSinMovimiento }} sin movimientos
                    @else
                        <i class="fas fa-check-circle"></i> Todos con actividad
                    @endif
                </div>
            </div>
            <div class="kpi-spark-wrap">
                <canvas id="sparkBienes" class="kpi-spark"></canvas>
            </div>
            <a href="{{ route('bien.index') }}" class="kpi-link">Ver bienes <i class="fas fa-arrow-right"></i></a>
            <div class="kpi-glow"></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card kpi-emerald">
            <div class="kpi-icon-wrap"><i class="fas fa-building"></i></div>
            <div class="kpi-body">
                <div class="kpi-number counter" data-target="{{ $totalAreas }}">0</div>
                <div class="kpi-label">{{ $esAdmin ? 'Áreas Activas' : 'Mis Áreas' }}</div>
                <div class="kpi-sub text-white-50 mt-1" style="font-size:.75rem;">
                    <i class="fas fa-map-marker-alt"></i> {{ $totalUbicaciones }} ubicaciones
                </div>
            </div>
            <div class="kpi-spark-wrap">
                <canvas id="sparkAreas" class="kpi-spark"></canvas>
            </div>
            <a href="{{ route('area.index') }}" class="kpi-link">Ver áreas <i class="fas fa-arrow-right"></i></a>
            <div class="kpi-glow"></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card kpi-violet">
            <div class="kpi-icon-wrap"><i class="fas fa-tags"></i></div>
            <div class="kpi-body">
                <div class="kpi-number counter" data-target="{{ $totalTiposBien }}">0</div>
                <div class="kpi-label">Tipos de Bien</div>
                <div class="kpi-sub text-white-50 mt-1" style="font-size:.75rem;">
                    <i class="fas fa-layer-group"></i> Categorías registradas
                </div>
            </div>
            <div class="kpi-spark-wrap">
                <canvas id="sparkTipos" class="kpi-spark"></canvas>
            </div>
            <a href="{{ route('tipo-bien.index') }}" class="kpi-link">Ver tipos <i class="fas fa-arrow-right"></i></a>
            <div class="kpi-glow"></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="kpi-card kpi-amber">
            <div class="kpi-icon-wrap"><i class="fas fa-exchange-alt"></i></div>
            <div class="kpi-body">
                <div class="kpi-number counter" data-target="{{ $movimientosHoy }}">0</div>
                <div class="kpi-label">Movimientos Hoy</div>
                <div class="kpi-sub text-white-50 mt-1" style="font-size:.75rem;">
                    @php $tend = $tendenciaMovimientos; @endphp
                    @if($tend > 0)
                        <i class="fas fa-arrow-trend-up"></i> +{{ $tend }}% vs mes anterior
                    @elseif($tend < 0)
                        <i class="fas fa-arrow-trend-down"></i> {{ $tend }}% vs mes anterior
                    @else
                        <i class="fas fa-minus"></i> Sin cambio vs mes anterior
                    @endif
                </div>
            </div>
            <div class="kpi-spark-wrap">
                <canvas id="sparkMovimientos" class="kpi-spark"></canvas>
            </div>
            <a href="{{ route('movimiento.index') }}" class="kpi-link">Ver movimientos <i class="fas fa-arrow-right"></i></a>
            <div class="kpi-glow"></div>
        </div>
    </div>
</div>

{{-- ============ FILA: BARRA DE COMPARATIVO MES ============ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="trend-bar">
            <div class="trend-bar-section-label">
                <i class="fas fa-exchange-alt"></i> Resumen de Movimientos
            </div>
            <div class="trend-divider"></div>
            <div class="trend-bar-item">
                <span class="trend-label"><i class="fas fa-calendar-alt"></i> Mes Actual</span>
                <span class="trend-value text-blue fw-bold">{{ $movimientosMesActual }}</span>
                <span class="trend-sublabel">movimientos</span>
            </div>
            <div class="trend-divider"></div>
            <div class="trend-bar-item">
                <span class="trend-label"><i class="fas fa-history"></i> Mes Anterior</span>
                <span class="trend-value text-muted fw-bold">{{ $movimientosMesAnterior }}</span>
                <span class="trend-sublabel">movimientos</span>
            </div>
            <div class="trend-divider"></div>
            <div class="trend-bar-item">
                <span class="trend-label"><i class="fas fa-chart-line"></i> Esta Semana</span>
                <span class="trend-value text-emerald fw-bold">{{ $movimientosSemana }}</span>
                <span class="trend-sublabel">movimientos</span>
            </div>
            <div class="trend-divider"></div>
            <div class="trend-bar-item">
                <span class="trend-label"><i class="fas fa-sun"></i> Hoy</span>
                <span class="trend-value text-amber fw-bold">{{ $movimientosHoy }}</span>
                <span class="trend-sublabel">movimientos</span>
            </div>
            <div class="trend-divider"></div>
            <div class="trend-bar-item">
                <span class="trend-label"><i class="fas fa-arrow-{{ $tendenciaMovimientos >= 0 ? 'up' : 'down' }} {{ $tendenciaMovimientos >= 0 ? 'text-emerald' : 'text-rose' }}"></i> Tendencia</span>
                <span class="trend-value fw-bold {{ $tendenciaMovimientos >= 0 ? 'text-emerald' : 'text-rose' }}">
                    {{ $tendenciaMovimientos >= 0 ? '+' : '' }}{{ $tendenciaMovimientos }}%
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
    --blue:    #3b82f6; --emerald: #10b981; --violet: #8b5cf6;
    --amber:   #f59e0b; --rose:    #f43f5e; --indigo: #6366f1;
    --radius:  16px;
    --shadow:  0 4px 24px rgba(0,0,0,0.08);
    --shadow-lg: 0 8px 40px rgba(0,0,0,0.13);
    --tr: all 0.3s cubic-bezier(.4,0,.2,1);
}

/* ====== WELCOME HERO BANNER ====== */
.welcome-hero {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 45%, #8b5cf6 100%);
    padding: 36px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 180px;
    box-shadow: 0 12px 40px rgba(59,130,246,0.35);
    animation: fadeUp .5s ease both;
}

/* Orbes de fondo */
.hero-orb {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.07);
    pointer-events: none;
    animation: floatOrb 6s ease-in-out infinite;
}
.hero-orb-1 { width:260px; height:260px; top:-80px; right:200px; animation-delay:0s; }
.hero-orb-2 { width:180px; height:180px; bottom:-60px; left:30%;  animation-delay:2s; }
.hero-orb-3 { width:120px; height:120px; top:20px;   right:180px; animation-delay:4s; background:rgba(255,255,255,0.05); }
@keyframes floatOrb { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-14px)} }

/* Lado izquierdo */
.hero-left  { z-index:2; flex:1; }
.hero-greeting {
    font-size: .88rem;
    font-weight: 600;
    color: rgba(255,255,255,0.75);
    letter-spacing: .5px;
    margin: 0 0 4px;
    text-transform: uppercase;
}
.hero-name {
    font-size: 2rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 8px;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.15);
}
.hero-sub {
    font-size: .88rem;
    color: rgba(255,255,255,0.72);
    margin: 0 0 18px;
}
.hero-pills { display:flex; flex-wrap:wrap; gap:8px; }
.hero-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 50px;
    font-size: .75rem;
    font-weight: 600;
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,0.25);
    color: #fff;
    letter-spacing: .2px;
}
.hero-pill-role { background: rgba(255,255,255,0.2); }
.hero-pill-date { background: rgba(255,255,255,0.12); }
.hero-pill-time { background: rgba(52,211,153,0.25); border-color:rgba(52,211,153,0.4); }

/* Lado derecho */
.hero-right {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-end;
    gap: 16px;
    flex-shrink: 0;
}
.hero-svg {
    width: 220px;
    height: 170px;
    filter: drop-shadow(0 8px 24px rgba(0,0,0,0.12));
    animation: floatSvg 4s ease-in-out infinite;
}
@keyframes floatSvg { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }

/* Tarjeta flotante de stat */
.hero-stat-card {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 14px;
    padding: 14px 18px;
    min-width: 130px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    animation: fadeUp .7s .2s ease both;
}
.hero-stat-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: rgba(255,255,255,0.2);
    display: flex; align-items:center; justify-content:center;
    font-size: 1.1rem;
    color: #fff;
}
.hero-stat-num   { font-size: 1.5rem; font-weight: 800; color: #fff; line-height:1; }
.hero-stat-label { font-size: .72rem; color: rgba(255,255,255,0.72); font-weight:500; margin-top:2px; }

/* Responsive */
@media (max-width:768px) {
    .hero-right  { display:none; }
    .welcome-hero { padding:24px 24px; }
    .hero-name   { font-size:1.5rem; }
}


/* TOP */
.dash-topbar  { padding: 4px 0 12px; }
.dash-title   { font-size:1.7rem; font-weight:800; color:#0f172a; margin:0; letter-spacing:-0.5px; }
.dash-icon-title { background:linear-gradient(135deg,#3b82f6,#8b5cf6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; margin-right:8px; }
.dash-subtitle { color:#64748b; font-size:.87rem; margin:2px 0 0; text-transform:capitalize; }

/* PILLS */
.pill         { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:50px; font-size:.78rem; font-weight:600; }
.pill-admin   { background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; }
.pill-area    { background:linear-gradient(135deg,#3b82f6,#6366f1); color:#fff; }
.pill-time    { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
.dot-live     { width:8px; height:8px; background:#10b981; border-radius:50%; display:inline-block; animation:blink 1.4s infinite; }
@keyframes blink { 0%,100%{opacity:1}50%{opacity:.3} }

/* QUICK ACTIONS */
.quick-actions { display:flex; flex-wrap:wrap; gap:10px; }
.qa-btn {
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 18px; border-radius:50px; font-size:.82rem; font-weight:600;
    text-decoration:none; transition:var(--tr); white-space:nowrap;
    border:2px solid transparent;
}
.qa-btn:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,0.15); text-decoration:none; }
.qa-blue   { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; } .qa-blue:hover   { background:#1d4ed8; color:#fff; }
.qa-emerald{ background:#ecfdf5; color:#065f46; border-color:#a7f3d0; } .qa-emerald:hover{ background:#059669; color:#fff; }
.qa-violet { background:#f5f3ff; color:#5b21b6; border-color:#ddd6fe; } .qa-violet:hover { background:#7c3aed; color:#fff; }
.qa-amber  { background:#fffbeb; color:#92400e; border-color:#fde68a; } .qa-amber:hover  { background:#d97706; color:#fff; }
.qa-rose   { background:#fff1f2; color:#be123c; border-color:#fecdd3; } .qa-rose:hover   { background:#e11d48; color:#fff; }
.qa-indigo { background:#eef2ff; color:#3730a3; border-color:#c7d2fe; } .qa-indigo:hover { background:#4338ca; color:#fff; }

/* KPI */
.kpi-card { position:relative; border-radius:var(--radius); padding:28px 24px 20px; overflow:hidden; cursor:pointer; transition:var(--tr); box-shadow:var(--shadow); display:flex; flex-direction:column; min-height:180px; color:#fff; }
.kpi-card:hover { transform:translateY(-6px); box-shadow:var(--shadow-lg); }
.kpi-blue    { background:linear-gradient(135deg,#1d4ed8,#3b82f6,#60a5fa); }
.kpi-emerald { background:linear-gradient(135deg,#059669,#10b981,#34d399); }
.kpi-violet  { background:linear-gradient(135deg,#6d28d9,#8b5cf6,#a78bfa); }
.kpi-amber   { background:linear-gradient(135deg,#d97706,#f59e0b,#fbbf24); }
.kpi-icon-wrap { position:absolute; top:20px; right:20px; width:60px; height:60px; border-radius:50%; background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; font-size:1.6rem; backdrop-filter:blur(4px); transition:var(--tr); }
.kpi-card:hover .kpi-icon-wrap { transform:rotate(-8deg) scale(1.1); background:rgba(255,255,255,.25); }
.kpi-body { flex:1; }
.kpi-number { font-size:3rem; font-weight:900; line-height:1; letter-spacing:-2px; text-shadow:0 2px 8px rgba(0,0,0,.15); }
.kpi-label  { font-size:.85rem; font-weight:500; opacity:.88; margin-top:6px; }
.kpi-link   { display:inline-flex; align-items:center; gap:6px; font-size:.78rem; font-weight:600; color:rgba(255,255,255,.85); text-decoration:none; margin-top:12px; border-top:1px solid rgba(255,255,255,.2); padding-top:10px; transition:color .2s; }
.kpi-link:hover { color:#fff; }
.kpi-glow   { position:absolute; bottom:-30px; right:-30px; width:120px; height:120px; border-radius:50%; background:rgba(255,255,255,.08); pointer-events:none; }
.kpi-spark-wrap { width:100%; height:52px; position:relative; margin-top:8px; margin-bottom:4px; }
.kpi-spark { width:100%!important; height:52px!important; display:block; opacity:.75; }

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
.recent-table .table thead th { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#94a3b8; padding:10px 16px; border:none; background:#f8fafc; }
.recent-table .table tbody td { padding:11px 16px; vertical-align:middle; font-size:.85rem; border-color:#f1f5f9; color:#334155; }
.recent-row:hover { background:#f8fafc; }
.code-badge { font-family:monospace; font-size:.78rem; font-weight:700; background:#f1f5f9; padding:3px 8px; border-radius:6px; color:#334155; }
.type-pill  { font-size:.72rem; font-weight:600; background:#eff6ff; color:var(--blue); padding:3px 9px; border-radius:50px; }
.empty-state { text-align:center; padding:32px; color:#94a3b8; font-size:.9rem; }

/* FEED */
.activity-feed { list-style:none; margin:0; padding:0; }
.feed-item     { display:flex; gap:14px; padding:14px 20px; border-bottom:1px solid #f1f5f9; transition:background .15s; }
.feed-item:hover { background:#f8fafc; }
.feed-item:last-child { border-bottom:none; }
.feed-dot { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.75rem; flex-shrink:0; color:#fff; }
.bg-emerald{background:var(--emerald)} .bg-blue{background:var(--blue)}
.bg-rose{background:var(--rose)}       .bg-amber{background:var(--amber)} .bg-indigo{background:var(--indigo)}
.feed-content { flex:1; min-width:0; }
.feed-top     { display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; }
.feed-badge   { font-size:.68rem; font-weight:700; letter-spacing:.4px; padding:2px 9px; border-radius:50px; }
.feed-badge-emerald{background:#d1fae5;color:#065f46} .feed-badge-blue{background:#dbeafe;color:#1d4ed8}
.feed-badge-rose{background:#ffe4e6;color:#be123c}    .feed-badge-amber{background:#fef3c7;color:#92400e}
.feed-badge-indigo{background:#e0e7ff;color:#3730a3}
.feed-time { font-size:.72rem; color:#94a3b8; white-space:nowrap; }
.feed-desc { font-size:.83rem; color:#1e293b; font-weight:600; margin-top:3px; }
.feed-meta { font-size:.75rem; color:#64748b; margin-top:2px; }

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

    // ── Paleta ──
    const pal = {
        blue:   ['rgba(59,130,246,.85)','rgba(59,130,246,1)'],
        emerald:['rgba(16,185,129,.85)','rgba(16,185,129,1)'],
        violet: ['rgba(139,92,246,.85)','rgba(139,92,246,1)'],
        amber:  ['rgba(245,158,11,.85)','rgba(245,158,11,1)'],
        rose:   ['rgba(244,63,94,.85)', 'rgba(244,63,94,1)'],
        indigo: ['rgba(99,102,241,.85)','rgba(99,102,241,1)'],
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
