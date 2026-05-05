@extends('layouts.main')

@section('title', 'Módulos')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="mb-0"><i class="fas fa-cubes"></i> Módulos</h1>

    <div class="mt-2 mt-md-0 d-flex align-items-center">
      <button type="button" class="btn btn-danger mr-2" id="btnInactivarSeleccionados" style="display:none;">
        <i class="fas fa-ban"></i> Desactivar seleccionados (<span id="contadorSeleccionados">0</span>)
      </button>

      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">
        <i class="fas fa-plus"></i> Nuevo
      </button>
    </div>
  </div>
@stop

@section('content')
<div class="card">
  <div class="card-body">

    <div class="row mb-3 align-items-start">
      {{-- Mostrar + Estado --}}
      <div class="col-md-6">
        <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
          <div class="d-flex align-items-center">
            <span class="text-muted mr-2">Mostrar</span>

            <select id="perPage" class="form-control form-control-sm" style="width:auto;">
              @foreach([5,10,20,25,50,100] as $n)
                <option value="{{ $n }}" @selected((int)request('per_page', 10) === $n)>{{ $n }}</option>
              @endforeach
            </select>

            <span class="text-muted ml-2">registros</span>
          </div>

          <div class="d-flex align-items-center">
            <span class="text-muted mr-2">Estado</span>
            <select id="filtroEstado" class="form-control form-control-sm" style="width:auto;">
              <option value="A" @selected(($estado ?? 'A') === 'A')>Activos</option>
              <option value="I" @selected(($estado ?? 'A') === 'I')>Inactivos</option>
              <option value="ALL" @selected(($estado ?? 'A') === 'ALL')>Todos</option>
            </select>
          </div>
        </div>
      </div>

      {{-- Buscar + info --}}
      <div class="col-md-6">
        <div class="float-right" style="width: 100%; max-width: 560px;">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text bg-primary">
                <i class="fas fa-search text-white"></i>
              </span>
            </div>

            <input type="text" id="searchInput" class="form-control"
                   placeholder="Buscar por módulo, etiqueta, color, ícono, ruta o ID..." autocomplete="off">

            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" id="btnLimpiar">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <small class="text-muted mt-1 d-block text-right">
            <span id="infoResultados">
              Mostrando <strong id="from">{{ $items->firstItem() ?? 0 }}</strong>
              a <strong id="to">{{ $items->lastItem() ?? 0 }}</strong>
              de <strong id="resultadosCount">{{ $items->total() }}</strong>
              (<strong id="totalCount">{{ $items->total() }}</strong> total)
            </span>

            <span id="loadingSearch" style="display:none;">
              <i class="fas fa-spinner fa-spin text-primary"></i> Buscando...
            </span>
          </small>
        </div>
      </div>
    </div>

    {{-- TABLA --}}
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover" id="tablaModulos">
        <thead class="thead-dark">
          <tr>
            <th width="5%" class="text-center"><input type="checkbox" id="checkAll"></th>

            <th width="8%" class="text-center sortable" data-column="idmodulo">
              ID <i class="fas fa-sort sort-icon"></i>
            </th>

            <th class="sortable" data-column="nommodulo">
              Módulo <i class="fas fa-sort sort-icon"></i>
            </th>

            <th width="14%" class="sortable" data-column="etiqueta">
              Etiqueta <i class="fas fa-sort sort-icon"></i>
            </th>

            <th width="14%" class="text-center sortable" data-column="icono">
              Ícono <i class="fas fa-sort sort-icon"></i>
            </th>

            <th width="14%" class="sortable text-center" data-column="color">
              Color <i class="fas fa-sort sort-icon"></i>
            </th>

            <th width="20" class="sortable" data-column="route_prefix">
  Ruta prefix <i class="fas fa-sort sort-icon"></i>
</th>


            <th width="11%" class="text-center sortable" data-column="estadomodulo">
              Estado <i class="fas fa-sort sort-icon"></i>
            </th>
          </tr>
        </thead>

        <tbody id="tablaBody">
          <tr id="filaVacia">
            <td colspan="8" class="text-center text-muted py-4">
              <i class="fas fa-spinner fa-spin mr-1"></i> Cargando...
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div id="paginacionContainer" class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
      <div class="mb-2 mb-md-0">
        <small class="text-muted">
          Mostrando <strong id="paginaInfo">{{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }}</strong>
          de <strong id="totalFooter">{{ $items->total() }}</strong>
          <span class="ml-2 d-none d-md-inline">
            <i class="fas fa-info-circle"></i> Doble click en el módulo para editar
          </span>
        </small>
      </div>
      <div id="paginacionLinks"></div>
    </div>

    {{-- SIN RESULTADOS --}}
    <div id="noResultados" class="text-center py-4" style="display:none;">
      <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
      <h5>No se encontraron resultados</h5>
      <p class="text-muted">No hay módulos que coincidan con "<strong id="terminoBuscado"></strong>"</p>
      <button class="btn btn-outline-primary" id="btnMostrarTodo">
        <i class="fas fa-undo"></i> Mostrar todo
      </button>
    </div>

  </div>
