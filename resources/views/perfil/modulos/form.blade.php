<form id="formPerfilModulosModal" data-perfil-id="{{ $perfil->idperfil }}">
  @csrf

  <style>
    /* Layout general */
    .pm-wrap { max-height: 62vh; overflow: auto; padding-right: .25rem; }
    .pm-toolbar { gap: .4rem; }
    .pm-sticky-footer {
      position: sticky; bottom: 0; z-index: 5;
      background: #fff; border-top: 1px solid rgba(0,0,0,.08);
      padding-top: .75rem;
    }

    /* Grupo */
    .pm-group { border: 1px solid rgba(0,0,0,.08); border-radius: 12px; overflow: hidden; }
    .pm-group-h {
      background: #f8f9fa;
      border-bottom: 1px solid rgba(0,0,0,.06);
      padding: .55rem .75rem;
    }
    .pm-group-title { font-weight: 700; letter-spacing: .3px; }
    .pm-count { font-weight: 600; }

    /* Item módulo */
    .pm-item { border: 1px solid rgba(0,0,0,.06); border-radius: 12px; padding: .65rem .75rem; background: #fff; }
    .pm-item:hover { box-shadow: 0 2px 10px rgba(0,0,0,.06); transition: .15s; }
    .pm-name { font-weight: 700; }
    .pm-sub { font-size: .82rem; color: #6c757d; }
    .pm-chip {
      width: 34px; height: 18px; border-radius: 999px;
      border: 1px solid rgba(0,0,0,.14); display: inline-block;
    }
    .pm-right { min-width: 160px; }

    /* Switch (Bootstrap 4 custom-switch) */
    .custom-switch { padding-left: 2.3rem; }
    .custom-control-label { cursor: pointer; }

    /* Badges */
    .badge { font-weight: 600; }
    
  </style>

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
    <div class="pr-2">
      <div class="d-flex align-items-center flex-wrap">
        <i class="fas fa-layer-group text-primary mr-2"></i>
        <h5 class="mb-0">Módulos para: <strong>{{ $perfil->nomperfil }}</strong></h5>
      </div>
      <small class="text-muted d-block mt-1">
        Selecciona los módulos que este perfil podrá ver y usar en el sistema.
      </small>
    </div>

    <div class="d-flex pm-toolbar mt-2 mt-md-0">
      <button type="button" class="btn btn-outline-secondary btn-sm" id="btnExpandirTodo">
        <i class="fas fa-plus mr-1"></i> Expandir
      </button>
      <button type="button" class="btn btn-outline-secondary btn-sm" id="btnColapsarTodo">
        <i class="fas fa-minus mr-1"></i> Colapsar
      </button>
      <button type="button" class="btn btn-outline-primary btn-sm" id="btnMarcarTodo">
        <i class="fas fa-check-square mr-1"></i> Marcar todo
      </button>
      <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDesmarcarTodo">
        <i class="far fa-square mr-1"></i> Desmarcar
      </button>
    </div>
  </div>

  {{-- Buscador + contador --}}
  <div class="row mb-2">
    <div class="col-md-8">
      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <span class="input-group-text bg-primary"><i class="fas fa-search text-white"></i></span>
        </div>
        <input type="text" class="form-control" id="filtroModulos" placeholder="Filtrar por módulo, etiqueta o ID...">
        <div class="input-group-append">
          <button class="btn btn-outline-secondary" type="button" id="btnLimpiarFiltro" title="Limpiar filtro">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    </div>

    <div class="col-md-4 mt-2 mt-md-0 text-md-right">
      <span class="text-muted">Seleccionados:</span>
      <span class="badge badge-primary" id="contadorModulosSel">0</span>
    </div>
  </div>

  {{-- Contenido scrolleable --}}
  <div class="pm-wrap" id="pmWrap">
    <div id="acordeonModulos">
      @forelse($modulos as $etiqueta => $lista)
        @php
          $gid = \Illuminate\Support\Str::slug($etiqueta).'-'.md5($etiqueta);
          $total = $lista->count();
          $activos = $lista->pluck('idmodulo')->filter(fn($id) => in_array($id, $asignados ?? []))->count();
        @endphp

        <div class="pm-group mb-2">
          {{-- Header del grupo --}}
          <div class="pm-group-h d-flex justify-content-between align-items-center flex-wrap">
            <button type="button"
                    class="btn btn-link p-0 text-dark"
                    style="text-decoration:none;"
                    data-toggle="collapse"
                    data-target="#col-{{ $gid }}"
                    aria-expanded="true">
              <i class="fas fa-folder-open text-primary mr-1"></i>
              <span class="pm-group-title">{{ strtoupper($etiqueta) }}</span>
              <span class="badge badge-light ml-2 pm-count">{{ $activos }}/{{ $total }}</span>
            </button>

            <div class="mt-2 mt-md-0">
              <button type="button" class="btn btn-outline-primary btn-sm btnToggleGrupo"
                      data-target="#col-{{ $gid }}" data-mode="all">
                <i class="fas fa-check mr-1"></i> Activar grupo
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm btnToggleGrupo"
                      data-target="#col-{{ $gid }}" data-mode="none">
                <i class="fas fa-times mr-1"></i> Desactivar
              </button>
            </div>
          </div>

          {{-- Body del grupo --}}
          <div id="col-{{ $gid }}" class="collapse show">
            <div class="p-2">
              <div class="row">
                @foreach($lista as $m)
                  @php
                    $checked = in_array($m->idmodulo, $asignados ?? []);
                    $switchId = "mod_{$m->idmodulo}";
                    $estado = strtoupper((string)($m->estadomodulo ?? 'A'));
                    $esActivo = $estado === 'A';
                    $color = trim((string)($m->color ?? ''));
                    $colorOk = $color !== '' ? $color : '#6c757d';
                    $searchText = \Illuminate\Support\Str::lower(
                      ($m->nommodulo ?? '').' '.($m->idmodulo ?? '').' '.($m->etiqueta ?? '')
                    );
                  @endphp

                  <div class="col-lg-6 modulo-item mb-2"
                       data-text="{{ $searchText }}"
                       data-modulo-id="{{ $m->idmodulo }}">
                    <div class="pm-item d-flex justify-content-between align-items-center">
                      <div class="pr-2" style="min-width:0;">
                        <div class="d-flex align-items-center flex-wrap">
                          <div class="pm-name mr-2"
                               style="max-width: 100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ $m->nommodulo }}
                          </div>

                          <span class="badge badge-info">{{ $m->etiqueta ?: 'SIN ETIQUETA' }}</span>

                          <span class="badge ml-2 {{ $esActivo ? 'badge-success' : 'badge-secondary' }}">
                            {{ $esActivo ? 'Activo' : 'Inactivo' }}
                          </span>
                        </div>

                        <div class="pm-sub mt-1 d-flex align-items-center flex-wrap">
                          <span>ID: <strong>{{ $m->idmodulo }}</strong></span>
                          <span class="mx-2">•</span>
                          <span class="pm-chip" style="background: {{ $colorOk }};"></span>
                          <span class="ml-2">Color</span>
                        </div>
                      </div>

                      <div class="text-right pm-right">
  <div class="custom-control custom-switch d-inline-block">
    <input type="checkbox"
           class="custom-control-input chk-modulo"
           id="{{ $switchId }}"
           name="modulos[]"
           value="{{ $m->idmodulo }}"
           {{ $checked ? 'checked' : '' }}
           {{ $esActivo ? '' : 'disabled' }}>
    <label class="custom-control-label" for="{{ $switchId }}"></label>
  </div>

  <div class="mt-1">
    @if(!$esActivo)
      <span class="badge badge-secondary">Deshabilitado</span>
    @else
      <span class="badge {{ $checked ? 'badge-primary' : 'badge-light' }}">
        {{ $checked ? 'Permitido' : 'No permitido' }}
      </span>
    @endif
  </div>

  @php
    $idperfilmodulo = $mapPerfilModulo[$m->idmodulo] ?? null;
  @endphp

  <button type="button"
          class="btn btn-outline-info btn-sm btn-permisos mt-2"
          data-perfilmodulo-id="{{ $idperfilmodulo }}"
          data-modulo="{{ $m->nommodulo }}"
          {{ ($checked && $idperfilmodulo) ? '' : 'disabled' }}>
    <i class="fas fa-key mr-1"></i> Permisos
  </button>

  @if(!$idperfilmodulo && $checked)
    <small class="text-muted d-block">Guarda cambios para habilitar permisos.</small>
  @endif
</div>

                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center text-muted py-4">
          <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
          No hay módulos configurados.
        </div>
      @endforelse
    </div>

    {{-- Footer sticky dentro del scroll --}}
    <div class="pm-sticky-footer mt-2">
      <div class="d-flex justify-content-between align-items-center flex-wrap">
        <small class="text-muted">
          Nota: los módulos “Inactivos” no se pueden asignar.
        </small>

        <div class="mt-2 mt-md-0">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cerrar
          </button>
          <button type="submit" class="btn btn-success" id="btnGuardarModulos">
            <i class="fas fa-save mr-1"></i> Guardar cambios
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
(function(){
  function contarSeleccionados(){
    const n = $('#formPerfilModulosModal .chk-modulo:checked').length;
    $('#contadorModulosSel').text(n);
  }
  contarSeleccionados();

  $(document).on('change', '#formPerfilModulosModal .chk-modulo', contarSeleccionados);

  $(document).on('click', '#btnLimpiarFiltro', function(){
    $('#filtroModulos').val('').trigger('keyup').focus();
  });

  $(document).on('keyup', '#filtroModulos', function(){
    const q = ($(this).val() || '').toLowerCase().trim();
    const $items = $('#formPerfilModulosModal .modulo-item');
    if(!q){ $items.show(); return; }
    $items.each(function(){
      $(this).toggle(($(this).data('text') || '').includes(q));
    });
  });

  $(document).on('click', '#btnMarcarTodo', function(){
    $('#formPerfilModulosModal .chk-modulo:not(:disabled)').prop('checked', true).trigger('change');
  });

  $(document).on('click', '#btnDesmarcarTodo', function(){
    $('#formPerfilModulosModal .chk-modulo').prop('checked', false).trigger('change');
  });

  $(document).on('click', '.btnToggleGrupo', function(){
    const target = $(this).data('target');
    const mode = $(this).data('mode'); // all | none
    const $checks = $(target).find('.chk-modulo:not(:disabled)');
    $checks.prop('checked', mode === 'all').trigger('change');
  });

  $(document).on('click', '#btnExpandirTodo', function(){
    $('#formPerfilModulosModal .collapse').collapse('show');
  });

  $(document).on('click', '#btnColapsarTodo', function(){
    $('#formPerfilModulosModal .collapse').collapse('hide');
  });
})();
</script>
