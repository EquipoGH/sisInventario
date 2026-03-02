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
{{-- FORMULARIO DE GENERACIÓN AVANZADO --}}
<div class="card card-primary card-outline mt-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter"></i> Filtros y Generación Masiva (PDF)
        </h3>
    </div>
    
    <div class="card-body bg-light rounded-bottom">
        <form id="formFiltrosQR" method="POST" action="{{ route('qr-bienes.generar-pdf') }}" target="_blank">
            @csrf
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    <div class="form-group">
                        <label class="text-muted"><i class="fas fa-layer-group"></i> Estado del Registro</label>
                        <select class="form-control" name="filtro" id="filtro">
                            <option value="todos" selected>Todos (Con y sin movimiento)</option>
                            <option value="con_movimiento">Solo Asignados (Con movimiento)</option>
                            <option value="sin_movimiento">No asignados (Sin movimiento)</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4">
                    <div class="form-group">
                        <label class="text-muted"><i class="fas fa-building"></i> Área</label>
                        <select class="form-control" name="area_id" id="area_id">
                            <option value="">-- Todas --</option>
                            @foreach($areas as $a)
                                <option value="{{ $a->id_area }}">{{ $a->nombre_area }}</option>
                            @endforeach
                        </select>
                        <div class="small text-muted mt-1 px-1">Filtra también ubicaciones internas.</div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4">
                    <div class="form-group">
                        <label class="text-muted"><i class="fas fa-map-marker-alt"></i> Ubicación</label>
                        <select class="form-control" name="ubicacion_id" id="ubicacion_id">
                            <option value="">-- Todas --</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="form-group">
                        <label class="text-muted"><i class="fas fa-tags"></i> Tipo bien</label>
                        <select class="form-control" name="tipo_bien" id="tipo_bien">
                            <option value="">-- Todos --</option>
                            @foreach($tiposBien as $tb)
                                <option value="{{ $tb->id_tipo_bien }}">{{ $tb->nombre_tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="form-group">
                        <label class="text-muted"><i class="fas fa-shield-alt"></i> Conservación</label>
                        <select class="form-control" name="estado_bien_id" id="estado_bien_id">
                            <option value="">-- Todos --</option>
                            @foreach($estadosBien as $eb)
                                <option value="{{ $eb->id_estado }}">{{ $eb->nombre_estado }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4">
                    <div class="form-group">
                        <label class="text-muted"><i class="fas fa-calendar-alt"></i> Año Registro</label>
                        <select class="form-control" name="anio" id="anio">
                            <option value="">-- Todos --</option>
                            @foreach($anios as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-7 col-md-8">
                    <div class="form-group">
                        <label class="text-muted"><i class="fas fa-search"></i> Búsqueda libre (Código, Nombre...)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-primary text-white border-primary">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" name="q" id="q"
                                   placeholder="Escribe para buscar específicamente...">
                        </div>
                    </div>
                </div>

            </div>

            <hr class="mt-2 mb-3">
            
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted mb-2 mb-md-0">
                    <i class="fas fa-info-circle text-info"></i> El sistema creará hojas <b>tamaño A4</b> conteniendo
                    <strong class="text-dark">9 QRs por página</strong> con la selección filtrada.
                </div>
                <div class="btn-group">
                    <button class="btn btn-outline-secondary" type="button" id="btnLimpiar">
                        <i class="fas fa-eraser"></i> Limpiar Filtros
                    </button>
                    <button type="submit" class="btn btn-danger font-weight-bold" id="btnPdfQR">
                        <i class="fas fa-file-pdf"></i> Imprimir Grilla QRs (PDF)
                    </button>
                </div>
            </div>
            
        </form>
    </div>
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
    // ========== SELECTS EN CASCADA (ÁREAS -> UBICACIONES) ==========
    const $area = $('#area_id');
    const $ubic = $('#ubicacion_id');
    
    async function cargarUbicacionesPorArea() {
        const areaId = $area.val();
        if (!areaId) {
            $ubic.html('<option value="">-- Todas --</option>');
            return;
        }
        $ubic.html('<option value="">Cargando...</option>');
        try {
            // Utilizamos la ruta genérica de "obtener ubicaciones por area"
            // Asume que exista una ruta para listar en json. Si no existe, podemos obviar este paso.
            // Por seguridad, haremos fetch genérico; si falla lo dejamos vacío.
            const url = `/api/ubicaciones-por-area?area_id=${areaId}`; // Ruta teórica o adaptar a la real de Laravel 
            const res = await fetch(url);
            if(res.ok) {
                const data = await res.json();
                let html = '<option value="">-- Todas --</option>';
                data.forEach(u => {
                    html += `<option value="${u.id_ubicacion}">${u.nombre_sede} - ${u.ambiente}</option>`;
                });
                $ubic.html(html);
            } else {
                 $ubic.html('<option value="">-- Todas --</option>');
            }
        } catch (e) {
            $ubic.html('<option value="">-- Todas --</option>');
        }
    }

    $area.on('change', cargarUbicacionesPorArea);

    // ========== ACTUALIZAR ESTADÍSTICAS AL CAMBIAR FILTRO PRINCIPAL ==========
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

        $('#bienes-filtrados').text(bienesFiltrados);
        $('#paginas-estimadas').text(Math.ceil(bienesFiltrados / 9));

        if (bienesFiltrados === 0) {
            toastr.warning('Atención: Parece que no hay registros bajo esta selección base.', 'Advertencia');
        }
    });

    // ========== LIMPIAR FILTROS ==========
    $('#btnLimpiar').on('click', function () {
        $('#formFiltrosQR')[0].reset();
        $ubic.html('<option value="">-- Todas --</option>');
        $('#filtro').trigger('change');
    });

    // ========== LOADING AL GENERAR PDF ==========
    $('#formFiltrosQR').on('submit', function() {
        try {
            const $btn = $('#btnPdfQR');
            const originalHtml = '<i class="fas fa-file-pdf"></i> Imprimir Grilla QRs (PDF)';

            // Deshabilitar botón y mostrar spinner rápidamente
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Preparando PDF...');
            toastr.info('Abriendo PDF en nueva pestaña...', 'Aguarde un momento', { timeOut: 2500 });

            // Restaurar botón después de 1 segundo pase lo que pase
            setTimeout(function() {
                $btn.prop('disabled', false).html(originalHtml);
            }, 1500);
            
        } catch (error) {
            console.error(error);
            $('#btnPdfQR').prop('disabled', false).html('<i class="fas fa-file-pdf"></i> Imprimir Grilla QRs (PDF)');
        }
        // Permitir que el formulario siga su curso normal (HTML form submit)
        return true;
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
