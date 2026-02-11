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
                <button type="button" class="btn btn-secondary ml-2" id="btnVerEliminados">
                    <i class="fas fa-trash-restore"></i> Eliminados
                    <span class="badge badge-light" id="badgeEliminados">0</span>
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
                        <th width="10%" class="sortable" data-column="codigo" style="cursor:pointer;">
                            Código <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="20%" class="sortable" data-column="denominacion" style="cursor:pointer;">
                            Denominación <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="10%" class="sortable" data-column="numdoc" style="cursor:pointer;">
                            Doc. Sustento <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="10%">Tipo</th>
                        <th width="8%">Marca</th>
                        <th width="8%">Modelo</th>
                        <th width="10%" class="sortable" data-column="fecha" style="cursor:pointer;">
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
                                    <i class="fas fa-eye"></i>
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
                            @if($bien->NumDoc)
                                <span class="badge badge-secondary"
                                      title="{{ $bien->documentoSustento->tipo_documento ?? 'Documento' }}">
                                    <i class="fas fa-file-alt"></i> {{ $bien->NumDoc }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
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
                        <td colspan="9" class="text-center text-muted">
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

                            <!-- ⭐ DOCUMENTO SUSTENTO -->
<div class="form-group">
    <label for="id_documento">
        Documento Sustentatorio
        <small class="text-muted">(Opcional)</small>
    </label>
    <select name="id_documento" id="id_documento" class="form-control">
        <option value="">-- Sin documento --</option>
    </select>
    <span class="text-danger error-id_documento"></span>
</div>

<!-- ⭐⭐⭐ NUMDOC MANUAL ⭐⭐⭐ -->
<div class="form-group">
    <label for="NumDoc">
        Número de Documento
        <small class="text-muted">(Opcional - Texto libre)</small>
    </label>
    <input type="text"
           name="NumDoc"
           id="NumDoc"
           class="form-control"
           maxlength="50"
           placeholder="Ej: DOC-2024-001">
    <small class="form-text text-muted">
        <i class="fas fa-info-circle"></i> Campo libre para ingresar número de documento
    </small>
    <span class="text-danger error-NumDoc"></span>
</div>


                            <div class="form-group">
                                <label for="denominacion_bien">Denominación <span class="text-danger">*</span></label>
                                <input type="text" name="denominacion_bien" id="denominacion_bien"
                                       class="form-control" maxlength="100" required>
                                <span class="text-danger error-denominacion_bien"></span>
                            </div>

                            <div class="form-group">
                                <label for="marca_bien">Marca</label>
                                <input type="text" name="marca_bien" id="marca_bien"
                                       class="form-control" maxlength="20">
                            </div>

                            <div class="form-group">
                                <label for="modelo_bien">Modelo</label>
                                <input type="text" name="modelo_bien" id="modelo_bien"
                                       class="form-control" maxlength="20">
                            </div>

                            <div class="form-group">
                                <label for="color_bien">Color</label>
                                <input type="text" name="color_bien" id="color_bien"
                                       class="form-control" maxlength="20">
                            </div>

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

                           <!-- ⭐ DOCUMENTO SUSTENTO -->
                            <div class="form-group">
                                <label for="edit_id_documento">
                                    Documento Sustentatorio
                                    <small class="text-muted">(Opcional)</small>
                                </label>
                                <select name="id_documento" id="edit_id_documento" class="form-control">
                                    <option value="">-- Sin documento --</option>
                                </select>
                                <span class="text-danger error-edit-id_documento"></span>
                            </div>

                            <!-- ⭐⭐⭐ NUMDOC MANUAL (EDITAR) ⭐⭐⭐ -->
                            <div class="form-group">
                                <label for="edit_NumDoc">
                                    Número de Documento
                                    <small class="text-muted">(Opcional - Texto libre)</small>
                                </label>
                                <input type="text"
                                    name="NumDoc"
                                    id="edit_NumDoc"
                                    class="form-control"
                                    maxlength="50"
                                    placeholder="Ej: DOC-2024-001">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Campo libre para ingresar número de documento
                                </small>
                                <span class="text-danger error-edit-NumDoc"></span>
                            </div>


                            <div class="form-group">
                                <label for="edit_denominacion_bien">Denominación <span class="text-danger">*</span></label>
                                <input type="text" name="denominacion_bien" id="edit_denominacion_bien"
                                       class="form-control" maxlength="100" required>
                                <span class="text-danger error-edit-denominacion_bien"></span>
                            </div>

                            <div class="form-group">
                                <label for="edit_marca_bien">Marca</label>
                                <input type="text" name="marca_bien" id="edit_marca_bien"
                                       class="form-control" maxlength="20">
                            </div>

                            <div class="form-group">
                                <label for="edit_modelo_bien">Modelo</label>
                                <input type="text" name="modelo_bien" id="edit_modelo_bien"
                                       class="form-control" maxlength="20">
                            </div>

                            <div class="form-group">
                                <label for="edit_color_bien">Color</label>
                                <input type="text" name="color_bien" id="edit_color_bien"
                                       class="form-control" maxlength="20">
                            </div>

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


<!-- Modal Bienes Eliminados -->
<div class="modal fade" id="modalBienesEliminados" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash-restore"></i> Bienes Eliminados (Inactivos)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Búsqueda -->
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-secondary text-white">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <input type="text" id="searchEliminados" class="form-control"
                           placeholder="Buscar por código o denominación...">
                </div>

                <!-- Tabla -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th width="15%">Código</th>
                                <th width="30%">Denominación</th>
                                <th width="15%">Tipo</th>
                                <th width="20%">Eliminado en</th>
                                <th width="10%" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tablaEliminados">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div id="paginacionEliminados" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>


{{-- ==========================================
     ⭐⭐⭐ MODAL CONFIRMACIÓN ELIMINAR CON DETALLES ⭐⭐⭐
     ========================================== --}}
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación de Bien
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- INFO DEL BIEN --}}
                <div class="alert alert-light border">
                    <h6 class="mb-2">
                        <i class="fas fa-box"></i> <strong>Bien a eliminar:</strong>
                    </h6>
                    <p class="mb-1">
                        <strong>Código:</strong> <span id="eliminar_codigo" class="text-primary"></span>
                    </p>
                    <p class="mb-0">
                        <strong>Denominación:</strong> <span id="eliminar_denominacion"></span>
                    </p>
                </div>

                {{-- ADVERTENCIA SI TIENE MOVIMIENTOS --}}
                <div id="alertaMovimientos" style="display:none;">
                    <div class="alert alert-warning">
                        <h6 class="mb-3">
                            <i class="fas fa-exclamation-circle"></i> 
                            <strong>¡ATENCIÓN!</strong> Este bien tiene movimientos activos
                        </h6>

                        {{-- ÚLTIMO MOVIMIENTO --}}
                        <div class="card border-warning mb-0">
                            <div class="card-header bg-warning text-white py-2">
                                <strong><i class="fas fa-history"></i> Último Movimiento</strong>
                            </div>
                            <div class="card-body" style="background-color: #fffbf0;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong>Tipo:</strong>
                                            <span id="mov_tipo" class="badge"></span>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Área:</strong>
                                            <span id="mov_area" class="text-dark"></span>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Ubicación:</strong>
                                            <span id="mov_ubicacion" class="text-dark"></span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong>Estado:</strong>
                                            <span id="mov_estado" class="badge"></span>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Fecha:</strong>
                                            <span id="mov_fecha" class="text-dark"></span>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Usuario:</strong>
                                            <span id="mov_usuario" class="text-dark"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ESTADÍSTICAS --}}
                        <div class="mt-3 p-2 bg-light border rounded">
                            <small>
                                <i class="fas fa-chart-bar text-primary"></i>
                                <strong>Historial:</strong>
                                <span id="total_movimientos">0</span> movimiento(s) registrado(s)
                                (<span id="movimientos_vigentes">0</span> vigente(s),
                                <span id="movimientos_anulados">0</span> anulado(s))
                            </small>
                        </div>
                    </div>
                </div>

                {{-- SIN MOVIMIENTOS O SOLO REGISTRO --}}
                <div id="sinMovimientos" style="display:none;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <span id="mensajeSinMovimientos">Este bien no tiene movimientos registrados.</span>
                        <p class="mb-0 mt-2">
                            <small class="text-muted">
                                <i class="fas fa-check"></i> 
                                Puede eliminarse sin afectar operaciones activas
                            </small>
                        </p>
                    </div>
                    
                    {{-- INFO DEL REGISTRO INICIAL (si existe) --}}
                    <div id="infoRegistroInicial" style="display:none;" class="mt-2">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white py-2">
                                <small><i class="fas fa-clipboard-list"></i> Registro Inicial</small>
                            </div>
                            <div class="card-body bg-light py-2">
                                <small>
                                    <strong>Tipo:</strong> <span id="reg_tipo"></span><br>
                                    <strong>Fecha:</strong> <span id="reg_fecha"></span><br>
                                    <strong>Usuario:</strong> <span id="reg_usuario"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- ADVERTENCIA FINAL --}}
                <div class="alert alert-danger mb-0">
                    <p class="mb-2">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Tipo de eliminación:</strong> Lógica (Soft Delete)
                    </p>
                    <small class="text-muted">
                        • El bien se marcará como inactivo<br>
                        • Se conservará su historial completo para auditoría<br>
                        • Podrá ser restaurado desde "Bienes Eliminados"
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                    <i class="fas fa-trash-alt"></i> Sí, eliminar de todas formas
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
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let paginaActual = 1;
    let searchTimeout;
    let codigoTimeout;
    let ordenActual = { columna: 'fecha', direccion: 'desc' };
    let documentos = [];

    // ===============================
    // ⭐ CARGAR DOCUMENTOS AL INICIAR
    // ===============================
    cargarDocumentos();

    function cargarDocumentos() {
        $.get('{{ route("bien.obtener-documentos") }}', function(data) {
            documentos = data;
            llenarSelectDocumentos('#id_documento', data);
            llenarSelectDocumentos('#edit_id_documento', data);
        }).fail(function() {
            console.error('Error al cargar documentos sustento');
        });
    }

    function llenarSelectDocumentos(selector, data) {
        const select = $(selector);
        select.find('option:not(:first)').remove();

        data.forEach(doc => {
            select.append(`<option value="${doc.id}">${doc.text}</option>`);
        });
    }



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
                   <i class="fas fa-eye"></i>
               </button>`
            : `<span class="text-muted">Sin foto</span>`;

        // ⭐ CORREGIDO: nombre_tipo NO nombre
        const tipoNombre = b.tipo_bien ? b.tipo_bien.nombre_tipo : 'N/A';

        // ⭐ CORREGIDO: NumDoc (mayúsculas) y documento_sustento
        const numDocHtml = b.NumDoc
            ? `<span class="badge badge-secondary" title="${b.documento_sustento?.tipo_documento || 'Documento'}">
                   <i class="fas fa-file-alt"></i> ${b.NumDoc}
               </span>`
            : `<span class="text-muted">-</span>`;

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
                <td>${numDocHtml}</td>
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
        ordenActual = { columna: 'fecha', direccion: 'desc' };
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

    // ===============================
    // CHECKBOX SELECCIONAR TODOS
    // ===============================
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

    // ===============================
// ⭐⭐⭐ ELIMINAR SELECCIONADOS (CON MODAL DETALLADO) ⭐⭐⭐
// ===============================
$('#btnEliminarSeleccionados').on('click', function() {
    let ids = [];
    $('.checkbox-item:checked').each(function() {
        ids.push($(this).val());
    });

    if (ids.length === 0) {
        return;
    }

    // ⭐ SI ES SOLO 1 BIEN → Mostrar modal detallado
    if (ids.length === 1) {
        const bienId = ids[0];
        mostrarModalEliminarDetallado(bienId);
    } else {
        // ⭐ SI SON MÚLTIPLES → Confirmación simple
        Swal.fire({
            title: '¿Eliminar ' + ids.length + ' bienes?',
            html: `
                <p>Se eliminarán <strong>${ids.length}</strong> bienes de forma lógica</p>
                <p class="text-muted small">
                    <i class="fas fa-info-circle"></i>
                    Los bienes se marcarán como inactivos pero conservarán su historial
                </p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarMultiples(ids);
            }
        });
    }
});

