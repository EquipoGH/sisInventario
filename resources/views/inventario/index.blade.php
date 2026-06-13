@extends('layouts.main')

@section('title', 'Gestión de Inventarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="font-weight-bold text-dark">
                <i class="fas fa-clipboard-list text-primary mr-2"></i> Gestión de Inventarios
            </h1>
            <p class="text-muted mb-0">Supervise y gestione los ciclos de auditoría patrimonial</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(Auth::user()->esAdmin())
                @php $hayActivo = ($estadisticas['pendiente'] ?? 0) > 0 || ($estadisticas['en_proceso'] ?? 0) > 0; @endphp
                @if(!$hayActivo)
                    <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalCreate">
                        <i class="fas fa-plus-circle mr-1"></i> Nuevo Inventario
                    </button>
                @endif
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="row">
    {{-- RESUMEN DE ESTADOS (KPIs) --}}
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 animate-up" style="border-radius: 15px; border-left: 4px solid #007bff !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small font-weight-bold">TOTAL CICLOS</div>
                                <div class="h3 font-weight-bold mb-0 text-dark" id="kpiTotal">{{ $total }}</div>
                            </div>
                            <div class="bg-primary-soft rounded-circle p-2 text-primary" style="background: rgba(0,123,255,0.1);">
                                <i class="fas fa-folder-open fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 animate-up" style="border-radius: 15px; border-left: 4px solid #ffc107 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-warning small font-weight-bold text-uppercase">Pendientes</div>
                                <div class="h3 font-weight-bold mb-0 text-dark" id="kpiPendientes">{{ $estadisticas['pendiente'] ?? 0 }}</div>
                            </div>
                            <div class="bg-warning-soft rounded-circle p-2 text-warning" style="background: rgba(255,193,7,0.1);">
                                <i class="fas fa-clock fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 animate-up" style="border-radius: 15px; border-left: 4px solid #17a2b8 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-info small font-weight-bold text-uppercase">En Proceso</div>
                                <div class="h3 font-weight-bold mb-0 text-dark" id="kpiProceso">{{ $estadisticas['en_proceso'] ?? 0 }}</div>
                            </div>
                            <div class="bg-info-soft rounded-circle p-2 text-info" style="background: rgba(23,162,184,0.1);">
                                <i class="fas fa-sync fa-spin fa-lg" style="animation-duration: 3s;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 animate-up" style="border-radius: 15px; border-left: 4px solid #28a745 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-success small font-weight-bold text-uppercase">Cerrados</div>
                                <div class="h3 font-weight-bold mb-0 text-dark" id="kpiCerrados">{{ $estadisticas['cerrado'] ?? 0 }}</div>
                            </div>
                            <div class="bg-success-soft rounded-circle p-2 text-success" style="background: rgba(40,167,69,0.1);">
                                <i class="fas fa-check-double fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow border-0" style="border-radius: 15px;">
            <div class="card-header bg-white py-3 border-0">
                <div class="row align-items-center">
                    <div class="col-md-6 d-flex align-items-center gap-3">
                        <select id="filtroEstado" class="form-control form-control-sm shadow-sm" style="width: 150px; border-radius: 50px;">
                            <option value="ALL">Todos los Estados</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="cerrado">Cerrados</option>
                            <option value="anulado">Anulados</option>
                        </select>
                        <select id="perPage" class="form-control form-control-sm shadow-sm ml-2" style="width: 100px; border-radius: 50px;">
                            @foreach([10,20,50,100] as $n)
                                <option value="{{ $n }}" @selected((int)request('per_page', 10) === $n)>{{ $n }} filas</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden; border: 1px solid #eee;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" id="searchInput" class="form-control border-0" placeholder="Buscar por código, tipo o responsable...">
                            <div class="input-group-append">
                                <button class="btn btn-white border-0" id="btnLimpiar"><i class="fas fa-times text-muted"></i></button>
                            </div>
                        </div>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tablaInventarios">
                <thead class="thead-dark">
                    <tr>
                        <th width="5%" class="text-center"><input type="checkbox" id="checkAll"></th>
                        <th width="12%" class="sortable" data-column="codigo">
                            Código <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="12%" class="sortable" data-column="fecha">
                            Fecha Inicio <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="12%">Fecha Fin</th>
                        <th width="12%">Tipo</th>
                        <th width="12%">Alcance</th>
                        <th width="12%">Estado</th>
                        <th width="18%">Responsable</th>
                        <th width="10%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    @forelse($inventarios as $inventario)
                    <tr id="row-{{ $inventario->id_inventario }}" class="{{ Auth::user()->esAdmin() && $inventario->puedeEditarse() ? 'editable-row' : '' }}" data-id="{{ $inventario->id_inventario }}">
                        <td class="align-middle text-center">
                            @if(Auth::user()->esAdmin() && $inventario->puedeEditarse())
                                <input type="checkbox" class="checkbox-item" value="{{ $inventario->id_inventario }}">
                            @endif
                        </td>
                        <td class="align-middle text-center">
                            <span class="badge badge-light border text-dark py-2 px-3 shadow-xs" style="font-size: 0.85rem; font-weight: 800; border-radius: 8px;">
                                {{ $inventario->codigoinventario }}
                            </span>
                        </td>
                        <td class="align-middle text-center">
                            <div class="small text-muted mb-1"><i class="fas fa-calendar-check mr-1 text-primary"></i> {{ $inventario->fecha_inicio ? $inventario->fecha_inicio->format('d/m/Y') : '-' }}</div>
                        </td>
                        <td class="align-middle text-center">
                            <div class="small text-muted">
                                @if($inventario->fecha_fin)
                                    <i class="fas fa-calendar-times mr-1 text-danger"></i> {{ $inventario->fecha_fin->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-soft rounded p-1 mr-2 text-primary" style="font-size: 0.7rem;">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <span class="text-uppercase font-weight-bold" style="font-size: 0.7rem; color: #495057;">{{ $inventario->tipoinventario ?: '-' }}</span>
                            </div>
                        </td>
                        <td class="align-middle text-center">{!! $inventario->getAlcanceBadge() !!}</td>
                        <td class="align-middle text-center">{!! $inventario->getBadgeEstado() !!}</td>
                        <td class="align-middle">
                            <div class="d-flex align-items-center">
                                @php
                                    $respName = $inventario->responsablePersona ? $inventario->responsablePersona->nombre_responsable . ' ' . $inventario->responsablePersona->apellidos_responsable : '-';
                                @endphp
                                @if($respName !== '-')
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($respName) }}&background=random&size=30" class="rounded-circle mr-2 shadow-xs">
                                @endif
                                <span class="font-weight-600 text-dark" style="font-size: 0.8rem;">{{ $respName }}</span>
                            </div>
                        </td>
                        <td class="align-middle text-center">
                            <div class="btn-group shadow-xs rounded-pill overflow-hidden">
                                <a href="{{ route('inventario.show', $inventario->id_inventario) }}" class="btn btn-info btn-sm" title="Ver / Gestionar Bienes">
                                    <i class="fas fa-clipboard-check mr-1"></i> Entrar
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="filaVacia">
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary"></i>
                            <h5>No hay inventarios registrados</h5>
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
                    Mostrando <strong id="paginaInfo">{{ $inventarios->firstItem() ?? 0 }} - {{ $inventarios->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $inventarios->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks">
                {{-- La paginación inicial se carga por JS o se renderiza, la haremos por JS --}}
            </div>
        </div>

        {{-- SIN RESULTADOS --}}
        <div id="noResultados" class="text-center py-4" style="display:none;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
            <h5>No se encontraron resultados</h5>
            <p class="text-muted">No hay inventarios que coincidan con "<strong id="terminoBuscado"></strong>"</p>
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
                    <i class="fas fa-plus-circle"></i> Nuevo Inventario
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCreate">
                @csrf
                <div class="modal-body p-0">
                    {{-- INDICADOR DE PASOS --}}
                    <div class="step-indicator bg-light border-bottom p-3 d-flex justify-content-around">
                        <div class="step-item active" data-step="1">
                            <span class="step-number">1</span>
                            <span class="step-label">Básico</span>
                        </div>
                        <div class="step-item" data-step="2">
                            <span class="step-number">2</span>
                            <span class="step-label">Alcance</span>
                        </div>
                        <div class="step-item" data-step="3">
                            <span class="step-number">3</span>
                            <span class="step-label">Firma</span>
                        </div>
                    </div>

                    <div class="p-4">
                        {{-- PASO 1: BÁSICO --}}
                        <div class="step-content" id="step-1">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-control-lg" required value="{{ date('Y-m-d') }}">
                                    <span class="text-danger error-fecha_inicio"></span>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="tipoinventario">Tipo de Inventario <span class="text-danger">*</span></label>
                                    <select name="tipoinventario" id="tipoinventario" class="form-control form-control-lg" required>
                                        <option value="">Seleccione un tipo...</option>
                                        <option value="Inventario Físico Anual">Inventario Físico Anual (Alcance General)</option>
                                        <option value="Inventario por Cambio de Personal">Inventario por Cambio de Personal (Por Responsable)</option>
                                        <option value="Inventario de Verificación">Inventario de Verificación (Por Ubicación / Área)</option>
                                        <option value="Inventario de Baja">Inventario de Baja (Bienes Inactivos)</option>
                                        <option value="Inventario Sorpresa">Inventario Sorpresa (Auditoría Libre)</option>
                                    </select>
                                    <span class="text-danger error-tipoinventario"></span>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i> Complete la información básica para continuar con la definición del alcance.
                            </div>
                        </div>

                        {{-- PASO 2: ALCANCE --}}
                        <div class="step-content" id="step-2" style="display:none;">
                            <label class="d-block mb-3 text-muted scope-main-label">¿Qué bienes desea auditar en este ciclo?</label>
                            
                            <div class="row scope-cards">
                                <div class="col-md-4">
                                    <div class="scope-card active" data-value="responsable">
                                        <input type="radio" name="alcance_tipo" value="responsable" checked class="d-none">
                                        <div class="scope-icon"><i class="fas fa-user-check"></i></div>
                                        <div class="scope-title">Por Responsable</div>
                                        <div class="scope-desc">Bienes asignados legalmente a una persona.</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="scope-card" data-value="ubicacion">
                                        <input type="radio" name="alcance_tipo" value="ubicacion" class="d-none">
                                        <div class="scope-icon"><i class="fas fa-map-marked-alt"></i></div>
                                        <div class="scope-title">Por Ubicación</div>
                                        <div class="scope-desc">Bienes ubicados físicamente en un área.</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="scope-card" data-value="general">
                                        <input type="radio" name="alcance_tipo" value="general" class="d-none">
                                        <div class="scope-icon"><i class="fas fa-city"></i></div>
                                        <div class="scope-title">General</div>
                                        <div class="scope-desc">Todos los bienes activos de la sede.</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Filtros dinámicos según elección --}}
                            <div class="mt-4 p-3 bg-light rounded border">
                                <div id="div_filter_responsable" class="alcance-filter">
                                    <label>Seleccione el Responsable a Auditar <span class="text-danger">*</span></label>
                                    <select name="responsable_alcance" id="responsable_alcance" class="form-control select2-scope" style="width: 100%;">
                                        <option value="">-- Buscar Responsable --</option>
                                        @foreach($responsables as $resp)
                                            <option value="{{ $resp->dni_responsable }}">{{ $resp->nombre_responsable }} {{ $resp->apellidos_responsable }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div id="div_filter_ubicacion" class="alcance-filter" style="display:none;">
                                    <label>Seleccione el Área <span class="text-danger">*</span></label>
                                    <select name="id_area" id="id_area_alcance" class="form-control select2-scope" style="width: 100%;">
                                        <option value="">-- Buscar Área --</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
                                        @endforeach
                                    </select>

                                    <div class="mt-3" id="div_select_ubicacion" style="display:none;">
                                        <label>Seleccione la Ubicación a Auditar <span class="text-danger">*</span></label>
                                        <select name="id_ubicacion" id="id_ubicacion_alcance" class="form-control select2-scope" style="width: 100%;">
                                            <option value="">-- Seleccione Ubicación --</option>
                                        </select>
                                    </div>
                                </div>

                                <div id="div_filter_general" class="alcance-filter" style="display:none;">
                                    <div class="d-flex align-items-center text-warning">
                                        <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                                        <div>
                                            <strong>Alcance Total:</strong> Se incluirán todos los bienes activos sin excepción.
                                        </div>
                                    </div>
                                </div>

                                {{-- ESTIMADOR DINÁMICO --}}
                                <div id="estimation_box" class="mt-3 text-center" style="display:none;">
                                    <hr>
                                    <div class="text-primary font-weight-bold">
                                        <i class="fas fa-calculator mr-2"></i> <span id="estimation_text">Calculando...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PASO 3: FIRMA --}}
                        <div class="step-content" id="step-3" style="display:none;">
                            <div class="form-group mb-4">
                                <label for="responsable">Autoridad que Suscribe (Firma del Acta) <span class="text-danger">*</span></label>
                                <select name="responsable" id="responsable" class="form-control select2" style="width: 100%;" required>
                                    <option value="">Seleccione quién firmará...</option>
                                    @foreach($responsables as $resp)
                                        <option value="{{ $resp->dni_responsable }}">{{ $resp->nombre_responsable }} {{ $resp->apellidos_responsable }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="resp_help_text">Esta persona aparecerá como el responsable administrativo en el documento final.</small>
                            </div>

                            <div class="form-group">
                                <label for="observacion">Observaciones / Motivo Especial</label>
                                <textarea name="observacion" id="observacion" class="form-control" rows="3" placeholder="Opcional: detalles sobre esta auditoría..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary mr-auto" id="btnPrev" style="display:none;">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-primary" id="btnNext">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGuardar" style="display:none;">
                        <i class="fas fa-save"></i> Crear Inventario
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- ========================= MODAL EDITAR (PREMIUM) ========================= --}}
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-white border-0 py-4 px-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-soft rounded-circle p-2 mr-3 text-primary" style="background: rgba(0,123,255,0.1); width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-edit fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold text-dark mb-0">Editar Inventario</h5>
                        <small class="text-muted">Actualice la información administrativa del ciclo</small>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                </button>
            </div>
            
            <form id="formEdit">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id">
                
                <div class="modal-body bg-light-soft px-4 py-3" style="background-color: #f8f9fb;">
                    <!-- Card de Información del Inventario -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border-left: 5px solid #007bff !important;">
                        <div class="card-body py-3">
                            <div class="row align-items-center text-center text-md-left">
                                <div class="col-md-3 border-right">
                                    <div class="small text-muted text-uppercase font-weight-bold mb-1">CÓDIGO</div>
                                    <div class="h5 font-weight-bold text-primary mb-0" id="lbl_codigo_edit_pro">-</div>
                                </div>
                                <div class="col-md-4 border-right px-4">
                                    <div class="small text-muted text-uppercase font-weight-bold mb-1">ALCANCE ACTUAL</div>
                                    <div class="h6 font-weight-bold text-dark mb-0" id="lbl_alcance_edit_pro">-</div>
                                </div>
                                <div class="col-md-5 px-4 text-md-right">
                                    <div class="badge badge-info-soft py-2 px-3 text-info" style="background: rgba(23,162,184,0.1); border-radius: 50px; font-weight: 800; font-size: 0.7rem;">
                                        <i class="fas fa-info-circle mr-1"></i> El alcance no es editable
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Fecha de Inicio -->
                        <div class="col-md-6 mb-4">
                            <label class="font-weight-bold text-muted small text-uppercase mb-2"><i class="fas fa-calendar-alt mr-1"></i> Fecha de Inicio *</label>
                            <input type="date" name="fecha_inicio" id="edit_fecha_inicio" class="form-control shadow-sm border-0" style="border-radius: 10px; height: 45px;" required>
                            <span class="text-danger error-edit-fecha_inicio small"></span>
                        </div>

                        <!-- Tipo de Inventario -->
                        <div class="col-md-6 mb-4">
                            <label class="font-weight-bold text-muted small text-uppercase mb-2"><i class="fas fa-tags mr-1"></i> Tipo de Inventario *</label>
                            <select name="tipoinventario" id="edit_tipoinventario" class="form-control shadow-sm border-0" style="border-radius: 10px; height: 45px;" required>
                                <option value="Inventario Físico Anual">Inventario Físico Anual (Alcance General)</option>
                                <option value="Inventario por Cambio de Personal">Inventario por Cambio de Personal (Por Responsable)</option>
                                <option value="Inventario de Verificación">Inventario de Verificación (Por Ubicación / Área)</option>
                                <option value="Inventario de Baja">Inventario de Baja (Bienes Inactivos)</option>
                                <option value="Inventario Sorpresa">Inventario Sorpresa (Auditoría Libre)</option>
                            </select>
                            <span class="text-danger error-edit-tipoinventario small"></span>
                        </div>

                        <!-- Autoridad Responsable -->
                        <div class="col-12 mb-4">
                            <label class="font-weight-bold text-muted small text-uppercase mb-2"><i class="fas fa-user-tie mr-1"></i> Autoridad que Suscribe (Firma) *</label>
                            <select name="responsable" id="edit_responsable" class="form-control select2 shadow-sm" style="width: 100%;" required>
                                @foreach($responsables as $resp)
                                    <option value="{{ $resp->dni_responsable }}">{{ $resp->nombre_responsable }} {{ $resp->apellidos_responsable }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-edit-responsable small"></span>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-12 mb-2">
                            <label class="font-weight-bold text-muted small text-uppercase mb-2"><i class="fas fa-comment-alt mr-1"></i> Observaciones / Motivo</label>
                            <textarea name="observacion" id="edit_observacion" class="form-control shadow-sm border-0" rows="3" style="border-radius: 12px;" placeholder="Detalles sobre este ciclo..."></textarea>
                            <span class="text-danger error-edit-observacion small"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 py-4 bg-white">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnActualizar" class="btn btn-primary rounded-pill px-5 shadow-sm font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .animate-up { transition: transform 0.3s ease; }
    .animate-up:hover { transform: translateY(-5px); }
    .bg-primary-soft { background: rgba(0,123,255,0.1) !important; }
    .bg-warning-soft { background: rgba(255,193,7,0.1) !important; }
    .bg-info-soft { background: rgba(23,162,184,0.1) !important; }
    .bg-success-soft { background: rgba(40,167,69,0.1) !important; }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .font-weight-600 { font-weight: 600; }
    .gap-2 { gap: 0.5rem; }
    .gap-3 { gap: 1rem; }
    
    /* Table Styling Pro */
    #tablaInventarios thead th {
        background-color: #f8f9fa;
        color: #334155;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-top: 0;
        border-bottom: 2px solid #e2e8f0;
    }
    #tablaInventarios tbody tr { transition: all 0.2s; cursor: pointer; }
    #tablaInventarios tbody tr:hover { background-color: #f1f5f9; }
    
    /* Custom Scrollbar */
    .table-responsive::-webkit-scrollbar { height: 8px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const esAdmin = {{ Auth::user()->esAdmin() ? 'true' : 'false' }};
</script>

<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Inicializar Select2
    $('.select2, .select2-scope').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalCreate')
    });
    
    // Configurar Select2 para modalEdit dinámicamente cuando se abre
    $('#modalEdit').on('shown.bs.modal', function () {
        $('#edit_responsable').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#modalEdit')
        });
    });
    
    // --- LÓGICA DE ALCANCE WIZARD ---
    let currentStep = 1;

    function showStep(step) {
        $('.step-content').hide();
        $(`#step-${step}`).fadeIn();
        
        $('.step-item').removeClass('active');
        $(`.step-item[data-step="${step}"]`).addClass('active');

        $('#btnPrev').toggle(step > 1);
        $('#btnNext').toggle(step < 3);
        $('#btnGuardar').toggle(step === 3);
    }

    $('#btnNext').on('click', function() {
        if (currentStep === 1) {
            if (!$('#tipoinventario').val()) {
                $('.error-tipoinventario').text('Seleccione un tipo de inventario');
                return;
            }
            
            // Auto-seleccionar y bloquear alcance según tipo de inventario
            const tipoInv = $('#tipoinventario').val();
            $('.scope-card').removeClass('disabled-scope').css({ opacity: '1', cursor: 'pointer' }); // Reset

            let autoClick = null;
            let hideCards = true;

            if (tipoInv === 'Inventario Físico Anual' || tipoInv === 'Inventario de Baja') {
                autoClick = 'general';
            } else if (tipoInv === 'Inventario por Cambio de Personal') {
                autoClick = 'responsable';
            } else if (tipoInv === 'Inventario de Verificación') {
                autoClick = 'ubicacion';
            } else if (tipoInv === 'Inventario Sorpresa') {
                // Auditoría Libre: Mostrar las tarjetas para que elijan libremente
                hideCards = false;
                if (!$('.scope-card.active').length) autoClick = 'general';
            }

            if (hideCards) {
                $('.scope-cards').hide();
                $('.scope-main-label').text('Configuración del Alcance Predefinido:');
            } else {
                $('.scope-cards').show();
                $('.scope-main-label').text('¿Qué bienes desea auditar en este ciclo?');
            }

            if (autoClick) {
                // Removemos el disabled momentaneamente para poder hacer click
                $('.scope-card').removeClass('disabled-scope');
                $('.scope-card[data-value="' + autoClick + '"]').click();
                
                // Si están ocultas, no importa el disabled, pero por seguridad:
                if (hideCards) {
                    $('.scope-card').addClass('disabled-scope');
                }
            }
        }
        if (currentStep === 2) {
            const tipo = $('input[name="alcance_tipo"]:checked').val();
            if (tipo === 'ubicacion') {
                if (!$('#id_area_alcance').val()) {
                    Swal.fire('Atención', 'Debe seleccionar un área para este alcance.', 'warning');
                    return;
                }
                if (!$('#id_ubicacion_alcance').val()) {
                    Swal.fire('Atención', 'Debe seleccionar una ubicación para este alcance.', 'warning');
                    return;
                }
            }
            if (tipo === 'responsable' && !$('#responsable_alcance').val()) {
                Swal.fire('Atención', 'Debe seleccionar un responsable para este alcance.', 'warning');
                return;
            }
        }
        currentStep++;
        showStep(currentStep);
    });

    $('#btnPrev').on('click', function() {
        currentStep--;
        showStep(currentStep);
    });

    // --- CARDS DE ALCANCE ---
    $('.scope-card').on('click', function() {
        if ($(this).hasClass('disabled-scope')) {
            Swal.fire({
                icon: 'info',
                title: 'Alcance Predefinido',
                text: 'El alcance está estrictamente bloqueado según el Tipo de Inventario que seleccionaste en el paso anterior para evitar inconsistencias.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        $('.scope-card').removeClass('active');
        $(this).addClass('active');
        $(this).find('input').prop('checked', true).trigger('change');
    });

    $('input[name="alcance_tipo"]').on('change', function() {
        const tipo = $(this).val();
        $('.alcance-filter').hide();
        
        if (tipo === 'responsable') $('#div_filter_responsable').fadeIn();
        else if (tipo === 'ubicacion') $('#div_filter_ubicacion').fadeIn();
        else if (tipo === 'general') $('#div_filter_general').fadeIn();

        pedirEstimacion();
    });

    // Actualizar estimación al cambiar filtros
    $('#responsable_alcance, #id_ubicacion_alcance').on('change', pedirEstimacion);

    function pedirEstimacion() {
        const tipo = $('input[name="alcance_tipo"]:checked').val();
        const area = $('#id_area_alcance').val();
        const ubicacion = $('#id_ubicacion_alcance').val();
        const resp = $('#responsable_alcance').val();

        $('#estimation_box').fadeIn();
        $('#estimation_text').html('<i class="fas fa-spinner fa-spin"></i> Calculando...');

        $.post('{{ route("inventario.estimar-alcance") }}', {
            alcance_tipo: tipo,
            id_area: area,
            id_ubicacion: ubicacion,
            responsable: resp
        }, function(res) {
            $('#estimation_text').text(res.mensaje);
            
            // Si el alcance es por responsable, auto-setearlo para el paso 3 también
            if (tipo === 'responsable' && resp) {
                $('#responsable').val(resp).trigger('change');
            }
        });
    }

    // Al elegir un área, intentar auto-seleccionar al responsable de esa área para la firma
    // Y cargar ubicaciones si es 'ubicacion'
    $('#id_area_alcance').on('change', function() {
        const areaId = $(this).val();
        const tipo = $('input[name="alcance_tipo"]:checked').val();
        
        if (areaId) {
            $.get(`{{ url('areas') }}/${areaId}/responsable`, function(res) {
                if (res.success && res.data) {
                    $('#responsable').val(res.data.dni_responsable).trigger('change');
                }
            });
            
            if (tipo === 'ubicacion') {
                $.get(`{{ url('areas') }}/${areaId}/ubicaciones`, function(res) {
                    if (res.success) {
                        const select = $('#id_ubicacion_alcance');
                        select.empty().append('<option value="">-- Seleccione Ubicación --</option>');
                        res.data.forEach(u => {
                            select.append(`<option value="${u.id_ubicacion}">${u.ambiente} (Piso: ${u.piso_ubicacion})</option>`);
                        });
                        select.trigger('change.select2'); // Actualiza solo el UI de select2
                        $('#div_select_ubicacion').fadeIn();
                    }
                });
            }
        } else {
            $('#div_select_ubicacion').hide();
            $('#id_ubicacion_alcance').empty().append('<option value="">-- Seleccione Ubicación --</option>');
        }
    });

    $('#modalCreate').on('hidden.bs.modal', function() {
        currentStep = 1;
        showStep(1);
        $('#formCreate')[0].reset();
        $('.select2-scope').val('').trigger('change');
        $('.text-danger').text('');
    });

    // ==================== ESTADO GLOBAL ====================
    let paginaActual = 1;
    let ordenActual = { columna: 'fecha', direccion: 'desc' };
    let terminoBusqueda = '';
    let perPage = parseInt($('#perPage').val() || '10', 10);
    let estadoActual = $('#filtroEstado').val() || 'ALL';

    actualizarIconosOrdenamiento();

    actualizarPaginacion({
        current_page: {{ $inventarios->currentPage() }},
        last_page: {{ $inventarios->lastPage() }}
    }, terminoBusqueda);

    // ==================== FILTROS SUPERIORES ====================
    $('#perPage').on('change', function() {
        perPage = parseInt($(this).val() || '10', 10);
        paginaActual = 1;
        buscar(terminoBusqueda, paginaActual);
    });

    $('#filtroEstado').on('change', function() {
        estadoActual = $(this).val();
        paginaActual = 1;
        $('#checkAll').prop('checked', false);
        $('.checkbox-item').prop('checked', false);
        actualizarBotonAnular();
        buscar(terminoBusqueda, paginaActual);
    });

    // ==================== BÚSQUEDA ====================
    let searchTimeout;

    $('#searchInput').on('keyup', function() {
        terminoBusqueda = $(this).val().trim();
        clearTimeout(searchTimeout);
        paginaActual = 1;

        if (terminoBusqueda.length === 0 || terminoBusqueda.length >= 2) {
            searchTimeout = setTimeout(() => buscar(terminoBusqueda, paginaActual), 400);
        }
    });

    function buscar(termino, page = 1) {
        mostrarCargando(true);

        $.ajax({
            url: '{{ route("inventario.index") }}',
            method: 'GET',
            data: {
                search: termino,
                page: page,
                orden: ordenActual.columna,
                direccion: ordenActual.direccion,
                per_page: perPage,
                estado: estadoActual
            },
            dataType: 'json',
            success: function(res) {
                actualizarTabla(res.data);
                actualizarContadores(res);
                actualizarPaginacion(res, termino);
                actualizarAccionesPrincipales(res.estadisticas);
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
                    title: 'Error al buscar datos'
                });
            }
        });
    }

    // ==================== ACTUALIZAR TABLA ====================
    function actualizarTabla(items) {
        const tbody = $('#tablaBody');
        tbody.empty();

        if (items.length === 0) {
            tbody.append(`
                <tr id="filaVacia">
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary"></i>
                        <h5>No hay inventarios registrados</h5>
                    </td>
                </tr>
            `);
            if (esAdmin) $('#checkAll').prop('checked', false).prop('disabled', true);
            actualizarBotonAnular();
            return;
        }

        if (esAdmin) $('#checkAll').prop('disabled', false);

        items.forEach(item => {
            const fechaFin = item.fecha_fin ? item.fecha_fin : '-';
            const fechaInicio = item.fecha_inicio ? item.fecha_inicio : '-';
            const responsable = item.responsable ? item.responsable.nombre : '-';
            
            let rowClass = 'animate__animated animate__fadeIn';
            let checkboxCol = '';
            
            if (esAdmin) {
                if (item.puedeEditarse) {
                    checkboxCol = `<td class="align-middle text-center"><input type="checkbox" class="checkbox-item" value="${item.id_inventario}"></td>`;
                    rowClass += ' editable-row';
                } else {
                    checkboxCol = `<td class="align-middle text-center"></td>`;
                }
            }

            tbody.append(`
                <tr id="row-${item.id_inventario}" class="${rowClass}" data-id="${item.id_inventario}">
                    ${checkboxCol}
                    <td class="align-middle text-center">
                        <span class="badge badge-light border text-dark py-2 px-3 shadow-xs" style="font-size: 0.85rem; font-weight: 800; border-radius: 8px;">
                            ${item.codigoinventario}
                        </span>
                    </td>
                    <td class="align-middle text-center">
                        <div class="small text-muted mb-1"><i class="fas fa-calendar-check mr-1 text-primary"></i> ${fechaInicio}</div>
                    </td>
                    <td class="align-middle text-center">
                        <div class="small text-muted">${fechaFin !== '-' ? `<i class="fas fa-calendar-times mr-1 text-danger"></i> ${fechaFin}` : '-'}</div>
                    </td>
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary-soft rounded p-1 mr-2 text-primary" style="font-size: 0.7rem;">
                                <i class="fas fa-tags"></i>
                            </div>
                            <span class="text-uppercase font-weight-bold" style="font-size: 0.7rem; color: #495057;">${item.tipoinventario || '-'}</span>
                        </div>
                    </td>
                    <td class="align-middle text-center">${item.alcancebadge}</td>
                    <td class="align-middle text-center">${item.badgeestado}</td>
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(responsable)}&background=random&size=30" class="rounded-circle mr-2 shadow-xs">
                            <span class="font-weight-600 text-dark" style="font-size: 0.8rem;">${responsable}</span>
                        </div>
                    </td>
                    <td class="align-middle text-center">
                        <div class="btn-group shadow-xs rounded-pill overflow-hidden">
                            <a href="/inventario/${item.id_inventario}" class="btn btn-info btn-sm" title="Ver / Gestionar Bienes">
                                <i class="fas fa-clipboard-check mr-1"></i> Entrar
                            </a>
                        </div>
                    </td>
                </tr>
            `);
        });

        $('#checkAll').prop('checked', false);
        actualizarBotonAnular();
    }

    function actualizarContadores(res) {
        $('#from').text(res.from || 0);
        $('#to').text(res.to || 0);
        $('#resultadosCount').text(res.resultados);
        $('#totalCount').text(res.total);
        $('#paginaInfo').text((res.from || 0) + ' - ' + (res.to || 0));
    }

    function actualizarAccionesPrincipales(stats) {
        if (!esAdmin) return;
        
        const hayActivo = (stats.pendiente || 0) > 0 || (stats.en_proceso || 0) > 0;
        const contenedor = $('#contenedorAccionPrincipal');
        
        if (hayActivo) {
            contenedor.html(`
                <div class="alert alert-info mb-0 py-1 px-3 border border-info" style="font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Hay un inventario activo. Finalícelo para crear uno nuevo.
                </div>
            `);
        } else {
            contenedor.html(`
                <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Nuevo Inventario
                </button>
            `);
        }
    }

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
                html += `<li class="page-item"><a class="page-link paginar" href="#" data-page="${i}">${i}</a></li>`;
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
            return `<li class="page-item"><a class="page-link paginar" href="#" data-page="${pagina}">${contenido}</a></li>`;
        }
        return `<li class="page-item disabled"><span class="page-link">${contenido}</span></li>`;
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
        $('.sortable .sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');

        if (ordenActual.columna) {
            const iconoActivo = $(`.sortable[data-column="${ordenActual.columna}"] .sort-icon`);
            iconoActivo.removeClass('fa-sort').addClass(ordenActual.direccion === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
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
        $('#tablaInventarios').hide();
        $('#paginacionContainer').hide();
        $('#terminoBuscado').text(termino);
        $('#noResultados').fadeIn();
    }

    function ocultarSinResultados() {
        $('#noResultados').hide();
        $('#tablaInventarios').show();
        $('#paginacionContainer').show();
    }

    $('#btnLimpiar, #btnMostrarTodo').on('click', function() {
        $('#searchInput').val('');
        $('#filtroEstado').val('ALL');
        $('#perPage').val('10');
        
        terminoBusqueda = '';
        estadoActual = 'ALL';
        perPage = 10;
        paginaActual = 1;
        ordenActual = { columna: 'fecha', direccion: 'desc' };
        
        $('#checkAll').prop('checked', false);
        $('.checkbox-item').prop('checked', false);
        actualizarBotonAnular();
        actualizarIconosOrdenamiento();
        
        buscar('', 1);
    });

    // ==================== CHECKBOXES Y ACCIÓN MASIVA ====================
    $('#checkAll').on('change', function() {
        $('.checkbox-item').prop('checked', $(this).is(':checked'));
        actualizarBotonAnular();
    });

    $(document).on('change', '.checkbox-item', function() {
        actualizarBotonAnular();
        const total = $('.checkbox-item').length;
        const checked = $('.checkbox-item:checked').length;
        $('#checkAll').prop('checked', total > 0 && total === checked);
    });

    function actualizarBotonAnular() {
        const seleccionados = $('.checkbox-item:checked');
        const cantidad = seleccionados.length;
        
        $('#contadorSeleccionados').text(cantidad);
        $('#contadorEliminar').text(cantidad);

        if (cantidad > 0) {
            let totalBienes = 0;
            seleccionados.each(function() {
                totalBienes += parseInt($(this).data('bienes')) || 0;
            });

            $('#btnAnularSeleccionados').fadeIn(200);

            if (totalBienes === 0) {
                $('#btnEliminarSeleccionados').fadeIn(200);
            } else {
                $('#btnEliminarSeleccionados').fadeOut(200);
            }
        } else {
            $('#btnAnularSeleccionados').fadeOut(200);
            $('#btnEliminarSeleccionados').fadeOut(200);
        }
    }

    $('#btnEliminarSeleccionados').on('click', function() {
        const ids = $('.checkbox-item:checked').map(function() {
            return $(this).val();
        }).get();

        if (ids.length === 0) return;

        Swal.fire({
            title: `¿Eliminar ${ids.length} inventario(s)?`,
            text: "Se eliminarán permanentemente. Esta acción no se puede revertir.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, Eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("inventario.eliminar-multiples") }}',
                    method: 'POST',
                    data: { ids: ids },
                    success: function(res) {
                        if (res.success) {
                            Toast.fire({ icon: 'success', title: res.message });
                            $('#checkAll').prop('checked', false);
                            actualizarBotonAnular();
                            buscar(terminoBusqueda, paginaActual);
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Error al procesar la solicitud';
                        Toast.fire({ icon: 'error', title: msg });
                    }
                });
            }
        });
    });

    $('#btnAnularSeleccionados').on('click', function() {
        const ids = $('.checkbox-item:checked').map(function() {
            return $(this).val();
        }).get();

        if (ids.length === 0) return;

        Swal.fire({
            title: `¿Anular ${ids.length} inventario(s)?`,
            text: "Los inventarios seleccionados pasarán a estado ANULADO y ya no podrán ser modificados.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-ban"></i> Sí, Anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("inventario.anular-multiples") }}',
                    method: 'POST',
                    data: { ids: ids },
                    success: function(res) {
                        if (res.success) {
                            Toast.fire({ icon: 'success', title: res.message });
                            $('#checkAll').prop('checked', false);
                            actualizarBotonAnular();
                            buscar(terminoBusqueda, paginaActual);
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Error al procesar la solicitud';
                        Toast.fire({ icon: 'error', title: msg });
                    }
                });
            }
        });
    });

    // ==================== CREAR ====================
    $('#formCreate').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text('');

        const btn = $('#btnGuardar');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        // Si es 'responsable', el responsable de firma debe ser el mismo que el del alcance
        const tipo = $('input[name="alcance_tipo"]:checked').val();
        if (tipo === 'responsable') {
            $('#responsable').val($('#responsable_alcance').val());
        }

        $.ajax({
            url: '{{ route("inventario.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Crear Inventario');

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
                    Toast.fire({ icon: 'error', title: 'Error al registrar inventario' });
                }
            }
        });
    });

    // ==================== EDITAR ====================
    $(document).on('dblclick', '.editable-row', function() {
        const id = $(this).data('id');
        
        $.get(`/inventario/${id}/edit`, function(res) {
            $('#edit_id').val(res.id_inventario);
            $('#lbl_codigo_edit_pro').text(res.codigoinventario);
            $('#lbl_alcance_edit_pro').html(res.alcancebadge || 'Alcance General');
            
            $('#edit_fecha_inicio').val(res.fecha_inicio_raw);
            $('#edit_tipoinventario').val(res.tipoinventario);
            $('#edit_responsable').val(res.responsable).trigger('change');
            $('#edit_observacion').val(res.observacion);
            
            $('#modalEdit').modal('show');
        }).fail(function() {
            Toast.fire({ icon: 'error', title: 'Error al cargar datos' });
        });
    });

    $('#formEdit').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text('');

        const id = $('#edit_id').val();
        const btn = $('#btnActualizar');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        $.ajax({
            url: `/inventario/${id}`,
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
                    Toast.fire({ icon: 'error', title: 'Error al actualizar inventario' });
                }
            }
        });
    });



    // ==================== RESET DE MODALES ====================
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('#responsable').val('').trigger('change');
        $('.text-danger').text('');
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        $('.text-danger').text('');
    });

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
});
</script>

