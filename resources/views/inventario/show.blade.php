@extends('layouts.main')

@section('title', 'Detalle de Inventario')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-clipboard-check"></i> Gestión de Bienes - Inventario 
            <span class="text-primary">{{ $inventario->codigoinventario }}</span>
        </h1>
        <a href="{{ route('inventario.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="row">
    {{-- RESUMEN CABECERA --}}
    <div class="col-md-3">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">Detalles del Inventario</h3>
                <p class="text-muted text-center">{!! $inventario->getBadgeEstado() !!}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Tipo</b> <a class="float-right">{{ $inventario->tipoinventario ?? '-' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>F. Inicio</b> <a class="float-right">{{ $inventario->fecha_inicio ? $inventario->fecha_inicio->format('d/m/Y') : '-' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>F. Fin</b> <a class="float-right">{{ $inventario->fecha_fin ? $inventario->fecha_fin->format('d/m/Y') : 'En curso' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Responsable</b> <br>
                        <small class="text-muted">{{ $inventario->responsablePersona ? $inventario->responsablePersona->nombre_responsable . ' ' . $inventario->responsablePersona->apellidos_responsable : '-' }}</small>
                    </li>
                    <li class="list-group-item">
                        <b>Progreso de Verificación</b> 
                        <div class="mt-2">
                            <div class="progress" style="height: 20px;">
                                <div id="progressBarAvance" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                            <div class="text-center mt-1 small">
                                <span id="contadorConformes" class="font-weight-bold text-success">0</span> conformes de 
                                <span id="contadorEsperados" class="font-weight-bold text-primary">0</span> esperados
                            </div>
                        </div>
                    </li>
                </ul>

                @if($inventario->estaCerrado() || strtolower($inventario->estadoinventario) == 'cerrado')
    @php
        // Debug output to verify condition values
        // Uncomment the line below for debugging purposes
        // logger('Download button condition: estaCerrado=' . ($inventario->estaCerrado() ? 'true' : 'false') . ', estadoinventario=' . $inventario->estadoinventario);
    @endphp
    <a href="{{ route('inventario.acta', $inventario->id_inventario) }}" class="btn btn-primary btn-block" target="_blank">
        <i class="fas fa-download"></i> Descargar Acta
    </a>

    @if(Auth::user()->esAdmin())
        <button type="button" id="btnRegularizarUbicaciones" class="btn btn-warning btn-block mt-2">
            <i class="fas fa-sync-alt"></i> Formalizar Ubicaciones
        </button>
    @endif
@endif

@if($inventario->puedeEditarse() && Auth::user()->esAdmin())
    <button type="button" id="btnFinalizarInventario" class="btn btn-success btn-block mt-3">
        <i class="fas fa-check-double"></i> Finalizar Inventario
    </button>
@endif
            </div>
        </div>
    </div>

    {{-- GESTIÓN DE DETALLES --}}
    <div class="col-md-9">
        <div class="card">
            <div class="card-header p-2 d-flex justify-content-between align-items-center">
                <ul class="nav nav-pills" id="tabsInventario">
                    <li class="nav-item"><a class="nav-link active" href="#lista" data-toggle="tab"><i class="fas fa-list"></i> Verificados (<span id="tabCountVerificados">0</span>)</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="#faltantes" data-toggle="tab"><i class="fas fa-exclamation-triangle"></i> Faltantes (<span id="tabCountFaltantes">0</span>)</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="#sobrantes" data-toggle="tab"><i class="fas fa-plus-circle"></i> Sobrantes (<span id="tabCountSobrantes">0</span>)</a></li>
                </ul>
                @if($inventario->puedeEditarse())
                    <button class="btn btn-primary btn-sm" id="btnAgregarBien" data-toggle="modal" data-target="#modalAgregarBien">
                        <i class="fas fa-plus"></i> Añadir Bien
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="tab-content">
                    {{-- TAB: VERIFICADOS --}}
                    <div class="active tab-pane fade show" id="lista">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="tablaDetalles">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Cód. Patrimonial</th>
                                        <th>Bien</th>
                                        <th>Ubicación Orig.</th>
                                        <th>Estado Conserv.</th>
                                        <th>Verificación</th>
                                        <th width="10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaDetallesBody">
                                    {{-- Se llena por AJAX --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB: FALTANTES --}}
                    <div class="tab-pane fade" id="faltantes">
                        <div class="alert alert-danger">
                            <h5><i class="icon fas fa-ban"></i> Bienes Faltantes</h5>
                            Estos bienes están registrados en el sistema como asignados a las áreas de este inventario, pero <strong>NO han sido verificados (no encontrados)</strong>.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-danger text-white">
                                    <tr>
                                        <th>Cód. Patrimonial</th>
                                        <th>Bien</th>
                                        <th>Tipo Bien</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaFaltantesBody">
                                    {{-- Se llena por AJAX --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB: SOBRANTES --}}
                    <div class="tab-pane fade" id="sobrantes">
                        <div class="alert alert-warning">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> Bienes Sobrantes</h5>
                            Estos bienes fueron verificados físicamente, pero según el sistema <strong>pertenecen a otra área u otro responsable</strong>.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-warning">
                                    <tr>
                                        <th>Cód. Patrimonial</th>
                                        <th>Bien</th>
                                        <th>Ubicación Original</th>
                                        <th>Ubicación Detectada</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaSobrantesBody">
                                    {{-- Se llena por AJAX --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL AGREGAR BIEN --}}
@if($inventario->puedeEditarse())
<div class="modal fade" id="modalAgregarBien" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-search-plus"></i> Seleccionar Bien a Inventariar</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formAgregarBien">
                <input type="hidden" name="id_inventario" value="{{ $inventario->id_inventario }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Buscar Bien <span class="text-danger">*</span></label>
                        <select name="id_movimiento" id="select_bienes" class="form-control" style="width: 100%;" required>
                            <option value="">Cargando bienes disponibles...</option>
                        </select>
                        <div class="alert alert-info mt-2 py-1 px-2 mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle"></i> 
                            @if($inventario->getRawOriginal('tipoinventario') === \App\Models\Inventario::TIPO_BAJA)
                                <strong>Modo Baja:</strong> Solo se muestran bienes que ya están marcados como <strong>Inactivos/Baja</strong>.
                            @else
                                <strong>Modo Estándar:</strong> Solo se muestran bienes en estado <strong>Activo</strong>.
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Estado de Verificación <span class="text-danger">*</span></label>
                            <select name="estadoverificacion" class="form-control" required>
                                <option value="verificado" selected>Verificado (Conforme)</option>
                                <option value="observado">Observado (Con Daños/Diferencias)</option>
                                <option value="no_encontrado">No Encontrado (Perdido)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Estado de Conservación <span class="text-danger">*</span></label>
                            <select name="estado_conservacion" class="form-control" required>
                                @foreach($estadosConservacion as $est)
                                    <option value="{{ $est->id_estado_conservacion }}">{{ $est->nombre_conservacion }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Ubicación Real / Detectada</label>
                        <select name="ubicaciondetectada" class="form-control select2" style="width: 100%;">
                            <option value="">Es la misma ubicación original (No se movió)</option>
                            @foreach($ubicaciones as $ubi)
                                <option value="{{ $ubi->id_ubicacion }}">{{ $ubi->area->nombre_area ?? '-' }} ({{ $ubi->ambiente ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observacion" class="form-control" rows="2" placeholder="Opcional. Escriba si el bien está deteriorado, en otro lugar, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnGuardarDetalle"><i class="fas fa-save"></i> Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL EDITAR DETALLE --}}
@if($inventario->puedeEditarse())
<div class="modal fade" id="modalEditDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Actualizar Verificación</h5>
                <button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formEditDetalle">
                @method('PUT')
                <input type="hidden" name="id_inventario" value="{{ $inventario->id_inventario }}">
                <input type="hidden" id="edit_detalle_id">
                <div class="modal-body">
                    <div class="alert alert-secondary">
                        <strong>Bien:</strong> <span id="lbl_bien_desc"></span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Estado de Verificación <span class="text-danger">*</span></label>
                            <select name="estadoverificacion" id="edit_estadoverificacion" class="form-control" required>
                                <option value="verificado">Verificado (Conforme)</option>
                                <option value="observado">Observado (Con Daños/Diferencias)</option>
                                <option value="no_encontrado">No Encontrado (Perdido)</option>
                                <option value="pendiente">Pendiente</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Estado de Conservación <span class="text-danger">*</span></label>
                            <select name="estado_conservacion" id="edit_estado_conservacion" class="form-control" required>
                                @foreach($estadosConservacion as $est)
                                    <option value="{{ $est->id_estado_conservacion }}">{{ $est->nombre_conservacion }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Ubicación Real / Detectada</label>
                        <select name="ubicaciondetectada" id="edit_ubicaciondetectada" class="form-control select2" style="width: 100%;">
                            <option value="">Misma ubicación original</option>
                            @foreach($ubicaciones as $ubi)
                                <option value="{{ $ubi->id_ubicacion }}">{{ $ubi->area->nombre_area ?? '-' }} ({{ $ubi->ambiente ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observacion" id="edit_observacion" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="btnActualizarDetalle"><i class="fas fa-sync"></i> Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const id_inventario = {{ $inventario->id_inventario }};
    const puedeEditarse = {{ $inventario->puedeEditarse() ? 'true' : 'false' }};
    const esAdmin = {{ Auth::user()->esAdmin() ? 'true' : 'false' }};
    
    // Variables globales
    let detallesCache = [];
    let estadisticasCache = {};

    $(document).ready(function() {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $('.select2').select2({ theme: 'bootstrap4' });
        
        if (puedeEditarse) {
            $('#select_bienes').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modalAgregarBien'),
                placeholder: 'Busque un bien por código o denominación...',
                minimumInputLength: 2,
                language: {
                    inputTooShort: function() { return "Por favor ingrese 2 o más caracteres"; },
                    noResults: function() { return "No se encontraron bienes disponibles"; },
                    searching: function() { return "Buscando..."; }
                },
                ajax: {
                    url: `/inventario/${id_inventario}/bienes-disponibles`,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return { results: data.results };
                    },
                    cache: true
                }
            });
            $('#edit_ubicaciondetectada').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modalEditDetalle')
            });
        }

        // 1. CARGAR DATOS
        cargarDetalles();
        // cargarBienesDisponibles() se eliminó porque Select2 ahora usa AJAX directamente

        // ==================== METODOS AJAX ====================
        
        function cargarDetalles() {
            $.get(`/inventario/${id_inventario}`, function(res) {
                if(res.success) {
                    detallesCache = res.data.detalles || [];
                    estadisticasCache = res.data.estadisticas_conciliacion || {};
                    
                    renderizarTabla(detallesCache);
                    renderizarFaltantes();
                    renderizarSobrantes();
                    actualizarContadores();
                }
            });
        }

        // La función cargarBienesDisponibles fue reemplazada por la carga AJAX nativa de Select2

        function renderizarTabla(detalles) {
            const tbody = $('#tablaDetallesBody');
            tbody.empty();

            if(detalles.length === 0) {
                tbody.append(`<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-box-open fa-2x mb-2 d-block"></i> Aún no se han agregado bienes a este inventario.</td></tr>`);
                return;
            }

            detalles.forEach(d => {
                let btnAcciones = '';
                let rowClass = '';
                if (puedeEditarse && esAdmin) {
                    btnAcciones = `
                        <button class="btn btn-danger btn-sm btn-delete-detalle" data-id="${d.id_detalle_inv}" title="Quitar de la lista"><i class="fas fa-times"></i></button>
                    `;
                    rowClass = 'editable-row';
                }

                tbody.append(`
                    <tr class="${rowClass}" data-id="${d.id_detalle_inv}">
                        <td><strong>${d.bien ? d.bien.codigo_patrimonial : '-'}</strong></td>
                        <td>
                            ${d.bien ? d.bien.denominacion_bien : '-'}
                            ${d.requiereregularizacion ? '<br><span class="badge badge-danger">Requiere Regularización</span>' : ''}
                        </td>
                        <td>${d.ubicacion_original ? (d.ubicacion_original.area + ' (' + d.ubicacion_original.ambiente + ')') : '-'}</td>
                        <td><span class="${d.estado_conservacion ? d.estado_conservacion.badge : 'badge badge-secondary'}">${d.estado_conservacion ? d.estado_conservacion.nombre : '-'}</span></td>
                        <td>${d.badgeverificacion}</td>
                        <td class="text-center">${btnAcciones}</td>
                    </tr>
                `);
            });
        }

        function renderizarFaltantes() {
            const tbody = $('#tablaFaltantesBody');
            tbody.empty();
            const faltantes = estadisticasCache.bienes_faltantes || [];

            if(faltantes.length === 0) {
                tbody.append(`<tr><td colspan="3" class="text-center text-muted py-4"><i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i> No hay bienes faltantes. ¡Excelente!</td></tr>`);
                return;
            }

            faltantes.forEach(b => {
                tbody.append(`
                    <tr>
                        <td><strong>${b.codigo_patrimonial}</strong></td>
                        <td>${b.denominacion_bien}</td>
                        <td>${b.tipo_bien}</td>
                    </tr>
                `);
            });
        }

        function renderizarSobrantes() {
            const tbody = $('#tablaSobrantesBody');
            tbody.empty();
            const sobrantesIds = estadisticasCache.sobrantes_ids || [];
            
            // Los sobrantes son detalles cuyo id_bien está en sobrantes_ids
            const sobrantes = detallesCache.filter(d => d.bien && sobrantesIds.includes(d.bien.id_bien));

            if(sobrantes.length === 0) {
                tbody.append(`<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i> No hay bienes sobrantes.</td></tr>`);
                return;
            }

            sobrantes.forEach(d => {
                tbody.append(`
                    <tr>
                        <td><strong>${d.bien ? d.bien.codigo_patrimonial : '-'}</strong></td>
                        <td>${d.bien ? d.bien.denominacion_bien : '-'}</td>
                        <td class="text-muted">${d.ubicacion_original ? (d.ubicacion_original.area + ' (' + d.ubicacion_original.ambiente + ')') : '-'}</td>
                        <td class="font-weight-bold text-primary">${d.ubicacion_detectada ? (d.ubicacion_detectada.area + ' (' + d.ubicacion_detectada.ambiente + ')') : 'Misma Original'}</td>
                    </tr>
                `);
            });
        }

        function actualizarContadores() {
            if (!estadisticasCache) return;

            $('#contadorConformes').text(estadisticasCache.verificados_conformes || 0);
            $('#contadorEsperados').text(estadisticasCache.total_esperados || 0);
            
            const progreso = estadisticasCache.progreso_porcentaje || 0;
            $('#progressBarAvance')
                .css('width', progreso + '%')
                .attr('aria-valuenow', progreso)
                .text(progreso + '%');

            $('#tabCountVerificados').text(estadisticasCache.total_verificados || 0);
            $('#tabCountFaltantes').text(estadisticasCache.total_faltantes || 0);
            $('#tabCountSobrantes').text(estadisticasCache.total_sobrantes || 0);
        }

        // ==================== GUARDAR NUEVO DETALLE ====================
        $('#formAgregarBien').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnGuardarDetalle');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registrando...');

            $.ajax({
                url: `/inventario/${id_inventario}/detalles`,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Registrar');
                    if(res.success) {
                        $('#modalAgregarBien').modal('hide');
                        Toast.fire({ icon: 'success', title: 'Bien registrado en el inventario' });
                        cargarDetalles();
                        $('#select_bienes').val(null).trigger('change');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Registrar');
                    let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error al guardar';
                    Toast.fire({ icon: 'error', title: msg });
                }
            });
        });

        // ==================== EDITAR DETALLE ====================
        $(document).on('dblclick', '.editable-row', function() {
            const id = $(this).data('id');
            const detalle = detallesCache.find(d => d.id_detalle_inv == id);
            
            if(detalle) {
                $('#edit_detalle_id').val(detalle.id_detalle_inv);
                $('#lbl_bien_desc').text(`[${detalle.bien.codigo_patrimonial}] ${detalle.bien.denominacion_bien}`);
                
                $('#edit_estadoverificacion').val(detalle.estadoverificacion);
                $('#edit_estado_conservacion').val(detalle.estado_conservacion ? detalle.estado_conservacion.id : '');
                $('#edit_ubicaciondetectada').val(detalle.ubicacion_detectada ? detalle.ubicacion_detectada.id_ubicacion : '').trigger('change');
                $('#edit_observacion').val(detalle.observacion);
                $('#edit_requiereregularizacion').prop('checked', !!detalle.requiereregularizacion);

                $('#modalEditDetalle').modal('show');
            }
        });

        $('#formEditDetalle').on('submit', function(e) {
            e.preventDefault();
            const idDetalle = $('#edit_detalle_id').val();
            const btn = $('#btnActualizarDetalle');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            $.ajax({
                url: `/inventario/${id_inventario}/detalles/${idDetalle}`,
                method: 'PUT',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Actualizar');
                    if(res.success) {
                        $('#modalEditDetalle').modal('hide');
                        Toast.fire({ icon: 'success', title: 'Verificación actualizada' });
                        cargarDetalles();
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Actualizar');
                    Toast.fire({ icon: 'error', title: 'Error al actualizar' });
                }
            });
        });

        // ==================== ELIMINAR DETALLE ====================
        $(document).on('click', '.btn-delete-detalle', function() {
            const idDetalle = $(this).data('id');
            
            Swal.fire({
                title: '¿Quitar Bien?',
                text: "Se eliminará la verificación de este bien en el inventario actual.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/inventario/${id_inventario}/detalles/${idDetalle}`,
                        method: 'DELETE',
                        success: function(res) {
                            if(res.success) {
                                Toast.fire({ icon: 'success', title: 'Bien retirado' });
                                cargarDetalles();
                                $('#select_bienes').val(null).trigger('change');
                            }
                        }
                    });
                }
            });
        });

        // ==================== FINALIZAR INVENTARIO ====================
        $('#btnFinalizarInventario').on('click', function() {
            Swal.fire({
                title: '¿Finalizar Inventario?',
                html: `
                    <p>El inventario pasará a estado <strong>CERRADO</strong>.</p>
                    <div class="alert alert-info text-left">
                        <i class="fas fa-info-circle"></i> 
                        Una vez cerrado, podrá descargar el <strong>Acta de Inventario</strong> para proceder con las regularizaciones manuales.
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check-double"></i> Sí, Finalizar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/inventario/${id_inventario}/cambiar-estado`,
                        method: 'POST',
                        data: { 
                            estadoinventario: 'cerrado'
                        },
                        success: function(res) {
                            if(res.success) {
                                Swal.fire('¡Cerrado!', 'El inventario ha sido finalizado con éxito.', 'success')
                                .then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Atención', res.message || 'No se pudo cerrar el inventario.', 'warning');
                            }
                        },
                        error: function(xhr) {
                            let msg = 'Error desconocido al intentar cerrar.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error de Validación', msg, 'error');
                        }
                    });
                }
            });
        });

        // ==================== REGULARIZAR UBICACIONES ====================
        $('#btnRegularizarUbicaciones').on('click', function() {
            Swal.fire({
                title: '¿Formalizar Ubicaciones?',
                html: `
                    <div class="text-left">
                        <p>Esta acción creará <strong>Movimientos de Reasignación</strong> automáticos para todos los bienes cuya ubicación física detectada sea distinta a la registrada en el sistema.</p>
                        <p class="text-danger small"><i class="fas fa-exclamation-triangle"></i> Asegúrese de que el <strong>Acta de Inventario</strong> esté firmada antes de proceder.</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f39c12',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-sync-alt"></i> Sí, Formalizar en Sistema',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: `/inventario/${id_inventario}/regularizar`,
                        method: 'POST',
                        success: function(res) {
                            if(res.success) {
                                Swal.fire('¡Éxito!', res.message, 'success');
                                cargarDetalles();
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'No se pudo completar la regularización.', 'error');
                        }
                    });
                }
            });
        });

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
        
        // Reset modales
        $('#modalAgregarBien').on('hidden.bs.modal', function() {
            $('#formAgregarBien')[0].reset();
            $('#select_bienes').val('').trigger('change');
        });
    });
</script>
<style>
    /* Indicador de fila editable */
    .editable-row { cursor: pointer; }
    .editable-row:hover { background-color: rgba(0,123,255,0.05) !important; }
</style>
@stop
