@extends('layouts.main')

@section('title', 'Gestión de Inventarios')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="mb-0"><i class="fas fa-clipboard-list"></i> Gestión de Inventarios</h1>

    <div class="mt-2 mt-md-0 d-flex align-items-center">
      <button type="button" class="btn btn-warning mr-2" id="btnAnularSeleccionados" style="display:none;">
        <i class="fas fa-ban"></i> Anular seleccionados (<span id="contadorSeleccionados">0</span>)
      </button>

      <button type="button" class="btn btn-danger mr-2" id="btnEliminarSeleccionados" style="display:none;">
        <i class="fas fa-trash"></i> Eliminar seleccionados (<span id="contadorEliminar">0</span>)
      </button>

      <div id="contenedorAccionPrincipal">
        @if(Auth::user()->esAdmin())
          @php $hayActivo = ($estadisticas['pendiente'] ?? 0) > 0 || ($estadisticas['en_proceso'] ?? 0) > 0; @endphp
          
          @if($hayActivo)
            <div class="alert alert-info mb-0 py-1 px-3 border border-info" style="font-size: 0.9rem;">
              <i class="fas fa-info-circle"></i> Hay un inventario activo. Finalícelo para crear uno nuevo.
            </div>
          @else
            <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalCreate">
              <i class="fas fa-plus"></i> Nuevo Inventario
            </button>
          @endif
        @endif
      </div>
    </div>
  </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        {{-- BARRA DE ACCIONES --}}
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
                            <option value="ALL">Todos</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="cerrado">Cerrados</option>
                            <option value="anulado">Anulados</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Buscar + info --}}
            <div class="col-md-6">
                <div class="float-right" style="width: 100%; max-width: 500px;">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-primary">
                                <i class="fas fa-search text-white"></i>
                            </span>
                        </div>
                        <input type="text"
                               id="searchInput"
                               class="form-control"
                               placeholder="Buscar por código, tipo, estado o ID..."
                               autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block text-right">
                        <span id="infoResultados">
                            Mostrando <strong id="from">{{ $inventarios->firstItem() ?? 0 }}</strong>
                            a <strong id="to">{{ $inventarios->lastItem() ?? 0 }}</strong>
                            de <strong id="resultadosCount">{{ $inventarios->total() }}</strong>
                            (<strong id="totalCount">{{ $total }}</strong> total)
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
            <table class="table table-bordered table-striped table-hover" id="tablaInventarios">
                <thead class="thead-dark">
                    <tr>
                        <th width="5%" class="text-center"><input type="checkbox" id="checkAll"></th>
                        <th width="12%" class="sortable" data-column="codigo">
                            Código <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="12%" class="sortable" data-column="fecha">
                            Fecha Inicio <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="12%">Fecha Fin</th>
                        <th width="15%" class="sortable" data-column="tipo">
                            Tipo <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="15%" class="sortable" data-column="estado">
                            Estado <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th width="20%">Responsable</th>
                        <th width="14%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    @forelse($inventarios as $inventario)
                    <tr id="row-{{ $inventario->id_inventario }}" class="{{ Auth::user()->esAdmin() && $inventario->puedeEditarse() ? 'editable-row' : '' }}" data-id="{{ $inventario->id_inventario }}">
                        <td class="text-center">
                            @if(Auth::user()->esAdmin() && $inventario->puedeEditarse())
                                <input type="checkbox" class="checkbox-item" value="{{ $inventario->id_inventario }}">
                            @endif
                        </td>
                        <td><strong>{{ $inventario->codigoinventario }}</strong></td>
                        <td>{{ $inventario->fecha_inicio ? $inventario->fecha_inicio->format('d/m/Y') : '-' }}</td>
                        <td>{{ $inventario->fecha_fin ? $inventario->fecha_fin->format('d/m/Y') : '-' }}</td>
                        <td>{{ $inventario->tipoinventario }}</td>
                        <td>{!! $inventario->getBadgeEstado() !!}</td>
                        <td>{{ $inventario->responsablePersona ? $inventario->responsablePersona->nombre_responsable . ' ' . $inventario->responsablePersona->apellidos_responsable : '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('inventario.show', $inventario->id_inventario) }}" class="btn btn-info btn-sm" title="Ver / Gestionar Bienes">
                                <i class="fas fa-clipboard-check"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr id="filaVacia">
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary"></i>
                            <h5>No hay inventarios registrados</h5>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        <div id="paginacionContainer" class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <small class="text-muted">
                    Mostrando <strong id="paginaInfo">{{ $inventarios->firstItem() ?? 0 }} - {{ $inventarios->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $inventarios->total() }}</strong>
                </small>
            </div>
            <div id="paginacionLinks">
                {{-- La paginación inicial se carga por JS o se renderiza, la haremos por JS --}}
            </div>
        </div>

        {{-- SIN RESULTADOS --}}
        <div id="noResultados" class="text-center py-4" style="display:none;">
            <i class="fas fa-search fa-3x text-muted mb-3 d-block"></i>
            <h5>No se encontraron resultados</h5>
            <p class="text-muted">No hay inventarios que coincidan con "<strong id="terminoBuscado"></strong>"</p>
            <button class="btn btn-outline-primary" id="btnMostrarTodo">
                <i class="fas fa-undo"></i> Mostrar todo
            </button>
        </div>
    </div>
</div>

{{-- ========================= MODAL CREAR ========================= --}}
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Nuevo Inventario
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCreate">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required value="{{ date('Y-m-d') }}">
                            <span class="text-danger error-fecha_inicio"></span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="tipoinventario">Tipo de Inventario <span class="text-danger">*</span></label>
                            <select name="tipoinventario" id="tipoinventario" class="form-control" required>
                                <option value="">Seleccione un tipo...</option>
                                <option value="Inventario Físico Anual">Inventario Físico Anual</option>
                                <option value="Inventario por Cambio de Personal">Inventario por Cambio de Personal</option>
                                <option value="Inventario por Transferencia">Inventario por Transferencia</option>
                                <option value="Inventario de Verificación">Inventario de Verificación</option>
                                <option value="Inventario de Baja">Inventario de Baja</option>
                                <option value="Inventario Sorpresa">Inventario Sorpresa (Auditoría)</option>
                            </select>
                            <small class="text-muted">Según Directiva N° 001-2015/SBN y normas vigentes.</small>
                            <span class="text-danger error-tipoinventario"></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="responsable">Responsable <span class="text-danger">*</span></label>
                        <select name="responsable" id="responsable" class="form-control select2" style="width: 100%;" required>
                            <option value="">Seleccione un responsable...</option>
                            @foreach($responsables as $resp)
                                <option value="{{ $resp->dni_responsable }}">{{ $resp->nombre_responsable }} {{ $resp->apellidos_responsable }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger error-responsable"></span>
                    </div>

                    <div class="form-group">
                        <label for="observacion">Observaciones / Motivo</label>
                        <textarea name="observacion" id="observacion" class="form-control" rows="3" maxlength="1000" placeholder="Especifique cualquier detalle adicional..."></textarea>
                        <span class="text-danger error-observacion"></span>
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

{{-- ========================= MODAL EDITAR ========================= --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Inventario
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEdit">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id">
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <strong>Código:</strong> <span id="lbl_codigo_edit"></span>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="edit_fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" id="edit_fecha_inicio" class="form-control" required>
                            <span class="text-danger error-edit-fecha_inicio"></span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="edit_tipoinventario">Tipo de Inventario <span class="text-danger">*</span></label>
                            <select name="tipoinventario" id="edit_tipoinventario" class="form-control" required>
                                <option value="">Seleccione un tipo...</option>
                                <option value="Inventario Físico Anual">Inventario Físico Anual</option>
                                <option value="Inventario por Cambio de Personal">Inventario por Cambio de Personal</option>
                                <option value="Inventario por Transferencia">Inventario por Transferencia</option>
                                <option value="Inventario de Verificación">Inventario de Verificación</option>
                                <option value="Inventario de Baja">Inventario de Baja</option>
                                <option value="Inventario Sorpresa">Inventario Sorpresa (Auditoría)</option>
                            </select>
                            <span class="text-danger error-edit-tipoinventario"></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_responsable">Responsable <span class="text-danger">*</span></label>
                        <select name="responsable" id="edit_responsable" class="form-control select2" style="width: 100%;" required>
                            <option value="">Seleccione un responsable...</option>
                            @foreach($responsables as $resp)
                                <option value="{{ $resp->dni_responsable }}">{{ $resp->nombre_responsable }} {{ $resp->apellidos_responsable }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger error-edit-responsable"></span>
                    </div>

                    <div class="form-group">
                        <label for="edit_observacion">Observaciones / Motivo</label>
                        <textarea name="observacion" id="edit_observacion" class="form-control" rows="3" maxlength="1000"></textarea>
                        <span class="text-danger error-edit-observacion"></span>
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

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const esAdmin = {{ Auth::user()->esAdmin() ? 'true' : 'false' }};
</script>

<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        dropdownParent: $('#modalCreate') // Asegurar que funcione en el modal create
    });
    
    // Configurar Select2 para modalEdit dinámicamente cuando se abre
    $('#modalEdit').on('shown.bs.modal', function () {
        $('#edit_responsable').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#modalEdit')
        });
    });
    
    $('#modalCreate').on('shown.bs.modal', function () {
        $('#responsable').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#modalCreate')
        });
    });

    // ==================== ESTADO GLOBAL ====================
    let paginaActual = 1;
    let ordenActual = { columna: 'fecha', direccion: 'desc' };
    let terminoBusqueda = '';
    let perPage = parseInt($('#perPage').val() || '10', 10);
    let estadoActual = $('#filtroEstado').val() || 'ALL';

    actualizarIconosOrdenamiento();

    actualizarPaginacion({
        current_page: {{ $inventarios->currentPage() }},
        last_page: {{ $inventarios->lastPage() }}
    }, terminoBusqueda);

    // ==================== FILTROS SUPERIORES ====================
    $('#perPage').on('change', function() {
        perPage = parseInt($(this).val() || '10', 10);
        paginaActual = 1;
        buscar(terminoBusqueda, paginaActual);
    });

    $('#filtroEstado').on('change', function() {
        estadoActual = $(this).val();
        paginaActual = 1;
        $('#checkAll').prop('checked', false);
        $('.checkbox-item').prop('checked', false);
        actualizarBotonAnular();
        buscar(terminoBusqueda, paginaActual);
    });

    // ==================== BÚSQUEDA ====================
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
            url: '{{ route("inventario.index") }}',
            method: 'GET',
            data: {
                search: termino,
                page: page,
                orden: ordenActual.columna,
                direccion: ordenActual.direccion,
                per_page: perPage,
                estado: estadoActual
            },
            dataType: 'json',
            success: function(res) {
                actualizarTabla(res.data);
                actualizarContadores(res);
                actualizarPaginacion(res, termino);
                actualizarAccionesPrincipales(res.estadisticas);
                mostrarCargando(false);

                if (res.resultados === 0) {
                    mostrarSinResultados(termino);
                } else {
                    ocultarSinResultados();
                }
            },
            error: function(xhr) {
                mostrarCargando(false);
                Toast.fire({
                    icon: 'error',
                    title: 'Error al buscar datos'
                });
            }
        });
    }

    // ==================== ACTUALIZAR TABLA ====================
    function actualizarTabla(items) {
        const tbody = $('#tablaBody');
        tbody.empty();

        if (items.length === 0) {
            tbody.append(`
                <tr id="filaVacia">
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary"></i>
                        <h5>No hay inventarios registrados</h5>
                    </td>
                </tr>
            `);
            if (esAdmin) $('#checkAll').prop('checked', false).prop('disabled', true);
            actualizarBotonAnular();
            return;
        }

        if (esAdmin) $('#checkAll').prop('disabled', false);

        items.forEach(item => {
            const fechaFin = item.fecha_fin ? item.fecha_fin : '-';
            const fechaInicio = item.fecha_inicio ? item.fecha_inicio : '-';
            const responsable = item.responsable ? item.responsable.nombre : '-';
            
            let rowClass = 'fade-in';
            let checkboxCol = '';
            
            if (esAdmin) {
                if (item.puedeEditarse) {
                    checkboxCol = `<td class="text-center"><input type="checkbox" class="checkbox-item" value="${item.id_inventario}" data-bienes="${item.total_detalles || 0}"></td>`;
                    rowClass += ' editable-row';
                } else {
                    checkboxCol = `<td class="text-center"></td>`;
                }
            }

            tbody.append(`
                <tr id="row-${item.id_inventario}" class="${rowClass}" data-id="${item.id_inventario}">
                    ${checkboxCol}
                    <td><strong>${item.codigoinventario}</strong></td>
                    <td>${fechaInicio}</td>
                    <td>${fechaFin}</td>
                    <td>${item.tipoinventario || '-'}</td>
                    <td>${item.badgeestado}</td>
                    <td>${responsable}</td>
                    <td class="text-center">
                        <a href="/inventario/${item.id_inventario}" class="btn btn-info btn-sm" title="Ver / Gestionar Bienes">
                            <i class="fas fa-clipboard-check"></i>
                        </a>
                    </td>
                </tr>
            `);
        });

        $('#checkAll').prop('checked', false);
        actualizarBotonAnular();
    }

    function actualizarContadores(res) {
        $('#from').text(res.from || 0);
        $('#to').text(res.to || 0);
        $('#resultadosCount').text(res.resultados);
        $('#totalCount').text(res.total);
        $('#paginaInfo').text((res.from || 0) + ' - ' + (res.to || 0));
    }

    function actualizarAccionesPrincipales(stats) {
        if (!esAdmin) return;
        
        const hayActivo = (stats.pendiente || 0) > 0 || (stats.en_proceso || 0) > 0;
        const contenedor = $('#contenedorAccionPrincipal');
        
        if (hayActivo) {
            contenedor.html(`
                <div class="alert alert-info mb-0 py-1 px-3 border border-info" style="font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Hay un inventario activo. Finalícelo para crear uno nuevo.
                </div>
            `);
        } else {
            contenedor.html(`
                <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#modalCreate">
                    <i class="fas fa-plus"></i> Nuevo Inventario
                </button>
            `);
        }
    }

    function actualizarPaginacion(res, termino) {
        const links = $('#paginacionLinks');
        links.empty();

        if (res.last_page <= 1) return;

        let html = '<ul class="pagination pagination-sm m-0">';

        html += generarBotonPaginacion(
            res.current_page > 1,
            res.current_page - 1,
            '<i class="fas fa-chevron-left"></i>'
        );

        const rango = 2;
        for (let i = 1; i <= res.last_page; i++) {
            const esActual = i === res.current_page;
            const esPrimera = i === 1;
            const esUltima = i === res.last_page;
            const estaCerca = Math.abs(i - res.current_page) <= rango;

            if (esActual) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (esPrimera || esUltima || estaCerca) {
                html += `<li class="page-item"><a class="page-link paginar" href="#" data-page="${i}">${i}</a></li>`;
            } else if (i === res.current_page - rango - 1 || i === res.current_page + rango + 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        html += generarBotonPaginacion(
            res.current_page < res.last_page,
            res.current_page + 1,
            '<i class="fas fa-chevron-right"></i>'
        );

        html += '</ul>';
        links.html(html);

        $('.paginar').on('click', function(e) {
            e.preventDefault();
            paginaActual = $(this).data('page');
            buscar(terminoBusqueda, paginaActual);
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
    }

    function generarBotonPaginacion(activo, pagina, contenido) {
        if (activo) {
            return `<li class="page-item"><a class="page-link paginar" href="#" data-page="${pagina}">${contenido}</a></li>`;
        }
        return `<li class="page-item disabled"><span class="page-link">${contenido}</span></li>`;
    }

    // ==================== ORDENAMIENTO ====================
    $('.sortable').on('click', function() {
        const columna = $(this).data('column');

        if (ordenActual.columna === columna) {
            ordenActual.direccion = ordenActual.direccion === 'asc' ? 'desc' : 'asc';
        } else {
            ordenActual.columna = columna;
            ordenActual.direccion = columna === 'fecha' ? 'desc' : 'asc';
        }

        actualizarIconosOrdenamiento();
        paginaActual = 1;
        buscar(terminoBusqueda, paginaActual);
    });

    function actualizarIconosOrdenamiento() {
        $('.sortable .sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');

        if (ordenActual.columna) {
            const iconoActivo = $(`.sortable[data-column="${ordenActual.columna}"] .sort-icon`);
            iconoActivo.removeClass('fa-sort').addClass(ordenActual.direccion === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        }
    }

    // ==================== UI HELPERS ====================
    function mostrarCargando(mostrar) {
        if (mostrar) {
            $('#loadingSearch').show();
            $('#infoResultados').hide();
        } else {
            $('#loadingSearch').hide();
            $('#infoResultados').show();
        }
    }

    function mostrarSinResultados(termino) {
        $('#tablaInventarios').hide();
        $('#paginacionContainer').hide();
        $('#terminoBuscado').text(termino);
        $('#noResultados').fadeIn();
    }

    function ocultarSinResultados() {
        $('#noResultados').hide();
        $('#tablaInventarios').show();
        $('#paginacionContainer').show();
    }

    $('#btnLimpiar, #btnMostrarTodo').on('click', function() {
        $('#searchInput').val('');
        $('#filtroEstado').val('ALL');
        $('#perPage').val('10');
        
        terminoBusqueda = '';
        estadoActual = 'ALL';
        perPage = 10;
        paginaActual = 1;
        ordenActual = { columna: 'fecha', direccion: 'desc' };
        
        $('#checkAll').prop('checked', false);
        $('.checkbox-item').prop('checked', false);
        actualizarBotonAnular();
        actualizarIconosOrdenamiento();
        
        buscar('', 1);
    });

    // ==================== CHECKBOXES Y ACCIÓN MASIVA ====================
    $('#checkAll').on('change', function() {
        $('.checkbox-item').prop('checked', $(this).is(':checked'));
        actualizarBotonAnular();
    });

    $(document).on('change', '.checkbox-item', function() {
        actualizarBotonAnular();
        const total = $('.checkbox-item').length;
        const checked = $('.checkbox-item:checked').length;
        $('#checkAll').prop('checked', total > 0 && total === checked);
    });

    function actualizarBotonAnular() {
        const seleccionados = $('.checkbox-item:checked');
        const cantidad = seleccionados.length;
        
        $('#contadorSeleccionados').text(cantidad);
        $('#contadorEliminar').text(cantidad);

        if (cantidad > 0) {
            let totalBienes = 0;
            seleccionados.each(function() {
                totalBienes += parseInt($(this).data('bienes')) || 0;
            });

            $('#btnAnularSeleccionados').fadeIn(200);

            if (totalBienes === 0) {
                $('#btnEliminarSeleccionados').fadeIn(200);
            } else {
                $('#btnEliminarSeleccionados').fadeOut(200);
            }
        } else {
            $('#btnAnularSeleccionados').fadeOut(200);
            $('#btnEliminarSeleccionados').fadeOut(200);
        }
    }

    $('#btnEliminarSeleccionados').on('click', function() {
        const ids = $('.checkbox-item:checked').map(function() {
            return $(this).val();
        }).get();

        if (ids.length === 0) return;

        Swal.fire({
            title: `¿Eliminar ${ids.length} inventario(s)?`,
            text: "Se eliminarán permanentemente. Esta acción no se puede revertir.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, Eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("inventario.eliminar-multiples") }}',
                    method: 'POST',
                    data: { ids: ids },
                    success: function(res) {
                        if (res.success) {
                            Toast.fire({ icon: 'success', title: res.message });
                            $('#checkAll').prop('checked', false);
                            actualizarBotonAnular();
                            buscar(terminoBusqueda, paginaActual);
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Error al procesar la solicitud';
                        Toast.fire({ icon: 'error', title: msg });
                    }
                });
            }
        });
    });

    $('#btnAnularSeleccionados').on('click', function() {
        const ids = $('.checkbox-item:checked').map(function() {
            return $(this).val();
        }).get();

        if (ids.length === 0) return;

        Swal.fire({
            title: `¿Anular ${ids.length} inventario(s)?`,
            text: "Los inventarios seleccionados pasarán a estado ANULADO y ya no podrán ser modificados.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-ban"></i> Sí, Anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("inventario.anular-multiples") }}',
                    method: 'POST',
                    data: { ids: ids },
                    success: function(res) {
                        if (res.success) {
                            Toast.fire({ icon: 'success', title: res.message });
                            $('#checkAll').prop('checked', false);
                            actualizarBotonAnular();
                            buscar(terminoBusqueda, paginaActual);
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Error al procesar la solicitud';
                        Toast.fire({ icon: 'error', title: msg });
                    }
                });
            }
        });
    });

    // ==================== CREAR ====================
    $('#formCreate').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text('');

        const btn = $('#btnGuardar');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("inventario.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if (res.success) {
                    $('#modalCreate').modal('hide');
                    Toast.fire({ icon: 'success', title: res.message });
                    $('#searchInput').val('');
                    terminoBusqueda = '';
                    buscar('', 1);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, (campo, mensajes) => {
                        $(`.error-${campo}`).text(mensajes[0]);
                    });
                } else {
                    Toast.fire({ icon: 'error', title: 'Error al registrar inventario' });
                }
            }
        });
    });

    // ==================== EDITAR ====================
    $(document).on('dblclick', '.editable-row', function() {
        const id = $(this).data('id');
        
        $.get(`/inventario/${id}/edit`, function(res) {
            if (res.success) {
                const data = res.data;
                $('#edit_id').val(data.id_inventario);
                $('#lbl_codigo_edit').text(data.codigoinventario);
                $('#edit_fecha_inicio').val(data.fecha_inicio);
                $('#edit_tipoinventario').val(data.tipoinventario);
                $('#edit_responsable').val(data.responsable ? data.responsable.dni : '').trigger('change');
                $('#edit_observacion').val(data.observacion);
                
                $('#modalEdit').modal('show');
            }
        }).fail(function() {
            Toast.fire({ icon: 'error', title: 'Error al cargar datos' });
        });
    });

    $('#formEdit').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text('');

        const id = $('#edit_id').val();
        const btn = $('#btnActualizar');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        $.ajax({
            url: `/inventario/${id}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');

                if (res.success) {
                    $('#modalEdit').modal('hide');
                    Toast.fire({ icon: 'success', title: res.message });
                    buscar(terminoBusqueda, paginaActual);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Actualizar');

                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, (campo, mensajes) => {
                        $(`.error-edit-${campo}`).text(mensajes[0]);
                    });
                } else {
                    Toast.fire({ icon: 'error', title: 'Error al actualizar inventario' });
                }
            }
        });
    });



    // ==================== RESET DE MODALES ====================
    $('#modalCreate').on('hidden.bs.modal', function() {
        $('#formCreate')[0].reset();
        $('#responsable').val('').trigger('change');
        $('.text-danger').text('');
    });

    $('#modalEdit').on('hidden.bs.modal', function() {
        $('#formEdit')[0].reset();
        $('.text-danger').text('');
    });

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
});
</script>

<style>
    /* Transiciones suaves */
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    
    /* Hover effects en la tabla */
    .table-hover tbody tr:hover { background-color: rgba(0,0,0,.03); }
    
    /* Indicador de fila editable */
    .editable-row { cursor: pointer; }
    .editable-row:hover { background-color: rgba(0,123,255,0.05) !important; }
    
    /* Iconos de ordenamiento */
    .sortable { cursor: pointer; user-select: none; }
    .sortable:hover { background-color: rgba(0,0,0,.05); }
    
    /* Select2 en modales de bootstrap 4 */
    .select2-container--bootstrap4 .select2-selection {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
</style>
@stop
