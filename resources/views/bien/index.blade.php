@extends('layouts.main')

@section('title', 'Gestión de Bienes')

@section('content_header')
    <h1>Gestión de Bienes Patrimoniales</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row mb-3">
            <div class="col-md-4">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Nuevo Bien
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
                               placeholder="Buscar por código, denominación, marca..."
                               autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block text-right">
                        <span id="infoResultados">
                            Mostrando <strong id="from">{{ $bienes->firstItem() ?? 0 }}</strong>
                            a <strong id="to">{{ $bienes->lastItem() ?? 0 }}</strong>
                            de <strong id="resultadosCount">{{ $bienes->total() }}</strong>
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
            <small class="text-muted"><i class="fas fa-info-circle"></i> Doble click en la denominación para editar</small>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tablaBienes">
                <thead class="thead-dark">
                    <tr>
                        <th width="3%">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="checkAll">
                                <label class="custom-control-label" for="checkAll"></label>
                            </div>
                        </th>
                        <th width="5%">Foto</th>
                        <th width="12%" class="sortable" data-column="codigo" style="cursor:pointer;">
                            Código <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="25%" class="sortable" data-column="denominacion" style="cursor:pointer;">
                            Denominación <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="12%">Tipo</th>
                        <th width="10%">Marca</th>
                        <th width="10%">Modelo</th>
                        <th width="12%" class="sortable" data-column="fecha" style="cursor:pointer;">
                            Fecha Registro <i class="fas fa-sort sort-icon"></i>
                        </th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    @forelse($bienes as $bien)
                    <tr id="row-{{ $bien->id_bien }}">
                        <td class="text-center">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox-item"
                                       id="check-{{ $bien->id_bien }}"
                                       value="{{ $bien->id_bien }}">
                                <label class="custom-control-label" for="check-{{ $bien->id_bien }}"></label>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($bien->foto_bien)
                                <button class="btn btn-sm btn-info btn-ver-foto" data-foto="{{ $bien->foto_bien }}">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                            @else
                                <span class="text-muted">Sin foto</span>
                            @endif
                        </td>
                        <td><strong>{{ $bien->codigo_patrimonial }}</strong></td>
                        <td class="editable-cell"
                            data-id="{{ $bien->id_bien }}"
                            style="cursor: pointer;"
                            title="Doble click para editar">
                            {{ $bien->denominacion_bien }}
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $bien->tipoBien->nombre_tipo ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $bien->marca_bien ?? '-' }}</td>
                        <td>{{ $bien->modelo_bien ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($bien->fecha_registro)->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No hay bienes registrados</p>
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
                    Mostrando <strong id="paginaInfo">{{ $bienes->firstItem() ?? 0 }} - {{ $bienes->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $bienes->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks"></div>
        </div>

        <!-- Sin resultados -->
        <div id="noResultados" class="text-center py-4" style="display:none;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
            <h5>No se encontraron resultados</h5>
            <p class="text-muted">No hay bienes que coincidan con "<strong id="terminoBuscado"></strong>"</p>
            <button class="btn btn-outline-primary" id="btnMostrarTodo">
                <i class="fas fa-undo"></i> Mostrar todo
            </button>
        </div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Registrar Nuevo Bien
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCreate" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- COLUMNA IZQUIERDA: Formulario -->
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="codigo_patrimonial">Código Patrimonial <span class="text-danger">*</span></label>
                                        <input type="text" name="codigo_patrimonial" id="codigo_patrimonial"
                                               class="form-control" maxlength="20" required autocomplete="off">
                                        <span class="text-danger error-codigo_patrimonial"></span>
                                        <small id="codigo_feedback" class="form-text"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_tipobien">Tipo de Bien <span class="text-danger">*</span></label>
                                        <select name="id_tipobien" id="id_tipobien" class="form-control" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($tiposBien as $tipo)
                                                <option value="{{ $tipo->id_tipo_bien }}">{{ $tipo->nombre_tipo }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger error-id_tipobien"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="denominacion_bien">Denominación <span class="text-danger">*</span></label>
                                <input type="text" name="denominacion_bien" id="denominacion_bien"
                                       class="form-control" maxlength="100" required>
                                <span class="text-danger error-denominacion_bien"></span>
                            </div>

                            <!-- MARCA - ANCHO COMPLETO -->
                            <div class="form-group">
                                <label for="marca_bien">Marca</label>
                                <input type="text" name="marca_bien" id="marca_bien"
                                       class="form-control" maxlength="20">
                            </div>

                            <!-- MODELO - ANCHO COMPLETO -->
                            <div class="form-group">
                                <label for="modelo_bien">Modelo</label>
                                <input type="text" name="modelo_bien" id="modelo_bien"
                                       class="form-control" maxlength="20">
                            </div>

                            <!-- COLOR - ANCHO COMPLETO -->
                            <div class="form-group">
                                <label for="color_bien">Color</label>
                                <input type="text" name="color_bien" id="color_bien"
                                       class="form-control" maxlength="20">
                            </div>

                            <!-- DIMENSIONES Y N° SERIE EN LA MISMA FILA -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dimensiones_bien">Dimensiones</label>
                                        <input type="text" name="dimensiones_bien" id="dimensiones_bien"
                                               class="form-control" maxlength="50" placeholder="Ej: 50x30x20 cm">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nserie_bien">N° de Serie</label>
                                        <input type="text" name="nserie_bien" id="nserie_bien"
                                               class="form-control" maxlength="20">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="fecha_registro">Fecha de Registro <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_registro" id="fecha_registro"
                                       class="form-control" required value="{{ date('Y-m-d') }}">
                                <span class="text-danger error-fecha_registro"></span>
                            </div>
                        </div>

                        <!-- COLUMNA DERECHA: Vista previa de imagen -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="foto_bien">Foto del Bien</label>
                                <input type="file" name="foto_bien" id="foto_bien"
                                       class="form-control-file" accept="image/*">
                                <small class="text-muted d-block">JPG, PNG o GIF - Máx. 5MB</small>
                                <span class="text-danger error-foto_bien"></span>
                            </div>
                            <div id="preview_create" class="text-center p-3 border rounded" style="min-height: 300px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                <div id="preview_placeholder">
                                    <i class="fas fa-image fa-5x text-muted mb-3"></i>
                                    <p class="text-muted">Previsualización de imagen</p>
                                </div>
                                <img id="img_preview_create" src="" class="img-fluid" style="display:none; max-height: 400px; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Bien
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEdit" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <!-- COLUMNA IZQUIERDA -->
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_codigo_patrimonial">Código Patrimonial <span class="text-danger">*</span></label>
                                        <input type="text" name="codigo_patrimonial" id="edit_codigo_patrimonial"
                                               class="form-control" maxlength="20" required autocomplete="off">
                                        <span class="text-danger error-edit-codigo_patrimonial"></span>
                                        <small id="edit_codigo_feedback" class="form-text"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_id_tipobien">Tipo de Bien <span class="text-danger">*</span></label>
                                        <select name="id_tipobien" id="edit_id_tipobien" class="form-control" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($tiposBien as $tipo)
                                                <option value="{{ $tipo->id_tipo_bien }}">{{ $tipo->nombre_tipo }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger error-edit-id_tipobien"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="edit_denominacion_bien">Denominación <span class="text-danger">*</span></label>
                                <input type="text" name="denominacion_bien" id="edit_denominacion_bien"
                                       class="form-control" maxlength="100" required>
                                <span class="text-danger error-edit-denominacion_bien"></span>
                            </div>

                            <!-- MARCA - ANCHO COMPLETO -->
                            <div class="form-group">
                                <label for="edit_marca_bien">Marca</label>
                                <input type="text" name="marca_bien" id="edit_marca_bien"
                                       class="form-control" maxlength="20">
                            </div>

                            <!-- MODELO - ANCHO COMPLETO -->
                            <div class="form-group">
                                <label for="edit_modelo_bien">Modelo</label>
                                <input type="text" name="modelo_bien" id="edit_modelo_bien"
                                       class="form-control" maxlength="20">
                            </div>

                            <!-- COLOR - ANCHO COMPLETO -->
                            <div class="form-group">
                                <label for="edit_color_bien">Color</label>
                                <input type="text" name="color_bien" id="edit_color_bien"
                                       class="form-control" maxlength="20">
                            </div>

                            <!-- DIMENSIONES Y N° SERIE EN LA MISMA FILA -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_dimensiones_bien">Dimensiones</label>
                                        <input type="text" name="dimensiones_bien" id="edit_dimensiones_bien"
                                               class="form-control" maxlength="50">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_nserie_bien">N° de Serie</label>
                                        <input type="text" name="nserie_bien" id="edit_nserie_bien"
                                               class="form-control" maxlength="20">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="edit_fecha_registro">Fecha de Modificación <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_registro" id="edit_fecha_registro"
                                       class="form-control bg-light" required readonly>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Fecha automática de modificación
                                </small>
                                <span class="text-danger error-edit-fecha_registro"></span>
                            </div>
                        </div>

                        <!-- COLUMNA DERECHA -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_foto_bien">Cambiar Foto</label>
                                <input type="file" name="foto_bien" id="edit_foto_bien"
                                       class="form-control-file" accept="image/*">
                                <small class="text-muted d-block">Dejar vacío para mantener la actual</small>
                                <span class="text-danger error-edit-foto_bien"></span>
                            </div>
                            <div id="preview_edit" class="text-center p-3 border rounded" style="min-height: 300px; background-color: #f8f9fa;">
                                <img id="img_preview_edit" src="" class="img-fluid" style="max-height: 400px; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-info" id="btnActualizar">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Foto Grande -->
<div class="modal fade" id="modalFoto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vista de Imagen</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="foto_grande" src="" class="img-fluid" style="max-height: 70vh;">
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
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let paginaActual = 1;
    let searchTimeout;
    let codigoTimeout;
    let ordenActual = { columna: 'fecha', direccion: 'desc' }; // 🔥 ORDENAMIENTO

    // 🔥 ESTABLECER ICONO INICIAL
    actualizarIconosOrdenamiento();

    // ===============================
    // 🔥 ORDENAMIENTO AL HACER CLICK EN COLUMNAS
    // ===============================
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
    // VALIDACIÓN EN TIEMPO REAL DEL CÓDIGO PATRIMONIAL (CREAR)
    // ===============================
    $('#codigo_patrimonial').on('keyup', function() {
        const codigo = $(this).val().trim();
        const feedback = $('#codigo_feedback');

        if (codigo.length === 0) {
            feedback.text('').removeClass('text-danger text-success');
            return;
        }

        clearTimeout(codigoTimeout);
        feedback.html('<i class="fas fa-spinner fa-spin"></i> Verificando...').removeClass('text-danger text-success').addClass('text-info');

        codigoTimeout = setTimeout(() => {
            $.post('{{ route("bien.verificar-codigo") }}', {
                codigo: codigo,
                id: null
            }, function(res) {
                if (res.existe) {
                    feedback.html('<i class="fas fa-times-circle"></i> Este código ya está registrado')
                        .removeClass('text-info text-success').addClass('text-danger');
                    $('#btnGuardar').prop('disabled', true);
                } else {
                    feedback.html('<i class="fas fa-check-circle"></i> Código disponible')
                        .removeClass('text-info text-danger').addClass('text-success');
                    $('#btnGuardar').prop('disabled', false);
                }
            });
        }, 500);
    });

    // ===============================
    // VALIDACIÓN EN TIEMPO REAL DEL CÓDIGO PATRIMONIAL (EDITAR)
    // ===============================
    $('#edit_codigo_patrimonial').on('keyup', function() {
        const codigo = $(this).val().trim();
        const feedback = $('#edit_codigo_feedback');
        const id = $('#edit_id').val();

        if (codigo.length === 0) {
            feedback.text('').removeClass('text-danger text-success');
            return;
        }

        clearTimeout(codigoTimeout);
        feedback.html('<i class="fas fa-spinner fa-spin"></i> Verificando...').removeClass('text-danger text-success').addClass('text-info');

        codigoTimeout = setTimeout(() => {
            $.post('{{ route("bien.verificar-codigo") }}', {
                codigo: codigo,
                id: id
            }, function(res) {
                if (res.existe) {
                    feedback.html('<i class="fas fa-times-circle"></i> Este código ya está registrado')
                        .removeClass('text-info text-success').addClass('text-danger');
                    $('#btnActualizar').prop('disabled', true);
                } else {
                    feedback.html('<i class="fas fa-check-circle"></i> Código válido')
                        .removeClass('text-info text-danger').addClass('text-success');
                    $('#btnActualizar').prop('disabled', false);
                }
            });
        }, 500);
    });

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
            url: '{{ route("bien.index") }}',
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

    function actualizarTabla(bienes) {
        const tbody = $('#tablaBody');
        tbody.empty();

        if (bienes.length === 0) return;

        bienes.forEach(b => {
            const fecha = new Date(b.fecha_registro).toLocaleDateString('es-PE', {
                day: '2-digit', month: '2-digit', year: 'numeric'
            });

            const fotoHtml = b.foto_bien
                ? `<button class="btn btn-sm btn-info btn-ver-foto" data-foto="${b.foto_bien}">
                       <i class="fas fa-eye"></i> Ver
                   </button>`
                : `<span class="text-muted">Sin foto</span>`;

            const tipoNombre = b.tipo_bien ? b.tipo_bien.nombre_tipo : 'N/A';

            tbody.append(`
                <tr id="row-${b.id_bien}">
                    <td class="text-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input checkbox-item"
                                   id="check-${b.id_bien}"
                                   value="${b.id_bien}">
                            <label class="custom-control-label" for="check-${b.id_bien}"></label>
                        </div>
                    </td>
                    <td class="text-center">${fotoHtml}</td>
                    <td><strong>${b.codigo_patrimonial}</strong></td>
                    <td class="editable-cell" data-id="${b.id_bien}" style="cursor:pointer"
                        title="Doble click para editar">${b.denominacion_bien.toUpperCase()}</td>
                    <td><span class="badge badge-info">${tipoNombre}</span></td>
                    <td>${b.marca_bien || '-'}</td>
                    <td>${b.modelo_bien || '-'}</td>
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
        ordenActual = { columna: 'fecha', direccion: 'desc' }; // 🔥 RESETEAR ORDEN
        actualizarIconosOrdenamiento();
        buscar('', 1);
    });

    // ===============================
    // VISTA PREVIA DE IMAGEN
    // ===============================
    $('#foto_bien').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview_placeholder').hide();
                $('#img_preview_create').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(file);
        }
    });

    $('#edit_foto_bien').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#img_preview_edit').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // ===============================
    // VER FOTO EN GRANDE
    // ===============================
    $(document).on('click', '.btn-ver-foto', function() {
        const foto = $(this).data('foto');
        $('#foto_grande').attr('src', foto);
        $('#modalFoto').modal('show');
    });

    // Checkbox seleccionar todos
    $('#checkAll').on('change', function() {
        $('.checkbox-item').prop('checked', $(this).is(':checked'));
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
        seleccionados > 0 ? $('#btnEliminarSeleccionados').fadeIn() : $('#btnEliminarSeleccionados').fadeOut();
    }

    // Eliminar seleccionados
    $('#btnEliminarSeleccionados').on('click', function() {
        let ids = [];
        $('.checkbox-item:checked').each(function() {
            ids.push($(this).val());
        });

        Swal.fire({
            title: '¿Eliminar ' + ids.length + ' bien(es)?',
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarMultiples(ids);
            }
        });
    });

    function eliminarMultiples(ids) {
        Swal.fire({
            title: 'Eliminando...',
            html: 'Eliminando <b>0</b> de <b>' + ids.length + '</b> bienes',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        let eliminados = 0;
        let promesas = ids.map(id => {
            return $.ajax({
                url: '/bien/' + id,
                method: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' }
            }).then(() => {
                eliminados++;
                Swal.update({
                    html: 'Eliminando <b>' + eliminados + '</b> de <b>' + ids.length + '</b> bienes'
                });
            });
        });

        Promise.allSettled(promesas).then(() => {
            $('.checkbox-item').prop('checked', false);
            $('#checkAll').prop('checked', false);
            $('#btnEliminarSeleccionados').fadeOut();
            $('#contadorSeleccionados').text('0');

            Swal.fire({
                icon: 'success',
                title: '¡Eliminados!',
                text: eliminados + ' bien(es) eliminado(s)',
                timer: 1500,
                showConfirmButton: false
            }).then(() => buscar($('#searchInput').val(), paginaActual));
        });
    }

    // Doble click para editar
    $(document).on('dblclick', '.editable-cell', function() {
        let id = $(this).data('id');
        cargarDatosEdicion(id);
    });

    function cargarDatosEdicion(id) {
        $.get('/bien/' + id + '/edit', function(data) {
            $('#edit_id').val(data.id_bien);
            $('#edit_codigo_patrimonial').val(data.codigo_patrimonial);
            $('#edit_denominacion_bien').val(data.denominacion_bien);
            $('#edit_id_tipobien').val(data.id_tipobien);
            $('#edit_marca_bien').val(data.marca_bien);
            $('#edit_modelo_bien').val(data.modelo_bien);
            $('#edit_color_bien').val(data.color_bien);
            $('#edit_dimensiones_bien').val(data.dimensiones_bien);
            $('#edit_nserie_bien').val(data.nserie_bien);

            const hoy = new Date().toISOString().split('T')[0];
            $('#edit_fecha_registro').val(hoy);

            if (data.foto_bien) {
                $('#img_preview_edit').attr('src', data.foto_bien).show();
            }

            $('#modalEdit').modal('show');
        });
    }

    // Crear
    $('#formCreate').on('submit', function(e) {
        e.preventDefault();

        $('.text-danger').text('');
        let btnGuardar = $('#btnGuardar');
        btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        let formData = new FormData(this);

        $.ajax({
            url: '{{ route("bien.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if(response.success) {
                    $('#modalCreate').modal('hide');
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
                    $.each(errors, function(key, value) {
                        $('.error-' + key).text(value[0]);
                    });
                } else {
                    Swal.fire('Error', 'No se pudo guardar', 'error');
                }
            }
        });
    });

    // Actualizar
    $('#formEdit').on('submit', function(e) {
        e.preventDefault();

        $('.text-danger').text('');
        let btnActualizar = $('#btnActualizar');
        btnActualizar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        let id = $('#edit_id').val();
        let formData = new FormData(this);
        formData.append('_method', 'PUT');

        $.ajax({
            url: '/bien/' + id,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
                    $.each(errors, function(key, value) {
                        $('.error-edit-' + key).text(value[0]);
                    });
                } else {
                    Swal.fire('Error', 'No se pudo actualizar', 'error');
                }
            }
        });
    });

    // Limpiar formularios
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('.text-danger').text('');
        $('#codigo_feedback').text('').removeClass('text-danger text-success text-info');
        $('#img_preview_create').hide();
        $('#preview_placeholder').show();
        $('#btnGuardar').prop('disabled', false);
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        $('.text-danger').text('');
        $('#edit_codigo_feedback').text('').removeClass('text-danger text-success text-info');
        $('#btnActualizar').prop('disabled', false);
    });
});
</script>
@stop

@section('css')
<style>
    .editable-cell {
        transition: all 0.2s ease;
        user-select: none;
    }
    .editable-cell:hover {
        background-color: #e3f2fd !important;
        font-weight: bold;
    }
    .btn-ver-foto {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    .sortable {
        transition: all 0.2s ease;
    }
    .sortable:hover {
        background-color: #495057 !important;
    }
    .sort-icon {
        font-size: 0.8rem;
        margin-left: 5px;
    }
</style>
@endsection
