@extends('layouts.main')

@section('title', 'Configuración del Sistema')

@section('content_header')
    <h1>⚙️ Configuración del Sistema</h1>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{ session('error') }}
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">🎨 Personalización de Colores y Apariencia</h3>
    </div>
    <form action="{{ route('configuracion.actualizar') }}" method="POST">
        @csrf
        <div class="card-body">
            @foreach($configuraciones as $grupo => $configs)
            <div class="mb-4">
                <h4 class="text-uppercase mb-3 text-primary">
                    <i class="fas fa-{{ $grupo == 'colores' ? 'palette' : 'cog' }}"></i>
                    {{ ucfirst($grupo) }}
                </h4>
                <div class="row">
                    @foreach($configs as $config)
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="{{ $config->clave }}" class="font-weight-bold">
                                {{ $config->descripcion }}
                            </label>

                            @if($config->tipo === 'color')
                                <div class="input-group">
                                    <input type="color"
                                           name="{{ $config->clave }}"
                                           id="{{ $config->clave }}"
                                           value="{{ $config->valor }}"
                                           class="form-control form-control-color color-input"
                                           data-preview="{{ $config->clave }}"
                                           style="height: 45px;">
                                    <input type="text"
                                           class="form-control"
                                           value="{{ $config->valor }}"
                                           readonly>
                                </div>
                            @elseif($config->tipo === 'number')
                                <input type="number"
                                       name="{{ $config->clave }}"
                                       value="{{ $config->valor }}"
                                       class="form-control"
                                       min="1">
                            @else
                                <input type="text"
                                       name="{{ $config->clave }}"
                                       value="{{ $config->valor }}"
                                       class="form-control">
                            @endif

                            <small class="text-muted">
                                <i class="fas fa-key"></i> {{ $config->clave }}
                            </small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <hr>
            @endforeach
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <button type="button" class="btn btn-warning btn-lg float-right" onclick="location.reload()">
                <i class="fas fa-undo"></i> Revertir Cambios
            </button>
        </div>
    </form>
</div>

<!-- 🎨 VISTA PREVIA DE COLORES -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">👁️ Vista Previa en Tiempo Real</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Vista previa modales -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header text-white"
                         id="preview_color_header_crear"
                         style="background-color: {{ $colores['color_header_crear'] ?? '#007bff' }}">
                        <i class="fas fa-plus-circle"></i> Modal CREAR
                    </div>
                    <div class="card-body">
                        Ejemplo de modal para crear registros
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header text-white"
                         id="preview_color_header_editar"
                         style="background-color: {{ $colores['color_header_editar'] ?? '#17a2b8' }}">
                        <i class="fas fa-edit"></i> Modal EDITAR
                    </div>
                    <div class="card-body">
                        Ejemplo de modal para editar registros
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header text-white"
                         id="preview_color_header_eliminar"
                         style="background-color: {{ $colores['color_header_eliminar'] ?? '#dc3545' }}">
                        <i class="fas fa-trash"></i> Modal ELIMINAR
                    </div>
                    <div class="card-body">
                        Ejemplo de modal para eliminar registros
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <!-- Vista previa tabla -->
            <div class="col-md-12">
                <table class="table table-bordered">
                    <thead id="preview_color_tabla_header"
                           style="background-color: {{ $colores['color_tabla_header'] ?? '#343a40' }}; color: white;">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="preview-hover">
                            <td>1</td>
                            <td>Ejemplo</td>
                            <td>Hover sobre esta fila para ver el color</td>
                        </tr>
                        <tr class="preview-hover">
                            <td>2</td>
                            <td>Muestra</td>
                            <td>
                                <span class="badge" id="preview_color_badge_info"
                                      style="background-color: {{ $colores['color_badge_info'] ?? '#17a2b8' }}">
                                    Badge Info
                                </span>
                                <span class="badge" id="preview_color_badge_warning"
                                      style="background-color: {{ $colores['color_badge_warning'] ?? '#ffc107' }}; color: #212529;">
                                    Badge Warning
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <!-- Vista previa botones -->
            <div class="col-md-12">
                <h5>Botones:</h5>
                <button type="button" class="btn btn-lg"
                        id="preview_color_btn_primario"
                        style="background-color: {{ $colores['color_btn_primario'] ?? '#007bff' }}; color: white;">
                    <i class="fas fa-plus"></i> Botón Primario
                </button>
                <button type="button" class="btn btn-lg"
                        id="preview_color_btn_success"
                        style="background-color: {{ $colores['color_btn_success'] ?? '#28a745' }}; color: white;">
                    <i class="fas fa-check"></i> Botón Success
                </button>
                <button type="button" class="btn btn-lg"
                        id="preview_color_btn_danger"
                        style="background-color: {{ $colores['color_btn_danger'] ?? '#dc3545' }}; color: white;">
                    <i class="fas fa-trash"></i> Botón Danger
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // 🎨 ACTUALIZAR VISTA PREVIA EN TIEMPO REAL
    $('.color-input').on('input change', function() {
        const clave = $(this).attr('name');
        const color = $(this).val();

        // Actualizar el texto del input
        $(this).next('input[type="text"]').val(color);

        // Actualizar vista previa
        const preview = $('#preview_' + clave);
        if (preview.length) {
            if (preview.hasClass('text-white') || preview.parent().hasClass('card-header')) {
                preview.css('background-color', color);
            } else {
                preview.css('background-color', color);
            }
        }

        // Actualizar color de hover en tabla
        if (clave === 'color_tabla_hover') {
            $('<style>.preview-hover:hover { background-color: ' + color + ' !important; }</style>').appendTo('head');
        }
    });
});
</script>
@stop


