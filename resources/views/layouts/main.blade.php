<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config_sistema('nombre_sistema', 'GesInventario') }}</title>

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

<body class="hold-transition sidebar-mini layout-fixed">
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
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-user"></i> {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Mi Perfil
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
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
                    <i class="fas fa-user-circle fa-2x text-white"></i>
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

                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <!-- INVENTARIO (Treeview) -->
                    <li class="nav-item {{ $invOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $invOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-warehouse"></i>
                            <p>
                                Inventario
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('area.index') }}" class="nav-link {{ request()->routeIs('area.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Áreas</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('responsable.index') }}" class="nav-link {{ request()->routeIs('responsable.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Responsables</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('responsable-area.index') }}" class="nav-link {{ request()->routeIs('responsable-area.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Asignaciones</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('ubicacion.index') }}" class="nav-link {{ request()->routeIs('ubicacion.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Ubicaciones</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('movimiento.index') }}" class="nav-link {{ request()->routeIs('movimiento.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Movimientos</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('tipo-mvto.index') }}" class="nav-link {{ request()->routeIs('tipo-mvto.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Tipo de Movimientos</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('bien.index') }}" class="nav-link {{ request()->routeIs('bien.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Bienes</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('documento-sustento.index') }}" class="nav-link {{ request()->routeIs('documento-sustento.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Documentos Sustento</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- CATÁLOGOS (Treeview) -->
                    <li class="nav-item {{ $catOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $catOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>
                                Catálogos
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('tipo-bien.index') }}" class="nav-link {{ request()->routeIs('tipo-bien.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Tipos de Bien</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('estado-bien.index') }}" class="nav-link {{ request()->routeIs('estado-bien.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Estados del Bien</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- REPORTES (Treeview) -->
                    <li class="nav-item {{ $repOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $repOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>
                                Reportes
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            {{-- ✅ NUEVO: Reporte de Bienes --}}
    <li class="nav-item">
      <a href="{{ route('reportes.bienes.index') }}"
         class="nav-link {{ request()->routeIs('reportes.bienes.*') ? 'active' : '' }}">
        <i class="far fa-circle nav-icon"></i>
        <p>Reporte de Bienes</p>
      </a>
    </li>
                            <li class="nav-item">
                                <a href="{{ route('reportes.kardex.index') }}" class="nav-link {{ request()->routeIs('reportes.kardex.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Kardex de Movimientos</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- SEGURIDAD (Treeview) -->
                    <li class="nav-item {{ $segOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $segOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shield-alt"></i>
                            <p>
                                Seguridad
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Usuarios</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('perfil.index') }}" class="nav-link {{ request()->routeIs('perfil.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Perfiles</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('permiso.index') }}" class="nav-link {{ request()->routeIs('permiso.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Permisos</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('modulo.index') }}" class="nav-link {{ request()->routeIs('modulo.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Módulos</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- CONFIGURACIÓN (Treeview) -->
<li class="nav-item {{ $confOpen ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ $confOpen ? 'active' : '' }}">
        <i class="nav-icon fas fa-cog"></i>
        <p>
            Configuración
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>

    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('configuracion.index') }}"
               class="nav-link {{ request()->routeIs('configuracion.index','configuracion.actualizar') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Apariencia del Sistema</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('configuracion.institucion') }}"
               class="nav-link {{ request()->routeIs('configuracion.institucion','configuracion.institucion.update') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Institución / Branding</p>
            </a>
        </li>
    </ul>
</li>


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

{{-- 8️⃣ SCRIPTS PERSONALIZADOS DE CADA VISTA --}}
@yield('js')

</body>
</html>