</div>

{{-- MODAL CREATE --}}
<div class="modal fade" id="modalCreate" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Nuevo Módulo</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <form id="formCreate">
        @csrf
        <div class="modal-body">
          <div class="row">

            <div class="col-md-8">
              <div class="form-group">
                <label>Nombre del Módulo <span class="text-danger">*</span></label>
                <input type="text" name="nommodulo" id="nommodulo" class="form-control" maxlength="150" required>
                <span class="text-danger error-nommodulo"></span>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>Estado <span class="text-danger">*</span></label>
                <select name="estadomodulo" id="estadomodulo" class="form-control" required>
                  <option value="A" selected>Activo</option>
                  <option value="I">Inactivo</option>
                </select>
                <span class="text-danger error-estadomodulo"></span>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Etiqueta</label>
                <input type="text" name="etiqueta" id="etiqueta" class="form-control" maxlength="30" placeholder="Opcional">
                <span class="text-danger error-etiqueta"></span>
              </div>
            </div>

            {{-- ICONO (CREATE) --}}
            <div class="col-md-6">
              <div class="form-group">
                <label>Ícono (FontAwesome)</label>
                <select name="icono" id="icono" class="form-control">
  <option value="">-- Sin ícono --</option>

  <option value="fas fa-layer-group">Módulo default</option>
  <option value="fas fa-boxes">Bienes</option>
  <option value="fas fa-exchange-alt">Movimientos</option>   <!-- AQUI -->
  <option value="fas fa-file-alt">Documentos</option>
  <option value="fas fa-book">Catálogos</option>
  <option value="fas fa-chart-bar">Reportes</option>
  <option value="fas fa-shield-alt">Seguridad</option>
  <option value="fas fa-cog">Configuración</option>
  <option value="fas fa-users">Usuarios</option>
</select>

                <small class="text-muted d-block mt-1">Se guarda como texto (clase).</small>
                <span class="text-danger error-icono"></span>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label>Ruta (prefix)</label>
                <select name="route_prefix" id="route_prefix" class="form-control">
  <option value="">-- Opcional: Selecciona un prefix --</option>
  @foreach(($routePrefixes ?? []) as $p)
    <option value="{{ $p }}">{{ $p }}</option>
  @endforeach
</select>
<span class="text-danger error-route_prefix"></span>


                <span class="text-danger error-route_prefix"></span>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Color</label>

                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text p-0" style="background:#fff;">
                      <input type="color" id="color_picker" class="color-picker-input" value="#0d6efd">
                    </span>
                  </div>

                  <input type="text" name="color" id="color" class="form-control"
                         maxlength="12" placeholder="#RRGGBB (opcional)">

                  <div class="input-group-append">
                    <span class="input-group-text color-preview" id="color_preview">-</span>
                  </div>
                </div>

                <span class="text-danger error-color"></span>
              </div>
            </div>

            {{-- PREVIEW ICONO (CREATE) --}}
            <div class="col-md-6">
              <div class="form-group">
                <label>Vista previa</label>
                <div class="border rounded p-3 bg-light d-flex align-items-center" style="min-height:58px;">
                  <i id="icon_preview" class="mr-2 fas fa-layer-group" aria-hidden="true"></i>
                  <span id="icon_preview_text" class="text-muted">fas fa-layer-group</span>
                </div>
                <small class="text-muted d-block mt-1">Así se verá en el sidebar.</small>
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

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Módulo</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <form id="formEdit">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit_id">

        <div class="modal-body">
          <div class="row">

            <div class="col-md-8">
              <div class="form-group">
                <label>Nombre del Módulo <span class="text-danger">*</span></label>
                <input type="text" name="nommodulo" id="edit_nommodulo" class="form-control" maxlength="150" required>
                <span class="text-danger error-edit-nommodulo"></span>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>Estado <span class="text-danger">*</span></label>
                <select name="estadomodulo" id="edit_estadomodulo" class="form-control" required>
                  <option value="A">Activo</option>
                  <option value="I">Inactivo</option>
                </select>
                <span class="text-danger error-edit-estadomodulo"></span>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Etiqueta</label>
                <input type="text" name="etiqueta" id="edit_etiqueta" class="form-control" maxlength="30" placeholder="Opcional">
                <span class="text-danger error-edit-etiqueta"></span>
              </div>
            </div>

            {{-- ICONO (EDIT) --}}
            <div class="col-md-6">
              <div class="form-group">
                <label>Ícono (FontAwesome)</label>
                <select name="icono" id="editicono" class="form-control">
  <option value="">-- Sin ícono --</option>

  <option value="fas fa-layer-group">Módulo default</option>
  <option value="fas fa-boxes">Bienes</option>
  <option value="fas fa-exchange-alt">Movimientos</option>   <!-- AQUI -->
  <option value="fas fa-file-alt">Documentos</option>
  <option value="fas fa-book">Catálogos</option>
  <option value="fas fa-chart-bar">Reportes</option>
  <option value="fas fa-shield-alt">Seguridad</option>
  <option value="fas fa-cog">Configuración</option>
  <option value="fas fa-users">Usuarios</option>
