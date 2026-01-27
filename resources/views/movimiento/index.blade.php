@extends('layouts.main')

@section('title', 'Gestión de Movimientos')

@section('content_header')
    <h1>Gestión de Movimientos</h1>
@stop

@section('css')
{{-- ⭐⭐⭐ COLORES POR TIPO DE MOVIMIENTO ⭐⭐⭐ --}}
<style>
/* ==========================================
   COLORES DE FONDO POR TIPO DE MOVIMIENTO
   ========================================== */

/* 🔵 REGISTRO - Celeste claro */
.tipo-registro {
    background-color: #e3f2fd !important;
}

.tipo-registro:hover {
    background-color: #bbdefb !important;
}

/* 🟢 ASIGNACIÓN - Verde claro */
.tipo-asignacion {
    background-color: #e8f5e9 !important;
}

.tipo-asignacion:hover {
    background-color: #c8e6c9 !important;
}

/* 🟡 ACTUALIZACIÓN - Amarillo claro */
.tipo-actualizacion {
    background-color: #fff3e0 !important;
}

.tipo-actualizacion:hover {
    background-color: #ffe0b2 !important;
}

/* 🔴 BAJA - Rojo claro */
.tipo-baja {
    background-color: #ffebee !important;
}

.tipo-baja:hover {
    background-color: #ffcdd2 !important;
}

/* ==========================================
   ⭐ LEYENDA PROFESIONAL MINIMALISTA
   ========================================== */
.leyenda-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-left: 4px solid #007bff;
    border-radius: 8px;
    padding: 15px 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.leyenda-card h6 {
    color: #495057;
    font-weight: 600;
    margin: 0 0 12px 0;
    font-size: 0.9rem;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

.leyenda-items {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
}

.leyenda-item {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.95);
    padding: 8px 15px;
    border-radius: 20px;
    border: 1px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.leyenda-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.12);
    border-color: #007bff;
}

