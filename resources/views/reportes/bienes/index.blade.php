@extends('layouts.main')
@section('title', 'Reporte de Bienes')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="mb-0"><i class="fas fa-clipboard-list"></i> Reporte de Bienes</h1>

    <div class="mt-2 mt-md-0 d-flex align-items-center">
      <a class="btn btn-danger mr-2" id="btnPdf" target="_blank" href="{{ route('reportes.bienes.pdf') }}">
        <i class="fas fa-file-pdf"></i> PDF
      </a>
      <a class="btn btn-success" id="btnExcel" href="{{ route('reportes.bienes.excel') }}">
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
      <button type="button" class="btn btn-tool" data-card-widget="collapse">
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

        <div class="col-md-3">
          <label class="text-muted">Tipo bien</label>
          <select class="form-control" name="tipo_bien" id="tipo_bien">
            <option value="">-- Todos --</option>
            @foreach($tiposBien as $tb)
              <option value="{{ $tb->id_tipo_bien }}">{{ $tb->nombre_tipo }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="text-muted">Documento</label>
          <select class="form-control" name="documento" id="documento">
            <option value="">-- Todos --</option>
            @foreach($documentos as $d)
              <option value="{{ $d->id_documento }}">{{ $d->tipo_documento }} - {{ $d->numero_documento }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="text-muted">Con documento</label>
          <select class="form-control" name="con_documento" id="con_documento">
            <option value="">-- Todos --</option>
            <option value="1">Sí</option>
            <option value="0">No</option>
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
                   placeholder="Código, denominación, marca, modelo, serie, doc...">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" id="btnLimpiar" title="Limpiar">
                <i class="fas fa-eraser"></i>
              </button>
            </div>
          </div>
          <small class="text-muted d-block mt-1">Tip: escribe mínimo 2 caracteres para buscar.</small>
        </div>

        <div class="col-md-6 mt-3 d-flex align-items-end justify-content-between">
          <div class="btn-group">
            <button class="btn btn-primary" type="button" id="btnFiltrar">
              <i class="fas fa-filter"></i> Aplicar
            </button>
            <button class="btn btn-default" type="button" id="btnRecargar">
              <i class="fas fa-sync"></i> Recargar
            </button>
          </div>

          <span class="badge badge-light border" id="badgeCount">0 registros</span>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card card-outline card-secondary">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-list"></i> Bienes</h3>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table id="tablaBienes" class="table table-hover table-striped table-sm" style="width:100%">
        <thead class="thead-dark">
          <tr>
            <th class="d-none">ID</th>
            <th>Fecha</th>
            <th>Código</th>
            <th>Bien</th>
            <th>Tipo</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Serie</th>
            <th>Doc</th>
            <th>Nro doc</th>
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
  function refreshExportLinks() {
    const qs = new URLSearchParams({
      desde: $('#desde').val(),
      hasta: $('#hasta').val(),
      tipo_bien: $('#tipo_bien').val(),
      documento: $('#documento').val(),
      con_documento: $('#con_documento').val(),
      q: $('#q').val(),
    }).toString();

    $('#btnPdf').attr('href', `{{ route('reportes.bienes.pdf') }}?${qs}`);
    $('#btnExcel').attr('href', `{{ route('reportes.bienes.excel') }}?${qs}`);
  }

  function fmtFecha(iso) {
    if (!iso) return '';
    const [y,m,d] = String(iso).substring(0,10).split('-');
    return (d && m && y) ? `${d}/${m}/${y}` : iso;
  }

  refreshExportLinks();

  const table = $('#tablaBienes').DataTable({
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
      url: "{{ route('reportes.bienes.data') }}",
      data: function (d) {
        d.desde = $('#desde').val();
        d.hasta = $('#hasta').val();
        d.tipo_bien = $('#tipo_bien').val();
        d.documento = $('#documento').val();
        d.con_documento = $('#con_documento').val();
        d.q = $('#q').val();
      },
      dataSrc: 'data'
    },

    order: [[1, 'desc']],
    columns: [
      { data: 'id_bien', visible: false, searchable: false },
      { data: 'fecha_registro', render: (d) => `<span class="font-weight-bold">${fmtFecha(d)}</span>` },
      { data: 'codigo_patrimonial', render: (d) => `<span class="badge badge-info font-weight-bold">${d || '-'}</span>` },
      { data: 'denominacion_bien', render: (d) => `<span class="font-weight-bold">${d || '-'}</span>` },
      { data: 'tipo_bien', render: (d) => d || '-' },
      { data: 'marca_bien', render: (d) => d || '-' },
      { data: 'modelo_bien', render: (d) => d || '-' },
      { data: 'nserie_bien', render: (d) => d || '-' },
      { data: 'documento', render: (d) => d ? `<span class="badge badge-dark">${d}</span>` : '-' },
      { data: 'numero_documento', render: (d) => d ? `<span class="badge badge-light border">${d}</span>` : '-' },
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

  // input búsqueda = search real server-side
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

  $('#desde,#hasta,#tipo_bien,#documento,#con_documento').on('change', function () {
    refreshExportLinks();
  });

  $('#btnLimpiar').on('click', function () {
    $('#formFiltros')[0].reset();
    $('#q').val('');
    table.search('').draw();
    refreshExportLinks();
    table.ajax.reload();
  });
});
</script>
@stop
