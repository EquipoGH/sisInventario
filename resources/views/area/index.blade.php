@extends('layouts.main')

@section('title', 'Gestión de Áreas')

@section('content_header')
    <h1><i class="fas fa-building"></i> Gestión de Áreas</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        {{-- BARRA DE ACCIONES --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Nueva Área
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
                               placeholder="Buscar por nombre o ID..."
                               autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block text-right">
                        <span id="infoResultados">
                            Mostrando <strong id="from">{{ $areas->firstItem() ?? 0 }}</strong>
                            a <strong id="to">{{ $areas->lastItem() ?? 0 }}</strong>
                            de <strong id="resultadosCount">{{ $areas->total() }}</strong>
                            (<strong id="totalCount">{{ $total }}</strong> total)
                        </span>
                        <span id="loadingSearch" style="display:none;">
                            <i class="fas fa-spinner fa-spin text-primary"></i> Buscando...
                        </span>
                    </small>
                </div>
            </div>
        </div>

        {{-- INFO: DOBLE CLICK --}}
        <div class="mb-2 text-right">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> Doble click en el nombre para editar
            </small>
        </div>

        {{-- TABLA --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tablaAreas">
                <thead class="thead-dark">
                    <tr>
                        <th width="5%" class="text-center">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th width="10%" class="text-center sortable" data-column="id">
                            ID <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="65%" class="sortable" data-column="nombre">
                            Nombre del Área <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="20%" class="sortable" data-column="fecha">
                            Fecha Registro <i class="fas fa-sort sort-icon"></i>
                        </th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    @forelse($areas as $area)
                    <tr id="row-{{ $area->id_area }}">
                        <td class="text-center">
                            <input type="checkbox" class="checkbox-item" value="{{ $area->id_area }}">
                        </td>
                        <td class="text-center"><strong>{{ $area->id_area }}</strong></td>
                        <td class="editable-cell"
                            data-id="{{ $area->id_area }}"
                            title="Doble click para editar">
                            <strong>{{ strtoupper($area->nombre_area) }}</strong>
                        </td>
                        <td>{{ $area->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr id="filaVacia">
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No hay áreas registradas
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
                    Mostrando <strong id="paginaInfo">{{ $areas->firstItem() ?? 0 }} - {{ $areas->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $areas->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks"></div>
        </div>

        {{-- SIN RESULTADOS --}}
        <div id="noResultados" class="text-center py-4" style="display:none;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
            <h5>No se encontraron resultados</h5>
            <p class="text-muted">No hay áreas que coincidan con "<strong id="terminoBuscado"></strong>"</p>
            <button class="btn btn-outline-primary" id="btnMostrarTodo">
                <i class="fas fa-undo"></i> Mostrar todo
            </button>
        </div>
    </div>
</div>

{{-- ========================= MODAL CREAR ========================= --}}
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Nueva Área
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCreate">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre_area">Nombre del Área <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nombre_area"
                               id="nombre_area"
                               class="form-control"
                               placeholder="Ej: Sistemas, Contabilidad"
                               maxlength="100"
                               required>
                        <span class="text-danger error-nombre_area"></span>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Área
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
                    <div class="form-group">
                        <label for="edit_nombre_area">Nombre del Área <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nombre_area"
                               id="edit_nombre_area"
                               class="form-control"
                               maxlength="100"
                               required>
                        <span class="text-danger error-edit-nombre_area"></span>
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

    // ==================== ESTADO GLOBAL ====================
    let paginaActual = 1;
    let ordenActual = { columna: 'fecha', direccion: 'desc' };
    let terminoBusqueda = '';

    // Establecer icono inicial de ordenamiento
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

    // ==================== FUNCIÓN PRINCIPAL DE BÚSQUEDA ====================
    function buscar(termino, page = 1) {
        mostrarCargando(true);

        $.ajax({
            url: '{{ route("area.index") }}',
            method: 'GET',
            data: {
                search: termino,
                page: page,
                orden: ordenActual.columna,
                direccion: ordenActual.direccion
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
    function actualizarTabla(areas) {
        const tbody = $('#tablaBody');
        tbody.empty();

        if (areas.length === 0) {
            tbody.append(`
                <tr id="filaVacia">
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No hay áreas registradas
                    </td>
                </tr>
            `);
            $('#checkAll').prop('checked', false).prop('disabled', true);
            return;
        }

        $('#checkAll').prop('disabled', false);

        areas.forEach(a => {
            const fecha = new Date(a.created_at).toLocaleDateString('es-PE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            tbody.append(`
                <tr id="row-${a.id_area}" class="fade-in">
                    <td class="text-center">
                        <input type="checkbox" class="checkbox-item" value="${a.id_area}">
                    </td>
                    <td class="text-center"><strong>${a.id_area}</strong></td>
                    <td class="editable-cell" data-id="${a.id_area}" title="Doble click para editar">
                        <strong>${a.nombre_area.toUpperCase()}</strong>
                    </td>
                    <td>${fecha}</td>
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

        // Botón Anterior
        html += generarBotonPaginacion(
            res.current_page > 1,
            res.current_page - 1,
            '<i class="fas fa-chevron-left"></i>'
        );

        // Números de página
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

        // Botón Siguiente
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
        $('#tablaAreas').hide();
        $('#paginacionContainer').hide();
        $('#terminoBuscado').text(termino);
        $('#noResultados').fadeIn();
    }

    function ocultarSinResultados() {
        $('#noResultados').hide();
        $('#tablaAreas').show();
        $('#paginacionContainer').show();
    }

    // ==================== LIMPIAR BÚSQUEDA ====================
    $('#btnLimpiar, #btnMostrarTodo').on('click', function() {
        $('#searchInput').val('');
        terminoBusqueda = '';
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
            title: `¿Eliminar ${ids.length} área(s)?`,
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
                    url: `/area/${id}`,
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
                    title: `${eliminados} área(s) eliminada(s)`,
                    text: errores > 0 ? `${errores} no se pudieron eliminar` : ''
                });

                $('#checkAll').prop('checked', false);
                buscar(terminoBusqueda, paginaActual);
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo eliminar ninguna área'
                });
            }
        });
    }

    // ==================== EDITAR (DOBLE CLICK) ====================
    $(document).on('dblclick', '.editable-cell', function() {
        const id = $(this).data('id');

        $.get(`/area/${id}/edit`, function(data) {
            $('#edit_id').val(data.id_area);
            $('#edit_nombre_area').val(data.nombre_area);
            $('#modalEdit').modal('show');
        }).fail(function(xhr) {
            Toast.fire({
                icon: 'error',
                title: 'Error al cargar datos',
                text: xhr.status === 404 ? 'Área no encontrada' : 'Error del servidor'
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
            url: '{{ route("area.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if (res.success) {
                    $('#modalCreate').modal('hide');
                    Toast.fire({ icon: 'success', title: res.message });
                    $('#searchInput').val('');
                    terminoBusqueda = '';
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
            url: `/area/${id}`,
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

    // ==================== LIMPIAR MODALES ====================
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('.text-danger').text('');
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        $('.text-danger').text('');
    });

    $('#modalCreate').on('shown.bs.modal', () => $('#nombre_area').focus());
    $('#modalEdit').on('shown.bs.modal', () => $('#edit_nombre_area').focus().select());

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