.leyenda-color {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.leyenda-item span {
    font-weight: 600;
    font-size: 0.85rem;
    color: #495057;
}


.color-registro {
    background-color: #e3f2fd;
}

.color-asignacion {
    background-color: #e8f5e9;
}

.color-actualizacion {
    background-color: #fff3e0;
}

.color-baja {
    background-color: #ffebee;
}

/* ==========================================
   ⭐ BADGES DE TIPO CON COLOR
   ========================================== */
.badge-tipo-registro {
    background-color: #2196F3 !important;
    color: white !important;
}

.badge-tipo-asignacion {
    background-color: #4CAF50 !important;
    color: white !important;
}

.badge-tipo-actualizacion {
    background-color: #FF9800 !important;
    color: white !important;
}

.badge-tipo-baja {
    background-color: #F44336 !important;
    color: white !important;
}

/* ==========================================
   ESTILOS GENERALES
   ========================================== */
.fila-movimiento {
    cursor: pointer;
    transition: all 0.2s ease;
}

.fila-movimiento:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

.responsable-text {
    font-size: 0.85rem;
    color: #495057;
}

.responsable-text i {
    color: #6c757d;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn 0.3s ease-in;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.custom-checkbox .custom-control-input:checked ~ .custom-control-label::before {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        {{-- ⭐⭐⭐ LEYENDA PROFESIONAL ⭐⭐⭐ --}}
        <div class="leyenda-card">
            <h6 class="text-center">CÓDIGO DE COLORES POR TIPO DE MOVIMIENTO</h6>
            <div class="leyenda-items">
                <div class="leyenda-item">
                    <div class="leyenda-color color-registro"></div>
                    <span>Registro</span>
                </div>

                <div class="leyenda-item">
                    <div class="leyenda-color color-asignacion"></div>
                    <span>Asignación</span>
                </div>

                <div class="leyenda-item">
                    <div class="leyenda-color color-actualizacion"></div>
                    <span>Actualización</span>
                </div>

                <div class="leyenda-item">
                    <div class="leyenda-color color-baja"></div>
                    <span>Baja</span>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Nuevo Movimiento
                </button>

                <button type="button" class="btn btn-success ml-2" id="btnAsignarSeleccionados" style="display:none;">
                    <i class="fas fa-share-square"></i> Asignar (<span id="contadorAsignar">0</span>)
                </button>

                <button type="button" class="btn btn-danger ml-2" id="btnEliminarSeleccionados" style="display:none;">
                    <i class="fas fa-trash-alt"></i> Eliminar (<span id="contadorSeleccionados">0</span>)
                </button>
            </div>
            <div class="col-md-6">
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

        {{-- Filtros Avanzados --}}
        <div class="row mb-2">
            <div class="col-md-3">
                <select id="filtroTipo" class="form-control form-control-sm">
                    <option value="">Tipo de Movimiento</option>
                    @foreach($tiposMovimiento as $tipo)
                        <option value="{{ $tipo->id_tipo_mvto }}">{{ $tipo->tipo_mvto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filtroBien" class="form-control form-control-sm select2">
                    <option value="">Seleccionar Bien</option>
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
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> Doble click en la fila para editar
            </small>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th width="4%">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="checkAll">
                                <label class="custom-control-label" for="checkAll"></label>
                            </div>
                        </th>
                        <th width="6%" class="sortable" data-column="id" style="cursor:pointer;">
                            ID <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="12%" class="sortable" data-column="fecha" style="cursor:pointer;">
                            FECHA/HORA <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="10%" class="sortable" data-column="responsable" style="cursor:pointer;">
                            RESPONSABLE <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="12%">CÓDIGO BIEN</th>
                        <th width="20%">DENOMINACIÓN</th>
                        <th width="11%" class="sortable" data-column="tipo" style="cursor:pointer;">
                            TIPO MVTO <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="13%">UBICACIÓN</th>
                        <th width="8%">ESTADO</th>
                        <th width="4%">ACCION</th>
                    </tr>
                </thead>
                <tbody id="tablaMovimientos">
                    @forelse($movimientos as $movimiento)
                    {{-- ⭐ CLASE DINÁMICA PARA COLOR DE FILA --}}
                    @php
                        $tipoNormalizado = strtolower(str_replace(['á','é','í','ó','ú','ñ',' '], ['a','e','i','o','u','n','-'], $movimiento->tipoMovimiento->tipo_mvto));

                        // Badge con color según tipo
                        $badgeClass = 'badge-tipo-' . $tipoNormalizado;
                    @endphp

                    <tr id="row-{{ $movimiento->id_movimiento }}"
                        class="fila-movimiento tipo-{{ $tipoNormalizado }}"
                        data-id="{{ $movimiento->id_movimiento }}">

                        <td class="text-center">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input checkbox-item"
                                       id="check-{{ $movimiento->id_movimiento }}"
                                       value="{{ $movimiento->id_movimiento }}"
                                       data-bien-id="{{ $movimiento->idbien }}">
                                <label class="custom-control-label" for="check-{{ $movimiento->id_movimiento }}"></label>
                            </div>
                        </td>

                        <td class="text-center"><strong>{{ $movimiento->id_movimiento }}</strong></td>

                        {{-- ⭐ COLUMNA FECHA/HORA (separada) --}}
                        <td>
                            <strong>{{ \Carbon\Carbon::parse($movimiento->fecha_mvto)->format('d/m/Y') }}</strong><br>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($movimiento->fecha_mvto)->format('H:i:s') }}</small>
                        </td>

                        {{-- ⭐ COLUMNA RESPONSABLE (separada) --}}
                        <td>
                            <span class="responsable-text">
                                <i class="fas fa-user-circle"></i> {{ $movimiento->usuario->name ?? 'N/A' }}
                            </span>
                        </td>

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

                        {{-- ⭐ BADGE CON COLOR SEGÚN TIPO --}}
                        <td>
                            <span class="badge {{ $badgeClass }}">
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

                        {{-- ⭐ ACCIONES VISIBLES (Ver + Editar + Eliminar) --}}
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-info btn-ver" title="Ver Detalles" data-id="{{ $movimiento->id_movimiento }}">
                                    <i class="fas fa-eye"></i>
                                </button>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No hay movimientos registrados</p>
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
                    Mostrando <strong id="paginaInfo">{{ $movimientos->firstItem() ?? 0 }} - {{ $movimientos->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $movimientos->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks">
                @if($movimientos->hasPages())
                    <ul class="pagination pagination-sm m-0">
                        {{-- Botón anterior --}}
                        @if ($movimientos->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link paginar-inicial" href="#" data-page="{{ $movimientos->currentPage() - 1 }}">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        @endif

                        {{-- Números de página --}}
                        @foreach(range(1, $movimientos->lastPage()) as $page)
                            @if ($page == $movimientos->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @elseif ($page == 1 || $page == $movimientos->lastPage() || abs($page - $movimientos->currentPage()) <= 2)
                                <li class="page-item">
                                    <a class="page-link paginar-inicial" href="#" data-page="{{ $page }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Botón siguiente --}}
                        @if ($movimientos->hasMorePages())
                            <li class="page-item">
                                <a class="page-link paginar-inicial" href="#" data-page="{{ $movimientos->currentPage() + 1 }}">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>

        </div>

        {{-- Sin resultados --}}
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

{{-- ==========================================
     MODAL CREAR MOVIMIENTO
     ========================================== --}}
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

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_mvto">Fecha de Movimiento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_mvto" name="fecha_mvto" required>
                                <span class="text-danger error-fecha_mvto d-block mt-1"></span>
                            </div>
                        </div>

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


                        {{-- ⭐ DOCUMENTO SUSTENTO --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="documento_sustentatorio">
                                    <i class="fas fa-file-invoice"></i> Documento Sustento (Opcional)
                                </label>
                                <select class="form-control" id="documento_sustentatorio" name="documento_sustentatorio">
                                    <option value="">Sin documento</option>
                                    @foreach($documentos as $doc)
                                        <option value="{{ $doc->id_documento }}">
                                            {{ $doc->tipo_documento }} - {{ $doc->numero_documento }} ({{ $doc->fecha_formateada }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Selecciona un documento sustento existente</small>
                                <span class="text-danger error-documento_sustentatorio d-block mt-1"></span>
                            </div>
                        </div>

                        {{-- ⭐ NÚMERO DE DOCUMENTO --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="NumDocto">
                                    <i class="fas fa-hashtag"></i> Número de Documento (Opcional)
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="NumDocto"
                                    name="NumDocto"
                                    placeholder="Ej: DOC-2026-001"
                                    maxlength="20">
                                <small class="text-muted">Número de documento en texto libre</small>
                                <span class="text-danger error-NumDocto d-block mt-1"></span>
                            </div>
                        </div>


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

{{-- ==========================================
     MODAL VER DETALLES
     ========================================== --}}
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

                            <dt class="col-sm-5">Responsable:</dt>
                            <dd class="col-sm-7" id="ver-usuario">-</dd>

                            <dt class="col-sm-5">Doc. Sustento:</dt>
                            <dd class="col-sm-7" id="ver-documento">-</dd>

                            <dt class="col-sm-5">Nro. Documento:</dt>
                            <dd class="col-sm-7" id="ver-numdoc">-</dd>

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

{{-- ==========================================
     MODAL EDITAR MOVIMIENTO
     ========================================== --}}
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

                        {{-- ⭐ DOCUMENTO SUSTENTO --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_documento_sustentatorio">
                                    <i class="fas fa-file-invoice"></i> Documento Sustento
                                </label>
                                <select class="form-control" id="edit_documento_sustentatorio" name="documento_sustentatorio">
                                    <option value="">Sin documento</option>
                                    @foreach($documentos as $doc)
                                        <option value="{{ $doc->id_documento }}">
                                            {{ $doc->tipo_documento }} - {{ $doc->numero_documento }} ({{ $doc->fecha_formateada }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-edit-documento_sustentatorio d-block mt-1"></span>
                            </div>
                        </div>

                        {{-- ⭐ NÚMERO DE DOCUMENTO --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_NumDocto">
                                    <i class="fas fa-hashtag"></i> Número de Documento
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="edit_NumDocto"
                                    name="NumDocto"
                                    maxlength="20">
                                <span class="text-danger error-edit-NumDocto d-block mt-1"></span>
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

{{-- ==========================================
     MODAL ASIGNACIÓN MASIVA
     ========================================== --}}
<div class="modal fade" id="modalAsignarMasivo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-share-square"></i> Asignación Masiva de Movimientos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formAsignarMasivo">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Bienes seleccionados: <span id="cantidadBienesSeleccionados">0</span></strong>
                        <ul id="listaBienesSeleccionados" class="mt-2 mb-0" style="max-height: 150px; overflow-y: auto;"></ul>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asignar_tipo_mvto">Tipo de Movimiento <span class="text-danger">*</span></label>
                                <select class="form-control" id="asignar_tipo_mvto" name="tipo_mvto" required>
                                    <option value="">Seleccione tipo</option>
                                    @foreach($tiposMovimiento as $tipo)
                                        <option value="{{ $tipo->id_tipo_mvto }}">{{ $tipo->tipo_mvto }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-asignar-tipo_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asignar_fecha_mvto">Fecha de Movimiento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="asignar_fecha_mvto" name="fecha_mvto" required>
                                <span class="text-danger error-asignar-fecha_mvto d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asignar_idubicacion">Ubicación</label>
                                <select class="form-control" id="asignar_idubicacion" name="idubicacion">
                                    <option value="">Sin ubicación</option>
                                    @foreach($ubicaciones as $ubicacion)
                                        <option value="{{ $ubicacion->id_ubicacion }}">
                                            {{ $ubicacion->ubicacion_completa }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-asignar-idubicacion d-block mt-1"></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asignar_id_estado_conservacion_bien">Estado de Conservación</label>
                                <select class="form-control" id="asignar_id_estado_conservacion_bien" name="id_estado_conservacion_bien">
                                    <option value="">Sin estado</option>
                                    @foreach($estadosConservacion as $estado)
                                        <option value="{{ $estado->id_estado }}">{{ $estado->nombre_estado }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-asignar-id_estado_conservacion_bien d-block mt-1"></span>
                            </div>
                        </div>

                        {{-- ⭐ DOCUMENTO SUSTENTO --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asignar_documento_sustentatorio">
                                    <i class="fas fa-file-invoice"></i> Documento Sustento
                                </label>
                                <select class="form-control" id="asignar_documento_sustentatorio" name="documento_sustentatorio">
                                    <option value="">Sin documento</option>
                                    @foreach($documentos as $doc)
                                        <option value="{{ $doc->id_documento }}">
                                            {{ $doc->tipo_documento }} - {{ $doc->numero_documento }} ({{ $doc->fecha_formateada }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-asignar-documento_sustentatorio d-block mt-1"></span>
                            </div>
                        </div>

                        {{-- ⭐ NÚMERO DE DOCUMENTO --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asignar_NumDocto">
                                    <i class="fas fa-hashtag"></i> Número de Documento
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="asignar_NumDocto"
                                    name="NumDocto"
                                    placeholder="Ej: DOC-2026-001"
                                    maxlength="20">
                                <small class="text-muted">Este número se aplicará a todos los movimientos</small>
                                <span class="text-danger error-asignar-NumDocto d-block mt-1"></span>
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="asignar_detalle_tecnico">Detalle Técnico (Opcional)</label>
                                <textarea class="form-control" id="asignar_detalle_tecnico" name="detalle_tecnico" rows="3" maxlength="500" placeholder="Este detalle se aplicará a todos los movimientos seleccionados..."></textarea>
                                <small class="text-muted">Máximo 500 caracteres. Si se deja vacío, se generará automáticamente.</small>
                                <span class="text-danger error-asignar-detalle_tecnico d-block mt-1"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGuardarAsignacion">
                        <i class="fas fa-save"></i> Crear Movimientos
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
        // ⭐ EVENTO PARA PAGINACIÓN INICIAL (CARGA DE PÁGINA)
    $(document).on('click', '.paginar-inicial', function(e) {
        e.preventDefault();
        paginaActual = $(this).data('page');
        buscar($('#searchInput').val().trim(), paginaActual);
        $('html, body').animate({ scrollTop: 0 }, 300);
    });


    // Establecer fecha actual por defecto
    $('#fecha_mvto, #asignar_fecha_mvto').val(new Date().toISOString().split('T')[0]);

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

    // ===============================
    // ⭐ ACTUALIZAR TABLA CON COLUMNAS SEPARADAS Y BADGES DE COLORES
    // ===============================
    function actualizarTabla(movimientos) {
        const tbody = $('#tablaMovimientos');
        tbody.empty();

        if (movimientos.length === 0) return;

        movimientos.forEach(m => {
            const fecha = moment(m.fecha_mvto).format('DD/MM/YYYY');
            const hora = moment(m.fecha_mvto).format('HH:mm:ss');
            const responsable = m.usuario ? m.usuario.name : 'N/A';

            // ⭐ Normalizar nombre del tipo para la clase CSS
            let tipoNormalizado = m.tipo_movimiento.tipo_mvto
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "") // Quitar acentos
                .replace(/\s+/g, '-'); // Espacios a guiones

            // ⭐ Badge con color según tipo
            let badgeClass = 'badge-tipo-' + tipoNormalizado;

            // Ubicación
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

            // Estado
            let estado = '-';
            if (m.estado_conservacion) {
                estado = `<span class="badge badge-success">${m.estado_conservacion.nombre_estado}</span>`;
            }

            const tipoBien = m.bien && m.bien.tipo_bien ? m.bien.tipo_bien.nombre_tipo : '';

            tbody.append(`
                <tr id="row-${m.id_movimiento}" class="fila-movimiento tipo-${tipoNormalizado} fade-in" data-id="${m.id_movimiento}">
                    <td class="text-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input checkbox-item"
                                   id="check-${m.id_movimiento}"
                                   value="${m.id_movimiento}"
                                   data-bien-id="${m.idbien}">
                            <label class="custom-control-label" for="check-${m.id_movimiento}"></label>
                        </div>
                    </td>
                    <td class="text-center"><strong>${m.id_movimiento}</strong></td>
                    <td>
                        <strong>${fecha}</strong><br>
                        <small class="text-muted">${hora}</small>
                    </td>
                    <td>
                        <span class="responsable-text">
                            <i class="fas fa-user-circle"></i> ${responsable}
                        </span>
                    </td>
                    <td><span class="badge badge-info">${m.bien.codigo_patrimonial}</span></td>
                    <td>
                        <strong>${m.bien.denominacion_bien.substring(0, 30)}${m.bien.denominacion_bien.length > 30 ? '...' : ''}</strong><br>
                        <small class="text-muted">${tipoBien}</small>
                    </td>
                    <td><span class="badge ${badgeClass}">${m.tipo_movimiento.tipo_mvto}</span></td>
                    <td><small class="text-muted">${ubicacion}</small></td>
                    <td>${estado}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-info btn-ver" data-id="${m.id_movimiento}" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>

                        </div>
                    </td>
                </tr>
            `);
        });

        $('.checkbox-item').on('change', actualizarBotonesSeleccion);
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
    // ⭐ CHECKBOX: Seleccionar todos y botones
    // ===============================
    $('#checkAll').on('change', function() {
        $('.checkbox-item').prop('checked', $(this).is(':checked'));
        actualizarBotonesSeleccion();
    });

    $(document).on('change', '.checkbox-item', actualizarBotonesSeleccion);

    function actualizarBotonesSeleccion() {
        let seleccionados = $('.checkbox-item:checked').length;

        $('#contadorSeleccionados').text(seleccionados);
        $('#contadorAsignar').text(seleccionados);

        if (seleccionados > 0) {
            $('#btnEliminarSeleccionados').fadeIn();
            $('#btnAsignarSeleccionados').fadeIn();
        } else {
            $('#btnEliminarSeleccionados').fadeOut();
            $('#btnAsignarSeleccionados').fadeOut();
        }
    }

    // ===============================
    // ⭐ ASIGNACIÓN MASIVA
    // ===============================
    $('#btnAsignarSeleccionados').on('click', function() {
        let seleccionados = [];
        let bienesInfo = [];

        $('.checkbox-item:checked').each(function() {
            const movimientoId = $(this).val();
            const bienId = $(this).data('bien-id');
            const fila = $(this).closest('tr');
            const codigoBien = fila.find('td:nth-child(5)').text().trim();
            const denominacion = fila.find('td:nth-child(6) strong').text().trim();

            seleccionados.push(bienId);
            bienesInfo.push({
                id: bienId,
                codigo: codigoBien,
                denominacion: denominacion
            });
        });

        if (seleccionados.length === 0) {
            Swal.fire('Atención', 'Debe seleccionar al menos un movimiento', 'warning');
            return;
        }

        $('#cantidadBienesSeleccionados').text(seleccionados.length);

        let listaBienes = '';
        bienesInfo.forEach(bien => {
            listaBienes += `<li><strong>${bien.codigo}</strong> - ${bien.denominacion}</li>`;
        });
        $('#listaBienesSeleccionados').html(listaBienes);

        $('#formAsignarMasivo')[0].reset();
        $('#asignar_fecha_mvto').val(new Date().toISOString().split('T')[0]);
        $('.error-asignar-tipo_mvto, .error-asignar-fecha_mvto, .error-asignar-idubicacion, .error-asignar-id_estado_conservacion_bien, .error-asignar-detalle_tecnico').text('');

        $('#formAsignarMasivo').data('bienes-ids', seleccionados);

        $('#modalAsignarMasivo').modal('show');
    });

    // ===============================
    // ⭐ SUBMIT ASIGNACIÓN MASIVA
    // ===============================
    $('#formAsignarMasivo').on('submit', function(e) {
        e.preventDefault();

        const bienesIds = $(this).data('bienes-ids');

        if (!bienesIds || bienesIds.length === 0) {
            Swal.fire('Error', 'No hay bienes seleccionados', 'error');
            return;
        }

        const datos = {
            bienes_ids: bienesIds,
            tipo_mvto: $('#asignar_tipo_mvto').val(),
            fecha_mvto: $('#asignar_fecha_mvto').val(),
            idubicacion: $('#asignar_idubicacion').val() || null,
            id_estado_conservacion_bien: $('#asignar_id_estado_conservacion_bien').val() || null,
            detalle_tecnico: $('#asignar_detalle_tecnico').val() || null,
            documento_sustentatorio: $('#asignar_documento_sustentatorio').val() || null, // ⭐ AGREGADO
            NumDocto: $('#asignar_NumDocto').val() || null // ⭐ AGREGADO

        };

        $('#btnGuardarAsignacion').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');

        $.ajax({
            url: '{{ route("movimiento.asignar-masivo") }}',
            method: 'POST',
            data: datos,
            success: function(res) {
                $('#modalAsignarMasivo').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('.checkbox-item').prop('checked', false);
                    $('#checkAll').prop('checked', false);
                    actualizarBotonesSeleccion();
                    buscar($('#searchInput').val(), paginaActual);
                });
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('.error-asignar-tipo_mvto').text(errors.tipo_mvto ? errors.tipo_mvto[0] : '');
                    $('.error-asignar-fecha_mvto').text(errors.fecha_mvto ? errors.fecha_mvto[0] : '');
                    $('.error-asignar-idubicacion').text(errors.idubicacion ? errors.idubicacion[0] : '');
                    $('.error-asignar-id_estado_conservacion_bien').text(errors.id_estado_conservacion_bien ? errors.id_estado_conservacion_bien[0] : '');
                    $('.error-asignar-detalle_tecnico').text(errors.detalle_tecnico ? errors.detalle_tecnico[0] : '');
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error al crear movimientos', 'error');
                }
            },
            complete: function() {
                $('#btnGuardarAsignacion').prop('disabled', false).html('<i class="fas fa-save"></i> Crear Movimientos');
            }
        });
    });

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
            }).then(() => {
                $('.checkbox-item').prop('checked', false);
                $('#checkAll').prop('checked', false);
                actualizarBotonesSeleccion();
                buscar($('#searchInput').val(), paginaActual);
            });
        });
    }

    // ===============================
    // ⭐ DOBLE CLICK PARA EDITAR
    // ===============================
    $(document).on('dblclick', '.fila-movimiento', function() {
        const id = $(this).data('id');
        editarMovimiento(id);
    });

    // ===============================
    // ⭐ BOTÓN EDITAR (Lápiz)
    // ===============================
    $(document).on('click', '.btn-editar', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        editarMovimiento(id);
    });

    // ===============================
    // VER DETALLES (Botón ojo)
    // ===============================
    $(document).on('click', '.btn-ver', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        verDetalle(id);
    });

    function verDetalle(id) {
        $.get(`/movimiento/${id}`, function(res) {
            if (res.success) {
                const m = res.data;

                $('#ver-id').text(m.id_movimiento);
                $('#ver-codigo').text(m.bien.codigo_patrimonial);
                $('#ver-denominacion').text(m.bien.denominacion_bien);
                $('#ver-tipo').text(m.tipo_movimiento.tipo_mvto);
                $('#ver-fecha').text(moment(m.fecha_mvto).format('DD/MM/YYYY HH:mm:ss'));
                $('#ver-ubicacion').text(m.ubicacion ?
                    `${m.ubicacion.nombre_sede || ''} ${m.ubicacion.ambiente ? '- ' + m.ubicacion.ambiente : ''}`
                    : '-');
                $('#ver-estado').text(m.estado_conservacion ? m.estado_conservacion.nombre_estado : '-');
                $('#ver-usuario').text(m.usuario ? m.usuario.name : 'N/A');
                $('#ver-created').text(moment(m.created_at).format('DD/MM/YYYY HH:mm:ss'));
                $('#ver-detalle-tecnico').text(m.detalle_tecnico || 'Sin detalle técnico');
                // ⭐ AGREGADO: Documento Sustento y NumDoc
                $('#ver-documento').text(m.documento_sustento ?
                    `${m.documento_sustento.tipo_documento} - ${m.documento_sustento.numero_documento}` : '-');
                $('#ver-numdoc').text(m.NumDocto || '-');

                $('#modalVer').modal('show');
            }
        }).fail(function() {
            Swal.fire('Error', 'No se pudo cargar el movimiento', 'error');
        });
    }

    // ===============================
    // CREAR MOVIMIENTO
    // ===============================
    $('#formCreate').on('submit', function(e) {
        e.preventDefault();

        $('.text-danger').text('');

        const datos = {
            idbien: $('#idbien').val(),
            tipo_mvto: $('#tipo_mvto').val(),
            fecha_mvto: $('#fecha_mvto').val(),
            detalle_tecnico: $('#detalle_tecnico').val(),
            idubicacion: $('#idubicacion').val() || null,
            id_estado_conservacion_bien: $('#id_estado_conservacion_bien').val() || null,
            documento_sustentatorio: $('#documento_sustentatorio').val() || null, // ⭐ AGREGADO
            NumDocto: $('#NumDocto').val() || null // ⭐ AGREGADO
        };

        $('#btnGuardar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("movimiento.store") }}',
            method: 'POST',
            data: datos,
            success: function(res) {
                $('#modalCreate').modal('hide');
                $('#formCreate')[0].reset();

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    buscar($('#searchInput').val(), paginaActual);
                });
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('.error-idbien').text(errors.idbien ? errors.idbien[0] : '');
                    $('.error-tipo_mvto').text(errors.tipo_mvto ? errors.tipo_mvto[0] : '');
                    $('.error-fecha_mvto').text(errors.fecha_mvto ? errors.fecha_mvto[0] : '');
                    $('.error-idubicacion').text(errors.idubicacion ? errors.idubicacion[0] : '');
                    $('.error-id_estado_conservacion_bien').text(errors.id_estado_conservacion_bien ? errors.id_estado_conservacion_bien[0] : '');
                    $('.error-detalle_tecnico').text(errors.detalle_tecnico ? errors.detalle_tecnico[0] : '');
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error al crear', 'error');
                }
            },
            complete: function() {
                $('#btnGuardar').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            }
        });
    });

    // ===============================
    // ⭐ EDITAR MOVIMIENTO
    // ===============================
    function editarMovimiento(id) {
        $.get(`/movimiento/${id}`, function(res) {
            if (res.success) {
                const m = res.data;

                $('#edit_id').val(m.id_movimiento);
                $('#edit-id-display').text(m.id_movimiento);
                $('#edit_idbien').val(m.idbien);
                $('#edit_tipo_mvto').val(m.tipo_mvto);
                $('#edit_fecha_mvto').val(moment(m.fecha_mvto).format('YYYY-MM-DD'));
                $('#edit_idubicacion').val(m.idubicacion || '');
                $('#edit_id_estado_conservacion_bien').val(m.id_estado_conservacion_bien || '');
                $('#edit_detalle_tecnico').val(m.detalle_tecnico || '');
                $('#edit_documento_sustentatorio').val(m.documento_sustentatorio || ''); // ⭐ AGREGADO
                $('#edit_NumDocto').val(m.NumDocto || ''); // ⭐ AGREGADO

                $('.text-danger').text('');

                $('#modalEdit').modal('show');
            }
        }).fail(function() {
            Swal.fire('Error', 'No se pudo cargar el movimiento', 'error');
        });
    }

    // ===============================
    // ACTUALIZAR MOVIMIENTO
    // ===============================
    $('#formEdit').on('submit', function(e) {
        e.preventDefault();

        const id = $('#edit_id').val();

        $('.text-danger').text('');

        const datos = {
            idbien: $('#edit_idbien').val(),
            tipo_mvto: $('#edit_tipo_mvto').val(),
            fecha_mvto: $('#edit_fecha_mvto').val(),
            idubicacion: $('#edit_idubicacion').val() || null,
            id_estado_conservacion_bien: $('#edit_id_estado_conservacion_bien').val() || null,
            detalle_tecnico: $('#edit_detalle_tecnico').val() || null,
            documento_sustentatorio: $('#edit_documento_sustentatorio').val() || null, // ⭐ AGREGADO
            NumDocto: $('#edit_NumDocto').val() || null // ⭐ AGREGADO

        };

        $('#btnActualizar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        $.ajax({
            url: `/movimiento/${id}`,
            method: 'PUT',
            data: datos,
            success: function(res) {
                $('#modalEdit').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    buscar($('#searchInput').val(), paginaActual);
                });
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('.error-edit-idbien').text(errors.idbien ? errors.idbien[0] : '');
                    $('.error-edit-tipo_mvto').text(errors.tipo_mvto ? errors.tipo_mvto[0] : '');
                    $('.error-edit-fecha_mvto').text(errors.fecha_mvto ? errors.fecha_mvto[0] : '');
                    $('.error-edit-idubicacion').text(errors.idubicacion ? errors.idubicacion[0] : '');
                    $('.error-edit-id_estado_conservacion_bien').text(errors.id_estado_conservacion_bien ? errors.id_estado_conservacion_bien[0] : '');
                    $('.error-edit-detalle_tecnico').text(errors.detalle_tecnico ? errors.detalle_tecnico[0] : '');
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Error al actualizar', 'error');
                }
            },
            complete: function() {
                $('#btnActualizar').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');
            }
        });
    });

    // ===============================
    // ⭐ ELIMINAR INDIVIDUAL
    // ===============================
    $(document).on('click', '.btn-eliminar', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar este movimiento?',
            text: "Esta acción no se puede revertir",
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
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            buscar($('#searchInput').val(), paginaActual);
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al eliminar', 'error');
                    }
                });
            }
        });
    });

    // ===============================
    // LIMPIAR FORMULARIOS AL CERRAR MODALES
    // ===============================
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('.text-danger').text('');
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        $('.text-danger').text('');
    });

    $('#modalAsignarMasivo').on('hidden.bs.modal', function() {
        $('#formAsignarMasivo')[0].reset();
        $('.text-danger').text('');
    });
});
</script>
@stop
