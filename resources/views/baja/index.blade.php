@extends('layouts.main')

@section('title', 'Gestión de Bajas')

@section('content_header')
    <h1>Registro de Bajas de Bienes</h1>
@stop

@section('content')

{{-- ===== TARJETAS ESTADÍSTICAS ===== --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Bajas Registradas</span>
                <span class="info-box-number" id="statTotal">{{ $total }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-calendar-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Bajas Este Mes</span>
                <span class="info-box-number" id="statMes">{{ $totalMes }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-boxes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Bienes con Baja Activa</span>
                <span class="info-box-number" id="statInactivos">{{ $bienesInactivos }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ===== CARD PRINCIPAL ===== --}}
<div class="card">
    <div class="card-header py-2">

        {{-- FILA 1: Botones de acción --}}
        <div class="row align-items-center mb-2">
            @if(Auth::user()->esAdmin())
            <div class="col-auto">
                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalBuscarBien">
                    <i class="fas fa-times-circle"></i> Registrar Baja
                </button>
            </div>
            @endif
            <div class="col-auto">
                <div class="btn-group btn-group-sm">
                    <a id="btnExportPdf"
                       href="{{ route('baja.exportar.pdf') }}"
                       class="btn btn-outline-danger"
                       title="Exportar PDF" target="_blank">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <a id="btnExportExcel"
                       href="{{ route('baja.exportar.excel') }}"
                       class="btn btn-outline-success"
                       title="Exportar Excel/CSV">
                        <i class="fas fa-file-excel"></i> Excel
                    </a>
                </div>
            </div>
            <div class="col text-right">
                <small class="text-muted">
                    Mostrando <strong id="from">{{ $bajas->firstItem() ?? 0 }}</strong>
                    a <strong id="to">{{ $bajas->lastItem() ?? 0 }}</strong>
                    de <strong id="resultadosCount">{{ $bajas->total() }}</strong>
                    <span id="loadingSearch" style="display:none;"> &nbsp;<i class="fas fa-spinner fa-spin text-danger"></i></span>
                </small>
            </div>
        </div>

        {{-- FILA 2: Filtros --}}
        <div class="row g-2 align-items-end">
            {{-- Buscador --}}
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-danger text-white"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="searchInput" class="form-control"
                           placeholder="Código, denominación, motivo..." autocomplete="off">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" id="btnLimpiar" title="Limpiar"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            </div>

            {{-- Tipo de Bien --}}
            <div class="col-md-2">
                <select id="filtroTipo" class="form-control form-control-sm">
                    <option value="">— Tipo de Bien —</option>
                    @foreach($tiposBien as $tipo)
                        <option value="{{ $tipo->id_tipo_bien }}">{{ $tipo->nombre_tipo }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Mes --}}
            <div class="col-md-1">
                <select id="filtroMes" class="form-control form-control-sm">
                    <option value="">— Mes —</option>
                    @foreach(['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'] as $k=>$v)
                        <option value="{{ $k }}" @if(now()->format('m') == $k) @endif>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Año --}}
            <div class="col-md-1">
                <select id="filtroAnio" class="form-control form-control-sm">
                    <option value="">— Año —</option>
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            {{-- Rango de fechas --}}
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <input type="date" id="filtroDesde" class="form-control" title="Fecha desde">
                    <div class="input-group-prepend input-group-append">
                        <span class="input-group-text">—</span>
                    </div>
                    <input type="date" id="filtroHasta" class="form-control" title="Fecha hasta">
                </div>
            </div>

            {{-- Botones filtrar / limpiar filtros --}}
            <div class="col-md-2">
                <div class="btn-group btn-group-sm w-100">
                    <button class="btn btn-primary" id="btnFiltrar" title="Aplicar filtros">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <button class="btn btn-outline-secondary" id="btnLimpiarFiltros" title="Limpiar todos los filtros">
                        <i class="fas fa-broom"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>{{-- /card-header --}}

    <div class="card-body p-0">
        <div class="table-responsive" id="tablaContainer">
            <table class="table table-bordered table-striped table-hover mb-0" style="font-size:0.85rem;">
                <thead class="thead-dark">
                    <tr>
                        <th width="4%">#</th>
                        <th width="11%">Código Patrimonial</th>
                        <th>Denominación del Bien</th>
                        <th width="11%">Tipo Bien</th>
                        <th width="9%">Fecha Baja</th>
                        <th width="20%">Motivo</th>
                        <th width="10%">Resolución</th>
                        @if(Auth::user()->esAdmin())
                        <th width="7%" class="text-center">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="tablaBajas">
                    @forelse($bajas as $baja)
                    <tr id="row-{{ $baja->id_baja }}">
                        <td class="text-center">{{ $baja->id_baja }}</td>
                        <td><code class="text-danger">{{ $baja->bien->codigo_patrimonial ?? '—' }}</code></td>
                        <td>
                            <strong>{{ $baja->bien->denominacion_bien ?? '—' }}</strong>
                            @if($baja->bien && $baja->bien->marca_bien)
                                <br><small class="text-muted">{{ $baja->bien->marca_bien }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary">
                                {{ $baja->bien->tipoBien->nombre_tipo ?? '—' }}
                            </span>
                        </td>
                        <td>{{ $baja->fecha_baja?->format('d/m/Y') }}</td>
                        <td>
                            <span title="{{ $baja->motivo_baja }}">
                                {{ Str::limit($baja->motivo_baja, 60) }}
                            </span>
                        </td>
                        <td>
                            @if($baja->resolucion)
                                <small class="text-success font-weight-bold">{{ $baja->resolucion }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        @if(Auth::user()->esAdmin())
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-info btn-ver-baja"
                                    data-id="{{ $baja->id_baja }}" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr id="rowEmpty">
                        <td colspan="{{ Auth::user()->esAdmin() ? 8 : 7 }}" class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-2 d-block"></i>
                            <h6>No hay bajas registradas</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div id="paginacionContainer" class="d-flex justify-content-between align-items-center p-3">
            <div>
                <small class="text-muted">
                    Mostrando <strong id="paginaInfo">{{ $bajas->firstItem() ?? 0 }} - {{ $bajas->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $bajas->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks"></div>
        </div>

        {{-- Sin resultados --}}
        <div id="noResultados" class="text-center py-5" style="display:none;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
            <h5>No se encontraron resultados</h5>
            <p class="text-muted">No hay bajas que coincidan con los filtros aplicados.</p>
            <button class="btn btn-outline-danger btn-sm" id="btnMostrarTodo">
                <i class="fas fa-undo"></i> Mostrar todo
            </button>
        </div>
    </div>
</div>

@if(Auth::user()->esAdmin())
{{-- ============================================================
     MODAL PASO 1: BUSCAR BIEN
============================================================ --}}
<div class="modal fade" id="modalBuscarBien" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-search"></i> Buscar Bien para dar de Baja
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                {{-- Buscador dentro del modal --}}
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-danger text-white"><i class="fas fa-barcode"></i></span>
                            </div>
                            <input type="text" id="buscarBienInput" class="form-control"
                                   placeholder="Buscar por código patrimonial, denominación o marca..."
                                   autocomplete="off">
                            <div class="input-group-append">
                                <span class="input-group-text" id="buscarBienSpinner" style="display:none;">
                                    <i class="fas fa-spinner fa-spin text-danger"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select id="filtroBuscarTipo" class="form-control">
                            <option value="">— Todos los tipos —</option>
                            @foreach($tiposBien as $tipo)
                                <option value="{{ $tipo->id_tipo_bien }}">{{ $tipo->nombre_tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tabla de resultados --}}
                <div id="resultadosBienesContainer" style="max-height: 320px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px;">
                    <table class="table table-sm table-hover mb-0" id="resultadosBienTabla">
                        <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>Código Patrimonial</th>
                                <th>Denominación</th>
                                <th>Marca</th>
                                <th>Tipo</th>
                                <th class="text-center">Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody id="resultadosBienBody">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    <i class="fas fa-info-circle"></i> Escriba para buscar bienes activos disponibles
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <small class="text-muted mt-2 d-block">
                    <i class="fas fa-info-circle text-info"></i>
                    Solo se muestran bienes activos sin baja registrada.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL PASO 2: REGISTRAR BAJA (se abre al seleccionar un bien)
============================================================ --}}
<div class="modal fade" id="modalRegistrarBaja" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Registrar Baja Formal
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formRegistrarBaja">
                @csrf
                <input type="hidden" name="id_bien" id="baja_id_bien_hidden">

                <div class="modal-body">
                    {{-- Alerta normativa --}}
                    <div class="alert alert-warning py-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> Esta acción marcará el bien como <strong>DADO DE BAJA</strong>
                        formalmente. Según la Directiva N° 001-2015/SBN, la baja es un acto <strong>definitivo</strong>.
                    </div>

                    {{-- Tarjeta del bien seleccionado --}}
                    <div class="card card-outline card-danger mb-3">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0 text-danger">
                                <i class="fas fa-cube"></i> Bien Seleccionado
                            </h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Código Patrimonial</small><br>
                                    <strong id="baja_show_codigo" class="text-danger">—</strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Tipo de Bien</small><br>
                                    <span id="baja_show_tipo" class="badge badge-secondary">—</span>
                                </div>
                            </div>
                            <hr class="my-2">
                            <small class="text-muted">Denominación</small><br>
                            <strong id="baja_show_denominacion">—</strong>
                            <small id="baja_show_marca" class="text-muted ml-2"></small>

                            <button type="button" class="btn btn-link btn-sm text-danger p-0 mt-1 d-block"
                                    id="btnCambiarBien">
                                <i class="fas fa-exchange-alt"></i> Cambiar bien
                            </button>
                        </div>
                    </div>

                    {{-- Campos del formulario --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="baja_fecha">Fecha de Baja <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_baja" id="baja_fecha" class="form-control"
                                       value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                                <span class="text-danger error-fecha_baja d-block mt-1"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="baja_resolucion">N° Resolución / Acto Administrativo</label>
                                <input type="text" name="resolucion" id="baja_resolucion" class="form-control"
                                       maxlength="100" placeholder="Ej: RES-2024-001" autocomplete="off">
                                <span class="text-danger error-resolucion d-block mt-1"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="baja_motivo">Motivo de Baja <span class="text-danger">*</span></label>
                        <textarea name="motivo_baja" id="baja_motivo" class="form-control" rows="3"
                                  maxlength="255"
                                  placeholder="Describa el motivo (obsolescencia, robo, deterioro, destrucción, etc.)..."
                                  required></textarea>
                        <small class="form-text text-muted"><span id="charCount">0</span>/255 caracteres</small>
                        <span class="text-danger error-motivo_baja d-block mt-1"></span>
                    </div>

                    <div class="form-group">
                        <label for="baja_observacion">Observación Adicional</label>
                        <textarea name="observacion" id="baja_observacion" class="form-control" rows="2"
                                  maxlength="1000" placeholder="Observaciones adicionales (opcional)..."></textarea>
                        <span class="text-danger error-observacion d-block mt-1"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger" id="btnRegistrarBaja">
                        <i class="fas fa-times-circle"></i> Registrar Baja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL: VER DETALLE
============================================================ --}}
<div class="modal fade" id="modalDetalleBaja" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Detalle de Baja</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="detalleBajaContenido">
                <div class="text-center py-3">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 text-muted">Cargando...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const esAdmin = {{ Auth::user()->esAdmin() ? 'true' : 'false' }};
const rutaBuscarBienes  = "{{ route('baja.buscar-bienes') }}";
const rutaBajaIndex     = "{{ route('baja.index') }}";
const rutaBajaStore     = "{{ route('baja.store') }}";
const rutaExportPdf     = "{{ route('baja.exportar.pdf') }}";
const rutaExportExcel   = "{{ route('baja.exportar.excel') }}";
</script>
<script>
$(document).ready(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // ─────────────────────────────────────
    // ESTADO DE FILTROS
    // ─────────────────────────────────────
    let paginaActual = 1;
    let searchTimeout;
    let bienSeleccionadoId = null;

    function getFiltros() {
        return {
            search:      $('#searchInput').val().trim(),
            tipo_bien:   $('#filtroTipo').val(),
            mes:         $('#filtroMes').val(),
            anio:        $('#filtroAnio').val(),
            fecha_desde: $('#filtroDesde').val(),
            fecha_hasta: $('#filtroHasta').val(),
        };
    }

    function buildQueryString() {
        const f = getFiltros();
        const p = new URLSearchParams();
        Object.entries(f).forEach(([k, v]) => { if (v) p.set(k, v); });
        return p.toString();
    }

    // Inicializar paginación
    actualizarPaginacion({ current_page: {{ $bajas->currentPage() }}, last_page: {{ $bajas->lastPage() }} }, '');

    // ─────────────────────────────────────
    // BÚSQUEDA Y FILTROS
    // ─────────────────────────────────────
    $('#searchInput').on('keyup', function () {
        clearTimeout(searchTimeout);
        paginaActual = 1;
        searchTimeout = setTimeout(() => buscar(), 400);
    });

    $('#btnFiltrar').on('click', function () {
        paginaActual = 1;
        buscar();
        actualizarEnlacesExport();
    });

    $('#filtroTipo, #filtroMes, #filtroAnio').on('change', function () {
        paginaActual = 1;
        buscar();
        actualizarEnlacesExport();
    });

    $('#btnLimpiar').on('click', function () {
        $('#searchInput').val('');
        paginaActual = 1;
        buscar();
    });

    $('#btnLimpiarFiltros, #btnMostrarTodo').on('click', function () {
        $('#searchInput').val('');
        $('#filtroTipo, #filtroMes, #filtroAnio').val('');
        $('#filtroDesde, #filtroHasta').val('');
        paginaActual = 1;
        buscar();
        actualizarEnlacesExport();
    });

    function actualizarEnlacesExport() {
        const qs = buildQueryString();
        $('#btnExportPdf').attr('href',   rutaExportPdf   + (qs ? '?' + qs : ''));
        $('#btnExportExcel').attr('href', rutaExportExcel + (qs ? '?' + qs : ''));
    }

    function buscar(page = paginaActual) {
        $('#loadingSearch').show();
        const params = { ...getFiltros(), page };

        $.ajax({
            url: rutaBajaIndex,
            method: 'GET',
            data: params,
            dataType: 'json',
            success: function (res) {
                actualizarTabla(res.data);
                actualizarContadores(res);
                actualizarPaginacion(res, '');
                $('#statTotal').text(res.total);
                if (res.total_mes     !== undefined) $('#statMes').text(res.total_mes);
                if (res.bienes_inactivos !== undefined) $('#statInactivos').text(res.bienes_inactivos);
                $('#loadingSearch').hide();

                if (res.resultados === 0) {
                    $('#tablaContainer, #paginacionContainer').hide();
                    $('#noResultados').fadeIn();
                } else {
                    $('#noResultados').hide();
                    $('#tablaContainer, #paginacionContainer').show();
                }
            },
            error: function () {
                $('#loadingSearch').hide();
                Swal.fire('Error', 'Error al buscar registros.', 'error');
            }
        });
    }

    // ─────────────────────────────────────
    // ACTUALIZAR TABLA
    // ─────────────────────────────────────
    function actualizarTabla(bajas) {
        const tbody = $('#tablaBajas');
        tbody.empty();
        if (!bajas || bajas.length === 0) return;

        bajas.forEach(b => {
            const fecha = b.fecha_baja
                ? new Date(b.fecha_baja + 'T00:00:00').toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'numeric' })
                : '—';
            const bien = b.bien || {};
            const motivo = b.motivo_baja && b.motivo_baja.length > 60
                ? b.motivo_baja.substring(0, 60) + '...'
                : (b.motivo_baja || '—');
            const resolucion = b.resolucion
                ? `<small class="text-success font-weight-bold">${b.resolucion}</small>`
                : `<span class="text-muted">—</span>`;
            const accionesCol = esAdmin
                ? `<td class="text-center"><button class="btn btn-sm btn-outline-info btn-ver-baja" data-id="${b.id_baja}" title="Ver detalle"><i class="fas fa-eye"></i></button></td>`
                : '';

            tbody.append(`
                <tr id="row-${b.id_baja}">
                    <td class="text-center">${b.id_baja}</td>
                    <td><code class="text-danger">${bien.codigo_patrimonial || '—'}</code></td>
                    <td>
                        <strong>${bien.denominacion_bien || '—'}</strong>
                        ${bien.marca_bien ? `<br><small class="text-muted">${bien.marca_bien}</small>` : ''}
                    </td>
                    <td><span class="badge badge-secondary">${bien.tipo_bien || '—'}</span></td>
                    <td>${fecha}</td>
                    <td title="${b.motivo_baja || ''}">${motivo}</td>
                    <td>${resolucion}</td>
                    ${accionesCol}
                </tr>
            `);
        });
    }

    function actualizarContadores(res) {
        $('#from').text(res.from || 0);
        $('#to').text(res.to || 0);
        $('#resultadosCount').text(res.resultados);
        $('#paginaInfo').text((res.from || 0) + ' - ' + (res.to || 0));
    }

    function actualizarPaginacion(res, termino) {
        const links = $('#paginacionLinks');
        links.empty();
        if (!res.last_page || res.last_page <= 1) return;

        let html = '<ul class="pagination pagination-sm m-0">';
        html += res.current_page > 1
            ? `<li class="page-item"><a class="page-link paginar" href="#" data-page="${res.current_page - 1}"><i class="fas fa-chevron-left"></i></a></li>`
            : `<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>`;

        for (let i = 1; i <= res.last_page; i++) {
            if (i === res.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (i === 1 || i === res.last_page || Math.abs(i - res.current_page) <= 2) {
                html += `<li class="page-item"><a class="page-link paginar" href="#" data-page="${i}">${i}</a></li>`;
            } else if (i === res.current_page - 3 || i === res.current_page + 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        html += res.current_page < res.last_page
            ? `<li class="page-item"><a class="page-link paginar" href="#" data-page="${res.current_page + 1}"><i class="fas fa-chevron-right"></i></a></li>`
            : `<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>`;

        html += '</ul>';
        links.html(html);

        $('#paginacionLinks').on('click', '.paginar', function (e) {
            e.preventDefault();
            paginaActual = parseInt($(this).data('page'));
            buscar(paginaActual);
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
    }

    // ─────────────────────────────────────
    // MODAL BUSCAR BIEN
    // ─────────────────────────────────────
    let buscarBienTimeout;

    function buscarBienes() {
        const q    = $('#buscarBienInput').val().trim();
        const tipo = $('#filtroBuscarTipo').val();

        $('#buscarBienSpinner').show();
        $('#resultadosBienBody').html(`
            <tr><td colspan="5" class="text-center py-2">
                <i class="fas fa-spinner fa-spin text-danger"></i> Buscando...
            </td></tr>
        `);

        $.ajax({
            url: rutaBuscarBienes,
            method: 'GET',
            data: { q, tipo },
            dataType: 'json',
            success: function (res) {
                $('#buscarBienSpinner').hide();
                const tbody = $('#resultadosBienBody');
                tbody.empty();

                if (!res.data || res.data.length === 0) {
                    tbody.html(`
                        <tr><td colspan="5" class="text-center text-muted py-3">
                            <i class="fas fa-search"></i> No se encontraron bienes activos disponibles
                        </td></tr>
                    `);
                    return;
                }

                res.data.forEach(b => {
                    tbody.append(`
                        <tr>
                            <td><code class="text-danger">${b.codigo_patrimonial}</code></td>
                            <td><strong>${b.denominacion_bien}</strong></td>
                            <td><small class="text-muted">${b.marca_bien || '—'}</small></td>
                            <td><span class="badge badge-secondary" style="font-size:0.7rem;">${b.tipo_bien || '—'}</span></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger btn-seleccionar-bien"
                                    data-id="${b.id}"
                                    data-codigo="${b.codigo_patrimonial}"
                                    data-denominacion="${b.denominacion_bien}"
                                    data-marca="${b.marca_bien || ''}"
                                    data-tipo="${b.tipo_bien || '—'}">
                                    <i class="fas fa-check"></i> Seleccionar
                                </button>
                            </td>
                        </tr>
                    `);
                });
            },
            error: function () {
                $('#buscarBienSpinner').hide();
                $('#resultadosBienBody').html(`
                    <tr><td colspan="5" class="text-center text-danger py-2">
                        <i class="fas fa-exclamation-circle"></i> Error al buscar bienes
                    </td></tr>
                `);
            }
        });
    }

    $('#buscarBienInput, #filtroBuscarTipo').on('input change', function () {
        clearTimeout(buscarBienTimeout);
        buscarBienTimeout = setTimeout(buscarBienes, 350);
    });

    // Cargar todos los bienes activos al abrir el modal
    $('#modalBuscarBien').on('shown.bs.modal', function () {
        $('#buscarBienInput').val('').focus();
        buscarBienes();
    });

    // Al seleccionar un bien: cerrar modal búsqueda, abrir modal baja
    $(document).on('click', '.btn-seleccionar-bien', function () {
        bienSeleccionadoId = $(this).data('id');
        const codigo       = $(this).data('codigo');
        const denominacion = $(this).data('denominacion');
        const marca        = $(this).data('marca');
        const tipo         = $(this).data('tipo');

        // Rellenar el modal de baja
        $('#baja_id_bien_hidden').val(bienSeleccionadoId);
        $('#baja_show_codigo').text(codigo);
        $('#baja_show_denominacion').text(denominacion);
        $('#baja_show_tipo').text(tipo);
        $('#baja_show_marca').text(marca ? '(' + marca + ')' : '');

        $('#modalBuscarBien').modal('hide');
        setTimeout(() => $('#modalRegistrarBaja').modal('show'), 300);
    });

    // Botón "Cambiar bien"
    $('#btnCambiarBien').on('click', function () {
        $('#modalRegistrarBaja').modal('hide');
        setTimeout(() => $('#modalBuscarBien').modal('show'), 300);
    });

    // ─────────────────────────────────────
    // REGISTRAR BAJA
    // ─────────────────────────────────────
    $('#baja_motivo').on('input', function () {
        $('#charCount').text($(this).val().length);
    });

    $('#formRegistrarBaja').on('submit', function (e) {
        e.preventDefault();

        if (!bienSeleccionadoId) {
            Swal.fire('Aviso', 'Debe seleccionar un bien primero.', 'warning');
            return;
        }

        $('.error-fecha_baja, .error-motivo_baja, .error-resolucion, .error-observacion').text('');
        const btn = $('#btnRegistrarBaja');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registrando...');

        $.ajax({
            url: rutaBajaStore,
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                btn.prop('disabled', false).html('<i class="fas fa-times-circle"></i> Registrar Baja');
                if (res.success) {
                    $('#modalRegistrarBaja').modal('hide');
                    limpiarFormBaja();
                    bienSeleccionadoId = null;
                    Swal.fire({
                        icon: 'success', title: '¡Baja Registrada!',
                        text: res.message, timer: 2500, showConfirmButton: false
                    }).then(() => buscar(1));
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-times-circle"></i> Registrar Baja');
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};
                    if (errors.fecha_baja)  $('.error-fecha_baja').text(errors.fecha_baja[0]);
                    if (errors.motivo_baja) $('.error-motivo_baja').text(errors.motivo_baja[0]);
                    if (errors.resolucion)  $('.error-resolucion').text(errors.resolucion[0]);
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo registrar la baja.', 'error');
                }
            }
        });
    });

    function limpiarFormBaja() {
        $('#formRegistrarBaja')[0].reset();
        $('#baja_id_bien_hidden').val('');
        $('#baja_show_codigo, #baja_show_denominacion, #baja_show_tipo').text('—');
        $('#baja_show_marca').text('');
        $('#charCount').text('0');
        $('.error-fecha_baja, .error-motivo_baja, .error-resolucion, .error-observacion').text('');
    }

    $('#modalRegistrarBaja').on('hidden.bs.modal', limpiarFormBaja);

    // ─────────────────────────────────────
    // VER DETALLE DE BAJA
    // ─────────────────────────────────────
    $(document).on('click', '.btn-ver-baja', function () {
        const id = $(this).data('id');
        $('#detalleBajaContenido').html(`
            <div class="text-center py-3">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2 text-muted">Cargando...</p>
            </div>
        `);
        $('#modalDetalleBaja').modal('show');

        $.ajax({
            url: '/baja/' + id,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.success || !res.data) return;
                const b    = res.data;
                const bien = b.bien || {};
                const fecha = b.fecha_baja
                    ? new Date(b.fecha_baja + 'T00:00:00').toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'numeric' })
                    : '—';

                $('#detalleBajaContenido').html(`
                    <table class="table table-sm table-bordered">
                        <tr><th class="bg-light" width="38%">Código Patrimonial</th>
                            <td><code class="text-danger">${bien.codigo_patrimonial || '—'}</code></td></tr>
                        <tr><th class="bg-light">Denominación</th>
                            <td><strong>${bien.denominacion_bien || '—'}</strong></td></tr>
                        <tr><th class="bg-light">Marca</th>
                            <td>${bien.marca_bien || '<span class="text-muted">—</span>'}</td></tr>
                        <tr><th class="bg-light">Tipo de Bien</th>
                            <td><span class="badge badge-secondary">${bien.tipo_bien || '—'}</span></td></tr>
                        <tr><th class="bg-light">Fecha de Baja</th><td>${fecha}</td></tr>
                        <tr><th class="bg-light">Motivo</th>
                            <td>${b.motivo_baja || '—'}</td></tr>
                        <tr><th class="bg-light">Resolución</th>
                            <td>${b.resolucion ? `<strong class="text-success">${b.resolucion}</strong>` : '<span class="text-muted">No registrada</span>'}</td></tr>
                        <tr><th class="bg-light">Observación</th>
                            <td>${b.observacion || '<span class="text-muted">—</span>'}</td></tr>
                        <tr><th class="bg-light">Registrado</th>
                            <td><small class="text-muted">${b.created_at || '—'}</small></td></tr>
                    </table>
                    <div class="alert alert-warning py-2 mb-0" style="font-size:0.8rem;">
                        <i class="fas fa-gavel"></i>
                        <strong>Normativa:</strong> Baja definitiva según Directiva N° 001-2015/SBN.
                        No puede revertirse. Si fue un error, registre un nuevo <strong>ALTA</strong>.
                    </div>
                `);
            },
            error: function () {
                $('#detalleBajaContenido').html('<div class="alert alert-danger">Error al cargar el detalle.</div>');
            }
        });
    });

});
</script>
@stop
