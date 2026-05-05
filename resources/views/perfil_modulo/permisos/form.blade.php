<form id="formPerfilModuloPermisos" data-perfilmodulo-id="{{ $perfilModulo->idperfilmodulo }}">
  @csrf
  @method('PUT')

  <style>
    .pp-wrap { max-height: 62vh; overflow:auto; padding-right:.25rem; }
    .pp-card { border: 1px solid rgba(0,0,0,.06); border-radius: 14px; overflow: hidden; background: #fff; }
    .pp-header { background: #f8f9fa; border-bottom: 1px solid rgba(0,0,0,.06); padding: .75rem .9rem; }
    .pp-title { font-weight: 800; letter-spacing: .2px; margin: 0; }
    .pp-subtitle { font-size: .86rem; color: #6c757d; }
    .pp-tools { display:flex; flex-wrap:wrap; gap:.4rem; align-items:center; justify-content:flex-end; }
    .pp-pill{
      display:inline-flex; align-items:center; gap:.45rem;
      padding:.35rem .65rem;
      border-radius: 999px;
      background: rgba(23,162,184,.12);
      border: 1px solid rgba(23,162,184,.20);
      color: #0b7285;
      font-weight: 800;
      white-space: nowrap;
    }
    .pp-search { padding: .65rem .9rem; border-bottom: 1px solid rgba(0,0,0,.06); background:#fff; }
    .pp-item{
      border: 1px solid rgba(0,0,0,.06);
      border-radius: 14px;
      padding: .7rem .85rem;
      background: #fff;
      transition: .15s;
      height: 100%;
    }
    .pp-item:hover { box-shadow: 0 8px 18px rgba(0,0,0,.08); transform: translateY(-1px); }
    .pp-item-name{
      font-weight: 800; line-height: 1.15;
      max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .pp-muted { font-size: .82rem; color: #6c757d; }
    .pp-chip{
      font-size: .72rem; font-weight: 800;
      padding: .2rem .55rem;
      border-radius: 999px;
      border: 1px solid rgba(0,0,0,.06);
      display: inline-flex; align-items: center; gap: .35rem; white-space: nowrap;
    }
    .pp-chip-on { background:#e7f7ee; color:#1e7e34; }
    .pp-chip-off { background:#f1f3f5; color:#495057; }
    .pp-sticky-footer{
      position: sticky; bottom: 0; z-index: 5;
      background: #f8f9fa;
      border-top: 1px solid rgba(0,0,0,.08);
      padding: .75rem .9rem;
    }
    .custom-switch { padding-left: 2.8rem; }
    .custom-switch .custom-control-label::before{
      left: -2.8rem; width: 2.25rem; height: 1.25rem; border-radius: 999px;
    }
    .custom-switch .custom-control-label::after{
      top: calc(.25rem + 2px);
      left: calc(-2.8rem + 2px);
      width: calc(1.25rem - 4px);
      height: calc(1.25rem - 4px);
      border-radius: 999px;
    }
    .custom-control-input:checked ~ .custom-control-label::after{ transform: translateX(1rem); }
  </style>

  <div class="pp-card">
    <div class="pp-header">
      <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div class="pr-2">
          <div class="d-flex align-items-center flex-wrap">
            <i class="fas fa-key text-info mr-2"></i>
            <h5 class="pp-title mb-0">Permisos</h5>
          </div>
          <div class="pp-subtitle mt-1">
            Perfil: <strong>{{ $perfilModulo->perfil->nomperfil ?? '-' }}</strong>
            <span class="mx-1">•</span>
            Módulo: <strong>{{ $perfilModulo->modulo->nommodulo ?? '-' }}</strong>
          </div>
        </div>

        <div class="pp-tools mt-2 mt-md-0">
          <span class="pp-pill">
            <i class="fas fa-check-circle"></i>
            Seleccionados: <span id="contadorPermisosSel">0</span>
          </span>

          <button type="button" class="btn btn-outline-info btn-sm" id="btnMarcarTodoPermisos">
            <i class="fas fa-check-double mr-1"></i> Marcar
          </button>

          <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDesmarcarTodoPermisos">
            <i class="fas fa-eraser mr-1"></i> Limpiar
          </button>
        </div>
      </div>
    </div>

    <div class="pp-search">
      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <span class="input-group-text bg-info"><i class="fas fa-search text-white"></i></span>
        </div>
        <input type="text" class="form-control" id="filtroPermisos" placeholder="Buscar permiso por nombre o ID...">
        <div class="input-group-append">
          <button type="button" class="btn btn-outline-secondary" id="btnLimpiarFiltroPermisos">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      <small class="text-muted d-block mt-2">Tip: marca lo necesario y luego guarda cambios.</small>
    </div>

    <div class="pp-wrap p-3">
      <div class="row" id="gridPermisos">
        @forelse($permisos as $p)
          @php
            $pid = $p->idpermiso;
            $checked = in_array($pid, $asignados ?? []);
            $label = $p->nombpermiso ?? ('Permiso #'.$pid);
            $search = \Illuminate\Support\Str::lower($label.' '.$pid);
          @endphp

          <div class="col-lg-6 mb-2 pp-permiso-item" data-text="{{ $search }}">
            <div class="pp-item d-flex justify-content-between align-items-center">
              <div class="pr-2" style="min-width:0;">
                <div class="pp-item-name">{{ $label }}</div>
                <div class="pp-muted mt-1">
                  ID: <strong>{{ $pid }}</strong>
                  <span class="mx-2">•</span>
                  <span class="pp-chip {{ $checked ? 'pp-chip-on' : 'pp-chip-off' }} badge-estado-permiso">
                    <i class="fas {{ $checked ? 'fa-check' : 'fa-ban' }}"></i>
                    <span class="pp-estado-text">{{ $checked ? 'Permitido' : 'No permitido' }}</span>
                  </span>
                </div>
              </div>

              <div class="text-right">
                <div class="custom-control custom-switch d-inline-block">
                  <input type="checkbox"
                         class="custom-control-input chk-permiso"
                         id="perm_{{ $pid }}"
                         name="permisos[]"
                         value="{{ $pid }}"
                         {{ $checked ? 'checked' : '' }}>
                  <label class="custom-control-label" for="perm_{{ $pid }}"></label>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12 text-center text-muted py-4">
            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
            No hay permisos configurados.
          </div>
        @endforelse
      </div>
    </div>

    <div class="pp-sticky-footer">
      <div class="d-flex justify-content-between align-items-center flex-wrap">
        <small class="text-muted">Los cambios se guardan al final.</small>

        <div class="mt-2 mt-md-0">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cerrar
          </button>
          <button type="submit" class="btn btn-success" id="btnGuardarPermisos">
            <i class="fas fa-save mr-1"></i> Guardar cambios
          </button>
        </div>
      </div>
    </div>
  </div>
</form>
