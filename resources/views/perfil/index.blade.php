@extends('layouts.main')

@section('title', 'Perfiles')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="mb-0"><i class="fas fa-user-shield"></i> Perfiles</h1>

    {{-- BOTONES AFUERA (header): ELIMINAR a la IZQUIERDA de NUEVO --}}
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

    {{-- BARRA: Mostrar + Buscar (sin el texto grande de doble click) --}}
    <div class="row mb-3 align-items-start">
      {{-- IZQUIERDA: Mostrar N --}}
      <div class="col-md-4">
        <div class="d-flex align-items-center">
          <span class="text-muted mr-2">Mostrar</span>

          <select id="perPage" class="form-control form-control-sm" style="width:auto;">
            @foreach([5,10,25,50,100] as $n)
              <option value="{{ $n }}" @selected((int)request('per_page', 10) === $n)>{{ $n }}</option>
            @endforeach
          </select>

          <span class="text-muted ml-2">registros</span>
        </div>
      </div>

      {{-- DERECHA: Buscador + info --}}
      <div class="col-md-8">
        <div class="float-right" style="width: 100%; max-width: 520px;">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text bg-primary">
                <i class="fas fa-search text-white"></i>
              </span>
            </div>

            <input type="text" id="searchInput" class="form-control"
                   placeholder="Buscar por nombre o ID..." autocomplete="off">

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
      <table class="table table-bordered table-striped table-hover" id="tablaPerfiles">
        <thead class="thead-dark">
          <tr>
            <th width="5%" class="text-center"><input type="checkbox" id="checkAll"></th>
            <th width="15%" class="text-center sortable" data-column="id">
              ID <i class="fas fa-sort sort-icon"></i>
            </th>
            <th class="sortable" data-column="nombre">
              Nombre <i class="fas fa-sort sort-icon"></i>
            </th>
            <th width="8%" class="text-center">ACCIONES</th>
          </tr>
        </thead>

        <tbody id="tablaBody">
          <tr id="filaVacia">
            <td colspan="4" class="text-center text-muted py-4">
              <i class="fas fa-spinner fa-spin mr-1"></i> Cargando...
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- PAGINACIÓN + HINT pequeño (bonito y sin estorbar arriba) --}}
    <div id="paginacionContainer" class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
      <div class="mb-2 mb-md-0">
        <small class="text-muted">
          Mostrando <strong id="paginaInfo">{{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }}</strong>
          de <strong id="totalFooter">{{ $items->total() }}</strong>
          <span class="ml-2 d-none d-md-inline">
            <i class="fas fa-info-circle"></i> Doble click en un nombre para editar
          </span>
        </small>
      </div>
      <div id="paginacionLinks"></div>
    </div>

    {{-- SIN RESULTADOS --}}
    <div id="noResultados" class="text-center py-4" style="display:none;">
      <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
      <h5>No se encontraron resultados</h5>
      <p class="text-muted">No hay perfiles que coincidan con "<strong id="terminoBuscado"></strong>"</p>
      <button class="btn btn-outline-primary" id="btnMostrarTodo">
        <i class="fas fa-undo"></i> Mostrar todo
      </button>
    </div>

  </div>
</div>

{{-- MODAL CREATE --}}
<div class="modal fade" id="modalCreate" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Nuevo Perfil</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <form id="formCreate">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre del Perfil <span class="text-danger">*</span></label>
            <input type="text" name="nomperfil" id="nomperfil" class="form-control" maxlength="160" required>
            <span class="text-danger error-nomperfil"></span>
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
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Perfil</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <form id="formEdit">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit_id">

        <div class="modal-body">
          <div class="form-group">
            <label>Nombre del Perfil <span class="text-danger">*</span></label>
            <input type="text" name="nomperfil" id="edit_nomperfil" class="form-control" maxlength="160" required>
            <span class="text-danger error-edit-nomperfil"></span>
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
{{-- MODAL MÓDULOS --}}
<div class="modal fade" id="modalModulos" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalModulosTitle">
          <i class="fas fa-layer-group mr-1"></i> Módulos
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div id="modulosLoading" class="text-center py-5">
          <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
          <div class="text-muted mt-2">Cargando módulos...</div>
        </div>

        <div id="modulosContent" style="display:none;"></div>
      </div>

    </div>
  </div>
</div>
{{-- MODAL PERMISOS --}}
<div class="modal fade" id="modalPermisos" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalPermisosTitle">
          <i class="fas fa-key mr-1"></i> Permisos
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div id="permisosLoading" class="text-center py-5">
          <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
          <div class="text-muted mt-2">Cargando permisos...</div>
        </div>

        <div id="permisosContent" style="display:none;"></div>
      </div>
    </div>
  </div>
</div>

@stop

