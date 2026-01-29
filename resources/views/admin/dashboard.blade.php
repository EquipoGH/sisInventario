@extends('layouts.main')

@section('title', 'Dashboard')

@section('content_header')
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
@stop

@section('content')
{{-- TARJETAS DE ESTADÍSTICAS --}}
<div class="row">
    {{-- Card: Total Áreas --}}
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalAreas }}</h3>
                <p>Total de Áreas</p>
            </div>
            <div class="icon">
                <i class="fas fa-building"></i>
            </div>

            @can('permiso','Areas')
                <a href="{{ route('area.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            @else
                <span class="small-box-footer" style="opacity:.65; cursor:not-allowed;">
                    Sin acceso <i class="fas fa-lock"></i>
                </span>
            @endcan
        </div>
    </div>

    {{-- Card: Total Tipos de Bien --}}
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $totalTiposBien }}</h3>
                <p>Total de Tipos de Bien</p>
            </div>
            <div class="icon">
                <i class="fas fa-tags"></i>
            </div>

            @can('permiso','Tipos De Bien')
                <a href="{{ route('tipo-bien.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            @else
                <span class="small-box-footer" style="opacity:.65; cursor:not-allowed;">
                    Sin acceso <i class="fas fa-lock"></i>
                </span>
            @endcan
        </div>
    </div>

    {{-- Card: Total Bienes --}}
    <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $totalBienes }}</h3>
                <p>Total de Bienes</p>
            </div>
            <div class="icon">
                <i class="fas fa-box"></i>
            </div>

            @can('permiso','Bienes')
                <a href="{{ route('bien.index') }}" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                </a>
            @else
                <span class="small-box-footer" style="opacity:.65; cursor:not-allowed;">
                    Sin acceso <i class="fas fa-lock"></i>
                </span>
            @endcan
        </div>
    </div>
</div>

{{-- TABLA DE ÚLTIMOS BIENES --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i> Últimos Bienes Registrados
        </h3>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-custom">
                    <tr>
                        <th width="8%">ID</th>
                        <th width="20%">Código</th>
                        <th width="42%">Denominación</th>
                        <th width="15%">Tipo</th>
                        <th width="15%">Fecha Registro</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimosBienes as $bien)
                        <tr>
                            <td class="text-center"><strong>{{ $bien->id_bien }}</strong></td>
                            <td><strong>{{ $bien->codigo_patrimonial }}</strong></td>
                            <td>{{ strtoupper($bien->denominacion_bien) }}</td>
                            <td>
                                <span class="badge badge-custom-info">
                                    {{ $bien->tipoBien->nombre ?? 'N/A' }}
                                </span>
                            </td>
                            <td>{{ $bien->fecha_registro->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No hay bienes registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($ultimosBienes->count() > 0)
        <div class="card-footer text-center">
            @can('permiso','Bienes')
                <a href="{{ route('bien.index') }}" class="btn btn-custom-primary">
                    <i class="fas fa-eye"></i> Ver todos los bienes
                </a>
            @endcan
        </div>
    @endif
</div>
@stop

@section('css')
<style>
/* ==================== ICONOS DE LAS CARDS ==================== */
.small-box .icon {
    font-size: 90px;
    position: absolute;
    right: 15px;
    top: 15px;
    transition: all 0.3s linear;
    color: rgba(0, 0, 0, 0.15);
    z-index: 0;
}

.small-box:hover .icon {
    font-size: 95px;
    transform: rotate(-10deg);
}

/* ==================== NÚMEROS GRANDES ==================== */
.small-box .inner h3 {
    font-size: 48px;
    font-weight: bold;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
}

.small-box .inner p {
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}

/* ==================== EFECTO HOVER EN CARDS ==================== */
.small-box {
    transition: all 0.3s ease;
    cursor: pointer;
    border-radius: 8px;
    overflow: hidden;
}

.small-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
}

/* ==================== FOOTER DE LAS CARDS ==================== */
.small-box-footer {
    position: relative;
    text-align: center;
    padding: 10px 0;
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

/* ==================== INNER (contenido de las cards) ==================== */
.small-box .inner {
    padding: 15px;
    position: relative;
    z-index: 5;
}

/* ==================== COLORES PERSONALIZADOS ==================== */
.small-box.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
}

.small-box.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%) !important;
}

.small-box.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
    color: #1f2d3d;
}

.small-box.bg-warning .small-box-footer {
    color: rgba(0, 0, 0, 0.6);
}

.small-box.bg-warning .small-box-footer:hover {
    color: rgba(0, 0, 0, 0.8);
}

/* ==================== TABLA ==================== */
.table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
}

.table tbody td {
    vertical-align: middle;
}

/* ==================== RESPONSIVE ==================== */
@media (max-width: 768px) {
    .small-box .icon {
        font-size: 60px;
    }

    .small-box:hover .icon {
        font-size: 65px;
    }

    .small-box .inner h3 {
        font-size: 32px;
    }

    .small-box .inner p {
        font-size: 14px;
    }
}
</style>
@endsection
