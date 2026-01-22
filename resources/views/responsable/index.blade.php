@extends('layouts.main')

@section('title', 'Gestión de Responsables')

@section('content_header')
    <h1><i class="fas fa-users"></i> Gestión de Responsables</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        {{-- BARRA DE ACCIONES --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Nuevo Responsable
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
                               placeholder="Buscar por DNI, nombre, apellidos o cargo..."
                               autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block text-right">
                        <span id="infoResultados">
                            Mostrando <strong id="from">{{ $responsables->firstItem() ?? 0 }}</strong>
                            a <strong id="to">{{ $responsables->lastItem() ?? 0 }}</strong>
                            de <strong id="resultadosCount">{{ $responsables->total() }}</strong>
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
                <i class="fas fa-info-circle"></i> Doble click en una fila para editar
            </small>
        </div>

        {{-- TABLA --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tablaResponsables">
                <thead class="thead-dark">
                    <tr>
                        <th width="5%" class="text-center">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th width="10%" class="text-center sortable" data-column="dni">
                            DNI <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="25%" class="sortable" data-column="nombre">
                            Nombre <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="25%" class="sortable" data-column="apellidos">
                            Apellidos <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="20%" class="sortable" data-column="cargo">
                            Cargo <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="15%" class="sortable" data-column="fecha">
                            Fecha Registro <i class="fas fa-sort sort-icon"></i>
                        </th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    @forelse($responsables as $responsable)
                    <tr id="row-{{ $responsable->dni_responsable }}" class="editable-row" data-dni="{{ $responsable->dni_responsable }}">
                        <td class="text-center">
                            <input type="checkbox" class="checkbox-item" value="{{ $responsable->dni_responsable }}">
                        </td>
                        <td class="text-center"><strong>{{ $responsable->dni_responsable }}</strong></td>
                        <td><strong>{{ strtoupper($responsable->nombre_responsable) }}</strong></td>
                        <td><strong>{{ strtoupper($responsable->apellidos_responsable) }}</strong></td>
                        <td>{{ strtoupper($responsable->cargo_responsable) }}</td>
                        <td>{{ $responsable->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr id="filaVacia">
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No hay responsables registrados
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
                    Mostrando <strong id="paginaInfo">{{ $responsables->firstItem() ?? 0 }} - {{ $responsables->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $responsables->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks"></div>
        </div>

        {{-- SIN RESULTADOS --}}
        <div id="noResultados" class="text-center py-4" style="display:none;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
            <h5>No se encontraron resultados</h5>
            <p class="text-muted">No hay responsables que coincidan con "<strong id="terminoBuscado"></strong>"</p>
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
                    <i class="fas fa-user-plus"></i> Nuevo Responsable
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCreate">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="dni_responsable">DNI <span class="text-danger">*</span></label>
                        <input type="text"
                               name="dni_responsable"
                               id="dni_responsable"
                               class="form-control"
                               placeholder="Ej: 12345678"
                               maxlength="8"
                               pattern="[0-9]{8}"
                               required>
                        <span class="text-danger error-dni_responsable"></span>
                    </div>

                    <div class="form-group">
                        <label for="nombre_responsable">Nombre <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nombre_responsable"
                               id="nombre_responsable"
                               class="form-control"
                               placeholder="Ej: Juan Carlos"
                               maxlength="20"
                               required>
                        <span class="text-danger error-nombre_responsable"></span>
                    </div>

                    <div class="form-group">
                        <label for="apellidos_responsable">Apellidos <span class="text-danger">*</span></label>
                        <input type="text"
                               name="apellidos_responsable"
                               id="apellidos_responsable"
                               class="form-control"
                               placeholder="Ej: García López"
                               maxlength="20"
                               required>
                        <span class="text-danger error-apellidos_responsable"></span>
                    </div>

                    <div class="form-group">
                        <label for="cargo_responsable">Cargo <span class="text-danger">*</span></label>
                        <input type="text"
                               name="cargo_responsable"
                               id="cargo_responsable"
                               class="form-control"
                               placeholder="Ej: Jefe de Área"
                               maxlength="20"
                               required>
                        <span class="text-danger error-cargo_responsable"></span>
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
                    <i class="fas fa-user-edit"></i> Editar Responsable
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEdit">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_dni_original">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_dni_responsable">DNI <span class="text-danger">*</span></label>
                        <input type="text"
                               name="dni_responsable"
                               id="edit_dni_responsable"
                               class="form-control"
                               maxlength="8"
                               pattern="[0-9]{8}"
                               required>
                        <span class="text-danger error-edit-dni_responsable"></span>
                    </div>

                    <div class="form-group">
                        <label for="edit_nombre_responsable">Nombre <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nombre_responsable"
                               id="edit_nombre_responsable"
                               class="form-control"
                               maxlength="20"
                               required>
                        <span class="text-danger error-edit-nombre_responsable"></span>
                    </div>

                    <div class="form-group">
                        <label for="edit_apellidos_responsable">Apellidos <span class="text-danger">*</span></label>
                        <input type="text"
                               name="apellidos_responsable"
                               id="edit_apellidos_responsable"
                               class="form-control"
                               maxlength="20"
                               required>
                        <span class="text-danger error-edit-apellidos_responsable"></span>
                    </div>

                    <div class="form-group">
                        <label for="edit_cargo_responsable">Cargo <span class="text-danger">*</span></label>
                        <input type="text"
                               name="cargo_responsable"
                               id="edit_cargo_responsable"
                               class="form-control"
                               maxlength="20"
                               required>
                        <span class="text-danger error-edit-cargo_responsable"></span>
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
            url: '{{ route("responsable.index") }}',
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
    function actualizarTabla(responsables) {
        const tbody = $('#tablaBody');
        tbody.empty();

        if (responsables.length === 0) {
            tbody.append(`
                <tr id="filaVacia">
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No hay responsables registrados
                    </td>
                </tr>
            `);
            $('#checkAll').prop('checked', false).prop('disabled', true);
            return;
        }

        $('#checkAll').prop('disabled', false);

        responsables.forEach(r => {
            const fecha = new Date(r.created_at).toLocaleDateString('es-PE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            tbody.append(`
                <tr id="row-${r.dni_responsable}" class="fade-in editable-row" data-dni="${r.dni_responsable}">
                    <td class="text-center">
                        <input type="checkbox" class="checkbox-item" value="${r.dni_responsable}">
                    </td>
                    <td class="text-center"><strong>${r.dni_responsable}</strong></td>
                    <td><strong>${r.nombre_responsable.toUpperCase()}</strong></td>
                    <td><strong>${r.apellidos_responsable.toUpperCase()}</strong></td>
                    <td>${r.cargo_responsable.toUpperCase()}</td>
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
        $('#tablaResponsables').hide();
        $('#paginacionContainer').hide();
        $('#terminoBuscado').text(termino);
        $('#noResultados').fadeIn();
    }

    function ocultarSinResultados() {
        $('#noResultados').hide();
        $('#tablaResponsables').show();
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
        const dnis = $('.checkbox-item:checked').map(function() {
            return $(this).val();
        }).get();

        Swal.fire({
            title: `¿Eliminar ${dnis.length} responsable(s)?`,
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarMultiples(dnis);
            }
        });
    });

    function eliminarMultiples(dnis) {
        let eliminados = 0;
        let errores = 0;

        Swal.fire({
            title: 'Eliminando...',
            html: `Procesando <b>${eliminados}</b> de <b>${dnis.length}</b>`,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        Promise.allSettled(
            dnis.map(dni =>
                $.ajax({
                    url: `/responsable/${dni}`,
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
                    title: `${eliminados} responsable(s) eliminado(s)`,
                    text: errores > 0 ? `${errores} no se pudieron eliminar` : ''
                });

                $('#checkAll').prop('checked', false);
                buscar(terminoBusqueda, paginaActual);
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'No se pudo eliminar ningún responsable'
                });
            }
        });
    }

    // ==================== EDITAR (DOBLE CLICK) ====================
    $(document).on('dblclick', '.editable-row', function() {
        const dni = $(this).data('dni');

        $.get(`/responsable/${dni}/edit`, function(data) {
            $('#edit_dni_original').val(data.dni_responsable);
            $('#edit_dni_responsable').val(data.dni_responsable);
            $('#edit_nombre_responsable').val(data.nombre_responsable);
            $('#edit_apellidos_responsable').val(data.apellidos_responsable);
            $('#edit_cargo_responsable').val(data.cargo_responsable);
            $('#modalEdit').modal('show');
        }).fail(function(xhr) {
            Toast.fire({
                icon: 'error',
                title: 'Error al cargar datos',
                text: xhr.status === 404 ? 'Responsable no encontrado' : 'Error del servidor'
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
            url: '{{ route("responsable.store") }}',
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
        const dni = $('#edit_dni_original').val();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        $.ajax({
            url: `/responsable/${dni}`,
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

    // ==================== VALIDACIÓN DNI EN TIEMPO REAL ====================
    $('#dni_responsable, #edit_dni_responsable').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 8);
    });

    // ==================== VALIDACIÓN SOLO LETRAS ====================
    $('#nombre_responsable, #apellidos_responsable, #edit_nombre_responsable, #edit_apellidos_responsable').on('input', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
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

    $('#modalCreate').on('shown.bs.modal', () => $('#dni_responsable').focus());
    $('#modalEdit').on('shown.bs.modal', () => $('#edit_nombre_responsable').focus().select());

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