/**
 * ⭐⭐⭐ MOSTRAR MODAL DETALLADO PARA ELIMINAR BIEN INDIVIDUAL ⭐⭐⭐
 */
/**
 * ⭐⭐⭐ MOSTRAR MODAL DETALLADO PARA ELIMINAR BIEN INDIVIDUAL ⭐⭐⭐
 */
function mostrarModalEliminarDetallado(bienId) {
    // Mostrar loading
    Swal.fire({
        title: 'Cargando información...',
        html: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
        showConfirmButton: false,
        allowOutsideClick: false
    });

    // Obtener último movimiento
    $.ajax({
        url: `/bien/${bienId}/ultimo-movimiento`,
        method: 'GET',
        success: function(response) {
            Swal.close();

            if (response.success) {
                // Llenar datos del bien
                $('#eliminar_codigo').text(response.bien.codigo);
                $('#eliminar_denominacion').text(response.bien.denominacion);

                if (response.tiene_movimientos) {
                    // ⭐ CASO 1: Tiene movimientos REALES (asignaciones, bajas, etc.)
                    const mov = response.ultimo_movimiento;
                    const stats = response.estadisticas;

                    $('#mov_tipo').text(mov.tipo).attr('class', 'badge ' + mov.tipo_badge);
                    $('#mov_area').text(mov.area);
                    $('#mov_ubicacion').text(mov.ubicacion);
                    $('#mov_estado').text(mov.estado_conservacion).attr('class', 'badge ' + mov.estado_badge);
                    $('#mov_fecha').text(mov.fecha);
                    $('#mov_usuario').text(mov.usuario);

                    $('#total_movimientos').text(stats.total_movimientos);
                    $('#movimientos_vigentes').text(stats.movimientos_vigentes);
                    $('#movimientos_anulados').text(stats.movimientos_anulados);

                    $('#alertaMovimientos').show();
                    $('#sinMovimientos').hide();
                } else {
                    // ⭐ CASO 2: Sin movimientos o solo registro inicial
                    
                    if (response.solo_registro) {
                        // ⭐ Sub-caso: Tiene movimiento de REGISTRO/SIN ASIGNAR
                        $('#mensajeSinMovimientos').html(
                            'Este bien está <strong>registrado pero sin asignar</strong>. ' +
                            'No tiene movimientos operativos activos.'
                        );
                        
                        // Mostrar info del registro inicial
                        $('#reg_tipo').text(response.movimiento_inicial.tipo);
                        $('#reg_fecha').text(response.movimiento_inicial.fecha);
                        $('#reg_usuario').text(response.movimiento_inicial.usuario);
                        $('#infoRegistroInicial').show();
                    } else {
                        // ⭐ Sub-caso: NO tiene ningún movimiento
                        $('#mensajeSinMovimientos').text(
                            'Este bien no tiene movimientos registrados.'
                        );
                        $('#infoRegistroInicial').hide();
                    }

                    $('#alertaMovimientos').hide();
                    $('#sinMovimientos').show();
                }

                // Guardar ID en el botón
                $('#btnConfirmarEliminar').data('bien-id', bienId);

                // Abrir modal
                $('#modalConfirmarEliminar').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar la información del bien'
            });
        }
    });
}