@section('css')
<style>
  .editable-cell{transition:.2s; user-select:none; cursor:pointer;}
  .editable-cell:hover{background:#e3f2fd !important; font-weight:bold;}
  .sortable{cursor:pointer; transition:.2s;}
  .sortable:hover{background:#495057 !important; color:#fff;}
  .sort-icon{font-size:.8rem; margin-left:5px;}
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

  let paginaActual = 1;
  let ordenActual = { columna: 'id', direccion: 'asc' };
  let terminoBusqueda = '';
  let perPage = parseInt($('#perPage').val() || '10', 10);

  actualizarIconosOrdenamiento();

  $('#paginacionLinks').on('click', '.paginar', function(e){
  e.preventDefault();
  paginaActual = $(this).data('page');
  buscar(terminoBusqueda, paginaActual);
  $('html, body').animate({ scrollTop: 0 }, 300);
});

  // per_page
  $('#perPage').on('change', function(){
    perPage = parseInt($(this).val() || '10', 10);
    paginaActual = 1;
    buscar(terminoBusqueda, paginaActual);
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
      url: '{{ route("perfil.index") }}',
      method: 'GET',
      data: {
        search: termino,
        page: page,
        per_page: perPage,
        orden: ordenActual.columna,
        direccion: ordenActual.direccion
      },
      dataType: 'json',
      success: function(res) {
        actualizarTabla(res.data || []);
        actualizarContadores(res);
        actualizarPaginacion(res);
        mostrarCargando(false);

        if ((res.total || 0) === 0 || (res.resultados || 0) === 0) {
          mostrarSinResultados(termino);
        } else {
          ocultarSinResultados();
        }
      },
      error: function() {
        mostrarCargando(false);
        Toast.fire({ icon: 'error', title: 'Error al cargar datos' });
      }
    });
  }

  function actualizarTabla(perfiles) {
    const tbody = $('#tablaBody');
    tbody.empty();

    if (!perfiles.length) {
  tbody.append(`
    <tr id="filaVacia">
      <td colspan="4" class="text-center text-muted py-4">
        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
        No hay perfiles registrados
      </td>
    </tr>
  `);
  $('#checkAll').prop('checked', false).prop('disabled', true);
  actualizarBotonEliminar();
  return;
}


    $('#checkAll').prop('disabled', false);

    perfiles.forEach(p => {
  tbody.append(`
    <tr id="row-${p.idperfil}">
      <td class="text-center">
        <input type="checkbox" class="checkbox-item" value="${p.idperfil}">
      </td>

      <td class="text-center"><strong>${p.idperfil}</strong></td>

      <td class="editable-cell" data-id="${p.idperfil}" title="Doble click para editar">
        <strong>${(p.nomperfil || '').toUpperCase()}</strong>
      </td>

      <td class="text-center">
        <div class="btn-group">
          <button type="button"
                  class="btn btn-sm btn-light border dropdown-toggle"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                  style="border-radius:999px;">
            <i class="fas fa-cog"></i>
          </button>

          <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item btn-modulos" href="#"
               data-id="${p.idperfil}"
               data-name="${(p.nomperfil || '').replace(/"/g,'&quot;')}">
              <i class="fas fa-layer-group mr-2"></i> Módulos
            </a>
          </div>
        </div>
      </td>
    </tr>
  `);
});


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
    $('#tablaPerfiles').hide();
    $('#paginacionContainer').hide();
    $('#terminoBuscado').text(termino);
    $('#noResultados').fadeIn();
  }
  function ocultarSinResultados() {
    $('#noResultados').hide();
    $('#tablaPerfiles').show();
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

  $('#btnEliminarSeleccionados').on('click', function() {
    const ids = $('.checkbox-item:checked').map(function(){ return $(this).val(); }).get();
    if (!ids.length) return;

    Swal.fire({
      title: `¿Eliminar ${ids.length} perfil(es)?`,
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
        $.ajax({ url: `/perfil/${id}`, method: 'POST', data: { _method: 'DELETE' } })
          .then(() => eliminados++)
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

  $(document).on('dblclick', '.editable-cell', function() {
    const id = $(this).data('id');

    $.get(`/perfil/${id}/edit`, function(data) {
      $('#edit_id').val(data.idperfil);
      $('#edit_nomperfil').val(data.nomperfil);
      $('#modalEdit').modal('show');
    }).fail(() => Toast.fire({ icon: 'error', title: 'No se pudo cargar el perfil' }));
  });

  $('#formCreate').on('submit', function(e) {
    e.preventDefault();
    $('.text-danger').text('');

    const btn = $('#btnGuardar');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
      url: '{{ route("perfil.store") }}',
      method: 'POST',
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
        if (xhr.status === 422) {
          $.each(xhr.responseJSON.errors, (campo, mensajes) => $(`.error-${campo}`).text(mensajes[0]));
        } else Toast.fire({ icon: 'error', title: 'Error al guardar' });
      }
    });
  });

  $('#formEdit').on('submit', function(e) {
    e.preventDefault();
    $('.text-danger').text('');

    const btn = $('#btnActualizar');
    const id = $('#edit_id').val();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

    $.ajax({
      url: `/perfil/${id}`,
      method: 'PUT',
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
        if (xhr.status === 422) {
          $.each(xhr.responseJSON.errors, (campo, mensajes) => $(`.error-edit-${campo}`).text(mensajes[0]));
        } else Toast.fire({ icon: 'error', title: 'Error al actualizar' });
      }
    });
  });

  $('#modalCreate').on('hidden.bs.modal', function(){ $('#formCreate')[0].reset(); $('.text-danger').text(''); });
  $('#modalEdit').on('hidden.bs.modal', function(){ $('#formEdit')[0].reset(); $('.text-danger').text(''); });

  buscar('', 1);
  // Abrir modal módulos y cargar formulario
$(document).on('click', '.btn-modulos', function(e){
  e.preventDefault();

  const id = $(this).data('id');
  const name = $(this).data('name') || '';

  $('#modalModulosTitle').html(`<i class="fas fa-layer-group mr-1"></i> Módulos de: ${name}`);

  $('#modalModulos').modal('show');
  $('#modulosLoading').show();
  $('#modulosContent').hide().empty();

  $.ajax({
    url: `/perfil/${id}/modulos`,
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    success: function(html){
      $('#modulosContent').html(html);
      // Si tu partial trae su propio JS (como el que te pasé), acá no necesitas más.
      $('#modulosLoading').hide();
      $('#modulosContent').fadeIn(150);
    },
    error: function(xhr){
      $('#modulosLoading').hide();
      $('#modulosContent').show().html(
        `<div class="alert alert-danger">Error al cargar módulos (HTTP ${xhr.status})</div>`
      );
    }
  });
});
// Guardar módulos (AJAX)
$(document).on('submit', '#formPerfilModulosModal', function(e){
  e.preventDefault();

  const id = $(this).data('perfil-id');
  const $btn = $('#btnGuardarModulos');

  $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

  $.ajax({
    url: `/perfil/${id}/modulos`,
    method: 'POST',
    data: $(this).serialize() + '&_method=PUT',
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    success: function(){
      $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar cambios');
      $('#modalModulos').modal('hide');
      Toast.fire({ icon: 'success', title: 'Módulos actualizados' });
    },
    error: function(xhr){
      $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar cambios');
      Toast.fire({ icon: 'error', title: 'No se pudo guardar', text: `HTTP ${xhr.status}` });
    }
  });
});
// Abrir modal permisos y cargar formulario
$(document).on('click', '.btn-permisos', function(e){
  e.preventDefault();

  const idperfilmodulo = $(this).data('perfilmodulo-id');
  const nomModulo = $(this).data('modulo') || '';

  if(!idperfilmodulo){
    Toast.fire({ icon: 'error', title: 'Primero guarda el módulo en el perfil' });
    return;
  }

  $('#modalPermisosTitle').html(`<i class="fas fa-key mr-1"></i> Permisos: ${nomModulo}`);
  $('#modalPermisos').modal('show');
  $('#permisosLoading').show();
  $('#permisosContent').hide().empty();

  $.ajax({
    url: `/perfil-modulo/${idperfilmodulo}/permisos`,
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    success: function(html){
      $('#permisosContent').html(html);
      $('#permisosLoading').hide();
      $('#permisosContent').fadeIn(150);
    },
    error: function(xhr){
      $('#permisosLoading').hide();
      $('#permisosContent').show().html(
        `<div class="alert alert-danger">Error al cargar permisos (HTTP ${xhr.status})</div>`
      );
    }
  });
});

// Guardar permisos (AJAX)
$(document).on('submit', '#formPerfilModuloPermisos', function(e){
  e.preventDefault();

  const idperfilmodulo = $(this).data('perfilmodulo-id');
  const $btn = $('#btnGuardarPermisos');

  $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

  $.ajax({
    url: `/perfil-modulo/${idperfilmodulo}/permisos`,
    method: 'POST',
    data: $(this).serialize() + '&_method=PUT',
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    success: function(){
      $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar cambios');
      $('#modalPermisos').modal('hide');
      Toast.fire({ icon: 'success', title: 'Permisos actualizados' });
    },
    error: function(xhr){
      $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Guardar cambios');
      Toast.fire({ icon: 'error', title: 'No se pudo guardar', text: `HTTP ${xhr.status}` });
    }
  });
});

});
</script>
@stop
