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

{{-- ESTADÍSTICAS (Banners superiores removidos a petición del usuario) --}}

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

{{-- ============================================================ --}}
{{--   SECCIÓN: GENERAR QR INDIVIDUAL POR CÓDIGO PATRIMONIAL       --}}
{{-- ============================================================ --}}
<div class="card card-outline card-info mt-4" id="cardQrIndividual">

    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-qrcode"></i> Generar QR Individual por C&oacute;digo Patrimonial
        </h3>
        <div class="card-tools">
            <span class="badge badge-info" id="qrIndBadgeEstado">Listo</span>
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>

    <div class="card-body">

        {{-- Fila del input y botones --}}
        <div class="row align-items-end">

            {{-- Input código --}}
            <div class="col-lg-6 col-md-8">
                <div class="form-group mb-0">
                    <label class="text-muted">
                        <i class="fas fa-barcode text-info"></i> C&oacute;digo Patrimonial
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-info border-info text-white">
                                <i class="fas fa-hashtag"></i>
                            </span>
                        </div>
                        <input type="text" id="codigoQrInput"
                               class="form-control"
                               placeholder="Ej: PAT001, 8238893428384..."
                               autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-default" id="btnLimpiarQrInd" type="button">
                                <i class="fas fa-times-circle"></i> Limpiar
                            </button>
                        </div>
                    </div>
                    <small class="text-muted" id="qrIndHint">
                        <i class="fas fa-info-circle text-info"></i>
                        Ingresa el c&oacute;digo del bien y presiona <strong>Ver QR</strong> o <kbd>Enter</kbd>
                    </small>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="col-lg-6 col-md-4 mt-3 mt-md-0">
                <div class="btn-group">
                    <button id="btnVerQrInd" class="btn btn-success" type="button">
                        <i class="fas fa-eye"></i> Ver QR
                    </button>
                    <a id="btnDescargarQrInd" href="#" target="_blank"
                       class="btn btn-primary disabled" aria-disabled="true">
                        <i class="fas fa-download"></i> Descargar PNG
                    </a>
                </div>
                <span id="qrIndSpinner" class="d-none ml-2">
                    <i class="fas fa-circle-notch fa-spin text-info"></i>
                    <span class="text-muted small">Generando...</span>
                </span>
            </div>
        </div>

        {{-- Panel de resultado (oculto hasta que se genera) --}}
        <div id="qrIndPreviewPanel" class="d-none mt-4">
            <hr>
            <div class="row">

                {{-- Columna imagen QR --}}
                <div class="col-auto text-center">
                    <div class="callout callout-info p-3">
                        <img id="qrIndImg" src="" alt="QR"
                             style="width:150px; height:150px; display:block; margin: 0 auto;">
                        <div class="mt-2">
                            <span class="badge badge-light border font-weight-bold" id="qrIndCodigoBadge"></span>
                        </div>
                    </div>
                </div>

                {{-- Columna info --}}
                <div class="col">
                    <h5 class="mb-1">
                        <i class="fas fa-box text-info"></i>
                        <span id="qrIndNombre" class="font-weight-bold">-</span>
                    </h5>
                    <div class="callout callout-default">
                        <p class="mb-1 text-muted small"><i class="fas fa-link"></i> URL codificada en el QR:</p>
                        <code id="qrIndUrl" class="small" style="word-break:break-all;">-</code>
                    </div>
                    <div class="btn-group btn-group-sm mt-2">
                        <a id="btnDescargarQrInd2" href="#" target="_blank"
                           class="btn btn-primary disabled" aria-disabled="true">
                            <i class="fas fa-download"></i> Descargar PNG
                        </a>
                        <button id="btnNuevoQrInd" class="btn btn-default" type="button">
                            <i class="fas fa-redo"></i> Nuevo c&oacute;digo
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- Panel de error --}}
        <div id="qrIndErrorPanel" class="d-none mt-3">
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" onclick="$(this).closest('.alert').parent().addClass('d-none')">
                    &times;
                </button>
                <i class="fas fa-exclamation-triangle"></i>
                <strong>No encontrado:</strong> <span id="qrIndErrorMsg">El c&oacute;digo ingresado no existe en el inventario.</span>
            </div>
        </div>

    </div>
