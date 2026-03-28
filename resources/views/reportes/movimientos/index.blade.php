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
  #tablaMovimientos { border-radius: 0.5rem; overflow: hidden; }
  #tablaMovimientos thead th { white-space: nowrap; border-bottom: 2px solid #dee2e6; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
  .thead-premium { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; }
  #tablaMovimientos tbody tr { transition: background-color 0.2s; }
  #tablaMovimientos tbody tr:hover { background-color: #f8f9fa !important; cursor: pointer; }
  #tablaMovimientos tbody td { vertical-align: middle; padding: 12px; }
  
  .td-clip { display:block; max-width: 380px; white-space: normal; word-break: break-word; line-height: 1.4; }

  /* Empty State Premium */
  .dataTables_empty { padding: 3rem !important; text-align: center; color: #6c757d; font-size: 1.1rem; }
  .empty-icon { font-size: 3rem; color: #dee2e6; margin-bottom: 1rem; display: block; }

  /* Fila Coloreadas por Tipo Movimiento */
  .row-registro { background-color: rgba(0, 123, 255, 0.08) !important; }
  .row-registro:hover { background-color: rgba(0, 123, 255, 0.15) !important; }
  
  .row-asignacion { background-color: rgba(40, 167, 69, 0.08) !important; }
  .row-asignacion:hover { background-color: rgba(40, 167, 69, 0.15) !important; }

  .row-baja { background-color: rgba(220, 53, 69, 0.08) !important; }
  .row-baja:hover { background-color: rgba(220, 53, 69, 0.15) !important; }
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
                <option value="{{ $u->id_ubicacion }}" data-area="{{ $u->idarea }}">
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
                @if(\App\Helpers\PermisosHelper::esInvitado() && stripos($t->tipo_mvto, 'baja') !== false)
                  @continue
                @endif
                <option value="{{ $t->id_tipo_mvto }}">
                  {{ $t->tipo_mvto }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-lg-2 col-md-6 col-sm-12">
          <div class="form-group">
            <label class="text-muted">Búsqueda (bien)</label>
            <input type="text" name="q" id="q" class="form-control form-control-sm"
                   placeholder="Código, serie...">
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
      <table id="tablaMovimientos" class="table table-hover table-sm table-bordered" style="width:100%">
        <thead class="thead-premium">
  <tr>
    <th class="border-top-0" style="width:50px;">#</th>
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
  { data: 'denominacion', render: (d)=> d ? `<span class="td-clip">${d}</span>` : '-' },
  { data: 'tipo_bien', render: (d)=> d || '-' },
  { data: 'fecha_mov', render: (d)=> d || '-' },   // puedes dejar la key fecha_mov
  { data: 'tipo_mov', render: (d)=> d || '-' },
  { data: 'area', render: (d)=> d || '-' },
  { data: 'ubicacion', render: (d)=> d ? `<span class="td-clip">${d}</span>` : '-' },
],

    createdRow: function (row, data, dataIndex) {
      if (!data.tipo_mov) return;
      const t = data.tipo_mov.toLowerCase();
      
      if (t.includes('registro') || t.includes('ingreso')) {
        $(row).addClass('row-registro');
      } else if (t.includes('asignacion') || t.includes('asignación')) {
        $(row).addClass('row-asignacion');
      } else if (t.includes('baja')) {
        $(row).addClass('row-baja');
      }
    },

    language: {
      processing: "<i class='fas fa-circle-notch fa-spin text-primary mr-2'></i> Cargando historial...",
      lengthMenu: "Mostrar _MENU_ eventos",
      info: "Mostrando de <b>_START_</b> a <b>_END_</b> de un total de <b>_TOTAL_</b> movimientos",
      infoEmpty: "Sin resultados para mostrar",
      zeroRecords: "<div class='text-center py-4'><i class='fas fa-exchange-alt empty-icon'></i><h5 class='text-muted mb-1 mt-3'>Aún no hay movimientos</h5><p class='text-muted small mb-0'>No hemos encontrado transacciones activas para estos filtros.</p></div>",
      paginate: { next: "Siguiente <i class='fas fa-chevron-right ml-1'></i>", previous: "<i class='fas fa-chevron-left mr-1'></i> Anterior" }
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
  $('#area_id,#tipo_mvto').on('change', function () { refreshExportLinks(); table.ajax.reload(); });
  $('#ubicacion_id').on('change', function () { refreshExportLinks(); table.ajax.reload(); });

  // ⭐ Lógica de filtrado anidado: Área -> Ubicación
  $('#area_id').on('change', function() {
    const areaSeleccionada = $(this).val();
    const $ubicacionSelect = $('#ubicacion_id');
    const valorActualUbicacion = $ubicacionSelect.val();

    let opcionValidaVisible = false;

    $ubicacionSelect.find('option').each(function() {
      const $opt = $(this);
      const areaOpt = $opt.data('area');

      // Si es el option por defecto ("-- Todas --") o coincide el area, lo mostramos
      if (!$opt.val() || !areaSeleccionada || areaOpt == areaSeleccionada) {
        $opt.show();
        if ($opt.val() === valorActualUbicacion) opcionValidaVisible = true;
      } else {
        $opt.hide();
      }
    });

    // Si la ubicación que estaba seleccionada ya no es visible para esta nueva área, la reseteamos
    if (!opcionValidaVisible && valorActualUbicacion !== '') {
      $ubicacionSelect.val('');
      refreshExportLinks();
      table.ajax.reload();
    }
  });

  refreshExportLinks();
  table.ajax.reload();
});
</script>
@stop
