@extends('layouts.main')
@section('title', 'Reporte de Bienes')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center flex-wrap">
    <div class="d-flex align-items-center">
      <h1 class="mb-0 mr-3">
        <i class="fas fa-clipboard-list"></i> Reporte de Bienes
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
  .filters-card .form-group label { font-size: .78rem; margin-bottom: .35rem; }
  .filters-card .form-control { font-size: .85rem; }
  .filters-actions .btn { min-width: 120px; }
  .hint { font-size: .78rem; color: #6c757d; }

  #tablaBienes thead th { white-space: nowrap; }
  #tablaBienes tbody td { vertical-align: top; }

  .td-clip{
    display:block;
    max-width: 320px;
    white-space: normal;
    word-break: break-word;
    line-height: 1.2;
  }
  .badge-code { font-size: .85rem; }

  #tablaBienes tbody tr.mvto-registro   { border-left: 6px solid #0d6efd; background: rgba(13,110,253,.06); }
  #tablaBienes tbody tr.mvto-asignacion { border-left: 6px solid #198754; background: rgba(25,135,84,.06); }
  #tablaBienes tbody tr.mvto-baja       { border-left: 6px solid #dc3545; background: rgba(220,53,69,.06); }
  #tablaBienes tbody tr:hover { filter: brightness(.985); }

  .mvto-legend{
    display:inline-flex;
    align-items:center;
    gap:10px;
    margin-left:12px;
    font-size:.85rem;
    color:#6c757d;
  }
  .mvto-dot{
    width:10px;height:10px;border-radius:50%;
    display:inline-block;border:1px solid rgba(0,0,0,.15);
    margin-right:6px;
  }
  .dot-registro{ background:#0d6efd; }
  .dot-asignacion{ background:#198754; }
  .dot-baja{ background:#dc3545; }
</style>
@endsection

@section('content')
  {{-- FILTROS --}}
  <div class="card card-outline card-primary filters-card" id="cardFiltros">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Contraer/Expandir">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    </div>

    <div class="card-body">
      <form id="formFiltros" action="javascript:void(0)">
        <div class="row">

          <div class="col-lg-2 col-md-4">
            <div class="form-group">
              <label class="text-muted">Estado</label>
              <select class="form-control" name="estado" id="estado">
                <option value="activos" selected>Activos</option>
                <option value="inactivos">Inactivos</option>
                <option value="todos">Todos</option>
              </select>
            </div>
          </div>

          <div class="col-lg-3 col-md-4">
            <div class="form-group">
              <label class="text-muted">Tipo de reporte</label>
              <select class="form-control" name="reporte" id="reporte">
                <option value="general">Bienes (general)</option>
                <option value="registrados">Bienes registrados</option>
                <option value="asignados">Bienes asignados</option>
                <option value="bajas">Bienes de baja (no revertidas)</option>
              </select>
            </div>
          </div>

          <div class="col-lg-2 col-md-4">
            <div class="form-group">
              <label class="text-muted">Desde</label>
              <input type="date" class="form-control" name="desde" id="desde" value="{{ request('desde') }}">
            </div>
          </div>

          <div class="col-lg-2 col-md-4">
            <div class="form-group">
              <label class="text-muted">Hasta</label>
              <input type="date" class="form-control" name="hasta" id="hasta" value="{{ request('hasta') }}">
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

          <div class="col-lg-8 col-md-6">
            <div class="form-group">
              <label class="text-muted">Búsqueda (global)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-primary border-primary">
                    <i class="fas fa-search text-white"></i>
                  </span>
                </div>
                <input type="text" class="form-control" name="q" id="q"
                       placeholder="Código, denominación, marca, modelo, serie...">
                <div class="input-group-append">
                  <button class="btn btn-outline-secondary" type="button" id="btnLimpiar" title="Limpiar filtros">
                    <i class="fas fa-eraser"></i>
                  </button>
                </div>
              </div>
              <div class="hint mt-1">Tip: escribe mínimo 2 caracteres para buscar.</div>
            </div>
          </div>

          <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap filters-actions">
              <div class="btn-group mb-2 mb-md-0">
                <button class="btn btn-primary" type="button" id="btnFiltrar">
                  <i class="fas fa-filter"></i> Aplicar
                </button>
                <button class="btn btn-default" type="button" id="btnRecargar">
                  <i class="fas fa-sync"></i> Recargar
                </button>
              </div>

              <small class="text-muted">
                El PDF/Excel se genera con los mismos filtros actuales.
              </small>
            </div>
          </div>

        </div>
      </form>
    </div>
  </div>

  {{-- TABLA --}}
  <div class="card card-outline card-secondary">
    <div class="card-header">
      <h3 class="card-title mb-0"><i class="fas fa-list"></i> Bienes</h3>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaBienes" class="table table-hover table-sm table-bordered" style="width:100%">
          <thead class="thead-dark">
            <tr>
              <th>Código</th>
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

  function qsObj() {
    return {
      estado: $('#estado').val(),
      reporte: $('#reporte').val(),
      desde: $('#desde').val(),
      hasta: $('#hasta').val(),
      tipo_bien: $('#tipo_bien').val(),
      area_id: $('#area_id').val(),
      ubicacion_id: $('#ubicacion_id').val(),
      q: $('#q').val(),
    };
  }

  function refreshExportLinks() {
    const qs = new URLSearchParams(qsObj()).toString();
    $('#btnPdf').attr('href', `{{ route('reportes.bienes.pdf') }}?${qs}`);
    $('#btnExcel').attr('href', `{{ route('reportes.bienes.excel') }}?${qs}`);
  }

  function mvtoRowClass(tipoMvto) {
    if (!tipoMvto) return '';
    const t = String(tipoMvto).toLowerCase();
    if (t.includes('baja')) return 'mvto-baja';
    if (t.includes('asign')) return 'mvto-asignacion';
    if (t.includes('registr')) return 'mvto-registro';
    return '';
  }

  function legendHtml() {
    return `
      <span class="mvto-legend">
        <span><span class="mvto-dot dot-registro"></span>Registro</span>
        <span><span class="mvto-dot dot-asignacion"></span>Asignación</span>
        <span><span class="mvto-dot dot-baja"></span>Baja</span>
      </span>
    `;
  }

  const table = $('#tablaBienes').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    autoWidth: false,
    deferRender: true,

    dom:
      "<'row align-items-center mb-2'<'col-md-6 d-flex align-items-center'l><'col-md-6 text-right'>>" +
      "<'row'<'col-12'tr>>" +
      "<'row'<'col-md-5'i><'col-md-7'p>>",

    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],

    ajax: {
      url: "{{ route('reportes.bienes.data') }}",
      data: function (d) { Object.assign(d, qsObj()); },
      dataSrc: 'data'
    },

    // sin fecha, ordenamos por Código (col 0) o Bien (col 1)
    order: [[0, 'desc']],

    columns: [
      { data:'codigo_patrimonial', render: (d)=> `<span class="badge badge-info badge-code font-weight-bold">${d || '-'}</span>` },
      { data:'denominacion_bien', render: (d)=> `<span class="font-weight-bold">${d || '-'}</span>` },
      { data:'tipo_bien', render: (d)=> d || '-' },
      { data:'marca_bien', render: (d)=> d || '-' },
      { data:'modelo_bien', render: (d)=> d || '-' },
      { data:'nserie_bien', render: (d)=> d || '-' },
      { data:'area', render: (d)=> d || '-' },
      { data:'ubicacion', render: (d)=> d ? `<span class="td-clip">${d}</span>` : '-' },
    ],

    createdRow: function(row, data) {
      row.classList.remove('mvto-registro','mvto-asignacion','mvto-baja');
      const cls = mvtoRowClass(data?.tipo_mvto);
      if (cls) row.classList.add(cls);
    },

    initComplete: function() {
      const $len = $('#tablaBienes_length');
      if ($len.find('.mvto-legend').length === 0) {
        $len.append(legendHtml());
      }
    },

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
    const total = (json && (json.recordsFiltered ?? json.recordsTotal))
      ? (json.recordsFiltered ?? json.recordsTotal)
      : 0;
    $('#badgeCount').text(`${total} registro${total === 1 ? '' : 's'}`);
  });

  refreshExportLinks();

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

  $('#btnFiltrar').on('click', function () {
    refreshExportLinks();
    table.ajax.reload();
  });

  $('#btnRecargar').on('click', function () {
    refreshExportLinks();
    table.ajax.reload(null, false);
  });

  $('#reporte,#estado').on('change', function () {
    refreshExportLinks();
    table.ajax.reload();
  });

  $('#desde,#hasta,#tipo_bien,#area_id,#ubicacion_id').on('change', function () {
    refreshExportLinks();
  });

  $('#btnLimpiar').on('click', function () {
    $('#formFiltros')[0].reset();
    $('#q').val('');
    refreshExportLinks();
    table.search('').draw();
    table.ajax.reload();
  });

});
</script>
@stop
