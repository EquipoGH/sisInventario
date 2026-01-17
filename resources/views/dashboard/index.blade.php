@extends('layouts.main')

@section('title', 'Dashboard - Sistema de Inventario')

@section('content_header')
    <h1>Dashboard - Gestión de Inventario</h1>
@stop

@section('content')

<!-- TARJETAS SUPERIORES -->
<div class="row">
    <!-- Total Bienes -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalBienes }}</h3>
                <p>Total de Bienes</p>
            </div>
            <div class="icon">
                <i class="fas fa-box"></i>
            </div>
            <a href="{{ url('bien') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Áreas -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $totalAreas }}</h3>
                <p>Áreas Registradas</p>
            </div>
            <div class="icon">
                <i class="fas fa-building"></i>
            </div>
            <a href="{{ url('area') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Tipos -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $totalTipos }}</h3>
                <p>Tipos de Bien</p>
            </div>
            <div class="icon">
                <i class="fas fa-tags"></i>
            </div>
            <a href="{{ url('tipo-bien') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Movimientos Hoy -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $movimientosHoy }}</h3>
                <p>Movimientos Hoy</p>
            </div>
            <div class="icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <a href="{{ url('mvto-bien') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- GRÁFICOS -->
<div class="row">
    <!-- Gráfico de Barras: Bienes por Estado -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Bienes por Estado
                </h3>
            </div>
            <div class="card-body">
                <canvas id="chartEstados" style="height: 250px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico de Dona: Bienes por Tipo -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Bienes por Tipo
                </h3>
            </div>
            <div class="card-body">
                <canvas id="chartTipos" style="height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Líneas: Movimientos por Mes -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i>
                    Movimientos de los Últimos 6 Meses
                </h3>
            </div>
            <div class="card-body">
                <canvas id="chartMovimientos" style="height: 250px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico Horizontal: Top 5 Áreas -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-building mr-1"></i>
                    Top 5 Áreas con Más Bienes
                </h3>
            </div>
            <div class="card-body">
                <canvas id="chartAreas" style="height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- TABLA DE ÚLTIMOS MOVIMIENTOS -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-1"></i>
                    Últimos Movimientos Registrados
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Bien</th>
                            <th>Tipo Movimiento</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimosMovimientos as $movimiento)
                        <tr>
                            <td>{{ $movimiento->fecha_mvto->format('d/m/Y H:i') }}</td>
                            <td>{{ $movimiento->bien->nombre_bien ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $movimiento->tipoMovimiento->nombre_tipo_mvto ?? 'N/A' }}
                                </span>
                            </td>
                            <td>{{ Str::limit($movimiento->observacion, 50) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No hay movimientos registrados
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {

    // ==========================================
    // GRÁFICO 1: BIENES POR ESTADO (Barras)
    // ==========================================
    const ctxEstados = document.getElementById('chartEstados').getContext('2d');
    new Chart(ctxEstados, {
        type: 'bar',
        data: {
            labels: @json($estadosLabels),
            datasets: [{
                label: 'Cantidad de Bienes',
                data: @json($estadosData),
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(153, 102, 255, 1)',
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // ==========================================
    // GRÁFICO 2: BIENES POR TIPO (Dona)
    // ==========================================
    const ctxTipos = document.getElementById('chartTipos').getContext('2d');
    new Chart(ctxTipos, {
        type: 'doughnut',
        data: {
            labels: @json($tiposLabels),
            datasets: [{
                data: @json($tiposData),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // ==========================================
    // GRÁFICO 3: MOVIMIENTOS POR MES (Líneas)
    // ==========================================
    const ctxMovimientos = document.getElementById('chartMovimientos').getContext('2d');
    new Chart(ctxMovimientos, {
        type: 'line',
        data: {
            labels: @json($mesesLabels),
            datasets: [{
                label: 'Movimientos',
                data: @json($mesesData),
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // ==========================================
    // GRÁFICO 4: TOP 5 ÁREAS (Horizontal)
    // ==========================================
    const ctxAreas = document.getElementById('chartAreas').getContext('2d');
    new Chart(ctxAreas, {
        type: 'bar',
        data: {
            labels: @json($areasLabels),
            datasets: [{
                label: 'Cantidad',
                data: @json($areasData),
                backgroundColor: 'rgba(153, 102, 255, 0.8)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 2
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@stop

@section('css')
<style>
    .small-box {
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .small-box .icon {
        font-size: 60px;
    }
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
@endsection