/**
 * ⭐ CONFIRMAR ELIMINACIÓN DESDE EL MODAL
 */
$(document).on('click', '#btnConfirmarEliminar', function() {
    const bienId = $(this).data('bien-id');

    $('#modalConfirmarEliminar').modal('hide');

    // Mostrar loading
    Swal.fire({
        title: 'Eliminando...',
        html: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
        showConfirmButton: false,
        allowOutsideClick: false
    });

    // Eliminar bien
    $.ajax({
        url: '/bien/' + bienId,
        method: 'POST',
        data: {
            _method: 'DELETE',
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Eliminado!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Limpiar selección
                    $('.checkbox-item').prop('checked', false);
                    $('#checkAll').prop('checked', false);
                    $('#btnEliminarSeleccionados').fadeOut();
                    $('#contadorSeleccionados').text('0');

                    // Recargar tabla
                    buscar($('#searchInput').val(), paginaActual);

                    // Actualizar contador de eliminados
                    cargarContadorEliminados();
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'No se pudo eliminar el bien'
            });
        }
    });
});

/**
 * ⭐ ELIMINACIÓN MÚLTIPLE (SIN MODAL DETALLADO)
 */
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
        }).then(() => {
            buscar($('#searchInput').val(), paginaActual);
            cargarContadorEliminados();
        });
    });
}


    // ===============================
    // DOBLE CLICK PARA EDITAR
    // ===============================
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

        // ⭐ Cargar documento sustento
        if (data.id_documento) {
            $('#edit_id_documento').val(data.id_documento);
        } else {
            $('#edit_id_documento').val('');
        }

        // ⭐⭐⭐ AGREGAR NUMDOC ⭐⭐⭐
        $('#edit_NumDoc').val(data.NumDoc || '');

        $('#edit_marca_bien').val(data.marca_bien || '');
        $('#edit_modelo_bien').val(data.modelo_bien || '');
        $('#edit_color_bien').val(data.color_bien || '');
        $('#edit_dimensiones_bien').val(data.dimensiones_bien || '');
        $('#edit_nserie_bien').val(data.nserie_bien || '');

        // ✅ FECHA ACTUAL PARA MODIFICACIÓN (CORREGIDO)
        $('#edit_fecha_registro').val('{{ now()->format("Y-m-d") }}');

        // Cargar foto si existe
        if (data.foto_bien) {
            $('#img_preview_edit').attr('src', data.foto_bien).show();
        } else {
            $('#img_preview_edit').hide();
        }

        // Abrir modal
        $('#modalEdit').modal('show');
    }).fail(function() {
        Swal.fire('Error', 'No se pudo cargar los datos del bien', 'error');
    });
}


    // ===============================
