@extends('layouts.main')

@section('title', 'Detalle de Inventario')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-clipboard-check"></i> Gestión de Bienes - Inventario 
            <span class="text-primary">{{ $inventario->codigoinventario }}</span>
        </h1>
        <a href="{{ route('inventario.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="row">
<div class="row">
    {{-- DASHBOARD PRO --}}
    <div class="col-12 mb-4">
        <div class="card card-outline card-primary shadow border-0" style="border-radius: 15px; overflow: hidden;">
            <div class="card-body p-0">
                <div class="row no-gutters text-center">
                    <div class="col-md-2 col-6 border-right py-3 animate-up">
                        <div class="text-muted small mb-1 font-weight-bold">ESPERADOS</div>
                        <div class="h4 font-weight-bold mb-0 text-primary" id="boxEsperados">0</div>
                        <div class="small text-muted"><i class="fas fa-list-ol opacity-50"></i> Total Alcance</div>
                    </div>
                    <div class="col-md-2 col-6 border-right py-3 animate-up">
                        <div class="text-success small mb-1 font-weight-bold">ENCONTRADOS</div>
                        <div class="h4 font-weight-bold mb-0 text-success" id="boxVerificados">0</div>
                        <div class="small text-muted"><i class="fas fa-check-circle opacity-50"></i> Verificados</div>
                    </div>
                    <div class="col-md-2 col-6 border-right py-3 animate-up">
                        <div class="text-danger small mb-1 font-weight-bold">FALTANTES</div>
                        <div class="h4 font-weight-bold mb-0 text-danger" id="boxFaltantes">0</div>
                        <div class="small text-muted"><i class="fas fa-exclamation-circle opacity-50"></i> Por procesar</div>
                    </div>
                    <div class="col-md-2 col-6 border-right py-3 animate-up">
                        <div class="text-dark small mb-1 font-weight-bold">PERDIDOS</div>
                        <div class="h4 font-weight-bold mb-0 text-dark" id="boxPerdidos">0</div>
                        <div class="small text-muted"><i class="fas fa-times-circle opacity-50"></i> Confirmados</div>
                    </div>
                    <div class="col-md-2 col-6 border-right py-3 animate-up">
                        <div class="text-warning small mb-1 font-weight-bold">INCIDENCIAS</div>
                        <div class="h4 font-weight-bold mb-0 text-warning" id="boxIncidencias">0</div>
                        <div class="small text-muted" id="boxPendientes">Pendientes: 0</div>
                    </div>
                    <div class="col-md-2 col-6 py-3 animate-up bg-light">
                        <div class="text-info small mb-1 font-weight-bold">AVANCE REAL</div>
                        <div class="h4 font-weight-bold mb-0 text-info" id="textProgreso">0%</div>
                        <div class="progress progress-xxs mx-4 mt-2 shadow-sm">
                            <div class="progress-bar bg-info progress-bar-striped progress-bar-animated" id="barProgreso" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white py-2 border-top">
                <div class="row align-items-center">
                    <div class="col-md-7 col-lg-8 small">
                        <i class="fas fa-user-tie mr-1 text-primary"></i> <b>Responsable:</b> {{ $inventario->responsablePersona ? $inventario->responsablePersona->nombre_responsable . ' ' . $inventario->responsablePersona->apellidos_responsable : '-' }}
                        <span class="mx-2 text-muted">|</span>
                        <i class="fas fa-map-marked-alt mr-1 text-primary"></i> <b>Alcance:</b> {!! $inventario->getAlcanceBadge() !!}
                        <span class="mx-2 text-muted">|</span>
                        <i class="fas fa-clipboard-list mr-1 text-primary"></i> <b>Tipo:</b> <span class="badge badge-light border">{{ $inventario->tipoinventario }}</span>
                        <span class="mx-2 text-muted">|</span>
                        <b>Estado:</b> <span id="estado_actual_badge">{!! $inventario->getBadgeEstado() !!}</span>
                    </div>
                    <div class="col-md-5 col-lg-4 d-flex justify-content-md-end align-items-center mt-3 mt-md-0">
                        @if($inventario->estaCerrado())
                            <div class="btn-group shadow-sm" role="group">
                                <a href="{{ route('inventario.acta', $inventario->id_inventario) }}" class="btn btn-primary btn-sm" target="_blank" data-toggle="tooltip" title="Descargar Acta Final en formato PDF">
                                    <i class="fas fa-file-pdf"></i> Acta PDF
                                </a>
                                <a href="{{ route('inventario.excel', $inventario->id_inventario) }}" class="btn btn-success btn-sm" data-toggle="tooltip" title="Descargar reporte analítico para auditoría">
                                    <i class="fas fa-file-excel"></i> Exportar Excel
                                </a>
                                @if(Auth::user()->esAdmin())
                                    <button type="button" id="btnRegularizarUbicaciones" class="btn btn-warning btn-sm text-dark font-weight-bold" data-toggle="tooltip" title="Sincronizar movimientos detectados con la base de datos central">
                                        <i class="fas fa-sync-alt"></i> Regularizar
                                    </button>
                                @endif
                            </div>
                        @elseif($inventario->puedeEditarse() && Auth::user()->esAdmin())
                            <button type="button" id="btnFinalizarInventario" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-check-double"></i> Cerrar Inventario
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- CONTENIDO PRINCIPAL --}}
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2 d-flex justify-content-between align-items-center flex-wrap">
                <ul class="nav nav-tabs border-0" id="tabsInventario">
                    <li class="nav-item"><a class="nav-link text-danger" href="#faltantes" data-toggle="tab"><i class="fas fa-clock mr-1"></i> Faltantes (<span id="tabCountFaltantes">0</span>)</a></li>
                    <li class="nav-item"><a class="nav-link active font-weight-bold" href="#lista" data-toggle="tab"><i class="fas fa-check-double mr-1"></i> Verificados (<span id="tabCountVerificados">0</span>)</a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#sobrantes" data-toggle="tab"><i class="fas fa-sync mr-1"></i> Sobrantes (<span id="tabCountSobrantes">0</span>)</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="#incidencias" data-toggle="tab"><i class="fas fa-bullhorn mr-1"></i> Incidencias (<span id="tabCountIncidencias">0</span>)</a></li>
                </ul>
                <div class="d-flex align-items-center ml-auto mt-2 mt-md-0">
                    <div class="search-container mr-2" style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 10px; top: 10px; color: #adb5bd;"></i>
                        <input type="text" id="inputBuscarTabla" class="form-control form-control-sm pl-4" style="border-radius: 20px; min-width: 200px;" placeholder="Buscar en esta tabla...">
                    </div>
                    @if($inventario->puedeEditarse())
                        <div class="d-flex shadow-sm" style="gap: 10px;">
                            <button class="btn btn-primary" id="btnAgregarBien" data-toggle="modal" data-target="#modalAgregarBien">
                                <i class="fas fa-plus-circle mr-1"></i> Añadir
                            </button>
                            <button class="btn btn-danger" id="btnReportarIncidencia" data-toggle="modal" data-target="#modalIncidencia">
                                <i class="fas fa-bullhorn mr-1"></i> Reportar
                            </button>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    {{-- TAB: VERIFICADOS --}}
                    <div class="active tab-pane fade show" id="lista">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="alert alert-success mb-0 flex-grow-1 mr-3 py-2 px-3 small shadow-xs border">
                                <h5><i class="icon fas fa-check-circle"></i> Bienes Verificados</h5>
                                Lista de bienes que han sido auditados con éxito.
                            </div>
                            @if($inventario->puedeEditarse() && Auth::user()->esAdmin())
                                <button class="btn btn-danger shadow-sm rounded-pill px-4" id="btnEliminarMasivo" disabled>
                                    <i class="fas fa-trash-alt mr-1"></i> Quitar <span class="badge badge-light ml-1" id="eliminarCount" style="display:none;">0</span>
                                </button>
                            @endif
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="tablaDetalles">
                                <thead class="thead-dark">
                                    <tr>
                                        @if($inventario->puedeEditarse() && Auth::user()->esAdmin())
                                            <th width="5%" class="text-center"><input type="checkbox" id="checkAllVerificados"></th>
                                        @endif
                                        <th>Cód. Patrimonial</th>
                                        <th>Bien</th>
                                        <th>Ubicación Orig.</th>
                                        <th class="text-center">Estado Conserv.</th>
                                        <th class="text-center">Verificación</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaDetallesBody">
                                    {{-- Se llena por AJAX --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB: FALTANTES --}}
                    <div class="tab-pane fade" id="faltantes">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="alert alert-danger mb-0 flex-grow-1 mr-3" id="alert_faltantes_header">
                                <h5><i class="icon fas fa-clipboard-list"></i> Bienes por Verificar</h5>
                                Lista de bienes esperados según el alcance del inventario que aún no han sido procesados.
                            </div>
                            <div id="sync_snapshot_box" style="display:none;" class="mr-2">
                                <button class="btn btn-outline-primary btn-sm btn-sync-snap" id="btnSyncSnapshot">
                                    <i class="fas fa-sync-alt"></i> Sincronizar Lista
                                </button>
                            </div>
                            @if($inventario->puedeEditarse())
                                <button class="btn btn-success shadow-sm rounded-pill px-4" id="btnVerificarMasivo" disabled>
                                    <i class="fas fa-check-circle mr-1"></i> Verificar
                                </button>
                            @endif
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaFaltantes">
                                <thead class="bg-danger text-white">
                                    <tr>
                                        @if($inventario->puedeEditarse())
                                            <th width="5%" class="text-center col-checkbox-faltantes" style="display:none;"><input type="checkbox" id="checkAllFaltantes"></th>
                                            <th width="5%" class="text-center col-lock-faltantes"><i class="fas fa-lock text-white-50"></i></th>
                                        @endif
                                        <th>Cód. Patrimonial</th>
                                        <th>Bien</th>
                                        <th>Tipo Bien</th>
                                        @if($inventario->puedeEditarse())
                                            <th width="10%" class="text-center">Estado</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="tablaFaltantesBody">
                                    {{-- Se llena por AJAX --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB: SOBRANTES --}}
                    <div class="tab-pane fade" id="sobrantes">
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info"></i> Bienes Sobrantes Detectados</h5>
                            Bienes que fueron encontrados físicamente pero no pertenecen a esta ubicación según el registro.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaSobrantes">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Cód. Patrimonial</th>
                                        <th>Bien</th>
                                        <th>Ubicación Orig.</th>
                                        <th>Ubicación Detectada</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaSobrantesBody">
                                    {{-- Se llena por AJAX --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB: INCIDENCIAS --}}
                    <div class="tab-pane fade" id="incidencias">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="tablaIncidencias">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="12%" class="text-center">Fecha</th>
                                        <th width="10%" class="text-center">Tipo</th>
                                        <th width="35%">Bien / Hallazgo</th>
                                        <th width="20%">Ubicación Reportada</th>
                                        <th width="10%" class="text-center">Estado</th>
                                        <th width="13%" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaIncidenciasBody">
                                    {{-- Se llena por AJAX --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- MODAL AGREGAR BIEN --}}
