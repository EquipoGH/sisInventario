@extends('layouts.main')

@section('title', 'Gestión de Movimientos')

@section('content_header')
    <h1>Gestión de Movimientos</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row mb-3">
            <div class="col-md-4">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Nuevo Movimiento
                </button>
                <button type="button" class="btn btn-danger ml-2" id="btnEliminarSeleccionados" style="display:none;">
                    <i class="fas fa-trash-alt"></i> Eliminar (<span id="contadorSeleccionados">0</span>)
                </button>
            </div>
            <div class="col-md-8">
                <div class="float-right" style="width: 100%; max-width: 500px;">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-primary">
                                <i class="fas fa-search text-white"></i>
                            </span>
                        </div>
                        <input type="text"
                               id="searchInput"
                               class="form-control"
                               placeholder="Buscar por código, denominación, tipo..."
                               autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block text-right">
                        <span id="infoResultados">
                            Mostrando <strong id="from">{{ $movimientos->firstItem() ?? 0 }}</strong>
                            a <strong id="to">{{ $movimientos->lastItem() ?? 0 }}</strong>
                            de <strong id="resultadosCount">{{ $movimientos->total() }}</strong>
                            (<strong id="totalCount">{{ $total }}</strong> total)
                        </span>
                        <span id="loadingSearch" style="display:none;">
                            <i class="fas fa-spinner fa-spin text-primary"></i> Buscando...
                        </span>
                    </small>
                </div>
            </div>
        </div>

        <!-- Filtros Avanzados -->
        <div class="row mb-2">
            <div class="col-md-3">
                <select id="filtroTipo" class="form-control form-control-sm">
                    <option value="">🔍 Tipo de Movimiento</option>
                    @foreach($tiposMovimiento as $tipo)
                        <option value="{{ $tipo->id_tipo_mvto }}">{{ $tipo->tipo_mvto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filtroBien" class="form-control form-control-sm select2">
                    <option value="">📦 Seleccionar Bien</option>
                    @foreach($bienes as $bien)
                        <option value="{{ $bien->id_bien }}">{{ $bien->codigo_patrimonial }} - {{ $bien->denominacion_bien }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" id="filtroFechaDesde" class="form-control form-control-sm" placeholder="Desde">
            </div>
            <div class="col-md-2">
                <input type="date" id="filtroFechaHasta" class="form-control form-control-sm" placeholder="Hasta">
            </div>
            <div class="col-md-2">
                <button type="button" id="btnAplicarFiltros" class="btn btn-info btn-sm btn-block">
                    <i class="fas fa-filter"></i> Aplicar
                </button>
            </div>
        </div>

        <div class="text-right">
            <small class="text-muted"><i class="fas fa-info-circle"></i> Doble click en la fila para ver detalles</small>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th width="5%">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="checkAll">
                                <label class="custom-control-label" for="checkAll"></label>
                            </div>
                        </th>
                        <th width="8%" class="sortable" data-column="id" style="cursor:pointer;">
                            ID <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="10%" class="sortable" data-column="fecha" style="cursor:pointer;">
                            Fecha <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="12%">Código Bien</th>
                        <th width="20%">Denominación</th>
                        <th width="12%" class="sortable" data-column="tipo" style="cursor:pointer;">
                            Tipo Mvto <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="15%">Ubicación</th>
                        <th width="10%">Estado</th>
                        <th width="8%">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaMovimientos">
                    @forelse($movimientos as $movimiento)
                    <tr id="row-{{ $movimiento->id_movimiento }}" class="fila-movimiento" data-id="{{ $movimiento->id_movimiento }}">
                        <td class="text-center">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox-item"
                                       id="check-{{ $movimiento->id_movimiento }}"
                                       value="{{ $movimiento->id_movimiento }}">
                                <label class="custom-control-label" for="check-{{ $movimiento->id_movimiento }}"></label>
                            </div>
                        </td>
                        <td class="text-center">{{ $movimiento->id_movimiento }}</td>
                        <td>{{ \Carbon\Carbon::parse($movimiento->fecha_mvto)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge badge-info">
                                {{ $movimiento->bien->codigo_patrimonial }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ Str::limit($movimiento->bien->denominacion_bien, 30) }}</strong>
                            <br>
                            <small class="text-muted">{{ $movimiento->bien->tipoBien->nombre_tipo }}</small>
                        </td>
                        <td>
                            <span class="badge badge-primary">
                                {{ $movimiento->tipoMovimiento->tipo_mvto }}
                            </span>
                        </td>
                        <td>
                            @if($movimiento->ubicacion)
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> {{ $movimiento->ubicacion->ubicacion_completa }}
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($movimiento->estadoConservacion)
                                <span class="badge badge-success">
                                    {{ $movimiento->estadoConservacion->nombre_estado }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-info btn-ver" title="Ver Detalles" data-id="{{ $movimiento->id_movimiento }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-warning btn-editar" title="Editar" data-id="{{ $movimiento->id_movimiento }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-eliminar" title="Eliminar" data-id="{{ $movimiento->id_movimiento }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No hay movimientos registrados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div id="paginacionContainer" class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Mostrando <strong id="paginaInfo">{{ $movimientos->firstItem() ?? 0 }} - {{ $movimientos->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $movimientos->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks">
                <!-- Links de paginación AJAX -->
            </div>
        </div>

        <!-- Sin resultados -->
        <div id="noResultados" class="text-center py-4" style="display:none;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
            <h5>No se encontraron resultados</h5>
            <p class="text-muted">No hay movimientos que coincidan con "<strong id="terminoBuscado"></strong>"</p>
            <button class="btn btn-outline-primary" id="btnMostrarTodo">
                <i class="fas fa-undo"></i> Mostrar todo
            </button>
        </div>
    </div>
</div>

<!-- Modal Crear Movimiento -->
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Nuevo Movimiento
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCreate">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Bien -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="idbien">Bien <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="idbien" name="idbien" required>
                                    <option value="">Seleccione un bien</option>
                                    @foreach($bienes as $bien)
                                        <option value="{{ $bien->id_bien }}">
                                            {{ $bien->codigo_patrimonial }} - {{ $bien->denominacion_bien }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-idbien d-block mt-1"></span>
                            </div>
                        </div>

                        <!-- Tipo de Movimiento -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_mvto">Tipo de Movimiento <span class="text-danger">*</span></label>
                                <select class="form-control" id="tipo_mvto" name="tipo_mvto" required>
                                    <option value="">Seleccione tipo</option>
                                    @foreach($tiposMovimiento as $tipo)
                                        <option value="{{ $tipo->id_tipo_mvto }}">{{ $tipo->tipo_mvto }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-tipo_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <!-- Fecha -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_mvto">Fecha de Movimiento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_mvto" name="fecha_mvto" required>
                                <span class="text-danger error-fecha_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="idubicacion">Ubicación</label>
                                <select class="form-control" id="idubicacion" name="idubicacion">
                                    <option value="">Sin ubicación</option>
                                    @foreach($ubicaciones as $ubicacion)
                                        <option value="{{ $ubicacion->id_ubicacion }}">
                                            {{ $ubicacion->ubicacion_completa }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-idubicacion d-block mt-1"></span>
                            </div>
                        </div>

                        <!-- ✅ Estado de Conservación - CORREGIDO -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="id_estado_conservacion_bien">Estado de Conservación</label>
                                <select class="form-control" id="id_estado_conservacion_bien" name="id_estado_conservacion_bien">
                                    <option value="">Sin estado</option>
                                    @foreach($estadosConservacion as $estado)
                                        <option value="{{ $estado->id_estado }}">{{ $estado->nombre_estado }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-id_estado_conservacion_bien d-block mt-1"></span>
                            </div>
                        </div>

                        <!-- Detalle Técnico -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="detalle_tecnico">Detalle Técnico</label>
                                <textarea class="form-control" id="detalle_tecnico" name="detalle_tecnico" rows="2" maxlength="500" placeholder="Descripción técnica del movimiento..."></textarea>
                                <small class="text-muted">Máximo 500 caracteres</small>
                                <span class="text-danger error-detalle_tecnico d-block mt-1"></span>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGuardar">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Detalles -->
<div class="modal fade" id="modalVer" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles del Movimiento #<span id="ver-id">-</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Código Bien:</dt>
                            <dd class="col-sm-7"><span class="badge badge-info" id="ver-codigo">-</span></dd>

                            <dt class="col-sm-5">Denominación:</dt>
                            <dd class="col-sm-7" id="ver-denominacion">-</dd>

                            <dt class="col-sm-5">Tipo Movimiento:</dt>
                            <dd class="col-sm-7"><span class="badge badge-primary" id="ver-tipo">-</span></dd>

                            <dt class="col-sm-5">Fecha:</dt>
                            <dd class="col-sm-7" id="ver-fecha">-</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Ubicación:</dt>
                            <dd class="col-sm-7" id="ver-ubicacion">-</dd>

                            <dt class="col-sm-5">Estado:</dt>
                            <dd class="col-sm-7" id="ver-estado">-</dd>

                            <dt class="col-sm-5">Usuario:</dt>
                            <dd class="col-sm-7" id="ver-usuario">-</dd>

                            <dt class="col-sm-5">Registrado:</dt>
                            <dd class="col-sm-7" id="ver-created">-</dd>
                        </dl>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h6><strong>Detalle Técnico:</strong></h6>
                        <p class="text-muted border p-2 rounded" id="ver-detalle-tecnico">-</p>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Movimiento #<span id="edit-id-display">-</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEdit">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <!-- Mismos campos que el formulario de crear -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_idbien">Bien <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_idbien" name="idbien" required>
                                    <option value="">Seleccione un bien</option>
                                    @foreach($bienes as $bien)
                                        <option value="{{ $bien->id_bien }}">
                                            {{ $bien->codigo_patrimonial }} - {{ $bien->denominacion_bien }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-idbien d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tipo_mvto">Tipo de Movimiento <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_tipo_mvto" name="tipo_mvto" required>
                                    <option value="">Seleccione tipo</option>
                                    @foreach($tiposMovimiento as $tipo)
                                        <option value="{{ $tipo->id_tipo_mvto }}">{{ $tipo->tipo_mvto }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-tipo_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_fecha_mvto">Fecha <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_fecha_mvto" name="fecha_mvto" required>
                                <span class="text-danger error-edit-fecha_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_idubicacion">Ubicación</label>
                                <select class="form-control" id="edit_idubicacion" name="idubicacion">
                                    <option value="">Sin ubicación</option>
                                    @foreach($ubicaciones as $ubicacion)
                                        <option value="{{ $ubicacion->id_ubicacion }}">
                                            {{ $ubicacion->ubicacion_completa }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-idubicacion d-block mt-1"></span>
                            </div>
                        </div>

                        <!-- ✅ Estado de Conservación EDITAR - CORREGIDO -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_id_estado_conservacion_bien">Estado de Conservación</label>
                                <select class="form-control" id="edit_id_estado_conservacion_bien" name="id_estado_conservacion_bien">
                                    <option value="">Sin estado</option>
                                    @foreach($estadosConservacion as $estado)
                                        <option value="{{ $estado->id_estado }}">{{ $estado->nombre_estado }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-id_estado_conservacion_bien d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="edit_detalle_tecnico">Detalle Técnico</label>
                                <textarea class="form-control" id="edit_detalle_tecnico" name="detalle_tecnico" rows="2" maxlength="500"></textarea>
                                <span class="text-danger error-edit-detalle_tecnico d-block mt-1"></span>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnActualizar">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let paginaActual = 1;
    let searchTimeout;
    let ordenActual = { columna: 'fecha', direccion: 'desc' };

    // Establecer fecha actual por defecto
    $('#fecha_mvto').val(new Date().toISOString().split('T')[0]);

    // Actualizar iconos de ordenamiento
    actualizarIconosOrdenamiento();

    // ===============================
    // ORDENAMIENTO
    // ===============================
    $('.sortable').on('click', function() {
        const columna = $(this).data('column');

        if (ordenActual.columna === columna) {
            ordenActual.direccion = ordenActual.direccion === 'asc' ? 'desc' : 'asc';
        } else {
            ordenActual.columna = columna;
            ordenActual.direccion = (columna === 'fecha' || columna === 'id') ? 'desc' : 'asc';
        }

        actualizarIconosOrdenamiento();
        paginaActual = 1;
        buscar($('#searchInput').val().trim(), paginaActual);
    });

    function actualizarIconosOrdenamiento() {
        $('.sortable .sort-icon')
            .removeClass('fa-sort-up fa-sort-down')
            .addClass('fa-sort');

        if (ordenActual.columna) {
            const iconoActivo = $(`.sortable[data-column="${ordenActual.columna}"] .sort-icon`);
            iconoActivo
                .removeClass('fa-sort')
                .addClass(ordenActual.direccion === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        }
    }

    // ===============================
    // BÚSQUEDA
    // ===============================
    $('#searchInput').on('keyup', function() {
        const termino = $(this).val().trim();
        clearTimeout(searchTimeout);
        paginaActual = 1;
        searchTimeout = setTimeout(() => buscar(termino, paginaActual), 400);
    });

    $('#btnAplicarFiltros').on('click', function() {
        paginaActual = 1;
        buscar($('#searchInput').val().trim(), paginaActual);
    });

    function buscar(termino, page = 1) {
        $('#loadingSearch').show();
        $('#infoResultados').hide();

        const filtros = {
            search: termino,
            page: page,
            orden: ordenActual.columna,
            direccion: ordenActual.direccion,
            tipo_mvto: $('#filtroTipo').val(),
            bien_id: $('#filtroBien').val(),
            fecha_desde: $('#filtroFechaDesde').val(),
            fecha_hasta: $('#filtroFechaHasta').val()
        };

        $.ajax({
            url: '{{ route("movimiento.index") }}',
            method: 'GET',
            data: filtros,
            dataType: 'json',
            success: function(res) {
                actualizarTabla(res.data);
                actualizarContadores(res);
                actualizarPaginacion(res, termino);
                $('#loadingSearch').hide();
                $('#infoResultados').show();

                if (res.resultados === 0) {
                    $('.table-responsive').hide();
                    $('#paginacionContainer').hide();
                    $('#terminoBuscado').text(termino);
                    $('#noResultados').fadeIn();
                } else {
                    $('#noResultados').hide();
                    $('.table-responsive').show();
                    $('#paginacionContainer').show();
                }
            },
            error: function() {
                $('#loadingSearch').hide();
                $('#infoResultados').show();
                Swal.fire('Error', 'Error en la búsqueda', 'error');
            }
        });
    }

    function actualizarTabla(movimientos) {
        const tbody = $('#tablaMovimientos');
        tbody.empty();

        if (movimientos.length === 0) return;

        movimientos.forEach(m => {
            const fecha = moment(m.fecha_mvto).format('DD/MM/YYYY');

            // ✅ CORREGIDO: Manejar ubicación correctamente
            let ubicacion = '-';
            if (m.ubicacion) {
                const sede = m.ubicacion.nombre_sede || '';
                const ambiente = m.ubicacion.ambiente || '';
                const piso = m.ubicacion.piso_ubicacion || '';

                if (sede && ambiente && piso) {
                    ubicacion = `<i class="fas fa-map-marker-alt"></i> ${sede} - ${ambiente} - Piso ${piso}`;
                } else if (sede && ambiente) {
                    ubicacion = `<i class="fas fa-map-marker-alt"></i> ${sede} - ${ambiente}`;
                } else {
                    ubicacion = `<i class="fas fa-map-marker-alt"></i> ${sede || ambiente || piso}`;
                }
            }

            // ✅ CORREGIDO: Manejar estado correctamente
            let estado = '-';
            if (m.estado_conservacion) {
                estado = `<span class="badge badge-success">${m.estado_conservacion.nombre_estado}</span>`;
            }

            const tipoBien = m.bien && m.bien.tipo_bien ? m.bien.tipo_bien.nombre_tipo : '';

            tbody.append(`
                <tr id="row-${m.id_movimiento}" class="fila-movimiento fade-in" data-id="${m.id_movimiento}">
                    <td class="text-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input checkbox-item"
                                   id="check-${m.id_movimiento}"
                                   value="${m.id_movimiento}">
                            <label class="custom-control-label" for="check-${m.id_movimiento}"></label>
                        </div>
                    </td>
                    <td class="text-center">${m.id_movimiento}</td>
                    <td>${fecha}</td>
                    <td><span class="badge badge-info">${m.bien.codigo_patrimonial}</span></td>
                    <td>
                        <strong>${m.bien.denominacion_bien.substring(0, 30)}${m.bien.denominacion_bien.length > 30 ? '...' : ''}</strong><br>
                        <small class="text-muted">${tipoBien}</small>
                    </td>
                    <td><span class="badge badge-primary">${m.tipo_movimiento.tipo_mvto}</span></td>
                    <td><small class="text-muted">${ubicacion}</small></td>
                    <td>${estado}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info btn-ver" data-id="${m.id_movimiento}" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-warning btn-editar" data-id="${m.id_movimiento}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-eliminar-individual" data-id="${m.id_movimiento}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });

        $('.checkbox-item').on('change', actualizarBotonEliminar);
        $('#checkAll').prop('checked', false);
    }

    function actualizarContadores(res) {
        $('#from').text(res.from || 0);
        $('#to').text(res.to || 0);
        $('#resultadosCount').text(res.resultados);
        $('#totalCount').text(res.total);
        $('#paginaInfo').text((res.from || 0) + ' - ' + (res.to || 0));
    }

    function actualizarPaginacion(res, termino) {
        const links = $('#paginacionLinks');
        links.empty();

        if (res.last_page <= 1) return;

        let html = '<ul class="pagination pagination-sm m-0">';

        if (res.current_page > 1) {
            html += `<li class="page-item">
                        <a class="page-link paginar" href="#" data-page="${res.current_page - 1}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                     </li>`;
        }

        for (let i = 1; i <= res.last_page; i++) {
            if (i == res.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (i == 1 || i == res.last_page || Math.abs(i - res.current_page) <= 2) {
                html += `<li class="page-item">
                            <a class="page-link paginar" href="#" data-page="${i}">${i}</a>
                         </li>`;
            }
        }

        if (res.current_page < res.last_page) {
            html += `<li class="page-item">
                        <a class="page-link paginar" href="#" data-page="${res.current_page + 1}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                     </li>`;
        }

        html += '</ul>';
        links.html(html);

        $('.paginar').on('click', function(e) {
            e.preventDefault();
            paginaActual = $(this).data('page');
            buscar(termino, paginaActual);
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
    }

    $('#btnLimpiar, #btnMostrarTodo').on('click', function() {
        $('#searchInput').val('');
        $('#filtroTipo').val('');
        $('#filtroBien').val('');
        $('#filtroFechaDesde').val('');
        $('#filtroFechaHasta').val('');
        paginaActual = 1;
        ordenActual = { columna: 'fecha', direccion: 'desc' };
        actualizarIconosOrdenamiento();
        buscar('', 1);
    });

    // ===============================
    // CHECKBOX: Seleccionar todos
    // ===============================
    $('#checkAll').on('change', function() {
        $('.checkbox-item').prop('checked', $(this).is(':checked'));
        actualizarBotonEliminar();
    });

    $(document).on('change', '.checkbox-item', actualizarBotonEliminar);

    function actualizarBotonEliminar() {
        let seleccionados = $('.checkbox-item:checked').length;
        $('#contadorSeleccionados').text(seleccionados);
        seleccionados > 0 ? $('#btnEliminarSeleccionados').fadeIn() : $('#btnEliminarSeleccionados').fadeOut();
    }

    // ===============================
    // ELIMINAR SELECCIONADOS
    // ===============================
    $('#btnEliminarSeleccionados').on('click', function() {
        let seleccionados = [];
        $('.checkbox-item:checked').each(function() {
            seleccionados.push($(this).val());
        });

        if (seleccionados.length === 0) return;

        Swal.fire({
            title: '¿Eliminar ' + seleccionados.length + ' movimiento(s)?',
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarMultiples(seleccionados);
            }
        });
    });

    function eliminarMultiples(ids) {
        Swal.fire({
            title: 'Eliminando...',
            html: 'Eliminando <b>0</b> de <b>' + ids.length + '</b> registros',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        let eliminados = 0;
        let promesas = ids.map(id => {
            return $.ajax({
                url: `/movimiento/${id}`,
                method: 'DELETE'
            }).then(() => {
                eliminados++;
                Swal.update({
                    html: 'Eliminando <b>' + eliminados + '</b> de <b>' + ids.length + '</b> registros'
                });
            });
        });

        Promise.allSettled(promesas).then(() => {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: eliminados + ' movimiento(s) eliminado(s)',
                timer: 1500,
                showConfirmButton: false
            }).then(() => buscar($('#searchInput').val(), paginaActual));
        });
    }

    // ===============================
    // VER DETALLES (Doble click o botón)
    // ===============================
    $(document).on('dblclick', '.fila-movimiento', function() {
        const id = $(this).data('id');
        verDetalle(id);
    });

    $(document).on('click', '.btn-ver', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        verDetalle(id);
    });

    function verDetalle(id) {
        $.get(`/movimiento/${id}`, function(res) {
            if (res.success) {
                const m = res.data;

                console.log('═══════════════════════════════════════');
                console.log('📥 DATOS DEL MOVIMIENTO:', m);
                console.log('Estado de conservación:', m.estado_conservacion);
                console.log('═══════════════════════════════════════');

                $('#ver-id').text(m.id_movimiento);
                $('#ver-codigo').text(m.bien.codigo_patrimonial);
                $('#ver-denominacion').text(m.bien.denominacion_bien);
                $('#ver-tipo').text(m.tipo_movimiento.tipo_mvto);
                $('#ver-fecha').text(moment(m.fecha_mvto).format('DD/MM/YYYY'));

                // ✅ UBICACIÓN COMPLETA
                let ubicacionTexto = '<span class="text-muted">Sin ubicación asignada</span>';
                if (m.ubicacion) {
                    const partes = [];
                    if (m.ubicacion.nombre_sede) partes.push(`<strong>Sede:</strong> ${m.ubicacion.nombre_sede}`);
                    if (m.ubicacion.ambiente) partes.push(`<strong>Ambiente:</strong> ${m.ubicacion.ambiente}`);
                    if (m.ubicacion.piso_ubicacion) partes.push(`<strong>Piso:</strong> ${m.ubicacion.piso_ubicacion}`);
                    if (m.ubicacion.area && m.ubicacion.area.nombre_area) {
                        partes.push(`<strong>Área:</strong> ${m.ubicacion.area.nombre_area}`);
                    }

                    if (partes.length > 0) {
                        ubicacionTexto = partes.join('<br>');
                    }
                }
                $('#ver-ubicacion').html(ubicacionTexto);

                // ✅✅ ESTADO DE CONSERVACIÓN - CORREGIDO
                let estadoHTML = '<span class="text-muted">Sin estado registrado</span>';
                if (m.estado_conservacion && m.estado_conservacion.nombre_estado) {
                    const estadoNombre = m.estado_conservacion.nombre_estado.toUpperCase();
                    let badgeColor = 'secondary';

                    if (estadoNombre.includes('BUENO') || estadoNombre.includes('EXCELENTE') || estadoNombre.includes('ÓPTIMO')) {
                        badgeColor = 'success';
                    } else if (estadoNombre.includes('REGULAR') || estadoNombre.includes('MEDIO')) {
                        badgeColor = 'warning';
                    } else if (estadoNombre.includes('MALO') || estadoNombre.includes('CRÍTICO') || estadoNombre.includes('DEFICIENTE')) {
                        badgeColor = 'danger';
                    } else if (estadoNombre.includes('NUEVO')) {
                        badgeColor = 'primary';
                    }

                    estadoHTML = `<span class="badge badge-${badgeColor}" style="font-size: 1rem; padding: 0.5rem 1rem;">
                        <i class="fas fa-check-circle"></i> ${estadoNombre}
                    </span>`;
                }
                $('#ver-estado').html(estadoHTML);

                $('#ver-usuario').text(m.usuario.name);
                $('#ver-created').text(moment(m.created_at).format('DD/MM/YYYY HH:mm'));

                $('#ver-detalle-tecnico').text(m.detalle_tecnico || 'Sin detalle técnico registrado');


                $('#modalVer').modal('show');
            }
        }).fail(function(xhr) {
            console.error('Error al cargar detalles:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar los detalles del movimiento'
            });
        });
    }

    // ===============================
    // CREAR
    // ===============================
    $('#formCreate').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores('');

        const formData = {
            idbien: $('#idbien').val(),
            tipo_mvto: $('#tipo_mvto').val(),
            fecha_mvto: $('#fecha_mvto').val(),
            idubicacion: $('#idubicacion').val() || null,
            id_estado_conservacion_bien: $('#id_estado_conservacion_bien').val() || null,  // ✅ CORREGIDO
            detalle_tecnico: $('#detalle_tecnico').val(),

        };

        console.log('📤 CREAR - Datos a enviar:', formData);

        let btnGuardar = $('#btnGuardar');
        btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("movimiento.store") }}',
            method: 'POST',
            data: formData,
            success: function(res) {
                console.log('✅ CREAR - Respuesta:', res);

                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if(res.success) {
                    $('#modalCreate').modal('hide');
                    $('#formCreate')[0].reset();
                    $('#fecha_mvto').val(new Date().toISOString().split('T')[0]);

                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => buscar('', 1));
                }
            },
            error: function(xhr) {
                console.error('❌ CREAR - Error:', xhr.responseJSON);

                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if(xhr.status === 422) {
                    mostrarErrores(xhr.responseJSON.errors, '');
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo guardar', 'error');
                }
            }
        });
    });

    // ===============================
    // EDITAR
    // ===============================
    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        limpiarErrores('edit-');

        $.get(`/movimiento/${id}/edit`, function(res) {
            if (res.success) {
                const m = res.data;

                console.log('═══════════════════════════════════════');
                console.log('📥 EDITAR - Datos recibidos:', m);
                console.log('Estado actual:', m.id_estado_conservacion_bien);
                console.log('═══════════════════════════════════════');

                $('#edit-id-display').text(m.id_movimiento);
                $('#edit_id').val(m.id_movimiento);
                $('#edit_idbien').val(m.idbien);
                $('#edit_tipo_mvto').val(m.tipo_mvto);
                $('#edit_fecha_mvto').val(m.fecha_mvto);
                $('#edit_idubicacion').val(m.idubicacion || '');
                $('#edit_id_estado_conservacion_bien').val(m.id_estado_conservacion_bien || '');  // ✅ CORREGIDO
                $('#edit_detalle_tecnico').val(m.detalle_tecnico || '');


                console.log('Valor asignado al select estado:', $('#edit_id_estado_conservacion_bien').val());

                $('#modalEdit').modal('show');
            }
        });
    });

    $('#formEdit').on('submit', function(e) {
        e.preventDefault();
        limpiarErrores('edit-');

        let btnActualizar = $('#btnActualizar');
        btnActualizar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        let id = $('#edit_id').val();

        const formData = {
            idbien: $('#edit_idbien').val(),
            tipo_mvto: $('#edit_tipo_mvto').val(),
            fecha_mvto: $('#edit_fecha_mvto').val(),
            idubicacion: $('#edit_idubicacion').val() || null,
            id_estado_conservacion_bien: $('#edit_id_estado_conservacion_bien').val() || null,  // ✅ CORREGIDO
            detalle_tecnico: $('#edit_detalle_tecnico').val(),

        };

        console.log('═══════════════════════════════════════');
        console.log('📤 EDITAR - Datos a enviar:', formData);
        console.log('Estado a enviar:', formData.id_estado_conservacion_bien);
        console.log('═══════════════════════════════════════');

        $.ajax({
            url: `/movimiento/${id}`,
            method: 'PUT',
            data: formData,
            success: function(res) {
                console.log('═══════════════════════════════════════');
                console.log('✅ EDITAR - Respuesta exitosa:', res);
                if (res.data && res.data.id_estado_conservacion_bien) {
                    console.log('🎯 Estado guardado:', res.data.id_estado_conservacion_bien);
                }
                console.log('═══════════════════════════════════════');

                btnActualizar.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');

                if(res.success) {
                    $('#modalEdit').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => buscar($('#searchInput').val(), paginaActual));
                }
            },
            error: function(xhr) {
                console.log('═══════════════════════════════════════');
                console.error('❌ EDITAR - Error:', xhr.responseJSON);
                console.log('═══════════════════════════════════════');

                btnActualizar.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');

                if(xhr.status === 422) {
                    mostrarErrores(xhr.responseJSON.errors, 'edit-');
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo actualizar', 'error');
                }
            }
        });
    });

    // ===============================
    // ELIMINAR INDIVIDUAL
    // ===============================
    $(document).on('click', '.btn-eliminar-individual', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar movimiento?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/movimiento/${id}`,
                    method: 'DELETE',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => buscar($('#searchInput').val(), paginaActual));
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo eliminar', 'error');
                    }
                });
            }
        });
    });

    // ===============================
    // UTILIDADES
    // ===============================
    function mostrarErrores(errors, prefix) {
        $.each(errors, function(key, value) {
            $(`.error-${prefix}${key}`).text(value[0]);
        });
    }

    function limpiarErrores(prefix) {
        $(`.error-${prefix}idbien, .error-${prefix}tipo_mvto, .error-${prefix}fecha_mvto,
           .error-${prefix}idubicacion, .error-${prefix}id_estado_conservacion_bien,
           .error-${prefix}detalle_tecnico`).text('');
    }

    // Limpiar formularios al cerrar modales
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('#fecha_mvto').val(new Date().toISOString().split('T')[0]);
        limpiarErrores('');
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        limpiarErrores('edit-');
    });

    // Focus automático
    $('#modalCreate').on('shown.bs.modal', function() {
        $('#idbien').focus();
    });
});
</script>
@stop
