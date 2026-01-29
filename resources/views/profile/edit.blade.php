@extends('layouts.main')

@section('title', 'Mi Perfil')

@section('css')
<style>
    /* ====== Paleta PRO (suave, limpia) ====== */
    :root{
        --gi-bg-1:#f5f7ff;
        --gi-bg-2:#eef2ff;
        --gi-accent:#4f46e5;      /* Indigo */
        --gi-accent-2:#7c3aed;    /* Purple */
        --gi-text:#0f172a;        /* Slate 900 */
        --gi-muted:#64748b;       /* Slate 500 */
        --gi-border:rgba(15, 23, 42, .10);
        --gi-shadow: 0 24px 60px rgba(15,23,42,.12);
    }

    /* Wrapper dentro del content de AdminLTE */
    .gi-profile-wrap{
        border-radius: 18px;
        padding: 22px;
        background:
            radial-gradient(900px 400px at 10% 0%, rgba(79,70,229,.10), transparent 55%),
            radial-gradient(900px 400px at 90% 0%, rgba(124,58,237,.10), transparent 55%),
            linear-gradient(180deg, var(--gi-bg-1) 0%, var(--gi-bg-2) 100%);
        border: 1px solid rgba(79,70,229,.12);
    }

    .gi-hero{
        border-radius: 16px;
        padding: 18px 18px;
        margin-bottom: 16px;
        background: rgba(255,255,255,.70);
        border: 1px solid rgba(255,255,255,.65);
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 30px rgba(15,23,42,.08);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
    }

    .gi-hero h2{
        margin:0;
        color:var(--gi-text);
        font-weight:900;
        font-size: 18px;
        display:flex;
        align-items:center;
        gap:10px;
    }

    .gi-hero p{
        margin:4px 0 0 0;
        color:var(--gi-muted);
        font-weight:600;
        font-size: 13px;
    }

    .gi-badge{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 10px 12px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(79,70,229,.12), rgba(124,58,237,.10));
        border: 1px solid rgba(79,70,229,.16);
        color: var(--gi-text);
        font-weight: 900;
        font-size: 12px;
        white-space: nowrap;
    }

    /* Cards estilo pro */
    .gi-card{
        background: rgba(255,255,255,.82);
        border: 1px solid rgba(255,255,255,.65);
        border-radius: 18px;
        box-shadow: var(--gi-shadow);
        overflow:hidden;
    }

    .gi-card-header{
        padding: 16px 18px;
        border-bottom: 1px solid var(--gi-border);
        background: rgba(255,255,255,.92);
    }

    .gi-title{
        margin:0;
        font-size: 16px;
        font-weight: 900;
        color: var(--gi-text);
        display:flex;
        align-items:center;
        gap:10px;
    }

    .gi-subtitle{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: var(--gi-muted);
        font-weight: 600;
    }

    .gi-section{
        padding: 16px 18px;
    }

    /* Inputs tipo register */
    .gi-label{
        display:block;
        font-size: 13px;
        font-weight: 900;
        color: #334155;
        margin-bottom: 8px;
    }

    .gi-input{
        width: 100%;
        border: 2px solid rgba(148,163,184,.65);
        border-radius: 14px;
        padding: 12px 14px;
        transition: .2s;
        outline: none;
        font-weight: 700;
        color: #111827;
        background: rgba(255,255,255,.95);
    }

    .gi-input:focus{
        border-color: rgba(79,70,229,.85);
        box-shadow: 0 0 0 4px rgba(79,70,229,.12);
    }

    .gi-help{
        margin-top: 6px;
        font-size: 12px;
        color: var(--gi-muted);
        font-weight: 600;
    }

    /* Botones */
    .gi-btn{
        border: 0;
        border-radius: 14px;
        padding: 12px 16px;
        font-weight: 900;
        width: 100%;
        transition: .2s;
    }

    .gi-btn-primary{
        color:#fff;
        background: linear-gradient(135deg, var(--gi-accent) 0%, var(--gi-accent-2) 100%);
        box-shadow: 0 14px 28px rgba(79,70,229,.25);
    }
    .gi-btn-primary:hover{
        transform: translateY(-1px);
        box-shadow: 0 18px 40px rgba(79,70,229,.30);
        color:#fff;
    }

    .gi-btn-outline{
        background: rgba(255,255,255,.90);
        border: 2px solid rgba(148,163,184,.75);
        color: #334155;
    }
    .gi-btn-outline:hover{
        background: rgba(248,250,252,1);
    }

    .gi-btn-danger{
        background: #dc2626;
        color:#fff;
        box-shadow: 0 14px 28px rgba(220,38,38,.20);
    }
    .gi-btn-danger:hover{
        background:#b91c1c;
        color:#fff;
        transform: translateY(-1px);
    }

    /* Alerts */
    .gi-alert{
        border-radius: 14px;
        padding: 12px 14px;
        font-weight: 800;
        font-size: 13px;
        border: 1px solid transparent;
        background: rgba(255,255,255,.80);
    }
    .gi-alert-success{ background:#ecfdf5; border-color:#bbf7d0; color:#065f46; }
    .gi-alert-warn{ background:#fffbeb; border-color:#fde68a; color:#92400e; }
    .gi-alert-danger{ background:#fef2f2; border-color:#fecaca; color:#991b1b; }

    .gi-eye-btn{
        position:absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        border:0;
        background: transparent;
        color:#64748b;
        padding: 6px;
    }
</style>
@endsection

@section('content_header')
    <h1 class="m-0"><i class="fas fa-user mr-2"></i> Perfil</h1>
@endsection

@section('content')
<div class="gi-profile-wrap">

    <div class="gi-hero">
        <div>
            <h2><i class="fas fa-user-gear text-primary"></i> Configuración de cuenta</h2>
            <p>Actualiza tu DNI, nombre, correo y seguridad de acceso.</p>
        </div>
        <div class="gi-badge">
            <i class="fas fa-user"></i>
            {{ Auth::user()->name }}
        </div>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="gi-alert gi-alert-success mb-3">
            <i class="fas fa-check-circle mr-2"></i> Perfil actualizado correctamente.
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="gi-alert gi-alert-success mb-3">
            <i class="fas fa-check-circle mr-2"></i> Contraseña actualizada correctamente.
        </div>
    @endif

    @if (session('status') === 'verification-link-sent')
        <div class="gi-alert gi-alert-success mb-3">
            <i class="fas fa-paper-plane mr-2"></i> Se envió el enlace de verificación a tu correo.
        </div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="gi-card mb-4">
                <div class="gi-card-header">
                    <h3 class="gi-title"><i class="fas fa-id-card text-primary"></i> Información del perfil</h3>
                    <p class="gi-subtitle">Actualiza tu DNI, nombre y correo electrónico.</p>
                </div>
                <div class="gi-section">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="gi-card mb-4">
                <div class="gi-card-header">
                    <h3 class="gi-title"><i class="fas fa-key text-warning"></i> Actualizar contraseña</h3>
                    <p class="gi-subtitle">Usa una contraseña larga y difícil de adivinar.</p>
                </div>
                <div class="gi-section">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="gi-card">
                <div class="gi-card-header">
                    <h3 class="gi-title" style="color:#b91c1c;"><i class="fas fa-triangle-exclamation"></i> Zona de riesgo</h3>
                    <p class="gi-subtitle">Eliminar cuenta es permanente.</p>
                </div>
                <div class="gi-section">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(inputId + '-eye');
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function giToggleBox(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('d-none');
    }

    // DNI solo números (máx 8)
    const dni = document.getElementById('dni_usuario');
    if (dni) {
        dni.addEventListener('input', function(){
            this.value = this.value.replace(/[^0-9]/g,'').slice(0,8);
        });
    }
</script>
@endsection
