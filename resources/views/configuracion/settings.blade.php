@extends('layouts.main')

@section('title', 'Configuración')

@php
    $logoPath = $settings['logo_path'] ?? null;
    $favPath  = $settings['favicon_path'] ?? null;
    $repPath  = $settings['logo_reportes_path'] ?? null;

    // IMPORTANTE: volvemos a asset('storage/...') porque a ti te funcionaba así.
    $logoUrl = $logoPath ? asset('storage/' . $logoPath) : null;
    $favUrl  = $favPath  ? asset('storage/' . $favPath)  : null;
    $repUrl  = $repPath  ? asset('storage/' . $repPath)  : null;

    $st = old('sidebar_theme', $settings['sidebar_theme'] ?? 'sidebar-dark-primary');
@endphp

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="m-0">Configuración</h1>
            <small class="text-muted">Institución</small>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-home mr-1"></i> Dashboard
        </a>
    </div>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            Revisa los campos marcados.
        </div>
    @endif

    <form method="POST" action="{{ route('configuracion.institucion.update') }}" enctype="multipart/form-data" id="form-branding">
        @csrf
        @method('PUT')

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cog mr-1"></i> Ajustes
                </h3>

                <div class="card-tools">
                    <span class="badge badge-light border" id="badgeDirty" style="display:none;">Cambios sin guardar</span>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" aria-label="Colapsar">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-identidad" role="tab">Identidad</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-contacto" role="tab">Contacto</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-regional" role="tab">Regional</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-reportes" role="tab">Reportes</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-soporte" role="tab">Soporte</a></li>
                </ul>

                <div class="tab-content pt-3">

                    {{-- IDENTIDAD --}}
                    <div class="tab-pane fade show active" id="tab-identidad" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre_institucion">Nombre</label>
                                    <input id="nombre_institucion" name="nombre_institucion" type="text"
                                           class="form-control @error('nombre_institucion') is-invalid @enderror"
                                           value="{{ old('nombre_institucion', $settings['nombre_institucion'] ?? '') }}"
                                           required maxlength="120">
                                    @error('nombre_institucion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="slogan">Slogan</label>
                                    <input id="slogan" name="slogan" type="text"
                                           class="form-control @error('slogan') is-invalid @enderror"
                                           value="{{ old('slogan', $settings['slogan'] ?? '') }}"
                                           maxlength="120">
                                    @error('slogan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="ruc">RUC</label>
                                    <input id="ruc" name="ruc" type="text"
                                           class="form-control @error('ruc') is-invalid @enderror"
                                           value="{{ old('ruc', $settings['ruc'] ?? '') }}"
                                           maxlength="30">
                                    @error('ruc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="web">Web</label>
                                    <input id="web" name="web" type="url"
                                           class="form-control @error('web') is-invalid @enderror"
                                           value="{{ old('web', $settings['web'] ?? '') }}"
                                           placeholder="https://">
                                    @error('web') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="items_por_pagina">Items por página</label>
                                    <input id="items_por_pagina" name="items_por_pagina" type="number"
                                           min="1" max="500"
                                           class="form-control @error('items_por_pagina') is-invalid @enderror"
                                           value="{{ old('items_por_pagina', $settings['items_por_pagina'] ?? 10) }}"
                                           required>
                                    @error('items_por_pagina') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group mb-0">
                                    <label for="sidebar_theme">Sidebar</label>
                                    <select id="sidebar_theme" name="sidebar_theme"
                                            class="form-control @error('sidebar_theme') is-invalid @enderror" required>
                                        <option value="sidebar-dark-primary" {{ $st === 'sidebar-dark-primary' ? 'selected' : '' }}>Dark Primary</option>
                                        <option value="sidebar-dark-success" {{ $st === 'sidebar-dark-success' ? 'selected' : '' }}>Dark Success</option>
                                        <option value="sidebar-dark-info" {{ $st === 'sidebar-dark-info' ? 'selected' : '' }}>Dark Info</option>
                                        <option value="sidebar-light-primary" {{ $st === 'sidebar-light-primary' ? 'selected' : '' }}>Light Primary</option>
                                    </select>
                                    @error('sidebar_theme') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card branding-preview">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="preview-avatar mr-3" id="previewLogoWrap">
                                                @if($logoUrl)
                                                    <img id="previewLogoImg" src="{{ $logoUrl }}" alt="Logo"
                                                         onerror="this.remove(); document.getElementById('previewLogoWrap').innerHTML='<i class=&quot;fas fa-image text-muted&quot;></i>';">
                                                @else
                                                    <i class="fas fa-image text-muted"></i>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <div class="font-weight-bold text-truncate" id="previewNombre">
                                                    {{ old('nombre_institucion', $settings['nombre_institucion'] ?? 'Institución') }}
                                                </div>
                                                <div class="text-muted small text-truncate" id="previewSlogan">
                                                    {{ old('slogan', $settings['slogan'] ?? '') }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 d-flex align-items-center flex-wrap">
                                            <div class="mini">
                                                <div class="mini-title">Favicon</div>
                                                <div class="mini-box" id="previewFavBox">
                                                    @if($favUrl)
                                                        <img id="previewFavImg" src="{{ $favUrl }}" alt="Favicon"
                                                             onerror="this.remove(); document.getElementById('previewFavBox').innerHTML='<i class=&quot;fas fa-star text-muted&quot;></i>';">
                                                    @else
                                                        <i class="fas fa-star text-muted"></i>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mini">
                                                <div class="mini-title">Reportes</div>
                                                <div class="mini-box wide" id="previewRepBox">
                                                    @if($repUrl)
                                                        <img id="previewRepImg" src="{{ $repUrl }}" alt="Logo reportes"
                                                             onerror="this.remove(); document.getElementById('previewRepBox').innerHTML='<i class=&quot;fas fa-file-image text-muted&quot;></i>';">
                                                    @else
                                                        <i class="fas fa-file-image text-muted"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="form-group">
                                    <label>Logo</label>
                                    <div class="custom-file">
                                        <input type="file" name="logo" id="logo"
                                               class="custom-file-input @error('logo') is-invalid @enderror"
                                               accept="image/png,image/jpeg,image/webp">
                                        <label class="custom-file-label" for="logo">Seleccionar…</label>
                                    </div>
                                    @error('logo') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                                    <small class="text-muted" id="helpLogo">Máx 2MB.</small>
                                </div>

                                <div class="form-group">
                                    <label>Favicon</label>
                                    <div class="custom-file">
                                        <input type="file" name="favicon" id="favicon"
                                               class="custom-file-input @error('favicon') is-invalid @enderror"
                                               accept="image/png,image/x-icon">
                                        <label class="custom-file-label" for="favicon">Seleccionar…</label>
                                    </div>
                                    @error('favicon') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                                    <small class="text-muted" id="helpFav">Máx 1MB.</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label>Logo reportes</label>
                                    <div class="custom-file">
                                        <input type="file" name="logo_reportes" id="logo_reportes"
                                               class="custom-file-input @error('logo_reportes') is-invalid @enderror"
                                               accept="image/png,image/jpeg,image/webp">
                                        <label class="custom-file-label" for="logo_reportes">Seleccionar…</label>
                                    </div>
                                    @error('logo_reportes') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                                    <small class="text-muted" id="helpRep">Máx 2MB.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CONTACTO --}}
                    <div class="tab-pane fade" id="tab-contacto" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email_contacto">Email</label>
                                    <input id="email_contacto" type="email" name="email_contacto"
                                           class="form-control @error('email_contacto') is-invalid @enderror"
                                           value="{{ old('email_contacto', $settings['email_contacto'] ?? '') }}">
                                    @error('email_contacto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input id="telefono" type="text" name="telefono"
                                           class="form-control @error('telefono') is-invalid @enderror"
                                           value="{{ old('telefono', $settings['telefono'] ?? '') }}">
                                    @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="direccion">Dirección</label>
                                    <input id="direccion" type="text" name="direccion"
                                           class="form-control @error('direccion') is-invalid @enderror"
                                           value="{{ old('direccion', $settings['direccion'] ?? '') }}">
                                    @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- REGIONAL --}}
                    <div class="tab-pane fade" id="tab-regional" role="tabpanel">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="moneda">Moneda</label>
                                    <input id="moneda" type="text" name="moneda"
                                           class="form-control @error('moneda') is-invalid @enderror"
                                           value="{{ old('moneda', $settings['moneda'] ?? 'PEN') }}" required>
                                    @error('moneda') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="timezone">Zona horaria</label>
                                    <input id="timezone" type="text" name="timezone"
                                           class="form-control @error('timezone') is-invalid @enderror"
                                           value="{{ old('timezone', $settings['timezone'] ?? 'America/Lima') }}" required>
                                    @error('timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="locale">Locale</label>
                                    <input id="locale" type="text" name="locale"
                                           class="form-control @error('locale') is-invalid @enderror"
                                           value="{{ old('locale', $settings['locale'] ?? 'es_PE') }}" required>
                                    @error('locale') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_format">Formato fecha</label>
                                    <input id="date_format" type="text" name="date_format"
                                           class="form-control @error('date_format') is-invalid @enderror"
                                           value="{{ old('date_format', $settings['date_format'] ?? 'd/m/Y') }}" required>
                                    @error('date_format') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- REPORTES --}}
                    <div class="tab-pane fade" id="tab-reportes" role="tabpanel">
                        <div class="form-group">
                            <label for="pie_reportes">Pie</label>
                            <textarea id="pie_reportes" name="pie_reportes" rows="3"
                                      class="form-control @error('pie_reportes') is-invalid @enderror">{{ old('pie_reportes', $settings['pie_reportes'] ?? '') }}</textarea>
                            @error('pie_reportes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label for="texto_legal">Texto legal</label>
                            <textarea id="texto_legal" name="texto_legal" rows="4"
                                      class="form-control @error('texto_legal') is-invalid @enderror">{{ old('texto_legal', $settings['texto_legal'] ?? '') }}</textarea>
                            @error('texto_legal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- SOPORTE --}}
                    <div class="tab-pane fade" id="tab-soporte" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="correo_soporte">Correo</label>
                                    <input id="correo_soporte" type="email" name="correo_soporte"
                                           class="form-control @error('correo_soporte') is-invalid @enderror"
                                           value="{{ old('correo_soporte', $settings['correo_soporte'] ?? '') }}">
                                    @error('correo_soporte') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefono_soporte">Teléfono</label>
                                    <input id="telefono_soporte" type="text" name="telefono_soporte"
                                           class="form-control @error('telefono_soporte') is-invalid @enderror"
                                           value="{{ old('telefono_soporte', $settings['telefono_soporte'] ?? '') }}">
                                    @error('telefono_soporte') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">Guarda para aplicar.</small>
                <button class="btn btn-primary" type="submit" id="btnSave">
                    <span class="btn-save-icon"><i class="fas fa-save mr-1"></i></span>
                    <span class="btn-save-text">Guardar</span>
                </button>
            </div>
        </div>
    </form>
@endsection

@section('css')
<style>
    .nav-tabs .nav-link { border-top-left-radius: .5rem; border-top-right-radius: .5rem; }
    .nav-tabs .nav-link.active { font-weight: 600; }

    .branding-preview { border-radius: .75rem; border: 1px solid rgba(0,0,0,.06); }
    .preview-avatar{
        width:56px; height:56px; border-radius:12px;
        background:#f1f3f5; border:1px solid rgba(0,0,0,.06);
        display:flex; align-items:center; justify-content:center;
        overflow:hidden;
    }
    .preview-avatar img{ width:100%; height:100%; object-fit:cover; display:block; }

    .mini{ margin-right: .75rem; margin-bottom: .5rem; }
    .mini-title{ font-size:.75rem; color:#6c757d; margin-bottom:.25rem; }
    .mini-box{
        width:70px; height:42px; border-radius:10px;
        background:#f8f9fa; border:1px dashed rgba(0,0,0,.18);
        display:flex; align-items:center; justify-content:center;
        overflow:hidden;
    }
    .mini-box.wide{ width:120px; }
    .mini-box img{ max-width:100%; max-height:100%; display:block; }
</style>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-branding');
    const badgeDirty = document.getElementById('badgeDirty');
    const btnSave = document.getElementById('btnSave');

    const nombre = document.getElementById('nombre_institucion');
    const slogan = document.getElementById('slogan');
    const previewNombre = document.getElementById('previewNombre');
    const previewSlogan = document.getElementById('previewSlogan');

    const logoInput = document.getElementById('logo');
    const favInput  = document.getElementById('favicon');
    const repInput  = document.getElementById('logo_reportes');

    const helpLogo = document.getElementById('helpLogo');
    const helpFav  = document.getElementById('helpFav');
    const helpRep  = document.getElementById('helpRep');

    const setDirty = (v) => {
        if (badgeDirty) badgeDirty.style.display = v ? 'inline-flex' : 'none';
    };

    const setFileLabel = (input) => {
        const label = input?.parentElement?.querySelector('.custom-file-label');
        if (!label) return;
        label.textContent = input.files && input.files[0] ? input.files[0].name : 'Seleccionar…';
    };

    const mb = (bytes) => (bytes / 1024 / 1024).toFixed(2);

    const validateMaxMB = (input, maxMB, helpEl) => {
        if (!input?.files?.[0]) return true;
        const f = input.files[0];
        const ok = f.size <= maxMB * 1024 * 1024;

        if (helpEl) {
            helpEl.classList.toggle('text-danger', !ok);
            helpEl.classList.toggle('text-muted', ok);
            helpEl.textContent = ok ? `${f.name} (${mb(f.size)} MB)` : `Archivo grande (${mb(f.size)} MB). Máx ${maxMB}MB.`;
        }
        return ok;
    };

    const setImgPreview = (boxId, imgId, file, fallbackHtml) => {
        const box = document.getElementById(boxId);
        if (!box || !file) return;

        box.innerHTML = '';
        const img = document.createElement('img');
        img.id = imgId;
        img.alt = 'preview';
        img.src = URL.createObjectURL(file);
        img.onerror = () => { box.innerHTML = fallbackHtml; };
        box.appendChild(img);
    };

    const setLogoPreview = (file) => {
        const wrap = document.getElementById('previewLogoWrap');
        if (!wrap || !file) return;

        wrap.innerHTML = '';
        const img = document.createElement('img');
        img.id = 'previewLogoImg';
        img.alt = 'Logo';
        img.src = URL.createObjectURL(file);
        img.onerror = () => { wrap.innerHTML = '<i class="fas fa-image text-muted"></i>'; };
        wrap.appendChild(img);
    };

    form?.querySelectorAll('input,select,textarea').forEach(el => {
        el.addEventListener('change', () => setDirty(true));
    });

    nombre?.addEventListener('input', () => {
        if (previewNombre) previewNombre.textContent = nombre.value || 'Institución';
        setDirty(true);
    });

    slogan?.addEventListener('input', () => {
        if (previewSlogan) previewSlogan.textContent = slogan.value || '';
        setDirty(true);
    });

    logoInput?.addEventListener('change', () => {
        setFileLabel(logoInput);
        if (!validateMaxMB(logoInput, 2, helpLogo)) return;
        if (logoInput.files[0]) setLogoPreview(logoInput.files[0]);
        setDirty(true);
    });

    favInput?.addEventListener('change', () => {
        setFileLabel(favInput);
        if (!validateMaxMB(favInput, 1, helpFav)) return;
        if (favInput.files[0]) {
            setImgPreview('previewFavBox', 'previewFavImg', favInput.files[0], '<i class="fas fa-star text-muted"></i>');
        }
        setDirty(true);
    });

    repInput?.addEventListener('change', () => {
        setFileLabel(repInput);
        if (!validateMaxMB(repInput, 2, helpRep)) return;
        if (repInput.files[0]) {
            setImgPreview('previewRepBox', 'previewRepImg', repInput.files[0], '<i class="fas fa-file-image text-muted"></i>');
        }
        setDirty(true);
    });

    form?.addEventListener('submit', () => {
        if (!btnSave) return;
        btnSave.disabled = true;
        const icon = btnSave.querySelector('.btn-save-icon');
        const text = btnSave.querySelector('.btn-save-text');
        if (icon) icon.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>';
        if (text) text.textContent = 'Guardando...';
    });
});
</script>
@endsection
