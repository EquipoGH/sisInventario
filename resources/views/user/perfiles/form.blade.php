@php
  $checked = old('perfiles', $user->perfiles->pluck('idperfil')->toArray());
@endphp

{{-- TOOLBAR --}}
<div class="perfiles-toolbar mb-3">
  <div class="row align-items-center">
    <div class="col-md-7 mb-2 mb-md-0">
      <div class="input-group input-group-sm perfiles-search">
        <div class="input-group-prepend">
          <span class="input-group-text bg-primary text-white">
            <i class="fas fa-search"></i>
          </span>
        </div>

        <input type="text" id="perfilSearch" class="form-control"
               placeholder="Buscar perfil..." autocomplete="off">

        <div class="input-group-append">
          <button class="btn btn-light border" type="button" id="btnClearSearch" title="Limpiar">
            <i class="fas fa-times text-muted"></i>
          </button>
        </div>
      </div>

      <div class="perfiles-meta mt-2">
        <span class="badge badge-pill badge-info" id="countSeleccionados">0</span>
        <span class="text-muted">seleccionados</span>

        <span class="mx-2 text-muted">·</span>

        <span class="badge badge-pill badge-secondary" id="countTotal">0</span>
        <span class="text-muted">visibles</span>

        <span class="perfiles-filter badge badge-pill badge-light border ml-2" id="badgeFiltro" style="display:none;">
          <i class="fas fa-filter mr-1 text-primary"></i>
          <span id="textoFiltro">Filtrando</span>
        </span>
      </div>
    </div>

    <div class="col-md-5 text-md-right">
      <div class="btn-group btn-group-sm">
        <button type="button" class="btn btn-outline-primary" id="btnSelectAll">
          <i class="fas fa-check-square mr-1"></i> Seleccionar todo
        </button>
        <button type="button" class="btn btn-outline-secondary" id="btnSelectNone">
          <i class="far fa-square mr-1"></i> Ninguno
        </button>
      </div>
    </div>
  </div>
</div>

<form id="formPerfilesModal" data-user-id="{{ $user->id }}">
  @csrf
  @method('PUT')

  {{-- GRID (scroll interno ideal para modales) --}}
  <div class="perfiles-grid-wrap">
    <div class="row" id="perfilesGrid">
      @foreach($perfiles as $perfil)
        @php $isChecked = in_array($perfil->idperfil, $checked); @endphp

        <div class="col-md-4 col-lg-3 mb-2 perfil-item"
             data-nombre="{{ strtolower($perfil->nomperfil) }}"
             data-nombre-original="{{ $perfil->nomperfil }}">
          <label class="perfil-chip {{ $isChecked ? 'is-checked' : '' }}" for="perfil_{{ $perfil->idperfil }}">
            <div class="custom-control custom-checkbox m-0">
              <input class="custom-control-input perfil-check"
                     type="checkbox"
                     id="perfil_{{ $perfil->idperfil }}"
                     name="perfiles[]"
                     value="{{ $perfil->idperfil }}"
                     {{ $isChecked ? 'checked' : '' }}>

              <span class="custom-control-label">
                <span class="perfil-text">{{ $perfil->nomperfil }}</span>
              </span>
            </div>
          </label>
        </div>
      @endforeach
    </div>

    <div id="noMatch" class="text-center text-muted py-4" style="display:none;">
      <i class="fas fa-search fa-2x d-block mb-2"></i>
      No hay perfiles que coincidan con la búsqueda.
    </div>
  </div>

  {{-- FOOTER sticky --}}
  <div class="perfiles-footer">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
      <button class="btn btn-primary" type="submit" id="btnGuardarPerfiles">
        <i class="fas fa-save mr-1"></i> Guardar cambios
      </button>

      <small class="text-muted mt-2 mt-md-0">
        Tip: busca y luego “Seleccionar todo” para marcar solo los filtrados.
      </small>
    </div>
  </div>
</form>

<style>
:root{
  --chip-radius: .85rem;
  --chip-border: rgba(0,0,0,.08);
  --chip-shadow: 0 10px 30px rgba(0,0,0,.08);
  --chip-shadow-hover: 0 14px 38px rgba(0,0,0,.12);
}

.perfiles-toolbar{
  padding: .85rem .85rem;
  border: 1px solid rgba(0,0,0,.06);
  border-radius: .85rem;
  background: linear-gradient(180deg, rgba(13,110,253,.08), rgba(255,255,255,1) 60%);
}

.perfiles-meta{
  display:flex;
  align-items:center;
  flex-wrap: wrap;
  gap: .35rem;
}

.perfiles-filter{ font-weight: 600; }

/* Scroll interno para modal (evita modal gigante) */
.perfiles-grid-wrap{
  max-height: 54vh;
  overflow: auto;
  padding-right: .35rem;
}

/* Scrollbar suave (Chrome/Edge) */
.perfiles-grid-wrap::-webkit-scrollbar{ width: 10px; }
.perfiles-grid-wrap::-webkit-scrollbar-thumb{
  background: rgba(0,0,0,.12);
  border-radius: 999px;
}
.perfiles-grid-wrap::-webkit-scrollbar-thumb:hover{
  background: rgba(0,0,0,.18);
}

/* Chip */
.perfil-chip{
  position: relative;
  display:block;
  padding: .55rem .7rem;
  border: 1px solid var(--chip-border);
  border-radius: var(--chip-radius);
  background: #fff;
  cursor:pointer;
  user-select:none;
  transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease, background-color .14s ease;
}