</select>
                <span class="text-danger error-edit-icono"></span>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label>Ruta (prefix)</label>
                <select name="route_prefix" id="edit_route_prefix" class="form-control">
  <option value="">-- Opcional: Selecciona un prefix --</option>
  @foreach(($routePrefixes ?? []) as $p)
    <option value="{{ $p }}">{{ $p }}</option>
  @endforeach
</select>
<span class="text-danger error-edit-route_prefix"></span>


                <span class="text-danger error-edit-route_prefix"></span>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Color</label>

                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text p-0" style="background:#fff;">
                      <input type="color" id="edit_color_picker" class="color-picker-input" value="#0d6efd">
                    </span>
                  </div>

                  <input type="text" name="color" id="edit_color" class="form-control"
                         maxlength="12" placeholder="#RRGGBB (opcional)">

                  <div class="input-group-append">
                    <span class="input-group-text color-preview" id="edit_color_preview">-</span>
                  </div>
                </div>

                <span class="text-danger error-edit-color"></span>
              </div>
            </div>

            {{-- PREVIEW ICONO (EDIT) --}}
            <div class="col-md-6">
              <div class="form-group">
                <label>Vista previa</label>
                <div class="border rounded p-3 bg-light d-flex align-items-center" style="min-height:58px;">
                  <i id="edit_icon_preview" class="mr-2 fas fa-layer-group" aria-hidden="true"></i>
                  <span id="edit_icon_preview_text" class="text-muted">fas fa-layer-group</span>
                </div>
                <small class="text-muted d-block mt-1">Así se verá en el sidebar.</small>
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
@stop

