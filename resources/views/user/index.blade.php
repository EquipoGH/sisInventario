@extends('layouts.main')

@section('title', 'Usuarios')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="mb-0"><i class="fas fa-users"></i> Usuarios</h1>

    <div class="mt-2 mt-md-0 d-flex align-items-center">
      <button type="button" class="btn btn-danger mr-2" id="btnEliminarSeleccionados" style="display:none;">
        <i class="fas fa-trash-alt"></i> Eliminar (<span id="contadorSeleccionados">0</span>)
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

    {{-- FILTROS --}}
    <div class="p-2 mb-3" style="background:#f8f9fa;border:1px solid rgba(0,0,0,.08);border-radius:.35rem;">
      <div class="form-row align-items-end">
        <div class="col-md-3 col-12 mb-2">
          <label class="text-muted mb-1">Rol</label>
          <select id="filterRol" class="form-control form-control-sm">
            <option value="">Todos</option>
            <option value="ADMIN">ADMIN</option>
            <option value="USUARIO">USUARIO</option>
            <option value="INVITADO">INVITADO</option>
          </select>
        </div>

        <div class="col-md-3 col-12 mb-2">
          <label class="text-muted mb-1">Estado</label>
          <select id="filterEstado" class="form-control form-control-sm">
            <option value="">Todos</option>
            <option value="A">Activo</option>
            <option value="I">Inactivo</option>
          </select>
        </div>

        <div class="col-md-3 col-12 mb-2">
          <label class="text-muted mb-1">Último acceso</label>
          <select id="filterUltimo" class="form-control form-control-sm">
            <option value="">Todos</option>
            <option value="hoy">Hoy</option>
            <option value="7d">Últimos 7 días</option>
            <option value="30d">Últimos 30 días</option>
            <option value="nunca">Nunca (sin login)</option>
          </select>
        </div>

        <div class="col-md-3 col-12 mb-2 d-flex">
          <button type="button" class="btn btn-sm btn-primary mr-2 flex-fill" id="btnAplicarFiltros">
            <i class="fas fa-filter"></i> Aplicar
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="btnLimpiarFiltros">
            <i class="fas fa-undo"></i> Limpiar
          </button>
        </div>
      </div>
    </div>

    <div class="row mb-3 align-items-start">
      {{-- Mostrar --}}
      <div class="col-md-4">
        <div class="d-flex align-items-center">
          <span class="text-muted mr-2">Mostrar</span>

          <select id="perPage" class="form-control form-control-sm" style="width:auto;">
            @foreach([5,10,20,25,50,100] as $n)
              <option value="{{ $n }}" @selected((int)request('per_page', 10) === $n)>{{ $n }}</option>
            @endforeach
          </select>

          <span class="text-muted ml-2">registros</span>
        </div>
      </div>

      {{-- Buscar + info --}}
      <div class="col-md-8">
        <div class="float-right" style="width: 100%; max-width: 560px;">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text bg-primary">
                <i class="fas fa-search text-white"></i>
              </span>
            </div>

            <input type="text" id="searchInput" class="form-control"
                   placeholder="Buscar por nombre, email, DNI, rol o ID..." autocomplete="off">

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
      <table class="table table-bordered table-striped table-hover" id="tablaUsers">
        <thead class="thead-dark">
          <tr>
            <th width="4%" class="text-center"><input type="checkbox" id="checkAll"></th>

            <th width="7%" class="text-center sortable" data-column="id">
              ID <i class="fas fa-sort sort-icon"></i>
            </th>

            <th class="sortable" data-column="nombre">
              Nombre <i class="fas fa-sort sort-icon"></i>
            </th>

            <th class="sortable" data-column="email">
              Email <i class="fas fa-sort sort-icon"></i>
            </th>

            <th width="10%" class="sortable" data-column="dni">
              DNI <i class="fas fa-sort sort-icon"></i>
            </th>

            <th width="10%" class="sortable" data-column="rol">
              Rol <i class="fas fa-sort sort-icon"></i>
            </th>

            <th width="10%" class="text-center sortable" data-column="estado">
              Estado <i class="fas fa-sort sort-icon"></i>
            </th>

            <th width="14%" class="text-center sortable" data-column="ultimo">
              Último acceso <i class="fas fa-sort sort-icon"></i>
            </th>
            <th width="6%" class="text-center">ACCIONES</th>

          </tr>
        </thead>

        <tbody id="tablaBody">
          <tr id="filaVacia">
            <td colspan="9" class="text-center text-muted py-4">
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
            <i class="fas fa-info-circle"></i> Doble click en el usuario para editar
          </span>
        </small>
      </div>
      <div id="paginacionLinks"></div>
    </div>

    {{-- SIN RESULTADOS --}}
    <div id="noResultados" class="text-center py-4" style="display:none;">
      <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
      <h5>No se encontraron resultados</h5>
      <p class="text-muted">No hay usuarios que coincidan con "<strong id="terminoBuscado"></strong>"</p>
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
        <h5 class="modal-title"><i class="fas fa-user-plus"></i> Nuevo Usuario</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <form id="formCreate">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Nombre <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control" required>
                <span class="text-danger error-name"></span>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control" required>
                <span class="text-danger error-email"></span>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>DNI</label>
                <input
                  type="text"
                  name="dni_usuario"
                  id="dni_usuario"
                  class="form-control dni-only"
                  inputmode="numeric"
                  maxlength="8"
                  pattern="\d{8}"
                  title="Debe contener exactamente 8 dígitos numéricos"
                  placeholder="12345678"
                  autocomplete="off"
                >
                <small class="text-muted">Solo números, 8 dígitos.</small>
                <span class="text-danger error-dni_usuario"></span>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>Rol <span class="text-danger">*</span></label>
                <select name="rol_usuario" id="rol_usuario" class="form-control" required>
                  <option value="ADMIN">ADMIN</option>
                  <option value="USUARIO" selected>USUARIO</option>
                  <option value="INVITADO">INVITADO</option>
                </select>
                <span class="text-danger error-rol_usuario"></span>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>Estado <span class="text-danger">*</span></label>
                <select name="estado_usuario" id="estado_usuario" class="form-control" required>
                  <option value="A" selected>Activo</option>
                  <option value="I">Inactivo</option>
                </select>
                <span class="text-danger error-estado_usuario"></span>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Contraseña <span class="text-danger">*</span></label>
                <input type="password" name="password" id="password" class="form-control" required>
                <span class="text-danger error-password"></span>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Confirmar contraseña <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
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
        <h5 class="modal-title"><i class="fas fa-user-edit"></i> Editar Usuario</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <form id="formEdit">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit_id">

        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Nombre <span class="text-danger">*</span></label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
                <span class="text-danger error-edit-name"></span>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="edit_email" class="form-control" required>
                <span class="text-danger error-edit-email"></span>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>DNI</label>
                <input
                  type="text"
                  name="dni_usuario"
                  id="edit_dni_usuario"
                  class="form-control dni-only"
                  inputmode="numeric"
                  maxlength="8"
                  pattern="\d{8}"
                  title="Debe contener exactamente 8 dígitos numéricos"
                  placeholder="12345678"
                  autocomplete="off"
                >
                <small class="text-muted">Solo números, 8 dígitos.</small>
                <span class="text-danger error-edit-dni_usuario"></span>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>Rol <span class="text-danger">*</span></label>
                <select name="rol_usuario" id="edit_rol_usuario" class="form-control" required>
                  <option value="ADMIN">ADMIN</option>
                  <option value="USUARIO">USUARIO</option>
                  <option value="INVITADO">INVITADO</option>
                </select>
                <span class="text-danger error-edit-rol_usuario"></span>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label>Estado <span class="text-danger">*</span></label>
                <select name="estado_usuario" id="edit_estado_usuario" class="form-control" required>
                  <option value="A">Activo</option>
                  <option value="I">Inactivo</option>
                </select>
                <span class="text-danger error-edit-estado_usuario"></span>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Nueva contraseña (opcional)</label>
                <input type="password" name="password" id="edit_password" class="form-control">
                <span class="text-danger error-edit-password"></span>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>Confirmar nueva contraseña</label>
                <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control">
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
{{-- MODAL PERFILES --}}
<div class="modal fade" id="modalPerfiles" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalPerfilesTitle">
  <i class="fas fa-id-card mr-1"></i> Perfiles
