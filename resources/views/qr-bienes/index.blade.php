@extends('layouts.main')

@section('title', 'Generador de Códigos QR')

@section('content_header')
    <h1>
        <i class="fas fa-qrcode"></i> Generador Masivo de Códigos QR
    </h1>
@stop

@section('content')

{{-- MENSAJES DE ALERTA --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-circle"></i> <strong>Error:</strong> {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle"></i> <strong>Éxito:</strong> {{ session('success') }}
    </div>
@endif

{{-- ESTADÍSTICAS --}}
<div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="stat-total">{{ $totalBienes }}</h3>
                <p>Total de Bienes Activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-box"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="stat-con-movimiento">{{ $bienesConMovimiento }}</h3>
                <p>Bienes con Movimientos</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="stat-sin-movimiento">{{ $totalBienes - $bienesConMovimiento }}</h3>
                <p>Bienes Sin Asignar</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
</div>

{{-- FORMULARIO DE GENERACIÓN --}}
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-cog"></i> Configuración de Generación
        </h3>
    </div>
    <form action="{{ route('qr-bienes.generar-pdf') }}" method="POST" id="formGenerarQR">
        @csrf
        <div class="card-body">
            <div class="row">
                {{-- FILTRO --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="filtro">
                            <i class="fas fa-filter"></i> Filtrar Bienes
                        </label>
                        <select name="filtro" id="filtro" class="form-control form-control-lg">
                            <option value="todos">📦 Todos los bienes activos</option>
                            <option value="con_movimiento">✅ Solo con movimientos</option>
                            <option value="sin_movimiento">⚠️ Solo sin asignar</option>
                        </select>
                        <small class="text-muted">
                            Selecciona qué bienes incluir en el reporte PDF
                        </small>
                    </div>
                </div>

                {{-- INFO DEL FORMATO GRID 3x3 --}}
                <div class="col-md-6">
                    <div class="alert alert-info mb-0" style="height: 100%;">
                        <h5><i class="icon fas fa-info-circle"></i> Formato de Impresión</h5>
                        <ul class="mb-0" style="font-size: 14px;">
                            <li><strong>9 QR por página</strong> (Grid 3 × 3)</li>
                            <li>Tamaño optimizado: <strong>120px</strong></li>
                            <li>Formato: <strong>A4 Portrait</strong></li>
                            <li>Incluye: código, nombre, tipo y ubicación</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- INFORMACIÓN TÉCNICA --}}
            <div class="row mt-3">
                <div class="col-12">
                    <div class="callout callout-success">
                        <h5><i class="fas fa-mobile-alt"></i> Compatible con tu App Móvil</h5>
                        <p class="mb-2">
                            <strong>URL de escaneo:</strong>
                            <code style="font-size: 13px;">https://web-production-84102.up.railway.app/qr/{codigo}</code>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-check text-success"></i> Al escanear con tu app Flutter, se mostrarán todos los detalles del bien
                        </p>
                    </div>
                </div>
            </div>

            {{-- VISTA PREVIA ESTIMADA --}}
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calculator"></i> Estimación de Páginas
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="description-block">
                                        <h5 class="description-header" id="bienes-filtrados">{{ $totalBienes }}</h5>
                                        <span class="description-text">Bienes a Generar</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="description-block">
                                        <h5 class="description-header text-primary">9</h5>
                                        <span class="description-text">QR por Página</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="description-block">
                                        <h5 class="description-header text-success" id="paginas-estimadas">
                                            {{ ceil($totalBienes / 9) }}
                                        </h5>
                                        <span class="description-text">Páginas Estimadas</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="description-block">
                                        <h5 class="description-header text-info">120px</h5>
                                        <span class="description-text">Tamaño del QR</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-lg" id="btnGenerar">
                <i class="fas fa-file-pdf"></i> Generar PDF con Códigos QR
            </button>
            <a href="{{ route('bien.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Volver a Bienes
            </a>
            <button type="button" class="btn btn-info btn-lg float-right" data-toggle="modal" data-target="#modalEjemplo">
                <i class="fas fa-eye"></i> Ver Ejemplo
            </button>
        </div>
    </form>
</div>

{{-- MODAL: EJEMPLO DE QR --}}
<div class="modal fade" id="modalEjemplo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode"></i> Ejemplo de Código QR Generado
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="border rounded p-4 d-inline-block bg-light">
                    {!! QrCode::size(200)->errorCorrection('H')->generate('https://web-production-84102.up.railway.app/qr/EJEMPLO001') !!}
                    <div class="mt-3">
                        <h5 class="text-primary mb-1">EJEMPLO001</h5>
                        <p class="text-muted mb-0">Computadora de Escritorio</p>
                        <small class="text-success">
                            <i class="fas fa-map-marker-alt"></i> Oficina Principal - Piso 2
                        </small>
                    </div>
                </div>
                <div class="mt-4 text-left">
                    <h6><i class="fas fa-info-circle text-info"></i> Información del QR:</h6>
                    <ul class="small">
                        <li>Tamaño de impresión: <strong>120px × 120px</strong></li>
                        <li>Corrección de errores: <strong>Alta (H)</strong></li>
                        <li>Formato: <strong>PNG de alta calidad</strong></li>
                        <li>Compatible con cualquier lector QR</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
$(document).ready(function() {
    // ========== ACTUALIZAR ESTADÍSTICAS AL CAMBIAR FILTRO ==========
    $('#filtro').on('change', function() {
        const filtro = $(this).val();
        const total = {{ $totalBienes }};
        const conMovimiento = {{ $bienesConMovimiento }};
        const sinMovimiento = total - conMovimiento;

        let bienesFiltrados = total;

        if (filtro === 'con_movimiento') {
            bienesFiltrados = conMovimiento;
        } else if (filtro === 'sin_movimiento') {
            bienesFiltrados = sinMovimiento;
        }

        // Actualizar contador de bienes
        $('#bienes-filtrados').text(bienesFiltrados);

        // Calcular páginas (9 QR por página)
        const paginasEstimadas = Math.ceil(bienesFiltrados / 9);
        $('#paginas-estimadas').text(paginasEstimadas);

        // Mostrar alerta si no hay bienes
        if (bienesFiltrados === 0) {
            toastr.warning('No hay bienes con este filtro', 'Advertencia');
        }
    });

    // ========== LOADING AL GENERAR PDF ==========
    $('#formGenerarQR').on('submit', function(e) {
        const bienesFiltrados = parseInt($('#bienes-filtrados').text());

        if (bienesFiltrados === 0) {
            e.preventDefault();
            toastr.error('No hay bienes para generar QR', 'Error');
            return false;
        }

        // Deshabilitar botón y mostrar spinner
        $('#btnGenerar')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Generando PDF...');

        // Mostrar notificación
        toastr.info('Generando ' + bienesFiltrados + ' códigos QR...', 'Procesando', {
            timeOut: 0,
            extendedTimeOut: 0,
            closeButton: false
        });

        // Rehabilitar botón después de 8 segundos
        setTimeout(function() {
            $('#btnGenerar')
                .prop('disabled', false)
                .html('<i class="fas fa-file-pdf"></i> Generar PDF con Códigos QR');

            toastr.clear();
            toastr.success('PDF generado exitosamente', 'Éxito');
        }, 8000);
    });

    // ========== CONFIGURACIÓN DE TOASTR ==========
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };
});
</script>
@stop

@section('css')
<style>
    /* Mejorar apariencia de los small-box */
    .small-box h3 {
        font-size: 2.5rem;
        font-weight: bold;
    }

    .small-box .icon {
        font-size: 70px;
    }

    /* Animación para el botón de generar */
    #btnGenerar:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }

    /* Estilo para el código de ejemplo */
    code {
        background: #f4f4f4;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 13px;
        color: #e83e8c;
    }

    /* Mejorar callout */
    .callout {
        border-left-width: 5px;
    }

    /* Card de estimación */
    .description-block {
        margin: 10px 0;
    }

    .description-header {
        font-size: 32px;
        font-weight: bold;
        margin: 0;
    }

    /* Modal mejorado */
    .modal-header.bg-info {
        color: white;
    }

    .modal-header .close {
        color: white;
        opacity: 0.8;
    }

    .modal-header .close:hover {
        opacity: 1;
    }
</style>
@stop
