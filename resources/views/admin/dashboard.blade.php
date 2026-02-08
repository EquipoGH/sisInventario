@extends('layouts.main')

@section('title', 'Dashboard - Sistema de Inventario')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">
            <i class="fas fa-chart-line text-primary"></i> Dashboard - Gestión de Inventario
        </h1>
        <div>
            <span class="badge badge-info badge-pulse" id="lastUpdate">
                <i class="fas fa-sync-alt"></i> Actualizado: {{ now()->format('H:i:s') }}
            </span>
        </div>
    </div>
@stop

@section('content')

{{-- ============================================
    🎯 SECCIÓN 1: CARDS SUPERIORES ANIMADAS
============================================= --}}
<div class="row mb-4">
    {{-- Card 1: Total Bienes --}}
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="small-box bg-gradient-info elevation-3 card-animate" style="animation-delay: 0.1s">
            <div class="inner">
                <h3 id="totalBienes">{{ $totalBienes }}</h3>
                <p>Total de Bienes</p>
            </div>
            <div class="icon">
                <i class="fas fa-box"></i>
            </div>
            <a href="{{ route('bien.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Card 2: Áreas --}}
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="small-box bg-gradient-success elevation-3 card-animate" style="animation-delay: 0.2s">
            <div class="inner">
                <h3>{{ $totalAreas }}</h3>
                <p>Áreas Activas</p>
            </div>
            <div class="icon">
                <i class="fas fa-building"></i>
            </div>
            <a href="{{ route('area.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Card 3: Tipos de Bien --}}
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="small-box bg-gradient-warning elevation-3 card-animate" style="animation-delay: 0.3s">
            <div class="inner">
                <h3>{{ $totalTiposBien }}</h3>
                <p>Tipos de Bien</p>
            </div>
            <div class="icon">
                <i class="fas fa-tags"></i>
            </div>
            <a href="{{ route('tipo-bien.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    {{-- Card 4: Movimientos Hoy --}}
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="small-box bg-gradient-danger elevation-3 card-animate" style="animation-delay: 0.4s">
            <div class="inner">
                <h3 id="movimientosHoy">{{ $movimientosHoy }}</h3>
                <p>Movimientos Hoy</p>
            </div>
            <div class="icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <a href="{{ route('movimiento.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- ============================================
    📊 SECCIÓN 2: GRÁFICOS PRINCIPALES
============================================= --}}
<div class="row mb-4">
    {{-- Gráfico 1: Movimientos por Tipo --}}
    <div class="col-lg-6 col-12 mb-3">
        <div class="card elevation-2 card-animate" style="animation-delay: 0.5s">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i>
                    <strong>Movimientos por Tipo</strong>
                </h3>
                <div class="card-tools">
                    <span class="badge badge-light">Top 5</span>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="chartEstados"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráfico 2: Bienes por Tipo (Dona) --}}
    <div class="col-lg-6 col-12 mb-3">
        <div class="card elevation-2 card-animate" style="animation-delay: 0.6s">
            <div class="card-header bg-gradient-info">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    <strong>Distribución por Tipo de Bien</strong>
                </h3>
                <div class="card-tools">
                    <span class="badge badge-light">{{ $totalTiposBien }} categorías</span>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="chartTipos"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================
    📈 SECCIÓN 3: GRÁFICO DE TENDENCIA + TOP ÁREAS
============================================= --}}
<div class="row mb-4">
    {{-- Gráfico 3: Movimientos últimos 7 días --}}
    <div class="col-lg-8 col-12 mb-3">
        <div class="card elevation-2 card-animate" style="animation-delay: 0.7s">
            <div class="card-header bg-gradient-success">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    <strong>Actividad de Movimientos (Últimos 7 Días)</strong>
                </h3>
                <div class="card-tools">
                    <span class="badge badge-light">{{ $movimientosSemana }} esta semana</span>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="chartMovimientos"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráfico 4: Top 5 Áreas --}}
    <div class="col-lg-4 col-12 mb-3">
        <div class="card elevation-2 card-animate" style="animation-delay: 0.8s">
            <div class="card-header bg-gradient-warning">
                <h3 class="card-title">
                    <i class="fas fa-trophy mr-2"></i>
                    <strong>Top 5 Áreas</strong>
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="chartAreas"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================
    🏢 SECCIÓN 4: TOP UBICACIONES
