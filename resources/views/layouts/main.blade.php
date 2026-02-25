<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config_sistema('nombre_sistema', 'GesInventario') }}</title>



    {{-- ⭐ ESTILOS PARA BADGES DE ROL --}}
<style>
    /* Badge en navbar superior */
    .navbar .nav-item.dropdown .badge {
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        animation: fadeIn 0.3s ease-in;
    }

    /* Badge en sidebar */
    .user-panel .badge {
        font-size: 9px !important;
        padding: 3px 6px;
        border-radius: 3px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Ajuste del dropdown del usuario */
    .navbar .nav-item.dropdown > .nav-link {
        white-space: nowrap;
        padding: 8px 12px;
    }

    /* Animación suave */
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }

    /* Responsive: ocultar badge en móviles pequeños */
    @media (max-width: 576px) {
        .navbar .nav-item.dropdown .badge {
            display: none;
        }
    }
</style>

@php $fav = setting('favicon_path'); @endphp
@if($fav)
    <link rel="icon" type="image/png" href="{{ asset('storage/'.$fav) }}">
@endif




    <!-- 1️⃣ Google Font: Inter -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">

    <!-- 2️⃣ Font Awesome LOCAL -->
    <link rel="stylesheet" href="{{ asset('fonts/fontawesome-free-6.5.1-web/css/all.min.css') }}">

    <!-- 3️⃣ AdminLTE 3.2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- 4️⃣ CSS GLOBAL PERSONALIZADO -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <!-- 5️⃣ DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

    <!-- 6️⃣ SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.3/dist/select2-bootstrap4.min.css" rel="stylesheet" />

    <!-- 7️⃣ CSS DINÁMICO DESDE BD -->
    @include('components.dynamic-styles')




    <!-- 8️⃣ CSS ESPECÍFICO DE CADA VISTA -->
    @yield('css')




</head>

@php
    // Estados para abrir/cerrar menús (Treeview)
    $invOpen = request()->routeIs('area.*','responsable.*','responsable-area.*','ubicacion.*','movimiento.*','tipo-mvto.*','bien.*','documento-sustento.*');
    $catOpen = request()->routeIs('tipo-bien.*','estado-bien.*');
    $repOpen = request()->routeIs(['reportes.kardex.*', 'reportes.bienes.*']);
    $repOpen = request()->routeIs('reportes.kardex.*');
    $segOpen = request()->routeIs('user.*','perfil.*','permiso.*','modulo.*');
    $confOpen = request()->routeIs('configuracion.*', 'configuracion.institucion*');
@endphp
@php
    $user = Auth::user();
    $photoUrl = null;

    if ($user && $user->profile_photo_path) {
        $photoUrl = Storage::url($user->profile_photo_path);
    }
@endphp

<body class="hold-transition sidebar-mini layout-fixed layout-footer-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">

        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle sidebar">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button" aria-label="Fullscreen">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>

            <li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#" style="display:flex; align-items:center; gap:8px;">
        @if($photoUrl)
            <img src="{{ $photoUrl }}"
                 alt="Foto de {{ $user->name }}"
                 class="img-circle elevation-2"
                 style="width:28px; height:28px; object-fit:cover;">
        @else
            <i class="fas fa-user"></i>
        @endif

        <span>{{ $user->name }}</span>

        {{-- BADGE DEL ROL --}}
        @php
            $rol = $user->rol_usuario ?? 'USUARIO';
            $colorRol = match(strtoupper($rol)) {
                'ADMIN', 'ADMINISTRADOR' => 'badge-danger',
                'ENCARGADO', 'SUPERVISOR' => 'badge-warning',
                'USUARIO' => 'badge-info',
                default => 'badge-secondary'
            };
        @endphp

        <span class="badge {{ $colorRol }}" style="font-size: 9px; padding: 3px 8px; border-radius: 10px;">
            {{ strtoupper($rol) }}
        </span>
    </a>

    <div class="dropdown-menu dropdown-menu-right">
        <a href="{{ route('profile.edit') }}" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Mi Perfil
        </a>
        <div class="dropdown-divider"></div>
        <form method="POST" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <button type="button" class="dropdown-item" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt mr-2 text-danger"></i> Cerrar Sesión
            </button>
        </form>
    </div>