<style>
    /* Transiciones suaves */
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    
    /* Hover effects en la tabla */
    .table-hover tbody tr:hover { background-color: rgba(0,0,0,.03); }
    
    /* Indicador de fila editable */
    .editable-row { cursor: pointer; }
    .editable-row:hover { background-color: rgba(0,123,255,0.05) !important; }
    
    /* Iconos de ordenamiento */
    .sortable { cursor: pointer; user-select: none; }
    .sortable:hover { background-color: rgba(0,0,0,.05); }
    
    /* Select2 en modales de bootstrap 4 */
    .select2-container--bootstrap4 .select2-selection {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
</style>
<style>
    /* WIZARD STEPS */
    .step-indicator {
        display: flex;
        align-items: center;
        background: #f8f9fa;
    }
    .step-item {
        text-align: center;
        position: relative;
        flex: 1;
        opacity: 0.4;
        transition: all 0.3s ease;
    }
    .step-item.active {
        opacity: 1;
    }
    .step-number {
        display: inline-block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        border-radius: 50%;
        background: #007bff;
        color: white;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .step-item.active .step-number {
        box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
    }
    .step-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    /* SCOPE CARDS */
    .scope-cards .col-md-4 {
        padding: 5px;
    }
    .scope-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        height: 100%;
        background: white;
    }
    .scope-card:hover {
        border-color: #007bff;
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .scope-card.active {
        border-color: #007bff;
        background-color: rgba(0,123,255,0.05);
    }
    .scope-icon {
        font-size: 2rem;
        color: #6c757d;
        margin-bottom: 10px;
    }
    .scope-card.active .scope-icon {
        color: #007bff;
    }
    .scope-title {
        font-weight: bold;
        margin-bottom: 5px;
        font-size: 0.95rem;
    }
    .scope-desc {
        font-size: 0.75rem;
        color: #6c757d;
        line-height: 1.2;
    }

    /* BADGE PURPLE */
    .badge-purple {
        background-color: #6f42c1;
        color: white;
    }
</style>
@stop
