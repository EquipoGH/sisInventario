@extends('layouts.main')

@section('title', 'Gestión de Ubicaciones')

@section('content_header')
    <h1><i class="fas fa-map-marker-alt"></i> Gestión de Ubicaciones</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        {{-- BARRA DE ACCIONES --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Nueva Ubicación
                </button>
                <button type="button" class="btn btn-danger ml-2" id="btnEliminarSeleccionados" style="display:none;">
                    <i class="fas fa-trash-alt"></i> Eliminar (<span id="contadorSeleccionados">0</span>)
                </button>
            </div>
            <div class="col-md-8">
                <div class="row">
                    {{-- FILTRO POR ÁREA --}}
                    <div class="col-md-5">
                        <select id="areaFiltro" class="form-control">
                            <option value="">📍 Todas las áreas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id_area }}">{{ strtoupper($area->nombre_area) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- BÚSQUEDA --}}
                    <div class="col-md-7">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-primary">
                                    <i class="fas fa-search text-white"></i>
                                </span>
                            </div>
                            <input type="text"
                                   id="searchInput"
                                   class="form-control"
                                   placeholder="Buscar por sede, ambiente, piso o área..."
                                   autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="btnLimpiar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block text-right">
                            <span id="infoResultados">
                                Mostrando <strong id="from">{{ $ubicaciones->firstItem() ?? 0 }}</strong>
                                a <strong id="to">{{ $ubicaciones->lastItem() ?? 0 }}</strong>
                                de <strong id="resultadosCount">{{ $ubicaciones->total() }}</strong>
                                (<strong id="totalCount">{{ $total }}</strong> total)
                            </span>
                            <span id="loadingSearch" style="display:none;">
                                <i class="fas fa-spinner fa-spin text-primary"></i> Buscando...
                            </span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- INFO: DOBLE CLICK --}}
        <div class="mb-2 text-right">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> Doble click en una fila para editar
            </small>
        </div>

        {{-- TABLA --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tablaUbicaciones">
                <thead class="thead-dark">
                    <tr>
                        <th width="4%" class="text-center">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th width="6%" class="text-center sortable" data-column="id">
                            ID <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="19%" class="sortable" data-column="sede">
                            Nombre Sede <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="17%" class="sortable" data-column="ambiente">
                            Ambiente <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="10%" class="sortable" data-column="piso">
                            Piso <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="13%" class="sortable" data-column="area">
                            Área <i class="fas fa-sort sort-icon"></i>
                        </th>
                        {{-- ⭐⭐⭐ NUEVA COLUMNA ⭐⭐⭐ --}}
                        <th width="12%" class="text-center">
                            <i class="fas fa-star text-warning"></i> Recepción
                            <i class="fas fa-info-circle text-info"
                               title="Ubicación de recepción inicial"
                               data-toggle="tooltip"></i>
                        </th>
                        <th width="13%" class="sortable" data-column="fecha">
                            Fecha <i class="fas fa-sort sort-icon"></i>
                        </th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    @forelse($ubicaciones as $ubicacion)
                    <tr id="row-{{ $ubicacion->id_ubicacion }}" class="editable-row" data-id="{{ $ubicacion->id_ubicacion }}">
                        <td class="text-center">
                            <input type="checkbox" class="checkbox-item" value="{{ $ubicacion->id_ubicacion }}">
                        </td>
                        <td class="text-center"><strong>{{ $ubicacion->id_ubicacion }}</strong></td>
                        <td><strong>{{ strtoupper($ubicacion->nombre_sede) }}</strong></td>
                        <td>{{ strtoupper($ubicacion->ambiente) }}</td>
                        <td class="text-center">{{ strtoupper($ubicacion->piso_ubicacion) }}</td>
                        <td>
                            <span class="badge badge-info">
                                {{ strtoupper($ubicacion->area->nombre_area ?? 'N/A') }}
                            </span>
                        </td>
                        {{-- ⭐⭐⭐ NUEVA CELDA CON BOTONES ⭐⭐⭐ --}}
                        <td class="text-center">
                            @if($ubicacion->es_recepcion_inicial)
                                <span class="badge badge-success mb-1 d-block">
                                    <i class="fas fa-check-circle"></i> ACTIVA
                                </span>
                                <button class="btn btn-xs btn-warning btn-desmarcar"
                                        data-id="{{ $ubicacion->id_ubicacion }}"
                                        data-nombre="{{ $ubicacion->nombre_sede }}"
                                        title="Desmarcar">
                                    <i class="fas fa-times"></i>
                                </button>
                            @else
                                <button class="btn btn-xs btn-success btn-marcar"
                                        data-id="{{ $ubicacion->id_ubicacion }}"
                                        data-nombre="{{ $ubicacion->nombre_sede }}"
                                        title="Marcar como recepción">
                                    <i class="fas fa-check"></i> Marcar
                                </button>
                            @endif
                        </td>
                        <td class="text-center">{{ $ubicacion->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr id="filaVacia">
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No hay ubicaciones registradas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- PAGINACIÓN --}}
        <div id="paginacionContainer" class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Mostrando <strong id="paginaInfo">{{ $ubicaciones->firstItem() ?? 0 }} - {{ $ubicaciones->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $ubicaciones->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks"></div>
        </div>

        {{-- SIN RESULTADOS --}}
        <div id="noResultados" class="text-center py-4" style="display:none;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
            <h5>No se encontraron resultados</h5>
            <p class="text-muted">No hay ubicaciones que coincidan con "<strong id="terminoBuscado"></strong>"</p>
            <button class="btn btn-outline-primary" id="btnMostrarTodo">
                <i class="fas fa-undo"></i> Mostrar todo
            </button>
        </div>
    </div>
</div>

{{-- ========================= MODAL CREAR ========================= --}}
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-map-marker-alt"></i> Nueva Ubicación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCreate">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_sede">Nombre de la Sede <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="nombre_sede"
                                       id="nombre_sede"
                                       class="form-control text-uppercase"
                                       placeholder="Ej: SEDE CENTRAL"
                                       maxlength="100"
                                       required>
                                <span class="text-danger error-nombre_sede"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ambiente">Ambiente <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="ambiente"
                                       id="ambiente"
                                       class="form-control text-uppercase"
                                       placeholder="Ej: OFICINA 101"
                                       maxlength="100"
                                       required>
                                <span class="text-danger error-ambiente"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="piso_ubicacion">Piso <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="piso_ubicacion"
                                       id="piso_ubicacion"
                                       class="form-control text-uppercase"
                                       placeholder="Ej: 1ER PISO, PLANTA BAJA"
                                       maxlength="100"
                                       required>
                                <span class="text-danger error-piso_ubicacion"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="idarea">Área <span class="text-danger">*</span></label>
                                <select name="idarea" id="idarea" class="form-control" required>
                                    <option value="">-- Seleccione un área --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}">{{ strtoupper($area->nombre_area) }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-idarea"></span>
                            </div>
                        </div>
                    </div>

                    {{-- ⭐⭐⭐ NUEVO CHECKBOX ⭐⭐⭐ --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="es_recepcion_inicial"
                                       name="es_recepcion_inicial"
                                       value="1">
                                <label class="custom-control-label" for="es_recepcion_inicial">
                                    <i class="fas fa-star text-warning"></i>
                                    <strong>Marcar como ubicación de recepción inicial</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted ml-4">
                                <i class="fas fa-info-circle"></i>
                                Los bienes registrados sin ubicación irán aquí automáticamente
                            </small>
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
{{-- ========================= MODAL EDITAR ========================= --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Ubicación
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_nombre_sede">Nombre de la Sede <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="nombre_sede"
                                       id="edit_nombre_sede"
                                       class="form-control text-uppercase"
                                       maxlength="100"
                                       required>
                                <span class="text-danger error-edit-nombre_sede"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_ambiente">Ambiente <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="ambiente"
                                       id="edit_ambiente"
                                       class="form-control text-uppercase"
                                       maxlength="100"
                                       required>
                                <span class="text-danger error-edit-ambiente"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_piso_ubicacion">Piso <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="piso_ubicacion"
                                       id="edit_piso_ubicacion"
                                       class="form-control text-uppercase"
                                       maxlength="100"
                                       required>
                                <span class="text-danger error-edit-piso_ubicacion"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_idarea">Área <span class="text-danger">*</span></label>
                                <select name="idarea" id="edit_idarea" class="form-control" required>
                                    <option value="">-- Seleccione un área --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}">{{ strtoupper($area->nombre_area) }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-idarea"></span>
                            </div>
                        </div>
                    </div>

                    {{-- ⭐⭐⭐ CHECKBOX EN EDITAR ⭐⭐⭐ --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="edit_es_recepcion_inicial"
                                       name="es_recepcion_inicial"
                                       value="1">
                                <label class="custom-control-label" for="edit_es_recepcion_inicial">
                                    <i class="fas fa-star text-warning"></i>
                                    <strong>Marcar como ubicación de recepción inicial</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted ml-4">
                                <i class="fas fa-info-circle"></i>
                                Los bienes registrados sin ubicación irán aquí automáticamente
                            </small>
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
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ⭐ Activar tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // ==================== ESTADO GLOBAL ====================
    let paginaActual = 1;
    let ordenActual = { columna: 'fecha', direccion: 'desc' };
    let terminoBusqueda = '';
    let areaSeleccionada = '';

    actualizarIconosOrdenamiento();

    // ==================== BÚSQUEDA CON DEBOUNCE ====================
    let searchTimeout;

    $('#searchInput').on('keyup', function() {
        terminoBusqueda = $(this).val().trim();
        clearTimeout(searchTimeout);
        paginaActual = 1;

        if (terminoBusqueda.length === 0 || terminoBusqueda.length >= 2) {
            searchTimeout = setTimeout(() => buscar(terminoBusqueda, paginaActual), 400);
        }
    });

    // ==================== FILTRO POR ÁREA ====================
    $('#areaFiltro').on('change', function() {
        areaSeleccionada = $(this).val();
        paginaActual = 1;
        buscar(terminoBusqueda, paginaActual);
    });

    // ==================== FUNCIÓN PRINCIPAL DE BÚSQUEDA ====================
    function buscar(termino, page = 1) {
        mostrarCargando(true);

        $.ajax({
            url: '{{ route("ubicacion.index") }}',
            method: 'GET',
            data: {
                search: termino,
                page: page,
                orden: ordenActual.columna,
                direccion: ordenActual.direccion,
                area_filtro: areaSeleccionada
            },
            dataType: 'json',
            success: function(res) {
                actualizarTabla(res.data);
                actualizarContadores(res);
                actualizarPaginacion(res, termino);
                mostrarCargando(false);

                if (res.resultados === 0) {
                    mostrarSinResultados(termino);
                } else {
                    ocultarSinResultados();
                }
            },
            error: function(xhr) {
                mostrarCargando(false);
                Toast.fire({
                    icon: 'error',
                    title: 'Error al cargar datos',
                    text: xhr.status === 500 ? 'Error del servidor' : 'Error de conexión'
                });
            }
        });
    }

    // ==================== ACTUALIZAR TABLA ====================
    function actualizarTabla(ubicaciones) {
        const tbody = $('#tablaBody');
        tbody.empty();

        if (ubicaciones.length === 0) {
            tbody.append(`
                <tr id="filaVacia">
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No hay ubicaciones registradas
                    </td>
                </tr>
            `);
            $('#checkAll').prop('checked', false).prop('disabled', true);
            return;
        }

        $('#checkAll').prop('disabled', false);

        ubicaciones.forEach(u => {
            const fecha = new Date(u.created_at).toLocaleDateString('es-PE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });

            const areaNombre = u.area ? u.area.nombre_area.toUpperCase() : 'N/A';

            // ⭐ Generar HTML para columna de recepción
            let recepcionHTML = '';
            if (u.es_recepcion_inicial) {
                recepcionHTML = `
                    <span class="badge badge-success mb-1 d-block">
                        <i class="fas fa-check-circle"></i> ACTIVA
                    </span>
                    <button class="btn btn-xs btn-warning btn-desmarcar"
                            data-id="${u.id_ubicacion}"
                            data-nombre="${u.nombre_sede}"
                            title="Desmarcar">
                        <i class="fas fa-times"></i>
                    </button>
                `;
            } else {
                recepcionHTML = `
                    <button class="btn btn-xs btn-success btn-marcar"
                            data-id="${u.id_ubicacion}"
                            data-nombre="${u.nombre_sede}"
                            title="Marcar como recepción">
                        <i class="fas fa-check"></i> Marcar
                    </button>
                `;
            }

            tbody.append(`
                <tr id="row-${u.id_ubicacion}" class="fade-in editable-row" data-id="${u.id_ubicacion}">
                    <td class="text-center">
                        <input type="checkbox" class="checkbox-item" value="${u.id_ubicacion}">
                    </td>
                    <td class="text-center"><strong>${u.id_ubicacion}</strong></td>
                    <td><strong>${u.nombre_sede.toUpperCase()}</strong></td>
                    <td>${u.ambiente.toUpperCase()}</td>
                    <td class="text-center">${u.piso_ubicacion.toUpperCase()}</td>
                    <td>
                        <span class="badge badge-info">${areaNombre}</span>
                    </td>
                    <td class="text-center">${recepcionHTML}</td>
                    <td class="text-center">${fecha}</td>
                </tr>
            `);
        });

        $('.checkbox-item').on('change', actualizarBotonEliminar);
        bindBotonesRecepcion();
    }

    // ⭐⭐⭐ EVENTOS PARA MARCAR/DESMARCAR RECEPCIÓN ⭐⭐⭐
    function bindBotonesRecepcion() {
        // MARCAR COMO RECEPCIÓN
        $('.btn-marcar').off('click').on('click', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');

            Swal.fire({
                title: '¿Marcar como recepción inicial?',
                html: `
                    <p>La ubicación <strong>${nombre}</strong> será la recepción inicial.</p>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle"></i>
                        Los bienes registrados sin ubicación irán aquí automáticamente.
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check"></i> Sí, marcar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    marcarRecepcion(id);
                }
            });
        });

        // DESMARCAR RECEPCIÓN
        $('.btn-desmarcar').off('click').on('click', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');

            Swal.fire({
                title: '¿Desmarcar ubicación?',
                html: `
                    <p><strong>${nombre}</strong> dejará de ser recepción inicial.</p>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        Los nuevos bienes no tendrán ubicación automática.
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-times"></i> Sí, desmarcar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    desmarcarRecepcion(id);
                }
            });
        });
    }

    bindBotonesRecepcion();

    // ⭐ MARCAR UBICACIÓN
    function marcarRecepcion(id) {
        $.ajax({
            url: `/ubicacion/${id}/marcar-recepcion`,
            method: 'POST',
            success: function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    buscar(terminoBusqueda, paginaActual);
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error al marcar',
                    text: xhr.responseJSON?.message || 'Error del servidor'
                });
            }
        });
    }

    // ⭐ DESMARCAR UBICACIÓN
    function desmarcarRecepcion(id) {
        $.ajax({
            url: `/ubicacion/${id}/desmarcar-recepcion`,
            method: 'POST',
            success: function(res) {
                if (res.success) {
                    Toast.fire({ icon: 'info', title: res.message });
                    buscar(terminoBusqueda, paginaActual);
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Error del servidor'
                });
            }
        });
    }

    // ==================== CONTADORES ====================
    function actualizarContadores(res) {
        $('#from').text(res.from || 0);
        $('#to').text(res.to || 0);
        $('#resultadosCount').text(res.resultados);
        $('#totalCount').text(res.total);
        $('#paginaInfo').text((res.from || 0) + ' - ' + (res.to || 0));
    }

    // ==================== PAGINACIÓN ====================
    function actualizarPaginacion(res, termino) {
        const links = $('#paginacionLinks');
        links.empty();

        if (res.last_page <= 1) return;

        let html = '<ul class="pagination pagination-sm m-0">';

        html += generarBotonPaginacion(
            res.current_page > 1,
            res.current_page - 1,
            '<i class="fas fa-chevron-left"></i>'
        );

        const rango = 2;

        for (let i = 1; i <= res.last_page; i++) {
            const esActual = i === res.current_page;
            const esPrimera = i === 1;
            const esUltima = i === res.last_page;
            const estaCerca = Math.abs(i - res.current_page) <= rango;

            if (esActual) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (esPrimera || esUltima || estaCerca) {
                html += `<li class="page-item">
                            <a class="page-link paginar" href="#" data-page="${i}">${i}</a>
                         </li>`;
            } else if (i === res.current_page - rango - 1 || i === res.current_page + rango + 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        html += generarBotonPaginacion(
            res.current_page < res.last_page,
            res.current_page + 1,
            '<i class="fas fa-chevron-right"></i>'
        );

        html += '</ul>';
        links.html(html);

        $('.paginar').on('click', function(e) {
            e.preventDefault();
            paginaActual = $(this).data('page');
            buscar(terminoBusqueda, paginaActual);
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
    }

    function generarBotonPaginacion(activo, pagina, contenido) {
        if (activo) {
            return `<li class="page-item">
                        <a class="page-link paginar" href="#" data-page="${pagina}">${contenido}</a>
                    </li>`;
        }
        return `<li class="page-item disabled">
                    <span class="page-link">${contenido}</span>
                </li>`;
    }

    // ==================== ORDENAMIENTO ====================
    $('.sortable').on('click', function() {
        const columna = $(this).data('column');

        if (ordenActual.columna === columna) {
            ordenActual.direccion = ordenActual.direccion === 'asc' ? 'desc' : 'asc';
        } else {
            ordenActual.columna = columna;
            ordenActual.direccion = columna === 'fecha' ? 'desc' : 'asc';
        }

        actualizarIconosOrdenamiento();
        paginaActual = 1;
        buscar(terminoBusqueda, paginaActual);
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

    // ==================== UI HELPERS ====================
    function mostrarCargando(mostrar) {
        if (mostrar) {
            $('#loadingSearch').show();
            $('#infoResultados').hide();
        } else {
            $('#loadingSearch').hide();
            $('#infoResultados').show();
        }
    }

    function mostrarSinResultados(termino) {
        $('#tablaUbicaciones').hide();
        $('#paginacionContainer').hide();
        $('#terminoBuscado').text(termino);
        $('#noResultados').fadeIn();
    }

    function ocultarSinResultados() {
        $('#noResultados').hide();
        $('#tablaUbicaciones').show();
        $('#paginacionContainer').show();
    }

    // ==================== LIMPIAR BÚSQUEDA ====================
    $('#btnLimpiar, #btnMostrarTodo').on('click', function() {
        $('#searchInput').val('');
        $('#areaFiltro').val('');
        terminoBusqueda = '';
        areaSeleccionada = '';
        paginaActual = 1;
        ordenActual = { columna: 'fecha', direccion: 'desc' };
        actualizarIconosOrdenamiento();
        buscar('', 1);
    });

    // ==================== CHECKBOXES ====================
    $('#checkAll').on('change', function() {
        $('.checkbox-item').prop('checked', $(this).is(':checked'));
        actualizarBotonEliminar();
    });

    $(document).on('change', '.checkbox-item', function() {
        actualizarBotonEliminar();
        const total = $('.checkbox-item').length;
        const checked = $('.checkbox-item:checked').length;
        $('#checkAll').prop('checked', total > 0 && total === checked);
    });

    function actualizarBotonEliminar() {
        const seleccionados = $('.checkbox-item:checked').length;
        $('#contadorSeleccionados').text(seleccionados);

        if (seleccionados > 0) {
            $('#btnEliminarSeleccionados').fadeIn(200);
        } else {
            $('#btnEliminarSeleccionados').fadeOut(200);
        }
    }

    // ==================== ELIMINAR MÚLTIPLES ====================
    $('#btnEliminarSeleccionados').on('click', function() {
        const ids = $('.checkbox-item:checked').map(function() {
            return $(this).val();
        }).get();

        Swal.fire({
            title: `¿Eliminar ${ids.length} ubicación(es)?`,
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarMultiples(ids);
            }
        });
    });

    function eliminarMultiples(ids) {
        let eliminados = 0;
        let errores = 0;

        Swal.fire({
            title: 'Eliminando...',
            html: `Procesando <b>${eliminados}</b> de <b>${ids.length}</b>`,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        Promise.allSettled(
            ids.map(id =>
                $.ajax({
                    url: `/ubicacion/${id}`,
                    method: 'POST',
                    data: { _method: 'DELETE' }
                }).then(() => {
                    eliminados++;
                    Swal.getHtmlContainer().querySelector('b').textContent = eliminados;
                }).catch(() => {
                    errores++;
                })
            )
        ).then(() => {
            Swal.close();

            if (eliminados > 0) {
                Toast.fire({
                    icon: 'success',
                    title: `${eliminados} ubicación(es) eliminada(s)`,
                    text: errores > 0 ? `${errores} no se pudieron eliminar` : ''
                });

                $('#checkAll').prop('checked', false);
                buscar(terminoBusqueda, paginaActual);
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo eliminar ninguna ubicación'
                });
            }
        });
    }

    // ==================== EDITAR (DOBLE CLICK) ====================
    $(document).on('dblclick', '.editable-row', function() {
        const id = $(this).data('id');

        $.get(`/ubicacion/${id}/edit`, function(data) {
            $('#edit_id').val(data.id_ubicacion);
            $('#edit_nombre_sede').val(data.nombre_sede);
            $('#edit_ambiente').val(data.ambiente);
            $('#edit_piso_ubicacion').val(data.piso_ubicacion);
            $('#edit_idarea').val(data.idarea);

            // ⭐ Marcar checkbox
            $('#edit_es_recepcion_inicial').prop('checked', data.es_recepcion_inicial === true || data.es_recepcion_inicial === 1);

            $('#modalEdit').modal('show');
        }).fail(function(xhr) {
            Toast.fire({
                icon: 'error',
                title: 'Error al cargar datos',
                text: xhr.status === 404 ? 'Ubicación no encontrada' : 'Error del servidor'
            });
        });
    });

    // ==================== CREAR ====================
    $('#formCreate').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text('');

        const btn = $('#btnGuardar');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("ubicacion.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if (res.success) {
                    $('#modalCreate').modal('hide');
                    Toast.fire({ icon: 'success', title: res.message });
                    $('#searchInput').val('');
                    $('#areaFiltro').val('');
                    terminoBusqueda = '';
                    areaSeleccionada = '';
                    buscar('', 1);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, (campo, mensajes) => {
                        $(`.error-${campo}`).text(mensajes[0]);
                    });
                } else {
                    Toast.fire({ icon: 'error', title: 'Error al guardar' });
                }
            }
        });
    });

    // ==================== ACTUALIZAR ====================
    $('#formEdit').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text('');

        const btn = $('#btnActualizar');
        const id = $('#edit_id').val();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        $.ajax({
            url: `/ubicacion/${id}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');

                if (res.success) {
                    $('#modalEdit').modal('hide');
                    Toast.fire({ icon: 'success', title: res.message });
                    buscar(terminoBusqueda, paginaActual);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');

                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, (campo, mensajes) => {
                        $(`.error-edit-${campo}`).text(mensajes[0]);
                    });
                } else {
                    Toast.fire({ icon: 'error', title: 'Error al actualizar' });
                }
            }
        });
    });

    // ==================== TEXTO AUTOMÁTICO EN MAYÚSCULAS ====================
    $('.text-uppercase').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // ==================== LIMPIAR MODALES ====================
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('.text-danger').text('');
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        $('.text-danger').text('');
    });

    $('#modalCreate').on('shown.bs.modal', () => $('#nombre_sede').focus());
    $('#modalEdit').on('shown.bs.modal', () => $('#edit_nombre_sede').focus().select());

    // ==================== TOAST NOTIFICATIONS ====================
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
});
</script>
@stop