</li>


        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar {{ setting('sidebar_theme','sidebar-dark-primary') }} elevation-4">

        <!-- Brand Logo -->
        @php
    $logo = setting('logo_path');
    $nombreSistema = setting('nombre_sistema', 'GesInventario');
@endphp

<a href="{{ route('dashboard') }}" class="brand-link">
    @if($logo)
        <img src="{{ asset('storage/'.$logo) }}" class="brand-image img-circle elevation-3" style="opacity:.9" alt="Logo">
    @else
        <i class="fas fa-box-open brand-image" style="opacity:.9"></i>
    @endif

    <span class="brand-text font-weight-light">{{ $nombreSistema }}</span>
</a>



        <!-- Sidebar -->
        <div class="sidebar">

            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
    @if($photoUrl)
        <img src="{{ $photoUrl }}"
             alt="Foto de {{ $user->name }}"
             class="img-circle elevation-2"
             style="width:34px; height:34px; object-fit:cover;">
    @else
        <i class="fas fa-user-circle fa-2x text-white"></i>
    @endif
</div>

                <div class="info">
                    <a href="{{ route('profile.edit') }}" class="d-block">{{ Auth::user()->name }}</a>
                </div>
            </div>

            <!-- ✅ Sidebar Search (AdminLTE plugin) -->
            <div class="form-inline mb-2">
                <div class="input-group" data-widget="sidebar-search">
                    <input class="form-control form-control-sidebar" type="search" placeholder="Buscar..." aria-label="Buscar">
                    <div class="input-group-append">
                        <button class="btn btn-sidebar" type="button">
                            <i class="fas fa-search fa-fw"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column"
                    data-widget="treeview" role="menu"
                    data-accordion="false">
                    @include('layouts.sidebar-dinamico')
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">

        <!-- Content Header -->
        @hasSection('content_header')
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            @yield('content_header')
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Version</b> 1.0.0
        </div>
        <strong>{{ config_sistema('nombre_sistema', 'Sistema de Inventario') }} &copy; {{ date('Y') }}</strong>
    </footer>
</div>

{{-- ==========================================
    ⭐⭐⭐ SCRIPTS EN ORDEN CORRECTO ⭐⭐⭐
    ========================================== --}}

<!-- 1️⃣ jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- 2️⃣ Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3️⃣ AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- 4️⃣ DataTables -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<!-- 5️⃣ Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- 6️⃣ SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- 7️⃣ Moment.js -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/locale/es.js"></script>

<!-- ✅ Persistencia del estado del sidebar (colapsado/expandido) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const key = 'sidebar-collapsed';

        // Restaurar estado
        if (localStorage.getItem(key) === '1') {
            document.body.classList.add('sidebar-collapse');
        }

        // Guardar cuando el usuario hace toggle
        const btn = document.querySelector('[data-widget="pushmenu"]');
        if (btn) {
            btn.addEventListener('click', function () {
                setTimeout(() => {
                    localStorage.setItem(
                        key,
                        document.body.classList.contains('sidebar-collapse') ? '1' : '0'
                    );
                }, 50);
            });
        }
    });
</script>

{{-- ✅ Script para confirmar cierre de sesión profesional y animado --}}
<script>
    function confirmLogout() {
        Swal.fire({
            title: '¿Desea cerrar su sesión?',
            text: "Se cerrará de forma segura su sesión en el sistema.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-sign-out-alt"></i> Sí, cerrar sesión',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true,
            padding: '2em',
            customClass: {
                title: 'text-dark',
                popup: 'rounded-lg shadow-lg'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cerrando sesión...',
                    html: 'Por favor, espere un momento.',
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                        setTimeout(() => {
                            document.getElementById('logout-form').submit();
                        }, 800);
                    }
                });
            }
        });
    }
</script>

{{-- 8️⃣ SCRIPTS PERSONALIZADOS DE CADA VISTA --}}
@yield('js')

</body>
</html>