// GUARDAR NUEVO BIEN
// ===============================
$('#formCreate').on('submit', function(e) {
    e.preventDefault();

    $('.error-codigo_patrimonial, .error-id_tipobien, .error-denominacion_bien').text('');

    let btnGuardar = $('#btnGuardar');
    btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    let formData = new FormData(this);

    $.ajax({
        url: '{{ route("bien.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

            if(response.success) {
                $('#modalCreate').modal('hide');
                $('#formCreate')[0].reset();
                $('#preview_placeholder').show();
                $('#img_preview_create').hide();
                $('#preview_numdoc').hide();

                // ⭐ AGREGAR LA FILA DIRECTAMENTE CON NOMBRES CORRECTOS
                const bien = response.data;

                const fechaRegistro = new Date(bien.fecha_registro).toLocaleDateString('es-PE', {
                    day: '2-digit', month: '2-digit', year: 'numeric'
                });

                // ⭐ DOCUMENTO SUSTENTO (con nombres correctos)
                const docSustento = bien.documento_sustento ?
                    `<span class="badge badge-secondary" title="${bien.documento_sustento.tipo_documento || 'Documento'}">
                        <i class="fas fa-file-alt"></i> ${bien.NumDoc}
                     </span>` :
                    '<span class="text-muted">-</span>';

                // ⭐ TIPO DE BIEN (nombre_tipo NO nombre)
                const tipoNombre = bien.tipo_bien ? bien.tipo_bien.nombre_tipo : 'N/A';

                // ⭐ FOTO (foto_bien NO foto)
                const fotoHtml = bien.foto_bien ?
                    `<button class="btn btn-sm btn-info btn-ver-foto" data-foto="${bien.foto_bien}">
                        <i class="fas fa-eye"></i>
                     </button>` :
                    '<span class="text-muted">Sin foto</span>';

                const nuevaFila = `
                    <tr id="row-${bien.id_bien}">
                        <td class="text-center">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox-item"
                                       id="check-${bien.id_bien}"
                                       value="${bien.id_bien}">
                                <label class="custom-control-label" for="check-${bien.id_bien}"></label>
                            </div>
                        </td>
                        <td class="text-center">${fotoHtml}</td>
                        <td><strong>${bien.codigo_patrimonial}</strong></td>
                        <td class="editable-cell" data-id="${bien.id_bien}" style="cursor:pointer" title="Doble click para editar">
                            ${bien.denominacion_bien.toUpperCase()}
                        </td>
                        <td>${docSustento}</td>
                        <td><span class="badge badge-info">${tipoNombre}</span></td>
                        <td>${bien.marca_bien || '-'}</td>
                        <td>${bien.modelo_bien || '-'}</td>
                        <td>${fechaRegistro}</td>
                    </tr>
                `;

                // ⭐ AGREGAR AL INICIO DE LA TABLA
                $('#tablaBody').prepend(nuevaFila);

                // ⭐ ACTUALIZAR CONTADORES
                const totalActual = parseInt($('#totalCount').text()) + 1;
                $('#totalCount').text(totalActual);
                $('#resultadosCount').text(totalActual);

                const fromActual = parseInt($('#from').text());
                const toActual = parseInt($('#to').text()) + 1;
                $('#from').text(fromActual);
                $('#to').text(toActual);
                $('#paginaInfo').text(fromActual + ' - ' + toActual);

                // ⭐ REACTIVAR EVENTOS
                $('.checkbox-item').off('change').on('change', actualizarBotonEliminar);

                $('.editable-cell').off('dblclick').on('dblclick', function() {
                    let id = $(this).data('id');
                    cargarDatosEdicion(id);
                });

                $('.btn-ver-foto').off('click').on('click', function() {
                    const foto = $(this).data('foto');
                    $('#foto_grande').attr('src', foto);
                    $('#modalFoto').modal('show');
                });

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        },
        error: function(xhr) {
            btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

            if(xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                if(errors.codigo_patrimonial) $('.error-codigo_patrimonial').text(errors.codigo_patrimonial[0]);
                if(errors.id_tipobien) $('.error-id_tipobien').text(errors.id_tipobien[0]);
                if(errors.denominacion_bien) $('.error-denominacion_bien').text(errors.denominacion_bien[0]);
            } else {
                Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo guardar el bien', 'error');
            }
        }
    });
});



    // ===============================
    // ACTUALIZAR BIEN
    // ===============================
    $('#formEdit').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        let id = $('#edit_id').val();
        let btnActualizar = $('#btnActualizar');

        btnActualizar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        $.ajax({
            url: '/bien/' + id,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
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
                    $('.error-edit-codigo_patrimonial, .error-edit-denominacion_bien, .error-edit-id_tipobien, .error-edit-id_documento, .error-edit-fecha_registro, .error-edit-foto_bien').text('');

                    if(errors.codigo_patrimonial) $('.error-edit-codigo_patrimonial').text(errors.codigo_patrimonial[0]);
                    if(errors.denominacion_bien) $('.error-edit-denominacion_bien').text(errors.denominacion_bien[0]);
                    if(errors.id_tipobien) $('.error-edit-id_tipobien').text(errors.id_tipobien[0]);
                    if(errors.id_documento) $('.error-edit-id_documento').text(errors.id_documento[0]);
                    if(errors.fecha_registro) $('.error-edit-fecha_registro').text(errors.fecha_registro[0]);
                    if(errors.foto_bien) $('.error-edit-foto_bien').text(errors.foto_bien[0]);
                } else {
                    Swal.fire('Error', 'No se pudo actualizar el bien', 'error');
                }
            }
        });
    });

    // ===============================
    // LIMPIAR FORMULARIOS AL CERRAR MODALES
    // ===============================
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('#NumDoc').val(''); // ⭐ LIMPIAR NUMDOC
        $('#preview_numdoc').hide();
        $('#preview_placeholder').show();
        $('#img_preview_create').hide();
        $('.error-codigo_patrimonial, .error-denominacion_bien, .error-id_tipobien, .error-id_documento, .error-fecha_registro, .error-foto_bien').text('');
        $('#btnGuardar').prop('disabled', false);
        $('#codigo_feedback').text('');
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        $('#edit_NumDoc').val(''); // ⭐ LIMPIAR NUMDOC
        $('#edit_preview_numdoc').hide();
        $('.error-edit-codigo_patrimonial, .error-edit-denominacion_bien, .error-edit-id_tipobien, .error-edit-id_documento, .error-edit-fecha_registro, .error-edit-foto_bien').text('');
        $('#btnActualizar').prop('disabled', false);
        $('#edit_codigo_feedback').text('');
    });

    // ===============================
    // ✅ GESTIÓN DE BIENES ELIMINADOS
    // ===============================

    // Cargar contador al inicio
    cargarContadorEliminados();

    function cargarContadorEliminados() {
        $.get('{{ route("bien.eliminados") }}', function(response) {
            if (response.success) {
                $('#badgeEliminados').text(response.data.total);
            }
        });
    }

    // Abrir modal de eliminados
    $('#btnVerEliminados').on('click', function() {
        $('#modalBienesEliminados').modal('show');
        cargarBienesEliminados();
    });

    // Cargar bienes eliminados
    function cargarBienesEliminados(page = 1) {
        const search = $('#searchEliminados').val().trim();

        $.ajax({
            url: '{{ route("bien.eliminados") }}',
            method: 'GET',
            data: { search: search, page: page },
            success: function(response) {
                if (response.success) {
                    renderizarTablaEliminados(response.data.data);
                    renderizarPaginacionEliminados(response.data);
                    $('#badgeEliminados').text(response.data.total);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar eliminados:', xhr);
                $('#tablaEliminados').html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error al cargar datos
                        </td>
                    </tr>
                `);
            }
        });
    }

    // Renderizar tabla de eliminados
    function renderizarTablaEliminados(bienes) {
        const tbody = $('#tablaEliminados');
        tbody.empty();

        if (bienes.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                        No hay bienes eliminados
                    </td>
                </tr>
            `);
            return;
        }

        bienes.forEach(function(bien) {
            const fecha = bien.eliminado_en
                ? new Date(bien.eliminado_en).toLocaleString('es-PE')
                : 'N/A';

            const tipoNombre = bien.tipo_bien ? bien.tipo_bien.nombre_tipo : 'N/A';

            tbody.append(`
                <tr>
                    <td><strong>${bien.codigo_patrimonial}</strong></td>
                    <td>${bien.denominacion_bien}</td>
                    <td><span class="badge badge-secondary">${tipoNombre}</span></td>
                    <td><small>${fecha}</small></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-success btn-restaurar"
                                data-id="${bien.id_bien}"
                                title="Restaurar bien">
                            <i class="fas fa-undo"></i> Restaurar
                        </button>
                    </td>
                </tr>
            `);
        });
    }

    // Renderizar paginación de eliminados
    function renderizarPaginacionEliminados(data) {
        const container = $('#paginacionEliminados');
        container.empty();

        if (data.last_page <= 1) return;

        let html = '<ul class="pagination pagination-sm justify-content-center m-0">';

        // Botón anterior
        if (data.current_page > 1) {
            html += `<li class="page-item">
                        <a class="page-link paginar-eliminados" href="#" data-page="${data.current_page - 1}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>`;
        }

        // Páginas
        for (let i = 1; i <= data.last_page; i++) {
            if (i === data.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                html += `<li class="page-item">
                            <a class="page-link paginar-eliminados" href="#" data-page="${i}">${i}</a>
                        </li>`;
            }
        }

        // Botón siguiente
        if (data.current_page < data.last_page) {
            html += `<li class="page-item">
                        <a class="page-link paginar-eliminados" href="#" data-page="${data.current_page + 1}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>`;
        }

        html += '</ul>';
        container.html(html);

        // Eventos de paginación
        $('.paginar-eliminados').on('click', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            cargarBienesEliminados(page);
        });
    }

    // Búsqueda en eliminados
    $('#searchEliminados').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => cargarBienesEliminados(1), 400);
    });

    // Restaurar bien individual
    $(document).on('click', '.btn-restaurar', function() {
        const bienId = $(this).data('id');

        Swal.fire({
            title: '¿Restaurar bien?',
            text: 'El bien volverá a estar activo',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/bien/restaurar/${bienId}`,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Restaurado!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            // Recargar datos
                            cargarBienesEliminados();
                            buscar($('#searchInput').val(), paginaActual);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'No se pudo restaurar el bien'
                        });
                    }
                });
            }
        });
    });


});
</script>
@endsection
