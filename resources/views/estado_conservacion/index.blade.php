@extends('layouts.main')

@section('title', 'Estados de Conservación')

@section('content_header')
    <h1>Gestión de Estados de Conservación</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row mb-3">
            @if(Auth::user()->esAdmin())
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Nuevo Estado
                </button>
                <button type="button" class="btn btn-danger ml-2" id="btnEliminarSeleccionados" style="display:none;">
                    <i class="fas fa-trash-alt"></i> Eliminar (<span id="contadorSeleccionados">0</span>)
                </button>
            @endif
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
                               placeholder="Buscar por nombre..."
                               autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block text-right">
                        <span id="infoResultados">
                            Mostrando <strong id="from">{{ $estados->firstItem() ?? 0 }}</strong>
                            a <strong id="to">{{ $estados->lastItem() ?? 0 }}</strong>
                            de <strong id="resultadosCount">{{ $estados->total() }}</strong>
                            (<strong id="totalCount">{{ $total }}</strong> total)
                        </span>
                        <span id="loadingSearch" style="display:none;">
                            <i class="fas fa-spinner fa-spin text-primary"></i> Buscando...
                        </span>
                    </small>
                </div>
            </div>
        </div>

        @if(Auth::user()->esAdmin())
        <div class="text-right">
            <small class="text-muted"><i class="fas fa-info-circle"></i> Doble click en el nombre para editar</small>
        </div>
        @endif
    </div>

    <div class="card-body">
        {{-- Alerta informativa sobre el propósito de este catálogo --}}
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i>
            <strong>Catálogo de Condición Física:</strong>
            Este catálogo registra el estado <strong>físico/material</strong> del bien
            (ej: Bueno, Regular, Malo, Chatarra). Es independiente del estado administrativo
            (Activo, Baja, Prestado).
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        @if(Auth::user()->esAdmin())
                        <th width="8%">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="checkAll">
                                <label class="custom-control-label" for="checkAll"></label>
                            </div>
                        </th>
                        @endif
                        <th width="10%" class="sortable" data-column="id" style="cursor:pointer;">
                            ID <i class="fas fa-sort-down sort-icon"></i>
                        </th>
                        <th class="sortable" data-column="nombre" style="cursor:pointer;">
                            Nombre del Estado <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="15%" class="text-center">Badge</th>
                        <th width="20%" class="sortable" data-column="fecha" style="cursor:pointer;">
                            Fecha Registro <i class="fas fa-sort sort-icon"></i>
                        </th>
                    </tr>
                </thead>
                <tbody id="tablaEstados">
                    @forelse($estados as $estado)
                    <tr id="row-{{ $estado->id_estado_conservacion }}">
                        @if(Auth::user()->esAdmin())
                        <td class="text-center">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox-item"
                                       id="check-{{ $estado->id_estado_conservacion }}"
                                       value="{{ $estado->id_estado_conservacion }}">
                                <label class="custom-control-label" for="check-{{ $estado->id_estado_conservacion }}"></label>
                            </div>
                        </td>
                        @endif
                        <td class="text-center"><strong>{{ $estado->id_estado_conservacion }}</strong></td>
                        <td class="{{ Auth::user()->esAdmin() ? 'editable-cell' : '' }}"
                            data-id="{{ $estado->id_estado_conservacion }}"
                            data-nombre="{{ $estado->nombre_conservacion }}"
                            style="{{ Auth::user()->esAdmin() ? 'cursor: pointer;' : '' }}"
                            title="{{ Auth::user()->esAdmin() ? 'Doble click para editar' : '' }}">
                            {{ $estado->nombre_conservacion }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $estado->getBadgeClass() }}">
                                {{ $estado->nombre_conservacion }}
                            </span>
                        </td>
                        <td>{{ $estado->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ Auth::user()->esAdmin() ? 5 : 4 }}" class="text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No hay registros disponibles</p>
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
                    Mostrando <strong id="paginaInfo">{{ $estados->firstItem() ?? 0 }} - {{ $estados->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $estados->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks"></div>
        </div>

        <!-- Sin resultados -->
        <div id="noResultados" class="text-center py-4" style="display:none;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
            <h5>No se encontraron resultados</h5>
            <p class="text-muted">No hay estados que coincidan con "<strong id="terminoBuscado"></strong>"</p>
            <button class="btn btn-outline-primary" id="btnMostrarTodo">
                <i class="fas fa-undo"></i> Mostrar todo
            </button>
        </div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Nuevo Estado de Conservación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCreate">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre_conservacion">Nombre del Estado <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nombre_conservacion"
                               id="nombre_conservacion"
                               class="form-control"
                               maxlength="50"
                               required
                               placeholder="Ej: Bueno, Regular, Malo, Chatarra..."
                               autocomplete="off">
                        <small class="form-text text-muted">Máximo 50 caracteres. Debe ser único.</small>
                        <span class="text-danger error-nombre_conservacion d-block mt-1"></span>
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

<!-- Modal Editar -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Estado de Conservación
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
                        <label for="edit_nombre_conservacion">Nombre del Estado <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nombre_conservacion"
                               id="edit_nombre_conservacion"
                               class="form-control"
                               maxlength="50"
                               required
                               autocomplete="off">
                        <span class="text-danger error-edit-nombre_conservacion d-block mt-1"></span>
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
const esAdmin = {{ Auth::user()->esAdmin() ? 'true' : 'false' }};
</script>
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let paginaActual = 1;
    let searchTimeout;
    let ordenActual = { columna: 'id', direccion: 'desc' };

    actualizarIconosOrdenamiento();

    actualizarPaginacion({
        current_page: {{ $estados->currentPage() }},
        last_page: {{ $estados->lastPage() }}
    }, '');

    // ==================== ORDENAMIENTO ====================
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
        $('.sortable .sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        if (ordenActual.columna) {
            $(`.sortable[data-column="${ordenActual.columna}"] .sort-icon`)
                .removeClass('fa-sort')
                .addClass(ordenActual.direccion === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        }
    }

    // ==================== BÚSQUEDA ====================
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        paginaActual = 1;
        searchTimeout = setTimeout(() => buscar($(this).val().trim(), paginaActual), 400);
    });

    function buscar(termino, page = 1) {
        $('#loadingSearch').show();
        $('#infoResultados').hide();

        $.ajax({
            url: '{{ route("catalogos.estado-conservacion.index") }}',
            method: 'GET',
            data: { search: termino, page: page, orden: ordenActual.columna, direccion: ordenActual.direccion },
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

    function getBadgeClass(nombre) {
        const n = nombre.toLowerCase().trim();
        if (n.includes('bueno'))    return 'badge badge-success';
        if (n.includes('regular'))  return 'badge badge-warning';
        if (n.includes('malo'))     return 'badge badge-danger';
        if (n.includes('chatarra')) return 'badge badge-dark';
        return 'badge badge-secondary';
    }

    function actualizarTabla(estados) {
        const tbody = $('#tablaEstados');
        tbody.empty();
        if (estados.length === 0) return;

        estados.forEach(e => {
            const fecha = new Date(e.created_at).toLocaleDateString('es-PE', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });

            const checkboxCol = esAdmin
                ? `<td class="text-center">
                       <div class="custom-control custom-checkbox">
                           <input type="checkbox" class="custom-control-input checkbox-item"
                                  id="check-${e.id_estado_conservacion}" value="${e.id_estado_conservacion}">
                           <label class="custom-control-label" for="check-${e.id_estado_conservacion}"></label>
                       </div>
                   </td>`
                : '';

            const nombreCell = esAdmin
                ? `<td class="editable-cell" data-id="${e.id_estado_conservacion}"
                       data-nombre="${e.nombre_conservacion}" style="cursor:pointer"
                       title="Doble click para editar">${e.nombre_conservacion}</td>`
                : `<td>${e.nombre_conservacion}</td>`;

            tbody.append(`
                <tr id="row-${e.id_estado_conservacion}" class="fade-in">
                    ${checkboxCol}
                    <td class="text-center"><strong>${e.id_estado_conservacion}</strong></td>
                    ${nombreCell}
                    <td class="text-center">
                        <span class="${getBadgeClass(e.nombre_conservacion)}">${e.nombre_conservacion}</span>
                    </td>
                    <td>${fecha}</td>
                </tr>
            `);
        });

        if (esAdmin) {
            $('.checkbox-item').on('change', actualizarBotonEliminar);
            $('#checkAll').prop('checked', false);
        }
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

        html += res.current_page > 1
            ? `<li class="page-item"><a class="page-link paginar" href="#" data-page="${res.current_page - 1}"><i class="fas fa-chevron-left"></i></a></li>`
            : `<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>`;

        for (let i = 1; i <= res.last_page; i++) {
            if (i == res.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (i == 1 || i == res.last_page || Math.abs(i - res.current_page) <= 2) {
                html += `<li class="page-item"><a class="page-link paginar" href="#" data-page="${i}">${i}</a></li>`;
            } else if (i == res.current_page - 3 || i == res.current_page + 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        html += res.current_page < res.last_page
            ? `<li class="page-item"><a class="page-link paginar" href="#" data-page="${res.current_page + 1}"><i class="fas fa-chevron-right"></i></a></li>`
            : `<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>`;

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
        paginaActual = 1;
        ordenActual = { columna: 'id', direccion: 'desc' };
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
        if (!$(this).is(':checked')) $('#checkAll').prop('checked', false);
        if ($('.checkbox-item:checked').length === $('.checkbox-item').length && $('.checkbox-item').length > 0) {
            $('#checkAll').prop('checked', true);
        }
    });

    function actualizarBotonEliminar() {
        let seleccionados = $('.checkbox-item:checked').length;
        $('#contadorSeleccionados').text(seleccionados);
        seleccionados > 0 ? $('#btnEliminarSeleccionados').fadeIn() : $('#btnEliminarSeleccionados').fadeOut();
    }

    // ==================== ELIMINAR MÚLTIPLES ====================
    $('#btnEliminarSeleccionados').on('click', function() {
        let seleccionados = [];
        $('.checkbox-item:checked').each(function() { seleccionados.push($(this).val()); });
        if (seleccionados.length === 0) return Swal.fire('Aviso', 'No hay registros seleccionados', 'info');

        Swal.fire({
            title: '¿Eliminar ' + seleccionados.length + ' registro(s)?',
            text: 'Esta acción no se puede revertir',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            let eliminados = 0, errores = 0;
            let promesas = seleccionados.map(id =>
                $.ajax({
                    url: '/catalogos/estado-conservacion/' + id,
                    method: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' }
                }).then(() => eliminados++).catch(() => errores++)
            );

            Promise.allSettled(promesas).then(() => {
                if (errores === 0) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: eliminados + ' registro(s) eliminado(s)', timer: 1500, showConfirmButton: false })
                        .then(() => buscar($('#searchInput').val(), paginaActual));
                } else {
                    Swal.fire({ icon: 'warning', title: 'Completado con errores',
                        html: `<p>Eliminados: <b>${eliminados}</b></p><p>Errores: <b>${errores}</b></p>` })
                        .then(() => buscar($('#searchInput').val(), paginaActual));
                }
            });
        });
    });

    // ==================== DOBLE CLICK EDITAR ====================
    if (esAdmin) {
        $(document).on('dblclick', '.editable-cell', function() {
            let id = $(this).data('id');
            let nombre = $(this).data('nombre');
            $('.error-edit-nombre_conservacion').text('');
            $('#edit_id').val(id);
            $('#edit_nombre_conservacion').val(nombre);
            $('#modalEdit').modal('show');
            $('#modalEdit').on('shown.bs.modal', function() {
                $('#edit_nombre_conservacion').focus().select();
            });
        });
    }

    // ==================== CREAR ====================
    $('#formCreate').on('submit', function(e) {
        e.preventDefault();
        $('.error-nombre_conservacion').text('');
        let btn = $('#btnGuardar');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("catalogos.estado-conservacion.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                if (res.success) {
                    $('#modalCreate').modal('hide');
                    $('#formCreate')[0].reset();
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: res.message, timer: 1500, showConfirmButton: false })
                        .then(() => buscar('', 1));
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    if (errors.nombre_conservacion) $('.error-nombre_conservacion').text(errors.nombre_conservacion[0]);
                } else {
                    Swal.fire('Error', 'No se pudo guardar el registro', 'error');
                }
            }
        });
    });

    // ==================== ACTUALIZAR ====================
    $('#formEdit').on('submit', function(e) {
        e.preventDefault();
        $('.error-edit-nombre_conservacion').text('');
        let btn = $('#btnActualizar');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
        let id = $('#edit_id').val();

        $.ajax({
            url: '/catalogos/estado-conservacion/' + id,
            method: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');
                if (res.success) {
                    $('#modalEdit').modal('hide');
                    Swal.fire({ icon: 'success', title: '¡Actualizado!', text: res.message, timer: 1500, showConfirmButton: false })
                        .then(() => buscar($('#searchInput').val(), paginaActual));
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    if (errors.nombre_conservacion) $('.error-edit-nombre_conservacion').text(errors.nombre_conservacion[0]);
                } else {
                    Swal.fire('Error', 'No se pudo actualizar el registro', 'error');
                }
            }
        });
    });

    // Limpiar modales al cerrar
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('.error-nombre_conservacion').text('');
    });
    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        $('.error-edit-nombre_conservacion').text('');
    });
    $('#modalCreate').on('shown.bs.modal', function() {
        $('#nombre_conservacion').focus();
    });
});
</script>
@stop