@if($inventario->puedeEditarse())
<div class="modal fade" id="modalAgregarBien">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-search-plus"></i> Seleccionar Bien a Inventariar</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formAgregarBien">
                <input type="hidden" name="id_inventario" value="{{ $inventario->id_inventario }}">
                <input type="hidden" name="id_movimiento" id="hidden_id_movimiento">
                
                <div class="modal-body p-0">
                    {{-- PASO 1: BUSCAR --}}
                    <div id="step_search" class="p-4">
                        <div class="text-center mb-4">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 70px; height: 70px;">
                                <i class="fas fa-search fa-2x"></i>
                            </div>
                            <h4>Buscar Bien a Inventariar</h4>
                            <p class="text-muted">Ingrese el código patrimonial o nombre del bien para localizarlo y verificarlo en su inventario.</p>
                        </div>
                        
                        <div class="search-box-pro position-relative mb-3">
                            <i class="fas fa-keyboard position-absolute" style="left: 20px; top: 18px; color: #adb5bd; z-index: 5;"></i>
                            <input type="text" id="pro_search_input" class="form-control form-control-lg pl-5 shadow-sm" 
                                   style="border-radius: 50px; height: 60px; font-size: 1.1rem; border: 2px solid #e9ecef; transition: all 0.3s;" 
                                   placeholder="Escriba código o nombre y espere un momento...">
                        </div>

                        <div id="pro_search_results" class="list-group shadow-xs overflow-auto" style="max-height: 350px; display: none;">
                            {{-- Resultados vía AJAX --}}
                        </div>

                        <div id="search_empty_state" class="text-center py-5 text-muted">
                            <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                            <p>Los resultados aparecerán aquí mientras escribe...</p>
                        </div>
                    </div>

                    {{-- PASO 2: VERIFICAR --}}
                    <div id="step_verify" class="p-4" style="display: none;">
                        <div class="d-flex align-items-center bg-primary text-white p-3 rounded-lg shadow-sm mb-4">
                            <div class="mr-3">
                                <i class="fas fa-check-circle fa-2x text-white-50"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 font-weight-bold" id="selected_bien_code">--</h6>
                                <small id="selected_bien_name" class="opacity-75">--</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-white ml-auto" id="btnBackToSearch">
                                <i class="fas fa-sync"></i> Cambiar
                            </button>
                        </div>

                        <div class="row bg-light p-3 rounded-lg border mx-0 mb-4">
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Estado de Verificación</label>
                                <select name="estadoverificacion" class="form-control" required>
                                    <option value="verificado" selected>Verificado (Conforme)</option>
                                    <option value="observado">Observado (Con Daños/Diferencias)</option>
                                    <option value="no_encontrado">No Encontrado (Perdido)</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Estado de Conservación</label>
                                <select name="estado_conservacion" class="form-control" required>
                                    @foreach($estadosConservacion as $est)
                                        <option value="{{ $est->id_estado_conservacion }}">{{ $est->nombre_conservacion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Área Detectada</label>
                                <select id="add_id_area" class="form-control select2-modal select-filter-area" style="width: 100%;" data-target="#add_id_ubicacion">
                                    <option value="">-- Seleccione Área --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold">Ubicación / Ambiente</label>
                                <select name="ubicaciondetectada" id="add_id_ubicacion" class="form-control select2-modal" style="width: 100%;">
                                    <option value="">Es la misma ubicación original</option>
                                    @foreach($ubicaciones as $ubi)
                                        <option value="{{ $ubi->id_ubicacion }}" data-area="{{ $ubi->idarea }}">{{ $ubi->ambiente ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="small font-weight-bold">Observaciones</label>
                            <textarea name="observacion" class="form-control" rows="2" placeholder="Opcional..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light" id="modal_footer_pro" style="display: none;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-5" id="btnGuardarDetalle">
                        <i class="fas fa-save mr-1"></i> Registrar Verificación
                    </button>
                </div>
            </form>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL EDITAR DETALLE --}}
@if($inventario->puedeEditarse())
<div class="modal fade" id="modalEditDetalle">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Actualizar Verificación</h5>
                <button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formEditDetalle">
                @method('PUT')
                <input type="hidden" name="id_inventario" value="{{ $inventario->id_inventario }}">
                <input type="hidden" id="edit_detalle_id">
                <div class="modal-body">
                    <div class="alert alert-secondary">
                        <strong>Bien:</strong> <span id="lbl_bien_desc"></span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Estado de Verificación <span class="text-danger">*</span></label>
                            <select name="estadoverificacion" id="edit_estadoverificacion" class="form-control" required>
                                <option value="verificado">Verificado (Conforme)</option>
                                <option value="observado">Observado (Con Daños/Diferencias)</option>
                                <option value="no_encontrado">No Encontrado (Perdido)</option>
                                <option value="pendiente">Pendiente</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Estado de Conservación <span class="text-danger">*</span></label>
                            <select name="estado_conservacion" id="edit_estado_conservacion" class="form-control" required>
                                @foreach($estadosConservacion as $est)
                                    <option value="{{ $est->id_estado_conservacion }}">{{ $est->nombre_conservacion }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Área Detectada</label>
                            <select id="edit_id_area" class="form-control select2-modal select-filter-area" style="width: 100%;" data-target="#edit_id_ubicacion">
                                <option value="">-- Seleccione Área --</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Ubicación / Ambiente</label>
                            <select name="ubicaciondetectada" id="edit_id_ubicacion" class="form-control select2-modal" style="width: 100%;">
                                <option value="">Misma ubicación original</option>
                                @foreach($ubicaciones as $ubi)
                                    <option value="{{ $ubi->id_ubicacion }}" data-area="{{ $ubi->idarea }}">{{ $ubi->ambiente ?? '-' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observacion" id="edit_observacion" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="btnActualizarDetalle"><i class="fas fa-sync"></i> Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL REPORTE DE INCIDENCIA --}}
@if($inventario->puedeEditarse())
<div class="modal fade" id="modalIncidencia">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-bullhorn"></i> Reportar Incidencia / Hallazgo</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formIncidencia" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_inventario" value="{{ $inventario->id_inventario }}">
                <input type="hidden" name="id_bien" id="hidden_inc_id_bien">
                
                <div class="modal-body p-0">
                    {{-- PASO 1: SELECCIONAR BIEN (SI APLICA) --}}
                    <div id="inc_step_search" class="p-4 bg-light border-bottom">
                        <label class="font-weight-bold text-primary small text-uppercase mb-2"><i class="fas fa-search-plus mr-1"></i> 1. Relacionar con un Bien (Opcional)</label>
                        <p class="text-muted small mb-2">Si el hallazgo está relacionado a un bien específico, búsquelo aquí para vincularlo automáticamente.</p>
                        <div class="search-box-pro position-relative">
                            <i class="fas fa-search position-absolute" style="left: 15px; top: 12px; color: #adb5bd;"></i>
                            <input type="text" id="inc_search_input" class="form-control pl-5 shadow-xs" style="border-radius: 10px;" placeholder="Escriba código patrimonial o nombre...">
                        </div>
                        <div id="inc_search_results" class="list-group shadow-sm mt-2 overflow-auto" style="max-height: 200px; display: none; border-radius: 10px;">
                            {{-- Resultados --}}
                        </div>
                        <div id="selected_inc_bien_box" class="mt-2" style="display: none;">
                            <div class="d-flex align-items-center bg-white p-3 border rounded shadow-sm border-primary">
                                <i class="fas fa-check-circle text-success fa-2x mr-3"></i>
                                <div class="flex-grow-1">
                                    <div class="badge badge-primary mb-1" id="lbl_inc_bien_code"></div>
                                    <div class="text-truncate small font-weight-bold" id="lbl_inc_bien_name"></div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle ml-2" id="btnRemoveIncBien" title="Quitar selección"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>

                    {{-- PASO 2: DETALLES --}}
                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-uppercase">Tipo de Hallazgo <span class="text-danger">*</span></label>
                                <select name="tipo_incidencia" class="form-control shadow-xs" required>
                                    <option value="">-- Seleccione --</option>
                                    <option value="sobrante">Bien Sobrante (Encontrado aquí)</option>
                                    <option value="deteriorado">Bien Deteriorado / Mal Estado</option>
                                    <option value="sin_codigo">Bien sin Código Patrimonial</option>
                                    <option value="faltante">Bien Faltante (Confirmado)</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-uppercase">Evidencia (Foto)</label>
                                <div class="custom-file shadow-xs">
                                    <input type="file" name="evidencia_foto" class="custom-file-input" id="inc_foto" accept="image/*">
                                    <label class="custom-file-label" for="inc_foto">Elegir archivo...</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-uppercase">Área del Hallazgo</label>
                                <select name="id_area" id="inc_id_area" class="form-control select2-modal select-filter-area" style="width: 100%;" data-target="#inc_id_ubicacion">
                                    <option value="">-- Seleccione Área --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id_area }}">{{ $area->nombre_area }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-uppercase">Ubicación Exacta</label>
                                <select name="id_ubicacion" id="inc_id_ubicacion" class="form-control select2-modal" style="width: 100%;">
                                    <option value="">-- Seleccione Ubicación --</option>
                                    @foreach($ubicaciones as $ubi)
                                        <option value="{{ $ubi->id_ubicacion }}" data-area="{{ $ubi->idarea }}">{{ $ubi->ambiente ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-uppercase">Descripción del Hallazgo <span class="text-danger">*</span></label>
                            <textarea name="observacion" class="form-control shadow-xs" rows="3" required placeholder="Describa brevemente lo que encontró..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm" id="btnGuardarIncidencia">
                        <i class="fas fa-bullhorn mr-1"></i> Reportar Hallazgo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif


{{-- MODAL RESOLVER INCIDENCIA (PRO) --}}
@if(Auth::user()->esAdmin())
<div class="modal fade" id="modalResolverIncidencia">
    <div class="modal-dialog">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-gavel"></i> Resolución de Incidencia</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formResolverIncidencia">
                <input type="hidden" id="resolve_id">
                <div class="modal-body">
                    <div class="callout callout-info mb-3">
                        <h5 id="resolve_tipo_label">Tipo de Incidencia</h5>
                        <p id="resolve_obs_preview" class="text-muted italic"></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Resolución Administrativa / Observación Final <span class="text-danger">*</span></label>
                        <textarea name="resolucion" id="resolve_text" class="form-control" rows="4" required 
                            placeholder="Escriba aquí la decisión tomada sobre este hallazgo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success" id="btnGuardarResolucion">
                        <i class="fas fa-check-circle"></i> Confirmar Resolución
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL VER IMAGEN INCIDENCIA --}}
<div class="modal fade" id="modalVerImagen">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Evidencia de Incidencia</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <img id="img_preview" src="" class="img-fluid rounded shadow" alt="Evidencia">
            </div>
        </div>
    </div>
</div>


@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/es.min.js"></script>
<script>
    const id_inventario = {{ $inventario->id_inventario }};
    const puedeEditarse = {{ $inventario->puedeEditarse() ? 'true' : 'false' }};
    const esAdmin = {{ Auth::user()->esAdmin() ? 'true' : 'false' }};
    
    // Detectar Base URL para entornos en subcarpetas (ej: /sisInventario)
    const getBaseUrl = () => {
        const path = window.location.pathname;
        const idx = path.lastIndexOf('/inventario/');
        return idx !== -1 ? path.substring(0, idx) : '';
    };
    const BASE_URL = getBaseUrl();
    
    let detallesCache = [];
    let estadisticasCache = {};
    let incidenciasCache = [];
    const tagAlcance = "{{ str_contains($inventario->observacion, '[ALCANCE_GENERAL]') ? 'GENERAL' : 'AREA' }}";

    // Capturador de errores global para soporte
    window.onerror = function(msg, url, line) {
        console.error("JS Error:", msg, "at", url, ":", line);
        // Solo mostrar si es algo grave que rompe la UX
        if(msg.toLowerCase().includes('moment') || msg.toLowerCase().includes('detalles')) {
             Swal.fire('Error de Interfaz', 'Se detectó un problema al cargar los componentes. Por favor recargue la página.', 'error');
        }
        return false;
    };

    $(document).ready(function() {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        initBuscadores();
        
        cargarDatos();

        // --- FUNCIONES DE CARGA ---
        function cargarDatos() {
            const url = window.location.pathname;
            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(res) {
                    if(res.success && res.data) {
                        detallesCache = res.data.detalles || [];
                        estadisticasCache = res.data.estadisticas_conciliacion || {};
                        renderizarTodo();
                        cargarIncidencias();
                    }
                },
                error: function(err) {
                    console.error("Error al cargar datos:", err);
                    Swal.fire('Error de Datos', 'No se pudo obtener la información del servidor. Verifique su conexión.', 'error');
                }
            });
        }

        function cargarIncidencias() {
            $.get(`/inventario/${id_inventario}/incidencias`, function(res) {
                if(res.success) {
                    incidenciasCache = res.data;
                    renderizarIncidencias(res.data);
                    actualizarDashboardIncidencias();
                }
            });
        }

        function renderizarTodo() {
            renderizarTabla();
            renderizarFaltantes();
            renderizarSobrantes();
            actualizarDashboard();
        }

        // --- RENDERIZADO DE TABLAS ---
        function renderEmptyState(mensaje, icon = 'fa-box-open', showSync = false) {
            let syncBtn = '';
            if (showSync && puedeEditarse) {
                syncBtn = `
                    <div class="mt-4">
                        <button class="btn btn-primary rounded-pill px-4 shadow-sm btn-sync-snap">
                            <i class="fas fa-sync-alt mr-2"></i> ${tagAlcance === 'GENERAL' ? 'Sincronizar Bienes (General)' : 'Sincronizar Bienes del Área'}
                        </button>
                    </div>
                `;
            }
            return `<tr><td colspan="6" class="text-center py-5">
                <div class="empty-state animate__animated animate__fadeIn">
                    <i class="fas ${icon} fa-4x text-muted mb-3 opacity-25"></i>
                    <h5 class="text-muted font-weight-light">${mensaje}</h5>
                    ${syncBtn}
                </div>
            </td></tr>`;
        }

        function renderizarTabla(detalles = null) {
            $('#checkAllVerificados').prop('checked', false);
            $('#btnEliminarMasivo').prop('disabled', true);
            $('#eliminarCount').hide();
            const tbody = $('#tablaDetallesBody');
            tbody.empty();
            
            const data = detalles || detallesCache.filter(d => d.estadoverificacion !== 'pendiente');

            if(data.length === 0) {
                tbody.append(renderEmptyState('No hay bienes verificados que coincidan con la búsqueda.', 'fa-clipboard-list'));
                return;
            }
            data.forEach(d => {
                const checkCol = (puedeEditarse && esAdmin) ? `<td class="text-center align-middle"><input type="checkbox" class="check-verificado" value="${d.id_detalle_inv}"></td>` : '';
                tbody.append(`
                    <tr class="${puedeEditarse ? 'editable-row' : ''} animate__animated animate__fadeIn" data-id="${d.id_detalle_inv}">
                        ${checkCol}
                        <td class="align-middle"><strong>${d.bien ? d.bien.codigo_patrimonial : '-'}</strong></td>
                        <td class="align-middle text-uppercase" style="font-size: 0.9rem;">${d.bien ? d.bien.denominacion_bien : '-'}</td>
                        <td class="align-middle small text-muted">${d.ubicacion_original ? (d.ubicacion_original.area + ' (' + d.ubicacion_original.ambiente + ')') : '-'}</td>
                        <td class="align-middle text-center"><span class="${d.estado_conservacion ? d.estado_conservacion.badge : 'badge badge-secondary'} shadow-xs">${d.estado_conservacion ? d.estado_conservacion.nombre : '-'}</span></td>
                        <td class="align-middle text-center">${d.badgeverificacion}</td>
                    </tr>
                `);
            });
        }

        function renderizarFaltantes(detalles = null) {
            $('#checkAllFaltantes').prop('checked', false);
            $('#btnVerificarMasivo').prop('disabled', true);
            const tbody = $('#tablaFaltantesBody');
            tbody.empty();
            
            const dataReal = detalles || detallesCache.filter(d => d.estadoverificacion === 'pendiente');
            const estadoInv = $('#estado_actual_badge').text().toLowerCase();
            const esEnProceso = estadoInv.includes('proceso');
            const totalFaltantesReal = estadisticasCache.total_faltantes || 0;

            // Mostrar botón de sincronización si faltan registros físicos vs el total real esperado
            if (esEnProceso && dataReal.length < totalFaltantesReal) {
                $('#sync_snapshot_box').fadeIn();
                $('#alert_faltantes_header h5').html('<i class="fas fa-exclamation-triangle"></i> Sincronización Necesaria');
                $('#alert_faltantes_header').removeClass('alert-danger').addClass('alert-warning');
            } else {
                $('#sync_snapshot_box').hide();
                $('#alert_faltantes_header h5').html('<i class="fas fa-search"></i> Bienes por Verificar');
                $('#alert_faltantes_header').removeClass('alert-warning').addClass('alert-danger');
            }

            let dataMostrar = dataReal;
            if (dataMostrar.length === 0 && estadisticasCache.bienes_faltantes && estadisticasCache.bienes_faltantes.length > 0) {
                dataMostrar = estadisticasCache.bienes_faltantes.map(b => ({ bien: b }));
            }

            // 1. Mostrar Snapshot real si existe
            if (dataMostrar.length > 0) {
                dataMostrar.forEach(d => {
                    tbody.append(`
                        <tr class="animate__animated animate__fadeIn">
                            ${puedeEditarse ? `<td class="text-center align-middle"><input type="checkbox" class="check-faltante" value="${d.bien ? d.bien.id_bien : ''}"></td>` : ''}
                            <td class="align-middle"><strong>${d.bien ? d.bien.codigo_patrimonial : '-'}</strong></td>
                            <td class="align-middle text-uppercase" style="font-size: 0.9rem;">${d.bien ? d.bien.denominacion_bien : '-'}</td>
                            <td class="align-middle"><span class="badge badge-light border">${d.bien ? d.bien.tipo_bien : '-'}</span></td>
                            ${puedeEditarse ? `
                            <td class="text-center align-middle">
                                <span class="badge badge-light border text-muted" style="font-size: 0.7rem;">PENDIENTE</span>
                            </td>` : ''}
                        </tr>
                    `);
                });
                return;
            }

            $('.col-checkbox-faltantes').show();
            $('.col-lock-faltantes').hide();

            const msjEmpty = (typeof tagAlcance !== 'undefined' && tagAlcance === 'GENERAL') 
                ? 'No hay bienes pendientes en el inventario general.' 
                : 'No hay bienes pendientes en la lista de trabajo del área.';
            
            tbody.append(renderEmptyState(msjEmpty, 'fa-check-double text-success', true));
        }

        function renderizarSobrantes() {
            const tbody = $('#tablaSobrantesBody');
            tbody.empty();
            const sobrantesIds = estadisticasCache.sobrantes_ids || [];
            const sobrantes = detallesCache.filter(d => d.bien && sobrantesIds.includes(d.bien.id_bien));

            if(sobrantes.length === 0) {
                tbody.append(renderEmptyState('No se han detectado bienes sobrantes.', 'fa-plus-circle'));
                return;
            }

            sobrantes.forEach(d => {
                tbody.append(`
                    <tr>
                        <td class="align-middle"><strong>${d.bien ? d.bien.codigo_patrimonial : '-'}</strong></td>
                        <td class="align-middle">${d.bien ? d.bien.denominacion_bien : '-'}</td>
                        <td class="align-middle small text-muted">${d.ubicacion_original ? (d.ubicacion_original.area + ' (' + d.ubicacion_original.ambiente + ')') : '-'}</td>
                        <td class="align-middle font-weight-bold text-primary">${d.ubicacion_detectada ? (d.ubicacion_detectada.area + ' (' + d.ubicacion_detectada.ambiente + ')') : 'Misma Original'}</td>
                    </tr>
                `);
            });
        }

        function renderizarIncidencias(incidencias) {
            const tbody = $('#tablaIncidenciasBody');
            tbody.empty();
            if (incidencias.length === 0) {
                tbody.append(renderEmptyState('Sin incidencias o hallazgos registrados.', 'fa-bullhorn'));
                return;
            }

            incidencias.forEach(i => {
                const esRevisado = i.estado === 'revisado';
                const areaNombre = i.area ? i.area.nombre_area : '-';
                const ambienteNombre = i.ubicacion ? i.ubicacion.ambiente : '-';
                
                tbody.append(`
                    <tr class="${esRevisado ? 'bg-light' : 'table-warning-light'} animate__animated animate__fadeIn">
                        <td class="align-middle text-center small">
                            ${moment(i.created_at).format('DD/MM/YY')}<br>
                            <span class="text-muted">${moment(i.created_at).format('HH:mm')}</span>
                        </td>
                        <td class="align-middle text-center">
                            <span class="badge ${getBadgeClaseTipo(i.tipo_incidencia)} text-uppercase p-1 shadow-xs" style="font-size:0.7rem">
                                ${i.tipo_incidencia.replace('_', ' ')}
                            </span>
                        </td>
                        <td class="align-middle">
                            <div class="font-weight-bold ${esRevisado ? 'text-muted' : 'text-dark'}">
                                ${i.bien ? '<i class="fas fa-tag mr-1 text-primary"></i> [' + i.bien.codigo_patrimonial + '] ' + i.bien.denominacion_bien : '<i class="fas fa-eye mr-1 text-danger"></i> Hallazgo sin código'}
                            </div>
                            <div class="text-muted small mt-1"><i class="fas fa-comment-dots mr-1"></i> ${i.observacion || 'Sin observaciones'}</div>
                            ${i.resolucion ? `
                                <div class="bg-white border-left border-success p-2 mt-2 shadow-sm rounded-sm small">
                                    <i class="fas fa-gavel text-success mr-1"></i> <b>Resolución:</b> ${i.resolucion}
                                </div>` : ''}
                        </td>
                        <td class="align-middle small">
                            <div><i class="fas fa-building text-muted mr-1"></i> ${areaNombre}</div>
                            <div><i class="fas fa-door-open text-muted mr-1"></i> ${ambienteNombre}</div>
                        </td>
                        <td class="align-middle text-center">
                            ${esRevisado 
                                ? '<span class="badge badge-success px-2 py-1 shadow-sm"><i class="fas fa-check-circle mr-1"></i> RESUELTO</span>' 
                                : '<span class="badge badge-warning px-2 py-1 shadow-sm animated pulse infinite"><i class="fas fa-clock mr-1"></i> PENDIENTE</span>'}
                        </td>
                        <td class="align-middle text-center">
                            <div class="btn-group shadow-xs">
                                ${i.img_bien ? `<button class="btn btn-info btn-sm btn-ver-img" data-src="/storage/${i.img_bien}" title="Ver Evidencia"><i class="fas fa-image"></i></button>` : ''}
                                ${esAdmin ? `
                                    <button class="btn ${esRevisado ? 'btn-success' : 'btn-warning'} btn-sm btn-resolver-inc" data-id="${i.id_incidencia}" title="Resolver">
                                        <i class="fas fa-gavel"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm btn-delete-inc" data-id="${i.id_incidencia}" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `);
            });
        }

        // --- DASHBOARD UPDATES ---
        function actualizarDashboard() {
            const stats = estadisticasCache || {};
            $('#boxEsperados, #contadorEsperados').text(stats.total_esperados || 0);
            $('#boxVerificados, #tabCountVerificados').text(stats.total_verificados || 0);
            $('#boxConformes, #contadorConformes').text(stats.verificados_conformes || 0);
            $('#boxFaltantes, #tabCountFaltantes').text(stats.total_faltantes || 0);
            $('#boxPerdidos').text(stats.total_perdidos || 0);
            $('#boxSobrantes, #tabCountSobrantes').text(stats.total_sobrantes || 0);
            
            const progreso = stats.progreso_porcentaje || 0;
            $('#barProgreso').css('width', progreso + '%');
            $('#textProgreso').text(progreso + '% Procesado');
        }

        function actualizarDashboardIncidencias() {
            const list = incidenciasCache || [];
            const total = list.length;
            const pendientes = list.filter(i => i.estado !== 'revisado').length;
            $('#boxIncidencias, #tabCountIncidencias').text(total);
            $('#boxPendientes').text('Pendientes: ' + pendientes);
        }

        // --- ACCIONES MASIVAS ---
        $(document).on('change', '#checkAllFaltantes, .check-faltante', function() {
            if($(this).attr('id') === 'checkAllFaltantes') {
                $('.check-faltante').prop('checked', $(this).prop('checked'));
            }
            const count = $('.check-faltante:checked').length;
            $('#btnVerificarMasivo').prop('disabled', count === 0);
        });

        // --- FILTRADO DINÁMICO DE UBICACIONES POR ÁREA ---
        $(document).on('change', '.select-filter-area', function() {
            const areaId = $(this).val();
            const targetSelect = $($(this).data('target'));
            
            // Guardar opciones originales si no existen
            if (!targetSelect.data('original-options')) {
                targetSelect.data('original-options', targetSelect.find('option').clone());
            }
            
            const originalOptions = targetSelect.data('original-options');
            targetSelect.empty();
            
            // Siempre añadir la opción por defecto (vacía)
            targetSelect.append(originalOptions.first().clone());
            
            if (areaId) {
                // Filtrar opciones que coincidan con el área
                const filtered = originalOptions.filter(function() {
                    return $(this).data('area') == areaId;
                });
                targetSelect.append(filtered);
            } else {
                // Si no hay área, mostrar todas (o solo la vacía según prefieras)
                // Por UX PRO, mostraremos todas si no hay área seleccionada
                targetSelect.append(originalOptions.slice(1));
            }
            
            targetSelect.trigger('change');
        });

        $(document).on('click', '.btn-verify-quick', function() {
            const id = $(this).data('id');
            const d = detallesCache.find(x => x.id_detalle_inv == id);
            if(!d) return;
            abrirModalEdicion(d);
        });

        $(document).on('dblclick', '.editable-row', function(e) {
            if($(e.target).closest('button, a, input[type="checkbox"]').length) return;
            const id = $(this).data('id');
            const d = detallesCache.find(x => x.id_detalle_inv == id);
            if(!d) return;
            abrirModalEdicion(d);
        });

        function abrirModalEdicion(d) {
            $('#edit_detalle_id').val(d.id_detalle_inv);
            $('#lbl_bien_desc').html(`[${d.bien ? d.bien.codigo_patrimonial : '-'}] <b>${d.bien ? d.bien.denominacion_bien : '-'}</b>`);
            
            // Si es 'pendiente', sugerir 'verificado'
            const estadoActual = d.estadoverificacion === 'pendiente' ? 'verificado' : d.estadoverificacion;
            $('#edit_estadoverificacion').val(estadoActual);
            
            $('#edit_estado_conservacion').val(d.estado_conservacion ? d.estado_conservacion.id : '');
            
            // Pre-seleccionar Area para filtrar Ubicaciones
            const areaId = d.ubicacion_detectada ? d.ubicacion_detectada.id_area : (d.ubicacion_original ? d.ubicacion_original.id_area : '');
            const ubicId = d.ubicacion_detectada ? d.ubicacion_detectada.id_ubicacion : '';
            
            $('#edit_id_area').val(areaId).trigger('change');
            $('#edit_id_ubicacion').val(ubicId).trigger('change');

            $('#edit_observacion').val(d.observacion);
            $('#modalEditDetalle').modal('show');
        }

        $('#btnVerificarMasivo').on('click', function() {
            const selectedItems = $('.check-faltante:checked').map((_, el) => {
                const row = $(el).closest('tr');
                return {
                    id: $(el).val(),
                    code: row.find('td:nth-child(2)').text(),
                    name: row.find('td:nth-child(3)').text()
                };
            }).get();

            const ids = selectedItems.map(item => item.id);
            
            Swal.fire({
                title: '<div class="mb-3"><i class="fas fa-check-circle fa-2x text-success"></i></div>Confirmar Verificación',
                html: `
                    <div class="text-center mb-4">
                        <p class="text-muted">Estás a punto de verificar <b>${ids.length}</b> bienes en su ubicación registrada.</p>
                    </div>
                    <div class="row mb-4 px-3">
                        <div class="col-4">
                            <label class="status-option w-100" data-value="1">
                                <input type="radio" name="swal_est" value="1" checked class="d-none">
                                <div class="status-card p-3 border rounded text-center">
                                    <i class="fas fa-smile fa-2x mb-2 text-success"></i>
                                    <div class="small font-weight-bold">BUENO</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-4">
                            <label class="status-option w-100" data-value="2">
                                <input type="radio" name="swal_est" value="2" class="d-none">
                                <div class="status-card p-3 border rounded text-center">
                                    <i class="fas fa-meh fa-2x mb-2 text-warning"></i>
                                    <div class="small font-weight-bold">REGULAR</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-4">
                            <label class="status-option w-100" data-value="3">
                                <input type="radio" name="swal_est" value="3" class="d-none">
                                <div class="status-card p-3 border rounded text-center">
                                    <i class="fas fa-frown fa-2x mb-2 text-danger"></i>
                                    <div class="small font-weight-bold">MALO</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="text-left bg-light p-3 rounded small border" style="max-height: 150px; overflow-y: auto;">
                        <div class="font-weight-bold mb-2 border-bottom pb-1">Bienes a procesar:</div>
                        ${selectedItems.map(item => `<div class="mb-1"><span class="badge badge-primary mr-2">${item.code}</span> <span class="text-truncate">${item.name}</span></div>`).join('')}
                    </div>
                    <style>
                        .status-option input:checked + .status-card {
                            background: rgba(40, 167, 69, 0.1);
                            border-color: #28a745 !important;
                            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                        }
                        .status-card { cursor: pointer; transition: all 0.2s; }
                        .status-card:hover { transform: translateY(-3px); }
                    </style>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check mr-1"></i> Sí, Verificar Todos',
                cancelButtonText: 'Cancelar',
                customClass: { 
                    confirmButton: 'btn btn-success btn-lg rounded-pill px-5 shadow-sm mx-2', 
                    cancelButton: 'btn btn-light btn-lg rounded-pill px-4 mx-2' 
                },
                buttonsStyling: false,
                reverseButtons: true,
                didOpen: () => {
                    $('.status-card').on('click', function() {
                        $(this).parent().find('input').prop('checked', true);
                    });
                }
            }).then(result => {
                if(result.isConfirmed) {
                    const estado = $('input[name="swal_est"]:checked').val();
                    
                    Swal.fire({
                        title: 'Procesando...',
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.post(`/inventario/${id_inventario}/detalles/verificar-masivo`, {
                        bienes_ids: ids,
                        estado_conservacion: estado
                    }, function(res) {
                        if(res.success) {
                            Toast.fire({ icon: 'success', title: res.message });
                            cargarDatos();
                        }
                    });
                }
            });
        });

        // Check All Verificados
        $(document).on('change', '#checkAllVerificados', function() {
            $('.check-verificado').prop('checked', $(this).prop('checked'));
            updateBtnEliminarMasivoState();
        });

        // Individual Checkbox Verificado
        $(document).on('change', '.check-verificado', function() {
            if (!$(this).prop('checked')) {
                $('#checkAllVerificados').prop('checked', false);
            } else {
                const allChecked = $('.check-verificado').length === $('.check-verificado:checked').length;
                $('#checkAllVerificados').prop('checked', allChecked);
            }
            updateBtnEliminarMasivoState();
        });

        function updateBtnEliminarMasivoState() {
            const count = $('.check-verificado:checked').length;
            const btn = $('#btnEliminarMasivo');
            if (count > 0) {
                btn.prop('disabled', false);
                $('#eliminarCount').text(count).show();
            } else {
                btn.prop('disabled', true);
                $('#eliminarCount').hide();
            }
        }

        $(document).on('click', '#btnEliminarMasivo', function() {
            const selectedItems = $('.check-verificado:checked').map((_, el) => {
                const row = $(el).closest('tr');
                return {
                    id: $(el).val(),
                    code: row.find('td:nth-child(2)').text(),
                    name: row.find('td:nth-child(3)').text()
                };
            }).get();

            const ids = selectedItems.map(item => item.id);
            
            Swal.fire({
                title: '<div class="mb-3"><i class="fas fa-exclamation-triangle fa-2x text-danger"></i></div>Quitar Verificación',
                html: `
                    <div class="text-center mb-4">
                        <p class="text-muted">Estás a punto de quitar la verificación de <b>${ids.length}</b> bienes. Volverán al listado de faltantes.</p>
                    </div>
                    <div class="text-left bg-light p-3 rounded small border" style="max-height: 150px; overflow-y: auto;">
                        <div class="font-weight-bold mb-2 border-bottom pb-1">Bienes a quitar:</div>
                        ${selectedItems.map(item => `<div class="mb-1"><span class="badge badge-danger mr-2">${item.code}</span> <span class="text-truncate">${item.name}</span></div>`).join('')}
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Sí, Quitar',
                cancelButtonText: 'Cancelar',
                customClass: { 
                    confirmButton: 'btn btn-danger btn-lg rounded-pill px-5 shadow-sm mx-2', 
                    cancelButton: 'btn btn-light btn-lg rounded-pill px-4 mx-2' 
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then(result => {
                if(result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: `/inventario/${id_inventario}/detalles/eliminar-masivo`,
                        method: 'POST',
                        data: { detalles_ids: ids },
                        success: function(res) {
                            if(res.success) {
                                Toast.fire({ icon: 'success', title: res.message });
                                $('#checkAllVerificados').prop('checked', false);
                                cargarDatos();
                            } else {
                                Swal.fire('Error', res.message || 'No se pudo quitar la verificación.', 'error');
                            }
                        },
                        error: function(err) {
                            Swal.fire('Error', 'Ocurrió un error en el servidor.', 'error');
                        }
                    });
                }
            });
        });

        // --- VER IMAGEN ---
        $(document).on('click', '.btn-ver-img', function() {
            const src = $(this).data('src');
            $('#img_preview').attr('src', src);
            $('#modalVerImagen').modal('show');
        });

        // --- ELIMINAR INCIDENCIA ---
        $(document).on('click', '.btn-delete-inc', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Eliminar Incidencia?',
                text: "Esta acción no se puede deshacer.",
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar permanentemente',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(result => {
                if(result.isConfirmed) {
                    $.ajax({
                        url: `/incidencias/${id}`,
                        method: 'DELETE',
                        success: function(res) {
                            if(res.success) {
                                Toast.fire({ icon: 'success', title: res.message });
                                cargarIncidencias();
                            }
                        }
                    });
                }
            });
        });

        // --- RESOLUCIÓN DE INCIDENCIAS ---
        $(document).on('click', '.btn-resolver-inc', function() {
            const id = $(this).data('id');
            const inc = incidenciasCache.find(x => x.id_incidencia == id);
            if(!inc) return;

            $('#resolve_id').val(id);
            $('#resolve_tipo_label').text('Tipo: ' + inc.tipo_incidencia.toUpperCase());
            $('#resolve_obs_preview').text('Hallazgo: ' + inc.observacion);
            $('#resolve_text').val(inc.resolucion || '');
            $('#modalResolverIncidencia').modal('show');
        });

        $('#formResolverIncidencia').on('submit', function(e) {
            e.preventDefault();
            const id = $('#resolve_id').val();
            $.post(`/incidencias/${id}/cambiar-estado`, {
                resolucion: $('#resolve_text').val()
            }, function(res) {
                if(res.success) {
                    $('#modalResolverIncidencia').modal('hide');
                    Toast.fire({ icon: 'success', title: 'Incidencia resuelta exitosamente' });
                    cargarIncidencias();
                }
            });
        });

        // --- SINCRONIZACIÓN DE SNAPSHOT ---
        $('#btnSyncSnapshot').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sincronizando...');
            
            $.post(`${BASE_URL}/inventario/${id_inventario}/regenerar-snapshot`, function(res) {
                if(res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    cargarDatos(); // Recargar todo
                } else {
                    Swal.fire('Atención', res.message, 'info');
                }
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Sincronizar Lista Oficial');
            }).fail(function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Sincronizar Lista Oficial');
                Toast.fire({ icon: 'error', title: 'Error al sincronizar lista' });
            });
        });

        // --- FINALIZACIÓN CON SEGURIDAD ---
        $(document).on('click', '#btnFinalizarInventario', function() {
            // Validar incidencias (usar longitud de cache o 0 si no existe)
            const incidenciasPendientes = (typeof incidenciasCache !== 'undefined') ? incidenciasCache.filter(i => i.estado !== 'revisado').length : 0;
            
            if(incidenciasPendientes > 0) {
                Swal.fire({
                    title: 'Hallazgos Pendientes',
                    text: `Hay ${incidenciasPendientes} incidencias sin resolución administrativa. Debe resolverlas antes de cerrar el inventario.`,
                    icon: 'warning',
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Validar bienes pendientes (snapshot)
            const bienesPendientes = (estadisticasCache && estadisticasCache.total_faltantes) ? estadisticasCache.total_faltantes : 0;
            let warningText = "Se cerrará el inventario y se generará el acta final.";
            
            if (bienesPendientes > 0) {
                warningText = `¡Atención! Aún hay ${bienesPendientes} bienes pendientes de verificar. Si cierra ahora, estos se considerarán como NO ENCONTRADOS en el acta final.`;
            }

            Swal.fire({
                title: '¿Finalizar Auditoría?',
                text: warningText,
                icon: bienesPendientes > 0 ? 'error' : 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Finalizar y Cerrar',
                cancelButtonText: 'Seguir Audita'
            }).then(result => {
                if(result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        html: 'Generando acta y cerrando registros.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: `${BASE_URL}/inventario/${id_inventario}/cambiar-estado`,
                        method: 'POST',
                        data: { 
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            estadoinventario: 'cerrado' 
                        },
                        success: function(res) {
                            if(res.success) {
                                Swal.fire({
                                    title: '¡Inventario Cerrado!',
                                    text: res.message,
                                    icon: 'success'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('No se pudo cerrar', res.message, 'warning');
                            }
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error interno del servidor';
                            Swal.fire('Error al cerrar', msg, 'error');
                        }
                    });
                }
            });
        });

        // --- REGULARIZACIÓN ADMINISTRATIVA (FORMALIZAR UBICACIONES) ---
        $(document).on('click', '#btnRegularizarUbicaciones', function() {
            Swal.fire({
                title: '¿Formalizar Ubicaciones?',
                text: "Se crearán nuevos movimientos de reasignación para todos los bienes cuya ubicación detectada sea distinta a la registrada. Esta acción es irreversible y actualiza el catálogo oficial.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Regularizar Sistema',
                cancelButtonText: 'Cancelar',
                footer: '<span class="text-info small"><i class="fas fa-info-circle"></i> Solo bienes con ubicación distinta serán procesados.</span>'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando Regularización',
                        html: 'Actualizando ubicaciones y generando movimientos oficiales...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: `${BASE_URL}/inventario/${id_inventario}/regularizar`,
                        method: 'POST',
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function(res) {
                            if(res.success) {
                                Swal.fire({
                                    title: '¡Sincronización Exitosa!',
                                    text: res.message,
                                    icon: 'success',
                                    confirmButtonText: 'Genial'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Error de Proceso', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al conectar con el servidor';
                            Swal.fire('Error Crítico', msg, 'error');
                        }
                    });
                }
            });
        });

        // --- HELPERS ---
        function getBadgeClaseTipo(tipo) {
            const map = { 'sobrante': 'badge-info', 'faltante': 'badge-danger', 'sin_codigo': 'badge-dark', 'deteriorado': 'badge-warning' };
            return map[tipo] || 'badge-secondary';
        }

        function initBuscadores() {
            if(puedeEditarse) {
                // Buscador AJAX Pro para "Añadir Bien"
                let typingTimer;
                const searchInput = $('#pro_search_input');
                const resultsBox = $('#pro_search_results');
                const emptyState = $('#search_empty_state');

                searchInput.on('keyup', function() {
                    clearTimeout(typingTimer);
                    const q = $(this).val().trim();
                    if (q.length < 3) {
                        resultsBox.hide();
                        emptyState.show();
                        return;
                    }

                    typingTimer = setTimeout(() => {
                        emptyState.html('<div class="py-4"><i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><p class="h6">Buscando bienes...</p></div>');
                        $.get(`${BASE_URL}/inventario/${id_inventario}/bienes-disponibles`, { q }, function(res) {
                            resultsBox.empty();
                            if (res.results && res.results.length > 0) {
                                res.results.forEach(item => {
                                    // Parsing seguro para evitar errores de JS si el formato cambia
                                    let code = '-';
                                    let desc = 'Sin descripción';
                                    let ubic = 'Sin ubicación';

                                    try {
                                        const matchCode = item.text.match(/\[(.*?)\]/);
                                        code = matchCode ? matchCode[1] : '-';
                                        
                                        const parts = item.text.split(']');
                                        if (parts.length > 1) {
                                            const subParts = parts[1].split(' - Ubic:');
                                            desc = subParts[0].trim();
                                            if (subParts.length > 1) ubic = subParts[1].trim();
                                        }
                                    } catch (e) {
                                        console.warn("Error parseando item:", item.text);
                                    }

                                    resultsBox.append(`
                                        <button type="button" class="list-group-item list-group-item-action p-3 pro-select-bien border-bottom" 
                                                data-id="${item.id}" data-code="${code}" 
                                                data-name="${desc}" 
                                                data-area="${item.id_area}" data-ubic="${item.id_ubicacion}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div style="flex: 1;">
                                                    <span class="badge badge-primary mb-1 shadow-sm">${code}</span>
                                                    <div class="font-weight-bold text-dark h6 mb-1 text-uppercase small">${desc}</div>
                                                    <div class="small text-muted"><i class="fas fa-map-marker-alt mr-1"></i> ${ubic}</div>
                                                </div>
                                                <div class="ml-3">
                                                    <span class="btn btn-sm btn-outline-primary rounded-pill">Seleccionar</span>
                                                </div>
                                            </div>
                                        </button>
                                    `);
                                });
                                resultsBox.show();
                                emptyState.hide();
                            } else {
                                resultsBox.hide();
                                emptyState.html('<div class="py-4 text-warning"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p class="h6">No se encontraron bienes disponibles.</p></div>').show();
                            }
                        }).fail(() => {
                            emptyState.html('<div class="py-4 text-danger"><i class="fas fa-times-circle fa-3x mb-3"></i><p class="h6">Error en la búsqueda. Reintente.</p></div>');
                        });
                    }, 400);
                });

                // Al seleccionar un bien de la lista pro
                $(document).on('click', '.pro-select-bien', function() {
                    const d = $(this).data();
                    $('#hidden_id_movimiento').val(d.id);
                    $('#selected_bien_code').text(d.code);
                    $('#selected_bien_name').text(d.name);
                    
                    // Auto-completar ubicacion
                    if (d.area) {
                        $('#add_id_area').val(d.area).trigger('change');
                        setTimeout(() => {
                            if (d.ubic) $('#add_id_ubicacion').val(d.ubic).trigger('change');
                        }, 300);
                    }

                    $('#step_search').hide();
                    $('#step_verify').fadeIn();
                    $('#modal_footer_pro').fadeIn();
                });

                $('#btnBackToSearch').on('click', function() {
                    $('#step_verify').hide();
                    $('#modal_footer_pro').hide();
                    $('#step_search').fadeIn();
                    $('#pro_search_input').focus();
                });

                // Reset modal al cerrar
                $('#modalAgregarBien').on('hidden.bs.modal', function () {
                    $('#step_verify, #modal_footer_pro').hide();
                    $('#step_search').show();
                    $('#pro_search_input').val('');
                    $('#pro_search_results').hide();
                    $('#search_empty_state').html('<i class="fas fa-search fa-3x mb-3 opacity-25"></i><p>Los resultados aparecerán aquí mientras escribe...</p>').show();
                });

                // --- BUSCADOR AJAX PRO PARA INCIDENCIAS ---
                let incTimer;
                $('#inc_search_input').on('keyup', function() {
                    clearTimeout(incTimer);
                    const q = $(this).val().trim();
                    const results = $('#inc_search_results');
                    if (q.length < 3) { results.hide(); return; }

                    incTimer = setTimeout(() => {
                        $.get(`${BASE_URL}/inventario/${id_inventario}/bienes-disponibles`, { q: q, incidencia: 1 }, function(res) {
                            results.empty();
                            if (res.results && res.results.length > 0) {
                                res.results.forEach(item => {
                                    const code = item.text.match(/\[(.*?)\]/)[1];
                                    const desc = item.text.split(']')[1].split('-')[0].trim();
                                    results.append(`
                                        <button type="button" class="list-group-item list-group-item-action py-2 px-3 small inc-select-bien" 
                                                data-id="${item.id}" data-code="${code}" data-name="${desc}"
                                                data-area="${item.id_area}" data-ubic="${item.id_ubicacion}">
                                            <strong>[${code}]</strong> ${desc}
                                        </button>
                                    `);
                                });
                                results.fadeIn();
                            } else {
                                results.hide();
                            }
                        });
                    }, 400);
                });

                $(document).on('click', '.inc-select-bien', function() {
                    const d = $(this).data();
                    $('#hidden_inc_id_bien').val(d.id); // Aquí realmente enviamos el ID del movimiento que el backend mapeará al bien
                    $('#lbl_inc_bien_code').text(code = d.code);
                    $('#lbl_inc_bien_name').text(d.name);
                    $('#inc_search_results').hide();
                    $('#inc_search_input').closest('.search-box-pro').hide();
                    $('#selected_inc_bien_box').fadeIn();

                    // Auto-completar ubicación
                    if (d.area) {
                        $('#inc_id_area').val(d.area).trigger('change');
                        setTimeout(() => {
                            if (d.ubic) $('#inc_id_ubicacion').val(d.ubic).trigger('change');
                        }, 200);
                    }
                });

                $('#btnRemoveIncBien').on('click', function() {
                    $('#hidden_inc_id_bien').val('');
                    $('#selected_inc_bien_box').hide();
                    $('#inc_search_input').val('');
                    $('#inc_search_input').closest('.search-box-pro').fadeIn(function() {
                        $(this).find('input').focus();
                    });
                });

                // Reset modal incidencia
                $('#modalIncidencia').on('hidden.bs.modal', function () {
                    $('#formIncidencia')[0].reset();
                    $('#formIncidencia .select2').val('').trigger('change');
                    $('#btnRemoveIncBien').click();
                    $('.custom-file-label').text('Elegir archivo...');
                    $('select[name="tipo_incidencia"]').trigger('change');
                });

                // --- DINAMISMO DEL MODAL INCIDENCIA ---
                $('select[name="tipo_incidencia"]').on('change', function() {
                    const tipo = $(this).val();
                    const searchBox = $('#inc_step_search');
                    const areaContainer = $('#inc_id_area').closest('.form-group');
                    const ubicContainer = $('#inc_id_ubicacion').closest('.form-group');
                    const areaLabel = areaContainer.find('label');
                    const ubicLabel = ubicContainer.find('label');
                    
                    // Remover * visual de requeridos
                    areaLabel.find('span.text-danger').remove();
                    ubicLabel.find('span.text-danger').remove();

                    if (tipo === 'sin_codigo') {
                        // Un bien sin código puede ser desconocido, o el usuario podría saber cuál es por sus características.
                        // Mantenemos la búsqueda visible de forma OPCIONAL por si quiere vincularlo a un bien existente en el sistema.
                        searchBox.slideDown();
                        
                        areaContainer.slideDown();
                        ubicContainer.slideDown();
                        $('#inc_id_area').prop('required', true);
                        $('#inc_id_ubicacion').prop('required', true);
                        areaLabel.append(' <span class="text-danger">*</span>');
                        ubicLabel.append(' <span class="text-danger">*</span>');
                    } else if (tipo === 'faltante') {
                        // Un bien faltante no tiene área ni ubicación de hallazgo
                        searchBox.slideDown();
                        areaContainer.slideUp();
                        ubicContainer.slideUp();
                        $('#inc_id_area').val('').trigger('change').prop('required', false);
                        $('#inc_id_ubicacion').val('').trigger('change').prop('required', false);
                    } else if (tipo === 'sobrante' || tipo === 'deteriorado') {
                        // Requieren saber dónde se encontró el bien
                        searchBox.slideDown();
                        areaContainer.slideDown();
                        ubicContainer.slideDown();
                        $('#inc_id_area').prop('required', true);
                        $('#inc_id_ubicacion').prop('required', true);
                        areaLabel.append(' <span class="text-danger">*</span>');
                        ubicLabel.append(' <span class="text-danger">*</span>');
                    } else {
                        // Por defecto
                        searchBox.slideDown();
                        areaContainer.slideDown();
                        ubicContainer.slideDown();
                        $('#inc_id_area').prop('required', false);
                        $('#inc_id_ubicacion').prop('required', false);
                    }
                });

                // Mostrar nombre de archivo en inputs file
                $('.custom-file-input').on('change', function() {
                    let fileName = $(this).val().split('\\').pop();
                    $(this).next('.custom-file-label').addClass("selected").html(fileName);
                });

                // Inicializar selectores secundarios en modales
                $('.select2-modal').each(function() {
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $(this).closest('.modal')
                    });
                });
            }
        }

        // --- SINCRONIZACIÓN DE SNAPSHOT ---
        $(document).on('click', '.btn-sync-snap', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Sincronizando...');
            
            $.post(`/inventario/${id_inventario}/regenerar-snapshot`, {}, function(res) {
                if(res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    cargarDatos();
                } else {
                    Swal.fire('Atención', res.message, 'info');
                }
            }).fail(err => {
                Swal.fire('Error', 'No se pudo sincronizar la lista.', 'error');
            }).always(() => {
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-2"></i> Sincronizar Bienes');
            });
        });

        // --- FORMULARIO: AGREGAR BIEN ---
        $('#formAgregarBien').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnGuardarDetalle');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            $.post(`${BASE_URL}/inventario/${id_inventario}/detalles`, $(this).serialize(), function(res) {
                if(res.success) {
                    $('#modalAgregarBien').modal('hide');
                    $('#formAgregarBien')[0].reset();
                    Toast.fire({ icon: 'success', title: res.message });
                    cargarDatos();
                }
            }).fail(err => {
                const msg = err.responseJSON ? err.responseJSON.message : 'Error al registrar el bien.';
                Swal.fire('Error', msg, 'error');
            }).always(() => {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Registrar Verificación');
            });
        });



        $('#formEditDetalle').on('submit', function(e) {
            e.preventDefault();
            const id = $('#edit_detalle_id').val();
            const btn = $('#btnActualizarDetalle');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            $.ajax({
                url: `${BASE_URL}/inventario/${id_inventario}/detalles/${id}`,
                method: 'PUT',
                data: $(this).serialize(),
                success: function(res) {
                    if(res.success) {
                        $('#modalEditDetalle').modal('hide');
                        Toast.fire({ icon: 'success', title: 'Registro actualizado correctamente' });
                        cargarDatos();
                    }
                },
                error: function(err) {
                    const msg = err.responseJSON ? err.responseJSON.message : 'Error al actualizar el registro.';
                    Swal.fire('Error', msg, 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Actualizar');
                }
            });
        });

        // --- FORMULARIO: INCIDENCIAS (CON IMAGEN) ---
        $('#formIncidencia').on('submit', function(e) {
            e.preventDefault();
            
            const tipo = $('select[name="tipo_incidencia"]').val();
            const idBien = $('#hidden_inc_id_bien').val();
            
            if ((tipo === 'faltante' || tipo === 'deteriorado') && !idBien) {
                Swal.fire('Atención', 'Para reportar un bien faltante o deteriorado, debe buscar y seleccionar el bien relacionado obligatoriamente.', 'warning');
                return;
            }

            const formData = new FormData(this);
            const btn = $('#btnGuardarIncidencia');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            $.ajax({
                url: `${BASE_URL}/inventario/${id_inventario}/incidencias`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if(res.success) {
                        $('#modalIncidencia').modal('hide');
                        $('#formIncidencia')[0].reset();
                        $('#formIncidencia .select2').val('').trigger('change');
                        Toast.fire({ icon: 'success', title: 'Incidencia registrada con éxito' });
                        cargarIncidencias();
                    }
                },
                error: function(err) {
                    Swal.fire('Error', 'No se pudo guardar la incidencia.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Incidencia');
                }
            });
        });

    // --- BUSQUEDA LOCAL EN TABLAS ---
    $('#inputBuscarTabla').on('keyup', function() {
        const term = $(this).val().toLowerCase().trim();
        const activeTab = $('#tabsInventario .nav-link.active').attr('href');
        
        if (activeTab === '#lista') {
            const filtrados = detallesCache.filter(d => 
                d.estadoverificacion !== 'pendiente' && 
                ( (d.bien?.codigo_patrimonial?.toLowerCase().includes(term)) || (d.bien?.denominacion_bien?.toLowerCase().includes(term)) )
            );
            renderizarTabla(filtrados);
        } else if (activeTab === '#faltantes') {
            const filtrados = detallesCache.filter(d => 
                d.estadoverificacion === 'pendiente' && 
                ( (d.bien?.codigo_patrimonial?.toLowerCase().includes(term)) || (d.bien?.denominacion_bien?.toLowerCase().includes(term)) )
            );
            renderizarFaltantes(filtrados);
        } else if (activeTab === '#incidencias') {
            const filtrados = incidenciasCache.filter(i => 
                (i.bien?.codigo_patrimonial?.toLowerCase().includes(term)) || 
                (i.bien?.denominacion_bien?.toLowerCase().includes(term)) ||
                (i.observacion?.toLowerCase().includes(term))
            );
            renderizarIncidencias(filtrados);
        }
    });

    // Limpiar buscador al cambiar de pestaña
    $('#tabsInventario .nav-link').on('shown.bs.tab', function () {
        $('#inputBuscarTabla').val('').trigger('keyup');
    });

    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
    }); // Cierre de $(document).ready
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .animate-up { transition: all 0.3s cubic-bezier(.25,.8,.25,1); }
    .animate-up:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.1); z-index: 10; background-color: #fff; }
    .editable-row { cursor: pointer; transition: all 0.2s; }
    .editable-row:hover { background-color: rgba(0,123,255,0.04) !important; box-shadow: inset 4px 0 0 #007bff; }
    .shadow-xs { box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .badge { font-weight: 700; padding: 0.5em 0.8em; border-radius: 50px; text-transform: uppercase; font-size: 0.7rem; }
    
    /* Tabs Estilo Pro */
    .nav-tabs { border-bottom: 2px solid #f4f6f9; gap: 5px; }
    .nav-tabs .nav-link { 
        border: none; 
        color: #888; 
        padding: 0.8rem 1.2rem; 
        transition: all 0.3s ease; 
        font-weight: 700;
        border-radius: 10px 10px 0 0;
        font-size: 0.85rem;
        letter-spacing: 0.3px;
    }
    .nav-tabs .nav-link:hover { color: #555; background: #fdfdfd; }
    .nav-tabs .nav-link.active { 
        color: #007bff !important; 
        background: #fff !important;
        border-bottom: 3px solid #007bff;
        box-shadow: 0 4px 10px rgba(0,123,255,0.05);
    }
    
    .progress-xxs { height: 8px; border-radius: 10px; background: #eaedf2; overflow: hidden; }
    .progress-bar-animated { animation: progress-bar-stripes 1s linear infinite; }
    
    .empty-state { transition: all 0.5s ease; }
    .empty-state i { opacity: 0.15; filter: grayscale(1); }
    .h4 { letter-spacing: -1px; font-family: 'Inter', sans-serif; }
    
    .search-container input {
        border-radius: 30px !important;
        background-color: #f1f3f5;
        border: 1px solid transparent;
        transition: all 0.3s ease;
        padding-left: 2.5rem !important;
    }
    .search-container input:focus {
        background-color: #fff;
        width: 300px !important;
        border-color: #007bff;
        box-shadow: 0 5px 15px rgba(0,123,255,0.1) !important;
    }
    .search-container i {
        left: 15px !important;
        z-index: 5;
    }

    /* Botones Estilo Pro (Pills) */
    .btn { 
        border-radius: 50px !important; 
        padding: 0.5rem 1.5rem; 
        font-weight: 700;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        letter-spacing: 0.2px;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(0,0,0,0.15) !important;
    }
    .btn-sm {
        padding: 0.25rem 0.8rem !important;
        font-size: 0.75rem;
    }

    /* Table Headers */
    .thead-dark th {
        background-color: #343a40;
        border-color: #454d55;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }
    .search-box-pro input:focus {
        border-color: #007bff !important;
        box-shadow: 0 10px 25px rgba(0,123,255,0.1) !important;
    }
    .pro-select-bien { border-left: 0; border-right: 0; transition: all 0.2s; border-radius: 0 !important; }
    .pro-select-bien:hover { background-color: #f8f9fa; border-left: 4px solid #007bff; padding-left: 1.5rem !important; }
    #pro_search_results { border-radius: 10px; border: 1px solid #eee; margin-top: 10px; }
    .empty-icon-container {
        position: relative;
        display: inline-block;
        padding: 20px;
    }
    .gap-2 { gap: 0.5rem; }
</style>
@stop
