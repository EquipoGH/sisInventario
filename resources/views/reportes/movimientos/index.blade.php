@extends('layouts.main')
@section('title', 'Reportes - Movimientos por Fecha')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
  <div class="d-flex align-items-center">
    <h1 class="mb-0 mr-3">
      <i class="fas fa-exchange-alt"></i> Reporte de Movimientos por Fecha (por Bien)
    </h1>
    <span class="badge badge-light border" id="badgeCount">0 registros</span>
  </div>

  <div class="mt-2 mt-md-0">
    <div class="btn-group">
      <a class="btn btn-danger" id="btnPdf" target="_blank" href="{{ route('reportes.movimientos.pdf') }}">
        <i class="fas fa-file-pdf"></i> PDF
      </a>
      <a class="btn btn-success" id="btnExcel" href="{{ route('reportes.movimientos.excel') }}">
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

  #tablaMovimientos thead th { white-space: nowrap; }
  #tablaMovimientos tbody td { vertical-align: top; }
  .td-clip { display:block; max-width: 380px; white-space: normal; word-break: break-word; line-height: 1.2; }
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
    <form id="filtrosForm" action="javascript:void(0)">
      <div class="row">

        <div class="col-lg-2 col-md-4">
          <div class="form-group">
            <label class="text-muted">Desde</label>
            <input type="date" name="desde" class="form-control form-control-sm">
          </div>
        </div>

        <div class="col-lg-2 col-md-4">
          <div class="form-group">
            <label class="text-muted">Hasta</label>
            <input type="date" name="hasta" class="form-control form-control-sm">
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="form-group">
            <label class="text-muted">Área</label>
            <select name="area_id" class="form-control form-control-sm" id="area_id">
              <option value="">-- Todas --</option>
              @foreach($areas as $a)
                <option value="{{ $a->id_area }}">
                  {{ $a->nombre_area }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="form-group">
            <label class="text-muted">Ubicación</label>
            <select name="ubicacion_id" class="form-control form-control-sm" id="ubicacion_id">
              <option value="">-- Todas --</option>
              @foreach($ubicaciones as $u)
                <option value="{{ $u->id_ubicacion }}">
                  {{ $u->nombre_sede ?? '' }} - {{ $u->ambiente ?? '' }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-lg-2 col-md-6">
          <div class="form-group">
            <label class="text-muted">Tipo mov.</label>
            {{-- CORREGIDO: name/id = tipo_mvto --}}
            <select name="tipo_mvto" class="form-control form-control-sm" id="tipo_mvto">
              <option value="">-- Todos --</option>
              @foreach($tiposMovimiento as $t)
                <option value="{{ $t->id_tipo_mvto }}">
                  {{ $t->tipo_mvto }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-lg-4 col-md-8">
          <div class="form-group">
            <label class="text-muted">Búsqueda (bien)</label>
            <input type="text" name="q" id="q" class="form-control form-control-sm"
                   placeholder="Código, denominación, marca, modelo, serie...">
            <div class="hint mt-1">Tip: escribe mínimo 2 caracteres para buscar.</div>
          </div>
        </div>

        <div class="col-12">
          <div class="d-flex align-items-center justify-content-between flex-wrap filters-actions">
            <div class="btn-group mb-2 mb-md-0">
              <button class="btn btn-primary btn-sm" type="button" id="btnFiltrar">
                <i class="fas fa-filter"></i> Aplicar
              </button>
              <button class="btn btn-default btn-sm" type="button" id="btnRecargar">
                <i class="fas fa-sync"></i> Recargar
              </button>
            </div>
            <small class="text-muted">Exporta después de aplicar filtros.</small>
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
      <table id="tablaMovimientos" class="table table-hover table-sm table-bordered" style="width:100%">
        <thead class="thead-dark">
  <tr>
    <th style="width:50px;">#</th>
    <th style="width:120px;">Código</th>
    <th>Denominación</th>
    <th style="width:140px;">Tipo bien</th>
    <th style="width:110px;">Fecha</th>
    <th style="width:150px;">Movimiento</th>
    <th style="width:150px;">Área</th>
    <th style="width:220px;">Ubicación</th>
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
  const $btnPdf   = $('#btnPdf');
  const $btnExcel = $('#btnExcel');

  function qsObj() {
    return {
      desde: $('input[name="desde"]').val(),
      hasta: $('input[name="hasta"]').val(),
      area_id: $('#area_id').val(),
      ubicacion_id: $('#ubicacion_id').val(),
      tipo_mvto: $('#tipo_mvto').val(), // CORREGIDO
      q: $('#q').val(),
    };
  }

  function refreshExportLinks() {
    const o = qsObj();
    const qs = new URLSearchParams(o).toString();
    $btnPdf.attr('href', `{{ route('reportes.movimientos.pdf') }}?${qs}`);
    $btnExcel.attr('href', `{{ route('reportes.movimientos.excel') }}?${qs}`);
  }

  const table = $('#tablaMovimientos').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    autoWidth: false,
    deferRender: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    ajax: {
      url: "{{ route('reportes.movimientos.data') }}",
      type: 'GET',
      data: function (d) {
        // IMPORTANTE: NO PISAR draw/start/length; solo añadir filtros. [web:111]
        Object.assign(d, qsObj());
      },
      dataSrc: function (json) {
        if (json && json.error) console.error('Backend error:', json.error);
        return (json && json.data) ? json.data : [];
      },
      error: function(xhr){
        console.error('DT Ajax error', xhr.status, xhr.responseText);
      }
    },
    order: [[1, 'asc']],
    columns: [
  { data: 'num' },
  { data: 'codigo' },
  { data: 'denominacion', render: (d)=> d ? `<span class="td-clip font-weight-bold">${d}</span>` : '-' },
  { data: 'tipo_bien', render: (d)=> d || '-' },
  { data: 'fecha_mov', render: (d)=> d || '-' },   // puedes dejar la key fecha_mov
  { data: 'tipo_mov', render: (d)=> d || '-' },
  { data: 'area', render: (d)=> d || '-' },
  { data: 'ubicacion', render: (d)=> d ? `<span class="td-clip">${d}</span>` : '-' },
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

  let t;
  $('#q').on('input', function () {
    clearTimeout(t);
    const val = ($('#q').val() || '').trim();
    if (val.length === 0 || val.length >= 2) {
      t = setTimeout(() => { refreshExportLinks(); table.ajax.reload(); }, 280);
    }
  });

  $('#btnFiltrar').on('click', function () { refreshExportLinks(); table.ajax.reload(); });
  $('#btnRecargar').on('click', function () { refreshExportLinks(); table.ajax.reload(null, false); });
  $('#area_id,#ubicacion_id,#tipo_mvto').on('change', function () { refreshExportLinks(); table.ajax.reload(); });

  refreshExportLinks();
  table.ajax.reload();
});
</script>
@stop