============================================= --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card elevation-2 card-animate" style="animation-delay: 0.9s">
            <div class="card-header bg-gradient-secondary">
                <h3 class="card-title">
                    <i class="fas fa-map-marked-alt mr-2"></i>
                    <strong>Top 5 Ubicaciones con Más Bienes</strong>
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 250px;">
                    <canvas id="chartUbicaciones"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================
    📋 SECCIÓN 5: ACTIVIDAD RECIENTE
============================================= --}}
<div class="row mb-4">
    {{-- Últimos Bienes Registrados --}}
    <div class="col-lg-6 col-12 mb-3">
        <div class="card elevation-2 card-animate" style="animation-delay: 1s">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title">
                    <i class="fas fa-box-open mr-2"></i>
                    <strong>Últimos Bienes Registrados</strong>
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="15%">Código</th>
                                <th width="45%">Denominación</th>
                                <th width="20%">Tipo</th>
                                <th width="20%">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosBienes as $bien)
                            <tr>
                                <td><strong>{{ $bien->codigo_patrimonial }}</strong></td>
                                <td>{{ Str::limit($bien->denominacion_bien, 40) }}</td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $bien->tipoBien->tipo_bien ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="far fa-clock"></i> {{ $bien->created_at->diffForHumans() }}
                                    </small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No hay bienes registrados
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('bien.index') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye"></i> Ver Todos los Bienes
                </a>
            </div>
        </div>
    </div>

    {{-- Últimos Movimientos - CORREGIDO --}}
    <div class="col-lg-6 col-12 mb-3">
        <div class="card elevation-2 card-animate" style="animation-delay: 1.1s">
            <div class="card-header bg-gradient-danger">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>
                    <strong>Actividad Reciente (Movimientos)</strong>
                </h3>
            </div>
            <div class="card-body p-3" style="max-height: 450px; overflow-y: auto;">
                @if($ultimosMovimientos && $ultimosMovimientos->count() > 0)
                    <ul class="list-unstyled">
                        @foreach($ultimosMovimientos->take(8) as $mov)
                            @php
                                $tipoMov = strtoupper($mov->tipoMovimiento->tipo_mvto ?? 'MOVIMIENTO');
                                $iconClass = 'fas fa-exchange-alt';
                                $badgeClass = 'info';

                                if (str_contains($tipoMov, 'REGISTRO')) {
                                    $iconClass = 'fas fa-plus-circle';
                                    $badgeClass = 'success';
                                } elseif (str_contains($tipoMov, 'ASIGNACION') || str_contains($tipoMov, 'ASIGNACIÓN')) {
                                    $iconClass = 'fas fa-arrow-circle-right';
                                    $badgeClass = 'info';
                                } elseif (str_contains($tipoMov, 'BAJA')) {
                                    $iconClass = 'fas fa-trash-alt';
                                    $badgeClass = 'danger';
                                } elseif (str_contains($tipoMov, 'TRANSFERENCIA')) {
                                    $iconClass = 'fas fa-exchange-alt';
                                    $badgeClass = 'warning';
                                }
                            @endphp

                            <li class="mb-3 pb-3 border-bottom">
                                {{-- Fecha --}}
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="far fa-clock"></i>
                                        <strong>{{ $mov->fecha_mvto->diffForHumans() }}</strong>
                                        <span class="text-muted">
                                            ({{ $mov->fecha_mvto->format('d/m/Y H:i') }})
                                        </span>
                                    </small>
                                </div>

                                {{-- Tipo de Movimiento --}}
                                <div class="mb-2">
                                    <i class="{{ $iconClass }} text-{{ $badgeClass }}"></i>
                                    <span class="badge badge-{{ $badgeClass }} ml-1">
                                        {{ $tipoMov }}
                                    </span>
                                </div>

                                {{-- Bien --}}
                                <div class="mb-1">
                                    <strong><i class="fas fa-box text-primary"></i> Bien:</strong>
                                    <span>{{ Str::limit($mov->bien->denominacion_bien ?? 'Sin bien', 50) }}</span>
                                </div>

                                {{-- Ubicación --}}
                                @if($mov->ubicacion)
                                    <div class="mb-1">
                                        <strong><i class="fas fa-map-marker-alt text-success"></i> Ubicación:</strong>
                                        <span>{{ $mov->ubicacion->nombre_sede }}</span>
                                    </div>
                                @endif

                                {{-- Área --}}
                                @if($mov->ubicacion && $mov->ubicacion->area)
                                    <div>
                                        <strong><i class="fas fa-building text-warning"></i> Área:</strong>
                                        <span>{{ $mov->ubicacion->area->nombre_area }}</span>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        <p>No hay movimientos registrados</p>
                    </div>
                @endif
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('movimiento.index') }}" class="btn btn-sm btn-danger">
                    <i class="fas fa-list"></i> Ver Todos los Movimientos
                </a>
            </div>
        </div>
    </div>