.perfil-chip:hover{
  transform: translateY(-1px);
  box-shadow: var(--chip-shadow-hover);
  border-color: rgba(13,110,253,.35);
}

.perfil-chip .custom-control{
  min-height:auto;
  padding-left: 1.65rem;
}

.perfil-chip .custom-control-label{
  margin:0;
  font-weight: 700;
  color:#2c3e50;
}

/* Checked: borde degradado + glow */
.perfil-chip.is-checked{
  border-color: rgba(25,135,84,.45);
  background: linear-gradient(180deg, rgba(25,135,84,.09), rgba(255,255,255,.92));
  box-shadow: var(--chip-shadow);
}

.perfil-chip.is-checked::before{
  content:"";
  position:absolute;
  inset:0;
  border-radius: var(--chip-radius);
  padding: 1px;
  background: linear-gradient(90deg, rgba(25,135,84,.85), rgba(13,110,253,.65));
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events:none;
}

/* Micro animación */
.perfil-chip.is-checked{ animation: pop .14s ease-out; }
@keyframes pop{ from{ transform: scale(.985); } to{ transform: scale(1); } }

/* Footer sticky dentro del body del modal */
.perfiles-footer{
  position: sticky;
  bottom: 0;
  margin-top: .75rem;
  padding-top: .85rem;
  padding-bottom: .25rem;
  background: rgba(255,255,255,.92);
  backdrop-filter: blur(6px);
  border-top: 1px solid rgba(0,0,0,.06);
  z-index: 2;
}

/* Mark del buscador */
.perfil-text mark{
  padding: 0 .15rem;
  border-radius: .25rem;
}
</style>

<script>
/**
 * Este JS es seguro para contenido inyectado en modal:
 * - Usa eventos con namespace para no duplicar.
 * - Recalcula contadores según visibles/checked.
 * - Resalta coincidencia con <mark>.
 */
(function () {
  const $root = $('#perfilesContent'); // contenedor del modal donde inyectas el HTML

  // Helpers
  const escapeHtml = (s) => String(s)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  function updateCounts(){
    $('#countTotal').text($('.perfil-item:visible').length);
    $('#countSeleccionados').text($('.perfil-check:checked').length);
  }

  function highlight($item, q){
    const original = $item.data('nombre-original') || '';
    const $txt = $item.find('.perfil-text');

    if (!q){
      $txt.html(escapeHtml(original));
      return;
    }

    const lower = original.toLowerCase();
    const idx = lower.indexOf(q);
    if (idx === -1){
      $txt.html(escapeHtml(original));
      return;
    }

    const before = escapeHtml(original.slice(0, idx));
    const match  = escapeHtml(original.slice(idx, idx + q.length));
    const after  = escapeHtml(original.slice(idx + q.length));
    $txt.html(before + '<mark>' + match + '</mark>' + after);
  }

  function applyFilter(){
    const qRaw = ($('#perfilSearch').val() || '').trim();
    const q = qRaw.toLowerCase();

    let visibles = 0;
    $('.perfil-item').each(function(){
      const $it = $(this);
      const nombre = ($it.data('nombre') || '');
      const show = (!q || nombre.includes(q));
      $it.toggle(show);
      if (show) visibles++;
      highlight($it, q);
    });

    $('#noMatch').toggle(visibles === 0);

    $('#badgeFiltro').toggle(qRaw.length > 0);
    $('#textoFiltro').text(qRaw.length > 0 ? `Filtrando: ${qRaw}` : 'Filtrando');

    updateCounts();
  }

  // Evita duplicar handlers si abres el modal varias veces
  $(document).off('change.perfiles', '#perfilesContent .perfil-check');
  $(document).off('input.perfiles',  '#perfilesContent #perfilSearch');
  $(document).off('click.perfiles',  '#perfilesContent #btnClearSearch');
  $(document).off('click.perfiles',  '#perfilesContent #btnSelectAll');
  $(document).off('click.perfiles',  '#perfilesContent #btnSelectNone');
  $(document).off('submit.perfiles', '#perfilesContent #formPerfilesModal');

  // Cambiar estilo al marcar/desmarcar
  $(document).on('change.perfiles', '#perfilesContent .perfil-check', function(){
    $(this).closest('.perfil-item').find('.perfil-chip')
      .toggleClass('is-checked', $(this).is(':checked'));
    updateCounts();
  });

  // Buscar
  $(document).on('input.perfiles', '#perfilesContent #perfilSearch', applyFilter);

  // Limpiar
  $(document).on('click.perfiles', '#perfilesContent #btnClearSearch', function(){
    $('#perfilSearch').val('');
    applyFilter();
    $('#perfilSearch').focus();
  });

  // Seleccionar todo (solo visibles)
  $(document).on('click.perfiles', '#perfilesContent #btnSelectAll', function(){
    $('.perfil-item:visible .perfil-check').prop('checked', true).trigger('change');
  });

  // Ninguno (solo visibles)
  $(document).on('click.perfiles', '#perfilesContent #btnSelectNone', function(){
    $('.perfil-item:visible .perfil-check').prop('checked', false).trigger('change');
  });

  // Submit feedback (el AJAX lo manejas en tu index)
  $(document).on('submit.perfiles', '#perfilesContent #formPerfilesModal', function(){
    $('#btnGuardarPerfiles').prop('disabled', true)
      .html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');
  });

  // Inicial
  applyFilter();
})();
</script>
