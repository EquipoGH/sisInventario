@extends('layouts.main')
@section('title', 'Reportes - Inventario de Bienes')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
  <div class="d-flex align-items-center">
    <h1 class="mb-0 mr-3">
      <i class="fas fa-clipboard-list"></i> Reportes de Bienes
    </h1>
    <span class="badge badge-light border" id="badgeCount">0 registros</span>
  </div>

  <div class="mt-2 mt-md-0">
    <div class="btn-group">
      <a class="btn btn-danger" id="btnPdf" target="_blank" href="{{ route('reportes.bienes.pdf') }}">
        <i class="fas fa-file-pdf"></i> PDF
      </a>
      <a class="btn btn-success" id="btnExcel" href="{{ route('reportes.bienes.excel') }}">
        <i class="fas fa-file-excel"></i> Excel
      </a>
    </div>
  </div>
</div>
@stop

@section('css')
<style>
  .filters-card .form-group label { font-size: .80rem; margin-bottom: .35rem; font-weight: 600; color: #495057; }
  .filters-card .form-control { font-size: .85rem; border-radius: 0.4rem; border: 1px solid #ced4da; transition: all 0.3s; }
  .filters-card .form-control:focus { border-color: #80bdff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); }
  .filters-actions .btn { min-width: 120px; border-radius: 0.4rem; font-weight: 600; transition: transform 0.2s, box-shadow 0.2s; }
  .filters-actions .btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
  .hint { font-size: .78rem; color: #6c757d; font-style: italic; }

  /* Premium Cards */
  .card { border: none; border-radius: 0.75rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: all 0.3s ease; }
  .filters-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.12); transform: translateY(-3px); }
  .card-header { border-bottom: 1px solid rgba(0,0,0,0.05); background-color: #fff; border-top-left-radius: 0.75rem !important; border-top-right-radius: 0.75rem !important; }
  .card-outline.card-primary { border-top: 4px solid #007bff; }
  .card-outline.card-secondary { border-top: 4px solid #6c757d; }

  /* Top Buttons */
  .btn-group .btn { transition: all 0.2s; border-radius: 0.4rem !important; margin-left: 5px; font-weight: 600; }
  .btn-group .btn:hover { transform: scale(1.05); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
  
  /* Premium DataTables */
  #tablaBienes { border-radius: 0.5rem; overflow: hidden; }
  #tablaBienes thead th { white-space: nowrap; border-bottom: 2px solid #dee2e6; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
  .thead-premium { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; }
  #tablaBienes tbody tr { transition: background-color 0.2s; }
  #tablaBienes tbody tr:hover { background-color: #f8f9fa !important; cursor: pointer; }
  #tablaBienes tbody td { vertical-align: middle; padding: 12px; }
  
  .td-clip { display:block; max-width: 340px; white-space: normal; word-break: break-word; line-height: 1.4; }
  .badge-code { font-size: .85rem; padding: 0.4em 0.6em; border-radius: 0.3rem; }
  
  .loading-select { opacity: .75; pointer-events: none; background-color: #e9ecef; }

  /* Empty State Premium */
  .dataTables_empty { padding: 3rem !important; text-align: center; color: #6c757d; font-size: 1.1rem; }
  .empty-icon { font-size: 3rem; color: #dee2e6; margin-bottom: 1rem; display: block; }
</style>
@endsection

@section('content')
<div class="card card-outline card-primary filters-card" id="cardFiltros">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse">
        <i class="fas fa-minus"></i>
      </button>
    </div>
  </div>

  <div class="card-body">
    <form id="formFiltros" action="javascript:void(0)">
      <div class="row">

        <div class="col-lg-3 col-md-4">
          <div class="form-group">
            <label class="text-muted">Tipo de reporte</label>
            <select class="form-control" name="reporte" id="reporte">
              <option value="inventario_general" selected>Inventario general (por año)</option>
              <option value="inventario_area">Inventario por área y ubicación</option>

              @auth
  @if(!\App\Helpers\PermisosHelper::esInvitado())
    <option value="inventario_estado_admin">Inventario por estado</option>
  @endif
@endauth


              <option value="bienes_responsable">Bienes por responsable</option>
            </select>
            <div class="hint mt-1">El PDF/Excel se genera con estos filtros.</div>
          </div>
        </div>

        @if(!\App\Helpers\PermisosHelper::esInvitado())
        <div class="col-lg-2 col-md-4" id="wrapEstado">
          <div class="form-group">
            <label class="text-muted">Estado (activos/inactivos)</label>
            <select class="form-control" name="estado" id="estado">
              <option value="activos" selected>Activos</option>
              <option value="inactivos">Inactivos</option>
              <option value="todos">Todos</option>
            </select>
          </div>
        </div>
        @else
        <input type="hidden" name="estado" id="estado" value="activos">
        @endif

        <div class="col-lg-2 col-md-4" id="wrapAnio">
          <div class="form-group">
            <label class="text-muted">Año</label>
            <select class="form-control" name="anio" id="anio">
              <option value="">-- Todos --</option>
              @foreach($anios as $y)
                <option value="{{ $y }}">{{ $y }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="form-group">
            <label class="text-muted">Tipo bien</label>
            <select class="form-control" name="tipo_bien" id="tipo_bien">
              <option value="">-- Todos --</option>
              @foreach($tiposBien as $tb)
                <option value="{{ $tb->id_tipo_bien }}">{{ $tb->nombre_tipo }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-lg-2 col-md-6">
          <div class="form-group">
            <label class="text-muted">Área</label>
            <select class="form-control" name="area_id" id="area_id">
              <option value="">-- Todas --</option>
              @foreach($areas as $a)
                <option value="{{ $a->id_area }}">{{ $a->nombre_area }}</option>
              @endforeach
            </select>
            <div class="hint mt-1">Al elegir un área se filtran sus ubicaciones.</div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="form-group">
            <label class="text-muted">Ubicación</label>
            <select class="form-control" name="ubicacion_id" id="ubicacion_id">
              <option value="">-- Todas --</option>
              @foreach($ubicaciones as $u)
                <option value="{{ $u->id_ubicacion }}">{{ $u->nombre_sede }} - {{ $u->ambiente }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Estado del bien (catálogo) - Solo para reporte admin --}}
        <div class="col-lg-4 col-md-6" id="wrapEstadoBien" style="display:none;">
          <div class="form-group">
            <label class="text-muted">Estado del bien (catálogo)</label>
            <select class="form-control" name="estado_bien_id" id="estado_bien_id">
              <option value="">-- Todos --</option>
              @foreach($estadosBien as $eb)
                <option value="{{ $eb->id_estado }}">{{ $eb->nombre_estado }}</option>
              @endforeach
            </select>
            <div class="hint mt-1">Disponible solo para reporte de estado (Admin).</div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6" id="wrapResponsable" style="display:none;">
          <div class="form-group">
            <label class="text-muted">Responsable</label>
            <select class="form-control" name="responsable_id" id="responsable_id">
              <option value="">-- Todos --</option>
              @foreach($responsables as $r)
                <option value="{{ $r->dni_responsable }}">
                  {{ $r->apellidos_responsable }} {{ $r->nombre_responsable }} ({{ $r->dni_responsable }})
                </option>
              @endforeach
            </select>
            <div class="hint mt-1">Si no eliges, muestra todos (si el backend lo soporta).</div>
          </div>
        </div>

        <div class="col-lg-5 col-md-12 col-sm-12">
          <div class="form-group">
            <label class="text-muted">Búsqueda (global)</label>
            <div class="input-group input-group-sm">
              <div class="input-group-prepend">
                <span class="input-group-text bg-primary border-primary">
                  <i class="fas fa-search text-white"></i>
                </span>
              </div>
              <input type="text" class="form-control" name="q" id="q"
                     placeholder="Código, serie...">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" id="btnLimpiar" title="Limpiar filtros">
                  <i class="fas fa-eraser"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-md-12 col-sm-12 d-flex align-items-end mb-3">
          <div class="w-100 d-flex justify-content-lg-end justify-content-md-start filters-actions">
            <div class="btn-group">
              <button class="btn btn-primary btn-sm" type="button" id="btnFiltrar">
                <i class="fas fa-filter"></i> Aplicar
              </button>
              <button class="btn btn-default btn-sm" type="button" id="btnRecargar">
                <i class="fas fa-sync"></i> Recargar
              </button>
            </div>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

<div class="card card-outline card-secondary">
  <div class="card-header">
    <h3 class="card-title mb-0"><i class="fas fa-list"></i> Resultados</h3>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table id="tablaBienes" class="table table-hover table-sm table-bordered" style="width:100%">
        <thead class="thead-premium">
          <tr>
            <th class="border-top-0">Código</th>
            <th>Bien</th>
            <th>Tipo</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Serie</th>
            <th>Área</th>
            <th>Ubicación</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
$(function () {

  const $btnPdf = $('#btnPdf');
  const $btnExcel = $('#btnExcel');

  const $reporte = $('#reporte');
  const $estado = $('#estado');
  const $anio = $('#anio');
  const $tipoBien = $('#tipo_bien');
  const $area = $('#area_id');
  const $ubic = $('#ubicacion_id');
  const $estadoBien = $('#estado_bien_id');
  const $resp = $('#responsable_id');
  const $q = $('#q');

  function qsObj() {
    return {
      estado: $estado.val(),
      reporte: $reporte.val(),
      anio: $anio.val(),
      tipo_bien: $tipoBien.val(),
      area_id: $area.val(),
      ubicacion_id: $ubic.val(),
      estado_bien_id: $estadoBien.val(),
      responsable_id: $resp.val(),
      q: $q.val(),
    };
  }

  function refreshExportLinks() {
    const qs = new URLSearchParams(qsObj()).toString();
    $btnPdf.attr('href', `{{ route('reportes.bienes.pdf') }}?${qs}`);
    $btnExcel.attr('href', `{{ route('reportes.bienes.excel') }}?${qs}`);
  }

  function setExportEnabled(enabled) {
    $btnPdf.toggleClass('disabled', !enabled).prop('disabled', !enabled);
    $btnExcel.toggleClass('disabled', !enabled).prop('disabled', !enabled);
  }

  function toggleFiltersByReporte() {
    const rep = $reporte.val();

    // Inventario general => Año visible siempre, así que no se oculta
    // $('#wrapAnio').toggle(rep === 'inventario_general');

    // Responsable solo para bienes_responsable
    $('#wrapResponsable').toggle(rep === 'bienes_responsable');
    if (rep !== 'bienes_responsable') $resp.val('');

    // Estado del bien solo para inventario_estado_admin
    $('#wrapEstadoBien').toggle(rep === 'inventario_estado_admin');
    if (rep !== 'inventario_estado_admin') $estadoBien.val('');

    refreshExportLinks();
  }

  async function cargarUbicacionesPorArea() {
    const areaId = $area.val();

    // Si no hay área, dejamos la ubicación como "Todas" y no forzamos AJAX
    if (!areaId) {
      $ubic.removeClass('loading-select').prop('disabled', false)
          .html('<option value="">-- Todas --</option>');
      refreshExportLinks();
      return;
    }

    setExportEnabled(false);
    $ubic.addClass('loading-select').prop('disabled', true)
        .html('<option value="">Cargando...</option>');

    try {
      const res = await $.get("{{ route('ubicacion.porArea') }}", { area_id: areaId });
      const data = res?.data || [];

      let html = '<option value="">-- Todas --</option>';
      data.forEach(u => {
        html += `<option value="${u.id_ubicacion}">${u.nombre_sede} - ${u.ambiente}</option>`;
      });

      $ubic.html(html).prop('disabled', false).removeClass('loading-select');
    } catch (e) {
      $ubic.html('<option value="">-- Todas --</option>').prop('disabled', false).removeClass('loading-select');
    } finally {
      setExportEnabled(true);
      refreshExportLinks();
    }
  }

  const table = $('#tablaBienes').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    autoWidth: false,
    deferRender: true,

    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],

    ajax: {
      url: "{{ route('reportes.bienes.data') }}",
      data: function (d) { Object.assign(d, qsObj()); },
      dataSrc: 'data'
    },

    order: [[0, 'desc']],

    columns: [
      { data:'codigo_patrimonial', render: (d)=> `<span class="badge badge-info badge-code font-weight-bold">${d || '-'}</span>` },
      { data:'denominacion_bien',  render: (d)=> `<span class="font-weight-bold">${d || '-'}</span>` },
      { data:'tipo_bien',          render: (d)=> d || '-' },
      { data:'marca_bien',         render: (d)=> d || '-' },
      { data:'modelo_bien',        render: (d)=> d || '-' },
      { data:'nserie_bien',        render: (d)=> d || '-' },
      { data:'area',               render: (d)=> d || '-' },
      { data:'ubicacion',          render: (d)=> d ? `<span class="td-clip">${d}</span>` : '-' },
    ],

    language: {
      processing: "<i class='fas fa-circle-notch fa-spin text-primary mr-2'></i> Cargando información...",
      lengthMenu: "Mostrar _MENU_ registros",
      info: "Mostrando de <b>_START_</b> a <b>_END_</b> de un total de <b>_TOTAL_</b> bienes",
      infoEmpty: "Sin resultados para mostrar",
      zeroRecords: "<div class='text-center py-4'><i class='fas fa-box-open empty-icon'></i><h5 class='text-muted mb-1 mt-3'>No encontramos ningún bien</h5><p class='text-muted small mb-0'>Intenta ajustar los filtros o el término de búsqueda.</p></div>",
      paginate: { next: "Siguiente <i class='fas fa-chevron-right ml-1'></i>", previous: "<i class='fas fa-chevron-left mr-1'></i> Anterior" }
    }
  });

  table.on('xhr.dt', function (e, settings, json) {
    const total = (json && (json.recordsFiltered ?? json.recordsTotal))
      ? (json.recordsFiltered ?? json.recordsTotal)
      : 0;
    $('#badgeCount').text(`${total} registro${total === 1 ? '' : 's'}`);
  });

  // INIT
  refreshExportLinks();
  toggleFiltersByReporte();

  // Eventos
  let t;
  $q.on('input', function () {
    clearTimeout(t);
    const val = ($q.val() || '').trim();
    if (val.length === 0 || val.length >= 2) {
      t = setTimeout(() => {
        table.search(val).draw();
        refreshExportLinks();
      }, 280);
    }
  });

  $('#btnFiltrar').on('click', function () {
    refreshExportLinks();
    table.ajax.reload();
  });

  $('#btnRecargar').on('click', function () {
    refreshExportLinks();
    table.ajax.reload(null, false);
  });

  $reporte.on('change', function () {
    toggleFiltersByReporte();
    table.ajax.reload();
  });

  $area.on('change', async function () {
    await cargarUbicacionesPorArea();
    table.ajax.reload();
  });

  $('#estado,#anio,#tipo_bien,#ubicacion_id,#estado_bien_id,#responsable_id').on('change', function () {
    refreshExportLinks();
    table.ajax.reload();
  });

  $('#btnLimpiar').on('click', async function () {
    $('#formFiltros')[0].reset();
    $q.val('');

    toggleFiltersByReporte();
    await cargarUbicacionesPorArea();

    table.search('').draw();
    table.ajax.reload();
  });

});
</script>
@stop