</div>

@stop

{{-- Continúa en el siguiente mensaje con CSS y JS --}}
@section('css')
<style>


/* ==================== TIMELINE LISTA ==================== */
.list-unstyled li {
    transition: all 0.2s ease;
}

.list-unstyled li:hover {
    background-color: #f8f9fa;
    padding-left: 10px;
    border-radius: 5px;
}

.border-bottom {
    border-bottom: 1px solid #dee2e6 !important;
}

.border-bottom:last-child {
    border-bottom: none !important;
}




/* ==================== ANIMACIONES ==================== */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card-animate {
    animation: slideInUp 0.6s ease-out;
    animation-fill-mode: both;
}

/* ==================== GRADIENTES MODERNOS ==================== */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%) !important;
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%) !important;
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%) !important;
}

/* ==================== SMALL BOXES (CARDS) ==================== */
.small-box {
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.small-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important;
}

.small-box .icon {
    font-size: 70px;
    position: absolute;
    right: 15px;
    top: 15px;
    transition: all 0.3s linear;
    color: rgba(0, 0, 0, 0.15);
    z-index: 0;
}

.small-box:hover .icon {
    font-size: 80px;
    transform: rotate(-10deg);
}

.small-box .inner {
    padding: 15px;
    position: relative;
    z-index: 5;
}

.small-box .inner h3 {
    font-size: 42px;
    font-weight: bold;
    margin: 0 0 10px 0;
    white-space: nowrap;
}

.small-box .inner p {
    font-size: 15px;
    font-weight: 500;
    margin: 0;
}

.small-box-footer {
    position: relative;
    text-align: center;
    padding: 8px 0;
    color: rgba(255, 255, 255, 0.8);
    display: block;
    z-index: 10;
    background: rgba(0, 0, 0, 0.1);
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
}

.small-box-footer:hover {
    color: #fff;
    background: rgba(0, 0, 0, 0.2);
    text-decoration: none;
}

/* ==================== CARDS ==================== */
.card {
    border-radius: 10px;
    border: none;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border: none;
}

/* ==================== TIMELINE ==================== */
.timeline > li > .timeline-item {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    border: none;
}

.timeline > li > .fa,
.timeline > li > .fas,
.timeline > li > .far {
    width: 35px;
    height: 35px;
    font-size: 14px;
    line-height: 35px;
}

/* ==================== BADGE PULSE ==================== */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(23, 162, 184, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(23, 162, 184, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(23, 162, 184, 0);
    }
}

.badge-pulse {
    animation: pulse 2s infinite;
    font-size: 0.85rem;
    padding: 8px 12px;
    border-radius: 20px;
}

/* ==================== TABLA ==================== */
.table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    background-color: #f8f9fa;
}

.table tbody td {
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: #f1f3f5;
    cursor: pointer;
}

/* ==================== SCROLLBAR PERSONALIZADO ==================== */
.card-body::-webkit-scrollbar {
    width: 6px;
}

.card-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.card-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.card-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* ==================== RESPONSIVE ==================== */
@media (max-width: 768px) {
    .small-box .icon {
        font-size: 50px;
    }

    .small-box:hover .icon {
        font-size: 55px;
    }

    .small-box .inner h3 {
        font-size: 28px;
    }

    .small-box .inner p {
        font-size: 13px;
    }

    .chart-container {
        height: 250px !important;
    }
}