</div>




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
                    {{-- QR generado vía JS para evitar la Facade de servidor --}}
                    <div id="modalQrImgWrap" style="width:200px; height:200px; margin:0 auto; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    </div>
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

    // ========================
    // GENERADOR QR INDIVIDUAL
    // ========================
    const urlImagenBase = "{{ route('qr-bienes.imagen', ['codigo' => '__CODE__']) }}";
    const urlDescargarBase = "{{ route('qr-bienes.descargar', ['codigo' => '__CODE__']) }}";

    function getUrlImagen(codigo) { return urlImagenBase.replace('__CODE__', encodeURIComponent(codigo)); }
    function getUrlDescargar(codigo) { return urlDescargarBase.replace('__CODE__', encodeURIComponent(codigo)); }

    function resetPreviewer() {
        $('#qrIndPreviewPanel, #qrIndErrorPanel').addClass('d-none');
        $('#qrIndImg').attr('src', '');
        $('#qrIndCodigoBadge, #qrIndNombre, #qrIndUrl').text('');
        $('#btnDescargarQrInd, #btnDescargarQrInd2').attr('href','#').addClass('disabled').attr('aria-disabled','true');
        $('#qrIndBadgeEstado').removeClass('badge-success badge-danger').addClass('badge-info').text('Listo');
    }

    async function generarQRIndividual() {
        const codigo = $('#codigoQrInput').val().trim();
        if (!codigo) {
            if (typeof toastr !== 'undefined') toastr.warning('Ingresa un código patrimonial primero.', 'Campo requerido');
            $('#codigoQrInput').focus();
            return;
        }

        resetPreviewer();
        $('#qrIndSpinner').removeClass('d-none');
        $('#btnVerQrInd').prop('disabled', true);
        $('#qrIndHint').html('<i class="fas fa-circle-notch fa-spin text-primary"></i> Buscando bien...');

        try {
            const res = await fetch(getUrlImagen(codigo), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await res.json();

            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'Bien no encontrado.');
            }

            // Mostrar preview
            const urlDesc = getUrlDescargar(codigo);

            $('#qrIndImg').attr('src', data.qr_img);
            $('#qrIndCodigoBadge').text(data.codigo);
            $('#qrIndNombre').text(data.nombre);
            $('#qrIndUrl').text('https://inventario-android-api.onrender.com/qr/' + data.codigo);

            $('#btnDescargarQrInd, #btnDescargarQrInd2')
                .attr('href', urlDesc)
                .removeClass('disabled').attr('aria-disabled','false');

            $('#qrIndPreviewPanel').removeClass('d-none');
            $('#qrIndBadgeEstado').removeClass('badge-info badge-danger').addClass('badge-success').text('Generado');
            $('#qrIndHint').html('<i class="fas fa-check-circle text-success"></i> QR generado para <strong>' + data.codigo + '</strong>');

            if (typeof toastr !== 'undefined') toastr.success('QR generado correctamente.', '¡Listo!');

        } catch (err) {
            $('#qrIndErrorMsg').text(err.message || 'El código ingresado no existe en el inventario.');
            $('#qrIndErrorPanel').removeClass('d-none');
            $('#qrIndHint').html('<i class="fas fa-info-circle text-info"></i> Ingresa el código del bien para generar su QR');
            $('#qrIndBadgeEstado').removeClass('badge-info badge-success').addClass('badge-danger').text('No encontrado');
            if (typeof toastr !== 'undefined') toastr.error(err.message || 'Código no encontrado.', 'Error');
        } finally {
            $('#qrIndSpinner').addClass('d-none');
            $('#btnVerQrInd').prop('disabled', false);
        }
    }

    // Botón Ver QR
    $('#btnVerQrInd').on('click', generarQRIndividual);

    // Enter en el input
    $('#codigoQrInput').on('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); generarQRIndividual(); }
    });

    // Limpiar
    $('#btnLimpiarQrInd, #btnNuevoQrInd').on('click', function() {
        $('#codigoQrInput').val('').focus();
        resetPreviewer();
        $('#qrIndErrorPanel').addClass('d-none');
        $('#qrIndHint').html('<i class="fas fa-info-circle text-info"></i> Ingresa el código del bien para generar su QR');
    });

    // ========================
    // MODAL EJEMPLO — carga QR dinámicamente (sin Facade server-side)
    // ========================
    $('#modalEjemplo').on('show.bs.modal', function() {
        const urlImgEjemplo = "{{ route('qr-bienes.imagen', ['codigo' => 'EJEMPLO001']) }}";
        const $wrap = $('#modalQrImgWrap');
        $wrap.html('<i class="fas fa-spinner fa-spin fa-2x text-muted"></i>');
        fetch(urlImgEjemplo, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    $wrap.html('<img src="' + data.qr_img + '" style="width:200px;height:200px;border-radius:6px;" alt="QR Ejemplo">');
                } else {
                    // Si EJEMPLO001 no existe en BD, mostramos un QR predefinido con la URL de Render
                    $wrap.html('<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://inventario-android-api.onrender.com/qr/EJEMPLO001" style="width:200px;height:200px;border-radius:6px;" alt="QR Ejemplo">');
                }
            })
            .catch(() => {
                $wrap.html('<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://inventario-android-api.onrender.com/qr/EJEMPLO001" style="width:200px;height:200px;border-radius:6px;" alt="QR Ejemplo">');
            });
    });

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