</h5>

        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        {{-- Loading --}}
        <div id="perfilesLoading" class="text-center py-5">
          <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
          <div class="text-muted mt-2">Cargando perfiles...</div>
        </div>

        {{-- Aquí se inyecta el formulario --}}
        <div id="perfilesContent" style="display:none;"></div>
      </div>

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
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  const Toast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
    didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
  });

  // DNI: solo números y máximo 8 (al escribir/pegar) [web:448][web:491]
  function aplicarDniRules(selector) {
    $(document).on('input', selector, function() {
      let v = $(this).val() || '';
      v = String(v).replace(/\D/g, '');
      v = v.slice(0, 8);
      $(this).val(v);
    });

    $(document).on('keypress', selector, function(e) {
      const ch = String.fromCharCode(e.which);
      if (!/^\d$/.test(ch)) e.preventDefault();
    });
  }
  aplicarDniRules('#dni_usuario');
  aplicarDniRules('#edit_dni_usuario');

  let paginaActual = 1;
  let ordenActual = { columna: 'id', direccion: 'asc' };
  let terminoBusqueda = '';
  let perPage = parseInt($('#perPage').val() || '10', 10);

  // filtros
  let filtroRol = '';
  let filtroEstado = '';
  let filtroUltimo = '';

  actualizarIconosOrdenamiento();

  $('#perPage').on('change', function(){
    perPage = parseInt($(this).val() || '10', 10);
    paginaActual = 1;
    buscar(terminoBusqueda, paginaActual);
  });

  // filtros
  function syncFiltrosFromUI() {
    filtroRol = ($('#filterRol').val() || '').trim();
    filtroEstado = ($('#filterEstado').val() || '').trim();
    filtroUltimo = ($('#filterUltimo').val() || '').trim();
  }

  $('#btnAplicarFiltros').on('click', function(){
    syncFiltrosFromUI();
    paginaActual = 1;
    buscar(terminoBusqueda, paginaActual);
  });

  $('#btnLimpiarFiltros').on('click', function(){
    $('#filterRol').val('');
    $('#filterEstado').val('');
    $('#filterUltimo').val('');
    syncFiltrosFromUI();
    paginaActual = 1;
    buscar(terminoBusqueda, paginaActual);
  });

  // si quieres que sea automático al cambiar:
  $('#filterRol, #filterEstado, #filterUltimo').on('change', function(){
    $('#btnAplicarFiltros').trigger('click');
  });

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
      url: '{{ route("user.index") }}',
      method: 'GET',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      data: {
        search: termino,
        page: page,
        per_page: perPage,
        orden: ordenActual.columna,
        direccion: ordenActual.direccion,
        rol: filtroRol,
        estado: filtroEstado,
        ultimo: filtroUltimo
      },
      dataType: 'json',
      success: function(res) {
        actualizarTabla(res.data || []);
        actualizarContadores(res);
        actualizarPaginacion(res);
        mostrarCargando(false);

        if ((res.total || 0) === 0 || (res.resultados || 0) === 0) mostrarSinResultados(termino);
        else ocultarSinResultados();
      },
      error: function(xhr) {
        mostrarCargando(false);
        console.log('INDEX ERROR', xhr.status, xhr.responseText);
        Toast.fire({ icon: 'error', title: 'Error al cargar datos', text: `HTTP ${xhr.status}` });
      }
    });
  }

  function badgeEstado(valor) {
    const v = String(valor).toUpperCase();
    if (v === 'A') return '<span class="badge badge-success">Activo</span>';
    return '<span class="badge badge-secondary">Inactivo</span>';
  }

  function safeText(v) {
    return (v === null || v === undefined || String(v).trim() === '') ? '-' : String(v);
  }

  function formatUltimoAcceso(val) {
    if (!val) return '<span class="text-muted">-</span>';
    return `<span class="badge badge-light" style="border:1px solid rgba(0,0,0,.15);">${val}</span>`;
  }

  function actualizarTabla(items) {
  const tbody = $('#tablaBody');
  tbody.empty();

  // 1) Sin registros
  if (!items || items.length === 0) {
    tbody.append(`
      <tr id="filaVacia">
        <td colspan="9" class="text-center text-muted py-4">
          <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
          No hay usuarios registrados
        </td>
      </tr>
    `);
    $('#checkAll').prop('checked', false).prop('disabled', true);
    actualizarBotonEliminar();
    return;
  }

  // 2) Hay registros
  $('#checkAll').prop('disabled', false);

  items.forEach(u => {
    tbody.append(`
      <tr id="row-${u.id}">
        <td class="text-center">
          <input type="checkbox" class="checkbox-item" value="${u.id}">
        </td>

        <td class="text-center"><strong>${u.id}</strong></td>

        <td class="editable-cell" data-id="${u.id}" title="Doble click para editar">
          <strong>${(u.name || '').toUpperCase()}</strong>
        </td>

        <td>${safeText(u.email)}</td>
        <td>${safeText(u.dni_usuario)}</td>
        <td>${safeText(u.rol_usuario)}</td>
        <td class="text-center">${badgeEstado(u.estado_usuario)}</td>
        <td class="text-center">${formatUltimoAcceso(u.ultimo_acceso)}</td>

        <td class="text-center">
  <div class="btn-group">
    <button type="button"
            class="btn btn-sm btn-light border dropdown-toggle"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
            style="border-radius:999px;">
      <i class="fas fa-id-card"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
      <a class="dropdown-item btn-perfiles" href="#"
         data-id="${u.id}"
         data-name="${(u.name || '').replace(/"/g,'&quot;')}">
        <i class="fas fa-id-card mr-2"></i> Perfiles
      </a>
    </div>
  </div>
</td>
      </tr>
    `);
  });

  // 3) Eventos de checks
  $('#checkAll').prop('checked', false);
  actualizarBotonEliminar();
}

  function actualizarContadores(res) {
    $('#from').text(res.from || 0);
    $('#to').text(res.to || 0);
    $('#resultadosCount').text(res.resultados || 0);
    $('#totalCount').text(res.total || 0);
    $('#totalFooter').text(res.total || 0);
    $('#paginaInfo').text((res.from || 0) + ' - ' + (res.to || 0));
  }

  function actualizarPaginacion(res) {
    const links = $('#paginacionLinks');
    links.empty();
    if (!res.last_page || res.last_page <= 1) return;

    let html = '<ul class="pagination pagination-sm m-0">';
    html += generarBtn(res.current_page > 1, res.current_page - 1, '<i class="fas fa-chevron-left"></i>');

    const rango = 2;
    for (let i=1; i<=res.last_page; i++) {
      const esActual = i === res.current_page;
      const esPrimera = i === 1;
      const esUltima = i === res.last_page;
      const cerca = Math.abs(i - res.current_page) <= rango;

      if (esActual) html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
      else if (esPrimera || esUltima || cerca) html += `<li class="page-item"><a class="page-link paginar" href="#" data-page="${i}">${i}</a></li>`;
      else if (i === res.current_page - rango - 1 || i === res.current_page + rango + 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }

    html += generarBtn(res.current_page < res.last_page, res.current_page + 1, '<i class="fas fa-chevron-right"></i>');
    html += '</ul>';
    links.html(html);

    $('.paginar').on('click', function(e){
      e.preventDefault();
      paginaActual = $(this).data('page');
      buscar(terminoBusqueda, paginaActual);
      $('html, body').animate({ scrollTop: 0 }, 300);
    });
  }

  function generarBtn(activo, pagina, contenido) {
    if (activo) return `<li class="page-item"><a class="page-link paginar" href="#" data-page="${pagina}">${contenido}</a></li>`;
    return `<li class="page-item disabled"><span class="page-link">${contenido}</span></li>`;
  }

  $('.sortable').on('click', function() {
    const columna = $(this).data('column');

    if (ordenActual.columna === columna) ordenActual.direccion = (ordenActual.direccion === 'asc') ? 'desc' : 'asc';
    else { ordenActual.columna = columna; ordenActual.direccion = 'asc'; }

    actualizarIconosOrdenamiento();
    paginaActual = 1;
    buscar(terminoBusqueda, paginaActual);
  });

  function actualizarIconosOrdenamiento() {
    $('.sortable .sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
    const icono = $(`.sortable[data-column="${ordenActual.columna}"] .sort-icon`);
    icono.removeClass('fa-sort').addClass(ordenActual.direccion === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
  }

  function mostrarCargando(mostrar) {
    if (mostrar) { $('#loadingSearch').show(); $('#infoResultados').hide(); }
    else { $('#loadingSearch').hide(); $('#infoResultados').show(); }
  }

  function mostrarSinResultados(termino) {
    $('#tablaUsers').hide();
    $('#paginacionContainer').hide();
    $('#terminoBuscado').text(termino);
    $('#noResultados').fadeIn();
  }

  function ocultarSinResultados() {
    $('#noResultados').hide();
    $('#tablaUsers').show();
    $('#paginacionContainer').show();
  }

  $('#btnLimpiar, #btnMostrarTodo').on('click', function() {
    $('#searchInput').val('');
    terminoBusqueda = '';
    paginaActual = 1;
    ordenActual = { columna: 'id', direccion: 'asc' };
    actualizarIconosOrdenamiento();
    buscar('', 1);
  });

  // Checkboxes
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

    if (seleccionados > 0) $('#btnEliminarSeleccionados').fadeIn(200);
    else $('#btnEliminarSeleccionados').fadeOut(200);
  }

  // Eliminar múltiple
  $('#btnEliminarSeleccionados').on('click', function() {
    const ids = $('.checkbox-item:checked').map(function(){ return $(this).val(); }).get();
    if (!ids.length) return;

    Swal.fire({
      title: `¿Eliminar ${ids.length} usuario(s)?`,
      text: "Esta acción no se puede revertir",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
      cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
    }).then((result) => { if (result.isConfirmed) eliminarMultiples(ids); });
  });

  function eliminarMultiples(ids) {
    let eliminados = 0, errores = 0;

    Swal.fire({
      title: 'Eliminando...',
      html: `Procesando <b>${eliminados}</b> de <b>${ids.length}</b>`,
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    Promise.allSettled(
      ids.map(id =>
        $.ajax({
          url: `/user/${id}`,
          method: 'POST',
          data: { _method: 'DELETE' }
        }).then(() => eliminados++)
          .catch(() => errores++)
      )
    ).then(() => {
      Swal.close();

      if (eliminados > 0) {
        Toast.fire({ icon: 'success', title: `${eliminados} eliminado(s)`, text: errores ? `${errores} error(es)` : '' });
        $('#checkAll').prop('checked', false);
        buscar(terminoBusqueda, paginaActual);
      } else {
        Toast.fire({ icon: 'error', title: 'No se pudo eliminar ninguno' });
      }
    });
  }

  // Edit (dblclick)
  $(document).on('dblclick', '.editable-cell', function() {
    const id = $(this).data('id');

    $.get(`/user/${id}/edit`, function(data) {
      $('#edit_id').val(data.id);
      $('#edit_name').val(data.name || '');
      $('#edit_email').val(data.email || '');
      $('#edit_dni_usuario').val(data.dni_usuario || '');
      $('#edit_rol_usuario').val(data.rol_usuario || 'USUARIO');
      $('#edit_estado_usuario').val(String(data.estado_usuario || 'A'));

      $('#edit_password').val('');
      $('#edit_password_confirmation').val('');

      $('#modalEdit').modal('show');
    }).fail(() => Toast.fire({ icon: 'error', title: 'No se pudo cargar el usuario' }));
  });

  // Create
  $('#formCreate').on('submit', function(e) {
    e.preventDefault();
    $('.text-danger').text('');

    const btn = $('#btnGuardar');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
      url: '{{ route("user.store") }}',
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      success: function(res) {
        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        if (res.success) {
          $('#modalCreate').modal('hide');
          Toast.fire({ icon: 'success', title: res.message || 'Registrado' });
          buscar('', 1);
        }
      },
      error: function(xhr) {
        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        console.log('STORE ERROR', xhr.status, xhr.responseText);

        if (xhr.status === 422) {
          const errors = xhr.responseJSON?.errors || {};
          Object.keys(errors).forEach(campo => $(`.error-${campo}`).text(errors[campo][0]));
          return;
        }

        Toast.fire({ icon: 'error', title: 'Error al guardar', text: `HTTP ${xhr.status}` });
      }
    });
  });

  // Update
  $('#formEdit').on('submit', function(e) {
    e.preventDefault();
    $('.text-danger').text('');

    const btn = $('#btnActualizar');
    const id = $('#edit_id').val();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

    $.ajax({
      url: `/user/${id}`,
      method: 'PUT',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      data: $(this).serialize(),
      success: function(res) {
        btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');
        if (res.success) {
          $('#modalEdit').modal('hide');
          Toast.fire({ icon: 'success', title: res.message || 'Actualizado' });
          buscar(terminoBusqueda, paginaActual);
        }
      },
      error: function(xhr) {
        btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');
        console.log('UPDATE ERROR', xhr.status, xhr.responseText);

        if (xhr.status === 422) {
          const errors = xhr.responseJSON?.errors || {};
          Object.keys(errors).forEach(campo => $(`.error-edit-${campo}`).text(errors[campo][0]));
          return;
        }

        Toast.fire({ icon: 'error', title: 'Error al actualizar', text: `HTTP ${xhr.status}` });
      }
    });
  });

  // Limpiar modales
  $('#modalCreate').on('hidden.bs.modal', function(){
    $('#formCreate')[0].reset();
    $('.text-danger').text('');
  });

  $('#modalEdit').on('hidden.bs.modal', function(){
    $('#formEdit')[0].reset();
    $('.text-danger').text('');
  });

  // Inicial
  buscar('', 1);
  // Abrir modal y cargar formulario
$(document).on('click', '.btn-perfiles', function(e){
  e.preventDefault();
  const id = $(this).data('id');
  const name = $(this).data('name') || '';
  $('#modalPerfilesTitle').html(`<i class="fas fa-id-card mr-1"></i> Perfiles de: ${name}`);

  $('#modalPerfiles').modal('show');
  $('#perfilesLoading').show();
  $('#perfilesContent').hide().empty();

  $.ajax({
    url: `/user/${id}/perfiles`,
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    success: function(html){
      $('#perfilesContent').html(html);
      initPerfilesUI();         // activa buscador/contadores
      $('#perfilesLoading').hide();
      $('#perfilesContent').fadeIn(150);
    },
    error: function(xhr){
      $('#perfilesLoading').hide();
      $('#perfilesContent').show().html(`<div class="alert alert-danger">Error al cargar perfiles (HTTP ${xhr.status})</div>`);
    }
  });
});

// Guardar perfiles (AJAX)
$(document).on('submit', '#formPerfilesModal', function(e){
  e.preventDefault();
  const id = $(this).data('user-id');

  const $btn = $('#btnGuardarPerfiles');
  $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

  $.ajax({
    url: `/user/${id}/perfiles`,
    method: 'POST',
    data: $(this).serialize() + '&_method=PUT',
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    success: function(res){
      $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar cambios');
      // Si tu update devuelve redirect, esto igual puede caer aquí si no devuelves JSON.
      // Recomendado: que update devuelva JSON (te lo dejo abajo).
      $('#modalPerfiles').modal('hide');
      Toast.fire({ icon: 'success', title: 'Perfiles actualizados' });
    },
    error: function(xhr){
      $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar cambios');
      Toast.fire({ icon: 'error', title: 'No se pudo guardar', text: `HTTP ${xhr.status}` });
    }
  });
});

function initPerfilesUI(){
  const $items = $('.perfil-item');
  const $search = $('#perfilSearch');
  const $noMatch = $('#noMatch');

  function updateCounts(){
    $('#countTotal').text($('.perfil-item:visible').length);
    $('#countSeleccionados').text($('.perfil-check:checked').length);
  }

  function applyFilter(){
    const q = ($search.val() || '').toLowerCase().trim();
    let visibles = 0;

    $items.each(function(){
      const nombre = ($(this).data('nombre') || '');
      const show = (q === '' || nombre.includes(q));
      $(this).toggle(show);
      if (show) visibles++;
    });

    $noMatch.toggle(visibles === 0);
    updateCounts();
  }

  $(document).off('change.perfiles').on('change.perfiles', '.perfil-check', function(){
    $(this).closest('.perfil-item').find('.perfil-chip').toggleClass('is-checked', $(this).is(':checked'));
    updateCounts();
  });

  $search.off('input').on('input', applyFilter);

  $('#btnClearSearch').off('click').on('click', function(){
    $search.val('');
    applyFilter();
    $search.focus();
  });

  $('#btnSelectAll').off('click').on('click', function(){
    $('.perfil-item:visible .perfil-check').prop('checked', true).trigger('change');
  });

  $('#btnSelectNone').off('click').on('click', function(){
    $('.perfil-item:visible .perfil-check').prop('checked', false).trigger('change');
  });

  applyFilter();
}

});
</script>
@stop
