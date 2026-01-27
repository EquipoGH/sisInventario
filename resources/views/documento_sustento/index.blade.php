@extends('layouts.main')

@section('title', 'Documentos Sustento')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gestión de Documentos Sustento</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
                            <i class="fas fa-plus"></i> Nuevo Documento
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
                                       placeholder="Buscar por tipo, número o fecha..."
                                       autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="btnLimpiar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block text-right">
                                <span id="infoResultados">
                                    Mostrando <strong id="from">{{ $documentos->firstItem() ?? 0 }}</strong>
                                    a <strong id="to">{{ $documentos->lastItem() ?? 0 }}</strong>
                                    de <strong id="resultadosCount">{{ $documentos->total() }}</strong>
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
                    <small class="text-muted"><i class="fas fa-info-circle"></i> Doble click en una fila para editar</small>
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
                                <th width="22%" class="sortable" data-column="tipo" style="cursor:pointer;">
                                    Tipo Documento <i class="fas fa-sort sort-icon"></i>
                                </th>
                                <th width="22%" class="sortable" data-column="numero" style="cursor:pointer;">
                                    Número Documento <i class="fas fa-sort sort-icon"></i>
                                </th>
                                <th width="15%" class="sortable" data-column="fecha" style="cursor:pointer;">
                                    Fecha Documento <i class="fas fa-sort sort-icon"></i>
                                </th>
                                <!-- ⭐ NUEVA COLUMNA: Bienes Asociados -->
                                <th width="10%" class="text-center">
                                    Bienes <i class="fas fa-box text-muted"></i>
                                </th>
                                <th width="18%">Fecha Registro</th>
                            </tr>
                        </thead>
                        <tbody id="tablaDocumentos">
                            @forelse($documentos as $doc)
                            <tr id="row-{{ $doc->id_documento }}" class="editable-row"
                                data-id="{{ $doc->id_documento }}"
                                data-tipo="{{ $doc->tipo_documento }}"
                                data-numero="{{ $doc->numero_documento }}"
                                data-fecha="{{ $doc->fecha_documento }}"
                                style="cursor: pointer;"
                                title="Doble click para editar">
                                <td class="text-center" onclick="event.stopPropagation();">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input checkbox-item"
                                               id="check-{{ $doc->id_documento }}"
                                               value="{{ $doc->id_documento }}">
                                        <label class="custom-control-label" for="check-{{ $doc->id_documento }}"></label>
                                    </div>
                                </td>
                                <td>{{ $doc->id_documento }}</td>
                                <td><strong>{{ $doc->tipo_documento }}</strong></td>
                                <td><span class="badge badge-info">{{ $doc->numero_documento }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($doc->fecha_documento)->format('d/m/Y') }}</td>
                                <!-- ⭐ NUEVA CELDA: Cantidad de bienes -->
                                <td class="text-center">
                                    @if($doc->bienes_count > 0)
                                        <span class="badge badge-success" title="{{ $doc->bienes_count }} bien(es) asociado(s)">
                                            <i class="fas fa-box"></i> {{ $doc->bienes_count }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <!-- ⭐ CORREGIDO: colspan de 6 a 7 -->
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No hay documentos registrados</p>
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
                            Mostrando <strong id="paginaInfo">{{ $documentos->firstItem() ?? 0 }} - {{ $documentos->lastItem() ?? 0 }}</strong>
                            de <strong>{{ $documentos->total() }}</strong>
                        </small>
                    </div>
                    <div id="paginacionLinks"></div>
                </div>

                <!-- Sin resultados -->
                <div id="noResultados" class="text-center py-4" style="display:none;">
                    <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
                    <h5>No se encontraron resultados</h5>
                    <p class="text-muted">No hay documentos que coincidan con "<strong id="terminoBuscado"></strong>"</p>
                    <button class="btn btn-outline-primary" id="btnMostrarTodo">
                        <i class="fas fa-undo"></i> Mostrar todo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Nuevo Documento Sustento
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCreate">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tipo_documento">Tipo de Documento <span class="text-danger">*</span></label>
                        <select name="tipo_documento" id="tipo_documento" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="FACTURA">Factura</option>
                            <option value="BOLETA">Boleta</option>
                            <option value="GUIA REMISION">Guía de Remisión</option>
                            <option value="ORDEN COMPRA">Orden de Compra</option>
                            <option value="ACTA">Acta</option>
                            <option value="RECIBO">Recibo</option>
                            <option value="NEA">NEA</option>
                            <option value="OTRO">Otro</option>
                        </select>
                        <span class="text-danger error-tipo_documento d-block mt-1"></span>
                    </div>

                    <div class="form-group">
                        <label for="numero_documento">Número de Documento <span class="text-danger">*</span></label>
                        <input type="text"
                               name="numero_documento"
                               id="numero_documento"
                               class="form-control"
                               maxlength="20"
                               required
                               placeholder="Ej: F001-12345"
                               autocomplete="off">
                        <small class="form-text text-muted">Máximo 20 caracteres</small>
                        <span class="text-danger error-numero_documento d-block mt-1"></span>
                        <small id="numero_feedback" class="form-text"></small>
                    </div>

                    <div class="form-group">
                        <label for="fecha_documento">Fecha de Documento <span class="text-danger">*</span></label>
                        <input type="date"
                            name="fecha_documento"
                            id="fecha_documento"
                            class="form-control"
                            required
                            value="{{ date('Y-m-d') }}"
                            max="{{ date('Y-m-d') }}">
                        <small class="form-text text-muted">
                            <i class="fas fa-calendar-check"></i> Fecha actual por defecto (puedes cambiarla)
                        </small>
                        <span class="text-danger error-fecha_documento d-block mt-1"></span>
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
                    <i class="fas fa-edit"></i> Editar Documento Sustento
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
                    <!-- ⭐ NUEVO: Alerta de bienes asociados -->
                    <div id="alert_bienes" class="alert alert-warning" style="display:none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> Este documento tiene <span id="count_bienes">0</span> bien(es) asociado(s).
                    </div>

                    <div class="form-group">
                        <label for="edit_tipo_documento">Tipo de Documento <span class="text-danger">*</span></label>
                        <select name="tipo_documento" id="edit_tipo_documento" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="FACTURA">Factura</option>
                            <option value="BOLETA">Boleta</option>
                            <option value="GUIA REMISION">Guía de Remisión</option>
                            <option value="ORDEN COMPRA">Orden de Compra</option>
                            <option value="ACTA">Acta</option>
                            <option value="RECIBO">Recibo</option>
                            <option value="NEA">NEA</option>
                            <option value="OTRO">Otro</option>
                        </select>
                        <span class="text-danger error-edit-tipo_documento d-block mt-1"></span>
                    </div>

                    <div class="form-group">
                        <label for="edit_numero_documento">Número de Documento <span class="text-danger">*</span></label>
                        <input type="text"
                               name="numero_documento"
                               id="edit_numero_documento"
                               class="form-control"
                               maxlength="20"
                               required
                               autocomplete="off">
                        <span class="text-danger error-edit-numero_documento d-block mt-1"></span>
                        <small id="edit_numero_feedback" class="form-text"></small>
                    </div>

                    <div class="form-group">
                        <label for="edit_fecha_documento">Fecha de Documento <span class="text-danger">*</span></label>
                        <input type="date"
                               name="fecha_documento"
                               id="edit_fecha_documento"
                               class="form-control"
                               required
                               max="{{ date('Y-m-d') }}">
                        <span class="text-danger error-edit-fecha_documento d-block mt-1"></span>
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
@endsection

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
    let numeroTimeout;
    let ordenActual = { columna: 'id', direccion: 'desc' };

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

    // ⭐ NUEVO: Validación en tiempo real del número (CREAR)
    $('#numero_documento').on('keyup', function() {
        const numero = $(this).val().trim();
        const feedback = $('#numero_feedback');

        if (numero.length === 0) {
            feedback.text('').removeClass('text-danger text-success');
            return;
        }

        clearTimeout(numeroTimeout);
        feedback.html('<i class="fas fa-spinner fa-spin"></i> Verificando...').removeClass('text-danger text-success').addClass('text-info');

        numeroTimeout = setTimeout(() => {
            $.post('{{ route("documento-sustento.verificar-numero") }}', {
                numero: numero,
                id: null
            }, function(res) {
                if (res.existe) {
                    feedback.html('<i class="fas fa-times-circle"></i> Este número ya está registrado')
                        .removeClass('text-info text-success').addClass('text-danger');
                    $('#btnGuardar').prop('disabled', true);
                } else {
                    feedback.html('<i class="fas fa-check-circle"></i> Número disponible')
                        .removeClass('text-info text-danger').addClass('text-success');
                    $('#btnGuardar').prop('disabled', false);
                }
            });
        }, 500);
    });

    // ⭐ NUEVO: Validación en tiempo real del número (EDITAR)
    $('#edit_numero_documento').on('keyup', function() {
        const numero = $(this).val().trim();
        const feedback = $('#edit_numero_feedback');
        const id = $('#edit_id').val();

        if (numero.length === 0) {
            feedback.text('').removeClass('text-danger text-success');
            return;
        }

        clearTimeout(numeroTimeout);
        feedback.html('<i class="fas fa-spinner fa-spin"></i> Verificando...').removeClass('text-danger text-success').addClass('text-info');

        numeroTimeout = setTimeout(() => {
            $.post('{{ route("documento-sustento.verificar-numero") }}', {
                numero: numero,
                id: id
            }, function(res) {
                if (res.existe) {
                    feedback.html('<i class="fas fa-times-circle"></i> Este número ya está registrado')
                        .removeClass('text-info text-success').addClass('text-danger');
                    $('#btnActualizar').prop('disabled', true);
                } else {
                    feedback.html('<i class="fas fa-check-circle"></i> Número válido')
                        .removeClass('text-info text-danger').addClass('text-success');
                    $('#btnActualizar').prop('disabled', false);
                }
            });
        }, 500);
    });

    // ===============================
    // BÚSQUEDA
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
            url: '{{ route("documento-sustento.index") }}',
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

    function actualizarTabla(documentos) {
        const tbody = $('#tablaDocumentos');
        tbody.empty();

        if (documentos.length === 0) return;

        documentos.forEach(doc => {
            const fechaDoc = new Date(doc.fecha_documento).toLocaleDateString('es-PE', {
                day: '2-digit', month: '2-digit', year: 'numeric'
            });
            const fechaReg = new Date(doc.created_at).toLocaleDateString('es-PE', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });

            // ⭐ NUEVO: Badge de bienes
            const bienesHtml = doc.bienes_count > 0
                ? `<span class="badge badge-success" title="${doc.bienes_count} bien(es) asociado(s)">
                       <i class="fas fa-box"></i> ${doc.bienes_count}
                   </span>`
                : `<span class="text-muted">-</span>`;

            tbody.append(`
                <tr id="row-${doc.id_documento}" class="editable-row fade-in"
                    data-id="${doc.id_documento}"
                    data-tipo="${doc.tipo_documento}"
                    data-numero="${doc.numero_documento}"
                    data-fecha="${doc.fecha_documento}"
                    data-bienes="${doc.bienes_count || 0}"
                    style="cursor:pointer"
                    title="Doble click para editar">
                    <td class="text-center" onclick="event.stopPropagation();">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input checkbox-item"
                                   id="check-${doc.id_documento}"
                                   value="${doc.id_documento}"
                                   data-bienes="${doc.bienes_count || 0}">
                            <label class="custom-control-label" for="check-${doc.id_documento}"></label>
                        </div>
                    </td>
                    <td>${doc.id_documento}</td>
                    <td><strong>${doc.tipo_documento.toUpperCase()}</strong></td>
                    <td><span class="badge badge-info">${doc.numero_documento}</span></td>
                    <td>${fechaDoc}</td>
                    <td class="text-center">${bienesHtml}</td>
                    <td>${fechaReg}</td>
                </tr>
            `);
        });

        $('.checkbox-item').on('change', actualizarBotonEliminar);
        $('#checkAll').prop('checked', false);

        $('.editable-row').on('dblclick', function() {
            abrirModalEditar($(this));
        });
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
        ordenActual = { columna: 'id', direccion: 'desc' };
        actualizarIconosOrdenamiento();
        buscar('', 1);
    });

    // ===============================
    // CHECKBOX
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

        if ($('.checkbox-item:checked').length === $('.checkbox-item').length && $('.checkbox-item').length > 0) {
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
    // ⭐ ELIMINAR SELECCIONADOS (MEJORADO)
    // ===============================
    $('#btnEliminarSeleccionados').on('click', function() {
        let seleccionados = [];
        let conBienes = 0;

        $('.checkbox-item:checked').each(function() {
            const id = $(this).val();
            const bienesCount = parseInt($(this).data('bienes')) || 0;

            seleccionados.push({
                id: id,
                bienes: bienesCount
            });

            if (bienesCount > 0) {
                conBienes++;
            }
        });

        if (seleccionados.length === 0) {
            Swal.fire('Aviso', 'No hay registros seleccionados', 'info');
            return;
        }

        // ⭐ ADVERTENCIA si hay documentos con bienes
        let mensaje = `¿Eliminar ${seleccionados.length} documento(s)?`;
        if (conBienes > 0) {
            mensaje += `\n\n⚠️ ADVERTENCIA: ${conBienes} documento(s) tiene(n) bienes asociados y NO se podrán eliminar.`;
        }

        Swal.fire({
            title: mensaje,
            text: "Esta acción no se puede revertir",
            icon: conBienes > 0 ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarMultiples(seleccionados);
            }
        });
    });

    function eliminarMultiples(documentos) {
        Swal.fire({
            title: 'Procesando...',
            html: 'Eliminados: <b>0</b> | Errores: <b>0</b> de <b>' + documentos.length + '</b>',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        let eliminados = 0;
        let errores = 0;
        let conBienes = [];

        let promesas = documentos.map(doc => {
            return $.ajax({
                url: '/documento-sustento/' + doc.id,
                method: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                }
            }).then(() => {
                eliminados++;
                Swal.update({
                    html: 'Eliminados: <b>' + eliminados + '</b> | Errores: <b>' + errores + '</b> de <b>' + documentos.length + '</b>'
                });
            }).catch((xhr) => {
                errores++;

                // ⭐ Si tiene bienes, guardar info
                if (xhr.status === 409 && xhr.responseJSON && xhr.responseJSON.bienes_count) {
                    conBienes.push({
                        id: doc.id,
                        count: xhr.responseJSON.bienes_count
                    });
                }

                Swal.update({
                    html: 'Eliminados: <b>' + eliminados + '</b> | Errores: <b>' + errores + '</b> de <b>' + documentos.length + '</b>'
                });
            });
        });

        Promise.allSettled(promesas).then(() => {
            let htmlMensaje = '<p>Eliminados: <b>' + eliminados + '</b></p>';

            if (errores > 0) {
                htmlMensaje += '<p>No eliminados: <b>' + errores + '</b></p>';

                if (conBienes.length > 0) {
                    htmlMensaje += '<hr><small class="text-muted">No se pueden eliminar documentos con bienes asociados.</small>';
                }
            }

            Swal.fire({
                icon: errores === 0 ? 'success' : 'warning',
                title: errores === 0 ? '¡Completado!' : 'Completado con errores',
                html: htmlMensaje,
                confirmButtonText: 'Aceptar'
            }).then(() => {
                $('.checkbox-item').prop('checked', false);
                $('#checkAll').prop('checked', false);
                $('#btnEliminarSeleccionados').fadeOut();
                buscar($('#searchInput').val(), paginaActual);
            });
        });
    }

    // ===============================
    // DOBLE CLICK PARA EDITAR
    // ===============================
    $(document).on('dblclick', '.editable-row', function() {
        abrirModalEditar($(this));
    });

    function abrirModalEditar(row) {
        let id = row.data('id');
        let tipo = row.data('tipo');
        let numero = row.data('numero');
        let fecha = row.data('fecha');
        let bienesCount = row.data('bienes') || 0;

        $('.error-edit-tipo_documento').text('');
        $('.error-edit-numero_documento').text('');
        $('.error-edit-fecha_documento').text('');
        $('#edit_numero_feedback').text('');

        $('#edit_id').val(id);
        $('#edit_tipo_documento').val(tipo);
        $('#edit_numero_documento').val(numero);
        $('#edit_fecha_documento').val(fecha);

        // ⭐ Mostrar alerta si tiene bienes
        if (bienesCount > 0) {
            $('#count_bienes').text(bienesCount);
            $('#alert_bienes').slideDown();
        } else {
            $('#alert_bienes').hide();
        }

        $('#modalEdit').modal('show');

        $('#modalEdit').on('shown.bs.modal', function() {
            $('#edit_tipo_documento').focus();
        });
    }

    // ===============================
    // CREAR
    // ===============================
    $('#formCreate').on('submit', function(e) {
        e.preventDefault();

        $('.error-tipo_documento').text('');
        $('.error-numero_documento').text('');
        $('.error-fecha_documento').text('');

        let btnGuardar = $('#btnGuardar');
        btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("documento-sustento.store") }}',
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
                    if(errors.tipo_documento) $('.error-tipo_documento').text(errors.tipo_documento[0]);
                    if(errors.numero_documento) $('.error-numero_documento').text(errors.numero_documento[0]);
                    if(errors.fecha_documento) $('.error-fecha_documento').text(errors.fecha_documento[0]);
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

        $('.error-edit-tipo_documento').text('');
        $('.error-edit-numero_documento').text('');
        $('.error-edit-fecha_documento').text('');

        let btnActualizar = $('#btnActualizar');
        btnActualizar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        let id = $('#edit_id').val();

        $.ajax({
            url: '/documento-sustento/' + id,
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
                    if(errors.tipo_documento) $('.error-edit-tipo_documento').text(errors.tipo_documento[0]);
                    if(errors.numero_documento) $('.error-edit-numero_documento').text(errors.numero_documento[0]);
                    if(errors.fecha_documento) $('.error-edit-fecha_documento').text(errors.fecha_documento[0]);
                } else {
                    Swal.fire('Error', 'No se pudo actualizar el registro', 'error');
                }
            }
        });
    });

    // Limpiar formularios al cerrar
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('.error-tipo_documento').text('');
        $('.error-numero_documento').text('');
        $('.error-fecha_documento').text('');
        $('#numero_feedback').text('');
        $('#btnGuardar').prop('disabled', false);
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        $('.error-edit-tipo_documento').text('');
        $('.error-edit-numero_documento').text('');
        $('.error-edit-fecha_documento').text('');
        $('#edit_numero_feedback').text('');
        $('#alert_bienes').hide();
        $('#btnActualizar').prop('disabled', false);
    });

    $('#modalCreate').on('shown.bs.modal', function() {
        $('#tipo_documento').focus();
    });
});
</script>
@endsection