/* ==================== LOADING STATE ==================== */
.fa-spin {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
@stop
@section('js')
{{-- ✅ Chart.js 4.4.1 --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
$(document).ready(function() {

    // ================================================
    // 🎨 CONFIGURACIÓN GLOBAL DE CHART.JS
    // ================================================
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 13;
    Chart.defaults.color = '#666';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.padding = 15;
    Chart.defaults.animation.duration = 1500;
    Chart.defaults.animation.easing = 'easeOutQuart';

    // ================================================
    // 📊 GRÁFICO 1: MOVIMIENTOS POR TIPO (Barras)
    // ================================================
    const ctxEstados = document.getElementById('chartEstados');
    if (ctxEstados) {
        new Chart(ctxEstados.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($estadosLabels),
                datasets: [{
                    label: 'Cantidad de Movimientos',
                    data: @json($estadosData),
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(251, 191, 36, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(168, 85, 247, 1)',
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return `${context.parsed.y} movimiento${context.parsed.y !== 1 ? 's' : ''}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ================================================
    // 🍩 GRÁFICO 2: BIENES POR TIPO (Dona)
    // ================================================
    const ctxTipos = document.getElementById('chartTipos');
    if (ctxTipos) {
        new Chart(ctxTipos.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json($tiposLabels),
                datasets: [{
                    data: @json($tiposData),
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.85)',
                        'rgba(59, 130, 246, 0.85)',
                        'rgba(251, 191, 36, 0.85)',
                        'rgba(34, 197, 94, 0.85)',
                        'rgba(168, 85, 247, 0.85)',
                        'rgba(236, 72, 153, 0.85)',
                    ],
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            font: { size: 12 },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        return {
                                            text: `${label}: ${value}`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8
                    }
                }
            }
        });
    }

    // ================================================
    // 📈 GRÁFICO 3: MOVIMIENTOS ÚLTIMOS 7 DÍAS (Línea)
    // ================================================
    const ctxMovimientos = document.getElementById('chartMovimientos');
    if (ctxMovimientos) {
        new Chart(ctxMovimientos.getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($diasLabels),
                datasets: [{
                    label: 'Movimientos',
                    data: @json($movimientosDias),
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(34, 197, 94, 1)',
                    pointBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ================================================
    // 🏆 GRÁFICO 4: TOP 5 ÁREAS (Barras horizontales)
    // ================================================
    const ctxAreas = document.getElementById('chartAreas');
    if (ctxAreas) {
        new Chart(ctxAreas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($areasLabels),
                datasets: [{
                    label: 'Bienes',
                    data: @json($areasData),
                    backgroundColor: 'rgba(168, 85, 247, 0.8)',
                    borderColor: 'rgba(168, 85, 247, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // ================================================
    // 🏢 GRÁFICO 5: TOP 5 UBICACIONES (Barras)
    // ================================================
    const ctxUbicaciones = document.getElementById('chartUbicaciones');
    if (ctxUbicaciones) {
        new Chart(ctxUbicaciones.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($ubicacionesLabels),
                datasets: [{
                    label: 'Bienes',
                    data: @json($ubicacionesData),
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                    borderColor: [
                        'rgba(239, 68, 68, 1)',
                        'rgba(251, 191, 36, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(168, 85, 247, 1)',
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // ================================================
    // 🎉 CONTADORES ANIMADOS
    // ================================================
    function animateValue(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            element.innerHTML = Math.floor(progress * (end - start) + start);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    const totalBienesEl = document.getElementById('totalBienes');
    const movimientosHoyEl = document.getElementById('movimientosHoy');

    if (totalBienesEl) animateValue(totalBienesEl, 0, {{ $totalBienes }}, 1500);
    if (movimientosHoyEl) animateValue(movimientosHoyEl, 0, {{ $movimientosHoy }}, 1500);

    console.log('%c🚀 Dashboard Cargado Exitosamente', 'color: #22c55e; font-size: 16px; font-weight: bold;');
    console.log('%c📊 Gráficos: Chart.js 4.4.1', 'color: #3b82f6; font-size: 12px;');
});
</script>
@stop
