@extends('layouts.main')

@section('title', 'Asignación de Responsables a Áreas')

@section('content_header')
    <h1><i class="fas fa-user-tag"></i> Asignación de Responsables a Áreas</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        {{-- BARRA DE ACCIONES --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Nueva Asignación
                </button>
                <button type="button" class="btn btn-danger ml-2" id="btnEliminarSeleccionados" style="display:none;">
                    <i class="fas fa-trash-alt"></i> Eliminar (<span id="contadorSeleccionados">0</span>)
                </button>
            </div>
            <div class="col-md-12">
                {{-- BARRA DE FILTROS EN UNA SOLA FILA --}}
                <div class="d-flex flex-wrap align-items-center gap-2" style="gap:.5rem;">

                    {{-- Responsable --}}
                    <div class="input-group" style="max-width:220px; flex:1 1 180px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-success text-white py-1 px-2">
                                <i class="fas fa-user fa-sm"></i>
                            </span>
                        </div>
                        <select id="responsableFiltro" class="form-control form-control-sm">
                            <option value="">Todos los responsables</option>
                            @foreach($responsables as $resp)
                                <option value="{{ $resp->dni_responsable }}">
                                    {{ $resp->dni_responsable }} - {{ strtoupper($resp->apellidos_responsable) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Área --}}
                    <div class="input-group" style="max-width:180px; flex:1 1 150px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-info text-white py-1 px-2">
                                <i class="fas fa-building fa-sm"></i>
                            </span>
                        </div>
                        <select id="areaFiltro" class="form-control form-control-sm">
                            <option value="">Todas las áreas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id_area }}">{{ strtoupper($area->nombre_area) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Año --}}
                    <div class="input-group" style="max-width:120px; flex:0 0 110px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-warning text-dark py-1 px-2">
                                <i class="fas fa-calendar-alt fa-sm"></i>
                            </span>
                        </div>
                        <select id="anioFiltro" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($aniosDisponibles as $anio)
                                <option value="{{ $anio }}">{{ $anio }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Búsqueda --}}
                    <div class="input-group" style="max-width:220px; flex:1 1 180px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-primary text-white py-1 px-2">
                                <i class="fas fa-search fa-sm"></i>
                            </span>
                        </div>
                        <input type="text" id="searchInput" class="form-control form-control-sm"
                               placeholder="Buscar DNI, nombre, área..." autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-sm btn-outline-secondary" type="button" id="btnLimpiar" title="Limpiar todo">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

                {{-- INFO RESULTADOS --}}
                <div class="row mt-2">
                    <div class="col-md-12">
                        <small class="text-muted float-right">
                            <span id="infoResultados">
                                Mostrando <strong id="from">{{ $asignaciones->firstItem() ?? 0 }}</strong>
                                a <strong id="to">{{ $asignaciones->lastItem() ?? 0 }}</strong>
                                de <strong id="resultadosCount">{{ $asignaciones->total() }}</strong>
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
            <table class="table table-bordered table-striped table-hover" id="tablaAsignaciones">
                <thead class="thead-dark">
                    <tr>
                        <th width="4%" class="text-center">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th width="11%" class="text-center sortable" data-column="dni">
                            DNI <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="28%" class="sortable" data-column="responsable">
                            Responsable <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="20%" class="sortable" data-column="area">
                            Área <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="9%" class="text-center sortable" data-column="anio">
                            Período <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="18%" class="sortable" data-column="fecha">
                            Fecha Asignación <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="10%" class="text-center">
                            Historial
                        </th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    @forelse($asignaciones as $asignacion)
                    <tr id="row-{{ $asignacion->id_responsable_area }}" class="editable-row" data-id="{{ $asignacion->id_responsable_area }}">
                        <td class="text-center">
                            <input type="checkbox" class="checkbox-item" value="{{ $asignacion->id_responsable_area }}">
                        </td>
                        <td class="text-center">{{ $asignacion->dni_responsable }}</td>
                        <td>
                            {{ strtoupper($asignacion->responsable->nombre_responsable ?? 'N/A') }} {{ strtoupper($asignacion->responsable->apellidos_responsable ?? '') }}
                            <br>
                            <small class="text-muted">{{ strtoupper($asignacion->responsable->cargo_responsable ?? 'N/A') }}</small>
                        </td>
                        <td>{{ strtoupper($asignacion->area->nombre_area ?? 'N/A') }}</td>
                        <td class="text-center"><span class="badge badge-primary">{{ $asignacion->periodo_anio ?? date('Y') }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($asignacion->fecha_asignacion)->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-info btn-historial"
                                    data-dni="{{ $asignacion->dni_responsable }}"
                                    title="Ver trazabilidad">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr id="filaVacia">
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No hay asignaciones registradas
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
                    Mostrando <strong id="paginaInfo">{{ $asignaciones->firstItem() ?? 0 }} - {{ $asignaciones->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $asignaciones->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks"></div>
        </div>

        {{-- SIN RESULTADOS --}}
        <div id="noResultados" class="text-center py-4" style="display:none;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
            <h5>No se encontraron resultados</h5>
            <p class="text-muted">No hay asignaciones que coincidan con los filtros aplicados</p>
            <button class="btn btn-outline-primary" id="btnMostrarTodo">
                <i class="fas fa-undo"></i> Limpiar filtros
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
                    <i class="fas fa-user-tag"></i> Nueva Asignación de Responsable a Área
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
                                <label for="dni_responsable">Responsable <span class="text-danger">*</span></label>
                                <select name="dni_responsable" id="dni_responsable" class="form-control" required>
                                    <option value="">-- Seleccione un responsable --</option>
                                    @foreach($responsables as $resp)
                                        <option value="{{ $resp->dni_responsable }}">
                                            {{ $resp->dni_responsable }} - {{ strtoupper($resp->nombre_responsable) }} {{ strtoupper($resp->apellidos_responsable) }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-dni_responsable"></span>
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

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="periodo_anio"><i class="fas fa-calendar-alt text-warning"></i> Período <span class="text-danger">*</span></label>
                                <select name="periodo_anio" id="periodo_anio" class="form-control" required>
                                    @foreach($aniosDisponibles as $anio)
                                        <option value="{{ $anio }}" {{ $anio == $anioActual ? 'selected' : '' }}>{{ $anio }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-periodo_anio"></span>
                                <small class="text-muted">Año en que el responsable está asignado al área.</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2">
                        <i class="fas fa-info-circle"></i>
                        La fecha de asignación se registrará automáticamente.
                    </div>

                    <div id="alertDuplicado" class="alert alert-danger" style="display:none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error:</strong> <span id="alertDuplicadoMsg">Este responsable ya está asignado a esta área en este año.</span>
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

{{-- ========================= MODAL RE-ASIGNAR (crea nuevo registro para historial) ========================= --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background:#e65100;">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Re-asignar a Nueva Área
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEdit">
                @csrf
                {{-- DNI del responsable para crear nuevo registro --}}
                <input type="hidden" id="edit_id" name="edit_id_registro">
                <input type="hidden" id="edit_dni_hidden" name="dni_responsable">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Responsable</label>
                                <input type="text" id="edit_responsable_nombre" class="form-control" disabled>
                                <small class="text-muted">DNI: <strong id="edit_dni_responsable"></strong></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_idarea">Nueva Área <span class="text-danger">*</span></label>
                                <select name="idarea" id="edit_idarea" class="form-control" required>
                                    <option value="">-- Seleccione el nuevo área --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}">{{ strtoupper($area->nombre_area) }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-idarea"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_periodo_anio"><i class="fas fa-calendar-alt text-warning"></i> Período <span class="text-danger">*</span></label>
                                <select name="periodo_anio" id="edit_periodo_anio" class="form-control" required>
                                    @foreach($aniosDisponibles as $anio)
                                        <option value="{{ $anio }}">{{ $anio }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2">
                        <i class="fas fa-history"></i>
                        La asignación anterior <strong>no se elimina</strong> — queda en el historial del responsable.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning text-white" id="btnActualizar">
                        <i class="fas fa-exchange-alt"></i> Registrar Re-asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- ========================= MODAL TRAZABILIDAD / HISTORIAL ========================= --}}
<div class="modal fade" id="modalHistorial" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a237e; color:#fff;">
                <h5 class="modal-title">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Trazabilidad — <span id="histNombre"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="histBody">
                {{-- Contenido cargado dinámicamente --}}
                <div class="text-center py-4" id="histLoading">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 text-muted">Cargando historial...</p>
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted mr-auto" id="histSubtitulo"></small>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
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

    // ==================== ESTADO GLOBAL ====================
    let paginaActual = 1;
    let ordenActual = { columna: 'fecha', direccion: 'desc' };
    let terminoBusqueda = '';
    let areaSeleccionada = '';
    let responsableSeleccionado = '';
    let anioSeleccionado = $('#anioFiltro').val() || '';

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

        if (areaSeleccionada) {
            $('#btnLimpiarArea').fadeIn(200);
            const nombreArea = $('#areaFiltro option:selected').text().replace('📍 ', '');
            $('#areaFiltrada').text(nombreArea);
            $('#infoArea').fadeIn(200);
        } else {
            $('#btnLimpiarArea').fadeOut(200);
            $('#infoArea').fadeOut(200);
        }

        buscar(terminoBusqueda, paginaActual);
    });

    $('#btnLimpiarArea').on('click', function() {
        $('#areaFiltro').val('');
        areaSeleccionada = '';
        $('#btnLimpiarArea').fadeOut(200);
        $('#infoArea').fadeOut(200);
        paginaActual = 1;
        buscar(terminoBusqueda, paginaActual);
    });

    // ==================== FILTRO POR RESPONSABLE ====================
    $('#responsableFiltro').on('change', function() {
        responsableSeleccionado = $(this).val();
        paginaActual = 1;

        if (responsableSeleccionado) {
            $('#btnLimpiarResponsable').fadeIn(200);
            const nombreResp = $('#responsableFiltro option:selected').text().replace('👤 ', '');
            $('#responsableFiltrado').text(nombreResp);
            $('#infoResponsable').fadeIn(200);
        } else {
            $('#btnLimpiarResponsable').fadeOut(200);
            $('#infoResponsable').fadeOut(200);
        }

        buscar(terminoBusqueda, paginaActual);
    });

    $('#btnLimpiarResponsable').on('click', function() {
        $('#responsableFiltro').val('');
        responsableSeleccionado = '';
        $('#btnLimpiarResponsable').fadeOut(200);
        $('#infoResponsable').fadeOut(200);
        paginaActual = 1;
        buscar(terminoBusqueda, paginaActual);
    });

    // ==================== FILTRO POR AÑO ====================
    $('#anioFiltro').on('change', function() {
        anioSeleccionado = $(this).val();
        paginaActual = 1;
        buscar(terminoBusqueda, paginaActual);
    });

    // ==================== FUNCIÓN PRINCIPAL DE BÚSQL ====================
    function buscar(termino, page = 1) {
        mostrarCargando(true);

        $.ajax({
            url: '{{ route("responsable-area.index") }}',
            method: 'GET',
            data: {
                search: termino,
                page: page,
                orden: ordenActual.columna,
                direccion: ordenActual.direccion,
                area_filtro: areaSeleccionada,
                responsable_filtro: responsableSeleccionado,
                anio_filtro: anioSeleccionado
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
    function actualizarTabla(asignaciones) {
        const tbody = $('#tablaBody');
        tbody.empty();

        if (asignaciones.length === 0) {
            tbody.append(`
                <tr id="filaVacia">
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No hay asignaciones registradas
                    </td>
                </tr>
            `);
            $('#checkAll').prop('checked', false).prop('disabled', true);
            return;
        }

        $('#checkAll').prop('disabled', false);

        asignaciones.forEach(a => {
            const fecha = new Date(a.fecha_asignacion).toLocaleDateString('es-PE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            const nombreResp = a.responsable ?
                `${a.responsable.nombre_responsable} ${a.responsable.apellidos_responsable}`.toUpperCase() :
                'N/A';

            const cargoResp = a.responsable ? a.responsable.cargo_responsable.toUpperCase() : 'N/A';
            const nombreArea = a.area ? a.area.nombre_area.toUpperCase() : 'N/A';

            tbody.append(`
                <tr id="row-${a.id_responsable_area}" class="fade-in editable-row" data-id="${a.id_responsable_area}">
                    <td class="text-center">
                        <input type="checkbox" class="checkbox-item" value="${a.id_responsable_area}">
                    </td>
                    <td class="text-center">${a.dni_responsable}</td>
                    <td>
                        ${nombreResp}<br>
                        <small class="text-muted">${cargoResp}</small>
                    </td>
                    <td>${nombreArea}</td>
                    <td class="text-center"><span class="badge badge-primary">${a.periodo_anio || ''}</span></td>
                    <td>${fecha}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-info btn-historial"
                                data-dni="${a.dni_responsable}"
                                title="Ver trazabilidad">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        $('.checkbox-item').on('change', actualizarBotonEliminar);

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
        $('#tablaAsignaciones').hide();
        $('#paginacionContainer').hide();
        $('#noResultados').fadeIn();
    }

    function ocultarSinResultados() {
        $('#noResultados').hide();
        $('#tablaAsignaciones').show();
        $('#paginacionContainer').show();
    }

    // ==================== LIMPIAR TODO ====================
    $('#btnLimpiar, #btnMostrarTodo').on('click', function() {
        $('#searchInput').val('');
        $('#areaFiltro').val('');
        $('#responsableFiltro').val('');
        $('#anioFiltro').val('').trigger('change');
        $('#btnLimpiarArea').fadeOut(200);
        $('#btnLimpiarResponsable').fadeOut(200);
        $('#infoArea').fadeOut(200);
        $('#infoResponsable').fadeOut(200);
        terminoBusqueda = '';
        areaSeleccionada = '';
        responsableSeleccionado = '';
        anioSeleccionado = '';
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
            title: `¿Eliminar ${ids.length} asignación(es)?`,
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
                    url: `/responsable-area/${id}`,
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
                    title: `${eliminados} asignación(es) eliminada(s)`,
                    text: errores > 0 ? `${errores} no se pudieron eliminar` : ''
                });

                $('#checkAll').prop('checked', false);
                buscar(terminoBusqueda, paginaActual);
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo eliminar ninguna asignación'
                });
            }
        });
    }

    // ==================== EDITAR (DOBLE CLICK) ====================
    $(document).on('dblclick', '.editable-row', function() {
        const id = $(this).data('id');

        $.get(`/responsable-area/${id}/edit`, function(data) {
            $('#edit_id').val(data.id_responsable_area);
            $('#edit_dni_responsable').text(data.dni_responsable);

            const nombreCompleto = data.responsable ?
                `${data.responsable.nombre_responsable} ${data.responsable.apellidos_responsable}`.toUpperCase() :
                'N/A';
            $('#edit_responsable_nombre').val(nombreCompleto);
            $('#edit_idarea').val(''); // Limpiar para que elija nueva área
            $('#edit_dni_hidden').val(data.dni_responsable);
            $('#edit_periodo_anio').val(data.periodo_anio || new Date().getFullYear());
            $('#modalEdit').modal('show');
        }).fail(function(xhr) {
            Toast.fire({
                icon: 'error',
                title: 'Error al cargar datos',
                text: xhr.status === 404 ? 'Asignación no encontrada' : 'Error del servidor'
            });
        });
    });

    // ==================== CREAR ====================
    $('#formCreate').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text('');
        $('#alertDuplicado').hide();

        const btn = $('#btnGuardar');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("responsable-area.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if (res.success) {
                    $('#modalCreate').modal('hide');
                    Toast.fire({ icon: 'success', title: res.message });
                    $('#searchInput').val('');
                    $('#areaFiltro').val('');
                    $('#responsableFiltro').val('');
                    terminoBusqueda = '';
                    areaSeleccionada = '';
                    responsableSeleccionado = '';
                    buscar('', 1);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;

                    if (errors.duplicado) {
                        $('#alertDuplicado').fadeIn();
                    }

                    $.each(errors, (campo, mensajes) => {
                        $(`.error-${campo}`).text(mensajes[0]);
                    });
                } else {
                    Toast.fire({ icon: 'error', title: 'Error al guardar' });
                }
            }
        });
    });

    // ==================== RE-ASIGNAR (crea nuevo registro) ====================
    $('#formEdit').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text('');

        const btn = $('#btnActualizar');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registrando...');

        // Construir datos para crear nuevo registro (POST al store)
        const formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            dni_responsable : $('#edit_dni_hidden').val(),
            idarea          : $('#edit_idarea').val(),
            periodo_anio    : $('#edit_periodo_anio').val()
        };

        $.ajax({
            url: '{{ route("responsable-area.store") }}',
            method: 'POST',
            data: formData,
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-exchange-alt"></i> Registrar Re-asignación');

                if (res.success) {
                    $('#modalEdit').modal('hide');
                    Toast.fire({ icon: 'success', title: '¡Re-asignación registrada en el historial!' });
                    buscar(terminoBusqueda, paginaActual);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-exchange-alt"></i> Registrar Re-asignación');

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};
                    $.each(errors, (campo, mensajes) => {
                        $(`.error-edit-${campo}`).text(mensajes[0]);
                    });
                    if (xhr.responseJSON.message) {
                        Toast.fire({ icon: 'error', title: xhr.responseJSON.message });
                    }
                } else {
                    Toast.fire({ icon: 'error', title: 'Error al registrar re-asignación' });
                }
            }
        });
    });

    // ==================== HISTORIAL / TRAZABILIDAD ====================
    $(document).on('click', '.btn-historial', function(e) {
        e.stopPropagation(); // evitar que dispare el dblclick de la fila

        const dni = $(this).data('dni');

        // Resetear modal
        $('#histNombre').text('...');
        $('#histSubtitulo').text('');
        $('#histBody').html(`
            <div class="text-center py-4" id="histLoading">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2 text-muted">Cargando historial...</p>
            </div>
        `);
        $('#modalHistorial').modal('show');

        $.ajax({
            url: `/catalogos/responsable-area/historial/${dni}`,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (!res.success) {
                    $('#histBody').html('<div class="alert alert-danger">No se encontró al responsable.</div>');
                    return;
                }

                const r = res.responsable;
                const asignaciones = res.asignaciones;

                $('#histNombre').text(r.nombre);
                $('#histSubtitulo').text(`DNI: ${r.dni}  •  ${r.cargo}`);

                let filas = '';
                if (asignaciones.length === 0) {
                    filas = `<tr><td colspan="3" class="text-center text-muted py-3">
                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>Sin asignaciones registradas
                    </td></tr>`;
                } else {
                    asignaciones.forEach((a, idx) => {
                        const esActual = idx === 0;
                        filas += `
                            <tr ${esActual ? 'class="table-primary font-weight-bold"' : ''}>
                                <td class="text-center">
                                    <span class="badge badge-${esActual ? 'success' : 'secondary'}">
                                        ${a.periodo_anio}
                                    </span>
                                </td>
                                <td>${a.area}</td>
                                <td class="text-muted small">${a.fecha_asignacion}</td>
                            </tr>
                        `;
                    });
                }

                $('#histBody').html(`
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3"
                             style="width:48px;height:48px;font-size:1.3rem;flex-shrink:0;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">${r.nombre}</h6>
                            <small class="text-muted">${r.cargo} &nbsp;|&nbsp; DNI: ${r.dni}</small>
                        </div>
                        <span class="ml-auto badge badge-pill badge-info px-3 py-2">
                            ${asignaciones.length} asignación(es)
                        </span>
                    </div>
                    <table class="table table-sm table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center" width="15%">Período</th>
                                <th>Área</th>
                                <th width="25%">Fecha asignación</th>
                            </tr>
                        </thead>
                        <tbody>${filas}</tbody>
                    </table>
                    <small class="text-muted">
                        <i class="fas fa-circle text-success"></i> Fila resaltada = asignación más reciente
                    </small>
                `);
            },
            error: function(xhr) {
                $('#histBody').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error al cargar el historial. (${xhr.status})
                    </div>
                `);
            }
        });
    });

    // ==================== LIMPIAR MODALES ====================
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('.text-danger').text('');
        $('#alertDuplicado').hide();
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        $('.text-danger').text('');
    });

    $('#modalCreate').on('shown.bs.modal', () => $('#dni_responsable').focus());
    $('#modalEdit').on('shown.bs.modal', () => $('#edit_idarea').focus());

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
