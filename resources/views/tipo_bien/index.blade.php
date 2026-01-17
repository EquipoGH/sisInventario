@extends('layouts.main')

@section('title', 'Tipos de Bien')

@section('content_header')
    <h1>Gestión de Tipos de Bien</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row mb-3">
            <div class="col-md-4">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Nuevo Tipo de Bien
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
                            Mostrando <strong id="from">{{ $tipoBienes->firstItem() ?? 0 }}</strong>
                            a <strong id="to">{{ $tipoBienes->lastItem() ?? 0 }}</strong>
                            de <strong id="resultadosCount">{{ $tipoBienes->total() }}</strong>
                            (<strong id="totalCount">{{ $total }}</strong> total)
                        </span>
                        <span id="loadingSearch" style="display:none;">
                            <i class="fas fa-spinner fa-spin text-primary"></i> Buscando...
                        </span>
                    </small>
                </div>
            </div>
        </div>

        <div class="text-right">
            <small class="text-muted"><i class="fas fa-info-circle"></i> Doble click en el nombre para editar</small>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th width="8%">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="checkAll">
                                <label class="custom-control-label" for="checkAll"></label>
                            </div>
                        </th>
                        <th width="12%" class="sortable" data-column="id" style="cursor:pointer;">
                            ID <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="50%" class="sortable" data-column="nombre" style="cursor:pointer;">
                            Nombre Tipo <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="30%" class="sortable" data-column="fecha" style="cursor:pointer;">
                            Fecha Registro <i class="fas fa-sort sort-icon"></i>
                        </th>
                    </tr>
                </thead>
                <tbody id="tablaTipoBien">
                    @forelse($tipoBienes as $tipo)
                    <tr id="row-{{ $tipo->id_tipo_bien }}">
                        <td class="text-center">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox-item"
                                       id="check-{{ $tipo->id_tipo_bien }}"
                                       value="{{ $tipo->id_tipo_bien }}">
                                <label class="custom-control-label" for="check-{{ $tipo->id_tipo_bien }}"></label>
                            </div>
                        </td>
                        <td>{{ $tipo->id_tipo_bien }}</td>
                        <td class="editable-cell"
                            data-id="{{ $tipo->id_tipo_bien }}"
                            data-nombre="{{ $tipo->nombre_tipo }}"
                            style="cursor: pointer;"
                            title="Doble click para editar">
                            <strong>{{ $tipo->nombre_tipo }}</strong>
                        </td>
                        <td>{{ $tipo->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">
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
                    Mostrando <strong id="paginaInfo">{{ $tipoBienes->firstItem() ?? 0 }} - {{ $tipoBienes->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $tipoBienes->total() }}</strong>
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
            <p class="text-muted">No hay tipos de bien que coincidan con "<strong id="terminoBuscado"></strong>"</p>
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
                    <i class="fas fa-plus-circle"></i> Nuevo Tipo de Bien
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCreate">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre_tipo">Nombre del Tipo <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nombre_tipo"
                               id="nombre_tipo"
                               class="form-control"
                               maxlength="20"
                               required
                               placeholder="Ej: Inmueble, Vehículo, Mueble..."
                               autocomplete="off">
                        <small class="form-text text-muted">Máximo 20 caracteres</small>
                        <span class="text-danger error-nombre_tipo d-block mt-1"></span>
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
                    <i class="fas fa-edit"></i> Editar Tipo de Bien
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
                        <label for="edit_nombre_tipo">Nombre del Tipo <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nombre_tipo"
                               id="edit_nombre_tipo"
                               class="form-control"
                               maxlength="20"
                               required
                               autocomplete="off">
                        <span class="text-danger error-edit-nombre_tipo d-block mt-1"></span>
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
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let paginaActual = 1;
    let searchTimeout;
    let ordenActual = { columna: 'id', direccion: 'desc' }; // 🔥 ORDEN POR DEFECTO

    // 🔥 ESTABLECER ICONO INICIAL
    actualizarIconosOrdenamiento();

    // ===============================
    // 🔥 ORDENAMIENTO AL HACER CLICK EN COLUMNAS
    // ===============================
    $('.sortable').on('click', function() {
        const columna = $(this).data('column');

        // Toggle dirección si es la misma columna
        if (ordenActual.columna === columna) {
            ordenActual.direccion = ordenActual.direccion === 'asc' ? 'desc' : 'asc';
        } else {
            ordenActual.columna = columna;
            // Por defecto: ID y Fecha DESC, Nombre ASC
            ordenActual.direccion = (columna === 'fecha' || columna === 'id') ? 'desc' : 'asc';
        }

        actualizarIconosOrdenamiento();
        paginaActual = 1;
        buscar($('#searchInput').val().trim(), paginaActual);
    });

    function actualizarIconosOrdenamiento() {
        // Resetear todos los iconos
        $('.sortable .sort-icon')
            .removeClass('fa-sort-up fa-sort-down')
            .addClass('fa-sort');

        // Aplicar icono activo
        if (ordenActual.columna) {
            const iconoActivo = $(`.sortable[data-column="${ordenActual.columna}"] .sort-icon`);
            iconoActivo
                .removeClass('fa-sort')
                .addClass(ordenActual.direccion === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        }
    }

    // ===============================
    // BÚSQUEDA EN VIVO
    // ===============================
    $('#searchInput').on('keyup', function() {
        const termino = $(this).val().trim();
        clearTimeout(searchTimeout);
        paginaActual = 1;
        searchTimeout = setTimeout(() => buscar(termino, paginaActual), 400);
    });

    function buscar(termino, page = 1) {
        $('#loadingSearch').show();
        $('#infoResultados').hide();

        $.ajax({
            url: '{{ route("tipo-bien.index") }}',
            method: 'GET',
            data: {
                search: termino,
                page: page,
                orden: ordenActual.columna,      // 🔥 ENVIAR ORDEN
                direccion: ordenActual.direccion // 🔥 ENVIAR DIRECCIÓN
            },
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

    function actualizarTabla(tipos) {
        const tbody = $('#tablaTipoBien');
        tbody.empty();

        if (tipos.length === 0) return;

        tipos.forEach(t => {
            const fecha = new Date(t.created_at).toLocaleDateString('es-PE', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });

            tbody.append(`
                <tr id="row-${t.id_tipo_bien}" class="fade-in">
                    <td class="text-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input checkbox-item"
                                   id="check-${t.id_tipo_bien}"
                                   value="${t.id_tipo_bien}">
                            <label class="custom-control-label" for="check-${t.id_tipo_bien}"></label>
                        </div>
                    </td>
                    <td>${t.id_tipo_bien}</td>
                    <td class="editable-cell" data-id="${t.id_tipo_bien}" data-nombre="${t.nombre_tipo}"
                        style="cursor:pointer" title="Doble click para editar">
                        <strong>${t.nombre_tipo.toUpperCase()}</strong>
                    </td>
                    <td>${fecha}</td>
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
        } else {
            html += `<li class="page-item disabled">
                        <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                     </li>`;
        }

        for (let i = 1; i <= res.last_page; i++) {
            if (i == res.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (i == 1 || i == res.last_page || Math.abs(i - res.current_page) <= 2) {
                html += `<li class="page-item">
                            <a class="page-link paginar" href="#" data-page="${i}">${i}</a>
                         </li>`;
            } else if (i == res.current_page - 3 || i == res.current_page + 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        if (res.current_page < res.last_page) {
            html += `<li class="page-item">
                        <a class="page-link paginar" href="#" data-page="${res.current_page + 1}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                     </li>`;
        } else {
            html += `<li class="page-item disabled">
                        <span class="page-link"><i class="fas fa-chevron-right"></i></span>
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
        paginaActual = 1;
        ordenActual = { columna: 'id', direccion: 'desc' }; // 🔥 RESETEAR ORDEN
        actualizarIconosOrdenamiento();
        buscar('', 1);
    });

    // ===============================
    // CHECKBOX: Seleccionar todos
    // ===============================
    $('#checkAll').on('change', function() {
        let isChecked = $(this).is(':checked');
        $('.checkbox-item').prop('checked', isChecked);
        actualizarBotonEliminar();
    });

    $(document).on('change', '.checkbox-item', function() {
        actualizarBotonEliminar();

        if (!$(this).is(':checked')) {
            $('#checkAll').prop('checked', false);
        }

        if ($('.checkbox-item:checked').length === $('.checkbox-item').length) {
            $('#checkAll').prop('checked', true);
        }
    });

    function actualizarBotonEliminar() {
        let seleccionados = $('.checkbox-item:checked').length;
        $('#contadorSeleccionados').text(seleccionados);

        if (seleccionados > 0) {
            $('#btnEliminarSeleccionados').fadeIn();
        } else {
            $('#btnEliminarSeleccionados').fadeOut();
        }
    }

    // ===============================
    // ELIMINAR SELECCIONADOS
    // ===============================
    $('#btnEliminarSeleccionados').on('click', function() {
        let seleccionados = [];
        $('.checkbox-item:checked').each(function() {
            seleccionados.push($(this).val());
        });

        if (seleccionados.length === 0) {
            Swal.fire('Aviso', 'No hay registros seleccionados', 'info');
            return;
        }

        Swal.fire({
            title: '¿Eliminar ' + seleccionados.length + ' registro(s)?',
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
            didOpen: () => {
                Swal.showLoading();
            }
        });

        let eliminados = 0;
        let errores = 0;

        let promesas = ids.map(id => {
            return $.ajax({
                url: '/tipo-bien/' + id,
                method: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                }
            }).then(() => {
                eliminados++;
                Swal.update({
                    html: 'Eliminando <b>' + eliminados + '</b> de <b>' + ids.length + '</b> registros'
                });
            }).catch(() => {
                errores++;
            });
        });

        Promise.allSettled(promesas).then(() => {
            if (errores === 0) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: eliminados + ' registro(s) eliminado(s) correctamente',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => buscar($('#searchInput').val(), paginaActual));
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Completado con errores',
                    html: '<p>Eliminados: <b>' + eliminados + '</b></p><p>Errores: <b>' + errores + '</b></p>',
                    confirmButtonText: 'Aceptar'
                }).then(() => buscar($('#searchInput').val(), paginaActual));
            }
        });
    }

    // ===============================
    // DOBLE CLICK PARA EDITAR
    // ===============================
    $(document).on('dblclick', '.editable-cell', function() {
        let id = $(this).data('id');
        let nombre = $(this).data('nombre');

        $('.error-edit-nombre_tipo').text('');
        $('#edit_id').val(id);
        $('#edit_nombre_tipo').val(nombre);
        $('#modalEdit').modal('show');

        $('#modalEdit').on('shown.bs.modal', function() {
            $('#edit_nombre_tipo').focus().select();
        });
    });

    // ===============================
    // CREAR
    // ===============================
    $('#formCreate').on('submit', function(e) {
        e.preventDefault();

        $('.error-nombre_tipo').text('');
        let btnGuardar = $('#btnGuardar');
        btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("tipo-bien.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if(response.success) {
                    $('#modalCreate').modal('hide');
                    $('#formCreate')[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => buscar('', 1));
                }
            },
            error: function(xhr) {
                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    if(errors.nombre_tipo) {
                        $('.error-nombre_tipo').text(errors.nombre_tipo[0]);
                    }
                } else {
                    Swal.fire('Error', 'No se pudo guardar el registro', 'error');
                }
            }
        });
    });

    // ===============================
    // ACTUALIZAR
    // ===============================
    $('#formEdit').on('submit', function(e) {
        e.preventDefault();

        $('.error-edit-nombre_tipo').text('');
        let btnActualizar = $('#btnActualizar');
        btnActualizar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        let id = $('#edit_id').val();

        $.ajax({
            url: '/tipo-bien/' + id,
            method: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            dataType: 'json',
            success: function(response) {
                btnActualizar.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');

                if(response.success) {
                    $('#modalEdit').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => buscar($('#searchInput').val(), paginaActual));
                }
            },
            error: function(xhr) {
                btnActualizar.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');

                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    if(errors.nombre_tipo) {
                        $('.error-edit-nombre_tipo').text(errors.nombre_tipo[0]);
                    }
                } else {
                    Swal.fire('Error', 'No se pudo actualizar el registro', 'error');
                }
            }
        });
    });

    // Limpiar formularios al cerrar
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('.error-nombre_tipo').text('');
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        $('.error-edit-nombre_tipo').text('');
    });

    // Focus automático al abrir modal de crear
    $('#modalCreate').on('shown.bs.modal', function() {
        $('#nombre_tipo').focus();
    });
});
</script>
@stop
