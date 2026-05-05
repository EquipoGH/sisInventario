@extends('layouts.main')

@section('title', 'Kardex de Movimientos')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="mb-0"><i class="fas fa-chart-bar"></i> Kardex de Movimientos</h1>

    <div class="mt-2 mt-md-0 d-flex align-items-center">
      <a class="btn btn-danger mr-2" id="btnPdf" target="_blank" href="{{ route('reportes.kardex.pdf') }}">
        <i class="fas fa-file-pdf"></i> PDF
      </a>
      <a class="btn btn-success" id="btnExcel" href="{{ route('reportes.kardex.excel') }}">
        <i class="fas fa-file-excel"></i> Excel
      </a>
    </div>
  </div>
@stop

@section('content')
<div class="card card-primary" id="cardFiltros">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Colapsar">
        <i class="fas fa-minus"></i>
      </button>
    </div>
  </div>

  <div class="card-body">
    <form id="formFiltros" action="javascript:void(0)">
      <div class="row">

        <div class="col-md-2">
          <label class="text-muted">Desde</label>
          <input type="date" class="form-control" name="desde" id="desde" value="{{ request('desde') }}">
        </div>

        <div class="col-md-2">
          <label class="text-muted">Hasta</label>
          <input type="date" class="form-control" name="hasta" id="hasta" value="{{ request('hasta') }}">
        </div>

        <div class="col-md-3 d-flex align-items-end mt-2 mt-md-0">
          <div class="btn-group w-100" role="group" aria-label="Rangos rápidos">
            <button type="button" class="btn btn-outline-primary btn-sm" data-range="hoy">Hoy</button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-range="7d">7 días</button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-range="mes">Mes</button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-range="anio">Año</button>
          </div>
        </div>

        <div class="col-md-2">
          <label class="text-muted">Tipo movto</label>
          <select class="form-control" name="tipo_mvto" id="tipo_mvto">
            <option value="">-- Todos --</option>
            @foreach($tiposMovto as $t)
              <option value="{{ $t->id_tipo_mvto }}" @selected(request('tipo_mvto') == $t->id_tipo_mvto)>
                {{ $t->tipo_mvto }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3 mt-3 mt-md-0">
          <label class="text-muted">Tipo bien</label>
          <select class="form-control" name="tipo_bien" id="tipo_bien">
            <option value="">-- Todos --</option>
            @foreach($tiposBien as $tb)
              <option value="{{ $tb->id_tipo_bien }}" @selected(request('tipo_bien') == $tb->id_tipo_bien)>
                {{ $tb->nombre_tipo }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3 mt-3">
          <label class="text-muted">Área</label>
          <select class="form-control" name="area" id="area">
            <option value="">-- Todas --</option>
            @foreach($areas as $a)
              <option value="{{ $a->id_area }}" @selected(request('area') == $a->id_area)>
                {{ $a->nombre_area }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3 mt-3">
          <label class="text-muted">Estado bien</label>
          <select class="form-control" name="estado" id="estado">
            <option value="">-- Todos --</option>
            @foreach($estados as $e)
              <option value="{{ $e->id_estado }}" @selected(request('estado') == $e->id_estado)>
                {{ $e->nombre_estado }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6 mt-3">
          <label class="text-muted">Búsqueda (global)</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text bg-primary border-primary">
                <i class="fas fa-search text-white"></i>
              </span>
            </div>

            <input type="text" class="form-control" name="q" id="q"
                   placeholder="Código patrimonial, denominación, detalle, nro documento..."
                   value="{{ request('q') }}">

            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" id="btnLimpiar" title="Limpiar filtros">
                <i class="fas fa-eraser"></i>
              </button>
            </div>
          </div>

          <small class="text-muted d-block mt-1" id="hintFiltros">
            Tip: escribe mínimo 2 caracteres para buscar rápido.
          </small>
        </div>

        <div class="col-md-6 mt-3 d-flex align-items-end justify-content-between">
          <div class="btn-group">
            <button type="button" class="btn btn-primary" id="btnFiltrar">
              <i class="fas fa-filter"></i> Aplicar
            </button>
            <button type="button" class="btn btn-default" id="btnRecargar">
              <i class="fas fa-sync"></i> Recargar
            </button>
          </div>

          <div class="d-flex align-items-center">
            <div class="custom-control custom-switch mr-3">
              <input type="checkbox" class="custom-control-input" id="soloOperativos">
              <label class="custom-control-label" for="soloOperativos">Solo operativos</label>
            </div>

            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="verUsuario" checked>
              <label class="custom-control-label" for="verUsuario">Ver usuario</label>
            </div>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

<div class="card card-outline card-secondary">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-list"></i> Movimientos</h3>
    <div class="card-tools">
      <span class="badge badge-light border" id="badgeCount">0 registros</span>
    </div>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table id="tablaKardex" class="table table-hover table-striped table-sm table-kardex" style="width:100%">
        <thead class="thead-dark">
          <tr>
            <th class="d-none">ID</th>
            <th>Fecha</th>
            <th>Tipo movto</th>
            <th>Código</th>
            <th>Bien</th>
            <th>Ubicación</th>
            <th>Estado</th>
            <th>Doc</th>
            <th>Nro doc</th>
            <th class="col-usuario">Usuario</th>
            <th>Detalle</th>
            <th style="width:40px;">Acción</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-dark">
        <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detalle del movimiento</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="detalleBody">
        <div class="text-muted">Cargando...</div>
      </div>
    </div>
  </div>
</div>
@stop

@section('css')
<style>
  #cardFiltros, .card-outline { animation: fadeInUp .22s ease-in-out; }
  @keyframes fadeInUp { from {opacity:0; transform: translateY(8px);} to {opacity:1; transform: translateY(0);} }

  .table-kardex thead th { white-space: nowrap; border-bottom: 0; }
  .table-kardex tbody td { vertical-align: middle; }
  .table-kardex { border: 1px solid rgba(0,0,0,.06); }
  .table-kardex tbody tr { transition: .12s ease-in-out; }
  .table-kardex tbody tr:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(0,0,0,.06); }

  .k-badge { border-radius: 999px; padding: .25rem .55rem; font-weight: 700; font-size: .75rem; }
  .k-pill  { border-radius: 10px; padding: .2rem .55rem; font-weight: 700; font-size: .75rem; display:inline-flex; align-items:center; gap:.35rem; }
  .k-code  { font-weight: 800; letter-spacing: .3px; }
  .k-muted { color: #6c757d; font-size: .82rem; }

  .k-clip-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  #hintFiltros { transition: .2s; opacity: .85; }
  #q:focus { box-shadow: 0 0 0 .12rem rgba(0,123,255,.18); }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2400,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });

  function refreshExportLinks() {
    const qs = new URLSearchParams({
      desde: $('#desde').val(),
      hasta: $('#hasta').val(),
      tipo_mvto: $('#tipo_mvto').val(),
      tipo_bien: $('#tipo_bien').val(),
      area: $('#area').val(),
      estado: $('#estado').val(),
      q: $('#q').val(),
      solo_operativos: $('#soloOperativos').is(':checked') ? 1 : 0,
    }).toString();

    $('#btnPdf').attr('href', `{{ route('reportes.kardex.pdf') }}?${qs}`);
    $('#btnExcel').attr('href', `{{ route('reportes.kardex.excel') }}?${qs}`);
  }

  function fmtFecha(iso) {
    if (!iso) return '';
    const [y,m,d] = iso.split('-');
    return (d && m && y) ? `${d}/${m}/${y}` : iso;
  }

  function badgeTipoMovto(text) {
    const t = (text || '').toLowerCase();
    if (t.includes('asign')) return `<span class="k-pill bg-success text-white"><i class="fas fa-check-circle"></i> ${text}</span>`;
    if (t.includes('baja') || t.includes('reti')) return `<span class="k-pill bg-danger text-white"><i class="fas fa-times-circle"></i> ${text}</span>`;
    if (t.includes('trasl')) return `<span class="k-pill bg-warning text-dark"><i class="fas fa-exchange-alt"></i> ${text}</span>`;
    return `<span class="k-pill bg-info text-white"><i class="fas fa-info-circle"></i> ${text || '-'}</span>`;
  }

  function badgeEstado(text) {
    const t = (text || '').toLowerCase();
    if (t.includes('oper')) return `<span class="k-badge bg-success text-white">${text}</span>`;
    if (t.includes('baja') || t.includes('inop') || t.includes('mal')) return `<span class="k-badge bg-danger text-white">${text}</span>`;
    return `<span class="k-badge bg-secondary text-white">${text || '-'}</span>`;
  }

  function renderDetalle(row) {
    const esc = (s) => $('<div/>').text(s ?? '').html();
    return `
      <div class="row">
        <div class="col-md-4"><strong>ID:</strong> ${esc(row.id_movimiento)}</div>
        <div class="col-md-4"><strong>Fecha:</strong> ${esc(row.fecha_mvto)}</div>
        <div class="col-md-4"><strong>Usuario:</strong> ${esc(row.usuario)}</div>
      </div>
      <hr>
      <div><strong>Código:</strong> ${esc(row.codigo_patrimonial)}</div>
      <div><strong>Bien:</strong> ${esc(row.denominacion_bien)} <span class="text-muted">(${esc(row.tipo_bien)})</span></div>
      <div><strong>Ubicación:</strong> ${esc(row.area)} - ${esc(row.sede)} - ${esc(row.ambiente)} - ${esc(row.piso)}</div>
      <div><strong>Estado:</strong> ${esc(row.estado_bien)}</div>
      <div><strong>Documento:</strong> ${esc(row.tipo_documento)} / ${esc(row.numero_documento)}</div>
      <div class="mt-2"><strong>Detalle técnico:</strong><br>${esc(row.detalle_tecnico || '-')}</div>
    `;
  }

  // Rangos rápidos
  function setRange(mode) {
    const today = new Date();
    const pad = (n) => String(n).padStart(2,'0');
    const toISO = (d) => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

    let desde = new Date(today), hasta = new Date(today);

    if (mode === 'hoy') {
      // desde/hasta = hoy
    } else if (mode === '7d') {
      desde.setDate(today.getDate() - 6);
    } else if (mode === 'mes') {
      desde = new Date(today.getFullYear(), today.getMonth(), 1);
      hasta = new Date(today.getFullYear(), today.getMonth()+1, 0);
    } else if (mode === 'anio') {
      desde = new Date(today.getFullYear(), 0, 1);
      hasta = new Date(today.getFullYear(), 11, 31);
    }

    $('#desde').val(toISO(desde));
    $('#hasta').val(toISO(hasta));
  }

  refreshExportLinks();

  const table = $('#tablaKardex').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    autoWidth: false,

    dom: "<'row'<'col-md-6'l><'col-md-6 text-right'>>" +
         "<'row'<'col-12'tr>>" +
         "<'row'<'col-md-5'i><'col-md-7'p>>",

    searching: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],

    ajax: {
      url: "{{ route('reportes.kardex.data') }}",
      data: function (d) {
        d.desde = $('#desde').val();
        d.hasta = $('#hasta').val();
        d.tipo_mvto = $('#tipo_mvto').val();
        d.tipo_bien = $('#tipo_bien').val();
        d.area = $('#area').val();
        d.estado = $('#estado').val();
        d.solo_operativos = $('#soloOperativos').is(':checked') ? 1 : 0;

        d.q = $('#q').val();
      },
      dataSrc: 'data'
    },

    order: [[0, 'desc']],
    columns: [
      { data: 'id_movimiento', visible: false, searchable: false },

      { data: 'fecha_mvto', render: (d) => `<span class="font-weight-bold">${fmtFecha(d)}</span>` },

      { data: 'tipo_movimiento', render: (d) => badgeTipoMovto(d) },

      { data: 'codigo_patrimonial', render: (d) => `<span class="badge badge-info k-code">${d || '-'}</span>` },

      {
        data: null,
        render: function (row) {
          const den = row.denominacion_bien || '-';
          const tb  = row.tipo_bien ? `<div class="k-muted">${row.tipo_bien}</div>` : '';
          return `<div class="font-weight-bold">${den}</div>${tb}`;
        }
      },

      {
        data: null,
        render: function (row) {
          const area = row.area ? `<div class="font-weight-bold">${row.area}</div>` : '';
          const sede = row.sede ? `<div class="k-muted"><i class="fas fa-map-marker-alt mr-1"></i>${row.sede}</div>` : '';
          const amb  = row.ambiente ? `<div class="k-muted">${row.ambiente}</div>` : '';
          const piso = row.piso ? `<div class="k-muted">${row.piso}</div>` : '';
          return `<div class="k-clip-2" title="${(row.sede||'')} ${(row.ambiente||'')}">${area}${sede}${amb}${piso}</div>`;
        }
      },

      { data: 'estado_bien', render: (d) => badgeEstado(d) },

      { data: 'tipo_documento', render: (d) => d ? `<span class="badge badge-dark">${d}</span>` : '-' },

      { data: 'numero_documento', render: (d) => d ? `<span class="badge badge-light border">${d}</span>` : '-' },

      { data: 'usuario', className: 'col-usuario', render: (d) => `<span class="k-pill bg-light border"><i class="fas fa-user"></i> ${d || '-'}</span>` },

      {
        data: 'detalle_tecnico',
        render: function (d) {
          const txt = d || '';
          const safe = $('<div/>').text(txt).html();
          return txt ? `<div class="k-clip-2" title="${safe}">${safe}</div>` : '<span class="text-muted">-</span>';
        }
      },

      {
        data: null,
        orderable: false,
        searchable: false,
        render: function(row){
          return `<button class="btn btn-outline-dark btn-sm btnDetalle" title="Ver detalle">
                    <i class="fas fa-eye"></i>
                  </button>`;
        }
      }
    ],

    language: {
      processing: "Cargando...",
      lengthMenu: "Mostrar _MENU_",
      info: "Mostrando _START_ a _END_ de _TOTAL_",
      infoEmpty: "Sin resultados",
      zeroRecords: "No hay datos para mostrar",
      paginate: { next: "Siguiente", previous: "Anterior" }
    }
  });

  table.on('xhr.dt', function (e, settings, json) {
    const total = (json && (json.recordsFiltered ?? json.recordsTotal)) ? (json.recordsFiltered ?? json.recordsTotal) : 0;
    $('#badgeCount').text(`${total} registro${total === 1 ? '' : 's'}`);
  });

  // Abrir modal detalle
  $('#tablaKardex tbody').on('click', 'button.btnDetalle', function () {
    const row = table.row($(this).closest('tr')).data();
    $('#detalleBody').html(renderDetalle(row));
    $('#modalDetalle').modal('show');
  });

  // Toggle Usuario
  $('#verUsuario').on('change', function(){
    const show = $(this).is(':checked');
    table.column('.col-usuario').visible(show);
    refreshExportLinks();
  });

  // Búsqueda global real (server-side)
  let t;
  $('#q').on('input', function () {
    clearTimeout(t);
    const val = ($(this).val() || '').trim();
    if (val.length === 0 || val.length >= 2) {
      t = setTimeout(() => {
        table.search(val).draw();
        refreshExportLinks();
      }, 280);
    }
  });

  // Rangos rápidos
  $('[data-range]').on('click', function(){
    setRange($(this).data('range'));
    refreshExportLinks();
    table.ajax.reload();
  });

  $('#btnFiltrar').on('click', function () {
    refreshExportLinks();
    table.ajax.reload();
    Toast.fire({ icon: 'success', title: 'Filtros aplicados' });
  });

  $('#btnRecargar').on('click', function () {
    refreshExportLinks();
    table.ajax.reload(null, false);
    Toast.fire({ icon: 'info', title: 'Tabla recargada' });
  });

  $('#soloOperativos').on('change', function () {
    refreshExportLinks();
    table.ajax.reload();
  });

  $('#btnLimpiar').on('click', function () {
    $('#formFiltros')[0].reset();
    $('#soloOperativos').prop('checked', false);
    $('#verUsuario').prop('checked', true);
    $('#q').val('');
    table.search('').draw();
    table.column('.col-usuario').visible(true);
    refreshExportLinks();
    Toast.fire({ icon: 'info', title: 'Filtros limpiados' });
  });
});
</script>
@stop
