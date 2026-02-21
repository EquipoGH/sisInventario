@extends('layouts.main')

@section('title', 'Acceso denegado')

@section('css')
    {{-- Animate.css (CDN). Si luego lo pasas a layout, quítalo de aquí. --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        /* Fondo elegante tipo “error page” */
        .page-403-wrap{
            min-height: calc(100vh - 210px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem 0;
        }
        .page-403-bg{
            background: radial-gradient(1200px circle at 10% 10%, rgba(220,53,69,.08), transparent 40%),
                        radial-gradient(1000px circle at 90% 30%, rgba(0,123,255,.08), transparent 45%),
                        linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
            border: 1px solid #e9ecef;
            border-radius: 14px;
            position: relative;
            overflow: hidden;
        }
        .page-403-bg::before{
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(0,0,0,.05) 1px, transparent 1px);
            background-size: 18px 18px;
            opacity: .35;
            pointer-events: none;
        }

        .icon-ring{
            width: 84px;
            height: 84px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(220,53,69,.12);
            border: 1px solid rgba(220,53,69,.25);
        }
        .icon-ring i{
            font-size: 30px;
            color: #dc3545;
        }

        .kpi-chip{
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .35rem .6rem;
            border-radius: 999px;
            background: #fff;
            border: 1px solid #e9ecef;
            color: #6c757d;
            font-size: .82rem;
            font-weight: 600;
        }

        .btn-soft-danger{
            background: rgba(220,53,69,.08);
            border: 1px solid rgba(220,53,69,.25);
            color: #dc3545;
            font-weight: 600;
            transition: all .2s ease;
        }
        .btn-soft-danger:hover{
            background: rgba(220,53,69,.14);
            transform: translateY(-1px);
        }

        /* Micro animación tipo la que ya tienes (fade-in) */
        @keyframes fadeInSoft {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in-soft{ animation: fadeInSoft .25s ease-in; }

        /* Overlay estilo AdminLTE (por si quieres simular carga) */
        .card .overlay.custom-overlay{
            background: rgba(255,255,255,.75);
        }
    </style>
@endsection

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0">
            <i class="fas fa-ban text-danger mr-1"></i> Acceso denegado
        </h1>

        <div class="d-flex" style="gap:.5rem;">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>

            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="page-403-wrap">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12 col-md-11 col-lg-9 col-xl-7">

                    <div class="card shadow-sm page-403-bg animate__animated animate__fadeInUp">
                        <div class="card-body p-4 p-md-5 position-relative">

                            {{-- Si algún día quieres mostrar “cargando”, AdminLTE recomienda overlay dentro del card --}}
                            {{-- <div class="overlay custom-overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div> --}}

                            <div class="text-center position-relative">
                                <div class="mb-3">
                                    <span class="icon-ring animate__animated animate__heartBeat animate__delay-1s">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <span class="kpi-chip">
                                        <i class="fas fa-shield-alt text-secondary"></i>
                                        Código 403 — Seguridad
                                    </span>
                                </div>

                                <h2 class="h4 mb-2">No tienes permiso para ver esta sección</h2>

                                <p class="text-muted mb-4">
                                    Tu cuenta no cuenta con los accesos necesarios para continuar.
                                    Si crees que es un error, solicita al administrador que te habilite el rol o permiso correspondiente.
                                </p>

                                <div class="d-flex flex-column flex-sm-row justify-content-center" style="gap:.6rem;">
                                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-home"></i> Ir al Dashboard
                                    </a>

                                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-undo"></i> Regresar
                                    </a>

                                    <button type="button" class="btn btn-soft-danger btn-lg" id="btnAyuda403">
                                        <i class="fas fa-headset"></i> Solicitar ayuda
                                    </button>
                                </div>

                                <hr class="my-4">

                                <div class="callout callout-danger text-left fade-in-soft mb-0">
                                    <h5 class="mb-1">
                                        <i class="fas fa-info-circle"></i> ¿Qué puedes hacer ahora?
                                    </h5>
                                    <div class="text-muted" style="font-size:.95rem;">
                                        Verifica que estés ingresando con el usuario correcto o vuelve al Dashboard.
                                        Si necesitas acceso, pide al administrador que te habilite el módulo.
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="alert alert-light border mt-3 animate__animated animate__fadeIn animate__delay-1s">
                        <i class="fas fa-user text-secondary mr-1"></i>
                        Usuario: <strong>{{ auth()->user()->name }}</strong>
                        <span class="text-muted">— si cambiaste de rol recientemente, vuelve a iniciar sesión.</span>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('btnAyuda403');
            if (!btn) return;

            btn.addEventListener('click', function () {
                Swal.fire({
                    icon: 'info',
                    title: 'Solicitar acceso',
                    html: `
                        <p class="mb-2">Copia y envía este mensaje al administrador:</p>
                        <div class="alert alert-light text-left mb-0" style="font-size:.92rem;">
                            <div><strong>Usuario:</strong> {{ auth()->user()->name }}</div>
                            <div><strong>Ruta:</strong> {{ request()->path() }}</div>
                            <div><strong>Acción:</strong> Solicitar permiso de acceso (403)</div>
                        </div>
                    `,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#6c757d'
                });
            });
        });
    </script>
@stop