@section('css')
<style>
  .sortable{ cursor:pointer; transition:.2s; }
  .sortable:hover{ background:#495057 !important; color:#fff; }
  .sort-icon{ font-size:.8rem; margin-left:5px; }
  .editable-cell{ user-select:none; cursor:pointer; }
  .editable-cell:hover{ background:#e3f2fd !important; font-weight:bold; }

  .color-picker-input{
    width: 44px;
    height: 36px;
    border: none;
    padding: 0;
    margin: 0;
    background: transparent;
    cursor: pointer;
  }
  .color-preview{
    min-width: 110px;
    justify-content: center;
    font-weight: 700;
    border-left: 0;
  }
  .chip-color{
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid rgba(0,0,0,.12);
    font-weight: 700;
    font-size: .8rem;
    line-height: 1;
    user-select: none;
    max-width: 170px;
    justify-content: center;
  }
  .chip-dot{
    width: 12px;
    height: 12px;
    border-radius: 999px;
    border: 1px solid rgba(0,0,0,.20);
    background: currentColor;
  }
  .fa-icon-cell{ white-space: nowrap; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

  // ==================== CSRF ====================
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

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

  // ==================== Selectores ====================
  const $perPage = $('#perPage');
  const $filtroEstado = $('#filtroEstado');
  const $searchInput = $('#searchInput');
  const $btnLimpiar = $('#btnLimpiar');
  const $btnMostrarTodo = $('#btnMostrarTodo');

  const $tablaModulos = $('#tablaModulos');
  const $tablaBody = $('#tablaBody');

  const $paginacionContainer = $('#paginacionContainer');
  const $paginacionLinks = $('#paginacionLinks');

  const $infoResultados = $('#infoResultados');
  const $loadingSearch = $('#loadingSearch');
  const $noResultados = $('#noResultados');
  const $terminoBuscado = $('#terminoBuscado');

  const $from = $('#from');
  const $to = $('#to');
  const $resultadosCount = $('#resultadosCount');
  const $totalCount = $('#totalCount');
  const $totalFooter = $('#totalFooter');
  const $paginaInfo = $('#paginaInfo');

  const $checkAll = $('#checkAll');
  const $btnInactivarSeleccionados = $('#btnInactivarSeleccionados');

  // Create modal
  const $modalCreate = $('#modalCreate');
  const $formCreate = $('#formCreate');
  const $btnGuardar = $('#btnGuardar');

  const $icono = $('#icono');
  const $iconPreview = $('#iconpreview');
  const $iconPreviewText = $('#iconpreviewtext');

  const $color = $('#color');
  const $colorPicker = $('#colorpicker');
  const $colorPreview = $('#colorpreview-span');

  const $routePrefix = $('#route_prefix'); // <-- route_prefix

  // Edit modal
  const $modalEdit = $('#modalEdit');
  const $formEdit = $('#formEdit');
  const $btnActualizar = $('#btnActualizar');

  const $editId = $('#edit_id');
  const $editNomModulo = $('#edit_nommodulo');
  const $editEstadoModulo = $('#edit_estadomodulo');
  const $editEtiqueta = $('#edit_etiqueta');

  const $editIcono = $('#editicono');
  const $editIconPreview = $('#edit_icon_preview');
  const $editIconPreviewText = $('#edit_icon_preview_text');

  const $editColor = $('#edit_color');
  const $editColorPicker = $('#edit_color_picker');
  const $editColorPreview = $('#edit_color_preview');

  const $editRoutePrefix = $('#edit_route_prefix'); // <-- edit_route_prefix

  // ==================== Estado global UI ====================
  let paginaActual = 1;
  let ordenActual = { columna: 'idmodulo', direccion: 'asc' };
  let terminoBusqueda = '';
  let perPage = parseInt($perPage.val() || '10', 10);
  let estadoActual = String($filtroEstado.val() || 'A').toUpperCase();

  const COLUMNMAP = {
    idmodulo: 'idmodulo',
    nommodulo: 'nommodulo',
    etiqueta: 'etiqueta',
    icono: 'icono',
    color: 'color',
    route_prefix: 'route_prefix', // <-- CORRECTO
    estadomodulo: 'estadomodulo',
  };

  // ==================== Helpers seguridad UI ====================
  function escapeHtml(text) {
    return String(text ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function safeText(v) {
    const s = (v === null || v === undefined) ? '' : String(v).trim();
    return s ? escapeHtml(s) : '<span class="text-muted">-</span>';
  }

  // ==================== Helpers color ====================
  function normalizeHex(v) {
    v = (v ?? '').toString().trim();
    if (!v) return '';
    if (v[0] !== '#') v = '#' + v;
    const m = v.match(/^#[0-9a-fA-F]{6}$/);
    return m ? v.toUpperCase() : v.toUpperCase();
  }

  function isValidHex6(v) {
    return /^#[0-9A-F]{6}$/.test(normalizeHex(v));
  }

  function hexToRgb(hex) {
    const h = normalizeHex(hex);
    if (!isValidHex6(h)) return null;
    const n = parseInt(h.slice(1), 16);
    return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
  }

  function idealTextColor(hex) {
    const rgb = hexToRgb(hex);
    if (!rgb) return '#111';
    const yiq = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
    return yiq >= 150 ? '#111' : '#fff';
  }

  function applyColorPreview(inputHex, $preview) {
    const hex = normalizeHex(inputHex);
    if (isValidHex6(hex)) {
      $preview.text(hex);
      $preview.css({ backgroundColor: hex, color: idealTextColor(hex), borderColor: 'rgba(0,0,0,.15)' });
    } else if (!hex) {
      $preview.text('-');
      $preview.css({ backgroundColor: '#f8f9fa', color: '#6c757d', borderColor: 'rgba(0,0,0,.15)' });
    } else {
      $preview.text(hex);
      $preview.css({ backgroundColor: '#f8f9fa', color: '#dc3545', borderColor: 'rgba(0,0,0,.15)' });
    }
  }

  function wireColorControls($text, $picker, $preview) {
    applyColorPreview($text.val(), $preview);

    $text.on('input', function () {
      const hex = normalizeHex($text.val());
      applyColorPreview(hex, $preview);
      if (isValidHex6(hex)) $picker.val(hex.toLowerCase());
    });

    $picker.on('input change', function () {
      const hex = normalizeHex($picker.val());
      $text.val(hex);
      applyColorPreview(hex, $preview);
    });
  }

  wireColorControls($color, $colorPicker, $colorPreview);
  wireColorControls($editColor, $editColorPicker, $editColorPreview);

  // ==================== Helpers icono ====================
  function normalizeIconClass(v) {
    return (v ?? '').toString().trim().replace(/\s+/g, ' ');
  }

  function applyIconPreview(iconClass, $icon, $text) {
    const cls = normalizeIconClass(iconClass);
    const finalCls = cls ? cls : 'fas fa-layer-group';
    $icon.attr('class', 'mr-2 ' + finalCls);
    $text.text(finalCls);
  }

  $icono.on('change', function () {
    applyIconPreview($(this).val(), $iconPreview, $iconPreviewText);
  });
  $editIcono.on('change', function () {
    applyIconPreview($(this).val(), $editIconPreview, $editIconPreviewText);
  });

  applyIconPreview($icono.val(), $iconPreview, $iconPreviewText);

  // ==================== UI general ====================
  $perPage.on('change', function () {
    perPage = parseInt($(this).val() || '10', 10);
    paginaActual = 1;
    buscar(terminoBusqueda, paginaActual);
  });

  $filtroEstado.on('change', function () {
    estadoActual = String($(this).val() || 'A').toUpperCase();
    paginaActual = 1;
    $checkAll.prop('checked', false);
    $('.checkbox-item').prop('checked', false);
    actualizarBotonAccionMasiva();
    buscar(terminoBusqueda, paginaActual);
  });

  let searchTimeout = null;
  $searchInput.on('keyup', function () {
    terminoBusqueda = $(this).val().trim();
    clearTimeout(searchTimeout);
    paginaActual = 1;

    if (terminoBusqueda.length === 0 || terminoBusqueda.length >= 2) {
      searchTimeout = setTimeout(function () {
        buscar(terminoBusqueda, paginaActual);
      }, 400);
    }
  });

  function mostrarCargando(mostrar) {
    if (mostrar) {
      $loadingSearch.show();
      $infoResultados.hide();
    } else {
      $loadingSearch.hide();
      $infoResultados.show();
    }
  }

  function mostrarSinResultados(termino) {
    $tablaModulos.hide();
    $paginacionContainer.hide();
    $terminoBuscado.text(termino);
    $noResultados.fadeIn(150);
  }

  function ocultarSinResultados() {
    $noResultados.hide();
    $tablaModulos.show();
    $paginacionContainer.show();
  }

  $btnLimpiar.on('click', function () {
    $searchInput.val('');
    terminoBusqueda = '';
    paginaActual = 1;
    ordenActual = { columna: 'idmodulo', direccion: 'asc' };
    actualizarIconosOrdenamiento();
    buscar('', 1);
  });

  $btnMostrarTodo.on('click', function () {
    $searchInput.val('');
    terminoBusqueda = '';
    paginaActual = 1;
    ordenActual = { columna: 'idmodulo', direccion: 'asc' };
    actualizarIconosOrdenamiento();
    buscar('', 1);
  });

  // ==================== Tabla ====================
  function badgeEstado(valor) {
    const v = String(valor ?? '').toUpperCase();
    if (v === 'A') return '<span class="badge badge-success">Activo</span>';
    return '<span class="badge badge-secondary">Inactivo</span>';
  }

  function chipColor(valor) {
    const hex = normalizeHex(valor);
    if (!hex) return '<span class="text-muted">-</span>';
    if (!isValidHex6(hex)) {
      return `<span class="badge badge-light" style="border:1px solid rgba(0,0,0,.15)">${escapeHtml(hex)}</span>`;
    }
    const txt = idealTextColor(hex);
    const dot = txt;
    return `
      <span class="chip-color" style="background:${hex};color:${txt}">
        <span class="chip-dot" style="color:${dot}"></span>
        ${escapeHtml(hex)}
      </span>`;
  }

  function iconCell(iconClass) {
    const cls = normalizeIconClass(iconClass);
    if (!cls) return '<span class="text-muted">-</span>';
    return `<span class="fa-icon-cell"><i class="${escapeHtml(cls)}" aria-hidden="true"></i> <span class="text-muted ml-1">${escapeHtml(cls)}</span></span>`;
  }

  function actualizarTabla(items) {
    $tablaBody.empty();

    if (!items || !items.length) {
      $tablaBody.append(`
        <tr id="filaVacia">
          <td colspan="8" class="text-center text-muted py-4">
            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
            No hay módulos registrados
          </td>
        </tr>
      `);
      $checkAll.prop('checked', false).prop('disabled', true);
      actualizarBotonAccionMasiva();
      return;
    }

    $checkAll.prop('disabled', false);

    items.forEach(function (m) {
      const id = m.idmodulo;
      $tablaBody.append(`
        <tr id="row-${escapeHtml(id)}">
          <td class="text-center">
            <input type="checkbox" class="checkbox-item" value="${escapeHtml(id)}">
          </td>
          <td class="text-center"><strong>${escapeHtml(id)}</strong></td>
          <td class="editable-cell" data-id="${escapeHtml(id)}" title="Doble click para editar">
            <strong>${escapeHtml(String(m.nommodulo ?? '').toUpperCase())}</strong>
          </td>
          <td>${safeText(m.etiqueta)}</td>
          <td>${iconCell(m.icono)}</td>
          <td class="text-center">${chipColor(m.color)}</td>
          <td>${safeText(m.route_prefix)}</td> <!-- <-- CORRECTO -->
          <td class="text-center">${badgeEstado(m.estadomodulo)}</td>
        </tr>
      `);
    });

    $checkAll.prop('checked', false);
    actualizarBotonAccionMasiva();
  }

  function actualizarContadores(res) {
    $from.text(res.from ?? 0);
    $to.text(res.to ?? 0);
    $resultadosCount.text(res.resultados ?? 0);
    $totalCount.text(res.total ?? 0);
    $totalFooter.text(res.total ?? 0);
    $paginaInfo.text(`${res.from ?? 0} - ${res.to ?? 0}`);
  }

  function generarBtn(activo, pagina, contenido) {
    if (activo) {
      return `<li class="page-item"><a class="page-link paginar" href="#" data-page="${pagina}">${contenido}</a></li>`;
    }
    return `<li class="page-item disabled"><span class="page-link">${contenido}</span></li>`;
  }

  function actualizarPaginacion(res) {
    $paginacionLinks.empty();

    const last = res.last_page ?? 1;
    const current = res.current_page ?? 1;

    if (!last || last <= 1) return;

    let html = `<ul class="pagination pagination-sm m-0">`;
    html += generarBtn(current > 1, current - 1, `<i class="fas fa-chevron-left"></i>`);

    const rango = 2;
    for (let i = 1; i <= last; i++) {
      const esActual = i === current;
      const esPrimera = i === 1;
      const esUltima = i === last;
      const cerca = Math.abs(i - current) <= rango;

      if (esActual) {
        html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
      } else if (esPrimera || esUltima || cerca) {
        html += `<li class="page-item"><a class="page-link paginar" href="#" data-page="${i}">${i}</a></li>`;
      } else if (i === current - rango - 1 || i === current + rango + 1) {
        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      }
    }

    html += generarBtn(current < last, current + 1, `<i class="fas fa-chevron-right"></i>`);
    html += `</ul>`;

    $paginacionLinks.html(html);
  }

  $(document).on('click', '.paginar', function (e) {
    e.preventDefault();
    paginaActual = parseInt($(this).data('page') || '1', 10);
    buscar(terminoBusqueda, paginaActual);
    $('html, body').animate({ scrollTop: 0 }, 300);
  });

  // ==================== Ordenamiento ====================
  function actualizarIconosOrdenamiento() {
    $('.sortable .sort-icon')
      .removeClass('fa-sort-up fa-sort-down')
      .addClass('fa-sort');

    const th = Object.keys(COLUMNMAP).find(k => COLUMNMAP[k] === ordenActual.columna) || ordenActual.columna;
    const $icono = $(`.sortable[data-column="${th}"] .sort-icon`);

    $icono.removeClass('fa-sort')
      .addClass(ordenActual.direccion === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
  }

  $(document).on('click', '.sortable', function () {
    const colUI = $(this).data('column');
    const colReal = COLUMNMAP[colUI] || colUI;

    if (ordenActual.columna === colReal) {
      ordenActual.direccion = (ordenActual.direccion === 'asc') ? 'desc' : 'asc';
    } else {
      ordenActual.columna = colReal;
      ordenActual.direccion = 'asc';
    }

    actualizarIconosOrdenamiento();
    paginaActual = 1;
    buscar(terminoBusqueda, paginaActual);
  });

  // ==================== Buscar AJAX ====================
  function buscar(termino, page = 1) {
    mostrarCargando(true);

    $.ajax({
      url: "{{ route('modulo.index') }}",
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      data: {
        search: termino,
        page: page,
        per_page: perPage,
        orden: ordenActual.columna,
        direccion: ordenActual.direccion,
        estado: estadoActual
      },
      dataType: 'json',
      success: function (res) {
        actualizarTabla(res.data || []);
        actualizarContadores(res);
        actualizarPaginacion(res);
        mostrarCargando(false);

        if ((res.total ?? 0) === 0 || (res.resultados ?? 0) === 0) {
          mostrarSinResultados(termino);
        } else {
          ocultarSinResultados();
        }
      },
      error: function (xhr) {
        mostrarCargando(false);
        console.log('INDEX ERROR', xhr.status, xhr.responseText);
        Toast.fire({ icon: 'error', title: 'Error al cargar datos', text: 'HTTP ' + xhr.status });
      }
    });
  }

  // ==================== Checkboxes ====================
  $checkAll.on('change', function () {
    $('.checkbox-item').prop('checked', $(this).is(':checked'));
    actualizarBotonAccionMasiva();
  });

  $(document).on('change', '.checkbox-item', function () {
    actualizarBotonAccionMasiva();
  });

  function actualizarBotonAccionMasiva() {
    const total = $('.checkbox-item').length;
    const checked = $('.checkbox-item:checked').length;

    $checkAll.prop('checked', total > 0 && total === checked);

    const esInactivos = (estadoActual === 'I');
    if (checked > 0) {
      $btnInactivarSeleccionados.fadeIn(150);
    } else {
      $btnInactivarSeleccionados.fadeOut(150);
    }

    $btnInactivarSeleccionados
      .toggleClass('btn-danger', !esInactivos)
      .toggleClass('btn-success', esInactivos)
      .html(esInactivos
        ? `<i class="fas fa-check"></i> Activar seleccionados <span id="contadorSeleccionados">${checked}</span>`
        : `<i class="fas fa-ban"></i> Desactivar seleccionados <span id="contadorSeleccionados">${checked}</span>`
      );
  }

  // ==================== Bulk activar/desactivar ====================
  function bulkInactivar(ids) {
    $.ajax({
      url: "{{ route('modulo.bulk-destroy') }}",
      type: 'POST',
      dataType: 'json',
      processData: false,
      contentType: 'application/json; charset=utf-8',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      data: JSON.stringify({ _method: 'DELETE', ids: ids.map(Number) }),
      success: function (res) {
        Toast.fire({ icon: 'success', title: res.message || 'Registros desactivados' });
        $checkAll.prop('checked', false);
        setTimeout(() => window.location.reload(), 1500);
      },
      error: function (xhr) {
        console.log('BULK DESACTIVAR ERROR', xhr.status, xhr.responseText, xhr.responseJSON);
        Toast.fire({ icon: 'error', title: 'Error al desactivar', text: 'HTTP ' + xhr.status });
      }
    });
  }

  function bulkActivar(ids) {
    $.ajax({
      url: "{{ route('modulo.bulk-restore') }}",
      type: 'POST',
      dataType: 'json',
      processData: false,
      contentType: 'application/json; charset=utf-8',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      data: JSON.stringify({ ids: ids.map(Number) }),
      success: function (res) {
        Toast.fire({ icon: 'success', title: res.message || 'Registros activados' });
        $checkAll.prop('checked', false);
        setTimeout(() => window.location.reload(), 1500);
      },
      error: function (xhr) {
        console.log('BULK ACTIVAR ERROR', xhr.status, xhr.responseText, xhr.responseJSON);
        if (xhr.status === 422) console.log('errors', xhr.responseJSON?.errors);
        Toast.fire({ icon: 'error', title: 'Error al activar', text: 'HTTP ' + xhr.status });
      }
    });
  }

  $btnInactivarSeleccionados.on('click', function () {
    const ids = $('.checkbox-item:checked').map(function () { return $(this).val(); }).get();
    if (!ids.length) return;

    const esRestore = (estadoActual === 'I');

    Swal.fire({
      title: esRestore ? `¿Activar ${ids.length} módulos?` : `¿Desactivar ${ids.length} módulos?`,
      html: esRestore
        ? 'Los módulos quedarán <b>Activos</b>.'
        : 'Los módulos quedarán <b>Inactivos</b> (no se borran).',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: esRestore ? '#28a745' : '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: esRestore ? '<i class="fas fa-check"></i> Sí, activar' : '<i class="fas fa-ban"></i> Sí, desactivar',
      cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
    }).then((result) => {
      if (!result.isConfirmed) return;
      if (esRestore) bulkActivar(ids);
      else bulkInactivar(ids);
    });
  });

  // ==================== Editar (doble click) ====================
  $(document).on('dblclick', '.editable-cell', function () {
    const id = $(this).data('id');

    $.get(`{{ url('modulo') }}/${id}/edit`, function (data) {
      $editId.val(data.idmodulo);
      $editNomModulo.val(data.nommodulo);
      $editEstadoModulo.val(String(data.estadomodulo || 'A'));
      $editEtiqueta.val(data.etiqueta);

      $editRoutePrefix.val(data.route_prefix); // <-- CORRECTO

      $editIcono.val(data.icono);
      applyIconPreview(data.icono, $editIconPreview, $editIconPreviewText);

      const hex = normalizeHex(data.color);
      $editColor.val(hex);
      if (isValidHex6(hex)) $editColorPicker.val(hex.toLowerCase());
      applyColorPreview(hex, $editColorPreview);

      $modalEdit.modal('show');
    }).fail(function () {
      Toast.fire({ icon: 'error', title: 'No se pudo cargar el módulo' });
    });
  });

  // ==================== Crear ====================
  function clearCreateErrors() {
    $('.error-nommodulo,.error-estadomodulo,.error-etiqueta,.error-color,.error-icono,.error-route_prefix').text('');
  }

  $formCreate.on('submit', function (e) {
    e.preventDefault();
    clearCreateErrors();

    $btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $color.val(normalizeHex($color.val()));

    $.ajax({
      url: "{{ route('modulo.store') }}",
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      success: function (res) {
        $btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        if (res.success) {
          $modalCreate.modal('hide');
          Toast.fire({ icon: 'success', title: res.message || 'Registrado' });
          setTimeout(() => window.location.reload(), 1500);
        }
      },
      error: function (xhr) {
        $btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        console.log('STORE ERROR', xhr.status, xhr.responseText);

        if (xhr.status === 422) {
          const errors = xhr.responseJSON?.errors || {};
          Object.keys(errors).forEach(function (campo) {
            $(`.error-${campo}`).text(errors[campo][0]);
          });
          return;
        }
        Toast.fire({ icon: 'error', title: 'Error al guardar', text: 'HTTP ' + xhr.status });
      }
    });
  });

  // ==================== Actualizar ====================
  function clearEditErrors() {
    $('.error-edit-nommodulo,.error-edit-estadomodulo,.error-edit-etiqueta,.error-edit-color,.error-edit-icono,.error-edit-route_prefix').text('');
  }

  $formEdit.on('submit', function (e) {
    e.preventDefault();
    clearEditErrors();

    const id = $editId.val();
    $btnActualizar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

    $editColor.val(normalizeHex($editColor.val()));

    $.ajax({
      url: `{{ url('modulo') }}/${id}`,
      method: 'PUT',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      success: function (res) {
        $btnActualizar.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');
        if (res.success) {
          $modalEdit.modal('hide');
          Toast.fire({ icon: 'success', title: res.message || 'Actualizado' });
          setTimeout(() => window.location.reload(), 1500);
        }
      },
      error: function (xhr) {
        $btnActualizar.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');
        console.log('UPDATE ERROR', xhr.status, xhr.responseText);

        if (xhr.status === 422) {
          const errors = xhr.responseJSON?.errors || {};
          Object.keys(errors).forEach(function (campo) {
            $(`.error-edit-${campo}`).text(errors[campo][0]);
          });
          return;
        }

        Toast.fire({ icon: 'error', title: 'Error al actualizar', text: 'HTTP ' + xhr.status });
      }
    });
  });

  // ==================== Limpiar modales ====================
  $modalCreate.on('hidden.bs.modal', function () {
    $formCreate[0].reset();
    clearCreateErrors();

    $colorPicker.val('#0d6efd');
    $color.val('');
    applyColorPreview('', $colorPreview);

    $icono.val('');
    applyIconPreview($icono.val(), $iconPreview, $iconPreviewText);

    $routePrefix.val('');
  });

  $modalEdit.on('hidden.bs.modal', function () {
    $formEdit[0].reset();
    clearEditErrors();

    $editColorPicker.val('#0d6efd');
    $editColor.val('');
    applyColorPreview('', $editColorPreview);

    $editIcono.val('');
    applyIconPreview($editIcono.val(), $editIconPreview, $editIconPreviewText);

    $editRoutePrefix.val('');
  });

  // ==================== Inicial ====================
  actualizarIconosOrdenamiento();
  buscar('', 1);

});
</script>
@stop
